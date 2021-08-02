<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-04-13
 * Time: 오후 1:48
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

    $where = " WHERE 1 = 1 ";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_certificate_nm LIKE '%".$search_value."%' OR dm_certificate_image LIKE '%".$search_value."%' OR dm_create_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_status != "") {
        $where .= " AND dm_status = '".$search_status."'";
    }

    if ($site_id)
    {
        $where .= " AND dm_domain = '$site_id'";
    }

    $query = "SELECT count(*) FROM `dm_certificate` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT * FROM `dm_certificate` $where ORDER BY `dm_id` DESC $limit";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
            if (file_exists($_VAR_CERTIFICATE_IMAGE_PATH.$arData['dm_certificate_image'])) {
                if (is_file($_VAR_CERTIFICATE_IMAGE_PATH.$arData['dm_certificate_image'])) {
                    $arData['dm_image_url'] = $_VAR_CERTIFICATE_IMAGE_URL.$arData['dm_certificate_image'];
                }
            }
            $arReturn[] = $arData;
        }
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == "update") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_certificate_nm = isset($_REQUEST['dm_certificate_nm']) ? trim($_REQUEST['dm_certificate_nm']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $dm_del_image = isset($_REQUEST['dm_del_image']) ? trim($_REQUEST['dm_del_image']) : "";
    $create_id = getSession("chk_dm_id");
    $dm_image = "";

    $query = "SELECT * FROM `dm_certificate` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    if ($row) {
        $dm_old_file = $row['dm_certificate_image'];
        if (file_exists($_VAR_CERTIFICATE_IMAGE_PATH.$dm_old_file)) {
            if (is_file($_VAR_CERTIFICATE_IMAGE_PATH.$dm_old_file)) {
                $dm_image = $dm_old_file;
            }
        }
    }

    if ($_FILES['dm_certificate_image'] && $_FILES['dm_certificate_image']['name'] != "") {
        $file = $_FILES['dm_certificate_image'];
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
            $arResult = array("result" => "fail", "_return" => '5MB 까지만 업로드 가능합니다.');
            echo json_encode($arReturn);
            exit;
        }

        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_CERTIFICATE_IMAGE_PATH.$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            //기존 파일 삭제
            if (file_exists($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image)) {
                if (is_file($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image)) {
                    unlink($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image);
                }
            }
            $dm_image = $new_file_name;
        }
    }

    if ($dm_del_image) {
        if (file_exists($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image)) {
            if (is_file($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image)) {
                unlink($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image);
                $dm_image = "";
            }
        }
    }

    $query = "INSERT INTO `dm_certificate` (`dm_id`, `dm_domain`, `dm_certificate_nm`, `dm_certificate_image`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`)
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_certificate_nm."', '".$dm_image."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."')
    ON DUPLICATE KEY UPDATE `dm_certificate_nm` = '".$dm_certificate_nm."', `dm_certificate_image` = '".$dm_image."', `dm_status` = '".$dm_status."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);
} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "SELECT * FROM `dm_certificate` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "I");

    if ($db -> Num >0) {
        $row = $db->Fetch();
        $dm_image = $row['dm_certificate_image'];

        //파일삭제
        if (file_exists($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image)) {
            if (is_file($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image)) {
                unlink($_VAR_CERTIFICATE_IMAGE_PATH.$dm_image);
            }
        }
    }

    $query = "DELETE FROM `dm_certificate` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "I");
    $arResult = array("result" => "success", "_return" => "");
    echo json_encode($arResult);
}
