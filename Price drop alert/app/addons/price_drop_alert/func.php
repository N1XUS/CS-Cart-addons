<?php

use Tygh\Registry;
use Tygh\Mailer;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_pda_notify_admin($data) {
    $data['product'] = fn_get_product_name($data['product_id']);
    $company_id = Registry::get('runtime.company_id');
    Mailer::sendMail(array(
        'to' => Registry::get('addons.price_drop_alert.admin_email'),
        'from' => 'company_support_department',
        'data' => array(
            'data' => $data
        ),
        'tpl' => 'addons/price_drop_alert/admin_alert.tpl',
        'company_id' => $company_id,
    ), 'A', CART_LANGUAGE);
}

function fn_pda_get_subscribers($params, $lang_code = CART_LANGUAGE) {
    
    $view_type = 'pda_subscribers';
    
    // Init filter
    $params = LastView::instance()->update($view_type, $params);
    
    $default_params = array(
        'items_per_page' => 0,
        'page' => 1,
    );
    
    $params = array_merge($default_params, $params);
        
    $sortings = array(
        'id' => '?:pda_subscribers.subscriber_id',
        'timestamp' => '?:pda_subscribers.timestamp',
        'email' => '?:pda_subscribers.email',
        'product' => '?:product_descriptions.product',
        'target_price' => '?:pda_subscribers.target_price',
        'status' => '?:pda_subscribers.status',
    );
    
    $condition = $limit = '';
    
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }
    
    $sorting = db_sort($params, $sortings, 'id', 'asc');
    
    $fields = array (
        '?:pda_subscribers.subscriber_id',
        '?:pda_subscribers.timestamp',
        '?:pda_subscribers.email',
        '?:pda_subscribers.product_id',
        '?:product_descriptions.product',
        '?:pda_subscribers.notification_type',
        '?:pda_subscribers.target_price',
        '?:pda_subscribers.status',
        '?:pda_subscribers.lang_code',
        '?:users.user_id'
    );
    
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:pda_subscribers.subscriber_id)) FROM ?:pda_subscribers WHERE 1 ?p ?p", $condition, $sorting);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    
    if (isset($params['email'])) {
        $condition .= db_quote(" AND ?:pda_subscribers.email = ?s", $params['email']);
    }
    
    if (isset($params['price_from'])) {
        $condition .= db_quote(" AND ?:pda_subscribers.target_price >= ?d", $params['price_from']);
    }
    
    if (isset($params['price_to'])) {
        $condition .= db_quote(" AND ?:pda_subscribers.target_price <= ?d", $params['price_to']);
    }
    
    if (isset($params['status'])) {
        $condition .= db_quote(" AND ?:pda_subscribers.status = ?s", $params['status']);
    }
    
    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:pda_subscribers.timestamp >= ?i AND ?:pda_subscribers.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }
    
    if (!empty($params['product_ids'])) {
        $arr = (strpos($params['product_ids'], ',') !== false || !is_array($params['product_ids'])) ? explode(',', $params['product_ids']) : $params['product_ids'];
        $condition .= db_quote(" AND ?:pda_subscribers.product_id IN (?n)", $arr);
    }
    
    $joins = '';
    
    $joins .= db_quote("LEFT JOIN ?:product_descriptions ON ?:pda_subscribers.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s", $lang_code);
    
    $joins .= db_quote("LEFT JOIN ?:users ON ?:pda_subscribers.email = ?:users.email");
    
    $subscribers = db_get_hash_array("SELECT ?p FROM ?:pda_subscribers ?p WHERE 1 ?p ?p ?p", 'subscriber_id', implode(", ", $fields), $joins, $condition, $sorting, $limit);
    
    LastView::instance()->processResults($view_type, $subscribers, $params);

    return array($subscribers, $params);
}

function fn_pda_get_subscriber_statistics($params, $lang_code = CART_LANGUAGE) {
    // Init filter
    
    $view_type = 'pda_subscriber_stats';
    
    $params = LastView::instance()->update($view_type, $params);
    
    $default_params = array(
        'items_per_page' => 0,
        'page' => 1,
    );
    
    $joins = $condition = $limit = '';
    
    $params = array_merge($default_params, $params);
    
    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }
    
    if (isset($params['status']) && !empty($params['status'])) {
        $condition .= db_quote(" AND ?:pda_subscribers.status = ?s", $params['status']);
    }
    
    if (!empty($params['product_ids'])) {
        $arr = (strpos($params['product_ids'], ',') !== false || !is_array($params['product_ids'])) ? explode(',', $params['product_ids']) : $params['product_ids'];
        $condition .= db_quote(" AND ?:pda_subscribers.product_id IN (?n)", $arr);
    }
    
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:pda_subscribers.product_id)) FROM ?:pda_subscribers WHERE 1", $condition);
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    
    $fields = array(
        '?:pda_subscribers.product_id',
        'MIN(?:pda_subscribers.target_price) AS min_price',
        'MAX(?:pda_subscribers.target_price) AS max_price',
        "COUNT(?:pda_subscribers.email) AS users",
        '?:product_descriptions.product',
    );
    
    $joins .= db_quote("LEFT JOIN ?:product_descriptions ON ?:pda_subscribers.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s", $lang_code);
    
    $subscribers = db_get_hash_array("SELECT ?p FROM ?:pda_subscribers ?p WHERE 1 ?p GROUP BY ?:pda_subscribers.product_id ?p", 'product_id', implode(", ", $fields), $joins, $condition, $limit);

    LastView::instance()->processResults($view_type, $subscribers, $params);
    
    return array($subscribers, $params);
}

function fn_pda_delete_subscriber($subscriber_id) {
    db_query("DELETE FROM ?:pda_subscribers WHERE subscriber_id = ?i", $subscriber_id);
}

/**
 * Gets list of subscription status filters
 *
 * @param string $filter current filter
 * @param boolean $add_hidden includes 'hiden' status filter
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return array filters list
 */
function fn_get_subscription_status_filters($lang_code = CART_LANGUAGE)
{
    $filters = array (
        'P' => __('addons.price_drop_alert.status_p', '', $lang_code),
        'Q' => __('addons.price_drop_alert.status_q', '', $lang_code),
        'N' => __('addons.price_drop_alert.status_n', '', $lang_code),
        'U' => __('addons.price_drop_alert.status_u', '', $lang_code),
    );

    return $filters;
}

/* HOOKS */

function fn_price_drop_alert_settings_variants_image_verification_use_for(&$objects)
{
    $objects['price_drop_alert'] = __('price_drop_alert.use_for_price_drop_alert');
}

function fn_price_drop_alert_update_product_post(&$product_data, &$product_id) {
    // Get subscribers for this product
    $subscribers = db_get_array("SELECT * FROM ?:pda_subscribers WHERE product_id = ?i AND ((notification_type = ?s AND status = ?s) OR (notification_type = ?s AND status != ?s))", $product_id, 'O', 'Q', 'A', 'P');
    foreach($subscribers as $subscriber) {
        // Check if user is registered
        if ($subscriber['notification_type'] == 'A' && $subscriber['last_price'] > 0 && $subscriber['last_price'] <= $product_data['price']) {
            continue; // Skip notification with old price
        }
        if ($product_data['price'] <= $subscriber['target_price']) {
            $user_info = db_get_row("SELECT user_id, firstname, lastname FROM ?:users WHERE email = ?s", $subscriber['email']);
            Mailer::sendMail(array(
                'to' => $subscriber['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'price' => $product_data['price'],
                    'currency_code' => $subscriber['currency_code'],
                    'product' => $product_data,
                    'product_id' => $product_id,
                    'product_image' => fn_get_image_pairs($product_id, 'product', 'M', true, true, $subscriber['lang_code']),
                    'subscriber' => $subscriber,
                    'user' => $user_info
                ),
                'tpl' => 'addons/price_drop_alert/alert.tpl',
                'company_id' => $product_data['company_id'],
            ), 'A', $subscriber['lang_code']);
            $db_data = array(
                'status' => 'N',
                'last_price' => $product_data['price']
            );
            db_query("UPDATE ?:pda_subscribers SET ?u WHERE product_id = ?i AND hash = ?s", $db_data, $product_id, $subscriber['hash']);
        }
    }
}

function fn_pda_convert_price_up($currency_code, $value) {
    $currencies = Registry::get('currencies');

    if (array_key_exists($currency_code, $currencies)) {
        if ($currencies[$currency_code]['is_primary'] != 'Y') {
            return fn_format_price($value / $currencies[$currency_code]['coefficient']);
        } else {
            return fn_format_price($value);
        }
    } else {
        return false;
    }

    return $price;
}

function fn_pda_convert_price_down($currency_code, $value) {
    $currencies = Registry::get('currencies');

    if (array_key_exists($currency_code, $currencies)) {
        if ($currencies[$currency_code]['is_primary'] != 'Y') {
            $price = fn_format_price($value * $currencies[$currency_code]['coefficient']);
        } else {
            return fn_format_price($value);
        }
    } else {
        return false;
    }

    return $price;
}