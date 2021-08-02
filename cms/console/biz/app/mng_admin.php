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
$site_id = getSession("site_id");

if($type == "select") {
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_start_date = isset($_REQUEST['search_start_date']) ? urldecode(trim($_REQUEST['search_start_date'])): "";
    $search_end_date = isset($_REQUEST['search_end_date']) ? urldecode(trim($_REQUEST['search_end_date'])): "";
    $search_level = isset($_REQUEST['search_level']) ? urldecode(trim($_REQUEST['search_level'])): "";

    $search_query = " AND dm_level >= 6";

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
    $dm_name = isset($_REQUEST['dm_name']) ? trim($_REQUEST['dm_name']) : "";
    $dm_nick = isset($_REQUEST['dm_nick']) ? trim($_REQUEST['dm_nick']) : "";
    $dm_password = isset($_REQUEST['dm_password']) ? trim($_REQUEST['dm_password']) : "";
    $dm_level = isset($_REQUEST['dm_level']) ? trim($_REQUEST['dm_level']) : "";
    $dm_sex = isset($_REQUEST['dm_sex']) ? trim($_REQUEST['dm_sex']) : "";
    $dm_email = isset($_REQUEST['dm_email']) ? trim($_REQUEST['dm_email']) : "";
    $dm_homepage = isset($_REQUEST['dm_homepage']) ? trim($_REQUEST['dm_homepage']) : "";
    $dm_hp = isset($_REQUEST['dm_hp']) ? trim($_REQUEST['dm_hp']) : "";
    $dm_tel = isset($_REQUEST['dm_tel']) ? trim($_REQUEST['dm_tel']) : "";
    $dm_addr1 = isset($_REQUEST['dm_addr1']) ? $_REQUEST['dm_addr1'] : "";
    $dm_addr2 = isset($_REQUEST['dm_addr2']) ? $_REQUEST['dm_addr2'] : "";
    $dm_addr3 = isset($_REQUEST['dm_addr3']) ? $_REQUEST['dm_addr3'] : "";
    $dm_addr_jibeon = isset($_REQUEST['dm_addr_jibeon']) ? $_REQUEST['dm_addr_jibeon'] : "";
    $dm_mailling = isset($_REQUEST['dm_mailling']) ? trim($_REQUEST['dm_mailling']) : "";
    $dm_sms = isset($_REQUEST['dm_sms']) ? trim($_REQUEST['dm_sms']) : "";
    $dm_memo = isset($_REQUEST['dm_memo']) ? $_REQUEST['dm_memo'] : "";
    $dm_leave_date = isset($_REQUEST['dm_leave_date']) ? trim($_REQUEST['dm_leave_date']) : "";
    $dm_intercept_date = isset($_REQUEST['dm_intercept_date']) ? trim($_REQUEST['dm_intercept_date']) : "";
    $dm_1 = isset($_REQUEST['dm_1']) ? $_REQUEST['dm_1'] : "";
    $dm_accept_ip = isset($_REQUEST['dm_accept_ip']) ? $_REQUEST['dm_accept_ip'] : "";

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


    $update_query = "";

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

    $query = "INSERT INTO `dm_member` (`dm_no`, `dm_domain`, `dm_id`, `dm_password`, `dm_name`, `dm_nick`, `dm_nick_date`, `dm_email`, `dm_homepage`, `dm_level`, `dm_sex`, `dm_birth`, `dm_tel`, `dm_hp`, `dm_certify`, `dm_adult`, `dm_dupinfo`, `dm_zip1`, `dm_zip2`,
    `dm_addr1`, `dm_addr2`, `dm_addr3`, `dm_addr_jibeon`, `dm_signature`, `dm_recommend`, `dm_point`, `dm_today_login`, `dm_login_ip`, `dm_datetime`, `dm_ip`, `dm_leave_date`, `dm_intercept_date`, `dm_email_certify`, `dm_email_certify2`, `dm_memo`, `dm_lost_certify`,
    `dm_mailling`, `dm_sms`, `dm_open`, `dm_open_date`, `dm_profile`, `dm_memo_call`, `dm_memo_cnt`, `dm_scrap_cnt`, `dm_1`, `dm_accept_ip`)
    VALUE ('".$dm_no."', '".$site_id."', '".$dm_id."', md5('".$dm_password."'), '".$dm_name."', '".$dm_nick."', now(), '".$dm_email."', '".$dm_homepage."', '".$dm_level."', '".$dm_sex."', '','".$dm_tel."', '".$dm_hp."', '".$dm_certify."', '".$dm_adult."', '', '".$dm_zip1."', '".$dm_zip2."', '".$dm_addr1."',
    '".$dm_addr2."', '".$dm_addr3."', '".$dm_addr_jibeon."', '".$dm_signature."', '', '0', '', '', now(), '".$_SERVER['REMOTE_ADDR']."', '".$dm_leave_date."', '".$dm_intercept_date."', '', '', '".$dm_memo."', '', '".$dm_mailling."', '".$dm_sms."', '', '', '', '', '', '', '".$dm_1."', '".$dm_accept_ip."')
     ON DUPLICATE KEY UPDATE `dm_name` = '".$dm_name."', `dm_email` = '".$dm_email."', `dm_homepage` = '".$dm_homepage."',`dm_level` = '".$dm_level."',
    `dm_sex` = '".$dm_sex."', `dm_tel` = '".$dm_tel."', `dm_hp` = '".$dm_hp."', `dm_certify` = '".$dm_certify."', `dm_adult` = '".$dm_adult."', `dm_zip1` = '".$dm_zip1."', `dm_zip2` = '".$dm_zip2."', `dm_addr1` = '".$dm_addr1."', `dm_addr2` = '".$dm_addr2."',
    `dm_addr3` = '".$dm_addr3."', `dm_addr_jibeon` = '".$dm_addr_jibeon."', `dm_signature` = '".$dm_signature."', `dm_leave_date` = '".$dm_leave_date."', `dm_intercept_date` = '".$dm_intercept_date."', `dm_memo` = '".$dm_memo."', `dm_mailling` = '".$dm_mailling."',
    `dm_sms` = '".$dm_sms."', `dm_1` = '".$dm_1."', `dm_domain` = '".$site_id."', `dm_accept_ip` = '".$dm_accept_ip."' " . $update_query;

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

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);
}
?>