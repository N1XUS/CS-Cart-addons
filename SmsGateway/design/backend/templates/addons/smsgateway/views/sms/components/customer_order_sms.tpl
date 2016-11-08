{__("addons.smsgateway.dear")} {$order.firstname} {$order.lastname}, {__("addons.smsgateway.your_order")} #{$order_id} {__("addons.smsgateway.sms_for_the_sum")} {include file="common/price.tpl" value=$total} {__("addons.smsgateway.is")} {$order_status_name}
);
