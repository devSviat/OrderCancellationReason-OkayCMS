{$meta_title = $btr->sviat__order_cancellation_reason__admin_title scope=global}

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="heading_page">{$btr->sviat__order_cancellation_reason__admin_title|escape}</div>
    </div>
</div>

{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert--center alert--icon alert--success">
                <div class="alert__content">
                    <div class="alert__title">{$btr->general_settings_saved|escape}</div>
                </div>
            </div>
        </div>
    </div>
{/if}

<div class="row">
    <div class="col-lg-5 col-md-12 pr-0">
        <div class="boxed fn_toggle_wrap">
            <div class="heading_box">{$btr->sviat__order_cancellation_reason__cancelled_status|escape}</div>
            <div class="toggle_body_wrap on fn_card">
                <form method="post" class="fn_form_list">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">
                    <p class="text_grey mb-1">{$btr->sviat__order_cancellation_reason__cancelled_status_hint|escape}</p>
                    <select name="cancelled_status_id" class="form-control selectpicker">
                        <option value="0">{$btr->sviat__order_cancellation_reason__select_status|escape}</option>
                        {foreach $order_statuses as $os}
                            <option value="{$os->id|escape}" {if $cancelled_status_id == $os->id}selected{/if}>{$os->name|escape}</option>
                        {/foreach}
                    </select>
                    <button type="submit" class="btn btn_small btn_blue mt-1">
                        {include file='svg_icon.tpl' svgId='checked'}
                        <span>{$btr->general_apply|escape}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7 col-md-12">
        <div class="boxed fn_toggle_wrap">
            <div class="heading_box">{$btr->sviat__order_cancellation_reason__reasons_list|escape}</div>
            <div class="toggle_body_wrap on fn_card">
                <form method="post" class="fn_form_list">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">
                    <input type="hidden" name="cancelled_status_id" value="{$cancelled_status_id|escape}">
                    <p class="text_grey mb-1">{$btr->sviat__order_cancellation_reason__reasons_list_hint|escape}</p>
                    <div class="okay_list">
                        <div class="okay_list_head">
                            <div class="okay_list_boding okay_list_drag"></div>
                            <div class="okay_list_heading okay_list_order_cancellation_name">{$btr->sviat__order_cancellation_reason__reason_name|escape}</div>
                            <div class="okay_list_heading okay_list_order_cancellation_other" title="{$btr->sviat__order_cancellation_reason__is_other_hint|escape}">{$btr->sviat__order_cancellation_reason__is_other|escape}</div>
                            <div class="okay_list_heading okay_list_close"></div>
                        </div>
                        <div id="sortable_reasons" class="okay_list_body sortable">
                            {foreach $order_cancellation_reasons as $r}
                                <div class="fn_row okay_list_body_item fn_sort_item">
                                    <div class="okay_list_row">
                                        <input type="hidden" name="positions[{$r->id}]" value="{$r->position|escape}">
                                        <input type="hidden" name="reason_id[]" value="{$r->id|escape}">
                                        <div class="okay_list_boding okay_list_drag move_zone">
                                            {include file='svg_icon.tpl' svgId='drag_vertical'}
                                        </div>
                                        <div class="okay_list_boding okay_list_order_cancellation_name">
                                            <input type="text" name="reason_name[]" class="form-control" value="{$r->name|escape}" placeholder="{$btr->sviat__order_cancellation_reason__reason_name|escape}">
                                        </div>
                                        <div class="okay_list_boding okay_list_order_cancellation_other">
                                            <div class="okay_switch clearfix">
                                                <label class="switch switch-default">
                                                    <input class="switch-input" type="checkbox" name="reason_is_other_index[{$r@index}]" value="1" {if $r->is_other}checked{/if}/>
                                                    <span class="switch-label"></span>
                                                    <span class="switch-handle"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="okay_list_boding okay_list_close">
                                            {if !$r->is_other}
                                                <input type="checkbox" name="delete_reason[]" value="{$r->id|escape}" class="hidden_check_1" id="ocr_del_{$r->id}">
                                                <button data-hint="{$btr->general_delete|escape}" type="button"
                                                    class="btn_close fn_ocr_delete_btn hint-bottom-right-t-info-s-small-mobile hint-anim"
                                                    data-toggle="modal" data-target="#ocr_delete_modal" data-reason-id="{$r->id|escape}">
                                                    {include file='svg_icon.tpl' svgId='trash'}
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                            <div class="okay_list_body_item fn_new_reason">
                                <div class="okay_list_row">
                                    <input type="hidden" name="reason_id[]" value="0">
                                    <div class="okay_list_boding okay_list_drag"></div>
                                    <div class="okay_list_boding okay_list_order_cancellation_name">
                                        <input type="text" name="reason_name[]" class="form-control" value="" placeholder="{$btr->sviat__order_cancellation_reason__reason_name|escape}">
                                    </div>
                                    <div class="okay_list_boding okay_list_order_cancellation_other">
                                        <div class="okay_switch clearfix">
                                            <label class="switch switch-default">
                                                <input class="switch-input" type="checkbox" name="reason_is_other_index[{$order_cancellation_reasons|count}]" value="1"/>
                                                <span class="switch-label"></span>
                                                <span class="switch-handle"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="okay_list_boding okay_list_close"></div>
                                </div>
                            </div>
                        </div>
                        <div class="okay_list_footer">
                            <div class="okay_list_foot_left"></div>
                            <button type="submit" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{* Модальне підтвердження видалення причини *}
<div id="ocr_delete_modal" class="modal fade" role="document">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="card-header">
                <div class="heading_modal">{$btr->index_confirm|escape}</div>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn_small btn_blue fn_ocr_confirm_delete mx-h" data-dismiss="modal">
                    {include file='svg_icon.tpl' svgId='checked'}
                    <span>{$btr->index_yes|escape}</span>
                </button>
                <button type="button" class="btn btn_small btn-danger mx-h" data-dismiss="modal">
                    {include file='svg_icon.tpl' svgId='delete'}
                    <span>{$btr->index_no|escape}</span>
                </button>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
$(function() {
    var ocrDeleteBtn = null;
    $('form').on('submit', function() {
        $('#sortable_reasons .fn_sort_item').each(function(index) {
            $(this).find('input[name^="positions"]').val(index + 1);
        });
    });
    $(document).on('click', '.fn_ocr_delete_btn', function() {
        ocrDeleteBtn = $(this);
    });
    $(document).on('click', '.fn_ocr_confirm_delete', function() {
        if (ocrDeleteBtn && ocrDeleteBtn.length) {
            var $row = ocrDeleteBtn.closest('.fn_row');
            $row.find('input[name="delete_reason[]"]').prop('checked', true);
            ocrDeleteBtn.closest('form').submit();
        }
        ocrDeleteBtn = null;
    });
});
</script>
{/literal}
