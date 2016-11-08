<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'delete') {
        fn_pda_delete_subscriber($_REQUEST['subscriber_id']);
        return array(CONTROLLER_STATUS_OK, "price_drop.subscribers");
    }
    
    if ($mode == 'm_delete') {
        foreach((array) $_REQUEST['subscriber_ids'] as $v) {
            fn_pda_delete_subscriber($v);
        }
        return array(CONTROLLER_STATUS_OK, "price_drop.subscribers");
    }
}

if ($mode == 'subscribers') {
    $params = $_REQUEST;
    $items_per_page = Registry::get('settings.Appearance.admin_pages_per_page');
    
    if (isset($_REQUEST['items_per_page'])) {
        $items_per_page = $_REQUEST['items_per_page'];
    }
    
    $params['items_per_page'] = $items_per_page;
    
    list($subscribers, $search) = fn_pda_get_subscribers($params);
    
    Tygh::$app['view']->assign('subscribers', $subscribers);
    Tygh::$app['view']->assign('search', $search);
}

if ($mode == 'statistics') {
    $params = $_REQUEST;
    $items_per_page = Registry::get('settings.Appearance.admin_pages_per_page');
    
    if (isset($_REQUEST['items_per_page'])) {
        $items_per_page = $_REQUEST['items_per_page'];
    }
    
    $params['items_per_page'] = $items_per_page;
    
    list($subscribers, $search) = fn_pda_get_subscriber_statistics($params);
    
    Tygh::$app['view']->assign('subscribers', $subscribers);
    Tygh::$app['view']->assign('search', $search);    
}