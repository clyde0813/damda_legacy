<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-05-25
 * Time: 오후 5:20
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$dm_id =  isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

$db = new DBSQL();
$db->DBconnect();

$site_id = getSession('site_id');
if ($type == "select")
{
    $arData = array();
    $arReturn = array();

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_status = isset($_REQUEST['search_status']) ? urldecode(trim($_REQUEST['search_status'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = " WHERE 1 = 1";

    if ($site_id)
    {
        $where = " WHERE dm_domain = '$site_id'";
    }

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_question LIKE '%".$search_value."%' OR dm_answer LIKE '%".$search_value."%' OR dm_create_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_status != "") {
        $where .= " AND dm_status = '".$search_status."'";
    }

    $query = "SELECT * FROM dm_faq $where";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1001');

    while ($arData = $db->Fetch()) {
        $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
        $arReturn[] = $arData;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type =='select_faq')
{

    $query = "SELECT * FROM dm_faq WHERE `dm_id` = '".$dm_id."'";
    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $row = $db->Fetch();
        $arResult = array("result" => "success", "_return" => "", "total" => "", "rows" => $row);
    } else {
        $arResult = array( "result" => "fail", "_return" => "","total" => "", "rows" => $arData);
    }

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "insert" || $type == "update")
{
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_question = isset($_REQUEST['dm_question']) ? trim($_REQUEST['dm_question']) : "";
    $dm_answer = isset($_REQUEST['dm_answer']) ? trim($_REQUEST['dm_answer']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? $_REQUEST['dm_order'] : "";

    $dm_create_id = getSession("chk_dm_id");

    if ($type == "insert")
    {
        $query = "INSERT INTO dm_faq (`dm_domain`, `dm_question`, `dm_answer`, `dm_status`, `dm_order`, `dm_create_dt`, `dm_create_id`, `dm_modify_id`, `dm_modify_dt`) 
    VALUE ('".$site_id."', '".$dm_question."', '".$dm_answer."', '".$dm_status."', '".$dm_order."', now(), '".$dm_create_id."', '".$dm_create_id."',  now())";
    }

    else if ($type == 'update')
    {
        $query = "UPDATE dm_faq SET `dm_question` = '".$dm_question."', `dm_answer` = '".$dm_answer."', `dm_status` = '".$dm_status."', `dm_order` = '".$dm_order."', 
        `dm_modify_id` = '".$dm_create_id."', `dm_modify_dt` = now() WHERE dm_id = '".$dm_id."'";
    }

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);
}

else if ($type =='delete')
{
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_faq` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "I");
    $arResult = array("result" => "success", "_return" => "");
    echo json_encode($arResult);
}