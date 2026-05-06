<?php

namespace Okay\Modules\Sviat\OrderCancellationReason\Init;

use Okay\Core\EntityFactory;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Settings;
use Okay\Modules\Sviat\OrderCancellationReason\Backend\Controllers\OrderCancellationReasonsAdmin;
use Okay\Modules\Sviat\OrderCancellationReason\Backend\Helpers\BackendOrderCancellationReasonHelper;
use Okay\Modules\Sviat\OrderCancellationReason\Extenders\BackendExtender;

return [
    BackendOrderCancellationReasonHelper::class => [
        'class' => BackendOrderCancellationReasonHelper::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Settings::class),
        ],
    ],
    BackendExtender::class => [
        'class' => BackendExtender::class,
        'arguments' => [
            new SR(BackendOrderCancellationReasonHelper::class),
            new SR(\Okay\Core\Design::class),
            new SR(\Okay\Core\Request::class),
            new SR(EntityFactory::class),
        ],
    ],
    OrderCancellationReasonsAdmin::class => [
        'class' => OrderCancellationReasonsAdmin::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(BackendOrderCancellationReasonHelper::class),
        ],
    ],
];
