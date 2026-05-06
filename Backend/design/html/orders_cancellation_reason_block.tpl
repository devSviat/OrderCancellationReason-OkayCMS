{* Поля причини скасування для масової зміни статусу у списку замовлень *}
{if $order_cancellation_cancelled_status_id && $order_cancellation_reasons}
<div class="fn_orders_cancellation_reason_fields">
    <select name="change_cancellation_reason_id" class="form-control selectpicker fn_change_cancellation_reason_select" style="min-width: 180px;">
        <option value="0">{$btr->sviat__order_cancellation_reason__select_reason_placeholder|escape}</option>
        {foreach $order_cancellation_reasons as $r}
            <option value="{$r->id|escape}" data-is-other="{if $r->is_other}1{else}0{/if}">{$r->name|escape}</option>
        {/foreach}
    </select>
    <div class="fn_change_cancellation_reason_text_wrap mt-h" style="display: none;">
        <input type="text" name="change_cancellation_reason_text" class="form-control mt-q" placeholder="{$btr->sviat__order_cancellation_reason__other_reason_placeholder|escape}" style="min-width: 200px;">
    </div>
</div>
{/if}
