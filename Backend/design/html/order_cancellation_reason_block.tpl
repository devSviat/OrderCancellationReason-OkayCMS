{* Блок у картці замовлення.
   При зміні статусу на «Скасовано» відкриває модальне вікно з вибором причини.
*}
{if $order_cancellation_cancelled_status_id}
<span class="fn_order_cancellation_cancelled_status_id" data-id="{$order_cancellation_cancelled_status_id}" data-msg-required="{$btr->order_cancellation_reason_required|escape:'quotes'}" data-msg-other-required="{$btr->order_cancellation_reason_other_text_required|escape:'quotes'}" style="display:none;"></span>

<div id="fn_cancellation_reason_modal" class="ocr-modal" style="display: none;">
    <div class="ocr-modal-content">
        <span class="ocr-modal-close">{include file='svg_icon.tpl' svgId='delete'}</span>
        <div class="ocr-modal_heading">{$btr->sviat__order_cancellation_reason__select_reason|escape}</div>
        <div class="form-group">
            <div class="heading_label">{$btr->sviat__order_cancellation_reason__select_reason|escape}</div>
            <select name="cancellation_reason_id" class="form-control selectpicker fn_cancellation_reason_select">
                <option value="0">{$btr->sviat__order_cancellation_reason__select_reason_placeholder|escape}</option>
                {foreach $order_cancellation_reasons as $r}
                    <option value="{$r->id|escape}" data-is-other="{if $r->is_other}1{else}0{/if}" {if isset($order->cancellation_reason_id) && $order->cancellation_reason_id == $r->id}selected{/if}>{$r->name|escape}</option>
                {/foreach}
            </select>
        </div>
        <div class="fn_cancellation_reason_text_wrap form-group" style="display: none;">
            <div class="heading_label">{$btr->sviat__order_cancellation_reason__other_reason_text|escape}</div>
            <textarea name="cancellation_reason_text" class="form-control short_textarea" placeholder="{$btr->sviat__order_cancellation_reason__other_reason_placeholder|escape}">{$order->cancellation_reason_text|escape}</textarea>
        </div>
        <div class="mt-1">
            <button type="button" class="fn_cancellation_reason_modal_apply btn btn_small btn_blue">
                {include file='svg_icon.tpl' svgId='checked'}
                <span>{$btr->general_apply|escape}</span>
            </button>
        </div>
    </div>
</div>

<style>
.ocr-modal { position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); padding-top: 60px; }
.ocr-modal-content { background-color: #fff; margin: 5% auto; padding: 20px; border: 1px solid #ddd; width: 90%; max-width: 480px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: relative; }
.ocr-modal_heading { font-weight: 600; color: #333; font-size: 16px; margin-bottom: 15px; }
.ocr-modal-close { color: #aaa; position: absolute; right: 15px; top: 15px; height: 24px; width: 24px; cursor: pointer; display: block; }
.ocr-modal-close:hover, .ocr-modal-close:focus { color: #333; }
</style>

{literal}
<script>
$(function() {
    var $h = $(".fn_order_cancellation_cancelled_status_id");
    var cancelledId = $h.data("id");
    if (!cancelledId) return;
    var msgRequired = $h.data("msg-required");
    var msgOtherRequired = $h.data("msg-other-required");
    var $modal = $("#fn_cancellation_reason_modal");
    var $statusSelect = $("select[name=status_id]");

    function openModal() {
        $modal.show();
        if ($.fn.selectpicker && $modal.find(".selectpicker").length) {
            $modal.find(".selectpicker").selectpicker("refresh");
        }
    }

    function closeModal() {
        $modal.hide();
    }

    function getReasonSelectVal() {
        var $select = $modal.find("select[name=cancellation_reason_id]");
        if (!$select.length) return 0;
        var val;
        if ($.fn.selectpicker && $select.data("selectpicker")) {
            val = $select.selectpicker("val");
            val = Array.isArray(val) ? (val[0] || "0") : (val || "0");
        } else {
            val = $select.val() || "0";
        }
        return parseInt(val, 10);
    }

    function showError(message) {
        if (window.toastr) {
            toastr.error('', message);
        } else {
            alert(message);
        }
    }

    function validateModal() {
        var reasonId = getReasonSelectVal();
        if (!reasonId) {
            showError(msgRequired);
            $modal.find("select[name=cancellation_reason_id]").focus();
            return false;
        }
        var $select = $modal.find("select[name=cancellation_reason_id]");
        var opt = $select.find("option[value='" + reasonId + "']");
        var isOther = opt.length ? parseInt(opt.data("is-other"), 10) : 0;
        if (isOther === 1) {
            var text = String($modal.find("textarea[name=cancellation_reason_text]").val() || "").trim();
            if (!text) {
                showError(msgOtherRequired);
                $modal.find("textarea[name=cancellation_reason_text]").focus();
                return false;
            }
        }
        return true;
    }

    $statusSelect.on("change", function() {
        if ($(this).val() == cancelledId) {
            openModal();
        }
    });

    $modal.find(".ocr-modal-close").on("click", closeModal);
    $modal.on("click", function(e) {
        if (e.target === this) closeModal();
    });

    $modal.find(".fn_cancellation_reason_modal_apply").on("click", function() {
        if (!validateModal()) return;
        var reasonId = getReasonSelectVal();
        var $select = $modal.find("select[name=cancellation_reason_id]");
        $select.val(reasonId);
        if ($.fn.selectpicker && $select.data("selectpicker")) {
            $select.selectpicker("refresh");
        }
        closeModal();
    });

    $modal.find(".fn_cancellation_reason_select").on("change", function() {
        var isOther = $(this).find("option:selected").data("is-other");
        $modal.find(".fn_cancellation_reason_text_wrap").toggle(isOther == 1);
    });

    $("form.fn_fast_button").on("submit", function() {
        if ($statusSelect.val() != cancelledId) return true;
        if (!validateModal()) {
            openModal();
            return false;
        }
        return true;
    });
});
</script>
{/literal}
{/if}
