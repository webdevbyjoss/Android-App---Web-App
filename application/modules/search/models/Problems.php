<?php
/**
 * This class will handle all bussines login related to problems reported by user
 */
class Search_Model_Problems extends Zend_Db_Table_Abstract
{
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
		$select->joinLeft('category', 'problems.category_id = category.id', 'seo_name');
		$select->order('id DESC');
		return new Zend_Paginator_Adapter_DbSelect($select);
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
	public function createItem($message, $lng, $lat, $type)
	{
		// validate message
		if (strlen($message) < 5) {
			throw new Search_Model_ProblemsException('Message is too short. Should be at least 5 characters. Received: ' . $message);
		}

		// if coordinates are passed then lets try to recognize the city
		if (empty($lng) || empty($lat) ){
			throw new Search_Model_ProblemsException('Coordinates are empty. Received lng:' . $lng .' and lat:' . $lat );
		}
		
		// try to recognize city
		$Cities = new Searchdata_Model_Cities();
		$currentCity = $Cities->getCityByCoords($lng, $lat);
		
		// throw exception if city wasn't recognized
		if (empty($currentCity->city_id)) {
			throw new Search_Model_ProblemsException('City not found. Received lng:' . $lng .' and lat:' . $lat);
		}

		$row = $this->createRow();
		$row->msg = $message;
		$row->lng = $lng;
		$row->lat = $lat;
		$row->category_id = $type;
		
		$row->created = new Zend_Db_Expr('NOW()');
		$row->updated = new Zend_Db_Expr('NOW()');
		
		$row->city_id = $currentCity->city_id;
		
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