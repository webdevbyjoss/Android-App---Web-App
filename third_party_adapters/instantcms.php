<?php
// 
// Street Problems Reporter to Instant CMS integration script
// 
// we will grab values from the request
// and put them into the database
//
// author: Joseph Chereshnovsky <joseph.chereshnovsky@gmail.com>
// (c) 2011, GPL 
//
// POST: 
//  token - security tokens
//	cat_id - category
//	title - problem description
//	addr_lat - longitude
//	addr_lng - latitude
//  imgfile   - <input type="file" name="imgfile" />

// CONFIGURATION

// security token
// to allow only authorized reports and prevent spam posting
define('SPR_SECURITY_TOKEN', 'QQQWWWEEERRRTTYYYYUUUUIIIOOO');


// Instant CMS user ID from `#__users` table
// who will own all items reported from mobile
define('CMS_USER_ID', 35);


// category IDs map between categories on SPR mobile app and InstantCMS maps categories
$spr_to_gpu_categories = array(
	'0' => '35', // Яма на дорозі
	'1' => '35', // Відкритий каналізаційний люк
	'2' => '34', // Переповнений смітник
	'3' => '37', // Ігрові автомати в житлових районах
	'4' => '36', // Неприбрані під'їзди
	'5' => '34', // Звалища сміття посеред міста
	'6' => '38', // Руйнування архітектури
	'7' => '51', // Відсутнє освітлення
	'8' => '52', // Інші скарги	
);

$default_category_titles = array(
	'0' => 'Яма на дорозі',
	'1' => 'Відкритий каналізаційний люк',
	'2' => 'Переповнений смітник',
	'3' => 'Ігрові автомати в житлових районах',
	'4' => 'Неприбрані під\'їзди',
	'5' => 'Звалища сміття посеред міста',
	'6' => 'Руйнування архітектури',
	'7' => 'Відсутнє освітлення',
	'8' => 'Інші скарги',
);

// default country
define('DEFAULT_COUNTRY', 'Україна');

// END Of CONFIGURATION
// WARNING!!! DO NOT EDIT THE CODE UNDER THIS LINE

// init web application framework
define('PATH', dirname(__FILE__));
define("VALID_CMS", 1);

include(PATH . '/core/cms.php');
include(PATH . '/core/lib_tags.php');
$inCore = cmsCore::getInstance();

// lets check security token provided
$secureToken = $inCore->request('token', 'str', '');
if ($secureToken !== SPR_SECURITY_TOKEN) {
	throw new Exception('Security error, invalid SPR token');
}

$inCore->loadClass('page');
$inCore->loadClass('plugin');
$inCore->loadClass('user');
$inCore->loadClass('actions');    

$inDB       = cmsDatabase::getInstance();
$inPage     = cmsPage::getInstance();
$inConf     = cmsConfig::getInstance();
$inUser     = cmsUser::getInstance();
$cmsActions	= cmsActions::getInstance();

// init maps module
$inCore->includeGraphics();
$inCore->loadModel('maps');
$model = new cms_model_maps();

// get maps specific configuration
$cfg = $model->getConfig();

// if this page was accessed via GET
// then just show the OK status
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	die('Status: OK');
}

// create new item
$item                   = array();

//set default item values
$item['user_id']        = CMS_USER_ID;
$item['vendor_id']      = 0;
$item['tpl']            = 'com_inmaps_item.tpl';
$item['is_comments']    = 0;
$item['published']      = 1; // lets publisheverything by default
$item['on_moderate']    = ($item['published'] ? 0 : 1);
$item['pubdate']        = date('Y-m-d H:i');
$item['is_front']       = 0;
$item['cats']           = array();
$item['auto_thumb']     = $inCore->request('auto_thumb', 'int', 1);

// save problem information
$mobile_cat_id = $inCore->request('cat_id', 'int', 0);
$item['category_id']    = $spr_to_gpu_categories[$mobile_cat_id]; // cat_id

// title can't be empty
// because we need clickable link in instantCMS admin area
// so in case title is not esterred - then lets enter the default 
// title of the selected category
$title = $inCore->request('title', 'str');
if (empty($title)) {
	$title = $default_category_titles[$mobile_cat_id];
}

$item['title']          = utf8_to_cp1251($title); // title

// coordinates
$item['addr_lat']       = $inCore->request('addr_lat', 'array'); // addr_lat
$item['addr_lng']       = $inCore->request('addr_lng', 'array'); // addr_lng

// save extra information
$item['shortdesc']      = $item['title']; // $inDB->escape_string($inCore->request('shortdesc', 'html'));
$item['description']    = $item['title']; // $inDB->escape_string($inCore->request('description', 'html'));
$item['tags']           = ''; // $inCore->request('tags', 'str');
$item['metakeys']       = ''; // $inCore->request('tags', 'str');
$item['metadesc']       = $item['title']; // $inCore->request('shortdesc', 'str');

// address (optional)
/*
$item['addr_id']        = $inCore->request('addr_id', 'array');
$item['addr_country']   = $inCore->request('addr_country', 'array');
$item['addr_city']      = $inCore->request('addr_city', 'array');
$item['addr_prefix']    = $inCore->request('addr_prefix', 'array');
$item['addr_street']    = $inCore->request('addr_street', 'array');
$item['addr_house']     = $inCore->request('addr_house', 'array');
$item['addr_room']      = $inCore->request('addr_room', 'array');
*/

// $item['chars']          = $inCore->request('chars', 'array');
// $item['contacts']       = $inCore->request('contacts', 'array');




// save information to database
$item['id']             = $model->addItem($item);

// let get the URL
$seolink = $inDB->get_field('cms_map_items', "id={$item['id']}", 'seolink');
$category = $inDB->get_fields('cms_map_cats', "id={$item['category_id']}", 'title, seolink');

$action_data = array(
    'object' => $item['title'],
    'object_url' =>  "/maps/{$seolink}.html",
    'object_id' =>  $item['id'],
    'target' => $category['title'],
    'target_url' => "/maps/{$category['seolink']}",
    'target_id' =>  $item['category_id'],
	'description' => ''
);

// we should insert information into 
// cms_map_items_cats - to assign category
$sql = "INSERT INTO cms_map_items_cats SET
	item_id = " . $item['id'] . ",
	category_id = " . $item['category_id'] . ",
	ordering = 1";
$inDB->query($sql);

// cms_map_markers - to place a marker on map
$sql = "INSERT INTO cms_map_markers SET
	item_id = " . $item['id'] . ",
	category_id = " . $item['category_id'] . ",
	addr_country = '" . DEFAULT_COUNTRY . "',
	addr_city = '',
	addr_prefix = '',
	addr_street = '',
	addr_house = '',
	addr_room = '',
	addr_hash = '" . md5('povidom-vladu.org.ua' . microtime()) . "',
	lat = " . floatval($item['addr_lat']) . ",
	lng = " . floatval($item['addr_lng']) . ",
	marker = 'attention.png',
	zoom = 0,
	published = 1,
	is_main = 0";

var_dump($sql);

$inDB->query($sql);

// cms_map_attend - to set the relation between object on map & object type
$sql = "INSERT INTO cms_map_attend SET 
	object_id = " . $item['id'] . ",
	object_type = 'item',
	user_id = " . CMS_USER_ID . "
";
$inDB->query($sql);

// cms_actions_log - to show the new item in actions log
// report successfull information submition
// cmsActions::log('add_maps_obj', $action_data);
$action = cmsActions::getAction('add_maps_obj');
if ($action) {
	$action_data['object']      =  $inDB->escape_string(stripslashes(str_replace(array('\r', '\n'), ' ', $action_data['object'])));
	$action_data['target']      =  $inDB->escape_string(stripslashes(str_replace(array('\r', '\n'), ' ', $action_data['target'])));
	$action_data['description'] =  $inDB->escape_string(stripslashes(str_replace(array('\r', '\n'), ' ', $action_data['description'])));
	$action_data['description'] =  preg_replace('/\[hide\](.*?)\[\/hide\]/i', '', $action_data['description']);
	$action_data['description'] =  preg_replace('/\[hide\](.*?)$/i', '', $action_data['description']);
	$action_data['user_id']     =  CMS_USER_ID;
	
	$sql = "INSERT INTO cms_actions_log (action_id, pubdate, user_id, object, object_url, object_id,
	           target, target_url, target_id, description, is_friends_only, is_users_only)
	        VALUES ('{$action['id']}', NOW(), '{$action_data['user_id']}',
	                '{$action_data['object']}', '{$action_data['object_url']}', '{$action_data['object_id']}',
	                '{$action_data['target']}', '{$action_data['target_url']}', '{$action_data['target_id']}',
	                '{$action_data['description']}', '{$action_data['is_friends_only']}', '{$action_data['is_users_only']}')";
	$inDB->query($sql);
}

// cms_map_chars_val - set the item status
// char_id - means "Статус проблемы"
$sql = "INSERT INTO `cms_map_chars_val` SET 
`item_id` = " . $item['id'] . ",
`char_id` = 40, 
`val` = '" . utf8_to_cp1251('|Не решена|') . "'
";
$inDB->query($sql);


// lets send the notification to email about new information submit
mail('joseph.chereshnovsky@gmail.com', '[POVIDOM-VLADU] gpu.org.ua - report processed', cp1251_to_utf8(var_export($action_data, true)) . "\n" . cp1251_to_utf8(var_export($item, true)));


// helper functions
// Dumn.. InstantCMS only works with cp1251
// to avoid dependency to iconv lets use some custom function
function utf8_to_cp1251($s) {
	return iconv('UTF-8', 'cp1251', $s);
}
// ... and another direction
function cp1251_to_utf8($s) {
	return iconv('cp1251', 'UTF-8', $s);
}
