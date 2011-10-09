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

// END Of CONFIGURATION
// WARNING!!! DO NOT EDIT THE CODE UNDER THIS LINE

// init web application framework
define('PATH', dirname(__FILE__));
define("VALID_CMS", 1);

include(PATH . '/core/cms.php');
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

// init maps module
$inCore->includeGraphics();
$inCore->loadModel('maps');
$model = new cms_model_maps();

// get maps specific configuration
$cfg = $model->getConfig();

// read values from request & save them to database
$id         = $inCore->request('id', 'int', 0);
$seolink    = $inCore->request('seolink', 'str', '');
$do         = $inCore->request('do', 'str', 'view');
$page       = $inCore->request('page', 'int', 1);

// create new item
$item                   = array();

//set default item values
$item['user_id']        = CMS_USER_ID;
$item['vendor_id']      = 0;
$item['tpl']            = 'com_inmaps_item.tpl';
$item['is_comments']    = 0;
$item['published']      = $cfg['published_add'];
$item['on_moderate']    = ($item['published'] ? 0 : 1);
$item['pubdate']        = date('Y-m-d H:i');
$item['is_front']       = 0;
$item['cats']           = array();
$item['auto_thumb']     = $inCore->request('auto_thumb', 'int', 1);

// save problem information
$item['category_id']    = $inCore->request('cat_id', 'int', 0); // cat_id
$item['title']          = $inCore->request('title', 'str'); // title
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


/*
if (isset($_FILES["imgfile"]["name"]) && @$_FILES["imgfile"]["name"]!='') {
	//generate image file			
	$tmp_name = $_FILES["imgfile"]["tmp_name"];
	$file = $_FILES["imgfile"]["name"];			
	$path_parts = pathinfo($file);
	$ext = $path_parts['extension'];	
	$file = md5($file.time()).'.'.$ext;
	$item['file'] = $file;
	
	//upload image and insert record in db		
	if (@move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT']."/upload/files/images/$file")){
		@img_resize($_SERVER['DOCUMENT_ROOT']."/upload/files/images/$file", $_SERVER['DOCUMENT_ROOT']."/upload/files/images/small/$file.jpg", 100, 100);
		@img_resize($_SERVER['DOCUMENT_ROOT']."/upload/files/images/$file", $_SERVER['DOCUMENT_ROOT']."/upload/files/images/medium/$file.jpg", 250, 250);
	    @chmod($_SERVER['DOCUMENT_ROOT']."/upload/files/images/$file", 0755);
		@chmod($_SERVER['DOCUMENT_ROOT']."/upload/files/images/small/$file.jpg", 0755);
		@chmod($_SERVER['DOCUMENT_ROOT']."/upload/files/images/medium/$file.jpg", 0644);
	}
}
*/



// save information to database
$item['id']             = $model->addItem($item);


// report successfull information submition
$seolink = $inDB->get_field('cms_map_items', "id={$item['id']}", 'seolink');
$category = $inDB->get_fields('cms_map_cats', "id={$item['category_id']}", 'title, seolink');

cmsActions::log('add_maps_obj', array(
                'object' => $item['title'],
                'object_url' =>  "/maps/{$seolink}.html",
                'object_id' =>  $item['id'],
                'target' => $category['title'],
                'target_url' => "/maps/{$category['seolink']}",
                'target_id' =>  $item['category_id'],
                'description' => ''
));
