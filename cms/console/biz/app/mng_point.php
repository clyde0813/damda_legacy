<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-02-04
 * Time: 오후 4:55
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $dm_id = isset($_REQUEST['dm_id']) ? urldecode(trim($_REQUEST['dm_id'])) : "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = " WHERE 1 = 1 AND dm_id = '".$dm_id."'";

    $query = "SELECT count(*) FROM `dm_point_log` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT * FROM `dm_point_log` $where order by dm_datetime desc $limit";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        if ($arData['dm_kind'] == 0) {
            $arData['dm_kind_text'] = "차감";
        } else if ($arData['dm_kind'] == 1) {
            $arData['dm_kind_text'] = "지급";
        }

        if ($arData['dm_type'] == 1) {
            $arData['dm_type_text'] = "게시글 읽기";
        } else if ($arData['dm_type'] == 2) {
            $arData['dm_type_text'] = "게시글 등록";
        } else if ($arData['dm_type'] == 3) {
            $arData['dm_type_text'] = "포인트 충전";
        } else if ($arData['dm_type'] == 4) {
            $arData['dm_type_text'] = "댓글작성";
        } else if ($arData['dm_type'] == 5) {
            $arData['dm_type_text'] = "관리자 ".$arData['dm_kind_text'];
        }

        $arData['dm_point_text'] = number_format($arData['dm_point']);
        $arData['dm_remain_point_text'] = number_format($arData['dm_remain_point']);

        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}
else if ($type == "insert") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_point = isset($_REQUEST['dm_point']) ? trim($_REQUEST['dm_point']) : "";
    $dm_memo = isset($_REQUEST['dm_memo']) ? trim($_REQUEST['dm_memo']) : "";
    $dm_kind = isset($_REQUEST['dm_kind']) ? trim($_REQUEST['dm_kind']) : "";

    $query = "SELECT * FROM dm_member WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $mb = $db->Fetch();

    if ($dm_kind == 0) {
        $total_point = (int)$mb['dm_point'] - (int)$dm_point;
    } else {
        $total_point = (int)$mb['dm_point'] + (int)$dm_point;
    }

    $query = "INSERT INTO dm_point_log (dm_type, dm_id, dm_point, dm_remain_point, dm_datetime, dm_ip, dm_kind, dm_memo)
    VALUE ('5','".$dm_id."', '".$dm_point."', '".$total_point."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$dm_kind."', '".$dm_memo."')";

    $db->ExecSql($query,"U");

    $query = "UPDATE dm_member SET dm_point = '".$total_point."' WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "U");

    if ($dm_kind == 1) {
        $text = "차감";
    } else {
        $text = "지급";
    }
    $arResult = array("result" => "success", "_return" => "", "notice" => "포인트를 정상적으로 {$text} 했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}