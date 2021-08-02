<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-03-19
 * Time: 오후 2:12
 */
include "../../lib/lib.php";

if (getSession('site_id'))
{
    $site_id = getSession('site_id');
}

function getMenu() {

    global $site_id;

    $db = new DBSQL();
    $db->DBconnect();
    $data = array();

    if ($site_id)
    {
        $where = " WHERE dm_domain = '$site_id'";
    }

    $query = "SELECT
    `dm_id` as `id`,
    `dm_menu_text` as `text`,
    `dm_parent_id`,
    `dm_menu_nm`,
    `dm_emnu_thema`,
    `dm_link_type`,
    `dm_link_data`,
    `dm_link_target`,
    `dm_menu_view`,
    `dm_create_dt`,
    `dm_create_id`,
    `dm_modify_dt`,
    `dm_modify_id`,
    `dm_menu_state`,
    `dm_menu_view`
    FROM dm_menus $where";

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

function getData($menu_id) {
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT * FROM dm_menus WHERE dm_parent_id = '$menu_id'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        while ($row = $db->Fetch()) {
            $query = "DELETE FROM dm_menus WHERE `dm_id` = '".$row['dm_id']."'";
            $db->ExecSql($query, "I");
            getData($row['dm_id']);
        }
        $query = "DELETE FROM dm_menus WHERE `dm_id` = '".$menu_id."'";
        $db->ExecSql($query, "I");
    } else {
        $query = "DELETE FROM dm_menus WHERE `dm_id` = '".$menu_id."'";
        $db->ExecSql($query, "I");

        $arResult = array("result" => "success", "_return" => "");
        echo json_encode($arResult);
    }
}

$depth_i = 1;
function getDepth($menu_id) {
    global $depth_i;
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT * FROM `dm_menus` WHERE `dm_id` = '$menu_id'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $row = $db->Fetch();
        if ($row) {
            $depth_i++;
            getDepth($row['dm_parent_id']);
        }
    }

    return $depth_i;
}

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $arData = getMenu();

    echo json_encode($arData, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_parent_id = isset($_REQUEST['dm_parent_id']) ? trim($_REQUEST['dm_parent_id']) : "";
    $dm_menu_nm = isset($_REQUEST['dm_menu_nm']) ? trim($_REQUEST['dm_menu_nm']) : "";
    $dm_menu_text = isset($_REQUEST['dm_menu_text']) ? trim($_REQUEST['dm_menu_text']) : "";
    $dm_link_type = isset($_REQUEST['dm_link_type']) ? trim($_REQUEST['dm_link_type']) : "";
    $dm_link_data = isset($_REQUEST['dm_link_data']) ? trim($_REQUEST['dm_link_data']) : "";
    $dm_link_target = isset($_REQUEST['dm_link_target']) ? trim($_REQUEST['dm_link_target']) : "";
    $dm_menu_view = isset($_REQUEST['dm_menu_view']) ? trim($_REQUEST['dm_menu_view']) : "";
    $dm_emnu_thema = isset($_REQUEST['dm_emnu_thema']) ? trim($_REQUEST['dm_emnu_thema']) : "";
    $dm_menu_order = isset($_REQUEST['dm_menu_order']) ? trim($_REQUEST['dm_menu_order']) : "";
    $dm_menu_desc = isset($_REQUEST['dm_menu_desc']) ? trim($_REQUEST['dm_menu_desc']) : "";
    $dm_menu_state = isset($_REQUEST['dm_menu_state']) ? trim($_REQUEST['dm_menu_state']) : "";
    $dm_url = isset($_REQUEST['dm_url']) ? trim($_REQUEST['dm_url']) : "";
    $dm_level = isset($_REQUEST['dm_level']) ? trim($_REQUEST['dm_level']) : "";
    $create_id = getSession('chk_dm_id');

    $depth = getDepth($dm_parent_id);

    $query = "INSERT INTO `dm_menus` (`dm_id`, `dm_domain`, `dm_parent_id`, `dm_menu_nm`, `dm_menu_text`, `dm_emnu_thema`, `dm_link_type`, `dm_link_data`, `dm_link_target`, `dm_menu_view`, `dm_menu_order`, `dm_create_dt`,
    `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_menu_desc`, `dm_depth`, `dm_url`, `dm_level`, `dm_menu_state`) VALUE ('".$dm_id."', '".$site_id."', '".$dm_parent_id."', '".$dm_menu_nm."', '".$dm_menu_text."', '".$dm_emnu_thema."', '".$dm_link_type."', '".$dm_link_data."', '".$dm_link_target."',
    '".$dm_menu_view."', '".$dm_menu_order."', now(), '".$create_id."', now(), '".$create_id."', '".$dm_menu_desc."', '".$depth."', '".$dm_url."', '".$dm_level."', '".$dm_menu_state."') ON DUPLICATE KEY UPDATE `dm_parent_id` = '".$dm_parent_id."', `dm_menu_nm` = '".$dm_menu_nm."', `dm_menu_text` = '".$dm_menu_text."',
    `dm_emnu_thema` = '".$dm_emnu_thema."', `dm_link_type` = '".$dm_link_type."', `dm_link_data` = '".$dm_link_data."', `dm_link_target` = '".$dm_link_target."', `dm_menu_view` = '".$dm_menu_view."',
    `dm_menu_order` = '".$dm_menu_order."', `dm_modify_dt` = now(), `dm_modify_dt` = '".$create_id."', `dm_menu_desc` = '".$dm_menu_desc."', `dm_depth` = '".$depth."', `dm_url` = '".$dm_url."', `dm_level` = '".$dm_level."'
    , `dm_menu_state` = '".$dm_menu_state."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type == 'select_table') {
    $query = "SELECT * FROM dm_menus order by dm_menu_desc";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        $arReturn[] = $arData;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
} else if ($type == "delete") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    getData($dm_id);
}