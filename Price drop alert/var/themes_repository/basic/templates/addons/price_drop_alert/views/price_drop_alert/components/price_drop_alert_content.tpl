
<div id="{$id}">

<form name="price_drop_form" id="form_{$id}" action="{""|fn_url}" method="post" class="cm-ajax">
<input type="hidden" name="result_ids" value="{$id}" />
<input type="hidden" name="return_url" value="{$config.current_url}" />

{if $product}
    <input type="hidden" name="subscribe_data[product_id]" value="{$product.product_id}" />
    <div class="ty-cr-product-info-container">
        <div class="ty-cr-product-info-image">
            {include file="common/image.tpl" images=$product.main_pair image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
        </div>
        <div class="ty-cr-product-info-header">
            <h3 class="ty-product-block-title">{$product.product}</h3>
        </div>
    </div>
{/if}

<div class="ty-control-group">
    <label for="subscribe_data_{$id}_when_to_send" class="ty-control-group__title">{__("addons.price_drop_alert.when_to_send")}:</label>
    <input type="hidden" name="subscribe_data[anytime]" value="N" />
    <input type="checkbox" name="subscribe_data[anytime]" value="Y" id="subscribe_data_{$id}_when_to_send"{if $pda_subscribe_data.anytime == "Y"} checked="checked"{/if}>&nbsp;{__("addons.price_drop_alert.anytime_price_drops")}
</div>

<div class="ty-control-group">
    <label for="subscribe_data_{$id}_if_price_below" class="ty-control-group__title">{__("addons.price_drop_alert.if_price_below")}:</label>
    {assign var="active_currency" value=$currencies.$secondary_currency}
    {if $active_currency.after == "N"}{$active_currency.symbol}&nbsp;{/if}
    {if $addons.price_drop_alert.default_price_percent > 0}
        {math equation="x - (x* (y/100))" x=$product.price y=$addons.price_drop_alert.default_price_percent assign="default_price_drop_original"}
        {assign var="default_price_drop" value=$active_currency.currency_code|fn_pda_convert_price_up:$default_price_drop_original}
    {else}
        {assign var="default_price_drop" value=""}
    {/if}
    <input type="text" id="subscribe_data_{$id}_if_price_below" class="ty-input-text-short" value="{$pda_subscribe_data.target_price|default:$default_price_drop}" name="subscribe_data[target_price]" />
    {if $active_currency.after == "Y"}&nbsp;{$currencies.$secondary_currency.symbol}{/if}
</div>

<div class="ty-control-group">
    <label for="subscribe_data_{$id}_email" class="ty-control-group__title cm-email cm-required">{__("email")}</label>
    <input id="subscribe_data_{$id}_email" class="ty-input-text-full" size="50" type="text" name="subscribe_data[email]" value="{$pda_subscribe_data.email|default:$user_info.email|default:""}" />
</div>

{include file="common/image_verification.tpl" option="price_drop_alert"}

<div class="buttons-container">
    {include file="buttons/button.tpl" but_name="dispatch[price_drop_alert.subscribe]" but_text=__("submit") but_role="submit" but_meta="ty-btn__primary ty-btn__big cm-form-dialog-closer ty-btn"}
</div>

</form>

<!--{$id}--></div>
