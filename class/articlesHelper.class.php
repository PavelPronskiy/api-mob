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
	 * data constructor
	 * @param type $objects
	 * @return type data
	 */

	static function getArticles($objects)
	{
		if (isset($objects->pathRoute))
		{
			switch($objects->pathRoute)
			{
				case "brief": 			self::getBriefData($objects); break;
				case "content": 		self::getContentData($objects); break;
				case "timeline": 		self::getTimeLine($objects);  break;
				default:
					throw new CodesExceptionHandler(1009);

			}
		}
	}


	/**
	 * brief by id json
	 * @param type $contentId
	 * @param type $params 
	 * @return type
	 */
	private static function getBriefData($data)
	{

		$K2HelperReturn = K2Helper::getK2ContentById($data->contentId);
		$data->objectList[] = $K2HelperReturn;

		if ($K2HelperReturn)
			dataModelViewer::dataView($data);
		else
			throw new CodesExceptionHandler(1009);

	}


	/**
	 * by id fulltext html
	 * @param type $contentId
	 * @param type $params 
	 * @return type
	 */
	private static function getContentData($data)
	{

		$K2HelperReturn = K2Helper::getK2ContentById($data->contentId);
		$data->objectList = $K2HelperReturn;
		
		if ($K2HelperReturn)
			dataModelViewer::dataView($data);
		else
			throw new CodesExceptionHandler(1009);

	}


	/**
	 * timeline model
	 * @param type $params 
	 * @return type
	 */
	private static function getTimeLine($data)
	{
		if (isset($data->categoryId))
			$data->objectList = K2Helper::getK2TimeLineObjects($data);


		if (isset($data->objectList))
			return dataModelViewer::dataView($data);
	}

}

?>