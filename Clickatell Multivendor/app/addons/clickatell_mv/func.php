<?php
    
use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
function fn_clickatell_mv_get_shipping_methods() {
    return db_get_hash_single_array("SELECT a.shipping_id, b.shipping FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id=b.shipping_id AND b.lang_code = '" . CART_LANGUAGE . "' ORDER BY a.position", array('shipping_id', 'shipping'));
}

function fn_clickatell_mv_get_order_statuses() {
    return db_get_hash_single_array("SELECT a.status, b.description FROM ?:statuses AS a LEFT JOIN ?:status_descriptions AS b ON a.status = b.status AND b.lang_code = ?s WHERE a.type = ?s", array('status', 'description'), CART_LANGUAGE,'O');
}

function fn_clickatell_mv_create_shipment(&$shipment_data, &$order_info, &$group_key, &$all_products) {
    $notify_user = fn_get_notification_rules($_REQUEST);
    $notify_user = $notify_user['C'];
    if ($notify_user == true) {
        if (empty($order_info['b_phone']) && empty($order_info['s_phone']) && empty($order_info['phone'])) {
            return;
        }
        
        $path = Registry::get('config.dir.addons') . 'clickatell_mv/';
        
        include ($path . 'vendor/autoload.php');
        
        $phone_area = Registry::get('addons.clickatell_mv.customer_phone_field');
        
        $phone_field = $phone_area . '_phone';
        
        $phone = $order_info[$phone_field];
        
        $country_field = $phone_area . '_country';
        
        $country = $order_info[$country_field];
        
        if (empty($phone)) {
            // If empty this field, than try to use default phone field
            if (!empty($order_info['phone'])) {
                $phone = $order_info['phone'];
            }
        }
        
        if (empty($country)) {
            if (!empty($order_info['country'])) {
                $country = $order_info['country'];
            } else {
                $country = Registry::get('settings.General.default_country');
            }
        }
        
        // Convert phone into E164 format
        
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        
        $phone_proto = $phoneUtil->parse($phone, $country);
        
        $phone = $phoneUtil->format($phone_proto, \libphonenumber\PhoneNumberFormat::E164);
        
        $shipping = db_get_field('SELECT shipping FROM ?:shipping_descriptions WHERE shipping_id = ?i AND lang_code = ?s', $shipment_data['shipping_id'], $order_info['lang_code']);
        
        Tygh::$app['view']->assign('tracking_number', $shipment_data['tracking_number']);
        Tygh::$app['view']->assign('order_id', $order_info['order_id']);
        Tygh::$app['view']->assign('shipping_method', $shipping);
        
        $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/new_shipment_sms.tpl');
        
        $body = fn_clickatell_mv_strip_tags($body);
        
        fn_clickatell_mv_send_sms($phone, $body);
    }
}

function fn_clickatell_mv_change_order_status(&$status_to, &$status_from, &$order_info, &$force_notification, &$order_statuses, &$place_order) {

    if (Registry::get('addons.clickatell_mv.admin_sms_order_updated') == 'Y' || Registry::get('addons.clickatell_mv.customer_sms_order_updated') == 'Y' || Registry::get('addons.clickatell_mv.vendor_sms_order_updated') == 'Y') {
        
        $order_id = $order_info['order_id'];
        
        Tygh::$app['view']->assign('order_id', $order_id);
        Tygh::$app['view']->assign('total', $order_info['total']);
        Tygh::$app['view']->assign('order_email', $order_info['email']);
        Tygh::$app['view']->assign('order_payment_info', $order_info['payment_method']['payment']);
        Tygh::$app['view']->assign('order_status_name', $order_statuses[$status_to]['description']);
        
        $body = '';
        
        if (Registry::get('addons.clickatell_mv.admin_sms_order_updated') == 'Y' && $place_order == false) {
            $result = fn_clickatell_mv_check_order_conditions('admin', $status_to, $order_info, $order_statuses);
            if ($result == true) {
                $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/admin_order_updated_sms.tpl');
                $body = fn_clickatell_mv_strip_tags($body);
                $phone = Registry::get('addons.clickatell_mv.admin_phone_number');
                $phone = explode(',', $phone);
                foreach($phone as $k => $v) {
                    fn_clickatell_mv_send_sms($v, $body);
                }
            }
        }
        
        if (Registry::get('addons.clickatell_mv.vendor_sms_order_updated') == 'Y' && $place_order == false) {
            $result = fn_clickatell_mv_check_order_conditions('vendor', $status_to, $order_info, $order_statuses);
            if ($result == true) {
                $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/admin_order_updated_sms.tpl');
                $body = fn_clickatell_mv_strip_tags($body);
                $phone = fn_clickatell_mv_get_company_phone($order_info['company_id']);
                $phone = explode(',', $phone);
                foreach($phone as $k => $v) {
                    fn_clickatell_mv_send_sms($v, $body);
                }
            }
        }
        
        if (Registry::get('addons.clickatell_mv.customer_sms_order_updated') == 'Y') {
            
            if (empty($order_info['b_phone']) && empty($order_info['s_phone']) && empty($order_info['phone'])) {
                return;
            }
            
            $path = Registry::get('config.dir.addons') . 'clickatell_mv/';
            
            include ($path . 'vendor/autoload.php');
            
            $phone_area = Registry::get('addons.clickatell_mv.customer_phone_field');
            
            $phone_field = $phone_area . '_phone';
            
            $phone = $order_info[$phone_field];
            
            $country_field = $phone_area . '_country';
            
            $country = $order_info[$country_field];
            
            if (empty($phone)) {
                // If empty this field, than try to use default phone field
                if (!empty($order_info['phone'])) {
                    $phone = $order_info['phone'];
                }
            }
            
            if (empty($country)) {
                if (!empty($order_info['country'])) {
                    $country = $order_info['country'];
                } else {
                    $country = 'UA'; // TODO: Replace hardcoded value with value from addon settings
                }
            }
            
            // Convert phone into E164 format
            
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            
            $phone_proto = $phoneUtil->parse($phone, $country);
            
            $phone = $phoneUtil->format($phone_proto, \libphonenumber\PhoneNumberFormat::E164);
            
            $result = fn_clickatell_mv_check_order_conditions('customer', $status_to, $order_info, $order_statuses);
            if ($result == true) {
                if ($order_statuses[$status_to]['sms_body']) {
                    
                    $replacements = array(
                        '%ORDER_ID%' => $order_id,
                        '%AMOUNT%' => $order_info['total'],
                        '%NAME%' => $order_info['firstname'],
                        '%LAST_NAME%' => $order_info['lastname'],
                        '%USER_EMAIL%' => $order_info['email']
                    );
                    
                    $body = $order_statuses[$status_to]['sms_body'];
                    $body = str_replace(array_keys($replacements), $replacements, $body);
                    
                    $body = fn_clickatell_mv_strip_tags($body);
                    
                    fn_clickatell_mv_send_sms($phone, $body);
                }
            }
        }
    }
    
    if ($place_order == true && Registry::get('addons.clickatell_mv.vendor_sms_new_order_placed') == 'Y') {
        $order_id = $order_info['order_id'];
        
        Tygh::$app['view']->assign('order_id', $order_id);
        Tygh::$app['view']->assign('total', $order_info['total']);
        
        Tygh::$app['view']->assign('send_info', $send_info == 'Y' ? true : false);
        Tygh::$app['view']->assign('send_email', $send_email == 'Y' ? true : false);
        Tygh::$app['view']->assign('send_min_amount', $send_min_amount == 'Y' ? true : false);
        
        Tygh::$app['view']->assign('order_email', $order_info['email']);
        Tygh::$app['view']->assign('order_payment_info', $order_info['payment_method']['payment']);
        
        $body = '';
        
        $result = fn_clickatell_mv_check_order_conditions('vendor', '', $order_info, array(), true);
        
        if ($result == true) {
            $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/admin_order_place_sms.tpl');
            $body = fn_clickatell_mv_strip_tags($body);
            $phone = fn_clickatell_mv_get_company_phone($order_info['company_id']);
            $phone = explode(',', $phone);
            foreach($phone as $k => $v) {
                fn_clickatell_mv_send_sms($v, $body);
            }
        }
    }
}

function fn_clickatell_mv_place_order(&$order_id, &$action, &$fake1, &$cart) {
    if ($action !== 'save' && Registry::get('addons.clickatell_mv.admin_sms_new_order_placed') == 'Y') {
        
        Tygh::$app['view']->assign('order_id', $order_id);
        Tygh::$app['view']->assign('total', $cart['total']);
        
        Tygh::$app['view']->assign('send_info', $send_info == 'Y' ? true : false);
        Tygh::$app['view']->assign('send_email', $send_email == 'Y' ? true : false);
        Tygh::$app['view']->assign('send_min_amount', $send_min_amount == 'Y' ? true : false);
        
        Tygh::$app['view']->assign('order_email', $order['email']);
        Tygh::$app['view']->assign('order_payment_info', $order['payment_method']['payment']);
        
        $order = fn_get_order_info($order_id);
    
        $result = fn_clickatell_mv_check_order_conditions('admin', '', $order, array(), true);
        if ($result == true) {
            $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/admin_order_place_sms.tpl');
            $body = fn_clickatell_mv_strip_tags($body);
            $phone = Registry::get('addons.clickatell_mv.admin_phone_number');
            $phone = explode(',', $phone);
            foreach($phone as $k => $v) {
                fn_clickatell_mv_send_sms($v, $body);
            }
        }
    }
}

function fn_clickatell_mv_update_profile(&$action, &$user_data) {
    if ($action == 'add' && AREA == 'C' && (Registry::get('addons.clickatell_mv.admin_sms_new_cusomer_registered') == 'Y' || Registry::get('addons.clickatell_mv.vendor_sms_new_cusomer_registered') == 'Y')) {
        Tygh::$app['view']->assign('customer', $user_data['firstname'] . (empty($user_data['lastname']) ? '' : $user_data['lastname']));
        $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/new_profile_sms.tpl');
        $body = fn_clickatell_mv_strip_tags($body);
        if (Registry::get('addons.clickatell_mv.admin_sms_new_cusomer_registered') == 'Y') {
            $phone = Registry::get('addons.clickatell_mv.admin_phone_number');
            $phone = explode(',', $phone);
            foreach($phone as $k => $v) {
                fn_clickatell_mv_send_sms($v, $body);
            }
        }
    }    
}

function fn_clickatell_mv_update_product_amount(&$new_amount, &$product_id) {
    if ($new_amount <= Registry::get('settings.General.low_stock_threshold') && (Registry::get('addons.clickatell_mv.admin_sms_product_negative_amount') == 'Y' || Registry::get('addons.clickatell_mv.vendor_sms_product_negative_amount') == 'Y')) {
        $lang_code = Registry::get('settings.Appearance.backend_default_language');

        Tygh::$app['view']->assign('product_id', $product_id);
        Tygh::$app['view']->assign('product', db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $product_id, $lang_code));
        $body = Tygh::$app['view']->fetch('addons/clickatell_mv/views/sms/components/low_stock_sms.tpl');
        $body = fn_clickatell_mv_strip_tags($body);
        if (Registry::get('addons.clickatell_mv.admin_sms_product_negative_amount') == 'Y') {
            $phone = Registry::get('addons.clickatell_mv.admin_phone_number');
            $phone = explode(',', $phone);
            foreach($phone as $k => $v) {
                fn_clickatell_mv_send_sms($v, $body);
            }            
        }
        if (Registry::get('addons.clickatell_mv.vendor_sms_product_negative_amount') == 'Y') {
            $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $product_id);
            $phone = fn_clickatell_mv_get_company_phone($company_id);
            $phone = explode(',', $phone);
            foreach($phone as $k => $v) {
                fn_clickatell_mv_send_sms($v, $body);
            }            
        }
    }    
}

function fn_clickatell_mv_check_order_conditions($for = 'admin', $status_to = '', $order, $order_statuses = array(), $skip_statuses = false) {
    $send_min_amount = Registry::get('addons.clickatell_mv.' . $for . '_sms_send_min_amount');
    $shippings = Registry::get('addons.clickatell_mv.' . $for . '_sms_send_shipping');
    if ($skip_statuses == false) {
        $statuses = Registry::get('addons.clickatell_mv.' . $for . '_sms_send_order_statuses');
    } else {
        $statuses = array();
    }
    
    if (!is_array($statuses)) {
        $statuses = array();
    }

    if (!is_array($shippings)) {
        $shippings = array ();
    }
    
    if (count($shippings) && !isset($shippings['N'])) {
        $in_shipping = false;

        if (!empty($order['shipping'])) {
            foreach ($order['shipping'] as $id => $data) {
                if ($shippings[$id] == 'Y') {
                    $in_shipping = true;
                    break;
                }
            }
        }
    } else {
        $in_shipping = true;
    }
    
    if (count($statuses)) {
        $in_status = false;
        if ($statuses[$status_to] == 'Y') {
            $in_status = true;
        }
        // check if status N is a status
        if (isset($statuses['N']) && empty($statuses['N'])) {
            $in_status = true;
        }
    } else {
        $in_status = true;
    }
    
    if ($in_status == true && $in_shipping == true && $order['subtotal'] > doubleval($send_min_amount)) {
        return true;
    }
    return false;
}

function fn_clickatell_mv_get_company_phone($company_id) {
    $company_data = fn_get_company_data($company_id);
    $phone = $company_data['phone'];
    return $phone;
}

function fn_clickatell_mv_send_sms($phone, $body) {
    
    $access_data = fn_clickatell_mv_get_sms_auth_data();
    if (fn_is_empty($access_data) || empty($phone) || !$body) {
        return false;
    }

    $concat = Registry::get('addons.clickatell_mv.clickatell_concat');
    //get the last symbol
    if (!empty($concat)) {
        $concat = intval($concat[strlen($concat)-1]);
    }
    if (!in_array($concat, array('1', '2', '3'))) {
        $concat = 1;
    }
    $data = array('user' => $access_data['login'],
                  'password' => $access_data['password'],
                  'api_id' => $access_data['api_id'],
                  'to' => $phone,
                  'concat' => $concat,
    );

    $unicode = Registry::get('addons.clickatell_mv.clickatell_unicode') == 'Y' ? 1 : 0;

    $sms_length = $unicode ? CLICKATELL_SMS_LENGTH_UNICODE : CLICKATELL_SMS_LENGTH;
    if ($concat > 1) {
        $sms_length *= $concat;
        $sms_length -= ($concat * CLICKATELL_SMS_LENGTH_CONCAT); // If a message is concatenated, it reduces the number of characters contained in each message by 7
    }

    $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
    $body = fn_substr($body, 0, $sms_length);

    if ($unicode) {
        $data['unicode'] = '1';

        $body = fn_convert_encoding('UTF-8', 'UCS-2', $body);
        $body = bin2hex($body);
    }

    $data['text'] = $body;

    Http::get('http://api.clickatell.com/http/sendmsg', $data);

}

function fn_clickatell_mv_get_sms_auth_data()
{
     return array('login' => Registry::get('addons.clickatell_mv.clickatell_user'),
                  'password' => Registry::get('addons.clickatell_mv.clickatell_password') ,
                  'api_id' => Registry::get('addons.clickatell_mv.clickatell_api_id'));
}

/**
 * Strip html tags from the data
 *
 * @param mixed $var variable to strip tags from
 * @return mixed filtered variable
 */
function fn_clickatell_mv_strip_tags(&$var)
{

    if (!is_array($var)) {
        return (strip_tags($var));
    } else {
        $stripped = array();
        foreach ($var as $k => $v) {
            $sk = strip_tags($k);
            if (!is_array($v)) {
                $sv = strip_tags($v);
            } else {
                $sv = fn_strip_tags($v);
            }
            $stripped[$sk] = $sv;
        }

        return ($stripped);
    }
}