<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-06-02
 * Time: 오후 2:51
 */

include "../../lib/lib.php";

function getMenu() {
    $db = new DBSQL();
    $db->DBconnect();
    $data = array();

    $query = "SELECT
    `dm_id` as `id`,
    `dm_nm` as `text`,
    `dm_view_title`,
    `dm_parent_id`,
    `dm_link_url`,
    `dm_is_close`,
    `dm_icon`,
    `dm_access_level`,
    `dm_status`,
    `dm_view_order`,
    `dm_create_dt`,
    `dm_create_id`,
    `dm_modify_dt`,
    `dm_modify_id`
    FROM dm_access_admin_menu order by dm_view_order";

    $db->ExecSql($query, "S");
    while($row = $db->Fetch()){
        $data[] = $row;
    }

    $node = array();
    // Build array of item references:
    foreach($data as $key => &$item) {
        $node[$item['id']] = &$item;
        $node[$item['id']]['children'] = array();
        $node[$item['id']]['data'] = new StdClass();
//        $node[$item['id']]['state'] = "closed";
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

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();

if ($type == "select")
{
    $arData = array();
    $arReturn = array();

    $arData = getMenu();

    echo json_encode($arData, JSON_UNESCAPED_UNICODE);

}

else if ($type == 'select_table')
{
    $query = "SELECT * FROM dm_access_admin_menu ";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        $arReturn[] = $arData;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'insert')
{
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_nm = isset($_REQUEST['dm_nm']) ? trim($_REQUEST['dm_nm']) : "";
    $dm_view_title = isset($_REQUEST['dm_view_title']) ? trim($_REQUEST['dm_view_title']) : "";
    $dm_parent_id = isset($_REQUEST['dm_parent_id']) ? trim($_REQUEST['dm_parent_id']) : "";

    $dm_link_url = isset($_REQUEST['dm_link_url']) ? trim($_REQUEST['dm_link_url']) : "";
    $dm_is_close = isset($_REQUEST['dm_is_close']) ? trim($_REQUEST['dm_is_close']) : "";
    $dm_icon = isset($_REQUEST['dm_icon']) ? trim($_REQUEST['dm_icon']) : "";
    $dm_access_level = isset($_REQUEST['dm_access_level']) ? trim($_REQUEST['dm_access_level']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $dm_view_order = isset($_REQUEST['dm_view_order']) ? trim($_REQUEST['dm_view_order']) : "";
    $create_id = getSession('chk_dm_id');

    $dm_link_url = htmlspecialchars($dm_link_url);

    $query = "INSERT INTO dm_access_admin_menu (`dm_id`, `dm_nm`, `dm_view_title`, `dm_parent_id`, `dm_link_url`, `dm_is_close`, `dm_icon`, `dm_access_level`, `dm_status`, `dm_view_order`, `dm_create_dt`, `dm_create_id`,
    `dm_modify_dt`, `dm_modify_id`) VALUE ('".$dm_id."', '".$dm_nm."', '".$dm_view_title."', '".$dm_parent_id."', '".$dm_link_url."', '".$dm_is_close."', '".$dm_icon."', '".$dm_access_level."', '".$dm_status."', '".$dm_view_order."',
     now(), '".$create_id."', now(), '".$create_id."') 
    ON DUPLICATE KEY UPDATE `dm_nm` = '".$dm_nm."', `dm_view_title` = '".$dm_view_title."', `dm_parent_id` = '".$dm_parent_id."', `dm_link_url` = '".$dm_link_url."', `dm_is_close` = '".$dm_is_close."', 
    `dm_icon` = '".$dm_icon."', `dm_access_level` = '".$dm_access_level."', `dm_status` = '".$dm_status."', `dm_view_order` = '".$dm_view_order."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "저장했습니다.");

    echo json_encode($arResult);
}