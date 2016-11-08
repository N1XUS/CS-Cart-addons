<?php
    
use Tygh\Registry;
use Tygh\Mailer;
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '';
    
    if ($mode == 'subscribe') {
        if (fn_image_verification('price_drop_alert', $_REQUEST) == false) {
            fn_save_post_data('pda_subscribe_data');
        } elseif (!empty($_REQUEST['subscribe_data'])) {
            // Check if this user already signed up for this product
            $sub_data = $_REQUEST['subscribe_data'];
            $confirm = Registry::get('addons.price_drop_alert.subscription_confirmation');
            $exist = db_get_field("SELECT email FROM ?:pda_subscribers WHERE email = ?s AND product_id = ?i", $sub_data['email'], $sub_data['product_id']);
            $product_name = fn_get_product_name($sub_data['product_id']);
            if (empty($sub_data['target_price'])) {
                $product = fn_get_product_data(
                    $sub_data['product_id'],
                    $auth,
                    CART_LANGUAGE,
                    '',
                    true,
                    true,
                    true,
                    true,
                    fn_is_preview_action($auth, $_REQUEST),
                    true,
                    false,
                    true
                );
                $sub_data['target_price'] = $product['price'] - 0.01; // No need to notify customer with not changed price;
            }
            $send_confirmation_letter = false;
            $db_data = array(
                'timestamp' => TIME,
                'email' => $sub_data['email'],
                'product_id' => $sub_data['product_id'],
                'target_price' => fn_pda_convert_price_down(CART_SECONDARY_CURRENCY, $sub_data['target_price']),
                'currency_code' => CART_SECONDARY_CURRENCY,
                'lang_code' => CART_LANGUAGE,
                'hash' => md5($sub_data['email'] . TIME . $sub_data['product_id'] . $sub_data['target_price'] . CART_LANGUAGE)
            );
            
            if ($sub_data['anytime'] == 'Y') {
                $db_data['notification_type'] = 'A';
            } else {
                $db_data['notification_type'] = 'O';
            }
            
            if ($confirm == 'Y') {
                $db_data['status'] = 'P';
                $send_confirmation_letter = true;
            } else {
                $db_data['status'] = 'Q';
            }
            
            if ($exist) {
                db_query("UPDATE ?:pda_subscribers SET ?u WHERE email = ?s AND product_id = ?i", $db_data, $sub_data['email'], $sub_data['product_id']);
            } else {
                db_query("INSERT INTO ?:pda_subscribers ?e", $db_data);
            }
            
            if ($confirm == 'N') {
                if ($exist) {
                    fn_set_notification('N', __('notice'), __("addons.price_drop_alert.updated", array('[PRODUCT_NAME]' => $product_name)));
                } else {
                    fn_set_notification('N', __('notice'), __("addons.price_drop_alert.signed", array('[PRODUCT_NAME]' => $product_name)));
                    if (Registry::get('addons.price_drop_alert.notify_admin') == 'Y') {
                        fn_pda_notify_admin($db_data);
                    }
                }
            } else {
                if ($exist) {
                    fn_set_notification('N', __('notice'), __("addons.price_drop_alert.updated", array('[PRODUCT_NAME]' => $product_name)));
                } else {
                    Mailer::sendMail(array(
                        'to' => $sub_data['email'],
                        'from' => 'company_orders_department',
                        'data' => array(
                            'hash' => $db_data['hash'],
                            'target_price' => $db_data['target_price'],
                            'currency_code' => $db_data['currency_code'],
                            'product' => array(
                                'product_id' => $db_data['product_id'],
                                'product' => $product_name
                            ),
                            'product_image' => fn_get_image_pairs($sub_data['product_id'], 'product', 'M', true, true, CART_LANGUAGE)
                        ),
                        'tpl' => 'addons/price_drop_alert/confirm.tpl',
                        'company_id' => Registry::get('runtime.company_id'),
                    ), 'A', CART_LANGUAGE);
                    fn_set_notification('N', __('notice'), __("addons.price_drop_alert.confirmation_send"));
                }
            }
        }
    }
    return array(CONTROLLER_STATUS_OK, $return_url);
}

if ($mode == 'confirm') {
    $hash = $_REQUEST['hash'];
    
    $data = db_get_row("SELECT status, product_id FROM ?:pda_subscribers WHERE hash = ?s", $hash);
    $status = $data['status'];
    $product_id = $data['product_id'];
    $product_name = fn_get_product_name($product_id);
    if ($status == 'P') {
        db_query("UPDATE ?:pda_subscribers SET status = ?s WHERE hash = ?s", 'Q', $hash);
        fn_set_notification('N', __('notice'), __("addons.price_drop_alert.confirmation_approved", array('[PRODUCT_NAME]' => $product_name)));
        if (Registry::get('addons.price_drop_alert.notify_admin') == 'Y') {
            $db_data = db_get_row("SELECT * FROM ?:pda_subscribers WHERE hash = ?s", $hash);
            fn_pda_notify_admin($db_data);            
        }
    } else if ($status == 'N') {
        fn_set_notification('N', __('notice'), __("addons.price_drop_alert.already_notified", array('[PRODUCT_NAME]' => $product_name)));
    } else if ($status == 'Q') {
        fn_set_notification('N', __('notice'), __("addons.price_drop_alert.already_approved", array('[PRODUCT_NAME]' => $product_name)));
    }
    return array(CONTROLLER_STATUS_REDIRECT, "index.index");
}

if ($mode == 'unsubscribe') {
    if (!empty($_REQUEST['hash'])) {
        $data = db_get_row("SELECT status, product_id FROM ?:pda_subscribers WHERE hash = ?s", $hash);
        $product_id = $data['product_id'];
        $product_name = fn_get_product_name($product_id);
        if (Registry::get('addons.price_drop_alert.delete_unsubscribed') == 'Y') {
            db_query("DELETE FROM ?:pda_subscribers WHERE hash = ?s", $_REQUEST['hash']);
        } else {
            db_query("UPDATE ?:pda_subscribers SET status = ?s WHERE hash = ?s", 'U', $_REQUEST['hash']);
        }
        fn_set_notification('N', __('notice'), __("addons.price_drop_alert.unsubscribed", array('[PRODUCT_NAME]' => $product_name)));
    }
    return array(CONTROLLER_STATUS_REDIRECT, "index.index");
}