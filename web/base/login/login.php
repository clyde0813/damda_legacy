<?php

include('../../lib/lib.php');
$contentId =  isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";
$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "login";


if ($command == 'login') {
    $dm_id = isset($_POST['dm_mb_id']) ? trim($_POST['dm_mb_id']) : "";
    $dm_pw = isset($_POST['dm_mb_password']) ? trim($_POST['dm_mb_password']) : "";
    $code = 'login';
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = $_SERVER['HTTP_REFERER'];
    $agent = $_SERVER["HTTP_USER_AGENT"];

    if (!$dm_id) {
        $result = '0';
        insert_log($dm_id, $ip, $code, $result, $url, $agent);

        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디를 입력해주세요.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_pw) {
        $result = '0';
        insert_log($dm_id, $ip, $code, $result, $url, $agent);

        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호를 입력해주세요.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    $query = "SELECT * FROM dm_member WHERE dm_id ='".$dm_id."' AND dm_password = md5('".$dm_pw."')  LIMIT 1";

    $db->ExecSql($query, "S");

    if ($db-> Num > 0) {
        $member = $db->Fetch();

        if (!$member['dm_leave_date'] && !$member['dm_intercept_date']) {
            setSession("chk_dm_level", $member['dm_level']);
            setSession("chk_dm_name", $member['dm_name']);
            setSession("chk_dm_id", $member['dm_id']);
            setSession("is_member", true);
            if ($member['dm_level'] >= 6) {
                setSession("is_admin", true);
            } else {
                setSession("is_admin", false);
            }

            $result = '1';
            insert_log($dm_id, $ip, $code, $result, $url, $agent);

            $visit_query = "";
            if (date("Y-m-d", strtotime($member['dm_today_login'])) < date("Y-m-d")) {
                setExpCount($member['dm_id'], 'attend');
                $visit_query = ", dm_visit_count = dm_visit_count + 1";
            }

            $query = "UPDATE dm_member SET dm_today_login = now() $visit_query WHERE dm_id ='".$dm_id."'";
            $db->ExecSql($query, "I");

            $saveId = isset($_REQUEST['saveId']) ? $_REQUEST['saveId'] : "";
            $autoLogin = isset($_REQUEST['autoLogin']) ? $_REQUEST['autoLogin'] : "";

            if ($saveId) {
                setcookie("userId", $member['dm_id'], time()+(86400*30), "/");
            } else {
                setcookie("userId", $member['dm_id'], time()-(86400*30), "/");
                unset($_COOKIE['userId']);
            }

            $key = "diam!@#";
            $hash_data = md5($member['dm_password'].$key);
            
            if ($autoLogin) {
                setcookie("autoLogin", "1", time()+(86400*30), "/");
                setcookie("userId", $member['dm_id'], time()+(86400*30), "/");
                setcookie("userHash", $hash_data, time()+(86400*30), "/");
            } else {
                setcookie("autoLogin", "", time()-(86400*30), "/");
                setcookie("userHash", "", time()-(86400*30), "/");
            }

            $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        } else {
            $result = '0';
            insert_log($dm_id, $ip, $code, $result, $url, $agent);

            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "사용할 수 없는 계정입니다.", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }


    } else {
        $result = '0';
        insert_log($dm_id, $ip, $code, $result, $url, $agent);

        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디 또는 비밀번호가 맞지 않습니다.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }
} else if ($command == 'logout') {
    $mb_level = getSession('chk_dm_level');
    setSession("chk_dm_level", "");
    setSession("chk_dm_name", "");
    setSession("chk_dm_id", "");
    setSession("is_member", "");

    if ($mb_level >= 6) {
        setSession("is_admin", "");
    }

    session_destroy();

    setcookie("autoLogin", "", time()-(86400*30), "/");
    setcookie("userHash", "", time()-(86400*30), "/");

    $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디 또는 비밀번호가 맞지 않습니다.", "objName" => $_POST['objName'] );
    echo json_encode($arResult);
    exit;
}

else if ($command == 'lost_id' || $command == 'lost_pw')
{
    $dm_name = isset($_POST['dm_name']) ? trim($_POST['dm_name']) : "";
    $dm_email = isset($_POST['dm_email']) ? trim($_POST['dm_email']) : "";
    $dm_id = isset($_POST['dm_id']) ? trim($_POST['dm_id']) : "";
    $dm_email1 = isset($_POST['dm_email1']) ? trim($_POST['dm_email1']) : "";

    if ($command == 'lost_id')
    {
        $query = "SELECT * FROM dm_member WHERE `dm_name` = '".$dm_name."' AND dm_email = '".$dm_email."'";
        $db->ExecSql($query, "S");

        if ($db->Num >0)
        {
            $memberInfo = $db->Fetch();

            $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $memberInfo['dm_id'], "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }
        else
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "정보가 존재하지 않습니다.", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }
    }

    if ($command == 'lost_pw')
    {
        $query = "SELECT * FROM dm_member WHERE `dm_id` = '".$dm_id."' AND dm_email = '".$dm_email1."'";
        $db->ExecSql($query, "S");

        if ($db->Num >0)
        {
            $memberInfo = $db->Fetch();
            $rand_pw = mt_rand(30000, 50000);

            $password = sql_password($rand_pw);

            $query = "UPDATE dm_member SET dm_password = '".$password."' WHERE `dm_id` = '".$dm_id."'";

            $db->ExecSql($query, "I");

            $query = "SELECT * FROM dm_mail_config";

            $db->ExecSql($query, "S");

            $mailConfig = $db->Fetch();

            if ($mailConfig['dm_use_password_mail'] == 'y') {
                $subject = $mailConfig['dm_password_subject'];
                $content = $mailConfig['dm_password_content'];

                $subject = str_replace("{사이트명}", $CONFIG['dm_company_name'], $subject);

                $CONFIG['dm_top_logo_url'] = $_VAR_URL_WEB_THEMA.$LAYOUT_VAL["dm_top_content"].'/images/'.$CONFIG['dm_top_logo'];

                $logo = "<img src='".$CONFIG['dm_top_logo_url']."' alt='로고'/>";

//            $content = str_replace("{로고}", $logo, $content);
                $content = str_replace("{로고}", $CONFIG['dm_company_name'], $content);
                $content = str_replace("{이름}", $CONFIG['dm_company_name'], $content);
                $content = str_replace("{새비밀번호}", $rand_pw, $content);
                $content = str_replace("{홈페이지 이름}", $CONFIG['dm_company_name'], $content);
                $admin_email = $mailConfig['dm_password_email'];
            } else {
                $subject = $CONFIG['dm_company_name']."임시 비밀번호 요청입니다.";
                $content .= "<p>회원명 : <strong>".$memberInfo['dm_name']."</strong></p>";
                $content .= "<p>임시 비밀번호 : <strong style='color:red'>".$rand_pw."</strong></p>";
                $content .= "<p>위 비밀번호로 로그인 후 반드시 새로운 비밀번호로 변경하시기 바랍니다.";
            }

            include ($_VAR_PATH_WEB_LIB."mailer.php");

            $res = mailer($subject, $admin_email, $memberInfo['dm_email'], $subject, $content, 1);

            $content = addslashes($content);

            $query = "INSERT INTO dm_mail_log (dm_subject, dm_from_email, dm_to_email, dm_content, dm_send_agree, dm_use_deny, dm_datetime, dm_id, dm_ip, dm_mail_type, dm_to_id)
    VALUE ('".$subject."', '".$admin_email."', '".$memberInfo['dm_email']."', '".$content."', 'n', 'n', now(), 'master', '".$_SERVER['REMOTE_ADDR']."', 'user', '".$memberInfo['dm_id']."') ";
            $db->ExecSql($query, "I");

            $arResult = array( "result" => "success","_return" => "","total" => "" , "rows" => "", "notice" => "", "objName" => "" );
            echo json_encode($arResult);
            exit;
        }
        else
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "정보가 존재하지 않습니다.", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }
    }

}
