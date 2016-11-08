<?php
    
use Tygh\Registry;
use Tygh\Session;

$cart = & $_SESSION['cart'];

if ($mode == 'checkout') {
    $params = array();
    
    $params['usergroup_ids'] = $_SESSION['auth']['usergroup_ids'];
    
    if (isset($cart['chosen_shipping'])) {
        $params['shipping_ids'] = $cart['chosen_shipping'];
    }
    
    if ($cart['products']) {
        foreach($cart['products'] as $k => $v) {
            $params['product_ids'][] = $v['product_id'];
            $_category_ids = db_get_array("SELECT category_id FROM ?:products_categories WHERE product_id = ?i", $v['product_id']);
            if (!empty($_category_ids)) {
                foreach($_category_ids as $cat) {
                    $params['category_ids'][] = $cat['category_id'];
                }
            }
            if (!empty($params['category_ids'])) {
                $params['category_ids'] = array_unique($params['category_ids']);
            }
        }
    }
    
    list($services, ) = fn_get_additional_services($params);
    
    if (!empty($cart['additional_services'])) {
        foreach($cart['additional_services'] as $k => $v) {
            if (!$services[$k]) {
                unset($cart['additional_services'][$k]);
            }
        }
    }
    
    Tygh::$app['view']->assign('additional_services', $services);
    Tygh::$app['view']->assign('services_display_type', Registry::get('addons.additional_services.display_type'));
        
    if (isset($_REQUEST['service_ids'])) {
        unset($cart['additional_services']);
        unset($cart['additional_services_total']);
        if (!fn_is_empty($_REQUEST['service_ids'])) {
            foreach((array) $_REQUEST['service_ids'] as $v) {
                $cart['additional_services_total'] += $services[$v]['price'];
                $cart['additional_services'][$v] = array(
                    'name' => $services[$v]['name'],
                    'price' => $services[$v]['price']
                );
            }
        }
    }
    
    Tygh::$app['view']->assign('cart', $cart);
    
}