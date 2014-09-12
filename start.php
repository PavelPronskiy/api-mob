<?
/**
 * API, ver 0.14b
**/

error_reporting(9);

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);
define('DATE_FORMAT', 'r');
define('HOSTNAME', 'http://'.$_SERVER['HTTP_HOST']);
define('URI_API_PREFIX', '/api/');

require_once($_SERVER["DOCUMENT_ROOT"].'/configuration.php');
require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');

jimport('joomla.filesystem.file');
jimport('joomla.database.table');
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');
jimport('joomla.application.component.view');
jimport('joomla.application.module.helper');

// load K2 model
JLoader::register('K2HelperRoute', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
JLoader::register('K2HelperUtilities', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');


class returnCodes
{
	public $incorrect_URI = array('errors' => array('code' => '1001', 'message' => 'Incorrect URI'));
	public $invalid_URI = array('errors' => array('code' => '1002', 'message' => 'Invalid URI'));
	public $news_not_found = array('errors' => array('code' => '1003', 'message' => 'News not found'));
	public $timeline_params_empty = array('errors' => array('code' => '1004', 'message' => 'Empty timeline params'));
	public $timeline_params_not_defined = array('errors' => array('code' => '1005', 'message' => 'Timeline params not defined'));
	public $timeline_params_invalid = array('errors' => array('code' => '1006', 'message' => 'Invalid timeline params'));
	public $timeline_empty = array('errors' => array('code' => '1007', 'message' => 'Empty timeline items'));
}

class prbClass
{
	var $connection;
	var $database;
	var $debug;

	function __errorCodesModel($outputFormatType, $data)
	{

		$rc = new returnCodes();

		switch($outputFormatType)
		{
			case "json":

				// set web server code status
				switch($data['errors']['code'])
				{
					case "1003": header('HTTP/1.1 404 Not Found'); break;
				}

				
				header('Content-Type: application/json');
				echo json_encode($data);
				exit;
			break;
		}

		exit;

	}

	function __validateReqURI($REQUEST_URI_API)
	{
		$rc = new returnCodes();
		$regex = "/[`'\"~!@# $*()<>,:;{}\|]/";
		if (preg_match($regex, $REQUEST_URI_API))
		{
			self::__errorCodesModel('json', $rc->incorrect_URI);
		}
	}

	function __dataModelView($method, $format='', $dataRow, $importantIdArray='')
	{
		// $method -> news,articles etc.
		// $format -> type output: json, html
		// $data   -> data array
		// $importantIdArray   -> important id array

		$rc = new returnCodes();
		$item = array();
		
		switch($method)
		{
			case "article_types":
				if (is_array($dataRow))
				{
					$item['id'] = $dataRow['id'];
					$item['articleType'] = $dataRow['alias'];
					$item['title'] = $dataRow['name'];
				}

				return $item;


			break;
			case "news":
				switch($format)
				{
					case "json":

						if (is_array($dataRow))
						{
							$item['id'] = $dataRow['id'];
							$item['title'] = $dataRow['title'];
							$item['brief'] = str_replace(array("\r\n","\r"), "", strip_tags($dataRow['introtext']));
							$item['createdAt'] = date(DATE_FORMAT, strtotime($dataRow['created']));
							$item['updatedAt'] = date(DATE_FORMAT, strtotime($dataRow['modified']));
							$item['imageURL'] = HOSTNAME.'/media/k2/items/cache/'.md5("Image".$dataRow['id']).'_M.jpg';
							$item['important'] = in_array($dataRow['id'], $importantIdArray, true) ? 'true' : 'false';
							$item['shareURL'] = HOSTNAME.'/'.str_replace(URI_API_PREFIX, '', JRoute::_(K2HelperRoute::getItemRoute($dataRow['id'].':'.$dataRow['alias'], $dataRow['catid'])));

							return $item;
						}
					break;
					case "html":
						$tidy = new tidy();
						$tidy->parseString(
							$dataRow['introtext'].$dataRow['fulltext'],
							array('show-body-only' => true, 'wrap' => false),
						'utf8');

						$tidy->cleanRepair();

						return $tidy;
					break;
				}
				
			break;
		}
	}

	function __getNewsById($newsId)
	{
		$rc = new returnCodes();
		$moduleParams = new JRegistry();
		$db = &JFactory::getDBO();
		$item = array();

		// get news (data array)
		$sql = "SELECT `id`, `alias`, `catid`, `title`, `introtext`, `created`, `modified` FROM #__k2_items";
		$sql .= " WHERE id=".$newsId;
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dataRow = $db->loadAssoc();
		
		// get important flag (data array)
		$sql = "SELECT `id`, `position`, `module`, `params` FROM #__modules";
		$sql .= " WHERE id='240'";
		$db->setQuery($sql);
		$moduleParamsData = $db->loadAssoc();

		$moduleParams->loadString($moduleParamsData['params']);
		$importantIdArray = $moduleParams->get('k2items');

		if (is_array($dataRow))
		{
			header('Content-Type: application/json');
			echo json_encode(self::__dataModelView('news', 'json', $dataRow, $importantIdArray));
		}
		else
		{
			self::__errorCodesModel('json', $rc->news_not_found);
		}
	}

	// fetch /news/{id}/content
	// output: html intro and full text
	function __getNewsByIdContent($newsId)
	{
		$rc = new returnCodes();
		$db = &JFactory::getDBO();
		$item = array();

		// get news (data array)
		$sql = "SELECT `id`, `alias`, `title`, `introtext`, `fulltext`, `created`, `modified` FROM #__k2_items";
		$sql .= " WHERE id=".$newsId;
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dataRow = $db->loadAssoc();

		if (is_array($dataRow))
		{
			header('Content-Type: text/html');
			echo self::__dataModelView('news', 'html', $dataRow);
		}
		else
		{
			self::__errorCodesModel('json', $rc->news_not_found);
		}
	}

	function __getNewsTimeline($method, $params)
	{
		
		$rc = new returnCodes();
		$db = &JFactory::getDBO();
		$item = array();

		if (
			!empty($params['since_id']) &&
			!empty($params['max_id']) &&
			!empty($params['count'])
		)
		{

			if (
				is_numeric($params['since_id']) &&
				is_numeric($params['max_id']) &&
				is_numeric($params['count'])
			)
			{

				($params['max_id'] < $params['since_id']) ? $sql_sort = 'DESC' : $sql_sort = 'ASC';

				$sql = "SELECT `id`, `alias`, `catid`, `title`, `introtext`, `created`, `modified` FROM #__k2_items";
				$sql .= " WHERE id < ".$params['max_id'];
				$sql .= " AND published='1'";
				$sql .= " AND id > ".$params['since_id'];
				$sql .= " AND catid='3'";
				$sql .= " ORDER BY id ".$sql_sort;

				$db->setQuery($sql, 0, $params['count']);
				$dataArray = $db->loadAssocList();

				if ($dataArray)
				{
					foreach($dataArray as $row)
					{
						$item[] = self::__dataModelView('news', 'json', $row);
					}

					if (isset($_GET['debug']) && $_GET['debug'])
					{
						header('Content-Type: application/json');
						print_r($item);
					}
					else
					{
						header('Content-Type: application/json');
						echo json_encode($item);
					}
				}
				else
				{
					self::__errorCodesModel('json', $rc->timeline_empty);
				}
			}
			else
			{
				self::__errorCodesModel('json', $rc->timeline_params_invalid);
			}
		}
		else
		{
			self::__errorCodesModel('json', $rc->timeline_params_empty);
		}

		
	}

	function __getArticleTypes($exclude_article_types_array)
	{
		$rc = new returnCodes();
		$db = &JFactory::getDBO();
		$item = array();

		// get categories (data array)
		$sql = "SELECT `id`, `name`, `alias` FROM #__k2_categories";
		$sql .= " WHERE parent='0'";
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dataArray = $db->loadAssocList();


		foreach($dataArray as $row)
		{
			if (!in_array($row['id'], $exclude_article_types_array)) {
				$item[] = self::__dataModelView('article_types','', $row);
			}
		}

		if (isset($_GET['debug']) && $_GET['debug'])
		{
			header('Content-Type: application/json');
			print_r($item);
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($item);
		}
	}

	function __methodExec($REQUEST_URI_API_METHOD, $REQUEST_URI_API_OPT)
	{

		/*
		 * $rm[0] --- request uri method
		 * $rm[1] --- id (json preview)
		 * $rm[2] --- id (html preview)
		 * /news/{id}
		 * /news/{id}/content
		 * 
		*/

		$rc = new returnCodes();
		$rm = explode('/', $REQUEST_URI_API_METHOD[0]);


		switch($rm[0])
		{
			case "news":
				// fetch /news/{id}
				if (
					isset($rm[1]) &&
					!isset($rm[2]) &&
					is_numeric($rm[1])
				) return self::__getNewsById($rm[1]);

				// fetch /news/{id}/content
				if (
					isset($rm[1]) &&
					isset($rm[2]) &&
					$rm[2] == 'content' &&
					is_numeric($rm[1])
				) return self::__getNewsByIdContent($rm[1]);
				
				// fetch timeline /news
				// params: since_id, max_id, count
				if (
					isset($REQUEST_URI_API_OPT['since_id']) &&
					isset($REQUEST_URI_API_OPT['max_id']) &&
					isset($REQUEST_URI_API_OPT['count'])
				)
				{
					return self::__getNewsTimeline($rm[0], $REQUEST_URI_API_OPT);
				}
				else
				{
					self::__errorCodesModel('json', $rc->timeline_params_not_defined);
				}

				break;
			case "article_types":
				$exclude_article_types_array = array(
					'154','155', '145', '3', '34', '71', '44',
					'43', '111', '133', '75', '60', '70', '41',
					'52', '54', '98'
				);

				self::__getArticleTypes($exclude_article_types_array);
			break;
			default:
				self::__errorCodesModel('json', $rc->invalid_URI);
		}
	}
}

// joomla init
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();

prbClass::__validateReqURI($_SERVER['REQUEST_URI']);
prbClass::__methodExec(str_replace(URI_API_PREFIX, '', explode('?', $_SERVER['REQUEST_URI'])), $_GET);

/* if (isset($_GET['debug']) && $_GET['debug'])
{
	//print_r($_GET);
	echo "\n";
	//print_r($execOutput);
	exit;
} */

?>