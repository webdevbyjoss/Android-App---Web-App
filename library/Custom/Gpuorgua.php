<?php

class Custom_Gpuorgua 
{
	const SERVICE_URL = 'http://gpu.org.ua/instantcms.php';
	const SERVICE_TOKEN = 'QQQWWWEEERRRTTYYYYUUUUIIIOOO';
	
	public function send($problem)
	{
		// send HTTP post data
		$client = new Zend_Http_Client(self::SERVICE_URL);
		
		// set POST params
		$client->setParameterPost(array(
		    'title'  => $problem->msg,
		    'token'   => self::SERVICE_TOKEN,
		    'cat_id' => $problem->category_id,
		    'addr_lat' => $problem->lat, // TODO: just to check if coordinates are OK
		    'addr_lng' => $problem->lng,
		));
		
		// Uploading an existing file
		$filename = realpath(APPLICATION_PATH . '/../public_html/images/problem' ) . '/' . $problem->photo;
		$client->setFileUpload($filename, 'imgfile');
		
		// Send information to remote server
		$response = $client->request('POST');
		
		// lets notify the administrator of the server
		// and possibly some other people
		$toList = array(
	       	'joseph.chereshnovsky@gmail.com' => 'Joseph Chereshnovsky',
            'oleksiy.oliynyk@gmail.com' => 'Олексій Олійник',
            // 'atom@mail.ua' => 'Артему для gpu.org.ua',  // Artem from gpu.org.ua
        );
                
        $mail = new Zend_Mail('UTF-8');
        $mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);

		$mail->setBodyText("Привіт!

Прийшов новий звіт з мобільного.
		
Категорія: " . $problem->msg . "
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
	    
	    // update database to mark this record as processed
	    // $problem->sync_gpu = 1;
	    $problem->save();
	    
	    return $response->getRawBody();
	}
	
	protected function gMapLink($lat, $lng)
	{
		return 'http://maps.google.com/maps?f=q&hl=uk&q=' . $lat . ',' . $lng 
			 . '&sll=' . $lat . ',' . $lng . '&ll=' . $lat . ',' . $lng . '&z=19';
	}
}