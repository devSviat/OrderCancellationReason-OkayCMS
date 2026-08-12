<?php

namespace Modules\Sviat\OrderCancellationReason;

use Okay\Core\Settings;
use Okay\Modules\Sviat\OrderCancellationReason\Backend\Helpers\BackendOrderCancellationReasonHelper as Helper;
use Okay\Modules\Sviat\OrderCancellationReason\Entities\OrderCancellationReasonEntity;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/Support/ModuleTestCase.php';

use Modules\Sviat\OrderCancellationReason\Support\ModuleTestCase;

/**
 * Причина скасування замовлення: що менеджер бачить у картці й до якого запису
 * історії вона чіпляється. Помилка тут не ламає нічого технічно — вона просто
 * показує менеджеру чужу або порожню причину.
 */
#[AllowMockObjectsWithoutExpectations]
class BackendOrderCancellationReasonHelperTest extends ModuleTestCase
{
    private function buildHelper(mixed $cancelledStatusId = 5, ?object $foundReason = null): Helper
    {
        $settings = $this->createStub(Settings::class);
        $settings->method('get')->willReturn($cancelledStatusId);

        $entity = $this->mockEntity(OrderCancellationReasonEntity::class, ['findOne', 'find']);
        $entity->method('findOne')->willReturn($foundReason ?? false);
        $entity->method('find')->willReturn([]);

        return new Helper(
            $this->mockEntityFactory([OrderCancellationReasonEntity::class => $entity]),
            $settings
        );
    }

    // --- статус скасування --------------------------------------------------

    /** @dataProvider cancelledStatusIdProvider */
    #[DataProvider('cancelledStatusIdProvider')]
    public function testCancelledStatusIdIsCastOrNull(mixed $stored, ?int $expected): void
    {
        self::assertSame($expected, $this->buildHelper($stored)->getCancelledStatusId());
    }

    public static function cancelledStatusIdProvider(): array
    {
        return [
            'число'           => [5, 5],
            'числовий рядок'  => ['5', 5],
            'не задано'       => [null, null],
            'порожній рядок'  => ['', null],
            'нуль'            => [0, 0],
        ];
    }

    /**
     * Порівняння статусів має бути стійким до типу: у різних місцях id статусу
     * приходить то int, то рядком із форми.
     */
    public function testStatusComparisonIgnoresType(): void
    {
        $helper = $this->buildHelper('5');

        self::assertTrue($helper->isCancelledStatus(5));
        self::assertFalse($helper->isCancelledStatus(6));
    }

    /** Без налаштованого статусу скасування жоден статус не вважається скасуванням. */
    public function testNothingIsCancelledWhenTheStatusIsNotConfigured(): void
    {
        $helper = $this->buildHelper(null);

        self::assertFalse($helper->isCancelledStatus(5));
        self::assertFalse($helper->isCancelledStatus(null));
    }

    public function testNullStatusIsNeverCancelled(): void
    {
        self::assertFalse($this->buildHelper(5)->isCancelledStatus(null));
    }

    // --- наявність причини --------------------------------------------------

    /** @dataProvider hasReasonProvider */
    #[DataProvider('hasReasonProvider')]
    public function testOrderHasReasonWhenEitherFieldIsFilled(array $order, bool $expected): void
    {
        self::assertSame($expected, $this->buildHelper()->hasCancellationReason((object) $order));
    }

    public static function hasReasonProvider(): array
    {
        return [
            'обрана зі списку'   => [['cancellation_reason_id' => 3], true],
            'вписана текстом'    => [['cancellation_reason_text' => 'передумав'], true],
            'обидві'             => [['cancellation_reason_id' => 3, 'cancellation_reason_text' => 'т'], true],
            'нічого'             => [[], false],
            'порожні значення'   => [['cancellation_reason_id' => 0, 'cancellation_reason_text' => ''], false],
            'самі пробіли'       => [['cancellation_reason_text' => '   '], false],
        ];
    }

    // --- пошук запису історії -----------------------------------------------

    /**
     * Береться ОСТАННІЙ перехід у скасований статус, а не перший: замовлення
     * могли скасувати, повернути в роботу й скасувати вдруге — причина
     * чіпляється саме до останнього скасування.
     */
    public function testLastTransitionIntoCancelledStatusWins(): void
    {
        $history = [
            (object) ['id' => 10, 'new_status_id' => 5],
            (object) ['id' => 11, 'new_status_id' => 2],
            (object) ['id' => 12, 'new_status_id' => 5],
            (object) ['id' => 13, 'new_status_id' => 3],
        ];

        self::assertSame(12, $this->buildHelper()->getLastCancelledHistoryIdFromItems($history, 5));
    }

    public function testNoMatchingHistoryGivesNull(): void
    {
        $history = [(object) ['id' => 10, 'new_status_id' => 2]];

        self::assertNull($this->buildHelper()->getLastCancelledHistoryIdFromItems($history, 5));
        self::assertNull($this->buildHelper()->getLastCancelledHistoryIdFromItems([], 5));
    }

    /** Без налаштованого статусу історія навіть не переглядається. */
    public function testHistoryScanIsSkippedWithoutAConfiguredStatus(): void
    {
        $history = [(object) ['id' => 10, 'new_status_id' => 5]];

        self::assertNull($this->buildHelper()->getLastCancelledHistoryIdFromItems($history, null));
    }

    /** Записи з порожнім новим статусом ігноруються, а не рахуються за нуль. */
    public function testHistoryItemsWithoutNewStatusAreIgnored(): void
    {
        $history = [
            (object) ['id' => 10, 'new_status_id' => 5],
            (object) ['id' => 11, 'new_status_id' => null],
            (object) ['id' => 12, 'new_status_id' => 0],
        ];

        self::assertSame(10, $this->buildHelper()->getLastCancelledHistoryIdFromItems($history, 5));
    }

    // --- текст для менеджера ------------------------------------------------

    /** Без обраної причини показується вписаний вручну текст. */
    public function testFreeTextIsShownWhenNoReasonIsSelected(): void
    {
        $order = (object) ['cancellation_reason_id' => null, 'cancellation_reason_text' => 'клієнт передумав'];

        self::assertSame('клієнт передумав', $this->buildHelper()->getCancellationReasonDisplayText($order));
    }

    /** Видалена зі словника причина відкочується на текст, а не показує порожнечу. */
    public function testDeletedReasonFallsBackToFreeText(): void
    {
        $helper = $this->buildHelper(5, null);
        $order = (object) ['cancellation_reason_id' => 99, 'cancellation_reason_text' => 'запасний текст'];

        self::assertSame('запасний текст', $helper->getCancellationReasonDisplayText($order));
    }

    public function testSelectedReasonNameIsShown(): void
    {
        $helper = $this->buildHelper(5, (object) ['id' => 3, 'name' => 'Немає в наявності', 'is_other' => 0]);
        $order = (object) ['cancellation_reason_id' => 3, 'cancellation_reason_text' => 'ігнорується'];

        self::assertSame('Немає в наявності', $helper->getCancellationReasonDisplayText($order));
    }

    /**
     * Причина «Інше» існує саме щоб показати вписаний текст. Якщо менеджер
     * обрав «Інше», але нічого не вписав — показується сама назва причини.
     */
    public function testOtherReasonPrefersTheFreeTextButFallsBackToItsOwnName(): void
    {
        $other = (object) ['id' => 9, 'name' => 'Інше', 'is_other' => 1];

        $withText = (object) ['cancellation_reason_id' => 9, 'cancellation_reason_text' => '  свій варіант '];
        self::assertSame('свій варіант', $this->buildHelper(5, $other)->getCancellationReasonDisplayText($withText));

        $withoutText = (object) ['cancellation_reason_id' => 9, 'cancellation_reason_text' => '   '];
        self::assertSame('Інше', $this->buildHelper(5, $other)->getCancellationReasonDisplayText($withoutText));
    }
}
