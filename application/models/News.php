<?php

class Default_Model_News extends Zend_Db_Table_Abstract 
{
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $_name = 'news';
	
	public function getLatestNews($count = 3)
	{
		$select = $this->select();
		$select->order('date DESC');
		$select->limit($count);
		
		return $this->fetchAll($select);
	}
	
}