<?
/**
 * API, ver 0.41b
**/

error_reporting(E_ALL);

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('URI_API_PREFIX', DS.'api'.DS);
define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);
define('HOSTNAME', 'http://'.$_SERVER['HTTP_HOST']);
define('API_CLASS_PATH', JPATH_BASE.URI_API_PREFIX.'class'.DS);
define('DATE_FORMAT', 'r');
define('MAX_COUNT_TIMELINE', 5);
define('MAX_ID_TIMELINE', 100000000);
define('SINCE_ID_TIMELINE', 0);
define('COUNT_LIMIT_TIMELINE', 100);
define('CLINICS_SINCE_HITS_TIMELINE', 1000000);
define('K2_ITEMS_IMAGES_PATH', '/media/k2/items/cache/');

define('NEWS_K2_CATEGORY_ID', 3);
define('WEBINARS_K2_CATEGORY_ID', 71);
define('CLINIC_REGIONS_K2_CATEGORY_ID', 156);
define('K2_GALLERY_PATH', '/media/k2/galleries/');


define('ARTICLE_TYPES_ID', '88,89,90,35,255,92,37,91,36,94,95,153,195,51,39');

require_once(JPATH_BASE.URI_API_PREFIX.'.rating-db-settings.php');
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

// registering classes
JLoader::register('K2HelperRoute', JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
JLoader::register('CodesExceptionHandler', API_CLASS_PATH.'returnCodesHandler.class.php');
JLoader::register('clinicsModelHelper', API_CLASS_PATH.'clinicsModelHelper.class.php');
JLoader::register('dataModelViewer', API_CLASS_PATH.'dataModelViewer.class.php');
JLoader::register('debugViewer', API_CLASS_PATH.'debugViewer.class.php');
JLoader::register('joomlaImports', API_CLASS_PATH.'joomlaImports.class.php');
JLoader::register('articlesHelper', API_CLASS_PATH.'articlesHelper.class.php');
JLoader::register('K2Helper', API_CLASS_PATH.'K2Helper.class.php');
JLoader::register('RatingHelper', API_CLASS_PATH.'RatingHelper.class.php');
JLoader::register('APIRouter', API_CLASS_PATH.'APIRouter.class.php');


// joomla init
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();



APIRouter::route();

?>