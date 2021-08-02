<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-05-21
 * Time: 오후 1:46
 */


include('../../lib/lib.php');

$contentId =  isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";
$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "list";
$wr_id =  isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";
$cate =  isset($_REQUEST['cate']) ? $_REQUEST['cate'] : "";
$sType =isset($_REQUEST['sType']) ? $_REQUEST['sType'] : "";
$sValue =isset($_REQUEST['sValue']) ? $_REQUEST['sValue'] : "";


$select_query = "SELECT
    `dm_id`,
    `dm_history_nm`,
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
    global $db, $select_query;
    $db->DBconnect();
    $data = array();

    $db->ExecSql($select_query, "S");
    while($row = $db->Fetch()){
        $data[] = $row;
    }

    $node = array();
    // Build array of item references:
    foreach($data as $key => &$item) {
        $node[$item['dm_id']] = &$item;
        $node[$item['dm_id']]['children'] = array();
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

$historyList = getHistory();