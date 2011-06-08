<?php
/**
 * This class will handle all bussines login related to problems reported by user
 */
class Search_Model_Problems extends Zend_Db_Table_Abstract
{
	/**
	 * The ammount of minutes user is able to drop records from database
	 */
	const DROP_TIME = 15;
	
	/**
	 * Means that this is deleted
	 */
	const IS_DELETED = 1;
	const IS_NOT_DELETED = 0;
	
	protected $_name = 'problems';
	
	/**
	 * Returns pagination adapter to paginate thrue data
	 *
	 * @param array $categoryId
	 * @return Zend_Paginator_Adapter_Interface
	 */
	public function getDataPagenation($cityId, $categoryId = null)
	{
		$select = $this->getAdapter()->select();
		$select->from('problems', array('id', 'msg', 'photo'));
		
		if ($categoryId !== null) {
			$select->where('category_id IN (' . $categoryId . ')');
		}
		$select->where('city_id = ?', $cityId);
		
		// exclude reports being removed
		$select->where('is_deleted = ?', self::IS_NOT_DELETED);
		
		$select->joinLeft('category', 'problems.category_id = category.id', 'seo_name');
		$select->order('id DESC');
		return new Zend_Paginator_Adapter_DbSelect($select);
	}
	
	/**
	 * Returns the report
	 * 
	 * @param int $id
	 * @return Zend_Db_Table_Row
	 */
	public function getreportById($id)
	{
		$select = $this->select();

		$select->where('id = ?', $id);
		// exclude reports being removed
		$select->where('is_deleted = ?', self::IS_NOT_DELETED);
		
		return $this->fetchAll($select)->current();
	}
	
	/**
	 * Returns last added items
	 * 
	 * @param int $amount
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function getLastItems($amount = 10)
	{
		$select = $this->getAdapter()->select();
		$select->from('problems', array('id', 'msg', 'photo'));
		
		// exclude reports being removed
		$select->where('is_deleted = ?', self::IS_NOT_DELETED);
		
		$select->limit($amount);
		$select->order('id DESC');
		$select->joinLeft('city', 'problems.city_id = city.city_id', array('city.seo_name_uk as city_seo_name', 'name_uk as name'));
		$select->joinLeft('category', 'problems.category_id = category.id', 'seo_name');
		
		return $this->getAdapter()->fetchAll($select);
	}
	
	/**
	 * Creates a new record in problems table and returns Zend_Db_Table_Row
	 * 
	 * @param string $message
	 * @param string $address
	 * @param string $lng
	 * @param string $lat
	 * @return Zend_Db_Table_Row
	 */
	public function createItem($message, $lng, $lat, $type, $dropHash)
	{
		// validate message
		if (strlen($message) < 5) {
			
		}

		// if coordinates are passed then lets try to recognize the city
		if (empty($lng) || empty($lat) ){
			throw new Search_Model_ProblemsException('Coordinates are empty. Received lat:' . $lat . 'lng:' . $lng );
		}
		
		// try to recognize city
		$Cities = new Searchdata_Model_Cities();
		$currentCity = $Cities->getCityByCoords($lng, $lat);
		
		// process the situation if city wasn't recognized using city-boundaries algorythm
		if (empty($currentCity)) {
			
			// try to find the closest available city and assign report to that city
			$relatedCities = $Cities->getRelatedCities($lat, $lng, 50); // max search range is 50km
			$currentCity = current($relatedCities);
			
			// throw new Search_Model_ProblemsException('City not found. Received lat:' . $lat . 'lng:' . $lng );
			// report exceptional situation to administrator
			mail(ADMIN_EMAIL, '[POVIDOM-VLADU] City-boundaries algorythm error', 
			'City not found using city-boundaries algorythm. Received lat:' . $lat . 'lng:' . $lng 
			. '. Assigned to "' . $currentCity['name_uk'] . '", city_id = "' . $currentCity['city_id'] . '"');
			
			$cityID = $currentCity['city_id'];
			
		} else {
			$cityID = $currentCity->city_id;
		}
		
		// add additional check to make sure that city was recognized correctly
		if (empty($cityID)) {
			throw new Search_Model_ProblemsException('City is not supported. lat:' . $lat . 'lng:' . $lng);
		}
		
		$row = $this->createRow();
		$row->msg = $message;
		$row->lng = $lng;
		$row->lat = $lat;
		$row->category_id = $type;
		$row->drophash = $dropHash;
		
		$row->created = new Zend_Db_Expr('NOW()');
		$row->updated = new Zend_Db_Expr('NOW()');
		
		$row->city_id = $cityID;
		
		// save record to generate item ID
		// we should begin transaction here to remove DB record if image upload fails 
		$db = $this->getAdapter();
		$db->beginTransaction();
		$row->save();
		
		// name photos
		$filename = $row->id. '.jpg';
		$filepath = realpath(APPLICATION_PATH . '/../public_html/images/problem');
		$imageFile = $filepath . '/' . $filename;
		$thumbFile = $filepath . '/thumb/' . $filename;

    	/**
    	 * Process image and save it into apropriate location with the mane ID.jpg
    	 */
		$upload = new Zend_File_Transfer_Adapter_Http();
		
		// Limit the extensions to jpg and png files
		$upload->addValidator('IsImage', false);
		$upload->addValidator('Extension', false, 'jpg,png');

		$upload->addFilter('Rename', array('target' => $imageFile));
		$res = $upload->receive();
		
		if ($res === false) {
			$messages = $upload->getMessages();
			$db->rollBack();
			throw new Search_Model_ProblemsException('Error during file upload.' . implode(' ', $messages));
		} else {
			// generate thumbnail
			$res = $this->resizeThumb($imageFile, $thumbFile, 150, 150);
			
			// check if thumbnail were generated successfully
			if (empty($res)) {
				$db->rollBack();
				throw new Search_Model_ProblemsException('Thumbs generation errors. Please contact support.');
			}
			
			$row->photo = $filename;
			$row->save();
		}
		
		$db->commit();
		return $row;
	}
	
	/**
	 * Drop
	 * 
	 * @param string $dropHash
	 */
	public function dropReport($dropHash)
	{
		$whereDrophash = $this->getAdapter()->quoteInto('drophash = ?', $dropHash);
		$whereTime = 'created >= NOW() - INTERVAL ' . self::DROP_TIME . ' MINUTE';
		
		$data = array(
			'is_deleted' => self::IS_DELETED,
		);
		
		$num = $this->update($data, array($whereDrophash, $whereTime));
		
		if (empty($num)) {
			throw new Search_Model_ProblemsException("Report with drophash #$dropHash can not me removed. Please contact support.");
		}
	}
	
	/**
	 * Resize image based on max width and height
     * 
     * @param integer $maxWidth
     * @param integer$maxHeight
     * @return resized image
     */
    public function resizeThumb($sourceFilename, $destinationFilename, $maxWidth = 70, $maxHeight = 70, $jpegQuality = 95)
    {
    	list($width, $height) = getimagesize($sourceFilename);

    	// late return if image is already resized
    	if ($width <= $maxWidth && $height <= $maxHeight) {
	        return;
        }
        
        // calculate real sizing
        // we will skip 20% from each side and resize the rest to fit in $maxWidth, $maxHeight
        $wSkip = ceil($width * 15 / 100);
        $hSkip = ceil($height * 15 / 100);
        
        $aspectRatio = $width / $height;
        
        // resize to fit the width
        if ($width > $height) {
        	
        	$newWidth = $height - $hSkip;
        	$newHeight = $height - $hSkip;
        	
        // resize to fit the height
        } elseif ($width < $height) {
        	
        	$newWidth = $width - $wSkip;
        	$newHeight = $width - $wSkip;

        	// resize to fit both sides
        } else {
        	$newWidth = $width - $wSkip;
        	$newHeight = $height - $hSkip;
      	}

        $newImage = imagecreatetruecolor($maxWidth, $maxHeight);
        $oldImage = imagecreatefromjpeg($sourceFilename);
        
        // imagecopyresized($newImage, $oldImage, 0, 0, $wSkip, $hSkip, $maxWidth, $maxHeight, $newWidth, $newHeight);
        imagecopyresampled($newImage, $oldImage, 0, 0, $wSkip, $hSkip, $maxWidth, $maxHeight, $newWidth, $newHeight);
        
        // save the image to destination file
        $res = imagejpeg($newImage, $destinationFilename, $jpegQuality);
        
        if ($res) {
        	return $destinationFilename;
        }
	}

}