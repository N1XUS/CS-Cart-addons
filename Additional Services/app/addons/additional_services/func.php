<?php
    
function fn_get_additional_services($params = array(), $lang_code = DESCR_SL) {
    $default_params = array(
        'items_per_page' => 0,
    );
    
    $params = array_merge($default_params, $params);
    
    $condition = $limit = '';

    $condition = (AREA == 'A') ? '' : " AND ?:services.status = 'A' ";
    
    if (!empty($params['shipping_ids'])) {
        $condition .= " AND (" . fn_find_array_in_set($params['shipping_ids'], '?:services.shipping_ids', true) . ")";
    }
    
    if (!empty($params['usergroup_ids'])) {
        $condition .= " AND (" . fn_find_array_in_set($params['usergroup_ids'], '?:services.usergroup_ids', true) . ")";
    }
    if (!empty($params['product_ids'])) {
        $condition .= " AND (" . fn_find_array_in_set($params['product_ids'], '?:services.product_ids', true) . ")";
    }
    if (!empty($params['category_ids'])) {
        $condition .= " AND (" . fn_find_array_in_set($params['category_ids'], '?:services.category_ids', true) . ")";
    }
    
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(service_id) FROM ?:services WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page'], $params['total_items']);
    }
    
    $services = db_get_hash_array("SELECT * FROM ?:services LEFT JOIN ?:service_descriptions ON ?:services.service_id = ?:service_descriptions.service_id WHERE ?:service_descriptions.lang_code = ?s $condition ORDER BY ?:services.position ASC ?p", 'service_id', $lang_code, $limit);

    return array($services, $params);
}

function fn_update_additional_service($service_id, $data, $lang_code = DESCR_SL) {

    $data['service_id'] = $service_id;
    if (isset($data['usergroup_ids'])) {
        $data['usergroup_ids'] = (is_array($data['usergroup_ids'])) ? implode(',', $data['usergroup_ids']) : '';
    }
    if (isset($data['shipping_ids'])) {
        $data['shipping_ids'] = (is_array($data['shipping_ids'])) ? implode(',', $data['shipping_ids']) : '';
    }
    if ($service_id == false) {
        $service_id = db_query("INSERT INTO ?:services ?e", $data);
        $data['service_id'] = $service_id;
        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query('INSERT INTO ?:service_descriptions ?e', $data);
        }
    } else {
        db_query("UPDATE ?:services SET ?u WHERE service_id = ?i", $data, $service_id);
        $exist = db_get_field("SELECT service_id FROM ?:service_descriptions WHERE service_id = ?i AND lang_code = ?s", $service_id, $lang_code);
        if ($exist) {
            db_query("UPDATE ?:service_descriptions SET ?u WHERE service_id = ?i AND lang_code = ?s", $data, $service_id, $lang_code);
        } else {
            $data['service_id'] = $service_id;
            $data['lang_code'] = $lang_code;
            db_query("INSERT INTO ?:service_descriptions ?e", $data);
        }
    }
    return $service_id;
}

function fn_delete_service($service_id) {
    db_query("DELETE FROM ?:services WHERE service_id = ?i", $service_id);
    db_query("DELETE FROM ?:service_descriptions WHERE service_id = ?i", $service_id);
}

function fn_get_additional_service_data($service_id, $lang_code = DESCR_SL) {
    $condition = db_quote("?:services.service_id = ?i", $service_id);
    $condition .= (AREA != 'A') ? db_quote(" AND ?:services.status = ?s", 'A') : '';
    $service = db_get_row("SELECT * FROM ?:services LEFT JOIN ?:service_descriptions ON ?:services.service_id = ?:service_descriptions.service_id WHERE ?p", $condition);
    $service['shipping_ids'] = explode(',', $service['shipping_ids']);
    return $service;
}

function fn_additional_services_calculate_cart(&$cart, &$cart_products, &$auth) {
    if (!empty($cart['additional_services_total'])) {
        $cart['total'] += $cart['additional_services_total'];
    }
}

function fn_additional_services_get_shipping_methods() {
    return db_get_hash_single_array("SELECT a.shipping_id, b.shipping FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id=b.shipping_id AND b.lang_code = '" . CART_LANGUAGE . "' ORDER BY a.position", array('shipping_id', 'shipping'));
}

function fn_additional_services_create_order(&$order) {
   
    if (!empty($order['additional_services'])) {
        $services_data = array(
            'additional_services_total' => $order['additional_services_total'],
            'additional_services' => $order['additional_services']
        );
        $order['additional_services'] = serialize($services_data);
    }
}

function fn_additional_services_update_order(&$order) {
    if (!empty($order['additional_services'])) {
        $services_data = array(
            'additional_services_total' => $order['additional_services_total'],
            'additional_services' => $order['additional_services']
        );
        $order['additional_services'] = serialize($services_data);
    }
}

function fn_additional_services_get_order_info(&$order, &$additional_data) {
    if (!empty($order['additional_services'])) {
        $services_data = @unserialize($order['additional_services']);
        if (isset($services_data['additional_services_total'])) {
            $order['additional_services_total'] = $services_data['additional_services_total'];
        }
        if (isset($services_data['additional_services'])) {
            $order['additional_services'] = $services_data['additional_services'];
        }
    }
}