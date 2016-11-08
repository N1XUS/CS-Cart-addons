<?php
    
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'send_requests') {
        $order_ids = $_REQUEST['order_ids'];
        
        $params = array(
            'order_id' => $order_ids
        );
        
        list($orders, $search, $totals) = fn_get_orders($params, 0, true);
        
        fn_review_request_send_request($orders);
    }
}

if ($mode == 'send_request') {
    $order_id = $_REQUEST['order_id'];
    
    $params = array(
        'order_id' => $order_id
    );
    
    list($orders, $search, $totals) = fn_get_orders($params, 0, true);
    
    fn_review_request_send_request($orders);
    
}