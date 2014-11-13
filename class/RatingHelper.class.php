<?

/**
 * output data model & viewer
 * @param type $url
 * @return type parsed youtube id video
 */
class RatingHelper
{

	/**
	 * Выводит object с двумя значениями:
	 *   currentValue
	 *   dailyChange
	 */
	static function getRatingValues($clinic_id)
	{
		$item = new stdClass();
		$itemRating = clinicsModelHelper::getClinicsRating($clinic_id);
		$item->currentValue = isset($itemRating->currentValue) ? $itemRating->currentValue : 0;
		$item->dailyChange = isset($item->dailyChange) ? (int)$itemRating->dailyChange : 0;
		return $item;
	}

}

?>