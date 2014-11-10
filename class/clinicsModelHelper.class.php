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
		$data->objectList = K2Helper::getCategoryTree($objects);

		foreach($data->objectList as $object)
			$itemIdArray[] = $object->id;

		$itemIdImplode = implode(',', $itemIdArray);

		$sql = "SELECT 
			a.id, a.alias, a.catid, a.title, a.introtext,
			a.created, a.modified, a.featured, a.hits,
			a.extra_fields, c.name AS catName
		FROM #__k2_items";

		$sql .= " AS a LEFT JOIN #__k2_categories AS c ON (a.catid=c.id)";
		$sql .= " WHERE `catid` IN (".$itemIdImplode.")";
		$sql .= " AND a.published=1";
		$sql .= " ORDER BY a.hits DESC";

		$objects->sqlQueryReturn = $db->setQuery($sql);
		$objects->objectList = $db->loadObjectList();

		if ($objects->objectList)
			dataModelViewer::dataView($objects);
		else
			throw new CodesExceptionHandler(1010);



	}




}

?>