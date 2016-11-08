<div class="control-group">
    <label class="control-label" for="liqpay_merchant_id">{__("addons.liqpay.merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="liqpay_merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="liqpay_password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="liqpay_password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="UAH" {if $processor_params.currency == "UAH"}selected="selected"{/if}>{__("addons.liqpay.uah")}</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("addons.liqpay.usd")}</option>
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("addons.liqpay.eur")}</option>
        </select>
    </div>
</div>
{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

<div class="control-group">
    <label class="control-label" for="order_success">{__("addons.liqpay.success_status")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][status][success]" id="order_success">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if $processor_params.status.success == $k || !$processor_params.status.success && $k == 'O'} selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_wait">{__("addons.liqpay.wait")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][status][wait]" id="order_wait">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"{if $processor_params.status.wait == $k || !$processor_params.wait.success && $k == 'B'} selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>