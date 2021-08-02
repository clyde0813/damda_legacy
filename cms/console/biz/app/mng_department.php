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

function getDepart() {

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
    `dm_depart_nm` as `text`,
    `dm_parent_id`,
    `dm_order`,
    `dm_depth`,
    `dm_create_dt`,
    `dm_create_id`,
    `dm_modify_dt`,
    `dm_modify_id`
    FROM dm_department $where order by dm_order asc";

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

    $query = "SELECT * FROM dm_department WHERE dm_parent_id = '$menu_id'";

    $db->ExecSql($query, "S");
    if ($db->Num > 0) {
        while ($row = $db->Fetch()) {
            $query = "DELETE FROM dm_department WHERE `dm_id` = '".$row['dm_id']."'";
            $db->ExecSql($query, "I");
            getData($menu_id);
        }

        $query = "DELETE FROM dm_department WHERE `dm_id` = '".$menu_id."'";
        $db->ExecSql($query, "I");
    } else {
        $query = "DELETE FROM dm_department WHERE `dm_id` = '".$menu_id."'";
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

    $query = "SELECT * FROM `dm_department` WHERE `dm_id` = '$menu_id'";

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
//    @ini_set("display_errors", 'On');
//@error_reporting(E_ALL);
    $arData = array();
    $arReturn = array();

    $arData = getDepart();

    echo json_encode($arData, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_parent_id = isset($_REQUEST['dm_parent_id']) ? trim($_REQUEST['dm_parent_id']) : "";
    $dm_depart_nm = isset($_REQUEST['dm_depart_nm']) ? trim($_REQUEST['dm_depart_nm']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? trim($_REQUEST['dm_order']) : "";
    $create_id = getSession('chk_dm_id');

    $depth = getDepth($dm_parent_id);

    $query = "INSERT INTO `dm_department` (`dm_id`, `dm_domain`, `dm_parent_id`, `dm_depart_nm`, `dm_order`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_depth`) 
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_parent_id."', '".$dm_depart_nm."', '".$dm_order."', now(), '".$create_id."', now(), '".$create_id."', '".$depth."')
    ON DUPLICATE KEY UPDATE `dm_parent_id` = '".$dm_parent_id."', `dm_depart_nm` = '".$dm_depart_nm."', `dm_order` = '".$dm_order."',
    `dm_depth` = '".$depth."', `dm_modify_dt` = now(), `dm_modify_dt` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type == 'select_table') {
    $query = "SELECT * FROM dm_department order by dm_order";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        $arReturn[] = $arData;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
} else if ($type == "delete") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    getData($dm_id);
}