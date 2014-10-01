<?
class apiRuntime
{
	/**
	 * Description
	 * $rm[0] --- request uri method
	 * $rm[1] --- id (json preview)
	 * $rm[2] --- id (html preview)
	 * /data_type/{id}
	 * /data_type/{id}/content
	 * @param type $REQUEST_URI_API_METHOD 
	 * @param type $REQUEST_URI_API_OPT 
	 * @return type
	 */
	public function initialise($REQUEST_URI_API_METHOD, $REQUEST_URI_API_OPT)
	{

		$dv = new debugViewer();
		$dm = new dataModelViewer();
		$rc = new returnCodesViewer();
		$tl = new timelineModelViewer();
		$rm = explode('/', $REQUEST_URI_API_METHOD[0]);

		switch($rm[0])
		{
			case "news":
				/* view preview article by id */
				if (isset($rm[1]) && (int)$rm[1] && !isset($rm[2]))
					return $dm->previewById($rm[0], (int)$rm[1]);


				/* view article by id html output */
				if (isset($rm[1]) && isset($rm[2]) && $rm[2] == 'content' && (int)$rm[1])
					return $dm->getArticleByIdContent($rm[1]);
				

				if (isset($REQUEST_URI_API_OPT['since_id']) OR
					isset($REQUEST_URI_API_OPT['max_id']) OR
					isset($REQUEST_URI_API_OPT['count']))
				{
					/* view list objects */
					return $tl->viewTimeline('news', 3, $REQUEST_URI_API_OPT);
				}
				else
				{
					/* view list objects without opts */
					return $tl->viewTimeline('news', 3, '');
				}

				break;
			case "article_types":
				$exclude_article_types_array = array(
					'154','155', '145', '3', '34', '71', '44',
					'43', '111', '133', '75', '60', '70', '41',
					'52', '54', '98'
				);

				return $dm->getArticleTypes($exclude_article_types_array);
			break;
			case "articles":
				/**
				 * $rm[1] - articleType or (int)id 
				 */

				/* by id */
				if (isset($rm[1]) && (int)$rm[1] && !isset($rm[2]))
					return $dm->previewById($rm[0], (int)$rm[1]);


				/* by id content */
				if (isset($rm[1]) && (int)$rm[1] && $rm[2] == 'content')
					return $dm->getArticleByIdContent((int)$rm[1]);


				/* by article_type */
				if (isset($rm[1]) && (string)$rm[1])
					return print_r('did ');


				/* by article_type */
				/* if (isset($rm[1]) && $rm[1]))
				{
					return print_r('article_type');
				} */

			break;
			case "webinars":

				/* by id */
				if (isset($rm[1]) && (int)$rm[1] && !isset($rm[2]))
					return $dm->previewById($rm[0], (int)$rm[1]);

				if (isset($REQUEST_URI_API_OPT['since_id']) OR
					isset($REQUEST_URI_API_OPT['max_id']) OR
					isset($REQUEST_URI_API_OPT['count']))
				{
					/* view list objects */
					return $tl->viewTimelineWebina('webinars', 71, $REQUEST_URI_API_OPT);
				}
				else
				{
					/* view list objects without opts */
					return $tl->viewTimeline('webinars', 71, '');
				}


			break;
			default:
				$rc->rcode('json', $rc->invalid_URI);
		}
	}
}

?>