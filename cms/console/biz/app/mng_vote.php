<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-08
 * Time: 오전 9:42
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();
$site_id = getSession('site_id');

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_status = isset($_REQUEST['search_status']) ? urldecode(trim($_REQUEST['search_status'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = "WHERE 1 = 1 ";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_vote_name LIKE '%".$search_value."%' OR dm_start_dt LIKE '%".$search_value."%' OR dm_end_dt LIKE '%".$search_value."%' OR dm_create_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_status) {
        $where .= " AND dm_status = '".$search_status."'";
    }

    $query = "SELECT count(*) FROM `dm_vote` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT * FROM `dm_vote` $where ORDER BY `dm_id` DESC";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
            $arData['dm_start_dt'] = date("Y-m-d", strtotime($arData['dm_start_dt']));
            $arData['dm_end_dt'] = date("Y-m-d", strtotime($arData['dm_end_dt']));
            if ($arData['dm_vote1_count'] > 0) {
                $arData['dm_vote1_percent'] = round(($arData['dm_vote1_count'] / ($arData['dm_vote1_count'] + $arData['dm_vote2_count']) * 100), 2);
                $arData['dm_vote2_percent'] = 100 - $arData['dm_vote1_percent'];
            } else if ($arData['dm_vote1_count'] <= 0 && $arData['dm_vote2_count'] > 0){
                $arData['dm_vote2_percent'] = round(($arData['dm_vote2_count'] / ($arData['dm_vote1_count'] + $arData['dm_vote2_count']) * 100), 2);
                $arData['dm_vote1_percent'] = 100 - $arData['dm_vote2_percent'];
            } else {
                $arData['dm_vote2_percent'] = 0;
                $arData['dm_vote1_percent'] = 0;
            }
            $arData['dm_category_text'] = ($arData['dm_vote_category'] == 1) ? "공감지표" : "이 종목 어때";

            $arReturn[] = $arData;
        }
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == 'update') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_vote_name = isset($_REQUEST['dm_vote_name']) ? trim($_REQUEST['dm_vote_name']) : "";
    $dm_vote_category = isset($_REQUEST['dm_vote_category']) ? trim($_REQUEST['dm_vote_category']) : "";
    $dm_vote1 = isset($_REQUEST['dm_vote1']) ? trim($_REQUEST['dm_vote1']) : "";
    $dm_vote2 = isset($_REQUEST['dm_vote2']) ? trim($_REQUEST['dm_vote2']) : "";
    $dm_vote1_count = isset($_REQUEST['dm_vote1_count']) ? trim($_REQUEST['dm_vote1_count']) : "";
    $dm_vote2_count = isset($_REQUEST['dm_vote2_count']) ? trim($_REQUEST['dm_vote2_count']) : "";
    $dm_start_dt = isset($_REQUEST['dm_start_dt']) ? trim($_REQUEST['dm_start_dt']) : "";
    $dm_end_dt = isset($_REQUEST['dm_end_dt']) ? trim($_REQUEST['dm_end_dt']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $create_id = getSession("chk_dm_id");

    $query = "INSERT INTO `dm_vote` (`dm_id`, `dm_vote_name`, `dm_vote1`, `dm_vote2`, `dm_vote1_count`, `dm_vote2_count`, `dm_start_dt`, `dm_end_dt`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_ip`,
    `dm_vote_category`)
    VALUE ('".$dm_id."', '".$dm_vote_name."', '".$dm_vote1."', '".$dm_vote2."', '".$dm_vote1_count."', '".$dm_vote2_count."',  '".$dm_start_dt."', '".$dm_end_dt."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."',
    '".$_SERVER['REMOTE_ADDR']."', '".$dm_vote_category."')
    ON DUPLICATE KEY UPDATE `dm_vote_name` = '".$dm_vote_name."', `dm_vote1` = '".$dm_vote1."', `dm_vote2` = '".$dm_vote2."', `dm_vote1_count` = '".$dm_vote1_count."', `dm_vote2_count` = '".$dm_vote2_count."', `dm_vote_category` = '".$dm_vote_category."',
    `dm_start_dt` = '".$dm_start_dt."', `dm_end_dt` = '".$dm_end_dt."', `dm_status` = '".$dm_status."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_vote` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}

else if ($type == 'vote_detail') {
    $arData = array();
    $arReturn = array();

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_vote = isset($_REQUEST['search_vote']) ? urldecode(trim($_REQUEST['search_vote'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = "WHERE 1 = 1 ";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (b.dm_vote_name LIKE '%".$search_value."%' OR b.dm_start_dt LIKE '%".$search_value."%' OR b.dm_end_dt LIKE '%".$search_value."%' OR b.dm_create_dt LIKE '%".$search_value."%' OR a.mb_id LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_vote && $search_vote != "전체") {
        $where .= " AND a.dm_vote_id = '".$search_vote."'";
    }

    $query = "SELECT count(*) FROM dm_vote_log AS a INNER JOIN dm_vote AS b ON a.dm_vote_id = b.dm_id ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT a.*, b.dm_vote_category, b.dm_vote_name, b.dm_vote1, b.dm_vote2 FROM dm_vote_log AS a INNER JOIN dm_vote AS b ON a.dm_vote_id = b.dm_id  $where ORDER BY `dm_id` DESC";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $arData['dm_category_text'] = ($arData['dm_vote_category'] == 1) ? "공감지표" : "이 종목 어때";
            $arData['vote_text'] = $arData['dm_vote'.$arData['dm_item']];

            $arReturn[] = $arData;
        }
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}