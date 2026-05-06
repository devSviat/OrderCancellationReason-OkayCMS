<?php

namespace Okay\Modules\Sviat\OrderCancellationReason\Init;

use Okay\Admin\Helpers\BackendOrderHistoryHelper;
use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Core\ServiceLocator;
use Okay\Core\Settings;
use Okay\Entities\OrdersEntity;
use Okay\Modules\Sviat\OrderCancellationReason\Backend\Helpers\BackendOrderCancellationReasonHelper;
use Okay\Modules\Sviat\OrderCancellationReason\Entities\OrderCancellationReasonEntity;
use Okay\Modules\Sviat\OrderCancellationReason\Extenders\BackendExtender;

class Init extends AbstractInit
{
    public function install()
    {
        $this->setBackendMainController('OrderCancellationReasonsAdmin');

        $nameField = (new EntityField('name'))->setTypeVarchar(255)->setIsLang();
        $this->migrateEntityTable(OrderCancellationReasonEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            $nameField,
            (new EntityField('position'))->setTypeInt(11, false)->setDefault(0),
            (new EntityField('is_other'))->setTypeTinyInt(1, false)->setDefault(0),
        ]);
        $this->migrateEntityField(OrderCancellationReasonEntity::class, $nameField);
        $this->registerEntityLangInfo(OrderCancellationReasonEntity::class, 'sviat__order_cancellation_reasons', 'order_cancellation_reason');

        $this->migrateEntityField(
            OrdersEntity::class,
            (new EntityField('cancellation_reason_id'))->setTypeInt(11, true)
        );
        $this->migrateEntityField(
            OrdersEntity::class,
            (new EntityField('cancellation_reason_text'))->setTypeText()->setNullable()
        );

        $this->createDefaultReasons();
    }

    private function createDefaultReasons(): void
    {
        $entityFactory = ServiceLocator::getInstance()->getService(EntityFactory::class);
        $entity = $entityFactory->get(OrderCancellationReasonEntity::class);
        if ($entity->count() > 0) {
            return;
        }
        $reason = (object)[
            'position' => 0,
            'is_other' => 1,
            'name' => 'Інша причина',
        ];
        $entity->add($reason);
    }

    public function init()
    {
        $this->registerEntityField(OrdersEntity::class, 'cancellation_reason_id');
        $this->registerEntityField(OrdersEntity::class, 'cancellation_reason_text');

        $this->registerBackendController('OrderCancellationReasonsAdmin');
        $this->addBackendControllerPermission('OrderCancellationReasonsAdmin', 'orders');

        $this->extendBackendMenu('left_orders', [
            'left_order_cancellation_reasons' => ['OrderCancellationReasonsAdmin'],
        ]);

        $this->registerChainExtension(
            [BackendOrdersHelper::class, 'findOrder'],
            [BackendExtender::class, 'findOrder']
        );
        $this->registerChainExtension(
            [BackendOrdersHelper::class, 'findOrders'],
            [BackendExtender::class, 'findOrders']
        );
        $this->registerChainExtension(
            [BackendOrderHistoryHelper::class, 'getHistory'],
            [BackendExtender::class, 'getHistory']
        );
        $this->registerChainExtension(
            [BackendOrderHistoryHelper::class, 'findOrdersHistory'],
            [BackendExtender::class, 'findOrdersHistory']
        );
        $this->registerChainExtension(
            [BackendOrdersHelper::class, 'updateOrderStatus'],
            [BackendExtender::class, 'updateOrderStatus']
        );
        $this->registerChainExtension(
            [BackendOrdersHelper::class, 'changeStatus'],
            [BackendExtender::class, 'changeStatus']
        );

        $this->addBackendBlock(
            'order_additional_info',
            'order_cancellation_reason_block.tpl',
            function (Design $design, EntityFactory $entityFactory, Settings $settings): void {
                $helper = new BackendOrderCancellationReasonHelper($entityFactory, $settings);
                $design->assign('order_cancellation_reasons', $helper->getReasons());
                $design->assign('order_cancellation_cancelled_status_id', $helper->getCancelledStatusId());
            }
        );
    }
}
