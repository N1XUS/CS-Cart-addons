<?php
    
use Tygh\Registry;    
    
include_once(Registry::get('config.dir.addons') . 'categories_exim/schemas/exim/categories.functions.php');

$schema = array(
    'section' => 'categories',
    'name' => __('categories'),
    'pattern_id' => 'categories',
    'key' => array('category_id'),
    'order' => 0,
    'table' => 'categories',
    'permissions' => array(
        'import' => 'manage_catalog',
        'export' => 'view_catalog',
    ),
    'references' => array(
        'category_descriptions' => array(
            'reference_fields' => array('category_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT'
        ),
        'images_links' => array(
            'reference_fields' => array('object_id' => '#key', 'object_type' => 'category', 'type' => 'M'),
            'join_type' => 'LEFT',
            'import_skip_db_processing' => true
        ),
        'companies' => array(
            'reference_fields' => array('company_id' => '&company_id'),
            'join_type' => 'LEFT',
            'import_skip_db_processing' => true
        )
    ),
    'condition' => array(
        'use_company_condition' => true,
    ),
    'range_options' => array(
        'selector_url' => 'categories.manage',
        'object_name' => __('categories'),
    ),
    'import_get_primary_object_id' => array(
        'fill_categories_alt_keys' => array(
            'function' => 'fn_import_fill_categories_alt_keys',
            'args' => array('$pattern', '$alt_keys', '$object', '$skip_get_primary_object_id'),
            'import_only' => true,
        ),
    ),
    'import_process_data' => array(
        'import_category_data' => array(
            'function' => 'fn_import_category_data',
            'args' => array('$data', '$options', '$processed_data', '$skip_record', '@category_delimiter'),
            'import_only' => true,
        )
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
        'category_delimiter' => array(
            'title' => 'category_delimiter',
            'description' => 'text_category_delimiter',
            'type' => 'input',
            'default_value' => '///'
        ),
        'images_path' => array(
            'title' => 'images_directory',
            'description' => 'text_images_directory',
            'type' => 'input',
            'default_value' => 'exim/backup/images/',
            'notes' => __('text_file_editor_notice', array('[href]' => fn_url('file_editor.manage?path=/'))),
        ),
    ),
    'export_fields' => array(
        'Category id' => array(
            'db_field' => 'category_id'
        ),
        'Category' => array(
            'table' => 'category_descriptions',
            'db_field' => 'category',
            'alt_key' => true,
            'required' => true,
            'alt_field' => 'category_id'
        ),
        'Path' => array(
            'process_get' => array('fn_exim_get_category_path', '#key', '@category_delimiter', '#lang_code'),
            'multilang' => true,
            'linked' => false,
            'default' => 'Catalog'            
        ),
        'Description' => array(
            'table' => 'category_descriptions',
            'db_field' => 'description',
            'multilang' => true,
            'process_get' => array('fn_export_category_descr', '#key', '#this', '#lang_code', 'description'),
        ),
        'Meta keywords' => array(
            'table' => 'category_descriptions',
            'db_field' => 'meta_keywords',
            'multilang' => true,
            'process_get' => array('fn_export_category_descr', '#key', '#this', '#lang_code', 'meta_keywords'),
        ),
        'Meta description' => array(
            'table' => 'category_descriptions',
            'db_field' => 'meta_description',
            'multilang' => true,
            'process_get' => array('fn_export_category_descr', '#key', '#this', '#lang_code', 'meta_description'),
        ),
        'Page title' => array(
            'table' => 'category_descriptions',
            'db_field' => 'page_title',
            'multilang' => true,
            'process_get' => array('fn_export_category_descr', '#key', '#this', '#lang_code', 'page_title'),
        ),
        'Usergroup IDs' => array(
            'db_field' => 'usergroup_ids'
        ),
        'Date added' => array(
            'db_field' => 'timestamp',
            'process_get' => array('fn_timestamp_to_date', '#this'),
            'convert_put' => array('fn_date_to_timestamp', '#this'),
            'return_result' => true,
            'default' => array('time')
        ),
        'Available views' => array(
            'db_field' => 'selected_views',
            'process_get' => array('fn_exim_get_available_views', '#this'),
            'return_result' => true,
            'default' => array('time')
        ),
        'Default view' => array(
            'db_field' => 'default_view'
        ),
        'Product details view' => array(
            'db_field' => 'product_details_view'
        ),
        'Status' => array(
            'db_field' => 'status',
            'default' => array('A')
        ),
        'Language' => array(
            'table' => 'category_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'required' => true,
            'multilang' => true
        ),
        'Thumbnail' => array(
            'table' => 'images_links',
            'db_field' => 'image_id',
            'use_put_from' => '%Detailed image%',
            'process_get' => array('fn_export_image', '#this', 'category', '@images_path')
        ),
        'Detailed image' => array(
            'db_field' => 'detailed_id',
            'table' => 'images_links',
            'process_get' => array('fn_export_image', '#this', 'detailed', '@images_path'),
            'process_put' => array('fn_import_images', '@images_path', '%Thumbnail%', '#this', '0', 'M', '#key', 'category')
        ),
        'Image URL' => array(
            'process_get' => array('fn_exim_get_image_url', '#key', 'category', 'M', true, false, '#lang_code'),
            'multilang' => true,
            'db_field' => 'image_id',
            'table' => 'images_links',
            'export_only' => true,
        ),
        'Detailed image URL' => array(
            'process_get' => array('fn_exim_get_detailed_image_url', '#key', 'category', 'M', '#lang_code'),
            'db_field' => 'detailed_id',
            'table' => 'images_links',
            'export_only' => true,
        ),
    )
);

$company_schema = array(
    'table' => 'companies',
    'db_field' => 'company',
    'process_put' => array('fn_exim_set_category_company', '#key', '#this')
);

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = $company_schema;

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
    }
}

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['export_fields']['Vendor'] = $company_schema;

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Vendor']['required'] = true;
    }
}

if (Registry::get('addons.seo.status') == 'A') {
    $schema['references']['seo_names'] = array (
        'reference_fields' => array ('object_id' => '#key', 'type' => 'c', 'dispatch' => '', 'lang_code' => '#category_descriptions.lang_code'),
        'join_type' => 'LEFT',
        'import_skip_db_processing' => true
    );
    if (fn_allowed_for('ULTIMATE')) {
        $schema['references']['seo_names']['reference_fields']['company_id'] = '&company_id';
    }
    
    $schema['export_fields']['SEO name'] = array (
        'table' => 'seo_names',
        'db_field' => 'name',
        'process_put' => array ('fn_create_import_seo_name', '#key', 'c', '#this', '%Category%', 0, '', '', '#lang_code'),
    );
    
    if (Registry::get('addons.seo.single_url') == 'N') {
        $schema['export_fields']['SEO name']['multilang'] = true;
    } 
}

return $schema;

?>