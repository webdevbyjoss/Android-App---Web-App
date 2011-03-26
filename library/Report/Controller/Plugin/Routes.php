<?php

class Report_Controller_Plugin_Routes extends Zend_Controller_Plugin_Abstract
{
	public function routeStartup(Zend_Controller_Request_Abstract $request)
	{
		$translate = Zend_Registry::get('Zend_Translate');
        $locale = Zend_Registry::get('Zend_Locale');

        // we should take locale from the URL, in case it is presented there and then
        // WARNING: this is very tricky and possibly there is some kind of better solution somewhere
        if (!($request instanceof  Zend_Controller_Request_Http)) {
        	// QUICKFIX: we receiving error message while running from CLI
            // PHP Fatal error:  Call to undefined method Zend_Controller_Request_Simple::getRequestUri()
            return;
        }

        // Set language to global param so that our language route can
        // fetch it nicely.
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        // $router->removeDefaultRoutes();
        
        // SEO purposes
        // application URL to be indexed by search engine

        // http://domain-name.com/город/днепропетровск
        // http://domain-name.com/місто/заліщики
        $cityRoute = new Zend_Controller_Router_Route(
        	'@url-city/:city/*',
            array(
            'module'       => 'default',
            'controller' => 'catalog',
            'action'     => 'index'
            )
		);
        $router->addRoute('city', $cityRoute);

        // http://domain-name.com/город/днепропетровск/канализация/
        // http://domain-name.com/місто/заліщики/ями-на-дорогах/
        $cityServiceRoute = new Zend_Controller_Router_Route(
        	'@url-city/:city/:problem',
            	array(
                	'module'       => 'default',
                    'controller' => 'catalog',
                    'action'     => 'index'
				)
		);
        $router->addRoute('city_problem', $cityServiceRoute);

        
        // http://domain-name.com/город/днепропетровск/канализация/
        // http://domain-name.com/місто/заліщики/ями-на-дорогах/
        $cityServiceRoute = new Zend_Controller_Router_Route(
        	'@url-city/:city/:problem/@report/:id',
            	array(
                	'module'       => 'default',
                    'controller' => 'catalog',
                    'action'     => 'item'
				)
        );
        $router->addRoute('city_problem_item', $cityServiceRoute);
        
	}
}