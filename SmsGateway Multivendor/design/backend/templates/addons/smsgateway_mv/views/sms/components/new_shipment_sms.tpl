{strip}
{__("addons.smsgateway_mv.new_shipment_sms", ["[order_id]" => "#{$order_id}", "[shipping_method]" => {$shipping_method}, "[tracking_number]" => $tracking_number])}.
{if $tracking_url}{$tracking_url nofilter}{/if}
{/strip}