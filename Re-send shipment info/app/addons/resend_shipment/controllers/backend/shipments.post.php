<?php
    
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Mailer;
    
if ($mode == 'resend') {
    
    $shipment_ids = $_REQUEST['shipment_ids'];
    
    if (!empty($shipment_ids)) {
        foreach($shipment_ids as $v) {
            
            list($shipment_data, $search) = fn_get_shipments_info(array('shipment_id' => $v, 'advanced_info' => true));
            
            $shipment_data = array_pop($shipment_data);
            
            $order_info = fn_get_order_info($shipment_data['order_id'], false, true, true);
            $use_shipments = (Settings::instance()->getValue('use_shipments', '', $order_info['company_id']) == 'Y') ? true : false;
    
            if (!$use_shipments && empty($shipment_data['tracking_number']) && empty($shipment_data['tracking_number'])) {
                continue;
            }
            
            $shipment = array(
                'shipment_id' => $v,
                'timestamp' => $shipment_data['shipment_timestamp'],
                'shipping' => $shipment_data['shipping'],
                'tracking_number' => $shipment_data['tracking_number'],
                'carrier' => $shipment_data['carrier'],
                'comments' => $shipment_data['comments'],
                'items' => $shipment_data['products'],
            );
            
            Mailer::sendMail(array(
                'to' => $order_info['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'shipment' => $shipment,
                    'order_info' => $order_info,
                ),
                'tpl' => 'shipments/shipment_products.tpl',
                'company_id' => $order_info['company_id'],
            ), 'C', $order_info['lang_code']);            
        }
    }
}