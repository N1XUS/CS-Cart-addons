<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'create_shipment',
    'place_order',
    'change_order_status',
    'update_profile',
    'update_product_amount'
);
