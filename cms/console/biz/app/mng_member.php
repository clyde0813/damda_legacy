<?php
/**
 * Created by PhpStorm.
 * User: mooyoung
 * Date: 2020-03-17
 * Time: 오후 2:08
 * 용도 : 회원 컨트롤러
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();

if($type == "select") {

    $db2 = new DBSQL();
    $db2->DBconnect();

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_start_date = isset($_REQUEST['search_start_date']) ? urldecode(trim($_REQUEST['search_start_date'])): "";
    $search_end_date = isset($_REQUEST['search_end_date']) ? urldecode(trim($_REQUEST['search_end_date'])): "";
    $search_level = isset($_REQUEST['search_level']) ? urldecode(trim($_REQUEST['search_level'])): "";
    $search_gender = isset($_REQUEST['search_gender']) ? urldecode(trim($_REQUEST['search_gender'])): "";
    $search_leave = isset($_REQUEST['search_leave']) ? urldecode(trim($_REQUEST['search_leave'])): "";
    $search_intercept = isset($_REQUEST['search_intercept']) ? urldecode(trim($_REQUEST['search_intercept'])): "";
    $search_business = isset($_REQUEST['search_business']) ? urldecode(trim($_REQUEST['search_business'])): "";
    $search_expert = isset($_REQUEST['search_expert']) ? urldecode(trim($_REQUEST['search_expert'])): "";
	$search_group = isset($_REQUEST['search_group']) ? urldecode(trim($_REQUEST['search_group'])): "";

    $search_query = "";

    if ($search_type != "") {
        if ($search_type == "all") {
            $search_query .= " AND (dm_id LIKE '%".$search_value."%' OR dm_name LIKE '%".$search_value."%' OR dm_nick LIKE '%".$search_value."%' OR dm_hp LIKE '%".$search_value."%' OR dm_datetime LIKE '%".$search_value."%' OR dm_today_login LIKE '%".$search_value."%')";
        } else {
            $search_query .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $search_query .= " AND `dm_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $search_query .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    if ($search_level != "") {
        $search_query .= " AND dm_level = '".$search_level."'";
    }

    if ($search_gender) {
        $search_query .= " AND dm_sex = '".$search_gender."'";
    }

    if ($search_leave) {
        $search_query .= " AND dm_leave_date <> ''";
    }

    if ($search_intercept) {
        $search_query .= " AND dm_intercept_date <> ''";
    }

    if ($search_business) {
        $search_query .= " AND dm_3 = '2'";
    }

    if ($search_expert) {
        $search_query .= " AND dm_3 = '3'";
    }

	if ($search_group) {
		$search_query .= " AND dm_group_id = '".$search_group."'";
	}

    $query = "SELECT count(*) FROM `dm_member` WHERE 1 = 1 AND dm_id <> 'admin' ".$search_query;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = "SELECT * FROM `dm_member` WHERE 1 = 1 AND dm_id <> 'admin' ".$search_query." ORDER BY `dm_no` ASC $limit";
	
    $db->ExecSql($query, "S");

    $arData = array();

    $ar_level = selectCommonCode('1002');
    $ar_sex = selectCommonCode('1003');

    if ($db-Num > 0) {
        while ($arItem = $db->Fetch()) {
            $arItem['dm_level_text'] = $ar_level[$arItem['dm_level']];
            $arItem['dm_sex_text'] = $ar_sex[$arItem['dm_sex']];
            $arItem['dm_state'] = ($arItem['dm_leave_date']) ? "탈퇴" : "정상";
            $arItem['dm_leave_date'] = date("Y-m-d", strtotime($arItem['dm_leave_date']));
            $arItem['dm_intercept_date'] = date("Y-m-d", strtotime($arItem['dm_intercept_date']));
            $arItem['dm_image_url'] = "";
            if ($arItem['dm_3'] == 2 || $arItem['dm_3'] == 3) {
                if (is_file($_VAR_WEB_DATA_PATH."file/member/".$arItem['dm_4'])) {
                    $arItem['dm_image_url'] = $_VAR_WEB_DATA_URL.'file/member/'.$arItem['dm_4'];
                }
            }

            if ($arItem['dm_6'] == 1) {
                $arItem['dm_accept_text'] = "승인완료";
            } else  {
                $arItem['dm_accept_text'] = "신청";
            }

            $query = "SELECT * FROM dm_group WHERE dm_group_id = '".$arItem['dm_group_id']."'";
            $db2->ExecSql($query, "S");
            $groupInfo = $db2->Fetch();
            $arItem['dm_group_text'] = $groupInfo['dm_group_name'];

            array_push($arData, $arItem);
        }
        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arData);
    } else {
        $arResult = array( "result" => "success", "_return" => "","total" => $total_count, "rows" => $arData);
    }

    echo json_encode($arResult);
} else if ($type=='insert' || $type=='update') {
    $dm_no = isset($_REQUEST['dm_no']) ? trim($_REQUEST['dm_no']) : "";
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
	$dm_group_id = isset($_REQUEST['dm_group_id']) ? trim($_REQUEST['dm_group_id']) : "";
	$update_query = "";

    if (getSession("chk_dm_id") != 'admin') {
        if ($dm_id == 'admin') {
            $arResult = array("result" => "fail", "_return" => $dm_no, "notice" => "사용할 수 없는 아이디입니다.");
            echo json_encode($arResult);
            exit;
        }
    }

    if ($type == 'update') {
        /*$query = "SELECT * FROM dm_member WHERE dm_id = '".$dm_id."'";
        $db->ExecSql($query, "S");

        $row = $db->Fetch();
        if ($row) {
            $arResult = array("result" => "fail", "_return" => $dm_no, "notice" => "사용중인 아이디입니다.");
            echo json_encode($arResult);
            exit;
        }*/

		$query = "SELECT * FROM dm_member WHERE dm_no = '".$dm_no."'";
		$db->ExecSql($query, "S");

        $row = $db->Fetch();
		if (!$row) {
			$arResult = array("result" => "fail", "_return" => $dm_no, "notice" => "회원정보를 찾을 수 없습니다.");
            echo json_encode($arResult);
            exit;
		} else {
			if ($row['dm_group_id'] != $dm_group_id && $dm_group_id == 'GROUP_0000000002') {
				$update_query = ", `dm_6` = '' , `dm_3` = '2'";
			} else if ($row['dm_group_id'] != $dm_group_id && $dm_group_id == 'GROUP_0000000003') {
				$update_query = ", `dm_6` = '' , `dm_3` = '3'";
			} else {
				$update_query = ", `dm_6` = '' , `dm_3` = '1', `dm_group_id` = '".$dm_group_id."'";
			}
		}
    } else {
		if ($dm_group_id == 'GROUP_0000000003') {
			$dm_3 = '3';
		} else if ($dm_group_id == 'GROUP_0000000002') {
			$dm_3 = '2';
		} else {
			$dm_3 = '1';
		}
	}

    $dm_name = isset($_REQUEST['dm_name']) ? trim($_REQUEST['dm_name']) : "";
    $dm_nick = isset($_REQUEST['dm_nick']) ? trim($_REQUEST['dm_nick']) : "";
    $dm_password = isset($_REQUEST['dm_password']) ? trim($_REQUEST['dm_password']) : "";
    $dm_level = isset($_REQUEST['dm_level']) ? trim($_REQUEST['dm_level']) : "";
    $dm_sex = isset($_REQUEST['dm_sex']) ? trim($_REQUEST['dm_sex']) : "";
    $dm_email = isset($_REQUEST['dm_email']) ? trim($_REQUEST['dm_email']) : "";
    $dm_homepage = isset($_REQUEST['dm_homepage']) ? trim($_REQUEST['dm_homepage']) : "";
    $dm_hp = isset($_REQUEST['dm_hp']) ? trim($_REQUEST['dm_hp']) : "";
    $dm_tel = isset($_REQUEST['dm_tel']) ? trim($_REQUEST['dm_tel']) : "";
    $dm_birth = isset($_REQUEST['dm_birth']) ? trim($_REQUEST['dm_birth']) : "";
    $dm_birth_type = isset($_REQUEST['dm_birth_type']) ? trim($_REQUEST['dm_birth_type']) : "";
    $dm_addr1 = isset($_REQUEST['dm_addr1']) ? $_REQUEST['dm_addr1'] : "";
    $dm_addr2 = isset($_REQUEST['dm_addr2']) ? $_REQUEST['dm_addr2'] : "";
    $dm_addr3 = isset($_REQUEST['dm_addr3']) ? $_REQUEST['dm_addr3'] : "";
    $dm_addr_jibeon = isset($_REQUEST['dm_addr_jibeon']) ? $_REQUEST['dm_addr_jibeon'] : "";
    $dm_mailling = isset($_REQUEST['dm_mailling']) ? trim($_REQUEST['dm_mailling']) : "";
    $dm_sms = isset($_REQUEST['dm_sms']) ? trim($_REQUEST['dm_sms']) : "";
    $dm_memo = isset($_REQUEST['dm_memo']) ? $_REQUEST['dm_memo'] : "";
    $dm_leave_date = isset($_REQUEST['dm_leave_date']) ? trim($_REQUEST['dm_leave_date']) : "";
    $dm_intercept_date = isset($_REQUEST['dm_intercept_date']) ? trim($_REQUEST['dm_intercept_date']) : "";
    $dm_private_expire = isset($_REQUEST['dm_private_expire']) ? trim($_REQUEST['dm_private_expire']) : "";
    $dm_org_chart = isset($_REQUEST['dm_org_chart']) ? $_REQUEST['dm_org_chart'] : "";
    $dm_position = isset($_REQUEST['dm_position']) ? trim($_REQUEST['dm_position']) : "";
    $dm_rank = isset($_REQUEST['dm_rank']) ? trim($_REQUEST['dm_rank']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? trim($_REQUEST['dm_order']) : "";
    $dm_point = isset($_REQUEST['dm_point']) ? trim($_REQUEST['dm_point']) : "";
    $dm_depart_id = isset($_REQUEST['dm_depart_id']) ? trim($_REQUEST['dm_depart_id']) : "";

    $mAgent = array("iPhone","iPod","Android","Blackberry", "Opera Mini", "Windows ce", "Nokia", "sony" );
    $chkMobile = false;
    for($i=0; $i<sizeof($mAgent); $i++){
        if(stripos( $_SERVER['HTTP_USER_AGENT'], $mAgent[$i] )){
            $chkMobile = true;
            break;
        }
    }

    if ($chkMobile) {
        $is_mobile = 1;
    }

    // 인증정보처리
    if($_POST['dm_certify_case'] && $_POST['dm_certify'] == 1) {
        $dm_certify = $_REQUEST['dm_certify_case'];
        $dm_adult = $_REQUEST['dm_adult'];
    } else {
        $dm_certify = '';
        $dm_adult = 0;
    }

    //우편번호 처리
    $dm_zip1 = substr($_REQUEST['dm_zip'], 0, 3);
    $dm_zip2 = substr($_REQUEST['dm_zip'], 3);

    //탈퇴일, 접근차단일 포멧변경
    if ($dm_leave_date)
    {
        $dm_leave_date = date("Ymd", strtotime($dm_leave_date));
    }

    if ($dm_intercept_date)
    {
        $dm_intercept_date = date("Ymd", strtotime($dm_intercept_date));
    }

    if($dm_no) {
        if ($dm_password) {
            $update_query = " ,`dm_password` = md5('".$dm_password."')";
        }

        $query = "SELECT * FROM `dm_member` WHERE `dm_no` = '".$dm_no."'";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();

        if ($row['dm_nick'] != $dm_nick) {
            $update_query .= " ,`dm_nick` = '".$dm_nick."', `dm_nick_date` = now() ";
        }
    }

    /*$query = "INSERT INTO `dm_member` (`dm_no`, `dm_id`, `dm_password`, `dm_name`, `dm_nick`, `dm_nick_date`, `dm_email`, `dm_homepage`, `dm_level`, `dm_sex`, `dm_birth`, `dm_birth_type`, `dm_tel`, `dm_hp`, `dm_certify`, `dm_adult`, `dm_zip1`, `dm_zip2`,
    `dm_addr1`, `dm_addr2`, `dm_addr3`, `dm_addr_jibeon`, `dm_datetime`, `dm_ip`, `dm_leave_date`, `dm_intercept_date`, `dm_memo`, `dm_mailling`, `dm_sms`, `dm_private_expire`, `dm_join_os`, `dm_org_chart`, `dm_position`, `dm_rank`, `dm_order`, `dm_depart_id`, `dm_group_id`, dm_point)
    VALUE ('".$dm_no."', '".$dm_id."', md5('".$dm_password."'), '".$dm_name."', '".$dm_nick."', now(), '".$dm_email."', '".$dm_homepage."', '".$dm_level."', '".$dm_sex."', '".$dm_birth."','".$dm_birth_type."', '".$dm_tel."', '".$dm_hp."', '".$dm_certify."', '".$dm_adult."', '".$dm_zip1."', '".$dm_zip2."', '".$dm_addr1."',
    '".$dm_addr2."', '".$dm_addr3."', '".$dm_addr_jibeon."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$dm_leave_date."', '".$dm_intercept_date."', '".$dm_memo."', '".$dm_mailling."', '".$dm_sms."', '".$dm_private_expire."', '".$is_mobile."', '".$dm_org_chart."', '".$dm_position."', '".$dm_rank."', '".$dm_order."', '".$dm_depart_id."', '".$dm_group_id."',
    '".$dm_point."')
     ON DUPLICATE KEY UPDATE `dm_name` = '".$dm_name."', `dm_email` = '".$dm_email."', `dm_homepage` = '".$dm_homepage."',`dm_level` = '".$dm_level."',
    `dm_sex` = '".$dm_sex."', `dm_tel` = '".$dm_tel."', `dm_hp` = '".$dm_hp."', `dm_certify` = '".$dm_certify."', `dm_birth` = '".$dm_birth."', `dm_birth_type` = '".$dm_birth_type."', `dm_adult` = '".$dm_adult."', 
    `dm_zip1` = '".$dm_zip1."', `dm_zip2` = '".$dm_zip2."', `dm_addr1` = '".$dm_addr1."', `dm_addr2` = '".$dm_addr2."',
    `dm_addr3` = '".$dm_addr3."', `dm_addr_jibeon` = '".$dm_addr_jibeon."',`dm_leave_date` = '".$dm_leave_date."', `dm_intercept_date` = '".$dm_intercept_date."', `dm_memo` = '".$dm_memo."', `dm_mailling` = '".$dm_mailling."',
    `dm_sms` = '".$dm_sms."', `dm_private_expire` = '".$dm_private_expire."', `dm_org_chart` = '".$dm_org_chart."', `dm_position` = '".$dm_position."', `dm_rank` = '".$dm_rank."', `dm_depart_id` = '".$dm_depart_id."',
     `dm_order` = '".$dm_order."', `dm_group_id` = '".$dm_group_id."', `dm_point` = '".$dm_point."' " . $update_query;*/
	
	//group id 업데이트 제거
	$query = "INSERT INTO `dm_member` (`dm_no`, `dm_id`, `dm_password`, `dm_name`, `dm_nick`, `dm_nick_date`, `dm_email`, `dm_homepage`, `dm_level`, `dm_sex`, `dm_birth`, `dm_birth_type`, `dm_tel`, `dm_hp`, `dm_certify`, `dm_adult`, `dm_zip1`, `dm_zip2`,
    `dm_addr1`, `dm_addr2`, `dm_addr3`, `dm_addr_jibeon`, `dm_datetime`, `dm_ip`, `dm_leave_date`, `dm_intercept_date`, `dm_memo`, `dm_mailling`, `dm_sms`, `dm_private_expire`, `dm_join_os`, `dm_org_chart`, `dm_position`, `dm_rank`, `dm_order`, `dm_depart_id`, `dm_group_id`, `dm_point`, `dm_3`)
    VALUE ('".$dm_no."', '".$dm_id."', md5('".$dm_password."'), '".$dm_name."', '".$dm_nick."', now(), '".$dm_email."', '".$dm_homepage."', '".$dm_level."', '".$dm_sex."', '".$dm_birth."','".$dm_birth_type."', '".$dm_tel."', '".$dm_hp."', '".$dm_certify."', '".$dm_adult."', '".$dm_zip1."', '".$dm_zip2."', '".$dm_addr1."',
    '".$dm_addr2."', '".$dm_addr3."', '".$dm_addr_jibeon."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$dm_leave_date."', '".$dm_intercept_date."', '".$dm_memo."', '".$dm_mailling."', '".$dm_sms."', '".$dm_private_expire."', '".$is_mobile."', '".$dm_org_chart."', '".$dm_position."', '".$dm_rank."', '".$dm_order."', '".$dm_depart_id."', '".$dm_group_id."',
    '".$dm_point."', '".$dm_3."')
     ON DUPLICATE KEY UPDATE `dm_name` = '".$dm_name."', `dm_email` = '".$dm_email."', `dm_homepage` = '".$dm_homepage."',`dm_level` = '".$dm_level."',
    `dm_sex` = '".$dm_sex."', `dm_tel` = '".$dm_tel."', `dm_hp` = '".$dm_hp."', `dm_certify` = '".$dm_certify."', `dm_birth` = '".$dm_birth."', `dm_birth_type` = '".$dm_birth_type."', `dm_adult` = '".$dm_adult."', 
    `dm_zip1` = '".$dm_zip1."', `dm_zip2` = '".$dm_zip2."', `dm_addr1` = '".$dm_addr1."', `dm_addr2` = '".$dm_addr2."',
    `dm_addr3` = '".$dm_addr3."', `dm_addr_jibeon` = '".$dm_addr_jibeon."',`dm_leave_date` = '".$dm_leave_date."', `dm_intercept_date` = '".$dm_intercept_date."', `dm_memo` = '".$dm_memo."', `dm_mailling` = '".$dm_mailling."',
    `dm_sms` = '".$dm_sms."', `dm_private_expire` = '".$dm_private_expire."', `dm_org_chart` = '".$dm_org_chart."', `dm_position` = '".$dm_position."', `dm_rank` = '".$dm_rank."', `dm_depart_id` = '".$dm_depart_id."',
     `dm_order` = '".$dm_order."', `dm_point` = '".$dm_point."' " . $update_query;

    $db->ExecSql($query, "I");

    if (!$dm_no) $dm_no = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);
} else if ($type == "select_member") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "SELECT * FROM `dm_member` WHERE `dm_id` = '".$dm_id."'";

    $db->ExecSql($query, "S");

    $arData = array();

    if ($db->Num > 0) {
        $arData = $db->Fetch();

        if ($arData['dm_leave_date'])
        {
            $arData['dm_leave_date'] = date("Y-m-d", strtotime($arData['dm_leave_date']));
        }

        if ($arData['dm_intercept_date'])
        {
            $arData['dm_intercept_date'] = date("Y-m-d", strtotime($arData['dm_intercept_date']));
        }

        $arResult = array("result" => "success", "_return" => "", "rows" => $arData);
    } else {
        $arResult = array("result" => "fail", "_return" => "", "rows" => $arData);
    }

    echo json_encode($arResult);
}

else if ($type == "delete") {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "DELETE FROM dm_member WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no, "notice" => "성공했습니다.");

    echo json_encode($arResult);
}

else if ($type == 'kick') {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "UPDATE dm_member SET dm_leave_date = '".date("Ymd")."' WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);

}

else if ($type == "unkick") {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "UPDATE dm_member SET dm_leave_date = '' WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);
}

else if ($type == "undormancy") {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "UPDATE dm_member SET dm_intercept_date = '' WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);
}

else if ($type == 'all_kick') {

    $query = "UPDATE dm_member SET dm_leave_date = '".date("Ymd")."' WHERE dm_intercept_date <> '' AND dm_id <> 'admin'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);

}

else if ($type == 'convert_excel') {
    $query = "SELECT * FROM dm_member WHERE dm_id <> 'admin'";

    $db->ExecSql($query, "S");

    $EXCEL_STR = "  
    <table border='1'>
    <tr>회원목록</tr>  
    <tr>  
       <td>번호</td>  
       <td>아이디</td>  
       <td>이름</td>  
       <td>닉네임</td>  
       <td>닉네임 업데이트</td>  
       <td>이메일</td>  
       <td>홈페이지</td>  
       <td>권한</td>  
       <td>성별</td>  
       <td>생일</td>  
       <td>전화번호</td>  
       <td>휴대전화</td>  
       <td>본인인증방식</td>  
       <td>성인인증유무</td>  
       <td>우편번호</td>  
       <td>주소</td>  
       <td>마지막로그인</td>  
       <td>로그인아이피</td>  
       <td>가입일</td>  
       <td>접속아이피</td>  
       <td>탈퇴일</td>  
       <td>접근차단일</td>  
       <td>남기는말씀</td>  
       <td>메일여부</td>  
       <td>SMS여부</td>  
       <td>허용아이피</td>  
    </tr>";

    $i = 1;
    $level_array = selectCommonCode('1002');
    $sex_array = selectCommonCode('1003');
    while ($row = $db->Fetch()) {
        $level = $level_array[$row['dm_level']];
        $sex = $sex_array[$row['dm_sex']];
        $certi = ($row['dm_certify'] == 'hp') ? "휴대폰" : "아이핀";
        $adult = ($row['dm_adult']) ? "인증" : "미인증";
        $mailling = ($row['dm_mailling']) ? "허용" : "미허용";
        $sms = ($row['dm_sms']) ? "허용" : "미허용";
        $EXCEL_STR .= "  
       <tr>
       <td>".$i."</td>
           <td>".$row['dm_id']."</td>  
           <td>".$row['dm_name']."</td>  
           <td>".$row['dm_nick']."</td>  
           <td>".$row['dm_nick_date']."</td>  
           <td>".$row['dm_email']."</td>  
           <td>".$row['dm_homepage']."</td>  
           <td>".$level."</td>  
           <td>".$sex."</td>  
           <td>".$row['dm_birth_type']." ".$row['dm_birth']."</td>  
           <td>".$row['dm_tel']."</td>  
           <td>".$row['dm_hp']."</td>  
           <td>".$certi."</td>
           <td>".$adult."</td>    
           <td>".$row['dm_zip1']."-".$row['dm_zip2']."</td>    
           <td>".$row['dm_addr1']." ".$row['dm_addr2']." ". $row['dm_addr3'] ."</td>
           <td>".$row['dm_today_login']."</td>    
           <td>".$row['dm_login_ip']."</td>    
           <td>".$row['dm_datetime']."</td>    
           <td>".$row['dm_ip']."</td>    
           <td>".date("Y-m-d", strtotime($row['dm_leave_date']))."</td>    
           <td>".date("Y-m-d", strtotime($row['dm_intercept_date']))."</td>    
           <td>".$row['dm_memo']."</td>    
           <td>".$mailling."</td>    
           <td>".$sms."</td>
           <td>".$row['dm_accept_ip']."</td>        
       </tr>  
       ";
        $i++;
    }

    $EXCEL_STR .= "</table>";

    header( "Content-type: application/vnd.ms-excel" );
    header( "Content-type: application/vnd.ms-excel; charset=utf-8");
    header( "Content-Disposition: attachment; filename = 회원목록_".date("His").".xls" );
    header( "Content-Description: PHP4 Generated Data" );
    echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";
    echo $EXCEL_STR;
}


else if ($type == 'convert_leave_excel') {
    $query = "SELECT * FROM dm_member WHERE dm_id <> 'admin' AND dm_leave_date <> ''";

    $db->ExecSql($query, "S");

    $EXCEL_STR = "  
    <table border='1'>
    <tr>탈퇴회원목록</tr>  
    <tr>  
       <td>번호</td>  
       <td>아이디</td>  
       <td>이름</td>  
       <td>닉네임</td>  
       <td>닉네임 업데이트</td>  
       <td>이메일</td>  
       <td>홈페이지</td>  
       <td>권한</td>  
       <td>성별</td>  
       <td>생일</td>  
       <td>전화번호</td>  
       <td>휴대전화</td>  
       <td>본인인증방식</td>  
       <td>성인인증유무</td>  
       <td>우편번호</td>  
       <td>주소</td>  
       <td>마지막로그인</td>  
       <td>로그인아이피</td>  
       <td>가입일</td>  
       <td>접속아이피</td>  
       <td>탈퇴일</td>  
       <td>접근차단일</td>  
       <td>남기는말씀</td>  
       <td>메일여부</td>  
       <td>SMS여부</td>  
       <td>허용아이피</td>  
    </tr>";

    $i = 1;
    $level_array = selectCommonCode('1002');
    $sex_array = selectCommonCode('1003');
    while ($row = $db->Fetch()) {
        $level = $level_array[$row['dm_level']];
        $sex = $sex_array[$row['dm_sex']];
        $certi = ($row['dm_certify'] == 'hp') ? "휴대폰" : "아이핀";
        $adult = ($row['dm_adult']) ? "인증" : "미인증";
        $mailling = ($row['dm_mailling']) ? "허용" : "미허용";
        $sms = ($row['dm_sms']) ? "허용" : "미허용";
        if ($row['dm_intercept_date']) {
            $intercept_date = date("Y-m-d", strtotime($row['dm_intercept_date']));
        }

        if ($row['dm_leave_date']) {
            $leave_date = date("Y-m-d", strtotime($row['dm_leave_date']));
        }
        $EXCEL_STR .= "  
       <tr>
       <td>".$i."</td>
           <td>".$row['dm_id']."</td>  
           <td>".$row['dm_name']."</td>  
           <td>".$row['dm_nick']."</td>  
           <td>".$row['dm_nick_date']."</td>  
           <td>".$row['dm_email']."</td>  
           <td>".$row['dm_homepage']."</td>  
           <td>".$level."</td>  
           <td>".$sex."</td>  
           <td>".$row['dm_birth_type']." ".$row['dm_birth']."</td>  
           <td>".$row['dm_tel']."</td>  
           <td>".$row['dm_hp']."</td>  
           <td>".$certi."</td>
           <td>".$adult."</td>    
           <td>".$row['dm_zip1']."-".$row['dm_zip2']."</td>    
           <td>".$row['dm_addr1']." ".$row['dm_addr2']." ". $row['dm_addr3'] ."</td>
           <td>".$row['dm_today_login']."</td>    
           <td>".$row['dm_login_ip']."</td>    
           <td>".$row['dm_datetime']."</td>    
           <td>".$row['dm_ip']."</td>    
           <td>".$leave_date."</td>    
           <td>".$intercept_date."</td>    
           <td>".$row['dm_memo']."</td>    
           <td>".$mailling."</td>    
           <td>".$sms."</td>
           <td>".$row['dm_accept_ip']."</td>        
       </tr>  
       ";
        $i++;
    }

    $EXCEL_STR .= "</table>";

    header( "Content-type: application/vnd.ms-excel" );
    header( "Content-type: application/vnd.ms-excel; charset=utf-8");
    header( "Content-Disposition: attachment; filename = 탈퇴회원목록_".date("His").".xls" );
    header( "Content-Description: PHP4 Generated Data" );
    echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";
    echo $EXCEL_STR;
}

else if ($type == 'send_mail') {
    $dm_subject = isset($_REQUEST['dm_subject']) ? trim($_REQUEST['dm_subject']) : "";
    $dm_content = isset($_REQUEST['dm_content']) ? trim($_REQUEST['dm_content']) : "";
    $dm_from_email = isset($_REQUEST['dm_from_email']) ? trim($_REQUEST['dm_from_email']) : "";
    $dm_to_type = isset($_REQUEST['ma_target']) ? trim($_REQUEST['ma_target']) : "";
    $dm_level = isset($_REQUEST['dm_level']) ? trim($_REQUEST['dm_level']) : "";
    $level_agree = isset($_REQUEST['ma_agree1']) ? trim($_REQUEST['ma_agree1']) : ""; //선택회원
    $all_agree = isset($_REQUEST['ma_agree2']) ? trim($_REQUEST['ma_agree2']) : ""; //전체회원
    $send_agree = isset($_REQUEST['ma_agree']) ? trim($_REQUEST['ma_agree']) : ""; //수신동의
    $send_refusal = isset($_REQUEST['ma_refusal']) ? trim($_REQUEST['ma_refusal']) : ""; //수신거부
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : array(); //선택된 회원

    if (!$dm_subject) {
        $arResult = array("result" => "fail", "_return" => $dm_no, "notice" => "제목을 입력해주세요.");
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_content) {
        $arResult = array("result" => "fail", "_return" => $dm_no, "notice" => "내용을 입력해주세요.");
        echo json_encode($arResult);
        exit;
    }

    if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $dm_from_email))
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "올바르지 않은 이메일 형식입니다.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_to_type) {
        $arResult = array("result" => "fail", "_return" => $dm_no, "notice" => "대상회원을 선택해주세요.");
        echo json_encode($arResult);
        exit;
    }

    if ($send_agree) {
        $agree_content = '<br><br><p>본 메일은 정보통신망 이용촉진 및 정보보호 등에 관한 법률시행규칙 등 관련규정에 의거하여 '.date("Y년 m월 d일").' 기준, 회원님의 이메일 수신 동의 여부를 확인한 결과 회원님께서 수신에 동의하셨기에 발송되었습니다.</p>';
        $dm_content .= $agree_content;
    }

    require_once ($_SERVER['DOCUMENT_ROOT'].'/diam/cms/console/lib/mail.php');
    $where = "";
    $email = array();
    $temp_content = $dm_content;

    if ($dm_to_type == 'level') {
        if ($level_agree) {
            $where = " and dm_mailling = 1";
        }
        $query = "SELECT * FROM dm_member WHERE dm_level = '".$dm_level."' $where";
        $db->ExecSql($query, "S");
        while ($row = $db->Fetch()) {
            $dm_md5 = md5($row['dm_id'].$row['dm_email'].$row['dm_datetime']);
            if ($send_refusal) {
                $refusal_content = '<br><br><p>수신을 원하지 않으시면, <a href="http://'.$CONFIG['dm_url'].'/diam/web/lib/mail_stop.php?dm_id='.$row['dm_id'].'&amp;dm_md5='.$dm_md5.'" target="_blank">[수신거부]</a>를 클릭하셔서 정보메일 수신동의를 수정하여 주시기 바랍니다.<br>  If you don’t want this type of information or e-mail, please click the refuse here. <br>
        본 메일은 발신전용입니다. 이외 문의사항이나 이용안내는 '.$CONFIG['dm_customer_email'].' 또는 '.$CONFIG['dm_customer_tel'].'(고객센터)를이용하여 주십시오.</p>';
                $dm_content = $temp_content.$refusal_content;
            }

            mailer($dm_subject, $dm_from_email, $row['dm_email'], $dm_subject, $dm_content, 0);
            $email[] = $row['dm_email'];
            $dm_id_array[] = $row['dm_id'];
        }
    } else if ($dm_to_type == 'user') {
        foreach ($dm_id as $key => $value) {
            $query = "SELECT * FROM dm_member WHERE dm_id = '".$value."'";
            $db->ExecSql($query, "S");
            while ($row = $db->Fetch()) {
                $dm_md5 = md5($row['dm_id'].$row['dm_email'].$row['dm_datetime']);
                if ($send_refusal) {
                    $refusal_content = '<br><br><p>수신을 원하지 않으시면, <a href="http://'.$CONFIG['dm_url'].'/diam/web/lib/mail_stop.php?dm_id='.$row['dm_id'].'&amp;dm_md5='.$dm_md5.'" target="_blank">[수신거부]</a>를 클릭하셔서 정보메일 수신동의를 수정하여 주시기 바랍니다.<br>  If you don’t want this type of information or e-mail, please click the refuse here. <br>
        본 메일은 발신전용입니다. 이외 문의사항이나 이용안내는 '.$CONFIG['dm_customer_email'].' 또는 '.$CONFIG['dm_customer_tel'].'(고객센터)를이용하여 주십시오.</p>';
                    $dm_content = $temp_content.$refusal_content;
                }

                mailer($dm_subject, $dm_from_email, $row['dm_email'], $dm_subject, $dm_content, 0);
                $email[] = $row['dm_email'];
                $dm_id_array[] = $row['dm_id'];
            }
        }
    } else {
        if ($all_agree) {
            $where = " and  dm_mailling = 1";
        }
        $query = "SELECT * FROM dm_member WHERE dm_id <> 'admin' $where";
        $db->ExecSql($query, "S");
        while ($row = $db->Fetch()) {
            $dm_md5 = md5($row['dm_id'].$row['dm_email'].$row['dm_datetime']);
            if ($send_refusal) {
                $refusal_content = '<br><br><p>수신을 원하지 않으시면, <a href="http://'.$CONFIG['dm_url'].'/diam/web/lib/mail_stop.php?dm_id='.$row['dm_id'].'&amp;dm_md5='.$dm_md5.'" target="_blank">[수신거부]</a>를 클릭하셔서 정보메일 수신동의를 수정하여 주시기 바랍니다.<br>  If you don’t want this type of information or e-mail, please click the refuse here. <br>
        본 메일은 발신전용입니다. 이외 문의사항이나 이용안내는 '.$CONFIG['dm_customer_email'].' 또는 '.$CONFIG['dm_customer_tel'].'(고객센터)를이용하여 주십시오.</p>';
                $dm_content = $temp_content.$refusal_content;
            }

            mailer($dm_subject, $dm_from_email, $row['dm_email'], $dm_subject, $dm_content, 0);
            $email[] = $row['dm_email'];
            $dm_id_array[] = $row['dm_id'];
        }
    }

    $dm_content = addslashes($dm_content);
    $date = date("Y-m-d H:i:s");

    foreach ($email as $key => $value) {
        $query = "INSERT INTO dm_mail_log (dm_subject, dm_from_email, dm_to_email, dm_content, dm_send_agree, dm_use_deny, dm_datetime, dm_id, dm_ip, dm_mail_type, dm_to_id)
    VALUE ('".$dm_subject."', '".$dm_from_email."', '".$value."', '".$dm_content."', '".$send_agree."', '".$send_refusal."', '".$date."', '".getSession("chk_dm_id")."', '".$_SERVER['REMOTE_ADDR']."', '".$dm_to_type."', '".$dm_id_array[$key]."') ";
        $db->ExecSql($query, "I");
    }

    $arResult = array("result" => "success", "_return" => $dm_no, "notice" => "메일을 발송했습니다.");
    echo json_encode($arResult);
    exit;
}

else if ($type == 'select_mail') {
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_start_date = isset($_REQUEST['search_start_date']) ? urldecode(trim($_REQUEST['search_start_date'])): "";
    $search_end_date = isset($_REQUEST['search_end_date']) ? urldecode(trim($_REQUEST['search_end_date'])): "";
    $search_email_type = isset($_REQUEST['search_email_type']) ? urldecode(trim($_REQUEST['search_email_type'])): "";

    $search_query = "";

    if ($search_type != "") {
        if ($search_type == "all") {
            $search_query .= " AND (dm_id LIKE '%".$search_value."%' OR dm_subject LIKE '%".$search_value."%' OR dm_content LIKE '%".$search_value."%' OR dm_datetime LIKE '%".$search_value."%')";
        } else {
            $search_query .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $search_query .= " AND `dm_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $search_query .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    if ($search_email_type) {
        $search_query .= " AND dm_mail_type = '".$search_email_type."'";
    }

    $query = "SELECT count(*) FROM `dm_mail_log` WHERE 1 = 1 ".$search_query. "group by dm_datetime";

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = "SELECT *, count(dm_datetime) as dm_count FROM `dm_mail_log` WHERE 1 = 1 ".$search_query." group by dm_datetime ORDER BY `dm_no` ASC $limit";

    $db->ExecSql($query, "S");

    $arData = array();

    if ($db-Num > 0) {
        while ($arItem = $db->Fetch()) {
            if ($arItem['dm_mail_type'] == 'level') {
                $arItem['dm_mail_type_text'] = "등급별";
            } else if ($arItem['dm_mail_type'] == 'user') {
                $arItem['dm_mail_type_text'] = "회원개인";
            } else {
                $arItem['dm_mail_type_text'] = "전체회원";
            }
            array_push($arData, $arItem);
        }
        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arData);
    } else {
        $arResult = array( "result" => "success", "_return" => "","total" => $total_count, "rows" => $arData);
    }

    echo json_encode($arResult);
}

else if ($type == 'delete_mail_log') {
    $dm_no = isset($_REQUEST['dm_no']) ? $_REQUEST['dm_no'] : "";

    $query = "SELECT * FROM dm_mail_log WHERE dm_no = '".$dm_no."'";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    $query = "DELETE FROM dm_mail_log WHERE dm_datetime = '".$row['dm_datetime']."' AND dm_id = '".$row['dm_id']."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no, "notice" => "성공했습니다.");

    echo json_encode($arResult);
}

else if ($type == "change_group_id") {
    $group_id = isset($_REQUEST['dm_group_id']) ? $_REQUEST['dm_group_id'] : "";
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "UPDATE dm_member SET dm_group_id = '".$group_id."', dm_6 = '1' WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "U");

    $arResult = array("result" => "success", "_return" => $dm_no, "notice" => "성공했습니다.");

    echo json_encode($arResult);
}

else if ($type == "general_excel") {
	$query = "SELECT * FROM dm_member WHERE dm_id <> 'admin' AND dm_leave_date = '' AND dm_group_id = 'GROUP_0000000001'";
	$db->ExecSql($query, "S");
	$EXCEL_STR = "  
		<table border='1'>
		<tr>  
			<td>이름</td>  
			<td>휴대폰번호</td>  
		</tr>";
	
	while ($row = $db->Fetch()) {
		$EXCEL_STR .= "  
			<tr>
			   <td>".$row['dm_name']."</td>  
			   <td>".$row['dm_hp']."</td>  
			</tr>  
			";
	}
	
	$EXCEL_STR .= "</table>";
	header( "Content-type: application/vnd.ms-excel" );
	header( "Content-type: application/vnd.ms-excel; charset=utf-8");
	header( "Content-Disposition: attachment; filename = 일반회원목록_".date("His").".xls" );
	header( "Content-Description: PHP4 Generated Data" );
	echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";
	echo $EXCEL_STR;
}

else if ($type == "company_excel") {
	$query = "SELECT * FROM dm_member WHERE dm_id <> 'admin' AND dm_leave_date = '' AND dm_group_id = 'GROUP_0000000002' AND dm_6 = '1'";
	$db->ExecSql($query, "S");
	$EXCEL_STR = "  
		<table border='1'>
		<tr>  
			<td>이름</td>  
			<td>휴대폰번호</td>  
		</tr>";
	
	while ($row = $db->Fetch()) {
		$EXCEL_STR .= "  
			<tr>
			   <td>".$row['dm_name']."</td>  
			   <td>".$row['dm_hp']."</td>  
			</tr>  
			";
	}
	
	$EXCEL_STR .= "</table>";
	header( "Content-type: application/vnd.ms-excel" );
	header( "Content-type: application/vnd.ms-excel; charset=utf-8");
	header( "Content-Disposition: attachment; filename = 기업회원목록_".date("His").".xls" );
	header( "Content-Description: PHP4 Generated Data" );
	echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";
	echo $EXCEL_STR;
}

else if ($type == "expert_excel") {
	$query = "SELECT * FROM dm_member WHERE dm_id <> 'admin' AND dm_leave_date = '' AND dm_group_id = 'GROUP_0000000003' AND dm_6 = '1'";
	$db->ExecSql($query, "S");
	$EXCEL_STR = "  
		<table border='1'>
		<tr>  
			<td>이름</td>  
			<td>휴대폰번호</td>  
		</tr>";
	
	while ($row = $db->Fetch()) {
		$EXCEL_STR .= "  
			<tr>
			   <td>".$row['dm_name']."</td>  
			   <td>".$row['dm_hp']."</td>  
			</tr>  
			";
	}
	
	$EXCEL_STR .= "</table>";
	header( "Content-type: application/vnd.ms-excel" );
	header( "Content-type: application/vnd.ms-excel; charset=utf-8");
	header( "Content-Disposition: attachment; filename = 전문가회원목록_".date("His").".xls" );
	header( "Content-Description: PHP4 Generated Data" );
	echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";
	echo $EXCEL_STR;
}

else if ($type == "all_excel") {
	$query = "SELECT * FROM dm_member WHERE dm_id <> 'admin' AND dm_id <> 'master' AND dm_leave_date = ''";
	$db->ExecSql($query, "S");
	$EXCEL_STR = "  
		<table border='1'>
		<tr>  
			<td>이름</td>  
			<td>휴대폰번호</td>  
		</tr>";
	
	while ($row = $db->Fetch()) {
		$EXCEL_STR .= "  
			<tr>
			   <td>".$row['dm_name']."</td>  
			   <td>".$row['dm_hp']."</td>  
			</tr>  
			";
	}
	
	$EXCEL_STR .= "</table>";
	header( "Content-type: application/vnd.ms-excel" );
	header( "Content-type: application/vnd.ms-excel; charset=utf-8");
	header( "Content-Disposition: attachment; filename = 전체회원목록_".date("His").".xls" );
	header( "Content-Description: PHP4 Generated Data" );
	echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";
	echo $EXCEL_STR;
}

?>