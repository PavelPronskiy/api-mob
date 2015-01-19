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


	static function getCategoryById($id)
	{

		$db = &JFactory::getDBO();

		$sql = "SELECT * FROM #__k2_categories";
		$sql .= " WHERE id='".$id."'";
		$sql .= " AND published='1'";

		$db->setQuery($sql);
		$return = $db->loadObject();

		// empty exception
		if ($return)
			return $return;
	}


	static function getCategoryTree($objects)
	{

		$db = &JFactory::getDBO();

		if (!$objects->categoryId)
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
			a.parent={$objects->categoryId}
		AND
			a.published=1
		GROUP BY
			a.id";

		$objects->sqlQueryReturn = $db->setQuery($sql);
		$objects->objectList = $db->loadObjectList();

		if ($objects->objectList)
			return $objects->objectList;
		else
			throw new CodesExceptionHandler(1006);

		
	}


	static function getGallery($id)
	{

		$galleryPath = K2_GALLERY_PATH_ABSOLUTE.$id;

		if (is_dir($galleryPath))
		{
			$files = scandir($galleryPath); 

			foreach ($files as $key => $file) 
			{
				if (!in_array($file, array(".","..")))
				{ 
					$items[] = HTTP_IMG_HOSTNAME.K2_GALLERY_PATH.$id.'/'.$file;
				} 
			} 
		}

		if (isset($items))
			return $items;
		else
			return NULL;
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
		WHERE a.id={$id}
		AND a.published=1";

		$db->setQuery($sql);
		$loadObject = $db->loadObject();

		if ($loadObject === NULL)
			return false;
		else
			return $loadObject;
	}

	static function getK2TimeLineObjects($objects)
	{
		$db = &JFactory::getDBO();
		$sqlQueryParams = '';
		$sqlQueryImportant = '';
		$maxIdTimeline = MAX_ID_TIMELINE;
		$getImportantIDSArray = joomlaImports::getImportantIDSArray();

		if (!isset($objects->pathParams->count))
			$objects->pathParams->count = MAX_COUNT_TIMELINE;

		/* max count = 100 */
		if ($objects->pathParams->count > COUNT_LIMIT_TIMELINE)
			$objects->pathParams->count = MAX_COUNT_TIMELINE;

		if (isset($objects->pathParams->since_id))
			$sqlQueryParams = "AND a.id < ".$objects->pathParams->since_id;

		if (isset($objects->pathParams->max_id))
			$sqlQueryParams = "AND a.id > ".$objects->pathParams->max_id;

		if (isset($objects->pathParams->since_id) && isset($objects->pathParams->max_id))
			$sqlQueryParams = "AND a.id > ".$objects->pathParams->since_id." AND a.id < ".$objects->pathParams->max_id;

		if (!isset($objects->pathParams->since_id) && !isset($objects->pathParams->max_id))
			$sqlQueryParams = '';


		// sort by since_id > 0 AND max_id == 0
		if (isset($objects->pathParams->max_id) && isset($objects->pathParams->since_id))
		{
			if ( ($objects->pathParams->max_id == 0) && ($objects->pathParams->since_id > 0) )
				$sqlQueryParams = "AND a.id < {$objects->pathParams->since_id}"; // sort by since_id
		}

		if (isset($objects->pathParams->since_id) && $objects->pathParams->since_id == '-1')
			$sqlQueryParams = "AND a.id < {$maxIdTimeline}"; // sort by since_id


		// important ids not defined in module NEWS_IMPORTANT
		if (isset($getImportantIDSArray))
		{
			if (!is_array($getImportantIDSArray))
				$objects->pathParams->important = 0;
		}


		if (isset($objects->pathParams->important) && $objects->pathParams->important == 1)
		{
			$collectSQLImportant = implode(',', $getImportantIDSArray);
			$sqlWhere = "a.id IN ({$collectSQLImportant})";
		}
		else
		{
			if (isset($getImportantIDSArray))
			{
				$collectSQLImportant = implode(',', $getImportantIDSArray);
			}

			$sqlWhere = "a.catid={$objects->categoryId} AND NOT a.id IN ({$collectSQLImportant})";
		}
		
		// clinics listing by clinics categories
		if ( (isset($objects->SQL_ClinicsCategories_ID)) && !empty($objects->SQL_ClinicsCategories_ID) )
			$sqlWhere = "a.catid IN ({$objects->SQL_ClinicsCategories_ID})";

		// sql query collect
		$sql = "SELECT 
			a.id, a.alias, a.catid, a.title, a.introtext,
			a.created, a.modified, a.featured, a.hits,
			a.extra_fields, b.name AS catName
		FROM #__k2_items AS a
		LEFT JOIN #__k2_categories AS b ON (a.catid=b.id)
		WHERE {$sqlWhere} {$sqlQueryParams}
		AND a.published=1
		ORDER BY a.id DESC
		LIMIT 0,{$objects->pathParams->count}";

		$objects->sqlQueryReturn = $db->setQuery($sql);
		$objects->objectList = $db->loadObjectList();

		//header('Content-Type: application/json');
		//print_r($objects->sqlQueryReturn);


		if ($objects->objectList)
			return $objects->objectList;
	}

	static function getMappingTypes($objects)
	{

		// mapping alias == id
		switch($objects)
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
		$objects = json_decode($extraFields);
		$items = '';

		if (!$objects)
			return NULL;

		foreach($objects as $k => $x)
		{
			if (isset($x->id))
			{
				if (isset($x->value) AND $x->id == $extraFieldId)
				{
					switch ($x->id)
					{
						case 1: if (isset($x->value) 	AND !empty($x->value)) $items = $x->value; break; // latitude
						case 2: if (isset($x->value) 	AND !empty($x->value)) $items = $x->value; break; // longitude
						case 5: if (isset($x->value) 	AND !empty($x->value)) $items = $x->value; break; // phoneNumber
						case 7: if (isset($x->value) 	AND !empty($x->value)) $items = $x->value; break; // address
						case 9: if (isset($x->value[1]) AND !empty($x->value[1])) $items = preg_match('/^http:\/\/$/', $x->value[1]) ? $items = '' : $items = $x->value[1]; break; // webURL
						case 11: if (isset($x->value) 	AND !empty($x->value)) $items = $x->value; break; // businessHours
						//default: $items = $x->value; break;
					}
				}
			}
		}

		if ($items)
			return $items;
		else
			return 'нет информации';
	}


}
?>