<?

/**
 * output data model & viewer
 * @param type $url
 * @return type parsed youtube id video
 */
class RatingHelper
{

	/**
	 * rating heper
	 * @param type $objects
	 * @return type item
	 */
	static function getRating($objects)
	{

		$item = new stdClass();
		$items = array();

		$item->currentValue = '0.0';
		$item->dailyChange = (int)0;

		return $item;

		//$countObjects = count($objects->objectList);
	}

}

?>