<?php

use Tygh\Registry;

$cart = & $_SESSION['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'place_order') {
        list($services, ) = fn_get_additional_services();
        if (isset($_REQUEST['service_ids'])) {
            unset($cart['additional_services']);
            unset($cart['additional_services_total']);
            if (!empty($_REQUEST['service_ids'])) {
                foreach((array) $_REQUEST['service_ids'] as $v) {
                    $cart['additional_services_total'] += $services[$v]['price'];
                    $cart['additional_services'][$v] = array(
                        'name' => $services[$v]['name'],
                        'price' => $services[$v]['price']
                    );
                }
            }
        }
    }
}

if ($mode == 'update') {
    list($services, ) = fn_get_additional_services();
    Tygh::$app['view']->assign('additional_services', $services);    
}