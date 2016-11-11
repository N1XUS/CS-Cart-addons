<?php
    
namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Registry;
use Tygh\Http;

/**
 * Nova Poshta shipping service
 */
class NovaPoshta implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    /**
     * Timeout requests to Russian Post
     *
     * @var integer $_timeout
     */
    private $_timeout = 5;

    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }
    
    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }
    
    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
    }
    
    /**
     * Gets shipping cost and information about possible errors
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return array  Shipping cost and errors
     */
    public function processResponse($response)
    {
        
        $response = json_decode($response, true);
        
        $return = array(
            'cost' => false,
            'error' => false,
            'delivery_time' => false
        );
        
        if (empty($this->_error_stack)) {
            $return['cost'] = $response['data'][0]['Cost'];
            if (CART_PRIMARY_CURRENCY != 'UAH') {
                $return['cost'] = fn_novaposhta_format_price_down($return['cost'], 'UAH');
            }
        } else {
            $return['error'] = $this->processErrors($response);
        }
        
        if ($return['cost'] == 0 || $response['success'] == false) {
            $return = array(
                'cost' => false,
                'error' => false,
                'delivery_time' => false
            );
            return $return;
        }

        // Get delivery date
        
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];
        $shipping_type = $this->_shipping_info['service_params']['shipping_type'];
        $cost = $this->_shipping_info['package_info']['C'];
        $default_volume = $this->_shipping_info['service_params']['default_volume'];
        
        $weight = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
        
        $data = array(
            'modelName' => 'InternetDocument',
            'calledMethod' => 'getDocumentDeliveryDate',
            'methodProperties' => array(
                'ServiceType' => $this->_shipping_info['service_params']['shipping_type'],
                'Weight' => $weight,
                'Cost' => $cost,
                'CitySender' => $this->_shipping_info['package_info']['origination']['city_id'],
                'CityRecipient' => $this->_shipping_info['package_info']['location']['city_id']
            )
        );
        
        $dtime = $this->GetNovaPoshtaData($data);
        
        if ($dtime == true) {
            $date = date_parse($dtime['data'][0]['DeliveryDate']['date']);
            $date = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
            
            $days = ceil(abs(time() - $date) / SECONDS_IN_DAY);
            
            if ($days > 1) {
                $days = $days . ' ' . __("days");
            } else {
                $days = $days . ' ' . __("day");
            }
            
            $return['delivery_time'] = $days;
        }
        
        return $return;
    }
    
    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        if (!empty($response['errors'])) {
            foreach($response['errors'] as $k => $v) {
                $this->_error_stack[] = $v;
                $errors[] = $v;
            }
        } else {
            $this->_error_stack[] = __('service_not_available');
        }
    }
    
    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];
        $shipping_type = $this->_shipping_info['service_params']['shipping_type'];
        $cost = $this->_shipping_info['package_info']['C'];
        $default_volume = $this->_shipping_info['service_params']['default_volume'];
        
        if ($weight_data['plain'] > 0.01) {
            $weight = (float) $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
        } else {
            $weight = 0.5;
        }
        
        if ($origination['country'] != 'UA' || $location['country'] != 'UA') {
            $this->_internalError(__('np_country_error'));
        }
        
        if (CART_PRIMARY_CURRENCY != 'UAH') {
            $cost = fn_novaposhta_format_price($cost, 'UAH');
        }
        
        // Get city id
        
        $origination_city_id = $this->getNovaPoshtaCityId($origination['city']);
        $this->_shipping_info['package_info']['origination']['city_id'] = $origination_city_id;
        
        $dest_city_id = $this->getNovaPoshtaCityId($location['city']);
        
        $this->_shipping_info['package_info']['location']['city_id'] = $dest_city_id;
        
        if ($dest_city_id != false) {
            // Get warehouses
            $warehouses = $this->getNovaPoshtaWarehouses($dest_city_id);
        }
        
        Registry::get('view')->assign('warehouses', $warehouses);
        
        $data = array(
            'apiKey' => Registry::get('addons.novaposhta.api_key'),
            'modelName' => 'InternetDocument',
            'calledMethod' => 'getDocumentPrice',
            'methodProperties' => array(
                'PayerType' => 'Sender',
                'PaymentMethod' => 'Cash',
                'DateTime' => date('d.m.Y'),
                'CargoType' => 'Cargo',
                'VolumeGeneral' => '0.00',
                'Weight' => $weight,
                'ServiceType' => $shipping_type,
                'SeatsAmount' => 1,
                'Cost' => $cost,
                'CitySender' => $origination_city_id,
                'CityRecipient' => $dest_city_id
            )
        );
        
        $data = json_encode($data);
        
        $url = NP_API_URL;
        
        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => $data,
        );
        
        return $request_data;
    }    
    
    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $data = $this->getRequestData();
        $response = Http::post($data['url'], $data['data']);

        return $response;
    }
    
    public function getNovaPoshtaWarehouses($city_id) {
        $local_warehouses = db_get_hash_array("SELECT warehouse_id, data FROM ?:novaposhta_warehouses WHERE city_id = ?s AND timestamp >= ?i", 'warehouse_id', $city_id, time());
        
        if (empty($local_warehouses)) {
            
            $local_warehouses = array();
            
            $data = array(
                'modelName' => 'Address',
                'calledMethod' => 'getWarehouses',
                'methodProperties' => array(
                    'CityRef' => $city_id
                )
            );
            $response = $this->GetNovaPoshtaData($data);
            
            foreach($response['data'] as $v) {
                $warehouse = $v;
                unset($warehouse['Delivery']);
                unset($warehouse['Schedule']);
                
                $warehouse['reception_time'] = array();
                
                foreach($warehouse['Reception'] as $day => $time) {
                    if (!empty($warehouse['reception_time'][$time])) {
                        $warehouse['reception_time'][$time]['end'] = strtoupper($day);
                    } else {
                        $warehouse['reception_time'][$time]['start'] = strtoupper($day);
                    }
                }
                unset($warehouse['Reception']);
                $local_warehouses[$v['Ref']] = $warehouse;
                
                $db_data = array(
                    'city_id' => $city_id,
                    'warehouse_id' => $v['Ref'],
                    'data' => json_encode($warehouse),
                    'timestamp' => time() + SECONDS_IN_DAY * Registry::get('addons.novaposhta.cache_livetime')
                );
                db_query("REPLACE INTO ?:novaposhta_warehouses ?e", $db_data);
            }
            
        } else {
            foreach($local_warehouses as $k => $v) {
                $local_warehouses[$k] = json_decode($v['data'], true);
            }
        }
        Registry::set('local_warehouses', $local_warehouses);
        return $local_warehouses;
        
    }
    
    public function getNovaPoshtaCityId($city_name) {
        $city_name = mb_strtolower($city_name);
        $local_id = db_get_field("SELECT city_id FROM ?:novaposhta_cities WHERE city_name = ?s OR city_name_ua = ?s AND timestamp >= ?i", $city_name, $city_name, time());
        if (empty($local_id)) {
            $data = array(
                'modelName' => 'Address',
                'calledMethod' => 'getCities',
                'methodProperties' => array(
                    'FindByString' => $city_name
                )
            );
            $response = $this->GetNovaPoshtaData($data);
            
            if ($response == true && $response['data'][0]['Ref'] == true) {
                $local_id = $response['data'][0]['Ref'];
                $db_data = array(
                    'city_id' => $local_id,
                    'city_name' => $response['data'][0]['DescriptionRu'],
                    'city_name_ua' => $response['data'][0]['Description'],
                    'timestamp' => time() + SECONDS_IN_DAY * Registry::get('addons.novaposhta.cache_livetime')
                );
                db_query("REPLACE INTO ?:novaposhta_cities ?e", $db_data);
            } else {
                return false;
            }
        }
        return $local_id;
    }
    
    public function GetNovaPoshtaData($data) {
        $data['apiKey'] = Registry::get('addons.novaposhta.api_key');
        $data = json_encode($data);
        $response = Http::post(NP_API_URL, $data);
        $response = json_decode($response, true);
        if ($response['success'] == true) {
            return $response;
        } else {
            return false;
        }
    }
}