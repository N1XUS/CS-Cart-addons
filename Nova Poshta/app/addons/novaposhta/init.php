<?php
    
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'create_order',
    'update_order',
    'get_order_info',
    'get_shipments_info_post'
);