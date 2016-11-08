<div class="control-group">
    <label class="control-label" for="liqpay_public_key">{__("addons.liqpay.public_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][public_key]" id="liqpay_public_key" value="{$processor_params.public_key}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="liqpay_private_key">{__("addons.liqpay.private_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][private_key]" id="liqpay_private_key" value="{$processor_params.private_key}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="page_type">{__("page_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][page_type]" id="page_type">
            <option value="liqnbuy" {if $processor_params.page_type == "liqnbuy"}selected="selected"{/if}>{__("addons.liqpay.liqnbuy")}</option>
            <option value="checkout" {if $processor_params.page_type == "checkout"}selected="selected"{/if}>{__("addons.liqpay.checkout")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pay_way">{__("available_payment_methods")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][pay_way][]" multiple="multiple" id="pay_way">
            <option value="card" {if in_array("card", $processor_params.pay_way)}selected="selected"{/if}>{__("addons.liqpay.card")}</option>
            <option value="liqpay" {if in_array("liqpay", $processor_params.pay_way)}selected="selected"{/if}>{__("addons.liqpay.liqpay")}</option>
            <option value="delayed" {if in_array("delayed", $processor_params.pay_way)}selected="selected"{/if}>{__("addons.liqpay.delayed")}</option>
            <option value="invoice" {if in_array("invoice", $processor_params.pay_way)}selected="selected"{/if}>{__("addons.liqpay.invoice")}</option>
            <option value="privat24" {if in_array("privat24", $processor_params.pay_way)}selected="selected"{/if}>{__("addons.liqpay.privat24")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="UAH" {if $processor_params.currency == "UAH"}selected="selected"{/if}>{__("addons.liqpay.uah")}</option>
            <option value="RUB" {if $processor_params.currency == "RUB"}selected="selected"{/if}>{__("addons.liqpay.rub")}</option>
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