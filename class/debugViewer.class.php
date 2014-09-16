<?

class debugViewer
{
	static function view($data)
	{
		if (isset($_GET['debug']) && $_GET['debug'])
		{
			print_r($data);
			exit;
		}
		
	} 
}

?>