<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-03-17
 * Time: 오전 10:54
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$mode =  isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";

$db = new DBSQL();
$db -> DBconnect();

if($type == "select") {

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode($_REQUEST['search_type']) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode($_REQUEST['search_value']) : "";

    $search_query = "";

    if ($search_type != "") {
        if ($search_type == "all") {
            $search_query .= " AND (dm_domain_nm LIKE '%".$search_value."%' OR dm_domain_url LIKE '%".$search_value."%' OR dm_domain_admin LIKE '%".$search_value."%')";
        } else {
            $search_query .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    $query = "SELECT count(*) FROM `dm_domain_list` WHERE 1 = 1 ".$search_query;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $query = "SELECT * FROM `dm_domain_list` WHERE 1 = 1 ".$search_query." ORDER BY `dm_id` ASC ";

    $db->ExecSql($query, "S");

    $arData = array();

    $ar_status = selectCommonCode('1001');

    if($mode) {
        $auth = getSession('chk_dm_level');
        if ($auth == 10)
        {
            $select = false;
            if (!getSession('site_id')) {
                $select = true;
            }
            $tempArr = array("dm_id" => "", "dm_domain_nm" => "전체", "selected" => $select);
            array_push($arData, $tempArr);
        }
    }

    if ($db-Num > 0) {
        while ($arItem = $db->Fetch()) {
            $arItem['dm_domain_status_text'] = $ar_status[$arItem['dm_domain_status']];
            if (getSession('site_id') == $arItem['dm_id']) {
                $arItem['selected'] = true;
            }
            array_push($arData, $arItem);
        }
        if(!$mode)
        {
            $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arData);
        }
        else
        {


            $arResult = $arData;
        }

    } else {
        $arResult = array( "result" => "success", "_return" => "","total" => $total_count, "rows" => $arData);
    }

    echo json_encode($arResult);

} else if($type=='insert' || $type=='update') {

    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_domain_nm = isset($_REQUEST['dm_domain_nm']) ? trim($_REQUEST['dm_domain_nm']) : "";
    $dm_domain_url = isset($_REQUEST['dm_domain_url']) ? trim($_REQUEST['dm_domain_url']) : "";
    $dm_domain_root = isset($_REQUEST['dm_domain_root']) ? trim($_REQUEST['dm_domain_root']) : "";
    $dm_domain_admin = isset($_REQUEST['dm_domain_admin']) ? trim($_REQUEST['dm_domain_admin']) : "";
    $dm_domain_status = isset($_REQUEST['dm_domain_status']) ? trim($_REQUEST['dm_domain_status']) : "";
    $dm_domain_description = isset($_REQUEST['dm_domain_description']) ? trim($_REQUEST['dm_domain_description']) : "";

	$db->writeLog($dm_domain_nm);

    $query = "INSERT INTO `dm_domain_list` (`dm_id`, `dm_domain_nm`, `dm_domain_url`, `dm_domain_root`, `dm_domain_admin`, `dm_domain_status`, `dm_domain_description`)
    VALUE ('".$dm_id."', '".$dm_domain_nm."', '".$dm_domain_url."', '".$dm_domain_root."', '".$dm_domain_admin."', '".$dm_domain_status."', '".$dm_domain_description."')
    ON DUPLICATE KEY UPDATE `dm_domain_nm` = '".$dm_domain_nm."', `dm_domain_url` = '".$dm_domain_url."', `dm_domain_root` = '".$dm_domain_root."', `dm_domain_admin` = '".$dm_domain_admin."',
    `dm_domain_status` = '".$dm_domain_status."', `dm_domain_description` = '".$dm_domain_description."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);
} else if ($type == "delete") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_domain_list` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => "");
    echo json_encode($arResult);
}

else if ($type == "set_site")
{
    $site_id = isset($_POST['site_id']) ? trim($_POST['site_id']): "";

    if ($site_id)
    {
        setSession("site_id", $site_id);
        $arResult = array("result" => "success", "_return" => "");
        echo json_encode($arResult);
    }
    else
    {
        echo getSession("chk_user_level");
    }

}
?>