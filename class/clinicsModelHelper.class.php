<?


class clinicsModelHelper
{

	function getClinicsCategoriesId($data)
	{
		$idArr = K2Helper::getCategoryTree($data);

		if ($idArr)
		{
			foreach($idArr as $val)
				$arr[] = $val->id;

			$imArr = implode(',', $arr);
		}
		else
		{
			$imArr = false;
		}

		return $imArr;


	}

	/**
	 * get rating clinics by id
	 * @param type 
	 * @return type
	 */

	function getClinicsRating($clinic_id)
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

	function getAllClinicsRating()
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
	function getRegions($objects)
	{
		$objects->objectList = K2Helper::getCategoryTree($objects);
		if ($objects->objectList)
			dataModelViewer::dataView($objects);
		else
			throw new CodesExceptionHandler(1009);
	}

function sort_arr_of_obj($array, $sortby, $direction='asc') {

	$sortedArr = array();
	$tmp_Array = array();

	foreach($array as $k => $v)
		$tmp_Array[] = strtolower($v->$sortby);

	$direction=='asc' ? asort($tmp_Array, SORT_LOCALE_STRING) : arsort($tmp_Array, SORT_LOCALE_STRING); 

	foreach($tmp_Array as $k=>$tmp)
		$sortedArr[] = $array[$k];

	return $sortedArr;
}

	/**
	 * search clinics
	 * @param type 
	 * @return type
	 */

	static function sortSwitch($sortType)
	{
		switch($sortType)
		{
			case 'abc': return 'ORDER BY binary lower(a.title) ASC';
			case 'rating': return 'ORDER BY a.title DESC';
			case 'voice_num': return 'ORDER BY a.title DESC';
			case 'voice_date': return 'ORDER BY a.title DESC';
			case 'feedbacks_num': return 'ORDER BY a.title DESC';
		}
	}


	static function truncateSearchValues($value)
	{
		$value = substr($value, 0, 64);
		$value = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $value); // clear
		$value = trim(preg_replace("/\s(\S{1,2})\s/", " ", preg_replace("/ +/", "  "," {$value} ")));
		$value = preg_replace("/ +/", " ", $value);
		return $value;
	}


	/**
	 * search clinics by k2 items
	 * @param type 
	 * @return type
	 */
	function searchConstruct($objects)
	{

		if ($objects->categoryId == false)
		throw new CodesExceptionHandler(1010);

		if (!isset($objects->pathParams->count))
			$objects->pathParams->count = MAX_COUNT_TIMELINE;

		/* max count = 100 */
		if ($objects->pathParams->count > COUNT_LIMIT_TIMELINE)
			$objects->pathParams->count = MAX_COUNT_TIMELINE;


		$db = JFactory::getDBO();
		$std = new stdClass();
		$std->name = self::truncateSearchValues($objects->pathParams->name);
		$std->categories = self::getClinicsCategoriesId($objects);

		$std->where = $std->categories
			? " a.catid IN ({$std->categories})"
			: " a.catid={$objects->categoryId}";

		$std->where .= ' AND a.title LIKE "%' . str_replace(' ', '%" OR '.$std->where.' AND a.title LIKE "%', $std->name) . '%"';
		$std->order = self::sortSwitch($objects->pathParams->sort_type);


/*
			a.id, a.alias, a.catid, a.title, a.introtext,
			a.created, a.modified, a.featured, a.hits,
			a.extra_fields, b.name AS catName
*/

		$sql = "SELECT 
			a.id, a.alias, a.catid, a.title, a.introtext,
			a.created, a.modified, a.featured, a.hits,
			a.extra_fields, b.name AS catName
		FROM #__k2_items AS a
		LEFT JOIN #__k2_categories AS b ON (a.catid=b.id)
		WHERE {$std->where}
		AND a.published=1
		{$std->order} 
		LIMIT 0,{$objects->pathParams->count}";

		$std->selectQuery = $db->setQuery($sql);
		$std->objects = $db->loadObjectList();

		//print_r($std->selectQuery);


		switch($objects->pathParams->sort_type)
		{
			case 'abc': $std->return = self::sort_arr_of_obj($std->objects, 'title', 'asc'); break;
		}

		return $std->return;

		
	}

	/**
	 * get regions by clinics
	 * @param type 
	 * @return type
	 */
	static function getClinics($objects)
	{

		switch ($objects->pathRoute)
		{

			case "search":
				header('Content-Type: application/json');
				$objects->categoryId = K2Helper::getCategoryIdByAlias($objects->pathParams->region);
				$objects->objectList = self::searchConstruct($objects);
		
				//print_r($objects->objectList);
				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1010);
			break;
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

				print_r($objects->objectList);

				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
			break;
			case "feedbacks":
				$options = '';
				$RatingSQLHelper = new RatingSQLHelper();
				$objects->objectList = $RatingSQLHelper->getFeedbacks();
				
				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1010);
			break;
		}
	}
}

?>