<?php
include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$site_id = getSession('site_id');

if($type == "select") {

    $db = new DBSQL();
    $db->DBconnect();
    $db2 = new DBSQL();
    $db2->DBconnect();

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_board_type = isset($_REQUEST['search_board_type']) ? $_REQUEST['search_board_type']: array();

    $search_query = "WHERE 1 = 1";

    if ($site_id) {
        $search_query .= " AND dm_domain = '".$site_id."'";
    }

    if ($search_type != "") {
        if ($search_type == "all") {
            $search_query .= " AND (dm_subject LIKE '%".$search_value."%' OR dm_table LIKE '%".$search_value."%' OR dm_skin LIKE '%".$search_value."%')";
        } else {
            $search_query .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    $search_board_type_count = count($search_board_type);

    if ($search_board_type_count > 0) {
        $search_query .= " AND ( ";
        foreach ($search_board_type as $key => $value) {
            $search_query .= " dm_board_type = '".$value."'";
            if ($search_board_type_count != ($key+1)) {
                $search_query .= " OR";
            }
        }
        $search_query .= " )";
    }

    $query = "SELECT count(*) FROM dm_board  ".$search_query;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $query = "SELECT * FROM dm_board ".$search_query." ORDER BY dm_order ASC ";

    $db->ExecSql($query, "S");

    $arData = array();

    //$ar_status = selectCommonCode('1001');

    if ($db-Num > 0) {
        while ($arItem = $db->Fetch()) {
            $query = "SELECT count(*) as write_count FROM dm_write_".$arItem['dm_table'];
            $db2->ExecSql($query, "S");
            $row = $db2->Fetch();

            $query = "SELECT count(*) as new_count FROM dm_write_".$arItem['dm_table'] . " WHERE DATE_FORMAT(wr_datetime, '%Y-%m-%d') = CURDATE()";
            $db2->ExecSql($query, "S");
            $new_count = $db2->Fetch();

            $query = "SELECT * FROM dm_pages WHERE dm_board_id = '".$arItem['dm_id']."'";
            $db2->ExecSql($query, "S");
            $pageInfo = $db2->Fetch();


            $arItem['write_count'] = $row['write_count'];
            $arItem['new_count'] = $new_count['new_count'];
            $arItem['dm_url'] = $_VAR_WEB_URL."?contentId=".$pageInfo['dm_uid'];
            if ($arItem['dm_device'] == 'pc') {
                $arItem['dm_device'] = "PC";
            } else if ($arItem['dm_device'] == 'mobile') {
                $arItem['dm_device'] = "모바일";
            } else {
                $arItem['dm_device'] = '모두';
            }

            array_push($arData, $arItem);
        }
        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arData);
    } else {
        $arResult = array( "result" => "success", "_return" => "","total" => $total_count, "rows" => $arData);
    }

    echo json_encode($arResult);

}elseif($type == "selectBbs") {

    $db = new DBSQL();
    $db->DBconnect();

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $search_query = "";

	$search_query .= " AND dm_id = ".$dm_id."";

    $query = "SELECT * FROM dm_board WHERE 1 = 1 ".$search_query." ";

	//echo $query;

    $db->ExecSql($query, "S");

    $arData = array();

    //$ar_status = selectCommonCode('1001');

    if ($db-Num > 0) {
        while ($arItem = $db->Fetch()) {
            //$arItem['dm_domain_status_text'] = $ar_status[$arItem['dm_domain_status']];
            $arItem['dm_hit_url'] = "";
            $arItem['dm_new_url'] = "";

            if ($arItem['dm_is_hit']) {
                if ($arItem['dm_hit_icon']) {
                    if (is_file($_VAR_BOARD_PATH.$arItem['dm_skin']."/images/".$arItem['dm_hit_icon'])) {
                        $arItem['dm_hit_url'] = $_VAR_BOARD_URL.$arItem['dm_skin']."/images/".$arItem['dm_hit_icon'];
                    }
                }
            }

            if ($arItem['dm_is_new']) {
                if ($arItem['dm_new_icon']) {
                    if (is_file($_VAR_BOARD_PATH . $arItem['dm_skin'] . "/images/" . $arItem['dm_new_icon'])) {
                        $arItem['dm_new_url'] = $_VAR_BOARD_URL . $arItem['dm_skin'] . "/images/" . $arItem['dm_new_icon'];
                    }
                }
            }
            $arItem['dm_list_group_array'] = array();
            $arItem['dm_write_group_array'] = array();
            $arItem['dm_read_group_array'] = array();
            $arItem['dm_comment_group_array'] = array();
            $arItem['dm_reply_group_array'] = array();
            $arItem['dm_link_group_array'] = array();
            $arItem['dm_upload_group_array'] = array();

            if ($arItem['dm_list_group']) {
                $arItem['dm_list_group_array'] = explode("|", $arItem['dm_list_group']);
            }

            if ($arItem['dm_write_group']) {
                $arItem['dm_write_group_array'] = explode("|", $arItem['dm_write_group']);
            }

            if ($arItem['dm_read_group']) {
                $arItem['dm_read_group_array'] = explode("|", $arItem['dm_read_group']);
            }

            if ($arItem['dm_comment_group']) {
                $arItem['dm_comment_group_array'] = explode("|", $arItem['dm_comment_group']);
            }

            if ($arItem['dm_reply_group']) {
                $arItem['dm_reply_group_array'] = explode("|", $arItem['dm_reply_group']);
            }

            if ($arItem['dm_link_group']) {
                $arItem['dm_link_group_array'] = explode("|", $arItem['dm_link_group']);
            }

            if ($arItem['dm_upload_group']) {
                $arItem['dm_upload_group_array'] = explode("|", $arItem['dm_upload_group']);
            }

//            $arItem['dm_mobile_image_url'] = $_VAR_BOARD_PATH.$arItem['dm_new_icon'];
            array_push($arData, $arItem);
        }
        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arData);
    } else {
        $arResult = array( "result" => "fail", "_return" => "","total" => $total_count, "rows" => $arData);
    }

    echo json_encode($arResult);

} else if($type=='insert') {

	$Query ="insert into dm_board(";
	$qField = "";
	$qValue = "";

	$_POST['txt_dm_category_list'] = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $_POST['txt_dm_category_list']);

//	$_POST['txt_dm_subject'] = strip_tags(clean_xss_attributes($_POST['txt_dm_subject']));
//	$_POST['txt_dm_mobile_subject'] = strip_tags(clean_xss_attributes($_POST['txt_dm_mobile_subject']));

    $_POST['txt_dm_subject'] = strip_tags($_POST['txt_dm_subject']);
    $_POST['txt_dm_mobile_subject'] = strip_tags($_POST['txt_dm_mobile_subject']);

	foreach($_POST as $key => $value)
	{
		$vKey = substr($key,0,3);
		$DKey = substr($key,4);
		if($vKey != "chk")
		{
			if($qField != "") $qField .=", ";
			if($qValue != "") $qValue .=", ";
			$qField .=$DKey;
			if($vKey == "int")
			{
				if($value == "on") 
				{
					$value = "1";
				}else
				{
                    if ($value <= 0) {
                        $value = "0";
                    }
				}
				$qValue .=$value;
			}elseif($vKey == "txt")
			{
				$qValue .="'".$value."'";
			}

		}
		
		
		//echo "$vKey ==> $DKey (".$value.")\r\n";
		//echo "$key ==> $value <br>";

	}
    $Query .= $qField.", dm_domain) values(".$qValue.", ".$site_id.")";

	$db = new DBSQL();
    $db->DBconnect();

	$TABLE_NAME = "dm_write_".$_POST['txt_dm_table'];

	$sQuery = "SHOW TABLES LIKE '".$TABLE_NAME."'";

	$db->ExecSql($sQuery, "S");
	if($db->Num > 0)
	{
		$arResult = array( "result" => "duplicate","_return" => $newId,"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
		echo json_encode( $arResult );
	}else
	{

		// �Խ��� ���̺� ����
		$file = file('./table_create.sql');

		$cQeury = implode("\n", $file);



		// sql_board.sql ������ ���̺���� ��ȯ
		$source = array('/<<TABLE_NAME>>/', '/;/');
		$target = array($TABLE_NAME, '');
		$cQeury = preg_replace($source, $target, $cQeury);

		$db->ExecSql($cQeury, "I");

		$db->ExecSql($Query, "I");

		$sQuery = "select * from dm_board where dm_subject = '".$_POST['txt_dm_subject']."' order by dm_id desc limit 0,1";
		$db->ExecSql($sQuery, "S");
		if($db->Num > 0)
		{
			$row = $db->GetPosition(0);
			$arResult = array( "result" => "success","_return" => $row["dm_id"],"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
		}else
		{
			$arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
		}

		echo json_encode( $arResult );
	}


	//$db->writeLog($Query);



	//echo $Query;
}
else if($type=='update') {

	$dm_id = isset($_REQUEST['chk_dm_id']) ? $_REQUEST['chk_dm_id'] : "";

	$Query ="update dm_board set ";
	$qField = "";
	$qValue = "";

	$_POST['txt_dm_category_list'] = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $_POST['txt_dm_category_list']);

	$_POST['txt_dm_subject'] = strip_tags($_POST['txt_dm_subject']);
	$_POST['txt_dm_mobile_subject'] = strip_tags($_POST['txt_dm_mobile_subject']);

	foreach($_POST as $key => $value)
	{
		$vKey = substr($key,0,3);
		$DKey = substr($key,4);
		if($vKey != "chk")
		{
			if($qValue != "") $qValue .=", ";
			if($vKey == "int")
			{
				if($value == "on")
				{
					$value = "1";
				}else
				{
				    if ($value <= 0) {
                        $value = "0";
                    }
				}
				$qValue .= "$DKey = ".$value;
			}elseif($vKey == "txt")
			{
				$qValue .= "$DKey = '".$value."'";
			}

		}


		//echo "$vKey ==> $DKey (".$value.")\r\n";
		//echo "$key ==> $value <br>";

	}

    if (!$_POST['int_dm_use_category']) {
        $qValue .= ", dm_use_category = 0";
    }

    if (!$_POST['int_dm_use_list_category']) {
        $qValue .= ", dm_use_list_category = 0";
    }

    if (!$_POST['int_dm_use_sideview']) {
        $qValue .= ", dm_use_sideview = 0";
    }

    if (!$_POST['int_dm_use_file_content']) {
        $qValue .= ", dm_use_file_content = 0";
    }

    if (!$_POST['int_dm_use_dhtml_editor']) {
        $qValue .= ", dm_use_dhtml_editor = 0";
    }

    if (!$_POST['int_dm_use_rss_view']) {
        $qValue .= ", dm_use_rss_view = 0";
    }

    if (!$_POST['int_dm_use_good']) {
        $qValue .= ", dm_use_good = 0";
    }

    if (!$_POST['int_dm_use_nogood']) {
        $qValue .= ", dm_use_nogood = 0";
    }

    if (!$_POST['int_dm_use_name']) {
        $qValue .= ", dm_use_name = 0";
    }

    if (!$_POST['int_dm_use_signature']) {
        $qValue .= ", dm_use_signature = 0";
    }

    if (!$_POST['int_dm_use_ip_view']) {
        $qValue .= ", dm_use_ip_view = 0";
    }

    if (!$_POST['int_dm_use_list_view']) {
        $qValue .= ", dm_use_list_view = 0";
    }

    if (!$_POST['int_dm_use_list_file']) {
        $qValue .= ", dm_use_list_file = 0";
    }

    if (!$_POST['int_dm_use_list_content']) {
        $qValue .= ", dm_use_list_content = 0";
    }

    $Query .= " ".$qValue." where dm_id = ".$dm_id;

	$db = new DBSQL();
    $db->DBconnect();

	$db->ExecSql($Query, "U");

	$arResult = array( "result" => "success","_return" => $dm_id,"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );

	echo json_encode( $arResult );


	//$db->writeLog($Query);



	//echo $Query;
}

else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT * FROM dm_board WHERE dm_id = $dm_id";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $query = "DELETE FROM dm_board WHERE dm_id = $dm_id";
        $db->ExecSql($query, "D");

        $arResult = array( "result" => "success","_return" => $dm_id,"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
    } else {
        $arResult = array( "result" => "fail","_return" => $dm_id,"total" => $total_count , "rows" => $arData, "notice" => '게시판이 존재하지 않습니다.', "objName" => $_POST['objName'] );
    }

    echo json_encode( $arResult );
}

else if ($type == 'new_insert') {
    $db = new DBSQL();
    $db->DBconnect();

    $dm_table = isset($_REQUEST['dm_table']) ? $_REQUEST['dm_table'] : "";
    $dm_id = isset($_REQUEST['chk_dm_id']) ? $_REQUEST['chk_dm_id'] : "";
    $dm_subject = isset($_REQUEST['dm_subject']) ? $_REQUEST['dm_subject'] : "";
    $dm_board_type = isset($_REQUEST['dm_board_type']) ? $_REQUEST['dm_board_type'] : "";
    $dm_device = isset($_REQUEST['dm_device']) ? $_REQUEST['dm_device'] : "";
    $dm_use_category = isset($_REQUEST['dm_use_category']) ? 1 : 0;
    $dm_category_list = isset($_REQUEST['dm_category_list']) ? $_REQUEST['dm_category_list'] : "";
    $dm_use_list_category = isset($_REQUEST['dm_use_list_category']) ? 1 : 0;
    $dm_list_level = isset($_REQUEST['dm_list_level']) ? $_REQUEST['dm_list_level'] : 0;
    $dm_read_level = isset($_REQUEST['dm_read_level']) ? $_REQUEST['dm_read_level'] : 0;
    $dm_write_level = isset($_REQUEST['dm_write_level']) ? $_REQUEST['dm_write_level'] : 0;
    $dm_reply_level = isset($_REQUEST['dm_reply_level']) ? $_REQUEST['dm_reply_level'] : 0;
    $dm_link_level = isset($_REQUEST['dm_link_level']) ? $_REQUEST['dm_link_level'] : 0;
    $dm_upload_level = isset($_REQUEST['dm_upload_level']) ? $_REQUEST['dm_upload_level'] : 0;
    $dm_is_reply = isset($_REQUEST['dm_is_reply']) ? 1 : 0;
    $dm_comment_level = isset($_REQUEST['dm_comment_level']) ? $_REQUEST['dm_comment_level'] : 0;
    $dm_use_secret = isset($_REQUEST['dm_use_secret']) ? $_REQUEST['dm_use_secret'] : "";
    $dm_use_good = isset($_REQUEST['dm_use_good']) ? $_REQUEST['dm_use_good'] : 0;
    $dm_use_nogood = isset($_REQUEST['dm_use_nogood']) ? $_REQUEST['dm_use_nogood'] : 0;
    $dm_subject_len = isset($_REQUEST['dm_subject_len']) ? $_REQUEST['dm_subject_len'] : "";
    $dm_page_rows = isset($_REQUEST['dm_page_rows']) ? $_REQUEST['dm_page_rows'] : "";
    $dm_mobile_page_rows = isset($_REQUEST['dm_mobile_page_rows']) ? $_REQUEST['dm_mobile_page_rows'] : "";
    $dm_use_captcha = isset($_REQUEST['dm_use_captcha']) ? $_REQUEST['dm_use_captcha'] : 0;
    $dm_use_dhtml_editor = isset($_REQUEST['dm_use_dhtml_editor']) ? $_REQUEST['dm_use_dhtml_editor'] : 0;
    $dm_upload_count = isset($_REQUEST['dm_upload_count']) ? $_REQUEST['dm_upload_count'] : 0;
    $dm_upload_size = isset($_REQUEST['dm_upload_size']) ? $_REQUEST['dm_upload_size'] : "";
    $dm_use_ip_view = isset($_REQUEST['dm_use_ip_view']) ? $_REQUEST['dm_use_ip_view'] : "";
    $dm_use_sns = isset($_REQUEST['dm_use_sns']) ? $_REQUEST['dm_use_sns'] : "";
    $dm_is_comment = isset($_REQUEST['dm_is_comment']) ? 1 : 0;
    $dm_basic_content = isset($_REQUEST['dm_basic_content']) ? $_REQUEST['dm_basic_content'] : "";
    $dm_header_content = isset($_REQUEST['dm_header_content']) ? $_REQUEST['dm_header_content'] : "";
    $dm_footer_content = isset($_REQUEST['dm_footer_content']) ? $_REQUEST['dm_footer_content'] : "";
    $dm_use_comment_secret = isset($_REQUEST['dm_use_comment_secret']) ? $_REQUEST['dm_use_comment_secret'] : "";
    $dm_admin_type = isset($_REQUEST['dm_admin_type']) ? $_REQUEST['dm_admin_type'] : "";
    $dm_admin_txt = isset($_REQUEST['dm_admin_txt']) ? $_REQUEST['dm_admin_txt'] : "";
    $dm_writer_type = isset($_REQUEST['dm_writer_type']) ? $_REQUEST['dm_writer_type'] : "";
    $dm_writer_secret = isset($_REQUEST['dm_writer_secret']) ? $_REQUEST['dm_writer_secret'] : "";
    $dm_hit_count = isset($_REQUEST['dm_hit_count']) ? $_REQUEST['dm_hit_count'] : 0;
    $dm_hit_ip_deny = isset($_REQUEST['dm_hit_ip_deny']) ? $_REQUEST['dm_hit_ip_deny'] : 0;
    $dm_is_hit = isset($_REQUEST['dm_is_hit']) ? $_REQUEST['dm_is_hit'] : 0;
    $dm_hit_max = isset($_REQUEST['dm_hit_max']) ? $_REQUEST['dm_hit_max'] : "";
    $dm_is_new = isset($_REQUEST['dm_is_new']) ? $_REQUEST['dm_is_new'] : "";
    $dm_new_time = isset($_REQUEST['dm_new_time']) ? $_REQUEST['dm_new_time'] : "";
    $dm_use_file_icon = isset($_REQUEST['dm_use_file_icon']) ? $_REQUEST['dm_use_file_icon'] : "";
    $dm_reply_delete_type = isset($_REQUEST['dm_reply_delete_type']) ? $_REQUEST['dm_reply_delete_type'] : "";
    $dm_use_link = isset($_REQUEST['dm_use_link']) ? $_REQUEST['dm_use_link'] : 0;
    $dm_use_file = isset($_REQUEST['dm_use_file']) ? $_REQUEST['dm_use_file'] : 0;
    $dm_upload_ext = isset($_REQUEST['dm_upload_ext']) ? $_REQUEST['dm_upload_ext'] : "";
    $dm_use_prev_write = isset($_REQUEST['dm_use_prev_write']) ? $_REQUEST['dm_use_prev_write'] : "";
    $dm_use_list = isset($_REQUEST['dm_use_list']) ? $_REQUEST['dm_use_list'] : "";
    $dm_read_point = isset($_REQUEST['dm_read_point']) ? $_REQUEST['dm_read_point'] : "";
    $dm_write_point = isset($_REQUEST['dm_write_point']) ? $_REQUEST['dm_write_point'] : "";
    $dm_download_point = isset($_REQUEST['dm_download_point']) ? $_REQUEST['dm_download_point'] : "";
    $dm_read_point_type = isset($_REQUEST['dm_read_point_type']) ? $_REQUEST['dm_read_point_type'] : "";
    $dm_write_point_type = isset($_REQUEST['dm_write_point_type']) ? $_REQUEST['dm_write_point_type'] : "";
    $dm_download_point_type = isset($_REQUEST['dm_download_point_type']) ? $_REQUEST['dm_download_point_type'] : "";
    $dm_comment_point = isset($_REQUEST['dm_comment_point']) ? $_REQUEST['dm_comment_point'] : "";
    $dm_comment_point_type = isset($_REQUEST['dm_comment_point_type']) ? $_REQUEST['dm_comment_point_type'] : "";
    $dm_read_point_expired = isset($_REQUEST['dm_read_point_expired']) ? $_REQUEST['dm_read_point_expired'] : "";
    $dm_download_point_expired = isset($_REQUEST['dm_download_point_expired']) ? $_REQUEST['dm_download_point_expired'] : "";
    $dm_skin = isset($_REQUEST['dm_skin']) ? $_REQUEST['dm_skin'] : "";
    $dm_mobile_skin = isset($_REQUEST['dm_mobile_skin']) ? $_REQUEST['dm_mobile_skin'] : "";
    $dm_use_name = isset($_REQUEST['dm_use_name']) ? $_REQUEST['dm_use_name'] : "";
    $dm_list_good = isset($_REQUEST['dm_list_good']) ? $_REQUEST['dm_list_good'] : "";
    $dm_use_view_level = isset($_REQUEST['dm_use_view_level']) ? $_REQUEST['dm_use_view_level'] : "";
    $dm_gallery_width = isset($_REQUEST['dm_gallery_width']) ? $_REQUEST['dm_gallery_width'] : "";
    $dm_gallery_height = isset($_REQUEST['dm_gallery_height']) ? $_REQUEST['dm_gallery_height'] : "";
    $dm_mobile_gallery_width = isset($_REQUEST['dm_mobile_gallery_width']) ? $_REQUEST['dm_mobile_gallery_width'] : "";
    $dm_mobile_gallery_height = isset($_REQUEST['dm_mobile_gallery_height']) ? $_REQUEST['dm_mobile_gallery_height'] : "";
    $dm_sort_field = isset($_REQUEST['dm_sort_field']) ? $_REQUEST['dm_sort_field'] : "";
    $dm_auth_type = isset($_REQUEST['dm_auth_type']) ? $_REQUEST['dm_auth_type'] : "";

    $dm_list_group = isset($_REQUEST['dm_list_group']) ? $_REQUEST['dm_list_group'] : array();
    $dm_read_group = isset($_REQUEST['dm_read_group']) ? $_REQUEST['dm_read_group'] : array();
    $dm_write_group = isset($_REQUEST['dm_write_group']) ? $_REQUEST['dm_write_group'] : array();
    $dm_reply_group = isset($_REQUEST['dm_reply_group']) ? $_REQUEST['dm_reply_group'] : array();
    $dm_comment_group = isset($_REQUEST['dm_comment_group']) ? $_REQUEST['dm_comment_group'] : array();
    $dm_link_group = isset($_REQUEST['dm_link_group']) ? $_REQUEST['dm_link_group'] : array();
    $dm_upload_group = isset($_REQUEST['dm_upload_group']) ? $_REQUEST['dm_upload_group'] : array();

    if ($_FILES['dm_hit_icon'] && $_FILES['dm_hit_icon']['name'] != "") {

        @mkdir($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);
        @chmod($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);

        $file = $_FILES['dm_hit_icon'];

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

        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_BOARD_PATH.$dm_skin."/images/".$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_file = $row['dm_hit_icon'];

                if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                    if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                        unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file);
                    }
                }
            }
        }

        $dm_hit_icon = $new_file_name;
    } else {
        $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_hit_icon = $row['dm_hit_icon'];

        if (!$dm_is_hit) {
            if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_hit_icon)) {
                if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_hit_icon)) {
                    unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_hit_icon);
                    $dm_hit_icon = "";
                }
            }
        }
    }

    if ($_FILES['dm_new_icon'] && $_FILES['dm_new_icon']['name'] != "") {

        @mkdir($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);
        @chmod($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);

        $file = $_FILES['dm_new_icon'];

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

        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_BOARD_PATH.$dm_skin."/images/".$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_file = $row['dm_new_icon'];

                if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                    if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                        unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file);
                    }
                }
            }
        }

        $dm_new_icon = $new_file_name;
    } else {
        $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_new_icon = $row['dm_new_icon'];

        if (!$dm_is_new) {
            if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_new_icon)) {
                if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_new_icon)) {
                    unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_new_icon);
                    $dm_new_icon = "";
                }
            }
        }
    }

    $dm_category_list = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $dm_category_list);
    $dm_subject = strip_tags($dm_subject);
    $dm_basic_content = htmlspecialchars($dm_basic_content, ENT_QUOTES);
    $dm_basic_content = addslashes($dm_basic_content);
    $dm_header_content = addslashes($dm_header_content);
    $dm_footer_content = addslashes($dm_footer_content);

    $dm_list_group = implode("|", $dm_list_group);
    $dm_read_group = implode("|", $dm_read_group);
    $dm_write_group = implode("|", $dm_write_group);
    $dm_reply_group = implode("|", $dm_reply_group);
    $dm_comment_group = implode("|", $dm_comment_group);
    $dm_link_group = implode("|", $dm_link_group);
    $dm_upload_group = implode("|", $dm_upload_group);

    $query = "INSERT INTO dm_board (`dm_id`, `dm_domain`, `dm_table`, `dm_subject`, `dm_device`, `dm_board_type`, `dm_list_level`, `dm_read_level`, `dm_write_level`, `dm_reply_level`, `dm_is_reply`, `dm_comment_level`, `dm_is_comment`,
    `dm_use_category`, `dm_use_list_category`,  `dm_category_list`, `dm_use_secret`, `dm_use_dhtml_editor`, `dm_use_good`, `dm_use_nogood`, `dm_use_ip_view`, `dm_subject_len`, `dm_page_rows`, `dm_mobile_page_rows`,  `dm_upload_size`,
    `dm_upload_count`, `dm_use_sns`, `dm_use_captcha`, `dm_basic_content`, `dm_header_content`, `dm_footer_content`, `dm_use_comment_secret`, `dm_admin_type`, `dm_admin_txt`, `dm_writer_type`, `dm_writer_secret`, `dm_hit_count`,
    `dm_hit_ip_deny`, `dm_is_hit`, `dm_hit_max`, `dm_is_new`, `dm_new_time`, `dm_use_file_icon`, `dm_reply_delete_type`, `dm_use_link`, `dm_use_file`, `dm_upload_ext`, `dm_use_prev_write`, `dm_use_list`, `dm_read_point`, `dm_write_point`, `dm_download_point`,
    `dm_read_point_type`, `dm_write_point_type`, `dm_download_point_type`, `dm_comment_point`, `dm_comment_point_type`, `dm_read_point_expired`, `dm_download_point_expired`, `dm_skin`, `dm_mobile_skin`, `dm_link_level`, `dm_upload_level`,
    `dm_hit_icon`, `dm_new_icon`, `dm_use_name`, `dm_list_good`, `dm_use_view_level`, `dm_gallery_width`, `dm_gallery_height`, `dm_mobile_gallery_width`, `dm_mobile_gallery_height`, `dm_sort_field`, `dm_list_group`,  `dm_read_group`,  `dm_write_group`, 
    `dm_reply_group`, `dm_comment_group`, `dm_link_group`, `dm_upload_group`, `dm_auth_type`) 
    VALUE ('".$dm_id."', '".$site_id."', '".$dm_table."', '".$dm_subject."', '".$dm_device."', '".$dm_board_type."', '".$dm_list_level."', '".$dm_read_level."', '".$dm_write_level."', '".$dm_reply_level."', '".$dm_is_reply."', '".$dm_comment_level."',
    '".$dm_is_comment."', '".$dm_use_category."', '".$dm_use_list_category."', '".$dm_category_list."', '".$dm_use_secret."', '".$dm_use_dhtml_editor."', '".$dm_use_good."', '".$dm_use_nogood."', '".$dm_use_ip_view."', '".$dm_subject_len."', 
    '".$dm_page_rows."', '".$dm_mobile_page_rows."', '".$dm_upload_size."', '".$dm_upload_count."', '".$dm_use_sns."', '".$dm_use_captcha."', '".$dm_basic_content."', '".$dm_header_content."','".$dm_footer_content."', '".$dm_use_comment_secret."',
    '".$dm_admin_type."', '".$dm_admin_txt."', '".$dm_writer_type."', '".$dm_writer_secret."', '".$dm_hit_count."', '".$dm_hit_ip_deny."', '".$dm_is_hit."', '".$dm_hit_max."', '".$dm_is_new."', '".$dm_new_time."', '".$dm_use_file_icon."'
    , '".$dm_reply_delete_type."', '".$dm_use_link."', '".$dm_use_file."', '".$dm_upload_ext."', '".$dm_use_prev_write."', '".$dm_use_list."', '".$dm_read_point."', '".$dm_write_point."', '".$dm_download_point."', 
    '".$dm_read_point_type."', '".$dm_write_point_type."', '".$dm_download_point_type."', '".$dm_comment_point."', '".$dm_comment_point_type."', '".$dm_read_point_expired."', '".$dm_download_point_expired."', '".$dm_skin."', '".$dm_mobile_skin."'
    , '".$dm_link_level."', '".$dm_upload_level."' , '".$dm_hit_icon."' , '".$dm_new_icon."', '".$dm_use_name."', '".$dm_list_good."', '".$dm_use_view_level."', '".$dm_gallery_width."', '".$dm_gallery_height."', '".$dm_mobile_gallery_width."',
    '".$dm_mobile_gallery_height."', '".$dm_sort_field."' , '".$dm_list_group."' ,  '".$dm_read_group."' ,'".$dm_write_group."' ,'".$dm_reply_group."' ,'".$dm_comment_group."' , '".$dm_link_group."' , '".$dm_upload_group."' , '".$dm_auth_type."')";

    $TABLE_NAME = "dm_write_".$_POST['dm_table'];

    $sQuery = "SHOW TABLES LIKE '".$TABLE_NAME."'";

    $db->ExecSql($sQuery, "S");
    if($db->Num > 0)
    {
        $arResult = array( "result" => "duplicate","_return" => $newId,"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
        echo json_encode( $arResult );
    }else
    {

        $file = file('./table_create.sql');

        $cQeury = implode("\n", $file);

        $source = array('/<<TABLE_NAME>>/', '/;/');
        $target = array($TABLE_NAME, '');
        $cQeury = preg_replace($source, $target, $cQeury);

        $db->ExecSql($cQeury, "I");

        $db->ExecSql($query, "I");

        $sQuery = "select * from dm_board where dm_subject = '".$_POST['dm_subject']."' order by dm_id desc limit 0,1";
        $db->ExecSql($sQuery, "S");
        if($db->Num > 0)
        {
            $row = $db->GetPosition(0);
            $arResult = array( "result" => "success","_return" => $row["dm_id"],"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
        }else
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
        }

        echo json_encode( $arResult );
    }
}

else if ($type == 'new_update') {
    $db = new DBSQL();
    $db->DBconnect();

    $dm_table = isset($_REQUEST['dm_table']) ? $_REQUEST['dm_table'] : "";
    $dm_id = isset($_REQUEST['chk_dm_id']) ? $_REQUEST['chk_dm_id'] : "";
    $dm_subject = isset($_REQUEST['dm_subject']) ? $_REQUEST['dm_subject'] : "";
    $dm_board_type = isset($_REQUEST['dm_board_type']) ? $_REQUEST['dm_board_type'] : "";
    $dm_device = isset($_REQUEST['dm_device']) ? $_REQUEST['dm_device'] : "";
    $dm_use_category = isset($_REQUEST['dm_use_category']) ? 1 : 0;
    $dm_category_list = isset($_REQUEST['dm_category_list']) ? $_REQUEST['dm_category_list'] : "";
    $dm_use_list_category = isset($_REQUEST['dm_use_list_category']) ? 1 : 0;
    $dm_list_level = isset($_REQUEST['dm_list_level']) ? $_REQUEST['dm_list_level'] : 0;
    $dm_read_level = isset($_REQUEST['dm_read_level']) ? $_REQUEST['dm_read_level'] : 0;
    $dm_write_level = isset($_REQUEST['dm_write_level']) ? $_REQUEST['dm_write_level'] : 0;
    $dm_reply_level = isset($_REQUEST['dm_reply_level']) ? $_REQUEST['dm_reply_level'] : 0;
    $dm_link_level = isset($_REQUEST['dm_link_level']) ? $_REQUEST['dm_link_level'] : 0;
    $dm_upload_level = isset($_REQUEST['dm_upload_level']) ? $_REQUEST['dm_upload_level'] : 0;
    $dm_is_reply = isset($_REQUEST['dm_is_reply']) ? 1 : 0;
    $dm_comment_level = isset($_REQUEST['dm_comment_level']) ? $_REQUEST['dm_comment_level'] : 0;
    $dm_use_secret = isset($_REQUEST['dm_use_secret']) ? $_REQUEST['dm_use_secret'] : "";
    $dm_use_good = isset($_REQUEST['dm_use_good']) ? $_REQUEST['dm_use_good'] : 0;
    $dm_use_nogood = isset($_REQUEST['dm_use_nogood']) ? $_REQUEST['dm_use_nogood'] : 0;
    $dm_subject_len = isset($_REQUEST['dm_subject_len']) ? $_REQUEST['dm_subject_len'] : "";
    $dm_page_rows = isset($_REQUEST['dm_page_rows']) ? $_REQUEST['dm_page_rows'] : "";
    $dm_mobile_page_rows = isset($_REQUEST['dm_mobile_page_rows']) ? $_REQUEST['dm_mobile_page_rows'] : "";
    $dm_use_captcha = isset($_REQUEST['dm_use_captcha']) ? $_REQUEST['dm_use_captcha'] : 0;
    $dm_use_dhtml_editor = isset($_REQUEST['dm_use_dhtml_editor']) ? $_REQUEST['dm_use_dhtml_editor'] : 0;
    $dm_upload_count = isset($_REQUEST['dm_upload_count']) ? $_REQUEST['dm_upload_count'] : 0;
    $dm_upload_size = isset($_REQUEST['dm_upload_size']) ? $_REQUEST['dm_upload_size'] : "";
    $dm_use_ip_view = isset($_REQUEST['dm_use_ip_view']) ? $_REQUEST['dm_use_ip_view'] : "";
    $dm_use_sns = isset($_REQUEST['dm_use_sns']) ? $_REQUEST['dm_use_sns'] : "";
    $dm_is_comment = isset($_REQUEST['dm_is_comment']) ? 1 : 0;
    $dm_basic_content = isset($_REQUEST['dm_basic_content']) ? $_REQUEST['dm_basic_content'] : "";
    $dm_header_content = isset($_REQUEST['dm_header_content']) ? $_REQUEST['dm_header_content'] : "";
    $dm_footer_content = isset($_REQUEST['dm_footer_content']) ? $_REQUEST['dm_footer_content'] : "";
    $dm_use_comment_secret = isset($_REQUEST['dm_use_comment_secret']) ? $_REQUEST['dm_use_comment_secret'] : "";
    $dm_admin_type = isset($_REQUEST['dm_admin_type']) ? $_REQUEST['dm_admin_type'] : "";
    $dm_admin_txt = isset($_REQUEST['dm_admin_txt']) ? $_REQUEST['dm_admin_txt'] : "";
    $dm_writer_type = isset($_REQUEST['dm_writer_type']) ? $_REQUEST['dm_writer_type'] : "";
    $dm_writer_secret = isset($_REQUEST['dm_writer_secret']) ? $_REQUEST['dm_writer_secret'] : "";
    $dm_hit_count = isset($_REQUEST['dm_hit_count']) ? $_REQUEST['dm_hit_count'] : 0;
    $dm_hit_ip_deny = isset($_REQUEST['dm_hit_ip_deny']) ? $_REQUEST['dm_hit_ip_deny'] : 0;
    $dm_is_hit = isset($_REQUEST['dm_is_hit']) ? $_REQUEST['dm_is_hit'] : 0;
    $dm_hit_max = isset($_REQUEST['dm_hit_max']) ? $_REQUEST['dm_hit_max'] : "";
    $dm_is_new = isset($_REQUEST['dm_is_new']) ? $_REQUEST['dm_is_new'] : "";
    $dm_new_time = isset($_REQUEST['dm_new_time']) ? $_REQUEST['dm_new_time'] : "";
    $dm_use_file_icon = isset($_REQUEST['dm_use_file_icon']) ? $_REQUEST['dm_use_file_icon'] : "";
    $dm_reply_delete_type = isset($_REQUEST['dm_reply_delete_type']) ? $_REQUEST['dm_reply_delete_type'] : "";
    $dm_use_link = isset($_REQUEST['dm_use_link']) ? $_REQUEST['dm_use_link'] : 0;
    $dm_use_file = isset($_REQUEST['dm_use_file']) ? $_REQUEST['dm_use_file'] : 0;
    $dm_upload_ext = isset($_REQUEST['dm_upload_ext']) ? $_REQUEST['dm_upload_ext'] : "";
    $dm_use_prev_write = isset($_REQUEST['dm_use_prev_write']) ? $_REQUEST['dm_use_prev_write'] : "";
    $dm_use_list = isset($_REQUEST['dm_use_list']) ? $_REQUEST['dm_use_list'] : "";
    $dm_read_point = isset($_REQUEST['dm_read_point']) ? $_REQUEST['dm_read_point'] : "";
    $dm_write_point = isset($_REQUEST['dm_write_point']) ? $_REQUEST['dm_write_point'] : "";
    $dm_download_point = isset($_REQUEST['dm_download_point']) ? $_REQUEST['dm_download_point'] : "";
    $dm_comment_point = isset($_REQUEST['dm_comment_point']) ? $_REQUEST['dm_comment_point'] : "";
    $dm_read_point_type = isset($_REQUEST['dm_read_point_type']) ? $_REQUEST['dm_read_point_type'] : "";
    $dm_write_point_type = isset($_REQUEST['dm_write_point_type']) ? $_REQUEST['dm_write_point_type'] : "";
    $dm_download_point_type = isset($_REQUEST['dm_download_point_type']) ? $_REQUEST['dm_download_point_type'] : "";
    $dm_comment_point_type = isset($_REQUEST['dm_comment_point_type']) ? $_REQUEST['dm_comment_point_type'] : "";
    $dm_skin = isset($_REQUEST['dm_skin']) ? $_REQUEST['dm_skin'] : "";
    $dm_mobile_skin = isset($_REQUEST['dm_mobile_skin']) ? $_REQUEST['dm_mobile_skin'] : "";
    $dm_use_name = isset($_REQUEST['dm_use_name']) ? $_REQUEST['dm_use_name'] : "";
    $dm_list_good = isset($_REQUEST['dm_list_good']) ? $_REQUEST['dm_list_good'] : "";
    $dm_use_view_level = isset($_REQUEST['dm_use_view_level']) ? $_REQUEST['dm_use_view_level'] : "";
    $dm_read_point_expired = isset($_REQUEST['dm_read_point_expired']) ? $_REQUEST['dm_read_point_expired'] : "";
    $dm_download_point_expired = isset($_REQUEST['dm_download_point_expired']) ? $_REQUEST['dm_download_point_expired'] : "";
    $dm_gallery_width = isset($_REQUEST['dm_gallery_width']) ? $_REQUEST['dm_gallery_width'] : "";
    $dm_gallery_height = isset($_REQUEST['dm_gallery_height']) ? $_REQUEST['dm_gallery_height'] : "";
    $dm_mobile_gallery_width = isset($_REQUEST['dm_mobile_gallery_width']) ? $_REQUEST['dm_mobile_gallery_width'] : "";
    $dm_mobile_gallery_height = isset($_REQUEST['dm_mobile_gallery_height']) ? $_REQUEST['dm_mobile_gallery_height'] : "";
    $dm_sort_field = isset($_REQUEST['dm_sort_field']) ? $_REQUEST['dm_sort_field'] : "";
    $dm_auth_type = isset($_REQUEST['dm_auth_type']) ? $_REQUEST['dm_auth_type'] : "";

    $dm_list_group = isset($_REQUEST['dm_list_group']) ? $_REQUEST['dm_list_group'] : array();
    $dm_read_group = isset($_REQUEST['dm_read_group']) ? $_REQUEST['dm_read_group'] : array();
    $dm_write_group = isset($_REQUEST['dm_write_group']) ? $_REQUEST['dm_write_group'] : array();
    $dm_reply_group = isset($_REQUEST['dm_reply_group']) ? $_REQUEST['dm_reply_group'] : array();
    $dm_comment_group = isset($_REQUEST['dm_comment_group']) ? $_REQUEST['dm_comment_group'] : array();
    $dm_link_group = isset($_REQUEST['dm_link_group']) ? $_REQUEST['dm_link_group'] : array();
    $dm_upload_group = isset($_REQUEST['dm_upload_group']) ? $_REQUEST['dm_upload_group'] : array();

    if ($_FILES['dm_hit_icon'] && $_FILES['dm_hit_icon']['name'] != "") {
        @mkdir($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);
        @chmod($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);

        $file = $_FILES['dm_hit_icon'];

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

        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_BOARD_PATH.$dm_skin."/images/".$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_file = $row['dm_hit_icon'];

                if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                    if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                        unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file);
                    }
                }
            }
        }

        $dm_hit_icon = $new_file_name;
    } else {
        $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_hit_icon = $row['dm_hit_icon'];
    }

    if ($_FILES['dm_new_icon'] && $_FILES['dm_new_icon']['name'] != "") {

        @mkdir($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);
        @chmod($_VAR_BOARD_PATH.$dm_skin."/images/", 0707);

        $file = $_FILES['dm_new_icon'];

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

        $new_file_name = date("YmdHis")."_".hash("md5", $file['name']).".".$ext;

        if(!move_uploaded_file($file['tmp_name'], $_VAR_BOARD_PATH.$dm_skin."/images/".$new_file_name)) {
            $arResult = array("result" => "fail", "_return" => '파일업로드 실패');
            echo json_encode($arReturn);
            exit;
        } else {
            $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

            $db->ExecSql($query, "S");

            $row = $db->Fetch();

            if ($row) {
                $dm_old_file = $row['dm_new_icon'];

                if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                    if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file)) {
                        unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_old_file);
                    }
                }
            }
        }

        $dm_new_icon = $new_file_name;
    } else {
        $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_new_icon = $row['dm_new_icon'];
    }

    if (isset($_REQUEST['dm_del_hit']) && $_REQUEST['dm_del_hit']) {
        $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_hit_icon = $row['dm_hit_icon'];

        if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_hit_icon)) {
            if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_hit_icon)) {
                unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_hit_icon);
                $dm_hit_icon = "";
            }
        }
    }

    if (isset($_REQUEST['dm_del_new']) && $_REQUEST['dm_del_new']) {
        $query = "SELECT * FROM `dm_board` WHERE `dm_id` = '$dm_id'";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        $dm_new_icon = $row['dm_new_icon'];

        if (file_exists($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_new_icon)) {
            if (is_file($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_new_icon)) {
                unlink($_VAR_BOARD_PATH.$dm_skin."/images/".$dm_new_icon);
                $dm_new_icon = "";
            }
        }
    }

    $dm_list_group = implode("|", $dm_list_group);
    $dm_read_group = implode("|", $dm_read_group);
    $dm_write_group = implode("|", $dm_write_group);
    $dm_reply_group = implode("|", $dm_reply_group);
    $dm_comment_group = implode("|", $dm_comment_group);
    $dm_link_group = implode("|", $dm_link_group);
    $dm_upload_group = implode("|", $dm_upload_group);

    $dm_category_list = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $dm_category_list);
    $dm_subject = strip_tags($dm_subject);
    $dm_basic_content = htmlspecialchars($dm_basic_content, ENT_QUOTES);
    $dm_basic_content = addslashes($dm_basic_content);
    $dm_header_content = addslashes($dm_header_content);
    $dm_footer_content = addslashes($dm_footer_content);

    $query = "UPDATE dm_board SET `dm_subject` ='{$dm_subject}', `dm_board_type` ='{$dm_board_type}', `dm_device` ='{$dm_device}', `dm_use_category` = '{$dm_use_category}',
    `dm_category_list` = '{$dm_category_list}', `dm_use_list_category` = '{$dm_use_list_category}', `dm_list_level` = '{$dm_list_level}', `dm_read_level` = '{$dm_read_level}',
    `dm_write_level` = '{$dm_write_level}', `dm_reply_level` = '{$dm_reply_level}', `dm_is_reply` = '{$dm_is_reply}', `dm_comment_level` = '{$dm_comment_level}',
    `dm_use_secret` = '{$dm_use_secret}', `dm_use_good` = '{$dm_use_good}', `dm_use_nogood` = '{$dm_use_nogood}', `dm_subject_len` = '{$dm_subject_len}',
    `dm_page_rows` = '{$dm_page_rows}', `dm_mobile_page_rows` = '{$dm_mobile_page_rows}', `dm_use_captcha` = '{$dm_use_captcha}', `dm_use_dhtml_editor` = '{$dm_use_dhtml_editor}',
    `dm_upload_count` = '{$dm_upload_count}', `dm_upload_size` = '{$dm_upload_size}', `dm_use_ip_view` = '{$dm_use_ip_view}', `dm_use_sns` = '{$dm_use_sns}',
    `dm_is_comment` = '{$dm_is_comment}', `dm_basic_content` = '{$dm_basic_content}', `dm_header_content` = '{$dm_header_content}', `dm_footer_content` = '{$dm_footer_content}',
    `dm_use_comment_secret` = '{$dm_use_comment_secret}', `dm_admin_type` = '{$dm_admin_type}', `dm_admin_txt` = '{$dm_admin_txt}', `dm_writer_type` = '{$dm_writer_type}',
    `dm_writer_secret` = '{$dm_writer_secret}', `dm_hit_count` = '{$dm_hit_count}', `dm_hit_ip_deny` = '{$dm_hit_ip_deny}', `dm_is_hit` = '{$dm_is_hit}',
    `dm_hit_max` = '{$dm_hit_max}', `dm_is_new` = '{$dm_is_new}', `dm_new_time` = '{$dm_new_time}', `dm_use_file_icon` = '{$dm_use_file_icon}',
    `dm_reply_delete_type` = '{$dm_reply_delete_type}', `dm_use_link` = '{$dm_use_link}', `dm_use_file` = '{$dm_use_file}', `dm_upload_ext` = '{$dm_upload_ext}',
    `dm_use_prev_write` = '{$dm_use_prev_write}', `dm_use_list` = '{$dm_use_list}', `dm_read_point` = '{$dm_read_point}', `dm_read_point` = '{$dm_read_point}',
     `dm_write_point` = '{$dm_write_point}', `dm_download_point` = '{$dm_download_point}', `dm_read_point_type` = '{$dm_read_point_type}', `dm_write_point_type` = '{$dm_write_point_type}',
     `dm_download_point_type` = '{$dm_download_point_type}', `dm_comment_point` = '{$dm_comment_point}', `dm_comment_point_type` = '{$dm_comment_point_type}',
     `dm_read_point_expired` = '{$dm_read_point_expired}', `dm_download_point_expired` = '{$dm_download_point_expired}', `dm_skin` = '{$dm_skin}', `dm_mobile_skin` = '{$dm_mobile_skin}'
     , `dm_hit_icon` = '{$dm_hit_icon}', `dm_new_icon` = '{$dm_new_icon}', `dm_use_name` = '".$dm_use_name."', `dm_list_good` = '{$dm_list_good}', `dm_use_view_level` = '{$dm_use_view_level}',
     `dm_gallery_width` = '{$dm_gallery_width}', `dm_gallery_height` = '{$dm_gallery_height}', `dm_mobile_gallery_width` = '{$dm_mobile_gallery_width}', `dm_mobile_gallery_height` = '{$dm_mobile_gallery_height}',
     `dm_sort_field` = '{$dm_sort_field}', `dm_list_group` = '{$dm_list_group}', `dm_read_group` = '{$dm_read_group}', `dm_write_group` = '{$dm_write_group}', `dm_reply_group` = '{$dm_reply_group}', `dm_comment_group` = '{$dm_comment_group}', 
     `dm_link_group` = '{$dm_link_group}', `dm_upload_group` = '{$dm_upload_group}' , `dm_auth_type` = '{$dm_auth_type}'
     WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "U");

    $arResult = array( "result" => "success","_return" => $dm_id,"total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );

    echo json_encode( $arResult );
}
?>