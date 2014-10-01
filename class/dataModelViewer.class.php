<?

class dataModelViewer
{

	/**
	 *  Check if input string is a valid YouTube URL
	 *  and try to extract the YouTube Video ID from it.
	 *  @param   $url   string   The string that shall be checked.
	 *  @return  mixed           Returns YouTube Video ID, or (boolean) false.
	 */        
	function parse_yturl($url) 
	{
		$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
		preg_match($pattern, $url, $matches);
		return (isset($matches[1])) ? $matches[1] : false;
	}

	/**
	 * format data
	 * @param type $method 
	 * @param type $format 
	 * @param type $dataRow 
	 * @param type $importantIdArray 
	 * @return type
	 */
	public function view($method, $format='', $dataRow, $importantIdArray='')
	{

		$item = new stdClass();
		
		switch($method)
		{
			case "article_types":
				$item->id = (int)$dataRow->id;
				$item->articleType = $dataRow->alias;
				$item->title = $dataRow->name;
				return $item;
			break;
			case "news":
			case "articles":
				$item->id = (int)$dataRow->id;
				$item->title = $dataRow->title;
				if ($method == 'news') $item->brief = str_replace(array("\r\n","\r"), "", strip_tags($dataRow->introtext));
				$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
				$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
				$item->imageURL = HOSTNAME.'/media/k2/items/cache/'.md5("Image".$dataRow->id).'_M.jpg';
				if ($method == 'news') $item->important = in_array($dataRow->id, $importantIdArray, true) ? 'true' : 'false';
				$item->shareURL = HOSTNAME.'/'.str_replace(URI_API_PREFIX, '', JRoute::_(K2HelperRoute::getItemRoute($dataRow->id.':'.$dataRow->alias, $dataRow->catid)));
				return $item;
			break;
			case "webinars":
				$item->id = (int)$dataRow->id;
				$item->ytId = self::parse_yturl($dataRow->introtext);
				$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
				$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
				return $item;
			break;
		}
	}

	/**
	 * article by id preview
	 * @param type $articleId
	 * @param type $params 
	 * @return type
	 */
	static function previewById($method, $articleId)
	{
		$ji = new joomlaImports();
		$dv = new debugViewer();
		$rc = new returnCodesViewer();
		$db = &JFactory::getDBO();
		$item = array();

		// get news (data array)
		$sql = "SELECT `id`, `alias`, `catid`, `title`, `introtext`, `created`, `modified` FROM #__k2_items";
		$sql .= " WHERE id=".$articleId;
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dataObject = $db->loadObject();

		if ($dataObject)
		{
			header('Content-Type: application/json');
			$dv->view($dataObject);
			echo json_encode(self::view($method, '', $dataObject, $ji->getImportantIDSArray()));
		}
		else
		{
			$rc->rcode('json', $rc->news_not_found);
		}
	}

	/**
	 * get article by id introtext + fulltext
	 * @param type $articleId 
	 * @return type
	 */
	static function getArticleByIdContent($articleId)
	{
		$dv = new debugViewer();
		$rc = new returnCodesViewer();
		$db = &JFactory::getDBO();
		$tidy = new tidy();
		$item = array();

		// get news (data array)
		$sql = "SELECT `introtext`, `fulltext` FROM #__k2_items";
		$sql .= " WHERE id=".$articleId;
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dataObject = $db->loadObject();

		if ($dataObject)
		{
			$tidy->parseString(
				$dataObject->introtext.$dataObject->fulltext,
				array('show-body-only' => true, 'wrap' => false),
			'utf8');

			$tidy->cleanRepair();

			header('Content-Type: text/html');
			$dv->view($tidy);
			echo $tidy;
		}
		else
		{
			$rc->rcode('json', $rc->news_not_found);
		}
	}

	/**
	 * get article categories
	 * @param type $excludeArray 
	 * @return type
	 */
	static function getArticleTypes($excludeArray)
	{
		$dv = new debugViewer();
		$rc = new returnCodesViewer();
		$db = &JFactory::getDBO();
		$tidy = new tidy();
		$item = array();

		// get categories (data array)
		$sql = "SELECT `id`, `name`, `alias` FROM #__k2_categories";
		$sql .= " WHERE parent='0'";
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dataObject = $db->loadObjectList();

		if ($dataObject)
		{
			foreach($dataObject as $a=>$b)
			{
				if (!in_array($b->id, $excludeArray)) {
					$item[] = self::view('article_types','', $b);
				}
			}

			header('Content-Type: application/json');
			$dv->view($item);
			echo json_encode($item);
		}
	}

}
?>
