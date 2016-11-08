<?php
    
use Tygh\Registry;
use Tygh\Session;
use Tygh\Http;

if (!defined('BOOTSTRAP')) {
    
    if (!empty($_REQUEST['order_id'])) {
        require '../../../payments/init_payment.php';
        
        fn_put_contents(Registry::get('config.dir.var') . 'resp_server.json', json_encode($_REQUEST, JSON_PRETTY_PRINT));
        
        $result = fn_liqpay_process_liqpay_response($_REQUEST['order_id'], $_REQUEST['data'], $_REQUEST['signature'], true);
        
        if ($result == false) {
            header(' ', true, 403);
        }
    }
    exit;
}

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'result') {
        
        fn_put_contents(Registry::get('config.dir.var') . 'resp_client.json', json_encode($_REQUEST, JSON_PRETTY_PRINT));
        
        $result = fn_liqpay_process_liqpay_response($_REQUEST['order_id'], $_REQUEST['data'], $_REQUEST['signature'], false);
        
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }
    
} else {
    $params = $processor_data['processor_params'];
    
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    
    $submit_url = 'https://www.liqpay.com/api/checkout';
    
    if (empty($params['pay_way'])) {
        $params['pay_way'] = 'card';
    }
    
    $pay_way = implode(',', $params['pay_way']);
    
    $language = 'en';
    
    $liqpay_languages = array('en', 'ru');
    
    if (in_array(CART_LANGUAGE, $liqpay_languages)) {
        $language = CART_LANGUAGE;
    } else if (CART_LANGUAGE == 'uk') {
        $language = 'ru';
    }
    
    $_post_data = array(
        'version' => 3,
        'public_key' => $params['public_key'],
        'amount' => $order_info["total"],
        'currency' => $params['currency'],
        'description' => __("addons.liqpay.pay_order") . ' ' . $_order_id,
        'order_id' => $_order_id,
        'server_url' => Registry::get('config.current_location') . '/app/addons/liqpay/payments/liqpay.php?order_id=' . $_order_id,
        'result_url' => fn_url("payment_notification.result?payment=liqpay&order_id=$order_id", AREA, 'current'),
        'type' => 'buy',
        'language' => $language,
        'pay_way' => $pay_way
    );
    
    if ($params['mode'] == 'test') {
        $_post_data['sandbox'] = 1;
    }
    
    if ($params['page_type'] == 'checkout') {
        $submit_url = 'https://www.liqpay.com/api/3/checkout';
        $_post_data['action'] = 'pay';
    }
    
    $_post_data = base64_encode(json_encode($_post_data));

    $_post_signature = base64_encode(sha1($params['private_key'] . $_post_data . $params['private_key'], 1));
    
    $post_data = array(
        'data' => $_post_data,
        'signature' => $_post_signature
    );
    
    fn_create_payment_form($submit_url, $post_data, 'LiqPay');
}