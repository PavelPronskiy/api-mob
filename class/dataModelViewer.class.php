<?

class dataModelViewer
{


     
	function parse_yturl($url) 
	{

		//$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
		//$pattern = '/embed\/([^\&\?\/]+)/';
		$pattern = '/<iframe.*src=\"(.*)?\".*><\/iframe>/isU';
		//$pattern = '#^(?:https?://)?(?:www\.)?(?:m\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';
		//$pattern = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		preg_match($pattern, $url, $matches);

		if (isset($matches[1]))
		{
			$replace_url = str_replace('?feature=player_detailpage','', $matches[1]);

			if (preg_match('/youtube/', $replace_url, $matchez))
			{
				$replace_url = preg_replace('#^(?:https?://)?(?:www\.)?youtube\.com/embed/#x','', $replace_url);
				return $replace_url;
			}

				
			
		}
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
			case "news":
				$item->id = (int)$dataRow->id;
				$item->title = $dataRow->title;
				$item->brief = str_replace(array("\r\n","\r"), "", strip_tags($dataRow->introtext));
				$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
				$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
				$item->imageURL = HOSTNAME.'/media/k2/items/cache/'.md5("Image".$dataRow->id).'_M.jpg';
				$item->important = in_array($dataRow->id, $importantIdArray, true) ? 'true' : 'false';
				$item->shareURL = HOSTNAME.'/'.str_replace(URI_API_PREFIX, '', JRoute::_(K2HelperRoute::getItemRoute($dataRow->id.':'.$dataRow->alias, $dataRow->catid)));

				return $item;

			case "articles":
				$item->id = (int)$dataRow->id;
				$item->title = $dataRow->title;
				$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
				$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
				$item->imageURL = HOSTNAME.'/media/k2/items/cache/'.md5("Image".$dataRow->id).'_M.jpg';
				$item->shareURL = HOSTNAME.'/'.str_replace(URI_API_PREFIX, '', JRoute::_(K2HelperRoute::getItemRoute($dataRow->id.':'.$dataRow->alias, $dataRow->catid)));
				$item->articleTypeId = (int)$dataRow->catid;

				return $item;
			case "webinars":
				$item->id = (int)$dataRow->id;
				$item->ytId = self::parse_yturl($dataRow->introtext);
				$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
				$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));

				if ($item->ytId)
					return $item;
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
