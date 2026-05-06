<?php

namespace Okay\Modules\Sviat\OrderCancellationReason\Entities;

use Okay\Core\Entity\Entity;

class OrderCancellationReasonEntity extends Entity
{
    protected static $fields = [
        'id',
        'position',
        'is_other',
    ];

    protected static $langFields = [
        'name',
    ];

    protected static $defaultOrderFields = [
        'position ASC',
    ];

    protected static $table = 'sviat__order_cancellation_reasons';
    protected static $tableAlias = 'ocr';
    protected static $langTable = 'sviat__order_cancellation_reasons';
    protected static $langObject = 'order_cancellation_reason';
}
