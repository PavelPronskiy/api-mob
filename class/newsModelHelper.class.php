<?

class newsModelHelper
{

	/**
	 * data constructor news
	 * @param type $objects
	 * @return type data
	 */

	public static function getNews($objects)
	{
		if (isset($objects->pathRoute))
		{
			switch($objects->pathRoute)
			{
				case "brief": 			self::getBriefData($objects); break;
				case "content": 		self::getContentData($objects);  break;
				case "timeline": 		self::getTimeLine($objects);  break;
				default:
					throw new CodesExceptionHandler(1009);

			}
		}
	}


	/**
	 * news brief by id json
	 * @param type $newsId
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
	 * news by id fulltext html
	 * @param type $newsId
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
	 * @param type $newsId
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