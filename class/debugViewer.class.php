<?

class debugViewer
{
	static function view($data, $dbReturn='')
	{
		if (isset($_GET['debug']) && $_GET['debug'])
		{
			print_r($data);
			print_r($dbReturn);
			exit;
		}
		
	} 
}

?>