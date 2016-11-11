<?php
    
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Session;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
$cart = &$_SESSION['cart'];

if ($mode == 'update_steps' || $mode == 'checkout') {
    $local_warehouses = Registry::get('local_warehouses');
    if (isset($cart['warehouse'])) {
        foreach((array) $cart['warehouse'] as $group_id => $warehouse_id) {
            $selected_warehouses_info[$group_id] = $local_warehouses[$warehouse_id];
        }
    }
    Tygh::$app['view']->assign('selected_warehouses_info', $selected_warehouses_info);
}
    
?>