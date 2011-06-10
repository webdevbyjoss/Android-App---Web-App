<?php

class UploadController extends Custom_Controller_Action
{
	public $contexts = array(
        'index'     => array('json'),
		'error'     => array('json'),
		'post'     => array('json'),
    );

    public function indexAction()
    {
    	// switch to AJAX responce
    	$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->initContext('json');
    	
        // get file from stream
		$input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        // if ($realSize != $this->getSize()){            
        //    return false;
        // }
        
        // write data into file 
        $path = realpath(APPLICATION_PATH . '/../data/uploads');
        $filename = tempnam($path, 'jpg');
        
        $target = fopen($filename, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        // generate preview into public directory
        $preview_path = realpath(APPLICATION_PATH . '/../public_html/images/tmp');
		$preview_filename = tempnam($preview_path, 'pre') . '.jpg';

        // TODO: resize preview
        $res =  copy($filename, $preview_filename);
        
        if (empty($res)) {
        	throw new Exception('Upload filed. File:' . $preview_filename);
        }
        
        // lets save tmp filename to session for further processing
        $session = new Zend_Session_Namespace('file');
        $session->filename = $filename;
        $session->preview_file = $preview_filename;
        $session->preview_filename = basename($preview_filename);
        
        $this->view->success = true;
    }
    
    public function reportAction()
    {
    	$Regions = new Searchdata_Model_Regions();
		// Ukraine has ID = 1
		// TODO: move this outside of controller
		$CountryCodeUkraine = 1;
		$this->view->regions = $Regions->getItems($CountryCodeUkraine);
		
		// get categories
		$Categories = new Searchdata_Model_Category();
		$this->view->categories = $Categories->getAllItems();
		
		$session = new Zend_Session_Namespace('file');
		
		$tmpUrl = '/images/tmp/';
		$this->view->imageUrl = $tmpUrl . $session->preview_filename;
    }
    
    public function postAction()
    {
    	// switch to AJAX responce
    	$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->initContext('json');
        
        /**
    	 * Save data to database and retrieve record ID
    	 */
		$data = $this->_request->getPost();
		$Problems = new Search_Model_Problems();
		
		// to allow user to drop the report during some time (~15 minutes) after it was added
		// we should generate a hash value - a special password to drop records from database
		$dropHash = md5(microtime());
		$this->view->dropHash = $dropHash;
		$this->view->data = $data;
		
		// read image URL file from session
		$session = new Zend_Session_Namespace('file'); 
		
		try {
			$elem = $Problems->createItemFromFile(
				trim($data['msg']),
				$data['long'], 
				$data['lat'],
				$data['type'],
				$dropHash,
				$session->filename
			);
		} catch (Search_Model_ProblemsException $e) {
			$this->view->fail_message = $e->getMessage();
			// QUICK FIX: we need to create an error reporting mechanism
			mail(ADMIN_EMAIL, '[POVIDOM-VLADU] WEB Report FAIL at povidom-vladu.org.ua', $e->getMessage());
			return;
		}
		
		// remove files after new report was successfully saved into database
		unlink($session->filename);
		unlink($session->preview_file);
		
		// session cleanup
		unset($session->filename);
		unset($session->preview_file);
		$this->view->success = true;
		
		// get city and category information
		$Cities = new Searchdata_Model_Cities();
		$cityInfo = $Cities->find($elem->city_id)->current();

		$Categories = new Searchdata_Model_Category();
		$categoryInfo = $Categories->find($elem->category_id)->current();
		
		// generate URL to new report
		$this->view->url = $this->view->absoluteUrl(array('city' => $cityInfo->seo_name_uk, 'problem' => $categoryInfo->seo_name, 'id' => $elem->id) ,'city_problem_item');
    }
}