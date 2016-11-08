<?php
    
use Tygh\Http;
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_privat_credit_install()
{
    fn_privat_credit_uninstall();

    $payments = fn_get_privat_credit_payment_methods();
    
    foreach($payments as $v) {
        db_query("INSERT INTO ?:payment_processors ?e", $v);
    }
}

function fn_privat_credit_uninstall()
{
    db_query("DELETE FROM ?:payment_processors WHERE addon = ?s", "privat_credit");
}

function fn_get_privat_credit_payment_methods() {
    return array(
        'privat_credit' => array(
            'processor' => 'Оплата Частями в Интернете через ПриватБанк',
            'processor_script' => 'privat_credit.php',
            'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
            'admin_template' => 'privat_credit.tpl',
            'callback' => 'Y',
            'type' => 'P',
            'addon' => 'privat_credit'            
        )
    );
}

function fn_privat_credit_generate_signature($parts) {
    $string = implode('', $parts);
    return base64_encode(hex2bin(sha1($string)));
}

function fn_privat_credit_send_request($url, $data) {
    $extra = array(
        'headers' => array(
            'Accept: application/json;',
            'Accept-Encoding: UTF-8;',
            'Content-Type: application/json;',
            'charset=UTF-8;'
        )
    );
    $data = json_encode($data);
    $result = Http::post($url, $data, $extra);
    return json_decode($result, true);
}

function fn_privat_credit_process_responce($order_id, $order_info, $status, $message = '', $params, $server = true) {
    static $wait_statuses = array('client_wait');
    static $success_statuses = array('created', 'success');
    static $pp_response = array();
    if (in_array($status, $success_statuses)) {
        $pp_response['order_status'] = $params['status']['success'];
    } elseif (in_array($status, $wait_statuses)) {
        $pp_response['order_status'] = $params['status']['wait'];
    } else {
        $pp_response['order_status'] = 'F';
        if (!empty($message)) {
            $pp_response['reason_text'] = $message;
        } else {
            $pp_response['reason_text'] = __('declined');
        }
    }
    
    if (fn_check_payment_script('privat_credit.php', $order_id)) {
        
        $valid_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);
        
        if ($valid_id) {
            fn_finish_payment($order_id, $pp_response);
        } else {
            fn_change_order_status($order_id, $pp_response['order_status']);
        }
    }
}