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
                                <td width="100%" valign="middle" style="text-align: right; font: normal 12px Arial; margin: 0px; color: #999999;">{__("addons.review_requests.request_heading")}</td>
                            </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr valign="top">
                                <td style="width: 100%; padding: 14px 0 20px; font-size: 13px; text-align: center; font-family: Arial;">
                                    {__("addons.review_requests.request_text", ["[NAME]" => "`$order_info.firstname` `$order_info.lastname`" ,"[ORDER_ID]" => $order_info.order_id])}
                                </td>
                            </tr>
                        </table>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            {foreach from=$order_info.products item="product"}
                            <tr>
                                <td style="padding: 5px 10px; background-color: #ffffff; font-size: 12px; font-family: Arial;">
                                    {include file="addons/review_requests/common/image.tpl" href="{"products.view?product_id={$product.product_id}"|fn_url:"C":"current"}" image=$product.main_pair image_width="150" image_height="150"}
                                </td>
                                <td style="padding: 5px 10px; background-color: #ffffff; font-size: 12px; font-family: Arial;">
                                    <a style="text-decoration: none; color: #1abc9c" href="{"products.view?product_id={$product.product_id}"|fn_url:"C":"current"}">{$product.product|default:__("deleted_product") nofilter}</a>
                                    {if $product.product_code}<p style="margin: 2px 0px 3px 0px;">{__("sku")}: {$product.product_code}</p>{/if}
                                </td>
                            </tr>
                            {/foreach}
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{include file="common/letter_footer.tpl"}