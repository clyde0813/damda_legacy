<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-04-01
 * Time: 오후 1:53
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

    if ($site_id)
    {
        $where .= " AND dm_domain = '$site_id'";
    }

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_popup_nm LIKE '%".$search_value."%' OR dm_start_dt LIKE '%".$search_value."%' OR dm_end_dt LIKE '%".$search_value."%' OR dm_create_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_status) {
        $where .= " AND dm_status = '".$search_status."'";
    }

    $query = "SELECT count(*) FROM `dm_popup` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT * FROM `dm_popup` $where ORDER BY `dm_id` DESC";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];

            $arData['dm_start_dt'] = date("Y-m-d", strtotime($arData['dm_start_dt']));
            $arData['dm_end_dt'] = date("Y-m-d", strtotime($arData['dm_end_dt']));
            if ($arData['dm_content_type'] == 1) {
                $arData['dm_content_type_text'] = "페이지";
            } else {
                $arData['dm_content_type_text'] = "이미지";
                $file_array = explode("/", $arData['dm_popup_page']);
                if (count($file_array) > 1) {
                    $arData['dm_file_url'] = $_VAR_POPUP_IMAGE_URL.end($file_array);
                } else {
                    $arData['dm_file_url'] = "";
                }

            }
            $arReturn[] = $arData;
        }
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == 'update') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_domain_id = isset($_REQUEST['dm_domain_id']) ? trim($_REQUEST['dm_domain_id']) : "";
    $dm_popup_nm = isset($_REQUEST['dm_popup_nm']) ? trim($_REQUEST['dm_popup_nm']) : "";
    $dm_popup_page = "";
    $dm_popup_expired = isset($_REQUEST['dm_popup_expired']) ? trim($_REQUEST['dm_popup_expired']) : "";
    $dm_popup_type = isset($_REQUEST['dm_popup_type']) ? trim($_REQUEST['dm_popup_type']) : "";
    $dm_content_type = isset($_REQUEST['dm_content_type']) ? trim($_REQUEST['dm_content_type']) : "";
    $dm_link = isset($_REQUEST['dm_link']) ? trim($_REQUEST['dm_link']) : "";
    $dm_link_type = isset($_REQUEST['dm_link_type']) ? trim($_REQUEST['dm_link_type']) : "";
    $dm_start_dt = isset($_REQUEST['dm_start_dt']) ? trim($_REQUEST['dm_start_dt']) : "";
    $dm_end_dt = isset($_REQUEST['dm_end_dt']) ? trim($_REQUEST['dm_end_dt']) : "";
    $dm_popup_width = isset($_REQUEST['dm_popup_width']) ? trim($_REQUEST['dm_popup_width']) : "";
    $dm_popup_height = isset($_REQUEST['dm_popup_height']) ? trim($_REQUEST['dm_popup_height']) : "";
    $dm_popup_top = isset($_REQUEST['dm_popup_top']) ? trim($_REQUEST['dm_popup_top']) : "";
    $dm_popup_top = isset($_REQUEST['dm_popup_top']) ? trim($_REQUEST['dm_popup_top']) : "";
    $dm_popup_left = isset($_REQUEST['dm_popup_left']) ? trim($_REQUEST['dm_popup_left']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $create_id = getSession("chk_dm_id");


    if ($dm_content_type == 1) {
        $dm_popup_page = $_REQUEST['dm_page'];
    } else {

        if ($_FILES['dm_popup_image'] && $_FILES['dm_popup_image']['name'] != "") {
            @mkdir($_VAR_POPUP_IMAGE_PATH, 0707);
            @chmod($_VAR_POPUP_IMAGE_PATH, 0707);

            $file = $_FILES['dm_popup_image'];

            $ext_str = "jpg,gif,png,JPG,GIF,PNG";

            $allowed_extensions = explode(',', $ext_str);

            $ext = substr($file['name'], strrpos($file['name'], '.') + 1);

            // 확장자 체크
            if(!in_array($ext, $allowed_extensions)) {
                $arResult = array("result" => "fail", "_return" => '업로드할 수 없는 확장자 입니다.', "notice" => '업로드할 수 없는 확장자 입니다.');
                echo json_encode($arReturn);
                exit;
            }

            // 파일 크기 체크
            if($file['size'] >= $_VAR_MAX_FILE_SIZE) {
                $arResult = array("result" => "fail", "_return" => '2MB 까지만 업로드 가능합니다.', "notice" => '2MB 까지만 업로드 가능합니다.');
                echo json_encode($arReturn);
                exit;
            }

            $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

            if(!move_uploaded_file($file['tmp_name'], $_VAR_POPUP_IMAGE_PATH.$new_file_name)) {
                $arResult = array("result" => "fail", "_return" => '파일업로드 실패', "notice" => '2MB 까지만 업로드 가능합니다.');
                echo json_encode($arReturn);
                exit;
            } else {
                $query = "SELECT * FROM `dm_popup` WHERE `dm_id` = '$dm_id'";

                $db->ExecSql($query, "S");

                $row = $db->Fetch();

                if ($row) {
                    $dm_old_file = $row['dm_popup_page'];
                    if (file_exists($_VAR_POPUP_IMAGE_PATH.$dm_old_file)) {
                        if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_old_file)) {
                            unlink($_VAR_POPUP_IMAGE_PATH.$dm_old_file);
                        }
                    }
                }
            }

            $dm_popup_page = "/diam/web/data/popup/".$new_file_name;
        } else {

            if(isset($_REQUEST['del_image']) && $_REQUEST['del_image']) {
                $query = "SELECT * FROM `dm_popup` WHERE `dm_id` = '$dm_id'";

                $db->ExecSql($query, "S");

                $row = $db->Fetch();

                $dm_popup_page = $row['dm_popup_page'];

                if (is_file($dm_popup_page)) {
                    unlink($dm_popup_page);
                    $dm_popup_page = '';
                }
            } else {
                $query = "SELECT * FROM `dm_popup` WHERE `dm_id` = '$dm_id'";

                $db->ExecSql($query, "S");

                $row = $db->Fetch();

                $dm_popup_page = $row['dm_popup_page'];
            }
        }
    }

    $query = "INSERT INTO `dm_popup` 
    (`dm_id`, `dm_domain`,`dm_popup_nm`, `dm_popup_type`, `dm_start_dt`, `dm_end_dt`, `dm_popup_width`, `dm_popup_height`, `dm_popup_top`, `dm_popup_left`, `dm_domain_id`, `dm_status`, 
    `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_content_type`, dm_link, dm_link_type, dm_popup_expired, dm_popup_page)
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_popup_nm."', '".$dm_popup_type."', '".$dm_start_dt."', '".$dm_end_dt."', '".$dm_popup_width."', '".$dm_popup_height."', '".$dm_popup_top."', 
    '".$dm_popup_left."', '".$dm_domain_id."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."', '".$dm_content_type."', '".$dm_link."', '".$dm_link_type."', '".$dm_popup_expired."', '".$dm_popup_page."')
    ON DUPLICATE KEY UPDATE `dm_popup_nm` = '".$dm_popup_nm."', `dm_popup_type` = '".$dm_popup_type."', `dm_start_dt` = '".$dm_start_dt."', `dm_end_dt` = '".$dm_end_dt."', `dm_popup_width` = '".$dm_popup_width."', `dm_popup_height` = '".$dm_popup_height."',
    `dm_popup_top` = '".$dm_popup_top."', `dm_popup_left` = '".$dm_popup_left."', `dm_domain_id` = '".$dm_domain_id."', `dm_status` = '".$dm_status."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."',
    dm_content_type = '".$dm_content_type."', dm_link = '".$dm_link."', dm_link_type = '".$dm_link_type."', dm_popup_expired = '".$dm_popup_expired."', dm_popup_page = '".$dm_popup_page."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_popup` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}

else if ($type == 'select_page') {
    $arData = array();
    $arReturn = array();

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = " WHERE 1 = 1 and dm_status = 1 and dm_page_division = 2";

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
}


else if ($type == 'combo') {
    $arData = array();
    $arReturn = array();


    $where = " WHERE 1 = 1 and dm_status = 1 and dm_page_division = 2";

    if ($site_id)
    {
        $where .= " AND dm_domain = '$site_id' ";
    }

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