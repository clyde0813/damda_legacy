<?
	include "db.php";
	// 현재 날짜 
	//$ytime  = mktime (0,0,0,date("m")  , date("d"), date("Y"));     
	$ytime  = time();
	$ntime  = time();     
	$CurDate = date("Ymd",$ytime); 
	$CurMonth = date("Ym",$ytime); 

	$prevDate = date("Ymd", strtotime("-1 day")); 

	$prevDate30 = date("Ymd", strtotime("-30 day"));

	//$wDate    = strtotime('-7days'); 
	$wDate  = date("Ymd",strtotime("-7 day"));   
	//$wDate = date("Ymd",$wDate); 

	$cGDate = date("Y-m-d",$ytime); 
	$wGDate = date("Y-m-d",strtotime("-10 day"));

	$CurDateTime = date("YmdHis",$ntime); 
	$Today = date("Y_m_d_His",$ntime); 
	$TodayTime = date("Y-m-d H:i:s",$ntime); 
	$aTime = date("Y.m.d H:i:s",$ntime); 
	$CurTime = date("H",$ntime); 
	$CurMit = date("i",$ntime); 
	$CurATime = date("His",$ntime); 

	$CurDate2 = date("Y-m-d",$ytime); 

	$_TITLE = "[디자인아이엠] CMS";
	$_THEMES = "gray";

	$_VAR_PATH_ROOT = '/diam/'; //루트경로
	$_VAR_PATH_URL = 'http://'.$_SERVER['HTTP_HOST']."/diam/"; //루트url

	$_VAR_PATH_CMS =  "/diam/cms/console/";
	$_VAR_PATH_JS = "/diam/cms/console/js/";
	$_VAR_PATH_LIB = "/diam/cms/console/lib/";
	$_VAR_PATH_BIZ = "/diam/cms/console/biz/";
	$_VAR_PATH_UI = "/diam/cms/console/ui/";
	$_VAR_PATH_IMG = "/diam/cms/console/img/";
	$_VAR_PATH_CSS = "/diam/cms/console/css/";

    $_VAR_BOARD_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/base/board/";
	$_VAR_BOARD_SKIN = $_SERVER["DOCUMENT_ROOT"]."/diam/web/base/board/";
	$_VAR_BOARD_MOBILE_SKIN = $_SERVER["DOCUMENT_ROOT"]."/diam/web/base/board";

	$_VAR_WEB_PATH = $_VAR_PATH_ROOT."web/"; //웹 루트경로
	$_VAR_WEB_URL = $_VAR_PATH_URL."web/"; //웹 루트 url

    $_VAR_BOARD_URL = $_VAR_PATH_URL."web/base/board/";

	$_VAR_WEB_DATA_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/data/"; //웹 data 경로
	$_VAR_WEB_DATA_URL = $_VAR_WEB_URL."data/"; //웹 data url

    $_VAR_BASE_PATH = $_VAR_WEB_PATH."base/"; //base 경로
    $_VAR_BASE_URL = $_VAR_WEB_URL."base/"; //base url

    $_VAR_HISTORY_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/base/history";
    $_VAR_CERTIFICATE_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/base/certificate";
    $_VAR_ORGANIZATION_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/base/organization";

	$_VAR_PAGE_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/ui/pages/";
	$_VAR_PAGE_RELATIVE_PATH = "./ui/pages/";

	$_VAR_BANNER_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/data/banner/";
	$_VAR_BANNER_URL = "/diam/web/data/banner/";

    $_VAR_CERTIFICATE_IMAGE_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/data/certificate/";
    $_VAR_CERTIFICATE_IMAGE_URL = "/diam/web/data/certificate/";

    $_VAR_LAYOUT_PATH = "./thema/";
    $_VAR_BOARD_RELATIVE_SKIN = "./base/board/";

    $_VAR_POPUP_IMAGE_PATH = $_SERVER["DOCUMENT_ROOT"]."/diam/web/data/popup/";
    $_VAR_POPUP_IMAGE_URL = "/diam/web/data/popup/";

	$_VAR_MAX_FILE_SIZE = 5242880;

	/*$ck_user_id = "admin";
	$ck_user_nm = iconv("utf-8", "euc-kr","관리자");
	$ck_user_group = "0002";
	$ck_site_id = "0000";
	$ck_access_id = "9";*/

	/*
	function insert_log($site, $id, $nm, $page, $cd, $result, $data, $Query)
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$_ntime  = time();
		$_CurDate = date("Ymd",$_ntime);
		$_CurATime = date("His",$_ntime);
		$_CurDateTime = date("YmdHis",$_ntime);

		$query = "insert into an_common_web_log(com_date, com_time, com_system_id, com_group_id, com_user_id, com_user_nm, com_conn_ip, com_fn_req_dt, com_page, com_fn_req_cd, com_fn_result, com_fn_pdt_1)";
		$query .= " values(";
		$query .= "'".$_CurDate."', '".$_CurATime."', 'APP', '".$site."', '".$id."', '".$nm."', '".$_SERVER["REMOTE_HOST"]."', '".$_CurDateTime."', ".$page.", '".$cd."', '".$result."','".iconv("utf-8", "euc-kr", $data)."')";
		
		$db->ExecSql($query, "I");
	}
    */

    function insert_log($id, $ip, $code, $result, $url, $agent, $dm_array=array(), $site='')
    {
        $db = new DBSQL();
        $db -> DBconnect();
        $site_id = getSession('site_id');

        for ($i=1; $i<=count($dm_array); $i++) {
            $var = "dm_$i";
            $$var = "";
            if (isset($dm_array['dm_'.$i]) && settype($dm_array['dm'.$i], 'string')) {
                $$var = trim($dm_array['dm_'.$i]);
            }
        }

        $query = "INSERT INTO dm_web_log (`dm_datetime`, `dm_domain`, `dm_login_id`, `dm_ip`, `dm_fn_code`, `dm_fn_result`, `dm_fn_url`, `dm_agent_info`, `dm_type`, `dm_1`, `dm_2`, `dm_3`, `dm_4`, `dm_5`) 
        VALUE (now(), '".$site_id."', '".$id."', '".$ip."', '".$code."', '".$result."', '".$url."', '".$agent."', '관리자페이지','".$dm_1."','".$dm_2."','".$dm_3."','".$dm_4."','".$dm_5."')";

        $db->ExecSql($query, "I");
    }

	function selectMainCategory()
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$Query = "select * from main_category"; 
		$db->ExecSql($Query, "S" );
	
		while ( $arItem = $db->Fetch() )
		{
			$arCCode[$arItem['id']] = $arItem['name'];
		}
		return $arCCode;
	}
	function selectSubCategory()
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$Query = "select * from sub_category"; 
		$db->ExecSql($Query, "S" );
	
		while ( $arItem = $db->Fetch() )
		{
			$arCCode[$arItem['id']] = $arItem['name'];
		}
		return $arCCode;
	}
	function selectMngDevice($id)
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$Query = "select * from user_device where user_id = ".$id.""; 
		
		$db->ExecSql($Query, "S" );
		
		$return = "";

		while ( $arItem = $db->Fetch() )
		{
			if ($return != "") $return .= ',';
			$return .= "".$arItem['device_id']."";	
		}

		if ($return != '')
		{
			$return = " and id in (".$return.")";
		}
		return $return;
	}
	function selectMngMainCategory($id)
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$Query = "select * from user_device where user_id = ".$id.""; 
		
		$db->ExecSql($Query, "S" );
		
		$Ids = "";
		$return = "";

		while ( $arItem = $db->Fetch() )
		{
			if ($Ids != "") $Ids .= ',';
			$Ids .= "".$arItem['device_id']."";	
		}

		if ($Ids != '')
		{
			$Ids = " id in (".$Ids.")";
		}

		$Query = "select distinct(main_category) from device where ".$Ids.""; 
		//$db->writeLog($Query);
		$db->ExecSql($Query, "S" );

		while ( $arItem = $db->Fetch() )
		{
			if ($return != "") $return .= ',';
			$return .= "".$arItem['main_category']."";	
		}

		if ($return != '')
		{
			$return = " and id in (".$return.")";
		}
		
		return $return;
	}
	function selectMngSubCategory($id)
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$Query = "select * from user_device where user_id = ".$id.""; 
		
		$db->ExecSql($Query, "S" );
		
		$Ids = "";
		$return = "";

		while ( $arItem = $db->Fetch() )
		{
			if ($Ids != "") $Ids .= ',';
			$Ids .= "".$arItem['device_id']."";	
		}

		if ($Ids != '')
		{
			$Ids = " id in (".$Ids.")";
		}

		$Query = "select distinct(sub_category) from device where ".$Ids.""; 
		$db->ExecSql($Query, "S" );

		while ( $arItem = $db->Fetch() )
		{
			if ($return != "") $return .= ',';
			$return .= "".$arItem['sub_category']."";	
		}

		if ($return != '')
		{
			$return = " and id in (".$return.")";
		}
		
		return $return;
	}

    function selectCommonCode($group)
    {
        $db = new DBSQL();
        $db -> DBconnect();

        $Query = "select * from dm_common_code where dm_code_group='".$group."' order by dm_code_asc asc";
        $db->ExecSql($Query, "S" );

        while ( $arItem = $db->Fetch() )
        {
            $arCCode[$arItem['dm_code_value']] = $arItem['dm_code_name'];
        }
        return $arCCode;
    }

    function setSession($session_name, $value)
    {
        @session_start();

        $_SESSION[$session_name] = $value;
    }

    // 세션변수값 얻음
    function getSession($session_name)
    {
        @session_start();
        return $_SESSION[$session_name];
    }

    // 메세지 출력후 창을 닫음
    function alert_close($msg)
    {
        echo "<meta http-equiv='content-type' content='text/html; charset=utf-8'>";
        echo "<script language='javascript'> alert(\"$msg\"); window.close(); </script>";
        exit;
    }

    // 경고창으로 메세지 출력
    function alert_only($msg)
    {
        echo "<script language='javascript'> alert(\"$msg\"); </script>";
    }

    // 메타태그를 이용한 URL 이동
    function goLink($url)
    {
        echo "<script language='JavaScript'> location.replace(\"$url\"); </script>";
        exit;
    }

    // 메세지를 경고창으로
    function alert($msg='', $url='')
    {
        if (!$msg) $msg = '올바른 방법으로 이용해 주십시오.';
        echo "<meta http-equiv='content-type' content='text/html; charset=UTF-8'>";
        echo "<script language='javascript'>alert(\"$msg\");";
        if(!$url) echo "history.go(-1);";
        echo "</script>";
        if ($url) goLink($url);
        exit;
    }

    function print_r2($arr) {
	    echo "<pre>";
	    print_r($arr);
	    echo "</pre>";
    }

    function sql_password($value)
    {
        global $db;
        $query = "SELECT md5('$value') as pass";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        return $row['pass'];
    }

    function getBrowserInfo()
    {
        $userAgent = $_SERVER["HTTP_USER_AGENT"];
        if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i',$userAgent)){
            $browser = 'Internet Explorer';
        }
        else if(preg_match('/Firefox/i',$userAgent)){
            $browser = 'Mozilla Firefox';
        }
        else if (preg_match('/Chrome/i',$userAgent)){
            $browser = 'Google Chrome';
        }
        else if(preg_match('/Safari/i',$userAgent)){
            $browser = 'Apple Safari';
        }
        elseif(preg_match('/Opera/i',$userAgent)){
            $browser = 'Opera';
        }
        elseif(preg_match('/Netscape/i',$userAgent)){
            $browser = 'Netscape';
        }
        else{
            $browser = "Other";
        }

        return $browser;
    }

    function insert_sms_log($post)
    {
        $db = new DBSQL();
        $db->DBconnect();
        $query = "INSERT INTO dm_sms (`dm_request_dt`, `dm_send_dt`, `dm_sms_type`, `dm_customer_name`, `dm_customer_info`, `dm_sms_no`, `dm_content`, `dm_att_file1`,`dm_att_file2`,`dm_att_file3`,`dm_sms_result`, `dm_sms_result_info`)
            VALUE ('".$post['dm_request_dt']."','".$post['dm_send_dt']."', '".$post['dm_sms_type']."', '".$post['dm_customer_name']."', '".$post['dm_customer_info']."','".$post['dm_sms_no']."','".$post['dm_content']."','".$post['dm_att_file1']."',
            '".$post['dm_att_file2']."','".$post['dm_att_file3']."','".$post['dm_sms_result']."','".$post['dm_result_info']."')";
        $db->ExecSql($query, "I");

        cutRemain($post['dm_type']);
    }

    function cutRemain($type)
    {
        $db = new DBSQL();
        $db->DBconnect();

        $query = "SELECT * FROM dm_sms_config";
        $db->ExecSql($query, "S");
        $smsInfo = $db->Fetch();

        if ($type == 'sms')
        {
            $cut_price = $smsInfo['dm_sms_price'];
        }
        else if ($type == 'lms')
        {
            $cut_price = $smsInfo['dm_lms_price'];
        }
        else
        {
            $cut_price = $smsInfo['dm_mms_price'];
        }

        $query = "UPDATE dm_sms_config SET dm_remain = dm_remain - $cut_price";

        $db->ExecSql($query, "I");
    }
    $db = new DBSQL();
    $db->DBconnect();

    $domain = $_SERVER['SERVER_NAME'];
    if (preg_match('/www/', $domain) == true) { // www 없을때
        $domain = str_replace("www.", "", $domain);
    }

    $query = "SELECT * FROM dm_config WHERE dm_url LIKE '%".$domain."%'";
    $db->ExecSql($query, "S");
    $CONFIG = $db->Fetch();

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 9600)) {
        // 3시간동안 사용하지 않았으면 세션 종료
        setSession("chk_dm_level", "");
        setSession("chk_dm_name", "");
        setSession("chk_dm_id", "");
        setSession("is_member", "");
        setSession("is_admin", "");
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage

    }
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp


    function setExpCount($dm_id, $type, $point="") {
        $db = new DBSQL();
        $db->DBconnect();
        if ($point) {
            $query = "INSERT INTO dm_member_exp (mb_id, `dm_point`) VALUE ('".$dm_id."', '".$point."') ON DUPLICATE KEY UPDATE `dm_point` = `dm_point` + $point";
        } else {
            $query = "INSERT INTO dm_member_exp (mb_id, `dm_{$type}_count`) VALUE ('".$dm_id."', 1) ON DUPLICATE KEY UPDATE `dm_{$type}_count` = `dm_{$type}_count` + 1";
        }

        $db->ExecSql($query, "I");
    }

?>