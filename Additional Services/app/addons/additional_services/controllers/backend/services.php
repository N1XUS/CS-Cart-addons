<?php
    
use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        $service_id = $_REQUEST['service_data']['service_id'];
        fn_update_additional_service($service_id, $_REQUEST['service_data']);
    }
    
    if ($mode == 'm_update') {
        if (!empty($_REQUEST['services_data'])) {
            foreach($_REQUEST['services_data'] as $service_id => $data) {
                fn_update_additional_service($service_id, $data);
            }
        }
    }
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['services_data'])) {
            foreach($_REQUEST['services_data'] as $service_id => $data) {
                fn_delete_service($service_id);
            }
        }
    }
    return array(CONTROLLER_STATUS_REDIRECT, "services.manage");
}
    
if ($mode == 'manage') {
    
    $params = array(
        'items_per_page' => Registry::get('settings.Appearance.admin_products_per_page')
    );
    
    list($services, ) = fn_get_additional_services($params);
    
    Tygh::$app['view']->assign('services', $services);
    
}

if ($mode == 'update') {
    $service = fn_get_additional_service_data($_REQUEST['service_id']);
    Tygh::$app['view']->assign('service', $service);
}

if ($mode == 'delete') {
    fn_delete_service($_REQUEST['service_id']);
}