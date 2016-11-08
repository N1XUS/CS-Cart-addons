<?php
    
use Tygh\Http;
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {
    
    if ($mode == 'result' || $mode == 'process') {
        $order_id = $_REQUEST['order_id'];
        
        $_order_id = (strpos($order_id, '_')) ? substr($order_id, 0, strpos($order_id, '_')) : $order_id;
        $_order_id = (strpos($_order_id, '-')) ? substr($_order_id, 0, strpos($_order_id, '-')) : $_order_id;
        
        $order_info = fn_get_order_info($_order_id);
        $processor_data = fn_get_payment_method_data($order_info['payment_id']);
        $params = $processor_data['processor_params'];        
    }
    
    if ($mode == 'result') {
        
        $url = 'https://payparts2.privatbank.ua/ipp/v2/payment/state';

        $signature_pieces = array($params['password'], $params['identifier'], $order_id, $params['password']);
        
        $post_data = array(
            'storeId' => $params['identifier'],
            'orderId' => $order_id,
            'signature' => fn_privat_credit_generate_signature($signature_pieces)
        );
        
        $result = fn_privat_credit_send_request($url, $post_data);
        
        $status = strtolower($result['paymentState']);
        
        $result_signature = fn_privat_credit_generate_signature(array(
            $params['password'],
            $result['state'],
            $result['storeId'],
            $result['orderId'],
            $result['paymentState'],
            $result['message'],
            $params['password']
        ));
        
        if ($result_signature == $result['signature']) {
            $_result = fn_privat_credit_process_responce($_order_id, $order_info, $status, $result['message'], $params, false);
            fn_order_placement_routines('route', $_order_id);            
        }
        
    } elseif ($mode == 'process') {
        $result = file_get_contents("php://input");
        $result = json_decode($result, true);
        
        $status = strtolower($result['paymentState']);
        
        $result_signature = fn_privat_credit_generate_signature(array(
            $params['password'],
            $result['storeId'],
            $result['orderId'],
            $result['paymentState'],
            $result['message'],
            $params['password']
        ));
        
        if ($result_signature == $result['signature']) {
            fn_privat_credit_process_responce($_order_id, $order_info, $status, $result['message'], $params, true);
        }
        exit;
    }
} else {
    static $order_products = array();
    static $signature = '';
    static $product_string = array();
    $params = $processor_data['processor_params'];
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $_order_id = $_order_id . '-' . time();
    $price = 0;
    static $submit_url = 'https://payparts2.privatbank.ua/ipp/v2/payment/create';
    
    // We can't use products list because of discounts that could be applied. Use order info instead
    
    $product_info = array(
        'name' => __("order") . ' #' . $order_info['order_id'],
        'count' => 1,
        'price' => $order_info['total']        
    );
    
    $order_products = $product_info;
    $product_info['price'] = round($product_info['price'] * 100, 0);
    $product_string = implode($product_info);
    
    $response_url = fn_url("payment_notification.process?payment=privat_credit&order_id=$_order_id", 'C', 'current');
    $redirect_url = fn_url("payment_notification.result?payment=privat_credit&order_id=$_order_id", AREA, 'current');
    
    $signature_pieces = array(
        $params['password'],
        $params['identifier'],
        $_order_id,
        $product_info['price'],
        $params['currency'],
        $params['parts_count'],
        $params['page_type'],
        $response_url,
        $redirect_url,
        $product_string,
        $params['password']
    );
    
    $signature = fn_privat_credit_generate_signature($signature_pieces);
    
    $data = array(
        'storeId' => $params['identifier'],
        'orderId' => $_order_id,
        'amount' => $order_info['total'],
        'currency' => $params['currency'],
        'partsCount' => $params['parts_count'],
        'merchantType' => $params['page_type'],
        'products' => array($order_products),
        'responseUrl' => $response_url,
        'redirectUrl' => $redirect_url,
        'signature' => $signature
    );

    $token = fn_privat_credit_send_request($submit_url, $data);
    
    // Check signature of response
    $response_signature = fn_privat_credit_generate_signature(array(
        $params['password'],
        $token['state'],
        $token['storeId'],
        $token['orderId'],
        $token['token'],
        $params['password']
    ));
    
    $status = strtolower($token['state']);
    
    if ($response_signature == $token['signature']) {
        if ($status == 'success') {
            $url = 'https://payparts2.privatbank.ua/ipp/v2/payment?token=' . $token['token'];
            fn_create_payment_form($url, array(), __("addons.privat_credit_form_page_" . strtolower($params['page_type'])), true, 'get');
            exit;
        }
    }
    return false;
    
    exit;
}