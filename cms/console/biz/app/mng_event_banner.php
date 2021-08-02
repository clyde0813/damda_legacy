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
$site_id = 1;

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

    $query = "SELECT count(*) FROM `dm_event_banner` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT * FROM `dm_event_banner` $where ORDER BY `dm_id` DESC";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
            if ($arData['dm_popup_image']) {
                if (is_file($_VAR_POPUP_IMAGE_PATH.$arData['dm_popup_image'])) {
                    $arData['dm_image_url'] = $_VAR_POPUP_IMAGE_URL.$arData['dm_popup_image'];
                }
            }
            if ($arData['dm_popup_mobile_image']) {
                if (is_file($_VAR_POPUP_IMAGE_PATH.$arData['dm_popup_mobile_image'])) {
                    $arData['dm_mobile_image_url'] = $_VAR_POPUP_IMAGE_URL.$arData['dm_popup_mobile_image'];
                }
            }
            $arReturn[] = $arData;
        }
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == 'update') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_popup_nm = isset($_REQUEST['dm_popup_nm']) ? trim($_REQUEST['dm_popup_nm']) : "";
    $dm_popup_type = isset($_REQUEST['dm_popup_type']) ? trim($_REQUEST['dm_popup_type']) : "";
    $dm_popup_link = isset($_REQUEST['dm_popup_link']) ? trim($_REQUEST['dm_popup_link']) : "";
    $dm_start_dt = isset($_REQUEST['dm_start_dt']) ? trim($_REQUEST['dm_start_dt']) : "";
    $dm_end_dt = isset($_REQUEST['dm_end_dt']) ? trim($_REQUEST['dm_end_dt']) : "";
    $dm_domain_id = isset($_REQUEST['dm_domain_id']) ? trim($_REQUEST['dm_domain_id']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $dm_link_target = isset($_REQUEST['dm_link_target']) ? trim($_REQUEST['dm_link_target']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? trim($_REQUEST['dm_order']) : "";
    $create_id = getSession("chk_dm_id");

    $dm_image = "";
    $dm_html = "";

    if ($_FILES['dm_popup_image'] && $_FILES['dm_popup_image']['name'] != "") {

        @mkdir($_VAR_POPUP_IMAGE_PATH, 0707);
        @chmod($_VAR_POPUP_IMAGE_PATH, 0707);

        $file = $_FILES['dm_popup_image'];

        $ext_str = "jpg,gif,png,JPG,GIF,PNG";

        $allowed_extensions = explode(',', $ext_str);

        $ext = substr($file['name'], strrpos($file['name'], '.') + 1);

        // 확장자 체크
        if(!in_array($ext, $allowed_extensions)) {
            $arResult = array("result" => "fail", "_return" => '업로드할 수 없는 확장자 입니다.');
            echo json_encode($arReturn);
            exit;
        }

        // 파일 크기 체크
        if($file['size'] >= $_VAR_MAX_FILE_SIZE) {
            $arResult = array("result" => "fail", "_return" => '2MB 까지만 업로드 가능합니다.');
            echo json_encode($arReturn);
            exit;
        }


        if ($_FILES['dm_popup_mobile_image'] && $_FILES['dm_popup_mobile_image']['name'] != "") {
            $mobile_file = $_FILES['dm_popup_mobile_image'];
            $mobile_ext = substr($mobile_file['name'], strrpos($mobile_file['name'], '.') + 1);
            $new_mobile_file_name = date("YmdHis")."_".hash("md5", $mobile_file['name']).".".$ext;

            if(!move_uploaded_file($mobile_file['tmp_name'], $_VAR_POPUP_IMAGE_PATH.$new_mobile_file_name)) {
                $arResult = array("result" => "fail", "_return" => '모바일 파일업로드 실패');
                echo json_encode($arReturn);
                exit;
            }

        }


        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_POPUP_IMAGE_PATH.$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            $query = "SELECT * FROM `dm_event_banner` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_file = $row['dm_popup_image'];
                $dm_old_mobile_file = $row['dm_popup_mobile_image'];
                if (file_exists($_VAR_POPUP_IMAGE_PATH.$dm_old_file)) {
                    if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_old_file)) {
                        unlink($_VAR_POPUP_IMAGE_PATH.$dm_old_file);
                    }
                }
                if (file_exists($_VAR_POPUP_IMAGE_PATH.$dm_old_mobile_file)) {
                    if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_old_mobile_file)) {
                        unlink($_VAR_POPUP_IMAGE_PATH.$dm_old_mobile_file);
                    }
                }
            }
        }

        $dm_image = $new_file_name;
        $dm_mobile_image = $new_mobile_file_name;
    } else {
        $query = "SELECT * FROM `dm_event_banner` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_image = $row['dm_popup_image'];
        $dm_mobile_image = $row['dm_popup_mobile_image'];
    }

    if(isset($_REQUEST['dm_del_image']) && $_REQUEST['dm_del_image']) {
        if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_image)) {
            unlink($_VAR_POPUP_IMAGE_PATH.$dm_image);
            $dm_image = '';
        }
    }

    if(isset($_REQUEST['dm_del_mobile_image']) && $_REQUEST['dm_del_mobile_image']) {
        if (is_file($_VAR_POPUP_IMAGE_PATH.$dm_image)) {
            unlink($_VAR_POPUP_IMAGE_PATH.$dm_mobile_image);
            $dm_mobile_image = '';
        }
    }

    $query = "INSERT INTO `dm_event_banner` (`dm_id`, `dm_domain`,`dm_popup_nm`, `dm_popup_link`, `dm_popup_image`, `dm_start_dt`, `dm_end_dt`, `dm_domain_id`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_popup_mobile_image`, `dm_order`, `dm_link_target`)
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_popup_nm."', '".$dm_popup_link."', '".$dm_image."', '".$dm_start_dt."', '".$dm_end_dt."', '".$dm_domain_id."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."', '".$dm_mobile_image."', '".$dm_order."', '".$dm_link_target."')
    ON DUPLICATE KEY UPDATE `dm_popup_nm` = '".$dm_popup_nm."', `dm_start_dt` = '".$dm_start_dt."', `dm_end_dt` = '".$dm_end_dt."', `dm_popup_mobile_image` = '".$dm_mobile_image."', `dm_order` = '".$dm_order."', `dm_link_target` = '".$dm_link_target."',
   `dm_popup_link` = '".$dm_popup_link."',  `dm_popup_image` = '".$dm_image."', `dm_domain_id` = '".$dm_domain_id."', `dm_status` = '".$dm_status."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_event_banner` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}