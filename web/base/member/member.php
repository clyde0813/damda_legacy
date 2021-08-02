<?php

include('../../lib/lib.php');

$contentId =  isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";
$member_table = "dm_member";
$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "join";

$dm_no =  isset($_REQUEST['dm_no']) ? trim($_REQUEST['dm_no']) : "";
$dm_id =  isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
$dm_password =  isset($_REQUEST['dm_password']) ? trim($_REQUEST['dm_password']) : "";
$dm_password_confirm =  isset($_REQUEST['dm_password_confirm']) ? trim($_REQUEST['dm_password_confirm']) : "";
$dm_name =  isset($_REQUEST['dm_name']) ? trim($_REQUEST['dm_name']) : "";
$dm_hp =  isset($_REQUEST['dm_hp']) ? trim($_REQUEST['dm_hp']) : "";
$dm_email =  isset($_REQUEST['dm_email']) ? trim($_REQUEST['dm_email']) : "";
$dm_email1 =  isset($_REQUEST['dm_email1']) ? trim($_REQUEST['dm_email1']) : "";
$dm_sms =  isset($_REQUEST['dm_sms']) ? trim($_REQUEST['dm_sms']) : "";
$dm_sex =  isset($_REQUEST['dm_sex']) ? trim($_REQUEST['dm_sex']) : "";
$dm_nick =  isset($_REQUEST['dm_nick']) ? trim($_REQUEST['dm_nick']) : "";
$dm_birth =  isset($_REQUEST['dm_birth']) ? trim($_REQUEST['dm_birth']) : "";

function passwordCheck($_str)
{
    $pw = $_str;
    $num = preg_match('/[0-9]/u', $pw);
    $eng = preg_match('/[a-z]/u', $pw);
    $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$pw);

    if(strlen($pw) < 8 || strlen($pw) > 30)
    {
        return array(false,"비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 8자리 ~ 최대 30자리 이내로 입력해주세요.");
        exit;
    }

    if(preg_match("/\s/u", $pw) == true)
    {
        return array(false, "비밀번호는 공백없이 입력해주세요.");
        exit;
    }

    if( $num == 0 || $eng == 0 || $spe == 0)
    {
        return array(false, "영문, 숫자, 특수문자를 혼합하여 입력해주세요.");
        exit;
    }

    return array(true);
}

if ($command == 'modify_form')
{
    $query = "SELECT * FROM $member_table WHERE `dm_id` = '".getSession("chk_dm_id")."'";
    $db->ExecSql($query, "S");

    if ($db->Num > 0)
    {
        $memberInfo = $db->Fetch();
    }

    $dm_email = explode("@", $memberInfo['dm_email']);
    $dm_birth = explode("-", $memberInfo['dm_birth']);
    $dm_hp = explode("-", $memberInfo['dm_hp']);

    $memberInfo['dm_email1'] = $dm_email[0];
    $memberInfo['dm_email2'] = $dm_email[1];
    $memberInfo['dm_birth1'] = $dm_birth[0];
    $memberInfo['dm_birth2'] = $dm_birth[1];
    $memberInfo['dm_birth3'] = $dm_birth[2];
    $memberInfo['dm_hp1'] = $dm_hp[0];
    $memberInfo['dm_hp2'] = $dm_hp[1];
    $memberInfo['dm_hp3'] = $dm_hp[2];

    if ($memberInfo['dm_3'] == 2 || $memberInfo['dm_3'] == 3) {
        if ($memberInfo['dm_4']) {
            $file_path = $_VAR_PATH_WEB_DATA.'file/member/';
            if (is_file($file_path.$memberInfo['dm_4'])) {
                $memberInfo['dm_file_url'] = $_VAR_URL_WEB_DATA.'file/member/'.$memberInfo['dm_4'];
                $is_image=true;
            }
        }
    }

}

else if ($command == 'set_member_type') {
    $member_type = isset($_POST['member_type']) ? $_POST['member_type'] : "";
    if (!$member_type) {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    setSession("join_type", $member_type);

    $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다.", "objName" => $_POST['objName'] );
    echo json_encode($arResult);
}