{include file="common/letter_header.tpl"}
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="main-table" style="height: 100%; background-color: #f4f6f8; font-size: 12px; font-family: Arial;">
    <tr>
        <td align="center" style="width: 100%; height: 100%;">
            <table cellpadding="0" cellspacing="0" border="0" style=" width: 602px; table-layout: fixed; margin: 24px 0 24px 0;">
                <tr>
                    <td style="background-color: #ffffff; border: 1px solid #e6e6e6; margin: 0px auto 0px auto; padding: 0px 44px 0px 46px; text-align: left;">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding: 27px 0px 0px 0px; border-bottom: 1px solid #868686; margin-bottom: 8px;">
                            <tr>
                                <td align="left" style="padding-bottom: 3px;" valign="middle"><img src="{$logos.mail.image.image_path}" width="{$logos.mail.image.image_x}" height="{$logos.mail.image.image_y}" border="0" alt="{$logos.mail.image.alt}" /></td>
                                <td width="100%" valign="middle" style="text-align: right; font: normal 12px Arial; margin: 0px; color: #999999;">{__("addons.abandoned_cart_reminder.you_have_incompleted_orders")}</td>
                            </tr>
                        </table>
                        {hook name="abandoned_cart_reminder:remind_text"}
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr valign="top">
                                <td style="width: 100%; padding: 14px 0 20px; font-size: 13px; text-align: center; font-family: Arial;">
                                    {__("addons.abandoned_cart_reminder.remind_text")}
                                </td>
                            </tr>
                        </table>
                        {/hook}
                        {hook name="abandoned_cart_reminder:products_list"}
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <th width="20%" style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">&nbsp;</th>
                                <th width="70%" style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("product")}</th>
                                <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("quantity")}</th>
                                <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("unit_price")}</th>
                                {if $order_info.use_discount}
                                    <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("discount")}</th>
                                {/if}
                                {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                                    <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("tax")}</th>
                                {/if}
                                <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap; font-size: 12px; font-family: Arial;">{__("subtotal")}</th>
                            </tr>
                            {foreach from=$cart.cart_products item="oi"}
                                {if !$oi.extra.parent}
                                <tr>
                                    <td style="padding: 5px 10px; background-color: #ffffff; font-size: 12px; font-family: Arial;">
                                        {include file="addons/abandoned_cart_reminder/common/image.tpl" href="{"products.view?product_id={$oi.product_id}"|fn_url:"C":"current"}" image=$oi.extra.main_pair image_width="150" image_height="150"}
                                    </td>
                                    <td style="padding: 5px 10px; background-color: #ffffff; font-size: 12px; font-family: Arial;">
                                        <a style="text-decoration: none; color: #1abc9c" href="{"products.view?product_id={$oi.product_id}"|fn_url:"C":"current"}">{$oi.product|default:__("deleted_product") nofilter}</a>
                                        {hook name="orders:product_info"}
                                        {if $oi.product_code}<p style="margin: 2px 0px 3px 0px;">{__("sku")}: {$oi.extra.product_code}</p>{/if}
                                        {/hook}
                                        {if $oi.product_options}<br/>{include file="common/options_info.tpl" product_options=$oi.extra.product_options}{/if}
                                    </td>
                                    <td style="padding: 5px 10px; background-color: #ffffff; text-align: center; font-size: 12px; font-family: Arial;">{$oi.extra.amount}</td>
                                    <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$oi.price}{/if}</td>
                                    {if $order_info.use_discount}
                                    <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $oi.extra.discount|floatval}{include file="common/price.tpl" value=$oi.extra.discount}{else}&nbsp;-&nbsp;{/if}</td>
                                    {/if}
                                    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                                        <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; font-size: 12px; font-family: Arial;">{if $oi.extra.tax_value}{include file="common/price.tpl" value=$oi.tax_value}{else}&nbsp;-&nbsp;{/if}</td>
                                    {/if}
                        
                                    <td style="padding: 5px 10px; background-color: #ffffff; text-align: right; white-space: nowrap; font-size: 12px; font-family: Arial;"><b>{if $oi.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=($oi.price * $oi.amount)}{/if}</b>&nbsp;</td>
                                </tr>
                                {/if}                                
                            {/foreach}
                        </table>
                        {/hook}
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td width="100%" style="padding: 10px 0; text-align: center;">
                                    <a style="display: inline-block; padding: 10px 20px; background: #EA621F; color: #fff; text-decoration: none; font-family: Arial; font-size: 16px; text-transform: uppercase;" href="{""|fn_url:"C":"current"}">{__("proceed_to_checkout")}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{include file="common/letter_footer.tpl"}