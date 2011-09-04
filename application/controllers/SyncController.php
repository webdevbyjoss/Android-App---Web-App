<?php

class SyncController extends Custom_Controller_Action 
{
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
		
		
		// get category information
		// TODO: this is very pure code and needs to be revise to 
		// grap category inforamtion with LEFT JOIN
		// to avoid the additional query
		$Categories = new Searchdata_Model_Category();
		foreach ($Categories->getAllItems() as $cat) {
			if ($cat->id == $problem->category_id) {
				$catTitle = $cat->name; 
			}
		}
		
		$toList = array(
			'joseph.chereshnovsky@gmail.com' => 'Joseph Chereshnovsky',
			'oleksiy.oliynyk@gmail.com' => 'Олексій Олійник',
			// 'atom@mail.ua' => 'Артему для gpu.org.ua',  // Artem from gpu.org.ua
		);
		
		$mail = new Zend_Mail('UTF-8');
		$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
	    
		// new email report
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
	    
	    $problem->sync_gpu = 1;
	    $problem->save();
	}
	
	protected function gMapLink($lat, $lng)
	{
		return 'http://maps.google.com/maps?f=q&hl=uk&q=' . $lat . ',' . $lng 
			 . '&sll=' . $lat . ',' . $lng . '&ll=' . $lat . ',' . $lng . '&z=19';
	}
}