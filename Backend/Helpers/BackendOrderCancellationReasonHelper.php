<?php

namespace Okay\Modules\Sviat\OrderCancellationReason\Backend\Helpers;

use Okay\Core\EntityFactory;
use Okay\Core\Settings;
use Okay\Modules\Sviat\OrderCancellationReason\Entities\OrderCancellationReasonEntity;

class BackendOrderCancellationReasonHelper
{
    public const SETTING_CANCELLED_STATUS_ID = 'sviat__order_cancellation_reason__cancelled_status_id';

    /** @var EntityFactory */
    private $entityFactory;
    /** @var Settings */
    private $settings;

    public function __construct(EntityFactory $entityFactory, Settings $settings)
    {
        $this->entityFactory = $entityFactory;
        $this->settings = $settings;
    }

    public function getCancelledStatusId(): ?int
    {
        $id = $this->settings->get(self::SETTING_CANCELLED_STATUS_ID);
        return $id !== null && $id !== '' ? (int) $id : null;
    }

    public function getReasons(): array
    {
        /** @var OrderCancellationReasonEntity $entity */
        $entity = $this->entityFactory->get(OrderCancellationReasonEntity::class);
        return $entity->mappedBy('id')->find();
    }

    public function hasCancellationReason($order): bool
    {
        return !empty($order->cancellation_reason_id)
            || trim((string) ($order->cancellation_reason_text ?? '')) !== '';
    }

    public function isCancelledStatus(?int $statusId): bool
    {
        if ($statusId === null) {
            return false;
        }
        $cancelledId = $this->getCancelledStatusId();
        return $cancelledId !== null && (int) $statusId === $cancelledId;
    }

    public function getOtherReasonId(): ?int
    {
        /** @var OrderCancellationReasonEntity $entity */
        $entity = $this->entityFactory->get(OrderCancellationReasonEntity::class);
        $reason = $entity->findOne(['is_other' => 1]);
        return $reason ? (int) $reason->id : null;
    }

    public function getLastCancelledHistoryIdFromItems(iterable $historyItems, ?int $cancelledStatusId): ?int
    {
        if ($cancelledStatusId === null) {
            return null;
        }
        $lastId = null;
        foreach ($historyItems as $item) {
            if (!empty($item->new_status_id) && (int) $item->new_status_id === $cancelledStatusId) {
                $lastId = $item->id;
            }
        }
        return $lastId;
    }

    public function getCancellationReasonDisplayText($order): string
    {
        if (empty($order->cancellation_reason_id)) {
            return (string) ($order->cancellation_reason_text ?? '');
        }
        /** @var OrderCancellationReasonEntity $entity */
        $entity = $this->entityFactory->get(OrderCancellationReasonEntity::class);
        $reason = $entity->findOne(['id' => (int) $order->cancellation_reason_id]);
        if (!$reason) {
            return (string) ($order->cancellation_reason_text ?? '');
        }
        if (!empty($reason->is_other)) {
            $text = trim((string) ($order->cancellation_reason_text ?? ''));
            return $text !== '' ? $text : (string) ($reason->name ?? '');
        }
        return (string) ($reason->name ?? '');
    }
}
