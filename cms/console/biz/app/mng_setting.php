<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-07-30
 * Time: 오후 5:15
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$dm_domain_id = getSession("site_id");
$db = new DBSQL();
$db->DBconnect();

if ($type == "select") {
    $query = "SELECT * FROM dm_layout WHERE dm_domain = '".$dm_domain_id."'";
    $db->ExecSql($query, "S");
    $layoutInfo = $db->Fetch();

    $query = "SELECT * FROM dm_config WHERE dm_domain_id = '".$dm_domain_id."'";

    $db->ExecSql($query, "S");

    $row = $db->Fetch();

    if ($row['dm_personal_image']) {
        $row['file_path'] = $_VAR_WEB_URL.'thema/'.$layoutInfo['dm_top_content'].'/images/'.$row['dm_personal_image'];
    }

    if ($row['dm_top_logo']) {
        $row['dm_top_logo_url'] = $_VAR_WEB_URL.'thema/'.$layoutInfo['dm_top_content'].'/images/'.$row['dm_top_logo'];
    }

    if ($row['dm_bottom_logo']) {
        $row['dm_bottom_logo_url'] = $_VAR_WEB_URL.'thema/'.$layoutInfo['dm_top_content'].'/images/'.$row['dm_bottom_logo'];
    }

    $arResult = array("result" => "success", "row" => $row);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == "update") {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_url = isset($_REQUEST['dm_url']) ? trim($_REQUEST['dm_url']) : "";
    $dm_site_name = isset($_REQUEST['dm_site_name']) ? trim($_REQUEST['dm_site_name']) : "";
    $dm_title = isset($_REQUEST['dm_title']) ? trim($_REQUEST['dm_title']) : "";
    $dm_meta_desc = isset($_REQUEST['dm_meta_desc']) ? trim($_REQUEST['dm_meta_desc']) : "";
    $dm_meta_keyword = isset($_REQUEST['dm_meta_keyword']) ? trim($_REQUEST['dm_meta_keyword']) : "";
    $dm_naver_site_verification = isset($_REQUEST['dm_naver_site_verification']) ? trim($_REQUEST['dm_naver_site_verification']) : "";
    $dm_company_name = isset($_REQUEST['dm_company_name']) ? trim($_REQUEST['dm_company_name']) : "";
    $dm_company_number1 = isset($_REQUEST['dm_company_number1']) ? trim($_REQUEST['dm_company_number1']) : "";
    $dm_company_number2 = isset($_REQUEST['dm_company_number2']) ? trim($_REQUEST['dm_company_number2']) : "";
    $dm_company_number3 = isset($_REQUEST['dm_company_number3']) ? trim($_REQUEST['dm_company_number3']) : "";
    $dm_tel_company_number = isset($_REQUEST['dm_tel_company_number']) ? trim($_REQUEST['dm_tel_company_number']) : "";
    $dm_ceo = isset($_REQUEST['dm_ceo']) ? trim($_REQUEST['dm_ceo']) : "";
    $dm_ceo_email1 = isset($_REQUEST['dm_ceo_email1']) ? trim($_REQUEST['dm_ceo_email1']) : "";
    $dm_ceo_email2 = isset($_REQUEST['dm_ceo_email2']) ? trim($_REQUEST['dm_ceo_email2']) : "";
    $dm_zip = isset($_REQUEST['dm_zip']) ? trim($_REQUEST['dm_zip']) : "";
    $dm_addr1 = isset($_REQUEST['dm_addr1']) ? trim($_REQUEST['dm_addr1']) : "";
    $dm_addr2 = isset($_REQUEST['dm_addr2']) ? trim($_REQUEST['dm_addr2']) : "";
    $dm_addr3 = isset($_REQUEST['dm_addr3']) ? trim($_REQUEST['dm_addr3']) : "";
    $dm_addr_jiboen = isset($_REQUEST['dm_addr_jibeon']) ? trim($_REQUEST['dm_addr_jibeon']) : "";
    $dm_tel = isset($_REQUEST['dm_tel']) ? trim($_REQUEST['dm_tel']) : "";
    $dm_customer_tel = isset($_REQUEST['dm_customer_tel']) ? trim($_REQUEST['dm_customer_tel']) : "";
    $dm_fax = isset($_REQUEST['dm_fax']) ? trim($_REQUEST['dm_fax']) : "";
    $dm_customer_email1 = isset($_REQUEST['dm_customer_email1']) ? trim($_REQUEST['dm_customer_email1']) : "";
    $dm_customer_email2 = isset($_REQUEST['dm_customer_email2']) ? trim($_REQUEST['dm_customer_email2']) : "";

    $dm_customer_daily_use = isset($_REQUEST['dm_customer_daily_use']) ? trim($_REQUEST['dm_customer_daily_use']) : "";
    $dm_customer_daily_text = isset($_REQUEST['dm_customer_daily_text']) ? trim($_REQUEST['dm_customer_daily_text']) : "";
    $dm_customer_daily_start_text = isset($_REQUEST['dm_customer_daily_start_text']) ? trim($_REQUEST['dm_customer_daily_start_text']) : "";
    $dm_customer_daily_start_hour = isset($_REQUEST['dm_customer_daily_start_hour']) ? trim($_REQUEST['dm_customer_daily_start_hour']) : "";
    $dm_customer_daily_start_min = isset($_REQUEST['dm_customer_daily_start_min']) ? trim($_REQUEST['dm_customer_daily_start_min']) : "";
    $dm_customer_daily_end_text = isset($_REQUEST['dm_customer_daily_end_text']) ? trim($_REQUEST['dm_customer_daily_end_text']) : "";
    $dm_customer_daily_end_hour = isset($_REQUEST['dm_customer_daily_end_hour']) ? trim($_REQUEST['dm_customer_daily_end_hour']) : "";
    $dm_customer_daily_end_min = isset($_REQUEST['dm_customer_daily_end_min']) ? trim($_REQUEST['dm_customer_daily_end_min']) : "";
    $dm_customer_weekend_use = isset($_REQUEST['dm_customer_weekend_use']) ? trim($_REQUEST['dm_customer_weekend_use']) : "";
    $dm_customer_weekend_text = isset($_REQUEST['dm_customer_weekend_text']) ? trim($_REQUEST['dm_customer_weekend_text']) : "";
    $dm_customer_weekend_start_text = isset($_REQUEST['dm_customer_weekend_start_text']) ? trim($_REQUEST['dm_customer_weekend_start_text']) : "";
    $dm_customer_weekend_start_hour = isset($_REQUEST['dm_customer_weekend_start_hour']) ? trim($_REQUEST['dm_customer_weekend_start_hour']) : "";
    $dm_customer_weekend_start_min = isset($_REQUEST['dm_customer_weekend_start_min']) ? trim($_REQUEST['dm_customer_weekend_start_min']) : "";
    $dm_customer_weekend_end_text = isset($_REQUEST['dm_customer_weekend_end_text']) ? trim($_REQUEST['dm_customer_weekend_end_text']) : "";
    $dm_customer_weekend_end_hour = isset($_REQUEST['dm_customer_weekend_end_hour']) ? trim($_REQUEST['dm_customer_weekend_end_hour']) : "";
    $dm_customer_weekend_end_min = isset($_REQUEST['dm_customer_weekend_end_min']) ? trim($_REQUEST['dm_customer_weekend_end_min']) : "";
    $dm_customer_break_use = isset($_REQUEST['dm_customer_break_use']) ? trim($_REQUEST['dm_customer_break_use']) : "";
    $dm_customer_break_text = isset($_REQUEST['dm_customer_break_text']) ? trim($_REQUEST['dm_customer_break_text']) : "";
    $dm_customer_break_start_text = isset($_REQUEST['dm_customer_break_start_text']) ? trim($_REQUEST['dm_customer_break_start_text']) : "";
    $dm_customer_break_start_hour = isset($_REQUEST['dm_customer_break_start_hour']) ? trim($_REQUEST['dm_customer_break_start_hour']) : "";
    $dm_customer_break_start_min = isset($_REQUEST['dm_customer_break_start_min']) ? trim($_REQUEST['dm_customer_break_start_min']) : "";
    $dm_customer_break_end_text = isset($_REQUEST['dm_customer_break_end_text']) ? trim($_REQUEST['dm_customer_break_end_text']) : "";
    $dm_customer_break_end_hour = isset($_REQUEST['dm_customer_break_end_hour']) ? trim($_REQUEST['dm_customer_break_end_hour']) : "";
    $dm_customer_break_end_min = isset($_REQUEST['dm_customer_break_end_min']) ? trim($_REQUEST['dm_customer_break_end_min']) : "";
    $dm_customer_text = isset($_REQUEST['dm_customer_text']) ? trim($_REQUEST['dm_customer_text']) : "";
    $dm_customer_text_use = isset($_REQUEST['dm_customer_text_use']) ? trim($_REQUEST['dm_customer_text_use']) : "";
    $dm_personal_image = isset($_FILES['dm_personal_image']) ? $_FILES['dm_personal_image'] : "";
    $dm_top_logo = isset($_FILES['dm_top_logo']) ? $_FILES['dm_top_logo'] : "";
    $dm_bottom_logo = isset($_FILES['dm_bottom_logo']) ? $_FILES['dm_bottom_logo'] : "";
    $dm_del_image = isset($_REQUEST['dm_del_image']) ? trim($_REQUEST['dm_del_image']) : "";
    $dm_del_top_logo = isset($_REQUEST['dm_del_top_logo']) ? trim($_REQUEST['dm_del_top_logo']) : "";
    $dm_del_bottom_logo = isset($_REQUEST['dm_del_bottom_logo']) ? trim($_REQUEST['dm_del_bottom_logo']) : "";
    $dm_account_bank = isset($_REQUEST['dm_account_bank']) ? trim($_REQUEST['dm_account_bank']) : "";
    $dm_account_number = isset($_REQUEST['dm_account_number']) ? trim($_REQUEST['dm_account_number']) : "";
    $dm_recaptcha_site_key = isset($_REQUEST['dm_recaptcha_site_key']) ? trim($_REQUEST['dm_recaptcha_site_key']) : "";
    $dm_recaptcha_secret_key = isset($_REQUEST['dm_recaptcha_secret_key']) ? trim($_REQUEST['dm_recaptcha_secret_key']) : "";
    $dm_naver_client_secret = isset($_REQUEST['dm_naver_client_secret']) ? trim($_REQUEST['dm_naver_client_secret']) : "";
    $dm_naver_client_id = isset($_REQUEST['dm_naver_client_id']) ? trim($_REQUEST['dm_naver_client_id']) : "";
    $dm_kakao_client_id = isset($_REQUEST['dm_kakao_client_id']) ? trim($_REQUEST['dm_kakao_client_id']) : "";
    $dm_kakao_client_secret = isset($_REQUEST['dm_kakao_client_secret']) ? trim($_REQUEST['dm_kakao_client_secret']) : "";
    $dm_use_naver_login = isset($_REQUEST['dm_use_naver_login']) ? trim($_REQUEST['dm_use_naver_login']) : "";
    $dm_use_kakao_login = isset($_REQUEST['dm_use_kakao_login']) ? trim($_REQUEST['dm_use_kakao_login']) : "";

    $dm_ceo_email = $dm_ceo_email1."@".$dm_ceo_email2;
    $dm_customer_email = $dm_customer_email1."@".$dm_customer_email2;

    $dm_customer_daily_start_time = $dm_customer_daily_start_hour . ":" . $dm_customer_daily_start_min;
    $dm_customer_daily_end_time = $dm_customer_daily_end_hour . ":" . $dm_customer_daily_end_min;
    $dm_customer_weekend_start_time = $dm_customer_weekend_start_hour . ":" . $dm_customer_weekend_start_min;
    $dm_customer_weekend_end_time = $dm_customer_weekend_end_hour . ":" . $dm_customer_weekend_end_min;;
    $dm_customer_break_start_time = $dm_customer_break_start_hour . ":" . $dm_customer_break_start_min;
    $dm_customer_break_end_time = $dm_customer_break_end_hour . ":" . $dm_customer_break_end_min;
    $dm_account_number = $dm_account_bank."|".$dm_account_number;

    if ($dm_company_number1 && $dm_company_number2 && $dm_company_number3) {
        $dm_company_number = $dm_company_number1."-".$dm_company_number2."-".$dm_company_number3;
    }

    $query = "SELECT * FROM dm_config WHERE dm_domain_id = '".$dm_domain_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    $query = "SELECT * FROM dm_layout WHERE dm_domain = '".$dm_domain_id."'";
    $db->ExecSql($query, "S");
    $layoutInfo = $db->Fetch();

    $file_path = $_SERVER['DOCUMENT_ROOT'].'/diam/web/thema/'.$layoutInfo['dm_top_content'].'/images';

    @mkdir($file_path, 0707);
    @chmod($file_path, 0707);

    $upload_file_name = "";
    $upload_ori_file_name = "";

    if ($dm_personal_image['name']) {
        if ($currentInfo['dm_personal_image']) {
            if (is_file($file_path.'/'.$currentInfo['dm_personal_image'])) {
                unlink($file_path.'/'.$currentInfo['dm_personal_image']);
            }
        }
        $uploadName = explode('.', $dm_personal_image['name']);
        $new_file_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
        $ori_file_name = $dm_personal_image['name'];

        if(!move_uploaded_file($dm_personal_image['tmp_name'], $file_path.'/'.$new_file_name)) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "파일업로드에 실패했습니다.", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }
    } else {
        $new_file_name = $currentInfo['dm_personal_image'];
        $ori_file_name = $currentInfo['dm_personal_image_original_name'];
    }

    if ($dm_top_logo['name']) {
        if ($currentInfo['dm_top_logo']) {
            if (is_file($file_path.'/'.$currentInfo['dm_top_logo'])) {
                unlink($file_path.'/'.$currentInfo['dm_top_logo']);
            }
        }
        $uploadName = explode('.', $dm_top_logo['name']);
        $new_top_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
        $ori_top_name = $dm_top_logo['name'];

        if(!move_uploaded_file($dm_top_logo['tmp_name'], $file_path.'/'.$new_top_name)) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "상단 로고 업로드에 실패했습니다.", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }
    } else {
        $new_top_name = $currentInfo['dm_top_logo'];
        $ori_top_name = $currentInfo['dm_top_logo_name'];
    }

    if ($dm_bottom_logo['name']) {
        if ($currentInfo['dm_bottom_logo']) {
            if (is_file($file_path.'/'.$currentInfo['dm_bottom_logo'])) {
                unlink($file_path.'/'.$currentInfo['dm_bottom_logo']);
            }
        }
        $uploadName = explode('.', $dm_bottom_logo['name']);
        $new_bottom_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
        $ori_bottom_name = $dm_bottom_logo['name'];

        if(!move_uploaded_file($dm_bottom_logo['tmp_name'], $file_path.'/'.$new_bottom_name)) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "파일업로드에 실패했습니다.", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }
    } else {
        $new_bottom_name = $currentInfo['dm_bottom_logo'];
        $ori_bottom_name = $currentInfo['dm_bottom_logo_name'];
    }

    if ($dm_del_image) {
        if (is_file($file_path.'/'.$currentInfo['dm_personal_image'])) {
            unlink($file_path.'/'.$currentInfo['dm_personal_image']);
        }
        $new_file_name = "";
        $ori_file_name = "";
    }

    if ($dm_del_top_logo) {
        if (is_file($file_path.'/'.$currentInfo['dm_top_logo'])) {
            unlink($file_path.'/'.$currentInfo['dm_top_logo']);
        }
        $new_top_name = "";
        $ori_top_name = "";
    }

    if ($dm_del_bottom_logo) {
        if (is_file($file_path.'/'.$currentInfo['dm_bottom_logo'])) {
            unlink($file_path.'/'.$currentInfo['dm_bottom_logo']);
        }
        $new_bottom_name = "";
        $ori_bottom_name = "";
    }

    if ($type == "insert") {
        $query = "
        INSERT INTO dm_config (`dm_id`,`dm_domain_id`,`dm_site_name`,`dm_title`,`dm_company_name`,`dm_company_number`,`dm_tel_company_number`,`dm_ceo`,`dm_ceo_email`,`dm_zip`,`dm_addr1`,`dm_addr2`,`dm_addr3`,`dm_jibeon`,`dm_tel`,`dm_fax`,
        `dm_meta_desc`,`dm_meta_keyword`,`dm_personal_image`,`dm_personal_image_original_name`,`dm_url`,`dm_naver_site_verification`,`dm_top_logo`,`dm_top_logo_name`,`dm_bottom_logo`,`dm_bottom_logo_name`,`dm_customer_tel`,`dm_customer_daily_use`,
        `dm_customer_daily_text`,`dm_customer_daily_start_text`,`dm_customer_daily_start_time`,`dm_customer_daily_end_text`,`dm_customer_daily_end_time`,`dm_customer_weekend_use`,`dm_customer_weekend_text`,`dm_customer_weekend_start_text`,
        `dm_customer_weekend_start_time`,`dm_customer_weekend_end_text`,`dm_customer_weekend_end_time`,`dm_customer_break_use`,`dm_customer_break_text`,`dm_customer_break_start_text`,`dm_customer_break_start_time`,`dm_customer_break_end_text`,
        `dm_customer_break_end_time`,`dm_customer_text`,`dm_customer_email`, `dm_customer_use`, `dm_account_number`, `dm_recaptcha_site_key`, `dm_recaptcha_secret_key`, `dm_use_naver_login`, `dm_naver_client_id`, `dm_naver_client_secret`,
        `dm_use_kakao_login`, `dm_kakao_client_id`, `dm_kakao_client_secret`) 
         VALUE ('".$dm_id."','".$dm_domain_id."','".$dm_site_name."','".$dm_title."','".$dm_company_name."','".$dm_company_number."','".$dm_tel_company_number."','".$dm_ceo."','".$dm_ceo_email."','".$dm_zip."','".$dm_addr1."','".$dm_addr2."',
        '".$dm_addr3."','".$dm_addr_jiboen."','".$dm_tel."','".$dm_fax."','".$dm_meta_desc."','".$dm_meta_keyword."','".$new_file_name."','".$ori_file_name."','".$dm_url."','".$dm_naver_site_verification."',
        '".$new_top_name."','".$ori_top_name."','".$new_bottom_name."','".$ori_bottom_name."','".$dm_customer_tel."','".$dm_customer_daily_use."','".$dm_customer_daily_text."','".$dm_customer_daily_start_text."',
        '".$dm_customer_daily_start_time."','".$dm_customer_daily_end_text."','".$dm_customer_daily_end_time."','".$dm_customer_weekend_use."','".$dm_customer_weekend_text."','".$dm_customer_weekend_start_text."','".$dm_customer_weekend_start_time."',
        '".$dm_customer_weekend_end_text."','".$dm_customer_weekend_end_time."','".$dm_customer_break_use."','".$dm_customer_break_text."','".$dm_customer_break_start_text."','".$dm_customer_break_start_time."','".$dm_customer_break_end_text."',
        '".$dm_customer_break_end_time."','".$dm_customer_text."','".$dm_customer_email."' ,'".$dm_customer_text_use."', '".$dm_account_number."', '".$dm_recaptcha_site_key."' , '".$dm_recaptcha_secret_key."', '".$dm_use_naver_login."',
        '".$dm_naver_client_id."', '".$dm_naver_client_secret."', '".$dm_use_kakao_login."', '".$dm_kakao_client_id."', '".$dm_kakao_client_secret."')";
    } else {
        $query = "UPDATE dm_config SET
        `dm_site_name` = '".$dm_site_name."',`dm_title` = '".$dm_title."',`dm_company_name` = '".$dm_company_name."',`dm_company_number` = '".$dm_company_number."',`dm_tel_company_number` = '".$dm_tel_company_number."',`dm_ceo` = '".$dm_ceo."',
        `dm_ceo_email` = '".$dm_ceo_email."',`dm_zip` = '".$dm_zip."',`dm_addr1` = '".$dm_addr1."',`dm_addr2` = '".$dm_addr2."',`dm_addr3` = '".$dm_addr3."',`dm_jibeon` = '".$dm_addr_jiboen."',`dm_tel` = '".$dm_tel."',`dm_fax` = '".$dm_fax."',
        `dm_meta_desc` = '".$dm_meta_desc."',`dm_meta_keyword` = '".$dm_meta_keyword."',`dm_personal_image` = '".$new_file_name."',`dm_personal_image_original_name` = '".$ori_file_name."',`dm_url` = '".$dm_url."',
        `dm_naver_site_verification` = '".$dm_naver_site_verification."',`dm_top_logo` = '".$new_top_name."',`dm_top_logo_name` = '".$ori_top_name."',`dm_bottom_logo` = '".$new_bottom_name."',`dm_bottom_logo_name` = '".$ori_bottom_name."',
        `dm_customer_tel` = '".$dm_customer_tel."',`dm_customer_daily_use` = '".$dm_customer_daily_use."',`dm_customer_daily_text` = '".$dm_customer_daily_text."',`dm_customer_daily_start_text` = '".$dm_customer_daily_start_text."',
        `dm_customer_daily_start_time` = '".$dm_customer_daily_start_time."',`dm_customer_daily_end_text` = '".$dm_customer_daily_end_text."',`dm_customer_daily_end_time` = '".$dm_customer_daily_end_time."',
        `dm_customer_weekend_use` = '".$dm_customer_weekend_use."',`dm_customer_weekend_text` = '".$dm_customer_weekend_text."',`dm_customer_weekend_start_text` = '".$dm_customer_weekend_start_text."',
        `dm_customer_weekend_start_time` = '".$dm_customer_weekend_start_time."',`dm_customer_weekend_end_text` = '".$dm_customer_weekend_end_text."',`dm_customer_weekend_end_time` = '".$dm_customer_weekend_end_time."',
        `dm_customer_break_use` = '".$dm_customer_break_use."',`dm_customer_break_text` = '".$dm_customer_break_text."',`dm_customer_break_start_text` = '".$dm_customer_break_start_text."',
        `dm_customer_break_start_time` = '".$dm_customer_break_start_time."',`dm_customer_break_end_text` = '".$dm_customer_break_end_text."',`dm_customer_break_end_time` = '".$dm_customer_break_end_time."',
        `dm_customer_text` = '".$dm_customer_text."',`dm_customer_email` = '".$dm_customer_email."',`dm_customer_use` = '".$dm_customer_text_use."', `dm_account_number` = '".$dm_account_number."',
        `dm_recaptcha_site_key` = '".$dm_recaptcha_site_key."', `dm_recaptcha_secret_key` = '".$dm_recaptcha_secret_key."', `dm_use_naver_login` = '".$dm_use_naver_login."', `dm_naver_client_id` = '".$dm_naver_client_id."',
        `dm_naver_client_secret` = '".$dm_naver_client_secret."', `dm_use_kakao_login` = '".$dm_use_kakao_login."', `dm_kakao_client_id` = '".$dm_kakao_client_id."', `dm_kakao_client_secret` = '".$dm_kakao_client_secret."'
        WHERE dm_domain_id = '".$dm_domain_id."'";

    }

    $db->ExecSql($query, "I");
    
    if (!$dm_id) $dm_id = $db->InsertId();

    $notice = '등록에 성공했습니다.';
    if ($type == 'update') {
        $notice = '수정에 성공했습니다.';
    }

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => $notice);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}
else if ($type == "page_insert") {
     $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
     $dm_private_use = isset($_REQUEST['dm_private_use']) ? trim($_REQUEST['dm_private_use']) : "";
     $dm_private_text = isset($_REQUEST['dm_private_text']) ? trim($_REQUEST['dm_private_text']) : "";
     $dm_private_name = isset($_REQUEST['dm_private_name']) ? trim($_REQUEST['dm_private_name']) : "";
     $dm_private_group = isset($_REQUEST['dm_private_group']) ? trim($_REQUEST['dm_private_group']) : "";
     $dm_private_email = isset($_REQUEST['dm_private_email']) ? trim($_REQUEST['dm_private_email']) : "";
     $dm_private_tel = isset($_REQUEST['dm_private_tel']) ? trim($_REQUEST['dm_private_tel']) : "";
     $dm_policy_text = isset($_REQUEST['dm_policy_text']) ? trim($_REQUEST['dm_policy_text']) : "";
     $dm_policy_use = isset($_REQUEST['dm_policy_use']) ? trim($_REQUEST['dm_policy_use']) : "";
     $dm_member_text = isset($_REQUEST['dm_member_text']) ? trim($_REQUEST['dm_member_text']) : "";
     $dm_no_member_text = isset($_REQUEST['dm_no_member_text']) ? trim($_REQUEST['dm_no_member_text']) : "";
     $dm_information_use = isset($_REQUEST['dm_information_use']) ? trim($_REQUEST['dm_information_use']) : "";
     $dm_information_text = isset($_REQUEST['dm_information_text']) ? trim($_REQUEST['dm_information_text']) : "";
     $dm_leave_text = isset($_REQUEST['dm_leave_text']) ? trim($_REQUEST['dm_leave_text']) : "";

     $dm_private_text = htmlspecialchars($dm_private_text, ENT_QUOTES);
     $dm_policy_text = htmlspecialchars($dm_policy_text, ENT_QUOTES);
     $dm_member_text = htmlspecialchars($dm_member_text, ENT_QUOTES);
     $dm_no_member_text = htmlspecialchars($dm_no_member_text, ENT_QUOTES);
     $dm_information_text = htmlspecialchars($dm_information_text, ENT_QUOTES);
     $dm_leave_text = htmlspecialchars($dm_leave_text, ENT_QUOTES);

     $query = "INSERT INTO `dm_config` (dm_id, dm_private_use, dm_private_text, dm_private_name, dm_private_group, dm_private_email, dm_private_tel, dm_policy_text, dm_policy_use, dm_member_text,dm_no_member_text, dm_information_use,
      dm_information_text, dm_leave_text) VALUE ('".$dm_id."', '".$dm_private_use."', '".$dm_private_text."', '".$dm_private_name."', '".$dm_private_group."', '".$dm_private_email."', '".$dm_private_tel."', '".$dm_policy_text."',
       '".$dm_policy_use."', '".$dm_member_text."', '".$dm_no_member_text."','".$dm_information_use."', '".$dm_information_text."', '".$dm_leave_text."')
       ON DUPLICATE KEY UPDATE dm_private_use = '".$dm_private_use."', dm_private_text = '".$dm_private_text."', dm_private_name = '".$dm_private_name."', dm_private_group = '".$dm_private_group."', dm_private_email = '".$dm_private_email."', 
       dm_private_tel = '".$dm_private_tel."', dm_policy_use = '".$dm_policy_use."', dm_member_text = '".$dm_member_text."', dm_no_member_text = '".$dm_no_member_text."', dm_information_use = '".$dm_information_use."',
       dm_information_text = '".$dm_information_text."',dm_leave_text = '".$dm_leave_text."', dm_policy_text = '".$dm_policy_text."'";

     $db->ExecSql($query, "I");

     if (!$dm_id) $dm_id = $db->InsertId();

     $notice = '등록에 성공했습니다.';

     $arResult = array("result" => "success", "_return" => $dm_id, "notice" => $notice);

     echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}