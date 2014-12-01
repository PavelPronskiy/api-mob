<?

class RatingSQLHelper
{

	function __construct($options)
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
		$this->options = $options;
		$this->dataReturned = new stdClass();
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