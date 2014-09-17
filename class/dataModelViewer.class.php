<?

class dataModelViewer
{

	public function prettyPrint($json)
	{
		$result = '';
		$level = 0;
		$in_quotes = false;
		$in_escape = false;
		$ends_line_level = NULL;
		$json_length = strlen( $json );

		for( $i = 0; $i < $json_length; $i++ )
		{
			$char = $json[$i];
			$new_line_level = NULL;
			$post = "";
			if( $ends_line_level !== NULL )
			{
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if ($in_escape)
			{
				$in_escape = false;
			}
			else if($char === '"')
			{
				$in_quotes = !$in_quotes;
			}
			else if (!$in_quotes)
			{
				switch($char) {
					case '}': case ']':
						$level--;
						$ends_line_level = NULL;
						$new_line_level = $level;
						break;

					case '{': case '[':
						$level++;
					case ',':
						$ends_line_level = $level;
						break;

					case ':':
						$post = " ";
						break;

					case " ": case "\t": case "\n": case "\r":
						$char = "";
						$ends_line_level = $new_line_level;
						$new_line_level = NULL;
						break;
				}
			}
			else if ($char === '\\')
			{
				$in_escape = true;
			}

			if($new_line_level !== NULL)
			{
				$result .= "\n".str_repeat("\t", $new_line_level);
			}

			$result .= $char.$post;
		}

		return $result;
	}

	public function view($method, $format='', $dataRow, $importantIdArray='')
	{
		// $method -> news,articles etc.
		// $format -> type output: json, html
		// $dataRow   -> data array
		// $importantIdArray   -> important ids array

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
		}
	}

	/**
	 * timeline article by id preview
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

	static function getArticleById($articleId)
	{
		$dv = new debugViewer();
		$rc = new returnCodesViewer();
		$db = &JFactory::getDBO();
		$tidy = new tidy();
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
			echo json_encode(self::view('articles', '', $dataObject, ''));
		}
		else
		{
			$rc->rcode('json', $rc->news_not_found);
		}


	}

}
?>
