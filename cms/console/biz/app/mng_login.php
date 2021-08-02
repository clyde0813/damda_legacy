<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-04-13
 * Time: 오후 5:56
 */


include "../../lib/lib.php";

$db = new DBSQL();
$db->DBconnect();

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : "";


if ($type == "logout") {
    setSession('chk_dm_id', '');
    setSession('chk_dm_name', '');
    setSession('chk_dm_domain', '');
    setSession('chk_dm_level', '');
    setSession('login_date', '');
    session_destroy();
    goLink($_VAR_PATH_CMS.'index.html');
} else {
    $dm_mb_id = isset($_REQUEST['dm_mb_id']) ? trim($_REQUEST['dm_mb_id']) : "";
    $dm_mb_pw = isset($_REQUEST['dm_mb_password']) ? trim($_REQUEST['dm_mb_password']) : "";
    $code = 'login';
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = $_SERVER['HTTP_REFERER'];
    $agent = $_SERVER["HTTP_USER_AGENT"];

    $query = "SELECT * FROM `dm_member` WHERE `dm_id` = '".$dm_mb_id."' AND `dm_password` = md5('".$dm_mb_pw."')";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $row = $db->Fetch();
        $ar_state = selectCommonCode('1001');
        $ar_level = selectCommonCode('1002');

        $mb_level = $row['dm_level'];
        $mb_state = $ar_state[$row['dm_1']];

        if ($row['dm_1'] != 1) {
            $result = '0';
            insert_log($dm_mb_id, $ip, $code, $result, $url, $agent);
            $arResult = array( "result" => "0", "data" => $arData, "notice" => "사용할 수 없는 계정입니다.", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        if ($row['dm_leave_date'] != '') {
            $result = '0';
            insert_log($dm_mb_id, $ip, $code, $result, $url, $agent);
            $arResult = array( "result" => "0", "data" => $arData, "notice" => "탈퇴된 계정입니다.", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        setSession('chk_dm_id', $row['dm_id']);
        setSession('chk_dm_name', $row['dm_name']);
        setSession('chk_dm_domain', $row['dm_domain']);
        setSession('chk_dm_level', $mb_level);
        setSession('login_date', date("Y-m-d H:i:s"));
        setSession('site_id', $row['dm_2']);
        if ($mb_level >= 6) {
            setSession("is_admin", true);
        } else {
            setSession("is_admin", false);
        }

        $result = '1';
        insert_log($dm_mb_id, $ip, $code, $result, $url, $agent);

        $query = "UPDATE dm_member SET dm_today_login = now(), dm_visit_count = dm_visit_count + 1  WHERE dm_id ='".$dm_id."'";
        $db->ExecSql($query, "I");

        $arResult = array( "result" => "1", "data" => $arData, "notice" => "", "objName" => $_POST['objName'] );

    } else {
        $result = '0';
        insert_log($dm_mb_id, $ip, $code, $result, $url, $agent);
        $arResult = array( "result" => "0", "data" => $arData, "notice" => "아이디 또는 패스워드가 존재하지 않습니다.", "objName" => $_POST['objName'] );
    }

    echo json_encode( $arResult );
}





