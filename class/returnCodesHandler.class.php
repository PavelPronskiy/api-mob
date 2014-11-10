<?

class CodesExceptionHandler extends Exception
{

	public function view($errorCodeId)
	{

		//$output = new stdClass;
		//$output->errors = '';

		$errorsArray = array(
			array('1001', 'Incorrect URI111'),
			array('1002', 'Invalid URI'),
			array('1003', 'Items not found'),
			array('1004', 'Empty timeline params'),
			array('1005', 'Timeline params not defined'),
			array('1006', 'Invalid timeline params'),
			array('1007', 'Empty timeline items'),
			array('1008', 'Internal error')
			array('1009', 'Item not found')
			array('1010', 'Clinics not found')
		);
	
		// set web server code status
		switch($errorCodeId)
		{
			case "1003":
				header('HTTP/1.1 404 Not Found');
				header('Content-Type: application/json');
			break;
			default:
				header('Content-Type: application/json');
		}

		foreach ($errorsArray as $id)
		{
			if ($id[0] == $errorCodeId)
			{
				$output->errors->code = $id[0];
				$output->errors->message = $id[1];
			}	
		}

		return json_encode($output);
	}

}

?>