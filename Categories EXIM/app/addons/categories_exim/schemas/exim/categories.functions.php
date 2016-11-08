<?php
    
use Tygh\Registry;
use Tygh\Storage;

function fn_import_category_data($data, $options, &$processed_data, &$skip_record, $category_delimiter) {
    
    static $categories = array();
    
    $skip_record = true;
    
    $category = reset($data);

    $category_id = false;
    
    if (empty($category['category'])) {
        return false;
    }
    $langs = array_keys($data);
    $main_lang = reset($langs);
    
    $category['use_custom_templates'] = 'N';
    
    array_walk($category, 'fn_trim_helper');
    
    if (empty($category['Path'])) {
        $category['Path'] = $category['category'];
    }
    
    if (Registry::get('runtime.company_id')) {
        
        $company_id = Registry::get('runtime.company_id');
    } else {
        
        if (!empty($category['company'])) {
            $company_id = fn_get_company_id_by_name($category['company']);
        } else {
            $company_id = isset($category['company_id']) ? $category['company_id'] : Registry::get('runtime.company_id');
        }        
    }
    
    $category['company_id'] = $company_id;
    
    if (isset($category['category_id'])) {
        $category_id = db_get_field('SELECT category_id FROM ?:categories WHERE category_id = ?i', $category['category_id']);
        
        if (empty($category_id)) {
            list($category_id, $new) = fn_get_category_id_by_path($category['Path'], $category_delimiter, $main_lang, false, $company_id);
            if ($new == true) {
                $processed_data['N']++;
            } else {
                $processed_data['E']++;
            }
        } else {
            $processed_data['E']++;
        }
    } else {
        // Try to locate category id by path
        list($category_id, $new) = fn_get_category_id_by_path($category['Path'], $category_delimiter, $main_lang, false, $company_id);
        if ($new == true) {
            $processed_data['N']++;
        } else {
            $processed_data['E']++;
        }
    }
    
    $category['category_id'] = $category_id;
    
    $category['id_path'] = fn_get_category_id_by_path($category['Path'], $category_delimiter, $main_lang, true, $company_id);
    
    unset($category['Path']);
    
    $category['selected_views'] = trim($category['selected_views']);
    
    if (!empty($category['selected_views'])) {
        $category['use_custom_templates'] = 'Y';
        $category['selected_views'] = fn_exim_set_available_views($category['selected_views']);
    }
    
    if (Registry::get('addons.seo.status') == 'A' && !empty($category['name'])) {
        $category['seo_name'] = $category['name'];
    }
    
    $category_id = fn_update_category($category, $category_id, $main_lang);
    
    if (!empty($category['detailed_id'])) {
        fn_import_images($options['images_path'], $category['detailed_id'], $category, 0, 'M', $category_id, 'category');
    }
    
    return $category_id;
}

function fn_get_category_id_by_path($path, $category_delimiter, $lang, $get_id_path = false, $company_id) {
    
    if (strpos($path, $category_delimiter) !== false) {
        $_paths = explode($category_delimiter, $path);
        array_walk($_paths, 'fn_trim_helper');
    } else {
        $_paths = array($path);
    }
    
    $parent_id = '0';
    
    $id_path = false;
    
    foreach($_paths as $k => $category) {
        $new = false;
        $category_id = db_get_field("SELECT c.category_id FROM ?:categories AS c LEFT JOIN ?:category_descriptions AS cd ON c.category_id = cd.category_id AND cd.lang_code = ?s WHERE cd.category = ?s AND c.parent_id = ?s AND c.company_id = ?i", $lang, $category, $parent_id, $company_id);
        if (empty($category_id)) {
            // If this category not exist, create one
            $category_data = array(
                'parent_id' => $parent_id,
                'company_id' => $company_id,
                'category' =>  $category,
                'timestamp' => TIME,
            );
            $category_id = fn_update_category($category_data);
            $new = true;  
        }
        $parent_id = $category_id;
        if ($get_id_path) {
            $id_path[] = $category_id;
        }
    }
    
    if ($get_id_path) {
        $id_path = array_reverse($id_path);
        $id_path = implode('/', $id_path);
        return $id_path;
    }
    
    return array($category_id, $new);
}

function fn_exim_get_category_path($category_id, $category_delimiter, $lang_code = '')
{
    $result = fn_get_category_path($category_id, $lang_code, $category_delimiter);

    return $result;
}

/**
 * Process category description. Depend on company id.
 * @param integer $category_id Category id
 * @param string $value Default category description
 * @param string $lang_code Lang code
 * @return string
 */
function fn_export_category_descr($category_id, $value, $lang_code, $field)
{
    $descr = db_get_field("SELECT $field FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $category_id, $lang_code);
    if (!empty($descr)) {
        return $descr;
    }

    return $value;
}

/**
 * Update category description for necessary store.
 * @param Array $data Category data
 * @param integer $category_id Category id
 * @param string $lang_code Lang code
 */
function fn_import_category_descr($data, $category_id, $field)
{
    foreach ($data as $lang_code => $_data) {
        db_query(
            "UPDATE ?:category_descriptions SET $field = ?s WHERE category_id = ?i AND lang_code = ?s",
            $_data, $category_id, $lang_code
        );
    }
}

function fn_exim_get_available_views($data) {
    if (!empty($data)) {
        $data = unserialize($data);
        return implode(',',$data);        
    }
}

function fn_exim_set_available_views($data) {
    $_data = '';
    if (!empty($data)) {
        $data = explode(',', $data);
        $_data = array();
        foreach((array) $data as $k =>$v) {
            $v = trim($v);
            $_data[$v] = $v;
        }
        return $_data;
    }
    return $_data;
}

function fn_import_fill_categories_alt_keys($pattern, &$alt_keys, &$object, &$skip_get_primary_object_id)
{
    if (Registry::get('runtime.company_id')) {
        $alt_keys['company_id'] = Registry::get('runtime.company_id');

    } elseif (!empty($object['company'])) {
        // field store is set
        $company_id = fn_get_company_id_by_name($object['company']);
        if ($company_id !== null) {
            $alt_keys['company_id'] = $company_id;
        }
    }
    
    $skip_get_primary_object_id = true;
}

function fn_import_unset_category_id(&$object)
{
    unset($object['category_id']);
}

?>