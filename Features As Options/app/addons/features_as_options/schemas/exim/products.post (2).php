<?php
    
$schema['export_fields']['Parent Product'] = array(
    'db_field' => 'parent'
);

$schema['export_fields']['Parent Product Code'] = array(
    'process_get' => array('fn_exim_get_parent_product_code', '#key'),
    'process_put' => array('fn_exim_set_parent_product_code', '#key', '#this'),
    'linked' => false, // this field is not linked during import-export
);

return $schema;

?>