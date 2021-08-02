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
            $where .= " AND (dm_goods_name LIKE '%".$search_value."%' OR dm_goods_price LIKE '%".$search_value."%' OR dm_bonus LIKE '%".$search_value."%') OR dm_datetime LIKE '%".$search_value."%'";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    $query = "SELECT count(*) FROM `dm_goods` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT * FROM `dm_goods` $where order by dm_goods_price asc $limit";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        if ($arData['dm_state'] == 1) {
            $arData['dm_status_text'] = "사용중";
        } else {
            $arData['dm_status_text'] = "사용안함";
        }
        $arData['dm_goods_price_text'] = number_format($arData['dm_goods_price']);
        $arData['dm_bonus_text'] = number_format($arData['dm_bonus']);

        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "insert" || $type == "update") {
    $dm_goods_name = isset($_REQUEST['dm_goods_name']) ? $_REQUEST['dm_goods_name'] : "";
    $dm_goods_price = isset($_REQUEST['dm_goods_price']) ? $_REQUEST['dm_goods_price'] : "";
    $dm_bonus = isset($_REQUEST['dm_bonus']) ? $_REQUEST['dm_bonus'] : "";
    $dm_state = isset($_REQUEST['dm_state']) ? $_REQUEST['dm_state'] : "";

    $query = "INSERT INTO dm_goods (dm_id, dm_goods_name, dm_goods_price, dm_bonus, dm_state, dm_datetime)
        VALUE ('".$dm_id."', '".$dm_goods_name."', '".$dm_goods_price."', '".$dm_bonus."', '".$dm_state."', now())
        ON DUPLICATE KEY UPDATE dm_goods_name = '".$dm_goods_name."', dm_goods_price = '".$dm_goods_price."', dm_bonus = '".$dm_bonus."',
        dm_state = '".$dm_state."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => "", "notice" => "성공했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "delete") {
    $query = "DELETE FROM dm_goods WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "D");

    $arResult = array("result" => "success", "_return" => "", "notice" => "성공했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "change_state") {
    $query = "UPDATE dm_goods SET dm_state = '2' WHERE dm_id = '".$dm_id."'";
    $db->ExecSql($query, "U");

    $arResult = array("result" => "success", "_return" => "", "notice" => "성공했습니다.", "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}