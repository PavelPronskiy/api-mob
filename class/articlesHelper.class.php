<?


class articlesHelper
{

	/**
	 * get article categories
	 * @param type $excludeArray 
	 * @return type
	 */
	static function getArticleTypes($objects)
	{
		$db = &JFactory::getDBO();
		$sql = "SELECT `id`, `name`, `alias` FROM #__k2_categories";
		$sql .= " WHERE `id` IN (".ARTICLE_TYPES_ID.")";
		$sql .= " ORDER BY FIELD(id, ".ARTICLE_TYPES_ID.")";

		$objects->sqlQueryReturn = $db->setQuery($sql);
		$objects->objectList = $db->loadObjectList();

		dataModelViewer::dataView($objects);

	}

	/**
	 * article brief by id json
	 * @param type $articleId
	 * @param type $params 
	 * @return type
	 */
	static function getBriefData($data)
	{
		if (isset($data->contentId))
			$data->objectList[] = K2Helper::getK2ContentById($data->contentId);
		else
			throw new CodesExceptionHandler(1003);

		if ($data->objectList)
			dataModelViewer::dataView($data);
		else
			throw new CodesExceptionHandler(1009);
	}


	/**
	 * article by id fulltext html
	 * @param type $articleId
	 * @param type $params 
	 * @return type
	 */
	static function getContentData($data)
	{
		if (isset($data->contentId))
			$data->objectList = K2Helper::getK2ContentById($data->contentId);
		else
			throw new CodesExceptionHandler(1003);

		if ($data->objectList)
			dataModelViewer::dataView($data);
		else
			throw new CodesExceptionHandler(1009);
	}


	/**
	 * timeline model
	 * @param type $articleId
	 * @param type $params 
	 * @return type
	 */
	static function getTimeLine($data)
	{
		if (isset($data->categoryId))
			$data->objectList = K2Helper::getK2TimeLineObjects($data);

		if (isset($data->objectList))
			dataModelViewer::dataView($data);
	}

}

?>