<?php

class Searchdata_Model_Category extends Zend_Db_Table_Abstract
{
    protected $_name = 'category';
    
    /**
     * Returns the list of availablle categories
     */
    public function getAllItems()
    {
    	return $this->fetchAll();
    }
    
    /**
     * Get category by SEO name
     *
     * @param string $seoName
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getBySeoName($seoName)
    {
    	$select = $this->select();
    	$select->where('seo_name = ?', $seoName);
    	
    	$data = $this->fetchAll($select);

    	if (0 == count($data)) {
    		return null;
    	}
    	
    	return $data->current();
    }

}
    