{* Показує причину лише біля останнього запису історії зі статусом «Скасовано» для цього замовлення *}
{assign var="ocr_last_id" value=$order_cancellation_last_cancelled_history_id|default:$order_cancellation_last_cancelled_history_ids[$order->id]|default:null}
{if $order_cancellation_cancelled_status_id && $history_item->new_status_id == $order_cancellation_cancelled_status_id && $history_item->id == $ocr_last_id}
    {assign var="ocr_display" value=$order->cancellation_reason_display|default:$order_cancellation_reason_display|default:''}
    {if $ocr_display}
        <div class="ocr-history-reason">
            <div class="boxed__content">
                <span class="text_500">{$btr->sviat__order_cancellation_reason__select_reason|escape}:</span>
                {$ocr_display|escape}
            </div>
        </div>
    {/if}
{/if}
