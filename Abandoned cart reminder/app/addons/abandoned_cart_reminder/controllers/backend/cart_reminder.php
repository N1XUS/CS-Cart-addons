<?php
    
use Tygh\Registry;
use Tygh\Session;
use Tygh\Mailer;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
if ($mode == 'send') {
    $cron_pass = Registry::get('addons.abandoned_cart_reminder.cron_password');
    if ($cron_pass != $_REQUEST['cron_password']) {
        exit;
    }
    
    $item_types = fn_get_cart_content_item_types();
    
    $params = array(
        'with_info_only' => 'Y',
         'period' => 'C',
        'time_from' => time() - SECONDS_IN_DAY * Registry::get('addons.abandoned_cart_reminder.abandoned_max_days'),
        'time_to' => time() - SECONDS_IN_DAY * Registry::get('addons.abandoned_cart_reminder.abandoned_min_days')
    );
    
    list($carts_list, $search) = fn_acr_get_carts($params);
    
    if (!empty($carts_list)) {
        foreach($carts_list as $k => $v) {
            $cart_products = db_get_array(
                "SELECT ?:user_session_products.item_id, ?:user_session_products.item_type, ?:user_session_products.product_id, ?:user_session_products.amount, ?:user_session_products.price, ?:user_session_products.extra, ?:product_descriptions.product, ?:products.status"
                . " FROM ?:user_session_products"
                . " LEFT JOIN ?:product_descriptions ON ?:user_session_products.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s"
                . " LEFT JOIN ?:products ON ?:user_session_products.product_id = ?:products.product_id"
                . " WHERE ?:user_session_products.user_id = ?i AND ?:user_session_products.type = 'C' AND ?:user_session_products.item_type IN (?a) AND ?:products.status != ?s",
                DESCR_SL, $v['user_id'], $item_types, 'D'
            );
            if (!empty($cart_products)) {
                foreach ($cart_products as $key => $product) {
                    $exist = db_get_field("SELECT product_id FROM ?:products WHERE product_id = ?i", $product['product_id']);
                    if (!$exist) {
                        unset($cart_products[$key]);
                        continue;
                    }
                    $cart_products[$key]['extra'] = unserialize($product['extra']);
                }
            }
            
            $user_info = db_get_row("SELECT email, firstname, lastname FROM ?:users WHERE user_id = ?i", $v['user_id']);
            $carts_list[$k]['user_info'] = $user_info;
            
            $carts_list[$k]['cart_products'] = $cart_products;
        }
        
        foreach($carts_list as $v) {
         
            Mailer::sendMail(array(
                'to' => $v['user_info']['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'cart' => $v,
                ),
                'tpl' => 'addons/abandoned_cart_reminder/cart_reminder.tpl',
                'company_id' => $v['company_id'],
            ), 'A', CART_LANGUAGE);
        }
        
    }
    
    exit;
}

function fn_acr_get_carts($params, $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('carts', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:user_session_products.user_id',
        '?:users.firstname',
        '?:users.lastname',
        '?:user_session_products.timestamp AS date',
    );

    // Define sort fields
    $sortings = array (
        'customer' => "CONCAT(?:users.lastname, ?:users.firstname)",
        'date' => "?:user_session_products.timestamp",
    );

    if (fn_allowed_for('ULTIMATE')) {
        $sortings['company_id'] = "?:user_session_products.company_id";
    }

    $sorting = db_sort($params, $sortings, 'customer', 'asc');

    $condition = $join = '';

    $group = " GROUP BY ?:user_session_products.user_id";
    $group_post = '';

    if (!empty($params['with_info_only'])) {
        $condition .= db_quote(" AND ?:users.email != ''");
    }


    if (!empty($params['period']) && $params['period'] != 'A') {
        $condition .= db_quote(" AND (?:user_session_products.timestamp >= ?i AND ?:user_session_products.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $_condition = array();

    if (!empty($_condition)) {
        $condition .= " AND (" . implode(" OR ", $_condition).")";
    }
    
    $join .= " LEFT JOIN ?:users ON ?:user_session_products.user_id = ?:users.user_id";

    // checking types for retrieving from the database
    $type_restrictions = array('C');
    fn_set_hook('get_carts', $type_restrictions, $params, $condition, $join, $fields, $group, $array_index_field);

    if (!empty($type_restrictions) && is_array($type_restrictions)) {
        $condition .= " AND ?:user_session_products.type IN ('" . implode("', '", $type_restrictions) . "')";
    }

    $carts_list = array();

    $group .= $group_post;

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    if (fn_allowed_for('ULTIMATE')) {
        $group = " GROUP BY ?:user_session_products.user_id, ?:user_session_products.company_id";
    }

    $carts_list = db_get_array("SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $fields) . " FROM ?:user_session_products $join WHERE 1 $condition $group $sorting $limit");

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_found_rows();
    }

    unset($_SESSION['abandoned_carts']);

    return array($carts_list, $params);
}