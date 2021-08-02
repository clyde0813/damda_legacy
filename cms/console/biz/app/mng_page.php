<?php
/**
 * Created by PhpStorm.
 * User: mooyoung
 * Date: 2020-03-31
 * Time: 오후 4:07
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();
$site_id = getSession('site_id');
$create_id = getSession('chk_dm_id');

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = " WHERE 1 = 1 and dm_status = 1";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_uid LIKE '%".$search_value."%' OR dm_page_name LIKE '%".$search_value."%' OR dm_title LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($site_id)
    {
        $where .= " AND dm_domain = '$site_id' ";
    }

    $query = "SELECT count(*) FROM `dm_pages` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT
    `dm_id` as `id`,
    `dm_page_name` as `text`,
    `dm_uid`, `dm_file_name`, `dm_file_src`, `dm_content_type`, `dm_thema`, `dm_page_division`, `dm_title`, `dm_page_type`, `dm_layout`, `dm_access_level`, `dm_board_id`, `dm_board_row_count`, `dm_version`, `dm_status`,
    `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`
    FROM `dm_pages` $where $limit";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1001');

    while ($arData = $db->Fetch()) {
        $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == "update") {
    $dm_check_id = isset($_REQUEST['chk_dm_id']) ? trim($_REQUEST['chk_dm_id']) : "";
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_file_name = isset($_REQUEST['dm_file_name']) ? trim($_REQUEST['dm_file_name']) : "";
    $dm_old_file_name = isset($_REQUEST['dm_old_file_name']) ? trim($_REQUEST['dm_old_file_name']) : "";
    $dm_file_src = isset($_REQUEST['dm_file_src']) ? trim($_REQUEST['dm_file_src']) : "";
    $dm_page_division = isset($_REQUEST['dm_page_division']) ? trim($_REQUEST['dm_page_division']) : "";
    $dm_page_name = isset($_REQUEST['dm_page_name']) ? trim($_REQUEST['dm_page_name']) : "";
    $dm_title = isset($_REQUEST['dm_title']) ? trim($_REQUEST['dm_title']) : "";
    $dm_page_type = isset($_REQUEST['dm_page_type']) ? trim($_REQUEST['dm_page_type']) : "";
    $dm_layout = isset($_REQUEST['dm_layout']) ? trim($_REQUEST['dm_layout']) : "";
    $dm_access_level = isset($_REQUEST['dm_access_level']) ? trim($_REQUEST['dm_access_level']) : "";
    $dm_uid = isset($_REQUEST['dm_uid']) ? trim($_REQUEST['dm_uid']) : "";
    $dm_board_id = "";
    $dm_board_row_count = "";
    $mode = isset($_REQUEST['dm_mode']) ? $_REQUEST['dm_mode'] : "";
    $dm_main_content = isset($_REQUEST['dm_main_content']) ? trim($_REQUEST['dm_main_content']) : "";
    $dm_create_id = getSession("chk_dm_id");

    if ($dm_page_type == "BOARD")
    {
        $dm_board_id = isset($_REQUEST['dm_board_id']) ? trim($_REQUEST['dm_board_id']) : "";
        $dm_board_row_count = isset($_REQUEST['dm_board_row_count']) ? trim($_REQUEST['dm_board_row_count']) : "";
        $dm_file_src = "";
    }
    else
    {
        $old_file = $_VAR_PAGE_PATH.$dm_old_file_name;

        $new_file = "page_" . date("YmdHis") . ".html";

        if (!$dm_uid) {
            $dm_uid = hash("md5", $new_file, false);
        }

        if (file_exists($old_file)) {
            if (is_file($old_file)) {
                if (!copy($old_file, $_VAR_PAGE_PATH.$new_file)) {
                    $arResult = array("result" => "fail", "_return" => "", "notice" => "페이지 복사 실패");
                    echo json_encode($arResult);
                    exit;
                }
            } else {
                if (!fopen($_VAR_PAGE_PATH.$new_file, "w")) {
                    $arResult = array("result" => "fail", "_return" => "", "notice" => "페이지 생성 실패");
                    echo json_encode($arResult);
                    exit;
                }
            }
        }
    }


    $query = "SELECT max(`dm_version`) as `version` FROM `dm_pages` WHERE `dm_uid` = '".$dm_uid."'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    $dm_version = $row['version'] + 0.1;

    $query = "UPDATE `dm_pages` SET `dm_status` = 0 WHERE `dm_uid` = '".$dm_uid."'";

    $db->ExecSql($query, "I");

    if ($dm_main_content) {
        // 해당 uid 제외하고 전체 0 처리
        $query = "UPDATE `dm_pages` SET `dm_main_content` = 0 WHERE `dm_uid` <> '".$dm_uid."'";
        $db->ExecSql($query, "I");
    }

    $query = "INSERT INTO `dm_pages` (`dm_id`, `dm_uid`, `dm_file_name`, `dm_file_src`, `dm_page_division`, `dm_page_name`, `dm_title`, `dm_page_type`, `dm_layout`, `dm_access_level`, `dm_board_id`, `dm_board_row_count`, `dm_version`,
    `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_main_content`, `dm_domain`)
    VALUE ('".$dm_id."', '".$dm_uid."', '".$new_file."', '".$_VAR_PAGE_RELATIVE_PATH."".$new_file."', '".$dm_page_division."', '".$dm_page_name."', '".$dm_title."', '".$dm_page_type."', '".$dm_layout."', '".$dm_access_level."', 
    '".$dm_board_id."', '".$dm_board_row_count."', '".$dm_version."','1', now(), '".$dm_create_id."', now(), '".$create_id."', '".$dm_main_content."', '".$site_id."')";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    if ($dm_page_type == "BOARD")
    {
        if (!$dm_check_id) $dm_check_id = $dm_id;
        if (!$dm_uid) $dm_uid = hash("md5", $dm_check_id, false);
        $query = "UPDATE `dm_pages` SET `dm_uid` = '".$dm_uid."' WHERE `dm_id` = '".$dm_id."'" ;
        $db->ExecSql($query, "I");
    }


    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "저장했습니다.");

    echo json_encode($arResult);

} else if ($type=="select_page") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "SELECT * FROM `dm_pages` WHERE `dm_id` = '".$dm_id."'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $row = $db->Fetch();
        $arResult = array("result" => "success", "_return" => "", "total" => "", "rows" => $row);
    } else {
        $arResult = array( "result" => "fail", "_return" => "","total" => "", "rows" => $arData);
    }

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
} else if ($type == "select_history") {
    $arData = array();

    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "SELECT `dm_uid` FROM `dm_pages` WHERE `dm_id` = '".$dm_id."'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $row = $db->Fetch();
        $query = "SELECT * FROM `dm_pages` WHERE `dm_uid` = '".$row['dm_uid']."' ORDER BY `dm_version` DESC";

        $db->ExecSql($query, "S");

        $selectCodeStatus = selectCommonCode('1001');

        if($db->Num > 0) {
            while ($row = $db->Fetch()) {
                $row['dm_status_text'] = $selectCodeStatus[$row['dm_status']];
                $arData[] = $row;
            }
        }
    }

    echo json_encode($arData, JSON_UNESCAPED_UNICODE);
} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['chk_dm_id']) ? trim($_REQUEST['chk_dm_id']) : "";
    $query = "SELECT * FROM `dm_pages` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "S");

    if ( $db->Num > 0 ) {
        $row = $db->Fetch();
        $query = "UPDATE `dm_pages` SET `dm_status` = 1 WHERE `dm_uid` = '".$row['dm_uid']."' AND `dm_version` = (".$row['dm_version']." - 0.1)";
        $db->ExecSql($query, "I");
    }

    $query = "DELETE FROM `dm_pages` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "I");
    $arResult = array("result" => "success", "_return" => "");
    echo json_encode($arResult);
}

else if ($type == 'select_table')
{
    $arData = array();
    $arReturn = array();

    $search_nm = isset($_REQUEST['search_nm']) ? trim($_REQUEST['search_nm']) : "";
    $search_status = isset($_REQUEST['search_status']) ? trim($_REQUEST['search_status']) : "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = "";

    if ($search_nm) {
        $where .= " AND dm_title LIKE '%".$search_nm."%'";
    }

    if ($search_status != "") {
        $where .= " AND dm_status = '".$search_status."'";
    }

    $query = "SELECT count(*) FROM `dm_pages` WHERE `dm_status` = 1 ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT
    `dm_id` as `id`,
    `dm_page_name` as `text`,
    `dm_uid`, `dm_file_name`, `dm_file_src`, `dm_content_type`, `dm_thema`, `dm_page_division`, `dm_title`, `dm_page_type`, `dm_layout`, `dm_access_level`, `dm_board_id`, `dm_board_row_count`, `dm_version`, `dm_status`,
    `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`
    FROM `dm_pages` WHERE `dm_status` = 1 $where $limit";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1001');

    while ($arData = $db->Fetch()) {
        $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'file_update')
{
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "SELECT * FROM dm_pages  WHERE dm_id = '$dm_id'";

    $db->ExecSql($query, "S");

    $pageInfo = $db->Fetch();

    $query = "SELECT max(`dm_version`) as `version` FROM `dm_pages` WHERE `dm_uid` = '".$pageInfo['dm_uid']."'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    $dm_version = $row['version'] + 0.1;

    $query = "UPDATE `dm_pages` SET `dm_status` = 0 WHERE `dm_uid` = '".$pageInfo['dm_uid']."'";

    $db->ExecSql($query, "I");

    $dm_file_name = explode("_", $pageInfo['dm_file_name']);
    $ext = explode(".", $dm_file_name[0]);

    $new_file = $ext[0]."_".date("YmdHis").".".$ext[1];

    $content = $_REQUEST['txt_content'];

    file_put_contents($_VAR_PAGE_PATH.$new_file, $content);

    $dm_create_id = getSession("chk_dm_id");

    $query = "INSERT INTO `dm_pages` (`dm_id`, `dm_domain`, `dm_uid`, `dm_file_name`, `dm_file_src`, `dm_page_division`, `dm_page_name`, `dm_title`, `dm_page_type`, `dm_layout`, `dm_access_level`, `dm_board_id`, `dm_board_row_count`, `dm_version`,
    `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_main_content`)
    VALUE ('', '".$site_id."', '".$pageInfo['dm_uid']."', '".$new_file."', '".$_VAR_PAGE_RELATIVE_PATH."".$new_file."', '".$pageInfo['dm_page_division']."', '".$pageInfo['dm_page_name']."', '".$pageInfo['dm_title']."', '".$pageInfo['dm_page_type']."', '".$pageInfo['dm_layout']."', '".$pageInfo['dm_access_level']."', 
    '".$pageInfo['dm_board_id']."', '".$pageInfo['dm_board_row_count']."', '".$dm_version."','1', now(), '".$dm_create_id."', now(), '".$dm_create_id."', '".$pageInfo['dm_main_content']."')";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}

else if ($type == 'combo')
{
    $arData = array();
    $arReturn = array();

    $search_nm = isset($_REQUEST['search_nm']) ? trim($_REQUEST['search_nm']) : "";
    $search_status = isset($_REQUEST['search_status']) ? trim($_REQUEST['search_status']) : "";

    if (getSession('site_id'))
    {
        $site_id = getSession('site_id');
        $where = " WHERE dm_domain = '$site_id' AND `dm_status` = 1 ";
    }

    if ($search_nm) {
        $where .= " AND dm_title LIKE '%".$search_nm."%'";
    }

    if ($search_status != "") {
        $where .= " AND dm_status = '".$search_status."'";
    }

    $query = "SELECT count(*) FROM `dm_pages` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $query = " SELECT
    `dm_id` as `id`,
    `dm_page_name` as `text`,
    `dm_uid`, `dm_file_name`, `dm_file_src`, `dm_content_type`, `dm_thema`, `dm_page_division`, `dm_title`, `dm_page_type`, `dm_layout`, `dm_access_level`, `dm_board_id`, `dm_board_row_count`, `dm_version`, `dm_status`,
    `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`
    FROM `dm_pages` $where";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1001');

    while ($arData = $db->Fetch()) {
        $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
        $arReturn[] = $arData;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type == "change_version")
{
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "SELECT * FROM dm_pages WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    $query = "UPDATE dm_pages SET dm_status = 0 WHERE dm_uid = '".$row['dm_uid']."'";

    $db->ExecSql($query, "I");

    $query = "UPDATE dm_pages SET dm_status = 1 WHERE dm_id = '".$row['dm_id']."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);

}
?>