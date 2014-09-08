<?
/**
 * probirka app init, ver 0.10b 
 * 
**/

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);
define('DATE_FORMAT', 'r');
define('HOSTNAME', 'http://'.$_SERVER['HTTP_HOST']);
define('URI_API_PREFIX', '/api/');

error_reporting(0);

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



error_reporting(9);

$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();


class returnCodes
{
	public $incorrect_URI = array('code' => '1001', 'msg' => 'Incorrect URI');
	public $invalid_URI = array('code' => '1002', 'msg' => 'Invalid URI');
	public $news_not_found = array('code' => '1003', 'msg' => 'News not found');
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

	function __getNewsById($newsId)
	{
		$rc = new returnCodes();
		$moduleParams = new JRegistry();
		$db = &JFactory::getDBO();
		$item = array();

		// get news (data array)
		$sql = "SELECT `id`, `alias`, `title`, `introtext`, `created`, `modified` FROM #__k2_items";
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
			if (in_array($dataRow['id'], $importantIdArray, true))
			{
				$item_important = 'true';
			}
			else
			{
				$item_important = 'false';
			}

			$item['id'] = $dataRow['id'];
			$item['title'] = $dataRow['title'];
			$item['brief'] = str_replace(array("\r\n","\r"), "", strip_tags($dataRow['introtext']));
			$item['createdAt'] = date(DATE_FORMAT, strtotime($dataRow['created']));
			$item['updatedAt'] = date(DATE_FORMAT, strtotime($dataRow['modified']));
			$item['imageURL'] = HOSTNAME.'/media/k2/items/cache/'.md5("Image".$dataRow['id']).'_M.jpg';
			$item['important'] = $item_important;
			$item['shareURL'] = HOSTNAME.'/newsflash/'.$dataRow['id'].'-'.$dataRow['alias'].'.html';
			
			if (isset($_GET['debug']) && $_GET['debug'])
			{
				header('Content-Type: application/json');
				print_r($item);
				exit;
			}
			else
			{
				header('Content-Type: application/json');
				echo json_encode($item);
				exit;
			}
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($rc->news_not_found);
			exit;
		}
	}

	// fetch /news/{id}/content
	function __getNewsByIdContent($newsId)
	{
		$rc = new returnCodes();
		//$lc = new libClass();
		$db = &JFactory::getDBO();
		$item = array();

		// get news (data array)
		$sql = "SELECT `id`, `alias`, `title`, `introtext`, `fulltext`, `created`, `modified` FROM #__k2_items";
		$sql .= " WHERE id=".$newsId;
		$sql .= " AND published='1'";
		$db->setQuery($sql);
		$dr = $db->loadAssoc();
		$tidy = new tidy();
		$tidy->parseString(
			$dr['introtext'].$dr['fulltext'],
			array(
				'show-body-only' => true,
				'wrap' => false
			),
		'utf8');

		$tidy->cleanRepair();

		if (is_array($dr))
		{
			header('Content-Type: text/html');
			echo $tidy;
			exit;
		}
		else
		{
			header('Content-Type: application/json');
			echo json_encode($rc->news_not_found);
			exit;
		}
	}

	function __methodExec($REQUEST_URI_API_METHOD, $REQUEST_URI_API_OPT)
	{
		$rm = explode('/', $REQUEST_URI_API_METHOD[0]);

		/*
		 * $rm[0] --- request uri method
		 * $rm[1] --- id (json preview)
		 * $rm[2] --- id (html preview)
		 * /news/{id}
		 * /news/{id}/content
		 * 
		*/

		$prb = new prbClass();
		$rc = new returnCodes();

		switch($rm[0])
		{
			case "news":
				// fetch /news/{id}
				if (isset($rm[1]) && !isset($rm[2]) && is_numeric($rm[1]))
				{
					return $prb->__getNewsById($rm[1]);
				}

				// fetch /news/{id}/content
				if (isset($rm[1]) && isset($rm[2]) && $rm[2] == 'content' && is_numeric($rm[1]))
				{
					return $prb->__getNewsByIdContent($rm[1]);
				}

				
				/* $prb = new prbClass();
				return $prb->__getNewsOrder(
					3,
					$REQUEST_URI_API_OPT['since_id'],
					$REQUEST_URI_API_OPT['max_id'],
					$REQUEST_URI_API_OPT['count']
				); */

				//print_r($rm);

				break;
			default:
				header('Content-Type: application/json');
				echo json_encode($rc->invalid_URI);
				exit;
		}
	}
}


$prbClass = new prbClass();
$prbClass->__validateReqURI($_SERVER['REQUEST_URI']);

$REQUEST_URI_API = explode('?', $_SERVER['REQUEST_URI']);

$execOutput = $prbClass->__methodExec(str_replace(URI_API_PREFIX, '', $REQUEST_URI_API), $_GET);

if (isset($_GET['debug']) && $_GET['debug'])
{
	print_r($_GET);
	echo "\n";
	print_r($execOutput);
	exit;
}

?>