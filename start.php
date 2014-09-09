<?
/**
 * API, ver 0.12b
**/

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);
define('DATE_FORMAT', 'r');
define('HOSTNAME', 'http://'.$_SERVER['HTTP_HOST']);
define('URI_API_PREFIX', '/api/');

error_reporting(9);

require_once($_SERVER["DOCUMENT_ROOT"].'/configuration.php');
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );




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
	public $incorrect_URI = array('code' => '1001', 'msg' => 'Incorrect URI');
	public $invalid_URI = array('code' => '1002', 'msg' => 'Invalid URI');
	public $news_not_found = array('code' => '1003', 'msg' => 'News not found');
	public $timeline_params_empty = array('code' => '1004', 'msg' => 'Empty timeline params');
}

class prbClass
{
	var $connection;
	var $database;
	var $debug;

	/* function __construct()
	{
		$cfg = new JConfig();
		if (!$this->connection = new PDO('mysql:host=' . $cfg->host . ';dbname=' . $cfg->db, $cfg->user, $cfg->password))
		{
			$this->debug[] = "Error: ".$cfg->db;
		}
	}

	function __getNewsOrder($catid, $since_id, $max_id, $limit)
	{
		$db = &JFactory::getDBO();
		$item = array();
		$incrId = 1;
		$sql = "SELECT `id`, `alias`, `title`, `introtext`, `created`, `modified` FROM #__k2_items";
		$sql .= " WHERE catid=".$catid;
		$sql .= " ORDER BY id DESC";

		$db->setQuery($sql, 0, $limit);
		$dataList = $db->loadAssocList();

		if (is_array($dataList))
		{
			foreach($dataList as $a=>$b)
			{
				$item[$a]['id'] = $incrId;
				$item[$a]['articleId'] = $b['id'];
				$item[$a]['title'] = $b['title'];
				$item[$a]['brief'] = str_replace(array("\r\n","\r"), "", strip_tags($b['introtext']));
				$item[$a]['createdAt'] = date(DATE_FORMAT, strtotime($b['created']));
				$item[$a]['updatedAt'] = date(DATE_FORMAT, strtotime($b['modified']));
				$item[$a]['imageURL'] = JURI::root().'media/k2/items/cache/'.md5("Image".$b['id']).'_M.jpg';
				$item[$a]['important'] = 'false';
				$item[$a]['shareURL'] = $b['alias'];
				$incrId++;
			}

			return $item;
		}
	} */

	function __validateReqURI($REQUEST_URI_API)
	{
		$rc = new returnCodes();
		$regex = "/[`'\"~!@# $*()<>,:;{}\|]/";
		if (preg_match($regex, $REQUEST_URI_API))
		{
			header('Content-Type: application/json');
			echo json_encode($rc->incorrect_URI);
			exit;
		}
	}

	function __dataModelView($method, $format, $dataRow, $importantIdArray='')
	{
		// $method -> news,articles etc.
		// $format -> type output: json, html
		// $data   -> data array
		// $importantIdArray   -> important id array


		$rc = new returnCodes();
		$item = array();
		
		switch($method)
		{
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

							header('Content-Type: application/json');
							echo json_encode($item);
						}
					break;
					case "html":
						$tidy = new tidy();
						$tidy->parseString(
							$dataRow['introtext'].$dataRow['fulltext'],
							array('show-body-only' => true, 'wrap' => false),
						'utf8');

						$tidy->cleanRepair();

						header('Content-Type: text/html');
						echo $tidy;
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
			prbClass::__dataModelView('news', 'json', $dataRow, $importantIdArray);
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($rc->news_not_found);
			exit;
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
			prbClass::__dataModelView('news', 'html', $dataRow);
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($rc->news_not_found);
			exit;
		}
	}

	function __getNewsTimeline($method, $params)
	{
		
		$rc = new returnCodes();

		if (!empty($params['since_id']) && !empty($params['max_id']) && !empty($params['count']))
		{
			// 
			print_r($params);
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($rc->timeline_params_empty);
			exit;
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
				if (isset($rm[1]) && !isset($rm[2]) && is_numeric($rm[1]))
					return prbClass::__getNewsById($rm[1]);

				// fetch /news/{id}/content
				if (isset($rm[1]) && isset($rm[2]) && $rm[2] == 'content' && is_numeric($rm[1]))
					return prbClass::__getNewsByIdContent($rm[1]);
				
				// fetch timeline /news
				// params: since_id, max_id, count
				if (isset($REQUEST_URI_API_OPT['since_id']) && isset($REQUEST_URI_API_OPT['max_id']) && isset($REQUEST_URI_API_OPT['count']))
					return prbClass::__getNewsTimeline($rm[0], $REQUEST_URI_API_OPT);

				break;
			default:
				header('Content-Type: application/json');
				echo json_encode($rc->invalid_URI);
				exit;
		}
	}
}

// joomla init
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();

prbClass::__validateReqURI($_SERVER['REQUEST_URI']);
prbClass::__methodExec(str_replace(URI_API_PREFIX, '', explode('?', $_SERVER['REQUEST_URI'])), $_GET);

if (isset($_GET['debug']) && $_GET['debug'])
{
	//print_r($_GET);
	echo "\n";
	//print_r($execOutput);
	exit;
}

?>