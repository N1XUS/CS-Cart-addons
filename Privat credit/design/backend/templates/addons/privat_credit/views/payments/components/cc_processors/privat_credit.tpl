<div class="control-group">
    <label class="control-label cm-required" for="privat_credit_identifier">{__("addons.privat_credit.identifier")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][identifier]" id="privat_credit_identifier" value="{$processor_params.identifier}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="privat_credit_password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="privat_credit_password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="privat_credit_parts_count">{__("addons.privat_credit.parts_count")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][parts_count]" id="privat_credit_parts_count" value="{$processor_params.parts_count|default:2}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="page_type">{__("type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][page_type]" id="page_type">
            <option value="II" {if $processor_params.page_type == "II"}selected="selected"{/if}>{__("addons.privat_credit.instant_installment")}</option>
            <option value="PP" {if $processor_params.page_type == "PP"}selected="selected"{/if}>{__("addons.privat_credit.partial_payments")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="980" {if $processor_params.currency == "980"}selected="selected"{/if}>{__("addons.privat_credit.uah")}</option>
            <option value="643" {if $processor_params.currency == "643"}selected="selected"{/if}>{__("addons.privat_credit.rub")}</option>
            <option value="840" {if $processor_params.currency == "840"}selected="selected"{/if}>{__("addons.privat_credit.usd")}</option>
        </select>
    </div>
</div>
{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

<div class="control-group">
    <label class="control-label" for="order_success">{__("addons.privat_credit.success")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][status][success]" id="order_success">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if $processor_params.status.success == $k || !$processor_params.status.success && $k == 'O'} selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_wait">{__("addons.privat_credit.wait")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][status][wait]" id="order_wait">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if $processor_params.status.wait == $k || !$processor_params.status.wait && $k == 'B'} selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>