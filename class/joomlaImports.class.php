<?

class joomlaImports
{
	static function getImportantIDSArray()
	{
		$db = &JFactory::getDBO();
		$moduleParams = new JRegistry();
		// get important flag (data array)
		$sql = "SELECT `id`, `position`, `module`, `params` FROM #__modules";
		$sql .= " WHERE id='240'";
		$db->setQuery($sql);
		$moduleParamsData = $db->loadAssoc();

		$moduleParams->loadString($moduleParamsData['params']);
		$importantIdArray = $moduleParams->get('k2items');

		return $importantIdArray;

	}


	static function validateRequestURI($REQUEST_URI_API)
	{
		$rc = new returnCodesViewer();
		$regex = "/[`'\"~!@# $*()<>,:;{}\|]/";
		if (preg_match($regex, $REQUEST_URI_API))
		{
			$rc->rcode('json', $rc->incorrect_URI);
		}
	}

}

?>