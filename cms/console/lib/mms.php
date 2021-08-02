<?
include "./lib.php";

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
try
{
    $log_array = array();
    $log_array['dm_request_dt'] = date("Y-m-d H:i:s");

	$to =  isset($_REQUEST['to']) ? $_REQUEST['to'] : "";
	$text =  isset($_REQUEST['text']) ? $_REQUEST['text'] : "";

    $log_array['dm_sms_no'] = $to;
    $log_array['dm_content'] = $text;
	
	//die("<BR>Done");
	$service_url = 'https://rest.surem.com/mms/v1';
	
	$curl = curl_init($service_url);

	
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

	$_VAR_FILE = './files';

	$path_parts = pathinfo($_FILES['file']['name']);
	$file_data = $path_parts['filename'].".".$path_parts['extension'];

    if (move_uploaded_file($_FILES['file']['tmp_name'], $_VAR_FILE."/".$file_data)) {

        $url = $_VAR_FILE ."/". $file_data;

        $cfile = curl_file_create($url, 'image/jpeg');

        $arPost = array('image1' => $cfile, "reqJSON" => json_encode($data));

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type:multipart/form-data'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $arPost);

        $curl_response = curl_exec($curl);

        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }
        curl_close($curl);

        $response = json_decode($curl_response);

        $log_array['dm_send_dt'] = date("Y-m-d H:i:s");
        $log_array['dm_sms_type'] = '3';
        $log_array['dm_customer_name'] = '';
        $log_array['dm_customer_info'] = '';
        $log_array['dm_att_file1'] = $file_data;
        $log_array['dm_att_file2'] = '';
        $log_array['dm_att_file3'] = '';
        $log_array['dm_type'] = 'mms';
        if ($response->results[0]->result == 'success') {
            $res = "Y";
        } else {
            $res = "N";
        }
        $log_array['dm_sms_result'] = $res;
        $log_array['dm_result_info'] = $response->results[0]->result;

        insert_sms_log($log_array);

        $arResult = array("result" => $response->results[0]->result, "_return" => "", "total" => $total_count, "rows" => $arReturn, "notice" => $response->message);
        echo json_encode($arResult);

    } else {
        $arResult = array("result" => "fail", "_return" => "", "total" => $total_count, "rows" => $arReturn, "notice" => "파일업로드 실패");
        echo json_encode($arResult);
    }

}
catch (Exception $e)
{
	echo $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';;
}


?>