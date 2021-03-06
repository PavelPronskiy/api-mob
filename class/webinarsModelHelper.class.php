<?

class webinarsModelHelper
{

	/**
	 * data constructor news
	 * @param type $objects
	 * @return type data
	 */

	public static function getWebinars($objects)
	{
		if (isset($objects->pathRoute))
		{
			switch($objects->pathRoute)
			{
				case "brief": 			self::getBriefData($objects); break;
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