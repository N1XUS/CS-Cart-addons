<?php
    
$schema['seo_filters.update'] = array(
    'func' => array('fn_get_seo_filter_name', '@combination_id'),
    'text' => 'seo_filter'
);

return $schema;