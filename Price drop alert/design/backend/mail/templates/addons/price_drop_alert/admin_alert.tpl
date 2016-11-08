{assign var="url" value="products.update?product_id=`$data.product_id`"|fn_url:"A":"current"}
{capture name="url"}<a href="{$url}">{$data.product}</a>{/capture}
{capture name="formatted_target_price"}{__("addons.price_drop_alert.target_price")}: {$data.target_price|format_price:$currencies[$data.currency_code] nofilter}{/capture}
<p>{__("addons.price_drop_alert.new_subscriber_body", ["[TARGET_PRICE]" => $smarty.capture.formatted_target_price, "[URL]" => $smarty.capture.url, "[EMAIL]" => $data.email])}</p>