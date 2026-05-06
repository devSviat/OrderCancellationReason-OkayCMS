<?php

namespace Okay\Modules\Sviat\OrderCancellationReason\Extenders;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Entities\OrdersEntity;
use Okay\Modules\Sviat\OrderCancellationReason\Backend\Helpers\BackendOrderCancellationReasonHelper;

class BackendExtender implements ExtensionInterface
{
    private $helper;
    private $design;
    private $request;
    private $entityFactory;

    public function __construct(
        BackendOrderCancellationReasonHelper $helper,
        Design $design,
        Request $request,
        EntityFactory $entityFactory
    ) {
        $this->helper = $helper;
        $this->design = $design;
        $this->request = $request;
        $this->entityFactory = $entityFactory;
    }

    public function findOrder($order, $orderId)
    {
        if ($order && !empty($order->id)) {
            $this->design->assign('order_cancellation_cancelled_status_id', $this->helper->getCancelledStatusId());
            if ($this->helper->hasCancellationReason($order)) {
                $this->design->assign('order_cancellation_reason_display', $this->helper->getCancellationReasonDisplayText($order));
            }
        }
        return $order;
    }

    public function findOrders($orders, $filter)
    {
        $cancelledId = $this->helper->getCancelledStatusId();
        $this->design->assign('order_cancellation_cancelled_status_id', $cancelledId);
        if (!is_array($orders)) {
            return $orders;
        }
        foreach ($orders as $order) {
            if ($this->helper->hasCancellationReason($order)) {
                $order->cancellation_reason_display = $this->helper->getCancellationReasonDisplayText($order);
            }
        }
        return $orders;
    }

    public function getHistory($orderHistory, $orderId)
    {
        $cancelledId = $this->helper->getCancelledStatusId();
        if (is_array($orderHistory)) {
            $lastId = $this->helper->getLastCancelledHistoryIdFromItems($orderHistory, $cancelledId);
            if ($lastId !== null) {
                $this->design->assign('order_cancellation_last_cancelled_history_id', $lastId);
            }
        }
        return $orderHistory;
    }

    public function findOrdersHistory($ordersHistory, array $ordersIds)
    {
        $cancelledId = $this->helper->getCancelledStatusId();
        if ($cancelledId === null || !is_array($ordersHistory)) {
            return $ordersHistory;
        }
        $lastCancelledById = [];
        foreach ($ordersHistory as $orderId => $items) {
            $lastId = $this->helper->getLastCancelledHistoryIdFromItems($items, $cancelledId);
            if ($lastId !== null) {
                $lastCancelledById[$orderId] = $lastId;
            }
        }
        if (!empty($lastCancelledById)) {
            $this->design->assign('order_cancellation_last_cancelled_history_ids', $lastCancelledById);
        }
        return $ordersHistory;
    }

    public function changeStatus($output, array $ids): void
    {
        $newStatusId = (int) $this->request->post('change_status_id');
        if (!$this->helper->isCancelledStatus($newStatusId) || empty($ids)) {
            return;
        }
        $reasonId = (int) $this->request->post('change_cancellation_reason_id');
        $reasonText = trim((string) $this->request->post('change_cancellation_reason_text'));
        /** @var OrdersEntity $ordersEntity */
        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
        foreach ($ids as $id) {
            $ordersEntity->update((int) $id, [
                'cancellation_reason_id' => $reasonId ?: null,
                'cancellation_reason_text' => $reasonText ?: null,
            ]);
        }
    }

    public function updateOrderStatus($result, $order, int $newStatusId)
    {
        if (!$result || !$order || empty($order->id)) {
            return $result;
        }
        if (!$this->helper->isCancelledStatus($newStatusId)) {
            return $result;
        }
        $reasonId = (int) $this->request->post('cancellation_reason_id');
        $reasonText = trim((string) $this->request->post('cancellation_reason_text'));
        /** @var OrdersEntity $ordersEntity */
        $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
        $ordersEntity->update($order->id, [
            'cancellation_reason_id' => $reasonId ?: null,
            'cancellation_reason_text' => $reasonText ?: null,
        ]);
        return $result;
    }
}
