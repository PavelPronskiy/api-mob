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

				if (!isset($objects->pathParams->since_hits))
					$objects->pathParams->since_hits = CLINICS_SINCE_HITS_TIMELINE;


				if ($objects->pathParams->count > COUNT_LIMIT_TIMELINE)
					$objects->pathParams->count = MAX_COUNT_TIMELINE;


				if (isset($objects->pathParams->since_id))
					$sqlTimeline = " AND a.id < ".$objects->pathParams->since_id;

				if (isset($objects->pathParams->max_id))
					$sqlTimeline = " AND a.id > ".$objects->pathParams->max_id;

				if (isset($objects->pathParams->since_id) && isset($objects->pathParams->max_id))
					$sqlTimeline = " AND a.id > ".$objects->pathParams->since_id." AND a.id < ".$objects->pathParams->max_id;

				if (isset($objects->pathParams->since_id) && isset($objects->pathParams->max_id) && isset($objects->pathParams->since_hits))
					$sqlTimeline = " AND a.id > ".$objects->pathParams->since_id." AND a.id < ".$objects->pathParams->max_id." OR a.hits < ".$objects->pathParams->since_hits;


				if (!isset($objects->pathParams->since_id) && !isset($objects->pathParams->max_id))
					$sqlTimeline = '';

				$objects->objectList = K2Helper::getCategoryTree($objects);

				foreach($objects->objectList as $object)
					$itemIdArray[] = $object->id;

				$itemIdImplode = implode(',', $itemIdArray);

				$sql = "SELECT 
					a.id, a.alias, a.catid, a.title, a.introtext,
					a.created, a.modified, a.featured, a.hits,
					a.extra_fields, a.gallery, c.name AS catName
				FROM #__k2_items";

				$sql .= " AS a LEFT JOIN #__k2_categories AS c ON (a.catid=c.id)";
				$sql .= " WHERE a.catid IN (".$itemIdImplode.")";
				$sql .= " AND a.published=1 {$sqlTimeline}";
				$sql .= " ORDER BY a.hits DESC LIMIT 0,{$objects->pathParams->count}";

				$objects->sqlQueryReturn = $db->setQuery($sql);
				$objects->objectList = $db->loadObjectList();

				// header('Content-Type: application/json');
				// print_r($objects);

				if ($objects->objectList)
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1010);


			break;
		}


	}




}

?>