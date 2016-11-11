<?php
    
use Tygh\Registry;
use Tygh\Http;
use Tygh\Languages\Languages;

function fn_novaposhta_get_cron_url_info() {
    $cron_password = Registry::get('addons.novaposhta.cron_password');
    $replacements = array(
        '[dir_root]' => Registry::get('config.dir.root') . '/' . Registry::get('config.admin_index'),
        '[cron_password]' => $cron_password,
        '[url_path]' => fn_url('novaposhta.renew_cities?cron_password=' . $cron_password, 'A', 'current')
    );
    return __("addons.novaposhta_cron_url", $replacements);    
}

function fn_novaposhta_create_order(&$order) {
    
    if (!empty($order['warehouse'])) {
        $order['warehouse'] = serialize(fn_novaposhta_get_warehouse_info($order['warehouse']));
    }
}

function fn_novaposhta_update_order(&$order) {
    if (!empty($order['warehouse'])) {
        $order['warehouse'] = serialize(fn_novaposhta_get_warehouse_info($order['warehouse']));
    }
}

function fn_novaposhta_get_order_info(&$order, &$additional_data) {
    
    if (!empty($order['warehouse'])) {
        $order['warehouse'] = @unserialize($order['warehouse']);
        
        $descr_field = (CART_LANGUAGE == 'uk') ? 'Description' : 'DescriptionRu';
        
        foreach($order['shipping'] as $k => $v) {
            if ($v['module'] == 'nova_poshta') {
                $order['shipping'][$k]['shipping'] = $v['shipping'] . '. ' . $order['warehouse'][$k][$descr_field];
            }
        }
    }
}

function fn_novaposhta_get_warehouse_info($warehouse_ids) {
    $warehouse_info = array();
    foreach((array) $warehouse_ids as $k => $v) {
        $v = trim($v);
        $data = db_get_field("SELECT data FROM ?:novaposhta_warehouses WHERE warehouse_id = ?s", $v);
        if (!empty($data)) {
            $warehouse_info[$k] = json_decode($data, true);
        }
    }
    return $warehouse_info;
}

function fn_novaposhta_get_shipments_info_post(&$shipments) {
    
    foreach($shipments as $k => $v) {
        if ($v['carrier'] == 'nova_poshta' && !empty($v['tracking_number'])) {
            $data = array(
                'apiKey' => Registry::get('addons.novaposhta.api_key'),
                'modelName' => 'InternetDocument',
                'calledMethod' => 'documentsTracking',
                'methodProperties' => array(
                    'Documents' => array($v['tracking_number'])
                )
            );
            $data = json_encode($data);
            $response = Http::post(NP_API_URL, $data);
            $response = json_decode($response, true);
            if ($response['success'] == true) {
                $shipments[$k]['shipment_status'] = $response['data'][0];
            }
        }
    }
}

function fn_novaposhta_install() {
    fn_novaposhta_uninstall();
    
    $service = fn_novaposhta_get_schema();
    
    $service_id = db_get_field('SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code = ?s', $service['module'], $service['code']);
    
    if (empty($service_id)) {
        $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);
        foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
            db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
        }   
    }
    
}

function fn_novaposhta_uninstall() {
    $service = fn_novaposhta_get_schema();
    
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', $service['module']);
    
    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }
}

function fn_novaposhta_get_schema() {
    $service = array(
        'status' => 'A',
        'module' => 'nova_poshta',
        'code' => 'nova_poshta',
        'sp_file' => '',
        'description' => 'Новая почта'
    );
    return $service;    
}

function fn_novaposhta_format_price_down($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        $price = fn_format_price($price * $currencies[$payment_currency]['coefficient']);
    } else {
        return false;
    }

    return $price;
}

function fn_novaposhta_format_price($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
    } else {
        return false;
    }

    return $price;
}