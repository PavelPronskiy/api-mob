<?

/*
* 
*/

error_reporting(E_ALL);

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

define('API_BASE_PATH', '/srv/www/vhosts/api.probirka.org/httpdocs'); 		// api define
define('JPATH_BASE', API_BASE_PATH.DS.'ext/joomla/2.5'); 					// joomla define
define('API_CLASS_PATH', API_BASE_PATH.DS.'class'); 							// api define

define('CLINIC_REGIONS_K2_CATEGORY_ID', 156); 								// api define

// cli hack (console run)
$_SERVER['HTTP_HOST'] = 'localhost';
global $_SERVER;

require_once(API_BASE_PATH.DS.'.rating-db-settings.php');
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

// registering classes
JLoader::register('JDatabase', JPATH_BASE.DS.'libraries'.DS.'joomla'.DS.'database'.DS.'database.php');
JLoader::register('K2Helper', API_CLASS_PATH.DS.'K2Helper.class.php');
JLoader::register('clinicsModelHelper', API_CLASS_PATH.DS.'clinicsModelHelper.class.php');
JLoader::register('ratingSyncer', API_CLASS_PATH.DS.'ratingSyncer.class.php');


$options = '';
$initRating = new ratingSyncer($options);


if ($_SERVER['argc'] == '2') {
	switch ($_SERVER['argv'][1]) {
		case '-cron':
			$objects = $initRating->initSyncer();

			if (isset($objects->return))
				print_r($objects);


		break;
		default:
		break;
	}
}
else
{
	echo 'Use: -cron';
	exit(1);
}


?>