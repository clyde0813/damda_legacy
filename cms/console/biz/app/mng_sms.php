<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-07-01
 * Time: 오전 10:21
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$search_start_date = isset($_REQUEST['search_start_date']) ? urldecode($_REQUEST['search_start_date']) : "";
$search_end_date = isset($_REQUEST['search_end_date']) ? urldecode($_REQUEST['search_end_date']) : "";
$search_kind = isset($_REQUEST['search_kind']) ? urldecode($_REQUEST['search_kind']) : "";
$search_status = isset($_REQUEST['search_status']) ? urldecode($_REQUEST['search_status']) : "";
$search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
$search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;


$db = new DBSQL();
$db->DBconnect();

$arReturn = array();


if ($type == "select")
{
    $where = " WHERE 1 = 1 ";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_request_dt` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where .= " AND dm_request_dt <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    if ($search_kind) {
        $where .= " AND dm_sms_type = '".$search_kind."'";
    }

    if ($search_status) {
        $where .= " AND dm_sms_result = '".$search_status."'";
    }

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_sms_no LIKE '%".$search_value."%' OR dm_request_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    $query = "SELECT count(*) FROM `dm_sms` $where";

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = "SELECT * FROM `dm_sms` $where $limit";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1025');
    $selectCodeType = selectCommonCode('1024');

    while ($arData = $db->Fetch()) {
        $arData['dm_sms_type_text'] = $selectCodeType[$arData['dm_sms_type']];
        $arData['dm_sms_result_text'] = $selectCodeStatus[$arData['dm_sms_result']];
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'get_sms_config')
{
    $sms_count = 0;
    $lms_count = 0;
    $mms_count = 0;

    $query = "SELECT * FROM dm_sms_config";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    if ($row['dm_remain'])
    {
        $sms_count = ceil($row['dm_remain'] / $row['dm_sms_price']);
        $lms_count = ceil($row['dm_remain'] / $row['dm_lms_price']);
        $mms_count = ceil($row['dm_remain'] / $row['dm_mms_price']);
    }

    $row['dm_sms_count'] = $sms_count;
    $row['dm_lms_count'] = $lms_count;
    $row['dm_mms_count'] = $mms_count;

    echo json_encode($row, JSON_UNESCAPED_UNICODE);
}