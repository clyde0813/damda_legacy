<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-02-01
 * Time: 오후 5:21
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$search_start_date = isset($_REQUEST['search_start_date']) ? trim($_REQUEST['search_start_date']) : "";
$search_end_date = isset($_REQUEST['search_end_date']) ? trim($_REQUEST['search_end_date']) : "";
$search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
$search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;
$db = new DBSQL();
$db->DBconnect();

if ($type == "update") {
    $wr_id = isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";
    $wr_2 = isset($_REQUEST['wr_2']) ? $_REQUEST['wr_2'] : "";
    $wr_4 = isset($_REQUEST['wr_4']) ? $_REQUEST['wr_4'] : "";

    if ($wr_2 == 2) {
        $wr_3 = date("Y-m-d H:i:s");
    } else {
        $wr_3 = "";
    }

    $query = "UPDATE dm_write_as SET wr_2 = '".$wr_2."', wr_3 = '".$wr_3."', wr_4 = '".$wr_4."' WHERE wr_id = '".$wr_id."'";
    $db->ExecSql($query, "U");

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);
}

else if ($type == 'select') {
    $arReturn = array();

    $where = " WHERE 1 = 1";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (wr_subject LIKE '%".$search_value."%' OR wr_content LIKE '%".$search_value."%' OR wr_name LIKE '%".$search_value."%' OR wr_datetime LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `wr_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where .= " AND wr_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }


    $query = "SELECT count(*) FROM `dm_write_as` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT * FROM `dm_write_as` $where ORDER BY `wr_id` DESC $limit";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {

            if($arData['wr_2'] == 1) {
                $arData['dm_status_text'] = "답변준비중";
            } else if ($arData['wr_2'] == 2) {
                $arData['dm_status_text'] = "답변완료";
            } else {
                $arData['dm_status_text'] = "";
            }
            $arReturn[] = $arData;
        }
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}