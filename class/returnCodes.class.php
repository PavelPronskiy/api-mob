<?

class returnCodesViewer
{
	public $incorrect_URI = array('errors' => array('code' => '1001', 'message' => 'Incorrect URI'));
	public $invalid_URI = array('errors' => array('code' => '1002', 'message' => 'Invalid URI'));
	public $news_not_found = array('errors' => array('code' => '1003', 'message' => 'News not found'));
	public $timeline_params_empty = array('errors' => array('code' => '1004', 'message' => 'Empty timeline params'));
	public $timeline_params_not_defined = array('errors' => array('code' => '1005', 'message' => 'Timeline params not defined'));
	public $timeline_params_invalid = array('errors' => array('code' => '1006', 'message' => 'Invalid timeline params'));
	public $timeline_empty = array('errors' => array('code' => '1007', 'message' => 'Empty timeline items'));


	public function rcode($outputFormatType, $data)
	{

		//$rc = new returnCodes();

		switch($outputFormatType)
		{
			case "json":

				// set web server code status
				switch($data['errors']['code'])
				{
					case "1003": header('HTTP/1.1 404 Not Found'); break;
				}

				
				header('Content-Type: application/json');
				echo json_encode($data);
				exit;
			break;
		}

		exit;
	}


}

?>