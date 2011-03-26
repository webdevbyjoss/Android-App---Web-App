<?php

class CatalogController extends Custom_Controller_Action
{
	/**
	 * The amount of results per page to show in search
	 */
	const RESULTS_PER_PAGE = 20;
	
	public function indexAction()
	{
		$request = $this->getRequest();
		// retrieve city information
		$citySeoName = $request->getParam('city');
		$city = $this->getCityInfo($citySeoName);
		$this->view->city = $city['city'];
		
		// retrieve services information
		$Categories = new Searchdata_Model_Category();
		$this->view->categories = $Categories->getAllItems();
		
		$problemSeoName = $request->getParam('problem');
		
		if (!empty($problemSeoName)) {
			$problem = $Categories->getBySeoName($problemSeoName);
			$this->view->problem = $problem;
		}

		// process search
		$problemsIndex = new Search_Model_Problems();
		
		if (!empty($problem)) {
			$pageadapter = $problemsIndex->getDataPagenation($city['city']->city_id, $problem->id);
		} else {
			$pageadapter = $problemsIndex->getDataPagenation($city['city']->city_id);
		}
		
		$pagination = new Zend_Paginator($pageadapter);
		$pagination->setCurrentPageNumber($request->page);
		$pagination->setDefaultItemCountPerPage(self::RESULTS_PER_PAGE);
		
		$this->view->data = $pagination;
	}
	
	public function itemAction()
	{
		$request = $this->getRequest();
		$itemId = $request->id;
		
		// retrieve city information
		$citySeoName = $request->getParam('city');
		$city = $this->getCityInfo($citySeoName);
		$this->view->cityTitle = $city['city']->name_uk;
		$this->view->citySeoTitle = $city['city']->seo_name_uk;
		
		$Problems = new Search_Model_Problems();
		$this->view->problem = $Problems->find($itemId)->current();
	}
	
	private function getCityInfo($cityTitle)
	{
		$Cities = new Searchdata_Model_Cities();
		$CitiesDistance = new Searchdata_Model_CitiesDistances();
		$city = $Cities->getCityBySeoName($cityTitle);
		$return['city'] = $city;
		if ($city->is_region_center == 0) {
			// FIXME: this should be uncommented in case there will be information for these cities
			// $return['city_near'] = $CitiesDistance->getNearCities($city->city_id);
		}
		$return['city_large'] = $CitiesDistance->getLargeCities($city->city_id);
		return $return;
	}

}