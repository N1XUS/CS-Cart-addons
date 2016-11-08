<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_liqpay_install()
{
    fn_liqpay_uninstall();

    $payments = fn_get_liqpay_payment_methods();
    
    foreach($payments as $v) {
        db_query("INSERT INTO ?:payment_processors ?e", $v);
    }
}

function fn_liqpay_uninstall()
{
    db_query("DELETE FROM ?:payment_processors WHERE addon = ?s", "liqpay");
}

function fn_get_liqpay_payment_methods() {
    return array(
        'liqpay' => array(
            'processor' => 'LiqPay',
            'processor_script' => 'liqpay.php',
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template' => 'liqpay.tpl',
            'callback' => 'Y',
            'type' => 'P',
            'addon' => 'liqpay'            
        ),
        'privat24' => array(
            'processor' => 'Приват24',
            'processor_script' => 'privat24.php',
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template' => 'privat24.tpl',
            'callback' => 'Y',
            'type' => 'P',
            'addon' => 'liqpay'
        )
    );
}

function fn_liqpay_process_liqpay_response($order_id, $data, $signature, $server = false) {
    $success_statuses = array('success', 'sandbox', 'subscribed');
    $wait_statuses = array('wait_secure', 'wait_accept', 'wait_lc', 'processing', 'cash_wait');
    
    $order_id = $_REQUEST['order_id'];
    $order_id = (strpos($order_id, '_')) ? substr($order_id, 0, strpos($order_id, '_')) : $order_id;
    $order_info = fn_get_order_info($order_id);
    $processor_data = fn_get_payment_method_data($order_info['payment_id']);
    
    $params = $processor_data['processor_params'];
    
    $_data = $_REQUEST['data'];
    
    if (empty($_data) && $server == false) {
        $pp_response = array();
        if (fn_check_payment_script('liqpay.php', $order_id)) {
            if ($order_info['status'] == "N") {
                $pp_response['order_status'] = $params['status']['wait'];
                fn_change_order_status($order_id, $pp_response['order_status']);
            }
        }
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }
    
    $private_key = $params['private_key'];
    
    $sign = base64_encode(sha1($private_key . $_data . $private_key, 1));
    
    if ($sign == $_REQUEST['signature']) {
        $data = json_decode(base64_decode($_REQUEST['data']), true);
        
        $pp_response = array();

        if (in_array($data['status'], $success_statuses)) {
            $pp_response['order_status'] = $params['status']['success'];
        } elseif (in_array($data['status'], $wait_statuses)) {
            $pp_response['order_status'] = $params['status']['wait'];
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('declined');                
        }
        
        if (fn_check_payment_script('liqpay.php', $order_id)) {
            if ($order_info['status'] == "N") {
                fn_finish_payment($order_id, $pp_response);
            } else {
                fn_change_order_status($order_id, $pp_response['order_status']);
            }
        }
        return true;
    }
    return false;
}

function fn_liqpay_process_privat24_response($order_id, $data, $signature, $server = false) {
    
    $success_statuses = array('ok', 'test');
    $wait_statuses = array('wait');
    
    parse_str($data, $_data);
    
    $order_info = fn_get_order_info($order_id);
    $processor_data = fn_get_payment_method_data($order_info['payment_id']);
    
    $params = $processor_data['processor_params'];
    
    $password = $params['password'];
    
    $sign = sha1(md5($data . $password));

    if ($sign == $signature) {
        
        $pp_response = array();

        if (in_array($_data['state'], $success_statuses)) {
            $pp_response['order_status'] = $params['status']['success'];
        } elseif (in_array($_data['state'], $wait_statuses)) {
            $pp_response['order_status'] = $params['status']['wait'];
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('declined');                
        }
        
        if (fn_check_payment_script('privat24.php', $order_id)) {
            if ($server == true) {
                fn_change_order_status($order_id, $pp_response['order_status']);
            } else {
                fn_finish_payment($order_id, $pp_response);
            }
        }
        
        return true;
    }
    return false;
}