<?php
    
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Session;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
$cart = &$_SESSION['cart'];
    
if ($mode == 'update_steps' || $mode == 'checkout') {
    if (!empty($_REQUEST['shipping_ids']) && isset($cart['warehouse'])) {
        unset($cart['warehouse']);
    }
    if (!empty($_REQUEST['warehouse'])) {
        $warehouses = (array) $_REQUEST['warehouse'];
        $cart['warehouse'] = $_REQUEST['warehouse'];
    }
    
    if (isset($cart['warehouse'])) {
        Tygh::$app['view']->assign('selected_warehouse', $cart['warehouse']);
    }
}