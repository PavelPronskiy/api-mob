<?

class RatingSQLHelper
{

	function __construct()
	{
		$option = array();
		$option['driver']   = 'mysqli';
		$option['host']     = DB_HOSTNAME;
		$option['user']     = DB_USERNAME;
		$option['password'] = DB_PASSWORD;
		$option['database'] = DB_NAME;
		$option['prefix']   = '';

		$db = JDatabase::getInstance($option);
		$this->db = $db;
		$this->dataReturned = new stdClass();
		$ret = new stdClass();
		$this->K2Helper = new K2Helper();
	}

	/*
	*
	* require getDoctorsData
	*/
	function getDoctorsFeedbacks($nid)
	{

		$sql = "SELECT
			a.cid AS id,
			a.created AS createdAt,
			a.changed AS updatedAt,
			a.subject AS author,
			b.comment_body_value AS text,
			c.field_reviews_type_value AS reviews
		FROM comment AS a
		LEFT JOIN field_data_comment_body AS b
		ON (a.cid=b.entity_id)
		LEFT JOIN field_data_field_reviews_type AS c
		ON (a.cid=c.entity_id)
		WHERE a.nid = {$nid}
		AND a.status = '1'
		ORDER BY a.changed ASC";

		$this->db->setQuery($sql);
		$ret->data = $this->db->loadObjectList();
		$ret->count = count($ret->data);

		return $ret;
	}

	/*
	*
	* require getDoctorsData
	*/
	function getDoctorsFeedbacksTimeLine($nid)
	{

		$maxIdTimeline = MAX_ID_TIMELINE;
		$sqlQueryParams = '';
		$countLimit = isset($objects->pathParams->count) ? $objects->pathParams->count : MAX_COUNT_TIMELINE;

		// sort by since_id > 0 AND max_id == 0
		if (isset($objects->pathParams->max_id) && isset($objects->pathParams->since_id))
		{
			if ( ($objects->pathParams->max_id == 0) && ($objects->pathParams->since_id > 0) )
				$sqlQueryParams = "AND b.cid < {$objects->pathParams->since_id}"; // sort by since_id
		}

		if (isset($objects->pathParams->since_id) && $objects->pathParams->since_id == '-1')
			$sqlQueryParams = "AND b.cid < {$maxIdTimeline}"; // sort by since_id


		$sql = "SELECT
			a.cid AS id,
			a.created AS createdAt,
			a.changed AS updatedAt,
			a.subject AS author,
			b.comment_body_value AS text,
			c.field_reviews_type_value AS reviews
		FROM comment AS a
		LEFT JOIN field_data_comment_body AS b
		ON (a.cid=b.entity_id)
		LEFT JOIN field_data_field_reviews_type AS c
		ON (a.cid=c.entity_id)
		WHERE a.nid = {$nid}
		AND a.status = '1'
		{$sqlQueryParams}
		ORDER BY a.changed DESC
		LIMIT 0,{$countLimit}";

		$this->db->setQuery($sql);
		$ret->data = $this->db->loadObjectList();
		$ret->count = count($ret->data);


		return $ret;
	}


	function getDoctorBio($objects)
	{
		$sql = "SELECT 
			a.entity_id,
			a.field_doctor_experience_value AS experience
		FROM field_data_field_doctor_experience AS a
		WHERE a.entity_id = {$objects->contentId}";

		$ret->conn = $this->db->setQuery($sql);
		$ret->data = $this->db->loadObject();

		if (isset($ret->data->entity_id))
		{
			$ret->newdata->entity_id = $ret->data->entity_id;
			$ret->newdata->introtext = $ret->data->experience;
			return $ret->newdata;
		}
		else
		{
			return false;
		}
	}


	
	/**
	 * get feedbacks (отзывы)
	 */
	public function getFeedbacks($objects)
	{

		$maxIdTimeline = MAX_ID_TIMELINE;
		$sqlQueryParams = '';
		$countLimit = isset($objects->pathParams->count) ? $objects->pathParams->count : MAX_COUNT_TIMELINE;

		// sort by since_id > 0 AND max_id == 0
		if (isset($objects->pathParams->max_id) && isset($objects->pathParams->since_id))
		{
			if ( ($objects->pathParams->max_id == 0) && ($objects->pathParams->since_id > 0) )
				$sqlQueryParams = "AND b.cid < {$objects->pathParams->since_id}"; // sort by since_id
		}

		if (isset($objects->pathParams->since_id) && $objects->pathParams->since_id == '-1')
			$sqlQueryParams = "AND b.cid < {$maxIdTimeline}"; // sort by since_id


		$sql = "SELECT
			b.cid AS id,
			b.subject AS author,
			c.comment_body_value AS `text`,
			d.field_app_status_value AS status_review,
			b.created AS created,
			b.changed AS modified
		FROM idevels_probirka_integration AS a
 		LEFT JOIN comment AS b ON (a.nid=b.nid)
 		LEFT JOIN field_data_comment_body AS c ON (b.cid=c.entity_id)
 		LEFT JOIN field_data_field_app_status AS d ON (b.cid=d.entity_id)
		WHERE a.id = {$objects->contentId}
		AND b.status = '1'
		{$sqlQueryParams}
		ORDER BY b.created DESC
		LIMIT 0,{$countLimit}";

		$this->db->setQuery($sql);
		$this->db->return = $this->db->loadObjectList();

		return $this->db->return;

	}

}

?>