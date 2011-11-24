<?php

class SyncController extends Custom_Controller_Action 
{
	protected $_rest = null;
	
	public function init()
	{
		$this->_rest['gpu'] = array(
			'url' => 'http://gpu.org.ua/instantcms.php',
			'token' => 'QQQWWWEEERRRTTYYYYUUUUIIIOOO'
		);
	}
	
	public function indexAction()
	{
		$Problems = new Search_Model_Problems();
		$this->view->problemsList = $Problems->getLastItems(999);
	}
	
	public function processGpuAction()
	{
		$request = $this->getRequest();
		$Problems = new Search_Model_Problems();
		
		$problem = $Problems->getReportById($request->getParam('id'));
		
		$Service = new Custom_Gpuorgua();
		$this->view->responce = $Service->send($problem);
	}

}