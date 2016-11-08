<?php
    
use Tygh\Registry;
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_review_requests_get_cron_info() {
    $cron_password = Registry::get('addons.review_requests.cron_password');
    $replacements = array(
        '[dir_root]' => Registry::get('config.dir.root') . '/' . Registry::get('config.admin_index'),
        '[cron_password]' => $cron_password,
        '[url_path]' => fn_url('review_requests.send_request?cron_password=' . $cron_password, 'A', 'current')
    );
    return __("addons.review_requests_cron_url", $replacements);    
}

function fn_review_requests_get_order_statuses() {
    return db_get_hash_single_array("SELECT a.status, b.description FROM ?:statuses AS a LEFT JOIN ?:status_descriptions AS b ON a.status = b.status AND b.lang_code = ?s WHERE a.type = ?s", array('status', 'description'), CART_LANGUAGE,'O');
}

function fn_review_request_send_request($orders = array()) {
    if (!empty($orders)) {
        foreach($orders as $order) {
            $order_info = fn_get_order_info($order['order_id'], false, true, true, true);
            
            if (!empty($order_info['products'])) {
                // Unset products that are deleted or have no ability to left comment for them
                foreach($order_info['products'] as $k => $product) {
                    if ($product['deleted_product'] == true) {
                        unset ($order_info['products'][$k]);
                    } else {
                        $allowed = db_get_field("SELECT thread_id FROM ?:discussion WHERE object_type = ?s AND object_id = ?i AND type IN(?n)", 'P', $product['product_id'], array('B', 'C', 'R'));
                        if ($allowed == false) {
                            unset ($order_info['products'][$k]);
                        }
                    }
                }
            }
            
            if (!empty($order_info['products'])) {
                
                fn_gather_additional_products_data($order_info['products'], array('get_icon' => true, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

                // If we have products that are available, send request to customers
                Mailer::sendMail(array(
                    'to' => $order_info['email'],
                    'from' => 'company_orders_department',
                    'data' => array(
                        'order_info' => $order_info
                    ),
                    'tpl' => 'addons/review_requests/request.tpl',
                    'company_id' => $order_info['company_id'],
                ), 'A', $order_info['lang_code']);
                db_query("UPDATE ?:orders SET review_requested = ?s WHERE order_id = ?i", 'Y', $order_info['order_id']);
            }
        }
    }    
}

/* HOOKS */

function fn_review_requests_get_orders(&$params, &$fields, &$sortings, &$condition, &$join, &$group) {
    $fields[] = '?:orders.review_requested';
    if (!empty($params['timestamp_to'])) {
        $condition .= db_quote(" AND ?:orders.timestamp <= ?i", $params['timestamp_to']);
    }
    if (isset($params['review_requested'])) {
        $condition .= db_quote(" AND ?:orders.review_requested = ?s", $params['review_requested']);
    }
}

/* PROMOTIONS */

function fn_commented_promo($promotion_id, $promotion, $product, $auth, $purchased = 'N')
{
    
    if (!empty($auth['user_id'])) {
        if ($purchased == 'Y') {
            $params = array(
                'user_id' => $auth['user_id'],
                'status' => fn_get_order_paid_statuses(),
                'p_ids' => $product['product_id']
            );
            list($orders, $search, $totals) = fn_get_orders($params);
            
            if (empty($orders)) {
                return 'N';
            }
        }
        
        $fields = array(
            '?:discussion_posts.post_id'
        );
        
        $fields = implode(', ', $fields);
        
        $joins = db_quote("LEFT JOIN ?:discussion ON ?:discussion_posts.thread_id = ?:discussion.thread_id");
        
        $condition = '';
        
        $condition .= db_quote("AND ?:discussion_posts.user_id = ?i", $auth['user_id']);
        
        $condition .= db_quote(" AND ?:discussion_posts.status = ?s", 'A');
        
        if ($purchased == 'Y') {
            
            $product_ids = [];
            
            foreach($orders as $order) {
                $order_info = fn_get_order_info($order['order_id']);
                if (!empty($order_info['products'])) {
                    foreach($order_info['products'] as $product) {
                        $product_ids[] = $product['product_id'];
                    }
                }
            }
            
            $product_ids = array_unique($product_ids);

            $condition .= db_quote(" AND ?:discussion.object_id IN(?n) AND ?:discussion.object_type = ?s", $product_ids, 'P');
        } else {
            $condition .= db_quote(" AND ?:discussion.object_type = ?s", 'P');
        }
        $post_id = db_get_field("SELECT ?p FROM ?:discussion_posts ?p WHERE 1 ?p", $fields, $joins, $condition);
    }

    return !empty($post_id) ? 'Y' : 'N';
}