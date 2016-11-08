<div class="control-group">
    <label for="custom_sms_message_{$id}" class="control-label">{__("addons.turbosms_mv.custom_sms_message")}:</label>
    <div class="controls cm-no-hide-input" id="container_custom_sms_message_{$id}">
        <textarea id="email_header_{$id}" name="status_data[sms_body]" class="input-textarea-long" {if $disable_input}disabled="disabled"{/if}>{$status_data.sms_body}</textarea>
        <span class="help-block">{__("addons.turbosms_mv.sms_placeholders")}</span>
        {if "ULTIMATE"|fn_allowed_for}
            {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="`$id`_email_header" name="update_all_vendors[email_header]" hide_element="email_header_`$id`"}
        {/if}
    </div>
</div>