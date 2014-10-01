<?

class timelineModelViewer
{

	/**
	 * sql select params
	 * @param type $catid 
	 * @param type $params 
	 * @return type
	 */
	static function construct_TimelineSQLParams($catid, $params='')
	{

		$sql_timeline_params = '';

		if (!isset($params['count']))
		{
			$params['count'] = MAX_COUNT_TIMELINE;
		}
		else
		{
			/* max count = 100 */
			if ((int)$params['count'] && $params['count'] >= 100)
				$params['count'] = MAX_COUNT_TIMELINE;
		}

		if (isset($params['since_id']) && (int)$params['since_id'])
			$sql_timeline_params = " AND id < ".$params['since_id'];

		if (isset($params['max_id']) && (int)$params['max_id'])
			$sql_timeline_params = " AND id > ".$params['max_id'];

		if (isset($params['since_id']) && (int)$params['since_id'] &&
			isset($params['max_id']) && (int)$params['max_id'])
			$sql_timeline_params = " AND id > ".$params['since_id']." AND id < ".$params['max_id'];

		if (!isset($params['since_id']) && !isset($params['max_id']))
			$sql_timeline_params = '';



		$sql_select = "SELECT `id`, `alias`, `catid`, `title`, `introtext`, `created`, `modified` FROM #__k2_items";
		$sql_select .= " WHERE catid=".$catid;
		$sql_select .= " AND published='1'";
		$sort_limit = " ORDER BY id DESC LIMIT 0,".$params['count'];


		return $sql_select.$sql_timeline_params.$sort_limit;
	}

	/**
	 * timeline list
	 * @param type $catid 
	 * @param type $params 
	 * @return type
	 */
	static function viewTimeline($model, $catid, $params='')
	{
		$ji = new joomlaImports();
		$dv = new debugViewer();
		$rc = new returnCodesViewer();
		$mv = new dataModelViewer();
		$db = &JFactory::getDBO();
		$item = array();
		$SQLParams = self::construct_TimelineSQLParams($catid, $params);
		$db->setQuery($SQLParams);
		$dataObject = $db->loadObjectList();

		if ($dataObject)
		{
			foreach($dataObject as $a=>$b)
			{
				$retn = $mv->view($model, '', $b, $ji->getImportantIDSArray());
				if ($retn) $item[] = $retn;
			}
			
			header('Content-Type: application/json');
			$dv->view($item);
			echo json_encode($item);
		}
		else
		{
			$rc->rcode('json', $rc->timeline_empty);
		}
	}


}

?>