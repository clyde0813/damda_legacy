<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-04-10
 * Time: 오후 2:36
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();
$site_id = getSession('site_id');

$select_query = "SELECT
    `dm_id` as `id`,
    `dm_history_nm` as `text`,
    `dm_parent_id`,
    `dm_history_text`,
    `dm_item_type`,
    `dm_link`,
    `dm_type`,
    `dm_order`,
    `dm_status`,
    `dm_create_dt`,
    `dm_create_id`,
    `dm_modify_dt`,
    `dm_modify_id`
    FROM `dm_history`";

function getHistory() {
    global $db;
    $db->DBconnect();
    $data = array();
    global $select_query;

    $db->ExecSql($select_query, "S");
    while($row = $db->Fetch()){
        $data[] = $row;
    }

    $node = array();
    // Build array of item references:
    foreach($data as $key => &$item) {
        $node[$item['id']] = &$item;
        $node[$item['id']]['children'] = array();
        $node[$item['id']]['data'] = new StdClass();
    }

    // Set items as children of the relevant parent item.
    foreach($data as $key => &$item) {
        if($item['dm_parent_id'] && isset($node[$item['dm_parent_id']])) {
            $node [$item['dm_parent_id']]['children'][] = &$item;
        }
    }

    // Remove items that were added to parents elsewhere:
    foreach($data as $key => &$item) {
        if($item['dm_parent_id'] && isset($node[$item['dm_parent_id']]))
            unset($data[$key]);
    }

    if (empty($data)) {
        $data = "";
    }
    return $data;
}

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $arData = getHistory();

    echo json_encode($arData, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == "update") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_parent_id = isset($_REQUEST['dm_parent_id']) ? trim($_REQUEST['dm_parent_id']) : "";
    $dm_history_nm = isset($_REQUEST['dm_history_nm']) ? trim($_REQUEST['dm_history_nm']) : "";
    $dm_history_text = isset($_REQUEST['dm_history_text']) ? trim($_REQUEST['dm_history_text']) : "";
    $dm_item_type = isset($_REQUEST['dm_item_type']) ? trim($_REQUEST['dm_item_type']) : "";
    $dm_link = isset($_REQUEST['dm_link']) ? trim($_REQUEST['dm_link']) : "";
    $dm_type = isset($_REQUEST['dm_type']) ? trim($_REQUEST['dm_type']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? trim($_REQUEST['dm_order']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $create_id = getSession("chk_dm_id");

    $query = "INSERT INTO `dm_history` (`dm_id`, `dm_domain`, `dm_parent_id`, `dm_history_nm`, `dm_history_text`, `dm_item_type`, `dm_link`, `dm_type`, `dm_order`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`)
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_parent_id."', '".$dm_history_nm."', '".$dm_history_text."', '".$dm_item_type."', '".$dm_link."', '".$dm_type."', '".$dm_order."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."')
    ON DUPLICATE KEY UPDATE `dm_parent_id` = '".$dm_parent_id."', `dm_history_nm` = '".$dm_history_nm."', `dm_history_text` = '".$dm_history_text."', `dm_item_type` = '".$dm_item_type."',
    `dm_link` = '".$dm_link."', `dm_type` = '".$dm_type."', `dm_order` = '".$dm_order."', `dm_status` = '".$dm_status."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);


} else if ($type == "select_table") {

    $arReturn = array();

    $db->ExecSql($select_query, "S");
    while($row = $db->Fetch()){
        $arReturn[] = $row;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim ($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_history` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");
    $arResult = array("result" => "success", "_return" => "", "notice" => "삭제에 성공했습니다.");
    echo json_encode($arResult);
}

