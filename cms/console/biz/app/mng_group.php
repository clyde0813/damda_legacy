<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-18
 * Time: 오전 10:50
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();


if ($type == "select") {
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";

    $arData = array();
    $arReturn = array();

    $where = " WHERE 1 = 1 ";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_group_id LIKE '%".$search_value."%' OR dm_group_name LIKE '%".$search_value."%' OR dm_group_desc LIKE '%".$search_value."%' OR dm_create_dt LIKE '%".$search_value."%' OR dm_create_id LIKE '%".$search_value."%' OR dm_modify_dt LIKE '%".$search_value."%' OR dm_modify_id LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    $query = "SELECT count(*) FROM `dm_group` $where";

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT * FROM `dm_group` $where order by dm_group_id asc $limit";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}

else if ($type == "insert" || $type == "update") {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";
    $dm_group_id = isset($_REQUEST['dm_group_id']) ? $_REQUEST['dm_group_id'] : "";
    $dm_group_name = isset($_REQUEST['dm_group_name']) ? $_REQUEST['dm_group_name'] : "";
    $dm_group_desc = isset($_REQUEST['dm_group_desc']) ? $_REQUEST['dm_group_desc'] : "";
    $create_id = getSession('chk_dm_id');

    if (!$dm_group_id) {
        $query = "SELECT MAX(dm_group_id) as a FROM dm_group";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $id = $row['a'];
        $id = explode("_", $id);
        $number = $id[1] + 1;
        $dm_group_id = "GROUP_".str_pad($number, "10", "0", STR_PAD_LEFT);
    }

    $query = "INSERT INTO dm_group (`dm_id`, `dm_group_id`, `dm_group_name` ,`dm_group_desc`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`)
    VALUE ('".$dm_id."', '".$dm_group_id."', '".$dm_group_name."', '".$dm_group_desc."', now(), '".$create_id."', now(), '".$create_id."' )
    ON DUPLICATE KEY UPDATE `dm_group_name` = '".$dm_group_name."', `dm_group_desc` = '".$dm_group_desc."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "성공했습니다.");

    echo json_encode($arResult);
}

else if ($type == "delete") {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "SELECT * FROM dm_group WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    $query = "DELETE FROM dm_group WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "D");

    $query = "UPDATE dm_member SET dm_group_id = '' WHERE dm_group_id = '".$row['dm_group_id']."'";

    $db->ExecSql($query, "U");

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "성공했습니다.");

    echo json_encode($arResult);

}

else if ($type == "select_code") {
    $arReturn = array();

    $query = "SELECT * FROM dm_group";

    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        $arReturn[] = $row;
    }

    echo json_encode($arReturn);
}
?>