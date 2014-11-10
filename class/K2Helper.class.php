<?

/**
 * K2 addition
 * @package default
 */


class K2Helper
{
	static function getCategoryIdByAlias($catAlias)
	{

		$db = &JFactory::getDBO();

		$sql = "SELECT `id` FROM #__k2_categories";
		$sql .= " WHERE alias='".$catAlias."'";
		$sql .= " AND published='1'";

		$db->setQuery($sql);
		$return = $db->loadResult();
		
		// empty exception
		if ($return)
			return $return;
		else
			throw new CodesExceptionHandler(1006);
		
	}


	static function getCategoryTree($data)
	{

		$db = &JFactory::getDBO();

		if (!$data->categoryId)
		throw new CodesExceptionHandler(1006);

		$sql = "SELECT 
			a.id, a.name, a.alias, count(b.id) as count
		FROM
			#__k2_categories a
		LEFT JOIN 
			#__k2_items b
		ON
			a.id=b.catid
		WHERE
			a.parent={$data->categoryId}
		AND
			a.published=1
		GROUP BY
			a.id";

		$data->sqlQueryReturn = $db->setQuery($sql);
		$data->objectList = $db->loadObjectList();

		if ($data->objectList)
			return $data->objectList;
		else
			throw new CodesExceptionHandler(1006);

		
	}


	static function getGallery($id)
	{

		$galleryPath = K2_GALLERY_PATH.$id;

		if (is_dir(JPATH_BASE.$galleryPath))
		{
			$files = scandir(JPATH_BASE.$galleryPath); 

			foreach ($files as $key => $file) 
			{ 
				if (!in_array($file, array(".","..")))
				{ 
					$items[] = HOSTNAME.K2_GALLERY_PATH.$file;
				} 
			} 
		}

		if ($items)
			return $items;
		else
			return false;
	}

	static function getK2ContentById($id)
	{
		$db = &JFactory::getDBO();

		$sql = "SELECT 
			a.id, a.alias, a.catid, a.title, a.introtext, a.fulltext,
			a.gallery, a.extra_fields, a.hits, a.created, a.modified,
			c.name AS catName
		FROM #__k2_items AS a 
		LEFT JOIN #__k2_categories AS c ON (a.catid=c.id)
		WHERE a.id=$id
		AND a.published=1";

		$db->setQuery($sql);
		return $db->loadObject();
	}

	static function getK2TimeLineObjects($data)
	{
		$db = &JFactory::getDBO();
		$sqlQueryParams = '';

		/* max count = 100 */
		if ($data->pathParams->count > COUNT_LIMIT_TIMELINE)
			$data->pathParams->count = MAX_COUNT_TIMELINE;

		if (isset($data->pathParams->since_id))
			$sqlQueryParams = " AND a.id < ".$data->pathParams->since_id;

		if (isset($data->pathParams->max_id))
			$sqlQueryParams = " AND a.id > ".$data->pathParams->max_id;

		if (isset($data->pathParams->since_id) && isset($data->pathParams->max_id))
			$sqlQueryParams = " AND a.id > ".$data->pathParams->since_id." AND a.id < ".$data->pathParams->max_id;

		if (!isset($data->pathParams->since_id) && !isset($data->pathParams->max_id))
			$sqlQueryParams = '';


		$sql = "SELECT 
			a.id, a.alias, a.catid, a.title, a.introtext,
			a.created, a.modified, a.featured, a.hits,
			a.extra_fields, c.name AS catName
		FROM #__k2_items AS a";

		$sql .= " LEFT JOIN #__k2_categories AS c ON (a.catid=c.id)";

		switch($data->section)
		{
			case "clinics" :
				$sql .= " WHERE `catid` IN (".clinicsModelHelper::getClinicsCategoriesId($data).")";
				break;
			default:
				$sql .= " WHERE `catid`=".$data->categoryId;
				break;
		}
		
		$sql .= " AND a.published=1";
		$sql .= " {$sqlQueryParams}";
		$sql .= " ORDER BY a.id DESC";
		$sql .= " LIMIT 0,{$data->pathParams->count}";

		$data->sqlQueryReturn = $db->setQuery($sql);
		$data->objectList = $db->loadObjectList();

		if ($data->objectList)
			return $data->objectList;
	}


	static function getMappingTypes($data)
	{
		// mapping alias == id
		switch($data)
		{
			case "news": 			return NEWS_K2_CATEGORY_ID; 		// id from k2 categories -> news
			case "webinars": 		return WEBINARS_K2_CATEGORY_ID; 	// id from k2 categories -> webinars
			case "regions": 		return CLINIC_REGIONS_K2_CATEGORY_ID; 	// id from k2 categories -> clinics and regions
			case "clinics": 		return CLINIC_REGIONS_K2_CATEGORY_ID; 	// id from k2 categories -> clinics and regions
			default: 				return NULL;
		}
	}

	// returned extrafields by extrafield id
	static function getExtrafields($extraFieldId, $extraFields)
	{

		$data = json_decode($extraFields);
		$items = '';

		//print_r($data);

		if (!$data)
			return false;

		foreach($data as $k => $x)
		{
			if (isset($x->id))
			{
				if (isset($x->value) AND $x->id == $extraFieldId)
				{
					switch ($x->id)
					{
						case 1: if (isset($x->value) AND !empty($x->value)) $items = '"'.$x->value.'"'; break; // latitude
						case 2: if (isset($x->value) AND !empty($x->value)) $items = '"'.$x->value.'"'; break; // longitude
						case 5: if (isset($x->value) AND !empty($x->value)) $items = '"'.$x->value.'"'; break; // phoneNumber
						case 7: if (isset($x->value) AND !empty($x->value)) $items = '"'.$x->value.'"'; break; // address
						case 9: if (isset($x->value[1]) AND !empty($x->value[1])) $items = '"'.$x->value[1].'"'; break; // webURL
						case 11: if (isset($x->value) AND !empty($x->value)) $items = '"'.$x->value.'"'; break; // businessHours
						//default: $items = $x->value; break;
					}

					
				}
			}
		}

		return $items;
	}


}
?>