<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-26
 * Time: 오후 5:22
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();

$search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
$search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
$dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

if ($type == "select") {
    $where = " WHERE 1 = 1";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_name LIKE '%".$search_value."%' OR mb_id LIKE '%".$search_value."%' OR dm_amount LIKE '%".$search_value."%') OR dm_datetime LIKE '%".$search_value."%'";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    $query = "SELECT count(*) FROM `dm_bank` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT * FROM `dm_bank` $where order by dm_id desc $limit";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        if ($arData['dm_state'] == 1) {
            $arData['dm_status_text'] = "입금신청";
        } else {
            $arData['dm_status_text'] = "입금완료";
        }
        $arData['dm_amount_text'] = number_format($arData['dm_amount']);
        if ($arData['dm_confirm_date'] == "0000-00-00 00:00:00") {
            $arData['dm_confirm_date'] = "-";
        }
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "insert" || $type == "update") {
    $mb_id = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : "";
    $dm_name = isset($_REQUEST['dm_name']) ? $_REQUEST['dm_name'] : "";
    $dm_amount = isset($_REQUEST['dm_amount']) ? $_REQUEST['dm_amount'] : "";
    $dm_uid = isset($_REQUEST['dm_uid']) ? $_REQUEST['dm_uid'] : "";
    $dm_state = isset($_REQUEST['dm_state']) ? $_REQUEST['dm_state'] : "";
//    $dm_datetime = isset($_REQUEST['dm_datetime']) ? $_REQUEST['dm_datetime'] : "";
    $dm_confirm_date = isset($_REQUEST['dm_confirm_date']) ? $_REQUEST['dm_confirm_date'] : "";

    if (!$dm_confirm_date && $dm_state == 2) {
        $dm_confirm_date = date("Y-m-d H:i:s");
    }

    $query = "SELECT * FROM dm_bank WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    $query = "UPDATE dm_bank SET dm_name = '".$dm_name."', dm_state = '".$dm_state."' , dm_amount = '".$dm_amount."'
    , dm_uid = '".$dm_uid."', dm_confirm_date = '".$dm_confirm_date."' WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "U");

    $query = "SELECT * FROM dm_point_log WHERE dm_id = '".$mb_id."' AND dm_uid = '".$dm_uid."'";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    if ($dm_state == 2 && !$row) {
        $query = "SELECT * FROM dm_member WHERE dm_id = '".$mb_id."'";
        $db->ExecSql($query, "S");
        $mb = $db->Fetch();

        if ($currentInfo['dm_bonus']) {
            $dm_amount = $dm_amount+$currentInfo['dm_bonus'];
        }

        $query = "UPDATE dm_member SET dm_point = dm_point + '".$dm_amount."' WHERE dm_id = '".$mb_id."'";
        $db->ExecSql($query, "U");

        $query = "INSERT INTO dm_point_log (dm_type, dm_id, dm_point, dm_remain_point, dm_datetime, dm_ip, dm_uid, dm_charge, dm_kind)
        VALUE (3, '".$mb_id."', '".$dm_amount."', '".($mb['dm_point']+$dm_amount)."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$dm_uid."', 1, 1)";
        $db->ExecSql($query, "U");

        setExpCount($mb_id, 'point', $dm_amount-$currentInfo['dm_bonus']);
    }

    $arResult = array("result" => "success", "_return" => "", "notice" => "성공했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "delete") {
    $query = "DELETE FROM dm_bank WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "S");

    $arResult = array("result" => "success", "_return" => "", "notice" => "성공했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "change_state") {
    $query = "SELECT * FROM dm_bank WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    $query = "SELECT * FROM dm_point_log WHERE dm_id = '".$currentInfo['mb_id']."' AND dm_uid = '".$currentInfo['dm_uid']."'";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    if (!$row) {
        $query = "UPDATE dm_bank SET dm_state = '2', dm_confirm_date = now() WHERE dm_id = '".$dm_id."'";
        $db->ExecSql($query, "U");

        $query = "SELECT * FROM dm_member WHERE dm_id = '".$currentInfo['mb_id']."'";
        $db->ExecSql($query, "S");
        $mb = $db->Fetch();

        $dm_amount = $currentInfo['dm_amount'];

        if ($currentInfo['dm_bonus']) {
            $dm_amount = $currentInfo['dm_amount']+$currentInfo['dm_bonus'];
        }

        $query = "UPDATE dm_member SET dm_point = dm_point + '".$dm_amount."' WHERE dm_id = '".$currentInfo['mb_id']."'";
        $db->ExecSql($query, "U");

        $query = "INSERT INTO dm_point_log (dm_type, dm_id, dm_point, dm_remain_point, dm_datetime, dm_ip, dm_uid, dm_charge, dm_kind)
        VALUE (3, '".$currentInfo['mb_id']."', '".$dm_amount."', '".($mb['dm_point']+$dm_amount)."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$currentInfo['dm_uid']."', 1, 1)";
        $db->ExecSql($query, "U");

        setExpCount($currentInfo['mb_id'], 'point', $currentInfo['dm_amount']);
    }

    $arResult = array("result" => "success", "_return" => "", "notice" => "성공했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}