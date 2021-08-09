<?php

include "db.php";
include "data.php";
include "config.php";

/*$ck_user_id = "admin";
	$ck_user_nm = iconv("utf-8", "euc-kr","관리자");
	$ck_user_group = "0002";
	$ck_site_id = "0000";
	$ck_access_id = "9";*/
/*
	function insert_log($site, $id, $nm, $page, $cd, $result, $data, $Query)
	{
		$db = new DBSQL();
		$db -> DBconnect();

		$_ntime  = time();
		$_CurDate = date("Ymd",$_ntime);
		$_CurATime = date("His",$_ntime);
		$_CurDateTime = date("YmdHis",$_ntime);

		$query = "insert into an_common_web_log(com_date, com_time, com_system_id, com_group_id, com_user_id, com_user_nm, com_conn_ip, com_fn_req_dt, com_page, com_fn_req_cd, com_fn_result, com_fn_pdt_1)";
		$query .= " values(";
		$query .= "'".$_CurDate."', '".$_CurATime."', 'APP', '".$site."', '".$id."', '".$nm."', '".$_SERVER["REMOTE_HOST"]."', '".$_CurDateTime."', ".$page.", '".$cd."', '".$result."','".iconv("utf-8", "euc-kr", $data)."')";

		$db->ExecSql($query, "I");
	}
    */

function insert_log($id, $ip, $code, $result, $url, $agent, $dm_array = array(), $site = '')
{
    global $CONFIG;
    $db = new DBSQL();
    $db->DBconnect();

    for ($i = 1; $i <= count($dm_array); $i++) {
        $var = "dm_$i";
        $$var = "";
        if (isset($dm_array['dm_' . $i]) && settype($dm_array['dm' . $i], 'string')) {
            $$var = trim($dm_array['dm_' . $i]);
        }
    }

    $query = "INSERT INTO dm_web_log (`dm_datetime`, `dm_domain`, `dm_login_id`, `dm_ip`, `dm_fn_code`, `dm_fn_result`, `dm_fn_url`, `dm_agent_info`, `dm_type`, `dm_1`, `dm_2`, `dm_3`, `dm_4`, `dm_5`) 
            VALUE (now(), '" . $CONFIG['dm_domain_id'] . "',  '" . $id . "', '" . $ip . "', '" . $code . "', '" . $result . "', '" . $url . "', '" . $agent . "', '홈페이지','" . $dm_1 . "','" . $dm_2 . "','" . $dm_3 . "','" . $dm_4 . "','" . $dm_5 . "')";

    $db->ExecSql($query, "I");
}

function insert_web_log($ip, $code, $result, $url, $agent, $dm_array = array(), $type = '')
{
    global $CONFIG;
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT * FROM dm_web_log WHERE dm_ip = '" . $ip . "'";

    $db->ExecSql($query, "S");

    $is_visit = $db->Fetch();

    if (!$is_visit) {
        $is_visit = 1;
    } else {
        $is_visit = 0;
    }

    $query = "SELECT * FROM dm_web_log WHERE date_format(`dm_datetime`, '%Y-%m-%d %H') = '" . date("Y-m-d H") . "' AND dm_ip = '" . $ip . "' AND dm_type = '" . $type . "'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    if (!$row) {
        for ($i = 1; $i <= count($dm_array); $i++) {
            $var = "dm_$i";
            $$var = "";
            if (isset($dm_array['dm_' . $i]) && settype($dm_array['dm' . $i], 'string')) {
                $$var = trim($dm_array['dm_' . $i]);
            }
        }

        $query = "INSERT INTO dm_web_log (`dm_datetime`, `dm_domain`, `dm_ip`, `dm_fn_code`, `dm_fn_result`, `dm_fn_url`, `dm_agent_info`, `dm_type`, `dm_1`, `dm_2`, `dm_3`, `dm_4`, `dm_5`, `dm_login_id`) 
            VALUE (now(), '" . $CONFIG['dm_domain_id'] . "', '" . $ip . "', '" . $code . "', '" . $result . "', '" . $url . "', '" . $agent . "', '" . $type . "','" . $is_visit . "','" . $dm_2 . "','" . $dm_3 . "','" . $dm_4 . "','" . $dm_5 . "', '" . getSession('chk_dm_id') . "')";

        $db->ExecSql($query, "I");
    }
}

function insert_visit($type = 'page', $name = '')
{
    $db = new DBSQL();
    $db->DBconnect();

    if (!getSession($type . 'visit')) {
        setSession($type . 'visit', "1");

        $current_date = date("Y-m-d H") . ":00:00";

        $query = "SELECT * FROM dm_visit WHERE dm_datetime = '" . $current_date . "' AND `dm_type` = '" . $type . "'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        if ($row) {
            $query = "UPDATE dm_visit SET dm_count = dm_count + 1 WHERE dm_datetime = '" . $current_date . "' AND `dm_type` = '" . $type . "'";
        } else {
            $query = "INSERT INTO dm_visit (dm_datetime, dm_count, dm_type) VALUE ('" . $current_date . "', 1, '" . $type . "')";
        }

        $db->ExecSql($query, "I");
    }
}

function insert_visit_orgin()
{
    $db = new DBSQL();
    $db->DBconnect();

    if (!getSession('visit_orgin')) {
        setSession('visit_orgin', "1");

        $refer = $_SERVER['HTTP_REFERER'];

        $current_date = date("Y-m-d");

        $query = "SELECT * FROM dm_visit_orgin WHERE date_format(dm_datetime, '%Y-%m-%d') = '" . $current_date . "' AND `dm_orgin` = '" . $refer . "'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        if ($row) {
            $query = "UPDATE dm_visit_orgin SET dm_count = dm_count + 1 WHERE date_format(dm_datetime, '%Y-%m-%d') = '" . $current_date . "' AND `dm_orgin` = '" . $refer . "'";
        } else {
            $query = "INSERT INTO dm_visit_orgin (dm_datetime, dm_count, dm_orgin) VALUE ('" . $current_date . "', 1, '" . $refer . "')";
        }

        $db->ExecSql($query, "I");
    }
}

function getBrowserInfo()
{
    $userAgent = $_SERVER["HTTP_USER_AGENT"];
    if (preg_match("/MSIE*/", $userAgent)) {
        // 익스플로러
        if (preg_match("/MSIE 6.0[0-9]*/", $userAgent)) {
            $browser = "Internet Explorer 6";
        } else if (preg_match("/MSIE 7.0*/", $userAgent)) {
            $browser = "Internet Explorer 7";
        } else if (preg_match("/MSIE 8.0*/", $userAgent)) {
            $browser = "Internet Explorer 8";
        } else if (preg_match("/MSIE 9.0*/", $userAgent)) {
            $browser = "Internet Explorer 9";
        } else if (preg_match("/MSIE 10.0*/", $userAgent)) {
            $browser = "Internet Explorer 10";
        } else {
            // 익스플로러 기타
            $browser = "Explorer ETC";
        }
    } else if (preg_match("/Trident*/", $userAgent && preg_match("/rv:11.0*/", $userAgent && preg_match("/Gecko*/", $userAgent)))) {
        $browser = "Internet Explorer 11";
    } else if (preg_match("/Opera*/", $userAgent)) {
        // 오페라
        $browser = "Opera";
    } else if (preg_match('/Chrome/i', $userAgent)) {
        $browser = 'Google Chrome';
    } else if (preg_match('/Safari/i', $userAgent)) {
        $browser = 'Apple Safari';
    } else {
        $browser = "Other";
    }
    return $browser;
}

function getOsInfo()
{
    $userAgent = $_SERVER["HTTP_USER_AGENT"];

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false) {
        $os = 'Android';
    } else if (preg_match('/linux/i', $userAgent)) {
        $os = 'linux';
    } else if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
        $os = 'IPhone';
    } else if (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $os = 'mac';
    } else if (preg_match('/windows|win32/i', $userAgent)) {
        $os = 'windows';
    } else {
        $os = 'Other';
    }
    return $os;
}

function insert_env()
{
    $brower = getBrowserInfo();
    $os = getOsInfo();
    $db = new DBSQL();
    $db->DBconnect();
    $is_mobile = 0;

    if (!getSession('visit_env')) {
        setSession('visit_env', "1");

        $mAgent = array("iPhone", "iPod", "Android", "Blackberry", "Opera Mini", "Windows ce", "Nokia", "sony");
        $chkMobile = false;
        for ($i = 0; $i < sizeof($mAgent); $i++) {
            if (stripos($_SERVER['HTTP_USER_AGENT'], $mAgent[$i])) {
                $chkMobile = true;
                break;
            }
        }

        if ($chkMobile) {
            $is_mobile = 1;
        }

        $current_date = date("Y-m-d");

        $query = "SELECT * FROM dm_visit_env WHERE date_format(dm_datetime, '%Y-%m-%d') = '" . $current_date . "' AND `dm_os` = '" . $os . "' AND dm_brower = '" . $brower . "' AND dm_type = '" . $is_mobile . "'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        if ($row) {
            $query = "UPDATE dm_visit_env SET dm_count = dm_count + 1 WHERE date_format(dm_datetime, '%Y-%m-%d') = '" . $current_date . "' AND `dm_os` = '" . $os . "' AND dm_brower = '" . $brower . "' AND dm_type = '" . $is_mobile . "'";
        } else {
            $query = "INSERT INTO dm_visit_env (dm_datetime, dm_count, dm_os, dm_brower, dm_type) VALUE ('" . $current_date . "', 1, '" . $os . "', '" . $brower . "', '" . $is_mobile . "')";
        }

        $db->ExecSql($query, "I");
    }

}

$dbSub = new DBSQL();
$dbSub->DBconnect();
$menu_count = 1;
$menu_class_array = array(
    2 => "depth1",
    3 => "depth2",
    4 => "depth3",
    5 => "depth4"
);
$arMenus = array();
$menu = "";
$mobile_menu = "";
$mobile_menu2 = "";

function getMenu($parent_id)
{
    global $menu_class_array, $contentId, $menu;
    $db = new DBSQL();
    $db->DBconnect();
    $data = array();

    $dm_level = (!getSession('chk_dm_level')) ? 0 : getSession('chk_dm_level');
    $query = "SELECT * FROM dm_menus where dm_parent_id = '$parent_id' and dm_menu_view = '1'  AND dm_level <= '{$dm_level}' order by dm_menu_order";

    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $data[] = $row;
    }

    if (count($data) > 0) {
        $menu .= "	<ul class='" . $menu_class_array[$data[0]['dm_depth']] . " test'>";
        foreach ($data as $key => $value) {
            $matches = array();
            $active_li_class = "";
            $active_a_class = "";

            if ($contentId) {
                if (preg_match('/' . $contentId . '/', $value['dm_url'], $matches)) {
                    $active_li_class = "open";
                }
            }

            $menu .= "<li class='" . $active_li_class . "'>";
            $dm_link = "/diam/web/index.html" . $value['dm_url'];
            if (!$value['dm_link_data']) {
                $dm_link = "#";
            }

            if ($value['dm_link_type'] == "2") {
                $dm_link = $value['dm_link_data'];
            }

            $menu .= "<a href='" . $dm_link . "' class='dep" . ($value['dm_depth'] - 1) . " " . $active_a_class . "'><span>" . $value['dm_menu_text'] . "</span></a>";

            getMenu($value['dm_id']);
            $menu .= "</li>";
        }
        $menu .= "	</ul>";
    }
    return $menu;
}

function getMobileMenu($parent_id)
{
    global $menu_class_array, $contentId, $mobile_menu;
    $db = new DBSQL();
    $db->DBconnect();
    $data = array();

    $dm_level = (!getSession('chk_dm_level')) ? 0 : getSession('chk_dm_level');
    $query = "SELECT * FROM dm_menus where dm_parent_id = '$parent_id' and dm_menu_view = '1'  AND dm_level <= '{$dm_level}' order by dm_menu_order";

    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $data[] = $row;
    }

    if (count($data) > 0) {
        $mobile_menu .= "	<ul class='" . $menu_class_array[$data[0]['dm_depth']] . " '>";
        foreach ($data as $key => $value) {
            $matches = array();
            $active_li_class = "";
            $active_a_class = "";

            if ($contentId) {
                if (preg_match('/' . $contentId . '/', $value['dm_url'], $matches)) {
                    $active_li_class = "open";
                }
            }

            $mobile_menu .= "<li class='" . $active_li_class . " '>";
            $dm_link = "/diam/web/index.html" . $value['dm_url'];
            if (!$value['dm_link_data']) {
                $dm_link = "#";
            }

            if ($value['dm_link_type'] == "2") {
                $dm_link = $value['dm_link_data'];
            }

            $mobile_menu .= "<a href='" . $dm_link . "' class='dep" . ($value['dm_depth'] - 1) . " " . $active_a_class . "'><span>" . $value['dm_menu_text'] . "</span></a>";

            getMobileMenu($value['dm_id']);
            $mobile_menu .= "</li>";
        }
        $mobile_menu .= "	</ul>";
    }
    return $mobile_menu;
}

function getMobileMenu2($parent_id)
{
    global $menu_class_array, $contentId, $mobile_menu2;
    $db = new DBSQL();
    $db->DBconnect();
    $data = array();

    $dm_level = (!getSession('chk_dm_level')) ? 0 : getSession('chk_dm_level');
    $query = "SELECT * FROM dm_menus where dm_parent_id = '$parent_id' and dm_menu_view = '1'  AND dm_level <= '{$dm_level}' order by dm_menu_order";

    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $data[] = $row;
    }

    if (count($data) > 0) {
        $mobile_menu2 .= "	<ul class='" . $menu_class_array[$data[0]['dm_depth']] . " test'>";
        foreach ($data as $key => $value) {
            $matches = array();
            $active_li_class = "";
            $active_a_class = "";

            if ($contentId) {
                if (preg_match('/' . $contentId . '/', $value['dm_url'], $matches)) {
                    $active_li_class = "open";
                }
            }

            $mobile_menu2 .= "<li class='" . $active_li_class . " keep'>";
            $dm_link = "/diam/web/index.html" . $value['dm_url'];
            if (!$value['dm_link_data']) {
                $dm_link = "#";
            }

            if ($value['dm_link_type'] == "2") {
                $dm_link = $value['dm_link_data'];
            }

            $mobile_menu2 .= "<a href='" . $dm_link . "' class='dep" . ($value['dm_depth'] - 1) . " " . $active_a_class . "'><span>" . $value['dm_menu_text'] . "</span></a>";

            getMobileMenu2($value['dm_id']);
            $mobile_menu2 .= "</li>";
        }
        $mobile_menu2 .= "	</ul>";
    }
    return $mobile_menu2;
}

function getMenuArray($parent_id)
{
    $db = new DBSQL();
    $db->DBconnect();
    $data = array();
    $query = "SELECT * FROM dm_menus where dm_parent_id = '$parent_id' and dm_menu_state = '1' order by dm_menu_order";

    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $data[] = $row;
    }

    $Menus = array();

    if (count($data) > 0) {
        foreach ($data as $key => $value) {
            $vMenu["rows"] = $value;
            $vMenu["children"] = getMenuArray($value['dm_id']);
            array_push($Menus, $vMenu);
        }
    }

    return $Menus;
}


function getMenuBackground($txt)
{
    $db = new DBSQL();
    $db->DBconnect();

    $res = "";
    $query = "SELECT * FROM `dm_menus` WHERE `dm_parent_id` = 1 order by dm_menu_order, dm_id";

    $db->ExecSql($query, "S");

    $i = 1;
    while ($row = $db->Fetch()) {
        if ($row['dm_menu_text'] == $txt) {

            $res = str_pad($i, "2", "0", STR_PAD_LEFT);;
        }

        $i++;
    }

    return $res;
}

function selectMainCategory()
{
    $db = new DBSQL();
    $db->DBconnect();

    $Query = "select * from main_category";
    $db->ExecSql($Query, "S");

    while ($arItem = $db->Fetch()) {
        $arCCode[$arItem['id']] = $arItem['name'];
    }
    return $arCCode;
}

function selectSubCategory()
{
    $db = new DBSQL();
    $db->DBconnect();

    $Query = "select * from sub_category";
    $db->ExecSql($Query, "S");

    while ($arItem = $db->Fetch()) {
        $arCCode[$arItem['id']] = $arItem['name'];
    }
    return $arCCode;
}

function selectMngDevice($id)
{
    $db = new DBSQL();
    $db->DBconnect();

    $Query = "select * from user_device where user_id = " . $id;

    $db->ExecSql($Query, "S");

    $return = "";

    while ($arItem = $db->Fetch()) {
        if ($return != "") $return .= ',';
        $return .= "" . $arItem['device_id'] . "";
    }

    if ($return != '') {
        $return = " and id in (" . $return . ")";
    }
    return $return;
}

function selectMngMainCategory($id)
{
    $db = new DBSQL();
    $db->DBconnect();

    $Query = "select * from user_device where user_id = " . $id;

    $db->ExecSql($Query, "S");

    $Ids = "";
    $return = "";

    while ($arItem = $db->Fetch()) {
        if ($Ids != "") $Ids .= ',';
        $Ids .= "" . $arItem['device_id'] . "";
    }

    if ($Ids != '') {
        $Ids = " id in (" . $Ids . ")";
    }

    $Query = "select distinct(main_category) from device where " . $Ids;
    //$db->writeLog($Query);
    $db->ExecSql($Query, "S");

    while ($arItem = $db->Fetch()) {
        if ($return != "") $return .= ',';
        $return .= "" . $arItem['main_category'] . "";
    }

    if ($return != '') {
        $return = " and id in (" . $return . ")";
    }

    return $return;
}

function selectMngSubCategory($id)
{
    $db = new DBSQL();
    $db->DBconnect();

    $Query = "select * from user_device where user_id = " . $id;

    $db->ExecSql($Query, "S");

    $Ids = "";
    $return = "";

    while ($arItem = $db->Fetch()) {
        if ($Ids != "") $Ids .= ',';
        $Ids .= "" . $arItem['device_id'] . "";
    }

    if ($Ids != '') {
        $Ids = " id in (" . $Ids . ")";
    }

    $Query = "select distinct(sub_category) from device where " . $Ids;
    $db->ExecSql($Query, "S");

    while ($arItem = $db->Fetch()) {
        if ($return != "") $return .= ',';
        $return .= "" . $arItem['sub_category'] . "";
    }

    if ($return != '') {
        $return = " and id in (" . $return . ")";
    }

    return $return;
}

function selectCommonCode($group)
{
    $db = new DBSQL();
    $db->DBconnect();

    $Query = "select * from dm_common_code where dm_code_group='" . $group . "' order by dm_code_asc asc";
    $db->ExecSql($Query, "S");

    while ($arItem = $db->Fetch()) {
        $arCCode[$arItem['dm_code_value']] = $arItem['dm_code_name'];
    }
    return $arCCode;
}

function DM_CONVERT_UID($page)
{
    global $_VAR_INDEX_PATH;
    return $_VAR_INDEX_PATH . hash("md5", $page, false);
}

// 에디터 이미지 얻기
function get_editor_image($contents, $view = true)
{
    if (!$contents)
        return false;

    // $contents 중 img 태그 추출
    if ($view)
        $pattern = "/<img([^>]*)>/iS";
    else
        $pattern = "/<img[^>]*src=[\'\"]?([^>\'\"]+[^>\'\"]+)[\'\"]?[^>]*>/i";
    preg_match_all($pattern, $contents, $matchs);

    return $matchs;
}

// 게시글리스트 썸네일 생성
function get_list_thumbnail($dm_table, $wr_id, $thumb_width, $thumb_height, $is_create = false, $is_crop = false, $crop_mode = 'center', $is_sharpen = false, $um_value = '80/0.5/3')
{
//        @ini_set("display_errors", 'On');
//@error_reporting(E_ALL);
    global $_VAR_PATH_WEB_DATA, $_VAR_PATH_WEB, $_VAR_URL_WEB, $_VAR_URL_WEB_DATA, $_VAR_URL_ROOT;
    $db = new DBSQL();
    $db->DBconnect();

    $filename = $alt = "";
    $edt = false;

    $query = " SELECT wr_file, wr_content FROM dm_write_{$dm_table} WHERE wr_id = '$wr_id'";

    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    if ($row['wr_file']) {
        $filename = explode("|", $row['wr_file']);
        $filename = $filename[0];
        if (!preg_match("/\.(gif|jpg|jpeg|png)$/i", $filename)) {
            $row['wr_file'] = "";
        }
    }

    if ($row['wr_file']) {
        $filename = explode("|", $row['wr_file']);
        $filename = $filename[0];
        $filepath = $_VAR_PATH_WEB_DATA . 'file';
        if (preg_match("/user/", $filename)) {
            $temp_file_name = explode("/", $filename);
            $temp_file_name = end($temp_file_name);
            $filepath = str_replace("/" . $temp_file_name, "", $filename);
            $filepath = $_SERVER['DOCUMENT_ROOT'] . $filepath;
            $filename = $temp_file_name;
        }

        //대체문구 $alt = $row['wr_content'];
    } else {
        $matches = get_editor_image($row['wr_content'], false);
        $edt = true;

        if (isset($matches[1]) && is_array($matches[1])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                // 이미지 path 구함
                $p = parse_url($matches[1][$i]);
                if (strpos($p['path'], '/' . $_VAR_PATH_WEB_DATA . '/') != 0)
                    $data_path = preg_replace('/^\/.*\/' . $_VAR_PATH_WEB_DATA . '/', '/' . $_VAR_PATH_WEB_DATA, $p['path']);
                else
                    $data_path = $p['path'];

                $srcfile = $_SERVER['DOCUMENT_ROOT'] . $data_path;

                if (preg_match("/\.(gif|jpg|jpeg|png)$/i", $srcfile) && is_file($srcfile)) {
                    $size = @getimagesize($srcfile);
                    if (empty($size))
                        continue;

                    $filename = basename($srcfile);
                    $filepath = dirname($srcfile);

                    preg_match("/alt=[\"\']?([^\"\']*)[\"\']?/", $matches[0][$i], $malt);
                    $alt = $malt[1];

                    break;
                }
            }
        }
    }

    if (!$filename)
        return false;

    $tname = thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, $is_create, $is_crop, $crop_mode, $is_sharpen, $um_value);

    if ($tname) {
        if ($edt) {
            // 오리지날 이미지
            $ori = $_SERVER['DOCUMENT_ROOT'] . $data_path;

            // 썸네일 이미지
            $src = str_replace($filename, $tname, $data_path);

        } else {
            $filename = explode("|", $row['wr_file']);
            $filename = $filename[0];

            if (preg_match("/user/", $filename)) {
                $temp_file_name = explode("/", $filename);
                $temp_file_name = end($temp_file_name);
                $filepath = str_replace("/" . $temp_file_name, "", $filename);
                $ori = "";
                $src = $filepath . "/" . $tname;

            } else {
                $ori = $_VAR_URL_WEB_DATA . 'file/' . $filename;
                $src = $_VAR_URL_WEB_DATA . 'file/dm_write_' . $dm_table . '/' . $tname;
            }
        }
    } else {

//			$thumb = array("src"=>$filename, "ori"=>$filename, "alt"=>$filename);
//	        return $thumb;

    }

    $thumb = array("src" => $src, "ori" => $ori, "alt" => $alt);

    return $thumb;
}

function thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create, $is_crop = false, $crop_mode = 'center', $is_sharpen = false, $um_value = '80/0.5/3')
{

    if (!$thumb_width && !$thumb_height)
        return;

    $source_file = "$source_path/$filename";

    // 원본 파일이 없다면
    if (!is_file($source_file)) {
        return;
    }

    $size = @getimagesize($source_file);

    if ($size[2] < 1 || $size[2] > 3) // gif, jpg, png 에 대해서만 적용
        return;

    if (!is_dir($target_path)) {
        @mkdir($target_path, 0707);
        @chmod($target_path, 0707);
    }

    // 디렉토리가 존재하지 않거나 쓰기 권한이 없으면 썸네일 생성하지 않음
    if (!(is_dir($target_path) && is_writable($target_path))) {
        return '';
    }


    // Animated GIF는 썸네일 생성하지 않음
    if ($size[2] == 1) {
        if (is_animated_gif($source_file))
            return basename($source_file);
    }

    $ext = array(1 => 'gif', 2 => 'jpg', 3 => 'png');

    $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename); // 확장자제거
    $thumb_filename_array = explode("/", $thumb_filename);
    $dm_table = "";
    if (count($thumb_filename_array) > 1) {
        $dm_table = "/" . $thumb_filename_array[0];
        $thumb_filename = $thumb_filename_array[1];
    }

    $thumb_file = "$target_path$dm_table/thumb-{$thumb_filename}_{$thumb_width}x{$thumb_height}." . $ext[$size[2]];

    $thumb_time = @filemtime($thumb_file);
    $source_time = @filemtime($source_file);

    if (file_exists($thumb_file)) {
        if ($is_create == false && $source_time < $thumb_time) {
            return basename($thumb_file);
        }
    }

    // 원본파일의 GD 이미지 생성
    $src = null;
    $degree = 0;

    if ($size[2] == 1) {
        $src = @imagecreatefromgif($source_file);
        $src_transparency = @imagecolortransparent($src);
    } else if ($size[2] == 2) {
        $src = @imagecreatefromjpeg($source_file);
        if (function_exists('exif_read_data')) {
            // exif 정보를 기준으로 회전각도 구함
            $exif = @exif_read_data($source_file);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 8:
                        $degree = 90;
                        break;
                    case 3:
                        $degree = 180;
                        break;
                    case 6:
                        $degree = -90;
                        break;
                }

                // 회전각도 있으면 이미지 회전
                if ($degree) {
                    $src = imagerotate($src, $degree, 0);

                    // 세로사진의 경우 가로, 세로 값 바꿈
                    if ($degree == 90 || $degree == -90) {
                        $tmp = $size;
                        $size[0] = $tmp[1];
                        $size[1] = $tmp[0];
                    }
                }
            }
        }
    } else if ($size[2] == 3) {
        $src = @imagecreatefrompng($source_file);
        @imagealphablending($src, true);
    } else {
        return;
    }

    if (!$src)
        return;

    $is_large = true;
    // width, height 설정

    if ($thumb_width) {
        if (!$thumb_height) {
            $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
        } else {
            if ($size[0] < $thumb_width || $size[1] < $thumb_height)
                $is_large = false;
        }
    } else {
        if ($thumb_height) {
            $thumb_width = round(($thumb_height * $size[0]) / $size[1]);
        }
    }

    $dst_x = 0;
    $dst_y = 0;
    $src_x = 0;
    $src_y = 0;
    $dst_w = $thumb_width;
    $dst_h = $thumb_height;
    $src_w = $size[0];
    $src_h = $size[1];

    $ratio = $dst_h / $dst_w;

    if ($is_large) {
        // 크롭처리
        if ($is_crop) {
            switch ($crop_mode) {
                case 'center':
                    if ($size[1] / $size[0] >= $ratio) {
                        $src_h = round($src_w * $ratio);
                        $src_y = round(($size[1] - $src_h) / 2);
                    } else {
                        $src_w = round($size[1] / $ratio);
                        $src_x = round(($size[0] - $src_w) / 2);
                    }
                    break;
                default:
                    if ($size[1] / $size[0] >= $ratio) {
                        $src_h = round($src_w * $ratio);
                    } else {
                        $src_w = round($size[1] / $ratio);
                    }
                    break;
            }

            $dst = imagecreatetruecolor($dst_w, $dst_h);

            if ($size[2] == 3) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            } else if ($size[2] == 1) {
                $palletsize = imagecolorstotal($src);
                if ($src_transparency >= 0 && $src_transparency < $palletsize) {
                    $transparent_color = imagecolorsforindex($src, $src_transparency);
                    $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($dst, 0, 0, $current_transparent);
                    imagecolortransparent($dst, $current_transparent);
                }
            }
        } else { // 비율에 맞게 생성
            $dst = imagecreatetruecolor($dst_w, $dst_h);
            $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 배경색

            if (!((defined('G5_USE_THUMB_RATIO') && false === G5_USE_THUMB_RATIO) || (defined('G5_THEME_USE_THUMB_RATIO') && false === G5_THEME_USE_THUMB_RATIO))) {
                if ($src_w > $src_h) {
                    $tmp_h = round(($dst_w * $src_h) / $src_w);
                    $dst_y = round(($dst_h - $tmp_h) / 2);
                    $dst_h = $tmp_h;
                } else {
                    $tmp_w = round(($dst_h * $src_w) / $src_h);
                    $dst_x = round(($dst_w - $tmp_w) / 2);
                    $dst_w = $tmp_w;
                }
            }

            if ($size[2] == 3) {
                $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefill($dst, 0, 0, $bgcolor);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            } else if ($size[2] == 1) {
                $palletsize = imagecolorstotal($src);
                if ($src_transparency >= 0 && $src_transparency < $palletsize) {
                    $transparent_color = imagecolorsforindex($src, $src_transparency);
                    $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($dst, 0, 0, $current_transparent);
                    imagecolortransparent($dst, $current_transparent);
                } else {
                    imagefill($dst, 0, 0, $bgcolor);
                }
            } else {
                imagefill($dst, 0, 0, $bgcolor);
            }

        }
    } else {
        $dst = imagecreatetruecolor($dst_w, $dst_h);
        $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 배경색

        if (((defined('DIAM_USE_THUMB_RATIO') && false === DIAM_USE_THUMB_RATIO) || (defined('DIAM_USE_THUMB_RATIO') && false === DIAM_USE_THUMB_RATIO))) {
            //이미지 썸네일을 비율 유지하지 않습니다.  (5.2.6 버전 이하에서 처리된 부분과 같음)

            if ($src_w < $dst_w) {
                if ($src_h >= $dst_h) {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $src_h = $dst_h;
                    if ($dst_w > $src_w) {
                        $dst_w = $src_w;
                    }
                } else {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_w = $src_w;
                    $dst_h = $src_h;
                }
            } else {
                if ($src_h < $dst_h) {
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_h = $src_h;
                    $src_w = $dst_w;
                }
            }

        } else {
            //이미지 썸네일을 비율 유지하며 썸네일 생성합니다.
            if ($src_w < $dst_w) {
                if ($src_h >= $dst_h) {
                    if ($src_h > $src_w) {
                        $tmp_w = round(($dst_h * $src_w) / $src_h);
                        $dst_x = round(($dst_w - $tmp_w) / 2);
                        $dst_w = $tmp_w;
                    } else {
                        $dst_x = round(($dst_w - $src_w) / 2);
                        $src_h = $dst_h;
                        if ($dst_w > $src_w) {
                            $dst_w = $src_w;
                        }
                    }
                } else {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_w = $src_w;
                    $dst_h = $src_h;
                }
            } else {
                if ($src_h < $dst_h) {
                    if ($src_w > $dst_w) {
                        $tmp_h = round(($dst_w * $src_h) / $src_w);
                        $dst_y = round(($dst_h - $tmp_h) / 2);
                        $dst_h = $tmp_h;
                    } else {
                        $dst_y = round(($dst_h - $src_h) / 2);
                        $dst_h = $src_h;
                        $src_w = $dst_w;
                    }
                }
            }
        }

        if ($size[2] == 3) {
            $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $bgcolor);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        } else if ($size[2] == 1) {
            $palletsize = imagecolorstotal($src);
            if ($src_transparency >= 0 && $src_transparency < $palletsize) {
                $transparent_color = imagecolorsforindex($src, $src_transparency);
                $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($dst, 0, 0, $current_transparent);
                imagecolortransparent($dst, $current_transparent);
            } else {
                imagefill($dst, 0, 0, $bgcolor);
            }
        } else {
            imagefill($dst, 0, 0, $bgcolor);
        }
    }

    imagecopyresampled($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    // sharpen 적용
    if ($is_sharpen && $is_large) {
        $val = explode('/', $um_value);
        UnsharpMask($dst, $val[0], $val[1], $val[2]);
    }

    if ($size[2] == 1) {
        imagegif($dst, $thumb_file);
    } else if ($size[2] == 3) {
        if (!defined('DIAM_THUMB_PNG_COMPRESS'))
            $png_compress = 5;
        else
            $png_compress = DIAM_THUMB_PNG_COMPRESS;

        imagepng($dst, $thumb_file, $png_compress);
    } else {
        if (!defined('DIAM_THUMB_JPG_QUALITY'))
            $jpg_quality = 90;
        else
            $jpg_quality = DIAM_THUMB_PNG_COMPRESS;

        imagejpeg($dst, $thumb_file, $jpg_quality);
    }

    chmod($thumb_file, 0644); // 추후 삭제를 위하여 파일모드 변경

    imagedestroy($src);
    imagedestroy($dst);

    return basename($thumb_file);
}

function UnsharpMask($img, $amount, $radius, $threshold)
{

    /*
        출처 : http://vikjavev.no/computing/ump.php
        New:
        - In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements.
        - From version 2 (July 17 2006) the script uses the imageconvolution function in PHP
        version >= 5.1, which improves the performance considerably.


        Unsharp masking is a traditional darkroom technique that has proven very suitable for
        digital imaging. The principle of unsharp masking is to create a blurred copy of the image
        and compare it to the underlying original. The difference in colour values
        between the two images is greatest for the pixels near sharp edges. When this
        difference is subtracted from the original image, the edges will be
        accentuated.

        The Amount parameter simply says how much of the effect you want. 100 is 'normal'.
        Radius is the radius of the blurring circle of the mask. 'Threshold' is the least
        difference in colour values that is allowed between the original and the mask. In practice
        this means that low-contrast areas of the picture are left unrendered whereas edges
        are treated normally. This is good for pictures of e.g. skin or blue skies.

        Any suggenstions for improvement of the algorithm, expecially regarding the speed
        and the roundoff errors in the Gaussian blur process, are welcome.

        */

    ////////////////////////////////////////////////////////////////////////////////////////////////
    ////
    ////                  Unsharp Mask for PHP - version 2.1.1
    ////
    ////    Unsharp mask algorithm by Torstein Hønsi 2003-07.
    ////             thoensi_at_netcom_dot_no.
    ////               Please leave this notice.
    ////
    ///////////////////////////////////////////////////////////////////////////////////////////////


    // $img is an image that is already created within php using
    // imgcreatetruecolor. No url! $img must be a truecolor image.

    // Attempt to calibrate the parameters to Photoshop:
    if ($amount > 500) $amount = 500;
    $amount = $amount * 0.016;
    if ($radius > 50) $radius = 50;
    $radius = $radius * 2;
    if ($threshold > 255) $threshold = 255;

    $radius = abs(round($radius));     // Only integers make sense.
    if ($radius == 0) {
        return $img;
        imagedestroy($img);
    }
    $w = imagesx($img);
    $h = imagesy($img);
    $imgCanvas = imagecreatetruecolor($w, $h);
    $imgBlur = imagecreatetruecolor($w, $h);


    // Gaussian blur matrix:
    //
    //    1    2    1
    //    2    4    2
    //    1    2    1
    //
    //////////////////////////////////////////////////


    if (function_exists('imageconvolution')) { // PHP >= 5.1
        $matrix = array(
            array(1, 2, 1),
            array(2, 4, 2),
            array(1, 2, 1)
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        $offset = 0;

        imagecopy($imgBlur, $img, 0, 0, 0, 0, $w, $h);
        imageconvolution($imgBlur, $matrix, $divisor, $offset);
    } else {

        // Move copies of the image around one pixel at the time and merge them with weight
        // according to the matrix. The same matrix is simply repeated for higher radii.
        for ($i = 0; $i < $radius; $i++) {
            imagecopy($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left
            imagecopymerge($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right
            imagecopymerge($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center
            imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

            imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333); // up
            imagecopymerge($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
        }
    }

    if ($threshold > 0) {
        // Calculate the difference between the blurred pixels and the original
        // and set the pixels
        for ($x = 0; $x < $w - 1; $x++) { // each row
            for ($y = 0; $y < $h; $y++) { // each pixel

                $rgbOrig = ImageColorAt($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                // When the masked pixels differ less from the original
                // than the threshold specifies, they are set to their original value.
                $rNew = (abs($rOrig - $rBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                    : $rOrig;
                $gNew = (abs($gOrig - $gBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                    : $gOrig;
                $bNew = (abs($bOrig - $bBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                    : $bOrig;


                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                    $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                    ImageSetPixel($img, $x, $y, $pixCol);
                }
            }
        }
    } else {
        for ($x = 0; $x < $w; $x++) { // each row
            for ($y = 0; $y < $h; $y++) { // each pixel
                $rgbOrig = ImageColorAt($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                if ($rNew > 255) {
                    $rNew = 255;
                } elseif ($rNew < 0) {
                    $rNew = 0;
                }
                $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                if ($gNew > 255) {
                    $gNew = 255;
                } elseif ($gNew < 0) {
                    $gNew = 0;
                }
                $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                if ($bNew > 255) {
                    $bNew = 255;
                } elseif ($bNew < 0) {
                    $bNew = 0;
                }
                $rgbNew = ($rNew << 16) + ($gNew << 8) + $bNew;
                ImageSetPixel($img, $x, $y, $rgbNew);
            }
        }
    }
    imagedestroy($imgCanvas);
    imagedestroy($imgBlur);

    return true;

}


function is_animated_gif($filename)
{
    if (!($fh = @fopen($filename, 'rb')))
        return false;
    $count = 0;
    // 출처 : http://www.php.net/manual/en/function.imagecreatefromgif.php#104473
    // an animated gif contains multiple "frames", with each frame having a
    // header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    while (!feof($fh) && $count < 2) {
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
    }

    fclose($fh);
    return $count > 1;
}


// 게시판 첨부파일 썸네일 삭제
function delete_board_thumbnail($bo_table, $file)
{
    if (!$bo_table || !$file)
        return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_DATA_PATH . '/file/' . $bo_table . '/thumb-' . $fn . '*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}

function setSession($session_name, $value)
{
    @session_start();

    $_SESSION[$session_name] = $value;
}

// 세션변수값 얻음
function getSession($session_name)
{
    @session_start();
    return $_SESSION[$session_name];
}

function sessionUnset($session_name)
{
    @session_start();

    unset($_SESSION[$session_name]);
}

// 메세지 출력후 창을 닫음
function alert_close($msg)
{
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8'>";
    echo "<script language='javascript'> alert(\"$msg\"); window.close(); </script>";
    exit;
}

// 경고창으로 메세지 출력
function alert_only($msg)
{
    echo "<script language='javascript'> alert(\"$msg\"); </script>";
}

// 경고창으로 메세지 출력
function confirm($msg, $play = "")
{
    echo "<script language='javascript'> if(\"$msg\") { $play } </script>";
}

// 메타태그를 이용한 URL 이동
function goLink($url)
{
    echo "<script language='JavaScript'> location.replace(\"$url\"); </script>";
    exit;
}

// 메세지를 경고창으로
function alert($msg = '', $url = '')
{
    if (!$msg) $msg = '올바른 방법으로 이용해 주십시오.';
    echo "<meta http-equiv='content-type' content='text/html; charset=UTF-8'>";
    echo "<script language='javascript'>alert(\"$msg\");";
    if (!$url) echo "history.go(-1);";
    echo "</script>";
    if ($url) goLink($url);
    exit;
}

function print_r2($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
function get_paging($write_pages, $cur_page, $total_page, $url, $add = "")
{
    //$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
    $url = preg_replace('#(&amp;)?page_no=[0-9]*#', '', $url);
    $url .= substr($url, -1) === '?' ? 'page_no=' : '&amp;page_no=';


    $str = "";
    if ($total_page != 1) {
        $str1 = '<div class="left">
            <div id="paging">';
        $str2 = "</div></div>";
    }

    if ($cur_page > 1) {
        $str .= '<span><a href="' . $url . '1' . $add . '" class="first"><i class="fas fa-angle-double-left"></i></a></span>' . PHP_EOL;
    }

    $start_page = (((int)(($cur_page - 1) / $write_pages)) * $write_pages) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<span><a href="' . $url . ($start_page - 1) . $add . '"><li><i class="fas fa-angle-left"></i></li></a>' . PHP_EOL;

    if ($total_page > 1) {
        for ($k = $start_page; $k <= $end_page; $k++) {
            if ($cur_page != $k)
                $str .= '<span><a href="' . $url . $k . $add . '"><li>' . $k . '</li></a></span>' . PHP_EOL;
            else
                $str .= '<span class="on"><strong>' . $k . '</strong></span>' . PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="' . $url . ($end_page + 1) . $add . '" ><li class="next"><i class="fas fa-angle-right"></i></li></a>' . PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<span><a href="' . $url . $total_page . $add . '" class="last"><i class="fas fa-angle-double-right"></i></a></span>' . PHP_EOL;
    }


    if ($str)
        return "{$str1}<div>{$str}</div>{$str2}";
    else
        return "";
}

// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL, 뷰 페이지 내의 페이징처리 (변수겹침으로 인한 이슈)
function get_view_paging($write_pages, $cur_page, $total_page, $url, $add = "")
{
    //$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
    $url = preg_replace('#(&amp;)?page_number=[0-9]*#', '', $url);
    $url .= substr($url, -1) === '?' ? 'page_number=' : '&amp;page_number=';


    $str = "";
    if ($total_page != 1) {
        $str1 = '<div class="left">
            <div id="paging">';
        $str2 = "</div></div>";
    }

    if ($cur_page > 1) {
        $str .= '<span><a href="' . $url . '1' . $add . '" class="first"><i class="fas fa-angle-double-left"></i></a></span>' . PHP_EOL;
    }

    $start_page = (((int)(($cur_page - 1) / $write_pages)) * $write_pages) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<span><a href="' . $url . ($start_page - 1) . $add . '"><li><i class="fas fa-angle-left"></i></li></a>' . PHP_EOL;

    if ($total_page > 1) {
        for ($k = $start_page; $k <= $end_page; $k++) {
            if ($cur_page != $k)
                $str .= '<span><a href="' . $url . $k . $add . '"><li>' . $k . '</li></a></span>' . PHP_EOL;
            else
                $str .= '<span class="on"><strong>' . $k . '</strong></span>' . PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="' . $url . ($end_page + 1) . $add . '" ><li class="next"><i class="fas fa-angle-right"></i></li></a>' . PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<span><a href="' . $url . $total_page . $add . '" class="last"><i class="fas fa-angle-double-right"></i></a></span>' . PHP_EOL;
    }


    if ($str)
        return "{$str1}<div>{$str}</div>{$str2}";
    else
        return "";
}

function cut_str($str, $len, $suffix = "…")
{
    $arr_str = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    $str_len = count($arr_str);

    if ($str_len >= $len) {
        $slice_str = array_slice($arr_str, 0, $len);
        $str = join("", $slice_str);

        return $str . ($str_len > $len ? $suffix : '');
    } else {
        $str = join("", $arr_str);
        return $str;
    }
}

function latest($table, $skin = 'basic', $rows = 10, $subject_len = 40, $cate = '', $is_secret_view = '0', $is_where = '', $order = "", $order_desc = "")
{
//       @ini_set("display_errors", 'On');
//@error_reporting(E_ALL);
    global $db, $_VAR_PATH_WEB_LASTEST, $_VAR_URL_WEB_BOARD, $_VAR_PATH_WEB_BOARD, $_VAR_URL_WEB_DATA, $layout_path;
    $db2 = new DBSQL();
    $db2->DBconnect();
    $db3 = new DBSQL();
    $db3->DBconnect();
    $orderQuery = "ORDER BY wr_datetime desc";
    $member_level = (getSession("chk_dm_level")) ? getSession("chk_dm_level") : 0;

    $query = "SELECT * FROM `dm_board` WHERE dm_table = '" . $table . "'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $boardInfo = $db->Fetch();

        if ($boardInfo["dm_sort_field"] == 1) {
            $orderQuery .= ', wr_num , wr_reply';
        } else if ($boardInfo["dm_sort_field"] == 2) {
            $orderQuery .= ', wr_num desc , wr_reply';
        } else if ($boardInfo["dm_sort_field"] == 'wr_2') {
            $orderQuery = 'ORDER BY cast(wr_2 as unsigned) asc ';
        } else if ($boardInfo["dm_sort_field"] == 'wr_3') {
            $orderQuery .= ', wr_3 desc ';
        }

        if ($order) {
            $orderQuery = "ORDER BY $order $order_desc";
        }

        $query = "SELECT * FROM dm_pages WHERE dm_board_id = '" . $boardInfo['dm_id'] . "' AND dm_status = 1 ";

        $db->ExecSql($query, "S");

        $pageInfo = $db->Fetch();
    } else {
        echo "게시판 정보가 없습니다.";
        exit;
    }

    $dm_table = 'dm_write_' . $table;
    $list = array();

    $where = " WHERE wr_is_comment = 0 AND wr_reply = '' AND wr_option NOT LIKE '%secret%'";

    if ($cate && $cate != "전체") {
        $where .= " AND ca_name = '" . $cate . "'";
    }

    if ($is_where) {
        $where .= " AND $is_where = 1 ";
    }

    $query = "SELECT * FROM {$dm_table} $where  $orderQuery LIMIT 0, $rows ";

    $db3->ExecSql($query, "S");

    $i = 0;
    if ($db3->Num > 0) {
        while ($listData = $db3->Fetch()) {
            try {
                unset ($listData['wr_password']);
            } catch (Exception $e) {

            }
            $listData['wr_email'] = '';

            $option_array = $listData['wr_option'];
            $option_array = explode(",", $option_array);

            $listData['wr_subject'] = cut_str(htmlspecialchars_decode($listData['wr_subject'], ENT_QUOTES), $subject_len, '…');
            $listData['wr_content'] = cut_str(strip_tags(htmlspecialchars_decode($listData['wr_content'], ENT_QUOTES)), $subject_len, '…');

            foreach ($option_array as $value) {
                if ($value == 'secret') {
                    if ($is_secret_view) {
                        $listData['wr_content'] = $listData['wr_link1'] = $listData['wr_link2'] = $listData['file'] = '';
                        $listData['wr_subject'] = "비밀글입니다.";
                    } else {
                        unset($listData);
                    }
                }
            }

            $query = "SELECT * FROM dm_member WHERE dm_id = '" . $listData['mb_id'] . "'";
            $db2->ExecSql($query, "S");
            $mb = $db2->Fetch();
            $listData['dm_nick'] = $mb['dm_nick'];

            if ($listData) {
                if ($boardInfo['dm_writer_type'] == 'id') {
                    $listData['wr_name'] = $mb['dm_id'];
                } else if ($boardInfo['dm_writer_type'] == 'nick') {
                    $query = "SELECT * FROM dm_member WHERE dm_id = '" . $listData['mb_id'] . "'";
                    $db->ExecSql($query, "S");
                    $mb = $db->Fetch();
                    $listData['wr_name'] = $mb['dm_nick'];
                }

                $selectCode = selectCommonCode('1002');
                $listData['level_text'] = $selectCode[$mb['dm_level']];

                $wr_name_leng = mb_strlen($listData['wr_name'], "UTF-8");
                if ($boardInfo['dm_writer_secret'] == 2) {
                    $listData['wr_name'] = mb_substr($listData['wr_name'], 0, 2) . '*';
                } else if ($boardInfo['dm_writer_secret'] == 3) {
                    $listData['wr_name'] = mb_substr($listData['wr_name'], 0, 1) . str_repeat('*', $wr_name_leng - 1);
                } else if ($boardInfo['dm_writer_secret'] == 4) {
                    $listData['wr_name'] = mb_substr($listData['wr_name'], 0, -2) . str_repeat('*', 2);
                }

                $list['list'][$i] = $listData;
                $board_admin = $boardInfo['dm_admin'];
                if (($boardInfo['dm_read_level'] <= $member_level) || (getSession('is_admin') || $board_admin == getSession('chk_dm_id'))) {
                    $list['list'][$i]['view_href'] = "?command=view&contentId=" . $pageInfo['dm_uid'] . "&wr_id=" . $listData['wr_id'] . "&cate=" . $listData['ca_name'];

                    $current_point = getMemberPoint();

                    if ($boardInfo['dm_read_point'] && (!getSession('is_admin') || $board_admin != getSession('chk_dm_id'))) {
                        $db2 = new DBSQL();
                        $db2->DBconnect();
                        $is_use = false;
                        $query = "SELECT * FROM dm_point_log WHERE dm_id = '" . getSession("chk_dm_id") . "' AND dm_table = '" . $boardInfo['dm_table'] . "' AND wr_id = '" . $listData['wr_id'] . "' AND dm_type = 1  order by dm_datetime desc";
                        $db2->ExecSql($query, "S");
                        $is_buy = $db2->Fetch();

                        if ($is_buy) {
                            $expired = $is_buy['dm_expired'];
                            if ($is_buy['dm_expired']) {
                                $expired_date = date("Y-m-d H:i:s", strtotime($is_buy['dm_datetime'] . "+" . $expired . " hour"));
                                $now = date("Y-m-d H:i:s");
                                if ($now >= $expired_date) {
                                    $is_use = false;
                                } else {
                                    $is_use = true;
                                }
                            } else {
                                $is_use = true;
                            }
                        }

                        if (!$is_use) {
                            if ($boardInfo['dm_read_point_type'] == 1) {
                                if ($boardInfo['dm_read_point'] > $current_point) {
                                    $list['list'][$i]['view_href'] = "javascript:alert('글을 읽을 포인트가 부족합니다.')";
                                } else {
                                    $text = "차감되는 포인트는 [" . $boardInfo['dm_read_point'] . "] 입니다. 글을 읽으시겠습니까?";
                                    $list['list'][$i]['view_href'] = "javascript:if (confirm('" . $text . "')) { location.href='" . $list['list'][$i]['view_href'] . "' }";
                                }
                            }
                        }
                    }

                } else {
                    $list['list'][$i]['view_href'] = "javascript:alert('글을 읽을 권한이 없습니다. 회원이시면 로그인해보세요');";
                }
            }
            $i++;
        }

        $list['pageInfo'] = $pageInfo;
        $list['boardInfo'] = $boardInfo;

    }

    if (is_file($_VAR_PATH_WEB_LASTEST . $skin . '_latest.html')) {

        include $_VAR_PATH_WEB_LASTEST . $skin . '_latest.html';
    } else {
        include $_VAR_PATH_WEB_LASTEST . 'basic_latest.html';
    }

}

function getBoardCategory($table)
{
    global $db;

    $query = "SELECT dm_category_list FROM dm_board WHERE dm_table = '" . $table . "'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    $cateList = explode(",", $row['dm_category_list']);

    return $cateList;
}


function sql_password($value)
{
    global $db;
    $query = "SELECT md5('$value') as pass";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    return $row['pass'];
}

function insert_sms_log($post)
{
    $db = new DBSQL();
    $db->DBconnect();
    $query = "INSERT INTO dm_sms (`dm_request_dt`, `dm_send_dt`, `dm_sms_type`, `dm_customer_name`, `dm_customer_info`, `dm_sms_no`, `dm_content`, `dm_att_file1`,`dm_att_file2`,`dm_att_file3`,`dm_sms_result`, `dm_sms_result_info`)
        VALUE ('" . $post['dm_request_dt'] . "','" . $post['dm_send_dt'] . "', '" . $post['dm_sms_type'] . "', '" . $post['dm_customer_name'] . "', '" . $post['dm_customer_info'] . "','" . $post['dm_sms_no'] . "','" . $post['dm_content'] . "','" . $post['dm_att_file1'] . "',
        '" . $post['dm_att_file2'] . "','" . $post['dm_att_file3'] . "','" . $post['dm_sms_result'] . "','" . $post['dm_result_info'] . "')";
    $db->ExecSql($query, "I");

    cutRemain($post['dm_type']);
}

function cutRemain($type)
{
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT * FROM dm_sms_config";
    $db->ExecSql($query, "S");
    $smsInfo = $db->Fetch();

    if ($type == 'sms') {
        $cut_price = $smsInfo['dm_sms_price'];
    } else if ($type == 'lms') {
        $cut_price = $smsInfo['dm_lms_price'];
    } else {
        $cut_price = $smsInfo['dm_mms_price'];
    }

    $query = "UPDATE dm_sms_config SET dm_remain = dm_remain - $cut_price";

    $db->ExecSql($query, "I");
}

// 해당년,월의 날수 return
function func_month_all_days($y, $m)
{
    for ($i = 28; ; $i++) {
        if (!checkdate($m, $i, $y)) break;
    }
    return $i - 1;
}

// 해당년,월,몇주차의 시작일(일)과 종료일(토)을 배열로 리턴
function func_week_start_end($y, $m, $w)
{
    $sat_cnt = 0;
    $sun_cnt = 0;

    // 전체 주차를 구한다.
    $last_day = func_month_all_days($y, $m);

    $total_week = ceil((date("w", mktime(0, 0, 1, $m, 1, $y)) + $last_day) / 7);
    $_1_w = date("w", mktime(0, 0, 1, $m, 1, $y));
    $last_w = date("w", mktime(0, 0, 1, $m, $last_day, $y));

    if ($_1_w == 0) {   // 1일이 일요일이면

        if ($w != 1) {

            for ($i = 1; $i <= $last_day; $i++) {
                if (date("w", mktime(0, 0, 1, $m, $i, $y)) == 0) {
                    $sun_cnt++;
                    if ($sun_cnt == $w) break;
                }
            }
            $start_day = $i;

            for ($j = 1; $j <= $last_day; $j++) {
                if (date("w", mktime(0, 0, 1, $m, $j, $y)) == 6 && ($sat_cnt + 1) != $total_week) {
                    $sat_cnt++;
                    $end_day = $j;
                    if ($sat_cnt == $w) break;
                } else if (date("w", mktime(0, 0, 1, $m, $j, $y)) == 6 && ($sat_cnt + 1) == $total_week) {
                    if ($m != 12) {
                        $end_day = (6 - $last_w);
                    } else {
                        $end_day = (6 - $last_w);
                        $m = 1;
                        $y++;
                    }
                }
            }

            $str_m = (strlen($m) == 1) ? "0" . $m : $m;
            $str_s = (strlen($start_day) == 1) ? "0" . $start_day : $start_day;
            $str_e = (strlen($end_day) == 1) ? "0" . $end_day : $end_day;

            $arr = array($y . "-" . $str_m . "-" . $str_s, $y . "-" . $str_m . "-" . $str_e);

        } else {    // 1주차이면

            $str_m = (strlen($m) == 1) ? "0" . $m : $m;
            $arr = array($y . "-" . $str_m . "-01", $y . "-" . $str_m . "-07");
        }

        return $arr;

    } else {        ////// 1일이 일요일이 아니면

        if ($w != 1) {  ////// 첫주가 아니면
            $sun_cnt = 1;

            for ($i = 1; $i <= $last_day; $i++) {
                if (date("w", mktime(0, 0, 1, $m, $i, $y)) == 0) {
                    $sun_cnt++;
                    if ($sun_cnt == $w) break;
                }
            }
            $start_day = $i;

            for ($j = 1; $j <= $last_day; $j++) {
                if (date("w", mktime(0, 0, 1, $m, $j, $y)) == 6 && (($sat_cnt + 1) != $total_week)) {
                    $sat_cnt++;
                    $end_day = $j;
                    if ($sat_cnt == $w) break;

                } else if (date("w", mktime(0, 0, 1, $m, $j, $y)) != 6 && (($sat_cnt + 1) != $total_week)) {
                    continue;

                } else {
                    if ($m != 12) {
                        $end_day = (6 - $last_w);
                        $nm = $m + 1;
                        $ny = $y;
                    } else {
                        $end_day = (6 - $last_w);
                        $nm = 1;
                        $ny = $y + 1;
                    }
                    $str_nm = (strlen($nm) == 1) ? "0" . $nm : $nm;
                    $str_m = (strlen($m) == 1) ? "0" . $m : $m;
                    $str_s = (strlen($start_day) == 1) ? "0" . $start_day : $start_day;
                    $str_e = (strlen($end_day) == 1) ? "0" . $end_day : $end_day;

                    $arr = array($y . "-" . $str_m . "-" . $str_s, $ny . "-" . $str_nm . "-" . $str_e);
                    return $arr;
                }
            }
            $str_m = (strlen($m) == 1) ? "0" . $m : $m;
            $str_s = (strlen($start_day) == 1) ? "0" . $start_day : $start_day;
            $str_e = (strlen($end_day) == 1) ? "0" . $end_day : $end_day;

            $arr = array($y . "-" . $str_m . "-" . $str_s, $y . "-" . $str_m . "-" . $str_e);

        } else {        ///// 1주차이면

            if ($m != 1) {
                $p_m = $m - 1;
                $tmp_last_month_day = func_month_all_days($y, $p_m);
                $start_day = $tmp_last_month_day - $_1_w + 1;
                $end_day = (6 - $_1_w) + 1;


                $str_pm = (strlen($p_m) == 1) ? "0" . $p_m : $p_m;
                $str_m = (strlen($m) == 1) ? "0" . $m : $m;
                $str_s = (strlen($start_day) == 1) ? "0" . $start_day : $start_day;
                $str_e = (strlen($end_day) == 1) ? "0" . $end_day : $end_day;

                $arr = array($y . "-" . $str_pm . "-" . $str_s, $y . "-" . $str_m . "-" . $str_e);
            } else {    // 1월이면
                $p_y = $y - 1;
                $p_m = 12;
                $start_day = 31 - $_1_w + 1;
                $end_day = (6 - $_1_w) + 1;

                $str_pm = (strlen($p_m) == 1) ? "0" . $p_m : $p_m;
                $str_m = (strlen($m) == 1) ? "0" . $m : $m;
                $str_s = (strlen($start_day) == 1) ? "0" . $start_day : $start_day;
                $str_e = (strlen($end_day) == 1) ? "0" . $end_day : $end_day;

                $arr = array($p_y . "-" . $str_pm . "-" . $str_s, $y . "-" . $str_m . "-" . $str_e);
            }

        }
        return $arr;

    }
}

function is_mobile()
{
    return preg_match('/phone|samsung|lgtel|mobile|[^A]skt|nokia|blackberry|BB10|android|sony/i', $_SERVER['HTTP_USER_AGENT']);
}

function insert_board_log($type, $dm_board, $content, $row)
{
    $db = new DBSQL();
    $db->DBconnect();
    $dm_id = (getSession('chk_dm_id')) ? getSession('chk_dm_id') : $row['wr_name'] . " (비회원)";
    $query = "INSERT INTO dm_board_log (dm_board, dm_type, dm_ip, dm_id, dm_agent, dm_text, dm_datetime, dm_wr_id, dm_wr_subject, dm_wr_content)
                    VALUE ('" . $dm_board . "', '" . $type . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $dm_id . "', '" . $_SERVER['HTTP_USER_AGENT'] . "',
                    '" . addslashes($content) . "', now(), '" . $row['wr_id'] . "','" . $row['wr_subject'] . "', '" . $row['wr_content'] . "')";

    $db->ExecSql($query, "I");

}

function getEventBanner()
{
    $db = new DBSQL();
    $db->DBconnect();
    $arReturn = array();
    global $_VAR_URL_WEB_POPUP, $_VAR_PATH_WEB_POPUP;

    $query = "SELECT * FROM dm_event_banner WHERE dm_start_dt <= NOW() AND dm_end_dt >= NOW() AND dm_status ='1' order by dm_order asc";
    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        if ($row['dm_popup_image']) {
            if (is_file($_VAR_PATH_WEB_POPUP . $row['dm_popup_image'])) {
                $row['image_url'] = $_VAR_URL_WEB_POPUP . $row['dm_popup_image'];
            }
        }
        if ($row['dm_popup_mobile_image']) {
            if (is_file($_VAR_PATH_WEB_POPUP . $row['dm_popup_mobile_image'])) {
                $row['mobile_image_url'] = $_VAR_URL_WEB_POPUP . $row['dm_popup_mobile_image'];
            }
        }
        $arReturn[] = $row;
    }

    return $arReturn;

}

function getMainVisual()
{
    $db = new DBSQL();
    $db->DBconnect();
    $arReturn = array();
    global $_VAR_URL_WEB_POPUP, $_VAR_PATH_WEB_POPUP;

    $query = "SELECT * FROM dm_main_visual WHERE dm_start_dt <= NOW() AND dm_end_dt >= NOW() AND dm_status ='1' order by dm_order asc";
    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        if ($row['dm_visual_name']) {
            if (is_file($_VAR_PATH_WEB_POPUP . $row['dm_visual_name'])) {
                $row['image_url'] = $_VAR_URL_WEB_POPUP . $row['dm_visual_name'];
            }
        }
        if ($row['dm_visual_mobile_name']) {
            if (is_file($_VAR_PATH_WEB_POPUP . $row['dm_visual_mobile_name'])) {
                $row['mobile_image_url'] = $_VAR_URL_WEB_POPUP . $row['dm_visual_mobile_name'];
            }
        }

        $arReturn[] = $row;
    }

    return $arReturn;
}

$mainVisual = getMainVisual();
$eventVisual = getEventBanner();

function getMemberPoint()
{
    $db = new DBSQL();
    $db->DBconnect();
    $arReturn = array();

    $query = "SELECT * FROM dm_member WHERE dm_id = '" . getSession("chk_dm_id") . "'";

    $db->ExecSql($query, "S");
    $arReturn = $db->Fetch();

    return $arReturn['dm_point'];
}

function getMember($dm_id)
{
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT * FROM dm_member WHERE dm_id = '" . $dm_id . "'";

    $db->ExecSql($query, "S");
    $arReturn = $db->Fetch();

    return $arReturn;
}

function insert_point($type, $point, $dm_table = '', $wr_id = '', $remain_point = '', $expired = '', $kind = "")
{
    $db = new DBSQL();
    $db->DBconnect();

    $query = "UPDATE dm_member SET dm_point = '" . $remain_point . "' WHERE dm_id = '" . getSession("chk_dm_id") . "'";
    $db->ExecSql($query, "S");

    $query = "INSERT INTO dm_point_log (dm_type, dm_id, dm_point, dm_table, wr_id, dm_datetime, dm_ip, dm_remain_point, dm_expired, dm_kind) VALUE ('" . $type . "', '" . getSession("chk_dm_id") . "', '" . $point . "', '" . $dm_table . "', 
                            '" . $wr_id . "', now(), '" . $_SERVER['REMOTE_ADDR'] . "', '" . $remain_point . "', '" . $expired . "', '" . $kind . "')";
    $db->ExecSql($query, "I");
}

function levelUp()
{
    global $MEMBER;
    $db = new DBSQL();
    $db->DBconnect();

    if (!getSession("is_admin")) {
        $query = "SELECT * FROM dm_member WHERE dm_id = '" . $MEMBER["dm_id"] . "'";
        $db->ExecSql($query, "S");
        $mb = $db->Fetch();
        $current_level = $mb['dm_level'];

        $query = "SELECT * FROM dm_member_levelup WHERE dm_kind  = '" . $current_level . "'";
        $db->ExecSql($query, "S");
        $compare = $db->Fetch();

        $query = "SELECT * FROM dm_member_exp WHERE mb_id = '" . $mb['dm_id'] . "'";
        $db->ExecSql($query, "S");
        $exp = $db->Fetch();

        if (
            $compare['dm_write_count'] <= $exp['dm_write_count'] &&
            $compare['dm_comment_count'] <= $exp['dm_comment_count'] &&
            $compare['dm_attend_count'] <= $exp['dm_attend_count'] &&
            $compare['dm_point'] <= $exp['dm_point']
        ) {
            $query = "UPDATE dm_member SET dm_level = dm_level + 1, dm_levelup = '1' WHERE dm_id = '" . $mb['dm_id'] . "'";
            $db->ExecSql($query, "U");
        }
    }
}

function setCloseLevelupPop()
{
    $db = new DBSQL();
    $db->DBconnect();
    $query = "UPDATE dm_member SET dm_levelup = 0 WHERE dm_id = '" . getSession("chk_dm_id") . "'";
    $db->ExecSql($query, "U");
}

function setExpCount($dm_id, $type, $point = "")
{
    $db = new DBSQL();
    $db->DBconnect();
    if ($point) {
        $query = "INSERT INTO dm_member_exp (mb_id, `dm_point`) VALUE ('" . $dm_id . "', '" . $point . "') ON DUPLICATE KEY UPDATE `dm_point` = `dm_point` + $point";
    } else {
        $query = "INSERT INTO dm_member_exp (mb_id, `dm_{$type}_count`) VALUE ('" . $dm_id . "', 1) ON DUPLICATE KEY UPDATE `dm_{$type}_count` = `dm_{$type}_count` + 1";
    }

    $db->ExecSql($query, "I");
}

// 날짜, 조회수의 경우 높은 순서대로 보여져야 하므로 $flag 를 추가
// $flag : asc 낮은 순서 , desc 높은 순서
// 제목별로 컬럼 정렬하는 QUERY STRING
function subject_sort_link($col, $flag = 'asc')
{
    global $orderKind, $sOrder, $sType, $sValue, $page_no, $cate, $contentId;

    $query_string = "contentId=" . $contentId;

    $q1 = "orderKind=$col";
    if ($flag == 'asc') {
        $q2 = 'sOrder=asc';
        if ($orderKind == $col) {
            if ($sOrder == 'asc') {
                $q2 = 'sOrder=desc';
            }
        }
    } else {
        $q2 = 'sOrder=desc';
        if ($orderKind == $col) {
            if ($sOrder == 'desc') {
                $q2 = 'sOrder=asc';
            }
        }
    }

    $arr_query = array();
    $arr_query[] = $query_string;
    $arr_query[] = $q1;
    $arr_query[] = $q2;
    $arr_query[] = 'sType=' . $sType;
    $arr_query[] = 'sValue=' . $sValue;
    $arr_query[] = 'cate=' . $cate;
    $arr_query[] = 'page_no=' . $page_no;
    $qstr = implode("&amp;", $arr_query);

    return "<a href=\"{$_SERVER['SCRIPT_NAME']}?{$qstr}\">";
}

function selectData($out_word, $start_word, $end_word, $start_pos, &$pos)
{
    $start_index = strpos($out_word, $start_word, $start_pos);
    //echo "------->".$start_index."//<br>";
    if ($start_index !== false) {
        $end_index = strpos($out_word, $end_word, $start_index);
        $len = $end_index - ($start_index + strlen($start_word));
        $ret_val = substr($out_word, $start_index + strlen($start_word), $len);
        $pos = $start_index + strlen($start_word) + $len + 1;
        return trim($ret_val);

    } else {
        //$pos = $start_pos;
        return "";
    }

}

function getTodayLogin()
{
    global $is_admin;
    $db = new DBSQL();
    $db->DBconnect();
    $res = true;

    if (!$is_admin) {
        if (getSession("chk_dm_id")) {
            $query = "SELECT * FROM dm_web_log WHERE dm_login_id = '" . getSession("chk_dm_id") . "' AND date_format(now(), '%Y-%m-%d') = date_format(dm_datetime, '%Y-%m-%d') AND dm_fn_code = 'popup'";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();

            if (!$row) {
                $query = "INSERT INTO dm_web_log (dm_domain, dm_type, dm_datetime, dm_login_id, dm_ip, dm_fn_code, dm_fn_result) VALUE (1, '팝업', now(), '" . getSession('chk_dm_id') . "', '" . $_SERVER['REMOTE_ADDR'] . "', 'popup', 1) ";
                $db->ExecSql($query, "I");
                $res = false;
            }
        }
    }

    return $res;
}

function autoLogin()
{
    global $MEMBER, $GROUP;
    $db = new DBSQL();
    $db->DBconnect();

    if (($_COOKIE['userId'] && $_COOKIE['userHash']) && $_COOKIE['autoLogin'] == 1) {
        $code = 'login';
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = $_SERVER['HTTP_REFERER'];
        $agent = $_SERVER["HTTP_USER_AGENT"];

        $key = "diam!@#";
        $id = $_COOKIE['userId'];
        $pw = $_COOKIE['userHash'];

        $member = getMember($id);
        $compare = md5($member['dm_password'] . $key);

        if (!$member['dm_leave_date'] && !$member['dm_intercept_date']) {
            if ($compare == $pw) {
                setSession("chk_dm_level", $member['dm_level']);
                setSession("chk_dm_name", $member['dm_name']);
                setSession("chk_dm_id", $member['dm_id']);
                setSession("is_member", true);
                if ($member['dm_level'] == 10) {
                    setSession("is_admin", true);
                } else {
                    setSession("is_admin", false);
                }

                $MEMBER = $member;

                $selectCode = selectCommonCode('1002');
                $MEMBER['level_text'] = $selectCode[$MEMBER['dm_level']];
                $query = "SELECT * FROM dm_group WHERE dm_group_id = '" . $MEMBER['dm_group_id'] . "'";
                $db->ExecSql($query, "S");
                $GROUP = $db->Fetch();

                $result = '1';
                insert_log($member['dm_id'], $ip, $code, $result, $url, $agent);

                $visit_query = "";
                if (date("Y-m-d", strtotime($member['dm_today_login'])) < date("Y-m-d")) {
                    setExpCount($member['dm_id'], 'attend');
                    $visit_query = ", dm_visit_count = dm_visit_count + 1";

                    $query = "UPDATE dm_member SET dm_today_login = now() $visit_query WHERE dm_id ='" . $member['dm_id'] . "'";
                    $db->ExecSql($query, "I");

//                        goLink("/diam/web");

                }
            }
        }

    }
}

function getSlideMenu($parent_id)
{
    global $contentId;
    $slideMenu;
    $db = new DBSQL();
    $db->DBconnect();
    $data = array();

    $dm_level = (!getSession('chk_dm_level')) ? 0 : getSession('chk_dm_level');
    $query = "SELECT * FROM dm_menus where dm_parent_id = '$parent_id' and dm_menu_view = '1'  AND dm_level <= '{$dm_level}' order by dm_menu_order";

    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $data[] = $row;
    }

    if (count($data) > 0) {
        $slideMenu .= "<div class='swiper-wrapper'>";
        foreach ($data as $key => $value) {
            $matches = array();
            $active_class = "";

            if ($contentId) {
                if (preg_match('/' . $contentId . '/', $value['dm_url'], $matches)) {
                    $active_class = "current";
                }
            }

            $slideMenu .= "<div class='swiper-slide'>";
            $dm_link = "/diam/web/index.html" . $value['dm_url'];
            if (!$value['dm_link_data']) {
                $dm_link = "#";
            }

            if ($value['dm_link_type'] == "2") {
                $dm_link = $value['dm_link_data'];
            }

            $slideMenu .= "<a href='" . $dm_link . "' class='dep" . ($value['dm_depth'] - 1) . " " . $active_class . "'>" . $value['dm_menu_text'] . "</a>";

            $slideMenu .= "</div>";
        }
        $slideMenu .= "	</div>";
    }
    return $slideMenu;
}

?>

