<?

/**
 * routing query requests
 * @package default
 */
class APIRouter
{
	// registry types API
	public static function getRouteObjects()
	{
		try {
			$returnMethod = new stdClass;
			$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$pathMethods = array_values(array_filter(explode(DS, $path)));

			// query vars
			parse_str($_SERVER['QUERY_STRING'], $parse_str);
			if (isset($parse_str) && count($parse_str))
				$returnMethod->pathParams = (object)$parse_str;


			if (isset($pathMethods[0]))
				$returnMethod->section = $pathMethods[0];
			else
				throw new CodesExceptionHandler(1002);


			// example: methodType/categoryAlias
			// example: methodType/categoryAlias?params
			if ( isset($pathMethods[0]) && isset($pathMethods[1]) && (filter_var($pathMethods[1], FILTER_VALIDATE_INT) === false) )
			{
				$returnMethod->categoryId = K2Helper::getCategoryIdByAlias($pathMethods[1]);
				$returnMethod->pathRoute = 'timeline';
				$returnMethod->dataTypeFormat = 'json';
				if (!isset($returnMethod->pathParams->max_id)) $returnMethod->pathParams->max_id = MAX_ID_TIMELINE;
				if (!isset($returnMethod->pathParams->since_id)) $returnMethod->pathParams->since_id = SINCE_ID_TIMELINE;
				if (!isset($returnMethod->pathParams->count)) $returnMethod->pathParams->count = MAX_COUNT_TIMELINE;
				return $returnMethod;
			}

			// brief example: /methodType/id
			if (
				(isset($pathMethods[1]) && ( filter_var($pathMethods[1], FILTER_VALIDATE_INT) !== false ) ) &&
				!isset($pathMethods[2])
			)
			{
				$returnMethod->contentId = $pathMethods[1];
				$returnMethod->pathRoute = 'brief';
				$returnMethod->dataTypeFormat = 'json';
				return $returnMethod;
			}

			// content example: /methodType/id/content
			if (isset($pathMethods[1]) && ( filter_var($pathMethods[1], FILTER_VALIDATE_INT) !== false ) &&
				isset($pathMethods[2]) && $pathMethods[2] == 'content' && $pathMethods[2] != DS)
			{
				$returnMethod->contentId = $pathMethods[1];
				$returnMethod->pathRoute = 'content';
				$returnMethod->dataTypeFormat = 'html';
				return $returnMethod;
			}

			// клиники
			// content example: /methodType/id/about
			if (isset($pathMethods[1]) && ( filter_var($pathMethods[1], FILTER_VALIDATE_INT) !== false ) &&
				isset($pathMethods[2]) && $pathMethods[2] == 'about' && $pathMethods[2] != DS)
			{
				$returnMethod->contentId = $pathMethods[1];
				$returnMethod->pathRoute = 'about';
				$returnMethod->dataTypeFormat = 'html';
				return $returnMethod;
			}

			// отзывы
			// content example: /methodType/id/feedbacks
			if (isset($pathMethods[1]) && ( filter_var($pathMethods[1], FILTER_VALIDATE_INT) !== false ) &&
				isset($pathMethods[2]) && $pathMethods[2] == 'feedbacks' && $pathMethods[2] != DS)
			{
				$returnMethod->contentId = $pathMethods[1];
				$returnMethod->pathRoute = 'feedbacks';
				$returnMethod->dataTypeFormat = 'json';
				return $returnMethod;
			}


			// timeline params construct
			if (
				(isset($pathMethods[0]) || isset($pathMethods[1])) &&
				(
					isset($returnMethod->pathParams->max_id) ||
					isset($returnMethod->pathParams->since_id) ||
					isset($returnMethod->pathParams->count)
				)
			)
			{

				if ($returnMethod->pathParams->since_id == '-1')
					$returnMethod->pathParams->since_id = MAX_ID_TIMELINE;

				$returnMethod->categoryId = K2Helper::getMappingTypes($pathMethods[0]);
				$returnMethod->pathRoute = 'timeline';
				$returnMethod->dataTypeFormat = 'json';
				return $returnMethod;
			}

			// timeline (empty params)
			if (
				!isset($pathMethods[1]) &&
				(
					!isset($returnMethod->pathParams->count) ||
					!isset($returnMethod->pathParams->max_id) ||
					!isset($returnMethod->pathParams->since_id)
				)
			)
			{
				$returnMethod->categoryId = K2Helper::getMappingTypes($pathMethods[0]);
				$returnMethod->pathRoute = 'timeline';
				$returnMethod->dataTypeFormat = 'json';
				$returnMethod->pathParams->max_id = MAX_ID_TIMELINE;
				$returnMethod->pathParams->since_id = SINCE_ID_TIMELINE;
				$returnMethod->pathParams->count = MAX_COUNT_TIMELINE;
				return $returnMethod;
			}


			// empty exception
			if (!isset($returnMethod->pathRoute))
			throw new CodesExceptionHandler(1006);

		}
		catch (CodesExceptionHandler $e)
		{
			die($e->view($e->getMessage()));
		}
	}

	public static function route()
	{
		try
		{
			$routeObjects = APIRouter::getRouteObjects();


			if (isset($routeObjects->section))
			{
				switch($routeObjects->section)
				{
					case "articles": 		articlesHelper::getArticles($routeObjects); break;
					case "news": 			newsModelHelper::getNews($routeObjects); break;
					case "article_types": 	articlesHelper::getArticleTypes($routeObjects); break;
					case "regions": 		clinicsModelHelper::getRegions($routeObjects); break;
					case "clinics": 		clinicsModelHelper::getClinics($routeObjects); break;
					case "webinars": 		webinarsModelHelper::getWebinars($routeObjects); break;
					default:
						throw new CodesExceptionHandler(1009);

				}
			}
			else
			{
				throw new CodesExceptionHandler(1009);
			}


		}
		catch (CodesExceptionHandler $e)
		{
			die($e->view($e->getMessage()));
		}
	}
}
?>