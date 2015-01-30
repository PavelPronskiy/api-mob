<?


class doctorsModelHelper
{

	function __construct()
	{
		$options = '';
		$this->RatingSQLHelper = new RatingSQLHelper();
		//print_r($this->RatingSQLHelper);
	}

	/**
	 * get doctor by nid
	 * @param type 
	 * @return type
	 **/

	static function getDoctorByNid($nid)
	{
		$db = JFactory::getDBO();

		$sql = "SELECT * FROM #__doctors_rating
		WHERE nid = {$nid}";

		$db->setQuery($sql);
		$ret = $db->loadObject();
		$out = isset($ret->nid) ? $ret : false;
		return $out;
	}

	/**
	 * get doctor by id
	 * @param type 
	 * @return type
	 **/

	function getDoctorById($id)
	{
		$db = JFactory::getDBO();
		$sql = "SELECT a.id, a.nid, a.fio FROM #__doctors_rating AS a
		WHERE a.id = {$id}";


		$db->setQuery($sql);
		$ret = $db->loadObject();
		$out = isset($ret->nid) ? $ret : false;
		return $out;
	}


	/**
	 * get all doctors array objects
	 * @param type 
	 * @return type
	 **/

	static function getAllDoctors()
	{
		$db = JFactory::getDBO();

		$sql = "SELECT * FROM #__doctors_rating
		ORDER BY createdAt ASC";

		$db->setQuery($sql);
		$ret = $db->loadObject();

		if (isset($ret->nid))
			return $ret;
		else
			return false;
	}

	function getDoctorsTimeLineObjects($objects)
	{
		$db = JFactory::getDBO();
		$sqlQueryParams = '';
		$sqlQueryImportant = '';
		$maxIdTimeline = MAX_ID_TIMELINE;

		if (!isset($objects->pathParams->count))
			$objects->pathParams->count = MAX_COUNT_TIMELINE;

		/* max count = 100 */
		if ($objects->pathParams->count > COUNT_LIMIT_TIMELINE)
			$objects->pathParams->count = MAX_COUNT_TIMELINE;

		if (isset($objects->pathParams->since_id))
			$sqlQueryParams = "a.nid < ".$objects->pathParams->since_id;

		if (isset($objects->pathParams->max_id))
			$sqlQueryParams = "a.nid > ".$objects->pathParams->max_id;

		if (isset($objects->pathParams->since_id) && isset($objects->pathParams->max_id))
			$sqlQueryParams = "a.nid > ".$objects->pathParams->since_id." AND a.nid < ".$objects->pathParams->max_id;

		if (!isset($objects->pathParams->since_id) && !isset($objects->pathParams->max_id))
			$sqlQueryParams = '';


		// sort by since_id > 0 AND max_id == 0
		if (isset($objects->pathParams->max_id) && isset($objects->pathParams->since_id))
		{
			if ( ($objects->pathParams->max_id == 0) && ($objects->pathParams->since_id > 0) )
				$sqlQueryParams = "a.nid < {$objects->pathParams->since_id}"; // sort by since_id
		}

		if (isset($objects->pathParams->since_id) && $objects->pathParams->since_id == '-1')
			$sqlQueryParams = "a.nid < {$maxIdTimeline}"; // sort by since_id

		// sql query collect
		$sql = "SELECT 
		*
		FROM #__doctors_rating AS a
		WHERE {$sqlQueryParams}
		ORDER BY a.nid DESC
		LIMIT 0,{$objects->pathParams->count}";

		$objects->sqlQueryReturn = $db->setQuery($sql);
		$objects->objectList = $db->loadObjectList(); 

		if ($objects->objectList)
			return $objects->objectList;
	}


	/**
	 * get data
	 * @param type 
	 * @return type
	 */
	function getDoctors($objects)
	{
		switch ($objects->pathRoute)
		{
			case "bio":
				$objects->objectList = $this->RatingSQLHelper->getDoctorBio($objects);
				if (isset($objects->objectList->entity_id))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1009);
			break;
			case "brief":
				$objects->objectList[] = self::getDoctorByNid($objects->contentId);
				if (isset($objects->objectList{0}->id))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1009);
			break;
			case "timeline":
				$objects->objectList = self::getDoctorsTimeLineObjects($objects);
				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
			break;
			case "feedbacks":
				$objects->objectList = $this->RatingSQLHelper->getDoctorsFeedbacksTimeLine($objects->contentId)->data;

				if (isset($objects->objectList) && is_array($objects->objectList))
					dataModelViewer::dataView($objects);
				else
					throw new CodesExceptionHandler(1010);
			break;
		}
	}
}

?>