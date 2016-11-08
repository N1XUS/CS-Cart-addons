<?php
    
$schema['conditions']['left_comment'] = array(
    'type' => 'statement',
    'field_function' => array('fn_commented_promo', '#id', '#this', '@product', '@auth', 'N'),
    'zones' => array('catalog', 'cart')
);

$schema['conditions']['left_comment_about_purchased_product'] = array(
    'type' => 'statement',
    'field_function' => array('fn_commented_promo', '#id', '#this', '@product', '@auth', 'Y'),
    'zones' => array('catalog', 'cart')
);

return $schema;