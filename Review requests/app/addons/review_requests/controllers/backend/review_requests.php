<?php
    
use Tygh\Registry;
use Tygh\Mailer;
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'send_request') {
    $cron_password = Registry::get('addons.review_requests.cron_password');
    if (!empty($_REQUEST['cron_password']) && $_REQUEST['cron_password'] == $cron_password) {
        
        $timestamp_to = time() - (Registry::get('addons.review_requests.send_request_after_days') * SECONDS_IN_DAY);
        
        $statuses = Registry::get('addons.review_requests.order_statuses');
        
        if (isset($statuses['N']) && empty($statuses['N'])) {
            die('No order statuses defined!');
        } else {
            $statuses = array_keys($statuses);
        }
        
        $params = array(
            'timestamp_to' => $timestamp_to,
            'status' => $statuses,
            'review_requested' => 'N'
        );
        
        list($orders, $search, $totals) = fn_get_orders($params, 0, true);
        
        fn_review_request_send_request($orders);
        
    } else {
        die('Access denied');
    }
    exit;
}