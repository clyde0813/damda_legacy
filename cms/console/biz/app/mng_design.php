<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-10-13
 * Time: 오전 10:26
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();
$site_id = getSession('site_id');
$create_id = getSession('chk_dm_id');
$arData = array();
$arReturn = array();

$search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
$search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;


if ($type == "visual_select") {
    $where = " WHERE 1 = 1 and dm_status = 1";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_visual_alt LIKE '%".$search_value."%' OR dm_visual_name LIKE '%".$search_value."%' OR dm_visual_mobile_name LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($site_id)
    {
        $where .= " AND dm_domain = '$site_id' ";
    }

    $query = "SELECT count(*) FROM `dm_main_visual` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = " SELECT * FROM `dm_main_visual` $where order by dm_order asc $limit";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1001');

    while ($arData = $db->Fetch()) {
        $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
        $arData['dm_image_url'] = "";
        $arData['dm_mobile_image_url'] = "";

        if ($arData['dm_visual_name']) {
            if (is_file($_VAR_POPUP_IMAGE_PATH.$arData['dm_visual_name'])) {
                $arData['dm_image_url'] = $_VAR_POPUP_IMAGE_URL.$arData['dm_visual_name'];
            }
        }
        if ($arData['dm_visual_mobile_name']) {
            if (is_file($_VAR_POPUP_IMAGE_PATH.$arData['dm_visual_mobile_name'])) {
                $arData['dm_mobile_image_url'] = $_VAR_POPUP_IMAGE_URL.$arData['dm_visual_mobile_name'];
            }
        }
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "visual_insert" || $type == "visual_update") {
//    @ini_set("display_errors", 'On');
//    @error_reporting(E_ALL);
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_visual_alt = isset($_REQUEST['dm_visual_alt']) ? trim($_REQUEST['dm_visual_alt']) : "";
    $dm_visual_link = isset($_REQUEST['dm_visual_link']) ? trim($_REQUEST['dm_visual_link']) : "";
    $dm_start_dt = isset($_REQUEST['dm_start_dt']) ? trim($_REQUEST['dm_start_dt']) : "";
    $dm_end_dt = isset($_REQUEST['dm_end_dt']) ? trim($_REQUEST['dm_end_dt']) : "";
    $dm_domain_id = isset($_REQUEST['dm_domain_id']) ? trim($_REQUEST['dm_domain_id']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? trim($_REQUEST['dm_order']) : "";
    $dm_link_target = isset($_REQUEST['dm_link_target']) ? trim($_REQUEST['dm_link_target']) : "";

    $dm_image = "";
    $dm_mobile_image = "";

    if ($_FILES['dm_visual_image'] && $_FILES['dm_visual_image']['name'] != "") {
        @mkdir($_VAR_POPUP_IMAGE_PATH, 0707);
        @chmod($_VAR_POPUP_IMAGE_PATH, 0707);

        $file = $_FILES['dm_visual_image'];

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
            $query = "SELECT * FROM `dm_main_visual` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_file = $row['dm_visual_name'];
                if (file_exists($_VAR_POPUP_IMAGE_PATH.$dm_old_file)) {
                    if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_old_file)) {
                        unlink($_VAR_POPUP_IMAGE_PATH.$dm_old_file);
                    }
                }
            }
        }

        $dm_image = $new_file_name;
    } else {
        $query = "SELECT * FROM `dm_main_visual` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_image = $row['dm_visual_name'];
    }

    if(isset($_REQUEST['dm_del_image']) && $_REQUEST['dm_del_image']) {
        if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_image)) {
            unlink($_VAR_POPUP_IMAGE_PATH.$dm_image);
            $dm_image = '';
        }
    }

    if ($_FILES['dm_visual_mobile_image'] && $_FILES['dm_visual_mobile_image']['name'] != "") {

        @mkdir($_VAR_POPUP_IMAGE_PATH, 0707);
        @chmod($_VAR_POPUP_IMAGE_PATH, 0707);

        $file = $_FILES['dm_visual_mobile_image'];

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
            $arResult = array("result" => "fail", "_return" => '모바일 비쥬얼 업로드 실패', "notice" => '모바일 비쥬얼 업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            $query = "SELECT * FROM `dm_main_visual` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_mobile_file = $row['dm_visual_mobile_name'];

                if (file_exists($_VAR_POPUP_IMAGE_PATH.$dm_old_mobile_file)) {
                    if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_old_mobile_file)) {
                        unlink($_VAR_POPUP_IMAGE_PATH.$dm_old_mobile_file);
                    }
                }
            }
        }

        $dm_mobile_image = $new_file_name;

    } else {
        $query = "SELECT * FROM `dm_main_visual` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_mobile_image = $row['dm_visual_mobile_name'];
    }

    if(isset($_REQUEST['dm_del_mobile_image']) && $_REQUEST['dm_del_mobile_image']) {
        if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_mobile_image)) {
            unlink($_VAR_POPUP_IMAGE_PATH.$dm_mobile_image);
            $dm_mobile_image = '';
        }
    }

    $query = "INSERT INTO `dm_main_visual` (`dm_id`, `dm_domain`,`dm_visual_alt`, `dm_visual_link`, `dm_visual_name`, `dm_visual_mobile_name`, `dm_start_dt`, `dm_end_dt`, `dm_domain_id`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_order`, `dm_link_target`)
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_visual_alt."', '".$dm_visual_link."', '".$dm_image."', '".$dm_mobile_image."', '".$dm_start_dt."', '".$dm_end_dt."', '".$dm_domain_id."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."', '".$dm_order."', '".$dm_link_target."')
    ON DUPLICATE KEY UPDATE `dm_visual_alt` = '".$dm_visual_alt."', `dm_start_dt` = '".$dm_start_dt."', `dm_end_dt` = '".$dm_end_dt."', `dm_visual_mobile_name` = '".$dm_mobile_image."', `dm_link_target` = '".$dm_link_target."',
   `dm_visual_link` = '".$dm_visual_link."',  `dm_visual_name` = '".$dm_image."', `dm_domain_id` = '".$dm_domain_id."', `dm_status` = '".$dm_status."', `dm_order` = '".$dm_order."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";


    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);
}
else if ($type == 'visual_delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_main_visual` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}