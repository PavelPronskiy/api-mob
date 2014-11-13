<?

/*
* 
*/

error_reporting(E_ALL);

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('URI_API_PREFIX', DS.'api'.DS);
define('JPATH_BASE', '/srv/www/vhosts/probirka.org/httpdocs');
define('API_CLASS_PATH', JPATH_BASE.URI_API_PREFIX.'class'.DS);
define('CLINIC_REGIONS_K2_CATEGORY_ID', 156);

// cli hack
$_SERVER['HTTP_HOST'] = 'localhost';
global $_SERVER;

require_once(JPATH_BASE.URI_API_PREFIX.'.rating-db-settings.php');
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

// registering classes
JLoader::register('JDatabase', JPATH_BASE.DS.'libraries'.DS.'joomla'.DS.'database'.DS.'database.php');
JLoader::register('K2Helper', API_CLASS_PATH.'K2Helper.class.php');
JLoader::register('clinicsModelHelper', API_CLASS_PATH.'clinicsModelHelper.class.php');
JLoader::register('ratingSyncer', API_CLASS_PATH.'ratingSyncer.class.php');


$options = '';
$initRating = new ratingSyncer($options);
$return = $initRating->initSyncer();


print_r($return);


?>