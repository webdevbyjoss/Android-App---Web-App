<?php

class NewsController extends Custom_Controller_Action
{
	public function indexAction()
	{
		require_once APPLICATION_PATH . '/models/News.php';
		$News = new Default_Model_News();
		
		$newsList = $News->getLatestNews(100);
		$this->view->newsList = $newsList;
	}
}