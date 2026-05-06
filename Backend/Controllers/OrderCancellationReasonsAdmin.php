<?php

namespace Okay\Modules\Sviat\OrderCancellationReason\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\EntityFactory;
use Okay\Entities\OrderStatusEntity;
use Okay\Modules\Sviat\OrderCancellationReason\Backend\Helpers\BackendOrderCancellationReasonHelper;
use Okay\Modules\Sviat\OrderCancellationReason\Entities\OrderCancellationReasonEntity;

class OrderCancellationReasonsAdmin extends IndexAdmin
{
    public function fetch(
        EntityFactory $entityFactory,
        BackendOrderCancellationReasonHelper $helper
    ) {
        /** @var OrderCancellationReasonEntity $reasonEntity */
        $reasonEntity = $entityFactory->get(OrderCancellationReasonEntity::class);
        /** @var OrderStatusEntity $orderStatusEntity */
        $orderStatusEntity = $entityFactory->get(OrderStatusEntity::class);

        if ($this->request->method('post')) {
            $cancelledStatusId = (int) $this->request->post('cancelled_status_id');
            $this->settings->set(BackendOrderCancellationReasonHelper::SETTING_CANCELLED_STATUS_ID, $cancelledStatusId > 0 ? $cancelledStatusId : '');

            $deleteIds = $this->request->post('delete_reason');
            if (!is_array($deleteIds)) {
                $deleteIds = $deleteIds !== null && $deleteIds !== '' ? [(int) $deleteIds] : [];
            }
            $deletedIds = [];
            $candidateIds = array_filter(array_map('intval', $deleteIds));
            if (!empty($candidateIds)) {
                $reasons = $reasonEntity->find(['id' => $candidateIds]);
                $idsToDelete = [];
                foreach ($reasons as $r) {
                    if (empty($r->is_other)) {
                        $idsToDelete[] = $r->id;
                        $deletedIds[$r->id] = true;
                    }
                }
                if (!empty($idsToDelete)) {
                    $reasonEntity->delete($idsToDelete);
                }
            }

            $positions = $this->request->post('positions');
            if (is_array($positions)) {
                foreach ($positions as $id => $position) {
                    $id = (int) $id;
                    if ($id > 0 && empty($deletedIds[$id])) {
                        $reasonEntity->update($id, ['position' => (int) $position]);
                    }
                }
            }

            $reasonIds = $this->request->post('reason_id');
            $reasonNames = $this->request->post('reason_name');
            $reasonIsOtherIndex = $this->request->post('reason_is_other_index');
            if (!is_array($reasonIds)) {
                $reasonIds = $reasonIds !== null && $reasonIds !== '' ? [(int) $reasonIds] : [];
            }
            if (!is_array($reasonNames)) {
                $reasonNames = $reasonNames !== null && $reasonNames !== '' ? [(string) $reasonNames] : [];
            }
            if (!is_array($reasonIsOtherIndex)) {
                $reasonIsOtherIndex = [];
            }
            $maxPosition = is_array($positions) && !empty($positions) ? (int) max($positions) : 0;

            if (!empty($reasonIds)) {
                foreach ($reasonIds as $i => $id) {
                    $id = (int) $id;
                    if ($id > 0 && !empty($deletedIds[$id])) {
                        continue;
                    }
                    $name = isset($reasonNames[$i]) ? trim((string) $reasonNames[$i]) : '';
                    $isOther = !empty($reasonIsOtherIndex[$i]);
                    if ($id > 0) {
                        $reasonEntity->update($id, [
                            'name' => $name,
                            'is_other' => $isOther ? 1 : 0,
                        ]);
                    } elseif ($name !== '') {
                        $maxPosition++;
                        $newId = $reasonEntity->add((object)[
                            'position' => $maxPosition,
                            'is_other' => $isOther ? 1 : 0,
                        ]);
                        if ($newId) {
                            $reasonEntity->update($newId, ['name' => $name]);
                        }
                    }
                }
            }

            $this->design->assign('message_success', 'saved');
        }

        $reasons = $reasonEntity->find();
        $orderStatuses = $orderStatusEntity->mappedBy('id')->find();
        $cancelledStatusId = $helper->getCancelledStatusId();

        $this->design->assign('order_cancellation_reasons', $reasons);
        $this->design->assign('order_statuses', $orderStatuses);
        $this->design->assign('cancelled_status_id', $cancelledStatusId);
        $this->response->setContent($this->design->fetch('order_cancellation_reasons_admin.tpl'));
    }
}
