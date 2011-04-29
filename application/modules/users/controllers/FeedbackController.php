<?php
/**
 * Controller for users feedbacks
 *
 * @version		0.0.1
 * @see			http://webdevbyjoss.blogspot.com/
 * @author		Andrew Gonchar <gonandriy@gmail.com>
 * @copyright	2010
 * @license		GPL
 */
class Users_FeedbackController extends Zend_Controller_Action
{
	public function postAction()
	{
		$this->_helper->layout->disableLayout();

		$req = $this->getRequest();
		
		$category = $req->getParam('category');
		if (is_array($category)) {
			$category = $category[0];
		}
		
		switch ($category) {
			case "1": $category = "errors on site"; break;
			case "2": $category = "search fail"; break;
			case "3": $category = "too slow"; break;
			case "4": $category = "make better"; break;
			case "5": $category = "partnership"; break;
			case "6": $category = "affiliate"; break;
		}

		$message = 'Category: ' . $category . "\n\n" .
		$req->getParam("message") . "\n\n" . 
		'Email: ' . $req->getParam("email");
		
		mail(ADMIN_EMAIL, '[POVIDOM-VLADU] Feedback form message', $message);
	}
}