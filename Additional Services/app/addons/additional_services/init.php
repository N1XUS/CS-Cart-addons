<?php
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'calculate_cart',
    'create_order',
    'update_order',
    'get_order_info'
);
