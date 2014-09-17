<?
/**
 * API, ver 0.16b
**/

error_reporting(9);

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);
define('DATE_FORMAT', 'r');
define('HOSTNAME', 'http://'.$_SERVER['HTTP_HOST']);
define('URI_API_PREFIX', '/api/');
define('MAX_COUNT_TIMELINE', '5');

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

// load necessary models
JLoader::register('K2HelperRoute', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
JLoader::register('K2HelperUtilities', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');
JLoader::register('returnCodesViewer', JPATH_SITE.DS.'api'.DS.'class'.DS.'returnCodes.class.php');
JLoader::register('dataModelViewer', JPATH_SITE.DS.'api'.DS.'class'.DS.'dataModelViewer.class.php');
JLoader::register('timelineModelViewer', JPATH_SITE.DS.'api'.DS.'class'.DS.'timelineModelHandler.class.php');
JLoader::register('debugViewer', JPATH_SITE.DS.'api'.DS.'class'.DS.'debugViewer.class.php');
JLoader::register('joomlaImports', JPATH_SITE.DS.'api'.DS.'class'.DS.'joomlaImports.class.php');
JLoader::register('apiRuntime', JPATH_SITE.DS.'api'.DS.'class'.DS.'apiRuntime.class.php');

// validate URI
joomlaImports::validateRequestURI($_SERVER['REQUEST_URI']);

// joomla init
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();

$apiRequest = str_replace(URI_API_PREFIX, '', explode('?', $_SERVER['REQUEST_URI']));

// api init
apiRuntime::initialise($apiRequest, $_GET);

?>