<?

class debugViewer
{
	static function view($data, $dbReturn='')
	{
		if (isset($_GET['debug']) && $_GET['debug'])
		{
			var_export($data);
			var_export($dbReturn);
			exit;
		}
		
	} 
}

?>