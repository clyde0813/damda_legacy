<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-03-30
 * Time: 오후 2:49
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

$db = new DBSQL();
$db->DBconnect();
$site_id = getSession('site_id');

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    if ($site_id)
    {
        $where = " WHERE dm_domain = '$site_id'";
    }

    $query = "
    SELECT 
    `dm_id` as `id`,
    `dm_layout_nm` as `text`,
    `dm_layout_type`,`dm_content_area`, `dm_top_content`, `dm_left_content`, `dm_right_content`, `dm_bottom_content`   
    FROM `dm_layout` $where";

    $db->ExecSql($query, "S");

    while($arData = $db->Fetch()) {
        $arReturn[] = $arData;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == 'update') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_layout_nm = isset($_REQUEST['dm_layout_nm']) ? trim($_REQUEST['dm_layout_nm']) : "";
    $dm_layout_type = isset($_REQUEST['dm_layout_type']) ? trim($_REQUEST['dm_layout_type']) : "";
    $dm_content_area = isset($_REQUEST['dm_content_area']) ? trim($_REQUEST['dm_content_area']) : "";
    $dm_top_content = isset($_REQUEST['dm_top_content']) ? trim($_REQUEST['dm_top_content']) : "";
    $dm_left_content = isset($_REQUEST['dm_left_content']) ? trim($_REQUEST['dm_left_content']) : "";
    $dm_right_content = isset($_REQUEST['dm_right_content']) ? trim($_REQUEST['dm_right_content']) : "";
    $dm_bottom_content = isset($_REQUEST['dm_bottom_content']) ? trim($_REQUEST['dm_bottom_content']) : "";
    $create_id = getSession("chk_dm_id");

    $query = "INSERT INTO `dm_layout` (`dm_id`, `dm_domain`,`dm_layout_nm`, `dm_layout_type`, `dm_content_area`, `dm_top_content`, `dm_left_content`, `dm_right_content`, `dm_bottom_content`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`)
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_layout_nm."', '".$dm_layout_type."', '".$dm_content_area."', '".$dm_top_content."', '".$dm_left_content."', '".$dm_right_content."', '".$dm_bottom_content."', now(), '".$create_id."', now(), '".$create_id."')
    ON DUPLICATE KEY UPDATE `dm_layout_nm` = '".$dm_layout_nm."', `dm_layout_type` = '".$dm_layout_type."', `dm_content_area` = '".$dm_content_area."', `dm_top_content` = '".$dm_top_content."', 
    `dm_left_content` = '".$dm_left_content."', `dm_right_content` = '".$dm_right_content."', `dm_bottom_content` = '".$dm_bottom_content."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type =="delete") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_layout` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => "", "notice" => "삭제에 성공했습니다.");

    echo json_encode($arResult);

}
?>