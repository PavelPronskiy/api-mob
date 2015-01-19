<?


class clinicsModelHelper
{


	static function getClinicsCategoriesId($data)
	{
		$idArr = K2Helper::getCategoryTree($data);
		foreach($idArr as $val)
			$arr[] = $val->id;

		$imArr = implode(',', $arr);

		return $imArr;

	}

	/**
	 * get rating clinics by id
	 * @param type 
	 * @return type
	 */

	static function getClinicsRating($clinic_id)
	{
		$db = &JFactory::getDBO();

		$sql = "SELECT 
			a.clinic_id, a.clinic_nid, a.currentValue, a.dailyChange
		FROM #__clinics_rating AS a 
		LEFT JOIN #__k2_categories AS c ON (a.clinic_id=c.id)
		WHERE a.clinic_id={$clinic_id}";

		$db->setQuery($sql);
		return $db->loadObject();
	}

	/**
	 * get all rating clinics by id
	 * @param type 
	 * @return type
	 */

	static function getAllClinicsRating()
	{
		$db = &JFactory::getDBO();

		$sql = "SELECT 
			a.clinic_id, a.clinic_nid, a.currentValue, a.dailyChange
		FROM #__clinics_rating AS a 
		LEFT JOIN #__k2_categories AS c ON (a.clinic_id=c.id)
		ORDER BY a.clinic_id";

		$db->setQuery($sql);
		return $db->loadObjectList();
	}


	/**
	 * get regions by clinics
	 * @param type 
	 * @return type
	 */
	static function getRegions($objects)
	{
		$objects->objectList = K2Helper::getCategoryTree($objects);
		if ($objects->objectList)
			dataModelViewer::dataView($objects);
		else
			throw new CodesExceptionHandler(1009);
	}


	/**
	 * get regions by clinics
	 * @param type 
	 * @return type
	 */
	static function getClinics($objects)
	{
		$db = &JFactory::getDBO();

		switch ($objects->pathRoute)
		{
			case "about":
				$objects->objectList = K2Helper::getK2ContentById($objects->contentId);
				if (isset($objects->objectList->id))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1009);
			break;
			case "brief":
				$objects->objectList[] = K2Helper::getK2ContentById($objects->contentId);
				if (isset($objects->objectList{0}->id))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1009);
			break;
			case "timeline":

				$objects->objectsCategory = K2Helper::getCategoryTree($objects);

				foreach($objects->objectsCategory as $object)
					$itemIdArray[] = $object->id;

				$objects->SQL_ClinicsCategories_ID = implode(',', $itemIdArray);
				$objects->objectList = K2Helper::getK2TimeLineObjects($objects);

				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
			break;
			case "feedbacks":
				$options = '';
				$RatingSQLHelper = new RatingSQLHelper($options);
				$objects->objectList = $RatingSQLHelper->getFeedbacks($objects);
				
				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1010);
			break;
		}
	}
}

?>