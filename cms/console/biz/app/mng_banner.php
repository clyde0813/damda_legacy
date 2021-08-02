<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-04-10
 * Time: 오후 2:36
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();
$site_id = getSession('site_id');
$where = " WHERE 1 = 1 ";
if ($site_id)
{
    $where .= " AND dm_domain = '$site_id'";
}

$select_query = "SELECT
    `dm_id` as `id`,
    `dm_banner_nm` as `text`,
    `dm_parent_id`,
    `dm_image`,
    `dm_link`,
    `dm_type`,
    `dm_order`,
    `dm_status`,
    `dm_create_dt`,
    `dm_create_id`,
    `dm_modify_dt`,
    `dm_modify_id`
    FROM `dm_banners` $where";

function getBanner() {
    global $db;
    $db->DBconnect();
    $data = array();
    global $select_query;

    $db->ExecSql($select_query, "S");
    while($row = $db->Fetch()){
        $data[] = $row;
    }

    $node = array();
    // Build array of item references:
    foreach($data as $key => &$item) {
        $node[$item['id']] = &$item;
        $node[$item['id']]['children'] = array();
        $node[$item['id']]['data'] = new StdClass();
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
if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $arData = getBanner();

    echo json_encode($arData, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == "update") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_parent_id = isset($_REQUEST['dm_parent_id']) ? trim($_REQUEST['dm_parent_id']) : "";
    $dm_banner_nm = isset($_REQUEST['dm_banner_nm']) ? trim($_REQUEST['dm_banner_nm']) : "";
    $dm_link = isset($_REQUEST['dm_link']) ? trim($_REQUEST['dm_link']) : "";
    $dm_type = isset($_REQUEST['dm_type']) ? trim($_REQUEST['dm_type']) : "";
    $dm_order = isset($_REQUEST['dm_order']) ? trim($_REQUEST['dm_order']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $dm_del_image = isset($_REQUEST['dm_del_image']) ? trim($_REQUEST['dm_del_image']) : "";
    $create_id = getSession("chk_dm_id");
    $dm_image = "";

    $query = "SELECT * FROM `dm_banners` WHERE `dm_id` = '$dm_id'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    if ($row) {
        $dm_old_file = $row['dm_image'];
        if (file_exists($_VAR_BANNER_PATH.$dm_old_file)) {
            if (is_file($_VAR_BANNER_PATH.$dm_old_file)) {
                $dm_image = $dm_old_file;
            }
        }
    }

    if ($_FILES['dm_image'] && $_FILES['dm_image']['name'] != "") {
        $file = $_FILES['dm_image'];
        $ext_str = "jpg,gif,png,JPG,GIF,PNG";

        $allowed_extensions = explode(',', $ext_str);

        $ext = substr($file['name'], strrpos($file['name'], '.') + 1);

        // 확장자 체크
        if(!in_array($ext, $allowed_extensions)) {
            echo "업로드할 수 없는 확장자 입니다.";
        }

        // 파일 크기 체크
        if($file['size'] >= $_VAR_MAX_FILE_SIZE) {
            echo "5MB 까지만 업로드 가능합니다.";
        }

        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_BANNER_PATH.$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            //기존 파일 삭제
            if (file_exists($_VAR_BANNER_PATH.$dm_image)) {
                if (is_file($_VAR_BANNER_PATH.$dm_image)) {
                    unlink($_VAR_BANNER_PATH.$dm_image);
                }
            }
            $dm_image = $new_file_name;
        }
    }

    if ($dm_del_image) {
        if (file_exists($_VAR_BANNER_PATH.$dm_image)) {
            if (is_file($_VAR_BANNER_PATH.$dm_image)) {
                unlink($_VAR_BANNER_PATH.$dm_image);
                $dm_image = "";
            }
        }
    }

    $query = "INSERT INTO `dm_banners` (`dm_id`, `dm_parent_id`, `dm_banner_nm`, `dm_image`, `dm_link`, `dm_type`, `dm_order`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`)
    VALUE ('".$dm_id."', '".$dm_parent_id."', '".$dm_banner_nm."', '".$dm_image."', '".$dm_link."', '".$dm_type."', '".$dm_order."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."')
    ON DUPLICATE KEY UPDATE `dm_parent_id` = '".$dm_parent_id."', `dm_banner_nm` = '".$dm_banner_nm."', `dm_image` = '".$dm_image."',
    `dm_link` = '".$dm_link."', `dm_type` = '".$dm_type."', `dm_order` = '".$dm_order."', `dm_status` = '".$dm_status."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);


} else if ($type == "select_table") {

    $arReturn = array();

    $db->ExecSql($select_query, "S");
    $idx = 0;
    while($row = $db->Fetch()) {
        $arReturn[$idx] = $row;
        if (file_exists($_VAR_BANNER_PATH.$row['dm_image'])) {
            if (is_file($_VAR_BANNER_PATH.$row['dm_image'])) {
                $arReturn[$idx]['dm_image_url'] = $_VAR_BANNER_URL.$row['dm_image'];
            }

        }
        $idx++;
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

