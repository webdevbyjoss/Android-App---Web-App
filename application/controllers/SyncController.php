<?php

class SyncController extends Custom_Controller_Action 
{
	protected $_rest = null;
	
	public function __init()
	{
		$this->_rest['gpu'] = array(
			'url' => 'http://dev.gpu.org.ua/instantcms.php',
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
		
		// send HTTP post data
		$client = new Zend_Http_Client($this->_rest['gpu']);
		
		// set POST params
		$client->setParameterPost(array(
		    'title'  => $problem->msg,
		    'token'   => $this->_rest['token'],
		    'cat_id' => $problem->category_id,
		    'addr_lat' => $problem->lat,
		    'addr_lng' => $problem->lng,
		));
		
		// Uploading an existing file
		$filename = realpath(APPLICATION_PATH . '/../public_html/images/problem' ) . '/' . $problem->photo;
		$client->setFileUpload($filename, 'imgfile');
		
		// Send the files
		$client->request('POST');
		
		// new email report
		$toList = array(
	       	'joseph.chereshnovsky@gmail.com' => 'Joseph Chereshnovsky',
            'oleksiy.oliynyk@gmail.com' => 'Олексій Олійник',
            // 'atom@mail.ua' => 'Артему для gpu.org.ua',  // Artem from gpu.org.ua
        );
                
        $mail = new Zend_Mail('UTF-8');
        $mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);

		$mail->setBodyText("Привіт!

Прийшов новий звіт з мобільного.
		
Категорія: " . $catTitle . "
Опис: " . $problem->msg . "

Координати:
- широта: " . $problem->lat . "
- довгота: " . $problem->lng . "
Посилання на Карти Google:
" . $this->gMapLink($problem->lat, $problem->lng)
);

		// attach image to email
		$myImage = file_get_contents(realpath(APPLICATION_PATH . '/../public_html/images/problem' ) . '/' . $problem->photo);
		$at = new Zend_Mime_Part($myImage);
		$at->type = 'image/jpeg';
		$at->disposition = Zend_Mime::DISPOSITION_INLINE;
		$at->encoding = Zend_Mime::ENCODING_BASE64;
		$at->filename = $problem->photo;
		$mail->addAttachment($at);
	    
	    $mail->setFrom('joseph.chereshnovsky@gmail.com', 'Joseph Chereshnovsky');
	    
	    foreach ($toList as $email => $name) {
	    	$mail->addTo($email, $name);
	    }
	    
	    $mail->setSubject('Новий звіт з мобільного');
	    $mail->send();
	    
	    // $problem->sync_gpu = 1;
	    $problem->save();
	}
	
	protected function gMapLink($lat, $lng)
	{
		return 'http://maps.google.com/maps?f=q&hl=uk&q=' . $lat . ',' . $lng 
			 . '&sll=' . $lat . ',' . $lng . '&ll=' . $lat . ',' . $lng . '&z=19';
	}
}