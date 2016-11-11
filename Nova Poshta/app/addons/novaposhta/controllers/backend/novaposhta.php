<?php
    
use Tygh\Registry;
use Tygh\Http;

set_time_limit(60000);

$api_key = Registry::get('addons.novaposhta.api_key');

if (empty($api_key)) {
    fn_set_notification('E', __('error'), __('addons.novaposhta.api_key_not_specified'));
    return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']);
}
    
if ($mode == 'renew_cities') {
    $password = Registry::get('addons.novaposhta.cron_password');
    if ($password != $_REQUEST['cron_password']) {
        exit;
    }
    $rdata = array(
        'apiKey' => Registry::get('addons.novaposhta.api_key'),
        'modelName' => 'Address',
        'calledMethod' => 'getCities'
    );
    $rdata = json_encode($rdata);
    
    $response = Http::post(NP_API_URL, $rdata);
    $response = json_decode($response, true);
    
    if ($response['success'] == true) {
    
        $time = time() + SECONDS_IN_DAY * Registry::get('addons.novaposhta.cache_livetime');
        
        db_query("TRUNCATE ?:novaposhta_cities");
    
        foreach($response['data'] as $k => $v) {
            
            $v['DescriptionRu'] = (mb_strpos($v['DescriptionRu'], '(') !== false) ? mb_substr($v['DescriptionRu'], 0, mb_strpos($v['DescriptionRu'], '(')) : $v['DescriptionRu'];
            $v['Description'] = (mb_strpos($v['Description'], '(') !== false) ? mb_substr($v['Description'], 0, mb_strpos($v['Description'], '(')) : $v['Description'];
            
            $db_data = array(
                'city_id' => $v['Ref'],
                'region_id' => $v['Area'],
                'city_name' => $v['DescriptionRu'],
                'city_name_ua' => $v['Description'],
                'timestamp' => $time
            );
            db_query("REPLACE INTO ?:novaposhta_cities ?e", $db_data);
        }
    }
    return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']);
}

if ($mode == 'renew_regions') {
    
    $rdata = array(
        'apiKey' => Registry::get('addons.novaposhta.api_key'),
        'modelName' => 'Address',
        'calledMethod' => 'getAreas'
    );
    $rdata = json_encode($rdata);
    
    $response = Http::post(NP_API_URL, $rdata);
    $response = json_decode($response, true);
    
    if ($response['success'] == true) {
        
        $states = db_get_array("SELECT state_id FROM ?:states WHERE np = ?s", 'Y');
        
        if (!empty($states)) {
            foreach($states as $v) {
                db_query("DELETE FROM ?:states WHERE state_id = ?i", $v['state_id']);
                db_query("DELETE FROM ?:state_descriptions WHERE state_id = ?i", $v['state_id']);
            }
        }
    
        foreach($response['data'] as $k => $v) {
            $db_data = array(
                'country_code' => 'UA',
                'code' => $v['Ref'],
                'status' => 'A',
                'np' => 'Y'
            );
            
            $state_id = db_query("REPLACE INTO ?:states ?e", $db_data);
            
            $languages = Registry::get('languages');
            foreach($languages as $lang_code => $_v) {
                $descr_data = array(
                    'state_id' => $state_id,
                    'lang_code' => $lang_code,
                    'state' => $v['Description'],
                );
                db_query("INSERT INTO ?:state_descriptions ?e", $descr_data);
            }
        }
    }
    return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']);
}