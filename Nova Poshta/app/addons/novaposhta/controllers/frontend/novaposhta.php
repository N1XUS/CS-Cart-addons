<?php
    
use Tygh\Registry;

if ($mode == 'autocomplete_city') {
    
    $params = $_REQUEST;
    
    if (defined('AJAX_REQUEST') && $params['q']) {
    
        $field = (CART_LANGUAGE == 'uk') ? 'city_name_ua' : 'city_name';
        
        $search = trim($params['q']) . "%";
        
        $cities = db_get_array("SELECT city_id, region_id, $field FROM ?:novaposhta_cities WHERE city_name LIKE ?l OR city_name_ua LIKE ?l LIMIT ?i", $search, $search, 10);
        
        if (!empty($cities)) {
            foreach ($cities as $city) {
                
                $state = fn_get_state_name($city['region_id'], 'UA');
                
                $select[] = array(
                    'code' => $city['city_id'],
                    'value' => $city[$field],
                    'label' => $city[$field] . ' - ' . $state,
                    'region_id' => $city['region_id'],
                    'country' => 'UA'
                );
            }
        }
        
        Registry::get('ajax')->assign('autocomplete', $select);
    
    }
    exit;
}