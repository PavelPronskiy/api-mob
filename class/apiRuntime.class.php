<?
class apiRuntime
{
	public function initialise($REQUEST_URI_API_METHOD, $REQUEST_URI_API_OPT)
	{

		/*
		 * $rm[0] --- request uri method
		 * $rm[1] --- id (json preview)
		 * $rm[2] --- id (html preview)
		 * /data_type/{id}
		 * /data_type/{id}/content
		 * 
		*/

		$dv = new debugViewer();
		$dm = new dataModelViewer();
		$rc = new returnCodesViewer();
		$tl = new timelineModelViewer();
		$rm = explode('/', $REQUEST_URI_API_METHOD[0]);

		switch($rm[0])
		{
			case "news":
			case "articles":
				/* view preview article by id */
				if (isset($rm[1]) && is_int((int)$rm[1]) && !isset($rm[2]))
					return $dm->previewById($rm[1]);


				/* view article by id html output */
				if (isset($rm[1]) && isset($rm[2]) && $rm[2] == 'content' && is_int((int)$rm[1]))
					return $dm->getNewsByIdContent($rm[1]);
				

				if (isset($REQUEST_URI_API_OPT['since_id']) OR
					isset($REQUEST_URI_API_OPT['max_id']) OR
					isset($REQUEST_URI_API_OPT['count']))
				{
					/* view list objects */
					return $tl->viewTimeline(3, $REQUEST_URI_API_OPT);
				}
				else
				{
					/* view list objects without opts */
					return $tl->viewTimeline(3, '');
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
			default: $rc->rcode('json', $rc->invalid_URI);
		}
	}
}

?>