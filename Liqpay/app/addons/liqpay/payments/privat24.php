<?php
    
use Tygh\Registry;
use Tygh\Session;
use Tygh\Http;

if (!defined('BOOTSTRAP')) {
    
    if (!empty($_REQUEST['order_id']) && !empty($_REQUEST['payment'])) {
        
        require '../../../payments/init_payment.php';
        
        $order_id = $_REQUEST['order_id'];
        $order_id = (strpos($order_id, '_')) ? substr($order_id, 0, strpos($order_id, '_')) : $order_id;
        
        fn_liqpay_process_privat24_response($order_id, $_REQUEST['payment'], $_REQUEST['signature'], true);
        
    }
    exit;
}

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'result') {
        $order_id = $_REQUEST['order_id'];
        $order_id = (strpos($order_id, '_')) ? substr($order_id, 0, strpos($order_id, '_')) : $order_id;
        
        fn_liqpay_process_privat24_response($order_id, $_REQUEST['payment'], $_REQUEST['signature'], false);
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }
    
} else {
    $params = $processor_data['processor_params'];
    
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    
    $submit_url = 'https://api.privatbank.ua/p24api/ishop';
    
    $product_ids = array();
    
    foreach($order_info['products'] as $v) {
        $product_ids[] = $v['product_id'];
    }
    
    $product_ids = implode(', ', $product_ids);
    
    $_post_data = array(
        'amt' => $order_info['total'],
        'ccy' => $params['currency'],
        'details' => __("addons.liqpay.pay_order") . ' ' . $_order_id,
        'ext_details' => $product_ids,
        'pay_way' => 'privat24',
        'order' => $_order_id,
        'merchant' => $params['merchant_id'],
    );
    
    $signature = sha1(md5(urldecode(http_build_query($_post_data)) . $params['password']));
    
    $_post_data['return_url'] = fn_url("payment_notification.result?payment=privat24&order_id=$order_id", AREA, 'current');
    $_post_data['server_url'] = Registry::get('config.current_location') . '/app/addons/liqpay/payments/privat24.php?order_id=' . $_order_id;
    $_post_data['signature'] = $signature;
    
    fn_create_payment_form($submit_url, $_post_data, 'Приват24');
}