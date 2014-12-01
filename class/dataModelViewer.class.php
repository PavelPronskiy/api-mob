<?

/**
 * output data model & viewer
 * @param type $url
 * @return type parsed youtube id video
 */
class dataModelViewer
{

	/**
	 * parse yotube source code
	 * @param type $url
	 * @return type parsed youtube id video
	 */
	function parse_yturl($url) 
	{
		$pattern = '/<iframe.*src=\"(.*)?\".*><\/iframe>/isU';
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
	 * dataConstructType
	 * @param type $objects
	 * @return type item
	 */
	static function dataConstructType($objects)
	{

		$item = new stdClass();
		$items = array();
		$countObjects = count($objects->objectList);

		foreach($objects->objectList as $dataRow)
		{
			switch($objects->section)
			{
				case "clinics":
					switch ($objects->pathRoute)
					{
						case "brief":
							$item->id = (int)$dataRow->id;
							$item->imageURL = HTTP_HOSTNAME_IMAGES.md5("Image".$dataRow->id).'_M.jpg';
							$item->rating = RatingHelper::getRatingValues($dataRow->id);
							$item->title = $dataRow->title;
							$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
							$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
							$item->regionId = $dataRow->catid;
							$item->regionTitle = $dataRow->catName;
							$item->phoneNumber = K2Helper::getExtrafields(5, $dataRow->extra_fields);
							$item->adress = K2Helper::getExtrafields(7, $dataRow->extra_fields);
							$item->webURL = K2Helper::getExtrafields(9, $dataRow->extra_fields);
							$item->photoURLs = K2Helper::getGallery($dataRow->id);
							$item->businessHours = K2Helper::getExtrafields(11, $dataRow->extra_fields);
							$item->location->latitude = K2Helper::getExtrafields(1, $dataRow->extra_fields);
							$item->location->longitude = K2Helper::getExtrafields(2, $dataRow->extra_fields);
							$item->since_hits = (int)$dataRow->hits;
						break;
						case "feedbacks":
							$item->id = (int)$dataRow->id;
							$item->createdAt = date(DATE_FORMAT, $dataRow->created);
							$item->updatedAt = date(DATE_FORMAT, $dataRow->modified);
							$item->author = (string)$dataRow->author;
							$item->text = (string)$dataRow->text;
							$item->rating = RatingHelper::getRatingStatus($dataRow->status_review);
						break;
						case "timeline":
							$item->id = (int)$dataRow->id;
							$item->imageURL = HTTP_HOSTNAME_IMAGES.md5("Image".$dataRow->id).'_M.jpg';
							$item->rating = RatingHelper::getRatingValues($dataRow->id);
							$item->title = $dataRow->title;
							$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
							$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
							$item->regionId = $dataRow->catid;
							$item->regionTitle = $dataRow->catName;
							$item->phoneNumber = K2Helper::getExtrafields(5, $dataRow->extra_fields);
							$item->adress = K2Helper::getExtrafields(7, $dataRow->extra_fields);
							$item->webURL = K2Helper::getExtrafields(9, $dataRow->extra_fields);
							$item->businessHours = K2Helper::getExtrafields(11, $dataRow->extra_fields);
							$item->location->latitude = K2Helper::getExtrafields(1, $dataRow->extra_fields);
							$item->location->longitude = K2Helper::getExtrafields(2, $dataRow->extra_fields);
							$item->since_hits = (int)$dataRow->hits;
						break;
					}

					break;
				case "regions":
					$item->id = (int)$dataRow->id;
					$item->regionName = $dataRow->alias;
					$item->title = $dataRow->name;
					$item->clinicsCount = (int)$dataRow->count;
					break;
				case "article_types":
					$item->id = (int)$dataRow->id;
					$item->articleType = $dataRow->alias;
					$item->title = $dataRow->name;
					break;
				case "news":

					$getImportantIDSArray = joomlaImports::getImportantIDSArray();


					$item->id = (int)$dataRow->id;
					$item->title = $dataRow->title;
					$item->brief = str_replace(array("\r\n","\r"), "", strip_tags($dataRow->introtext));
					$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
					$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
					$item->imageURL = HTTP_HOSTNAME_IMAGES.md5("Image".$dataRow->id).'_M.jpg';
					$item->important = in_array($dataRow->id, $getImportantIDSArray, true) ? 'true' : 'false';
					$item->shareURL = HTTP_HOSTNAME.JRoute::_(K2HelperRoute::getItemRoute($dataRow->id.':'.$dataRow->alias, $dataRow->catid));
					break;
				case "articles":
					$item->id = (int)$dataRow->id;
					$item->title = $dataRow->title;
					$item->brief = str_replace(array("\r\n","\r"), "", strip_tags($dataRow->introtext));
					$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
					$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
					$item->imageURL = HTTP_HOSTNAME_IMAGES.md5("Image".$dataRow->id).'_M.jpg';
					$item->shareURL = HTTP_HOSTNAME.JRoute::_(K2HelperRoute::getItemRoute($dataRow->id.':'.$dataRow->alias, $dataRow->catid));
					$item->articleTypeId = (int)$dataRow->catid;
					break;
				case "webinars":
					$ytid = self::parse_yturl($dataRow->introtext);
					if ($ytid)
					{
						$item->id = (int)$dataRow->id;
						$item->ytId = $ytid;
						$item->createdAt = date(DATE_FORMAT, strtotime($dataRow->created));
						$item->updatedAt = date(DATE_FORMAT, strtotime($dataRow->modified));
					}
				break;
			}


			if ($countObjects == 1)
			{
				return $item;
			}


			if (isset($item))
			{
				$items[] = $item;
				unset($item);
			}
		}
		
		return $items;
	}



	/**
	 * format data
	 * @param type $method 
	 * @param type $format 
	 * @param type $dataRow 
	 * @param type $importantIdArray 
	 * @return type
	 */
	static function dataView($objects)
	{

		$item = new stdClass();
		$tidy = new tidy();

		switch($objects->dataTypeFormat)
		{
			case "json":
				header('Content-Type: application/json');
				$dataObjects = self::dataConstructType($objects);

				debugViewer::view($dataObjects);
				echo json_encode($dataObjects);
			break;
			case "html":
				header('Content-Type: text/html');
				$tidy->parseString(
					$objects->objectList->introtext.$objects->objectList->fulltext,
					array('show-body-only' => true, 'wrap' => false, 'show-warnings' => false),
				'utf8'
				);

				$tidy->cleanRepair();
				debugViewer::view($tidy);
				echo $tidy->value;
			break;
			case "object":
				//print_r($objects);
				$objectList = self::dataConstructType($objects);
				//print_r($objects, $objectList);
			break;
		}
	}
}
?>
