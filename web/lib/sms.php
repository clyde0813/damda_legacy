<?
include "./lib.php";
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
try
{
	//$service_url = 'https://rest.surem.com/mms/v1';
    $log_array = array();
    $log_array['dm_request_dt'] = date("Y-m-d H:i:s");

	$service_url = 'https://rest.surem.com/sms/v1/json';
	$curl = curl_init($service_url);

	$to =  isset($_REQUEST['to']) ? $_REQUEST['to'] : "";
	$text =  isset($_REQUEST['text']) ? $_REQUEST['text'] : "테스트 결과 전송11";

    $log_array['dm_sms_no'] = $to;
    $log_array['dm_content'] = $text;

    $message = array(
    'message_id' => '',
	'to' => $to
)	;
	$arMessage = array($message);

	$data = array(
		'usercode' => 'design0912',
		'deptcode' => '3U-271-7Y',
		'messages' => $arMessage,
		'text' => $text,
		'from' => '01062765708',
)	;
	$post_data = json_encode( $data );

	echo $curl_post_data."<BR>";
	echo $post_data;

	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_HTTPHEADER , array('Content-Type: application/json')); 
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	
	$curl_response = curl_exec($curl);
	if ($curl_response === false) {
		$info = curl_getinfo($curl);
		curl_close($curl);
		die('error occured during curl exec. Additioanl info: ' . var_export($info));
	}
	curl_close($curl);
    $response = json_decode($curl_response);

    $log_array['dm_send_dt'] = date("Y-m-d H:i:s");
    $log_array['dm_sms_type'] = '2';
    $log_array['dm_customer_name'] = $_POST['customer_nm'];
    $log_array['dm_customer_info'] = '';
    $log_array['dm_att_file1'] = "";
    $log_array['dm_att_file2'] = '';
    $log_array['dm_att_file3'] = '';
    $log_array['dm_type'] = 'sms';
    if ($response->results[0]->result == 'success')
    {
        $res = "Y";
    }
    else
    {
        $res = "N";
    }
    $log_array['dm_sms_result'] = $res;
    $log_array['dm_result_info'] = $response->results[0]->result;

    insert_sms_log($log_array);

	echo $curl_response;
	

}
catch (Exception $e)
{
	echo $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';;
}


?>