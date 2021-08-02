<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-05-21
 * Time: 오후 4:29
 */

include ("./member.php");

function fn_imagejpeg($image,$upload_file,$new_file_width,$new_file_height,$width, $height,$new_quality) {
    $tmpCreation = imagecreatetruecolor($new_file_width, $new_file_height);
    imagecopyresampled($tmpCreation, $image, 0, 0, 0, 0, $new_file_width, $new_file_height, $width, $height);
    imagejpeg($tmpCreation, $upload_file, $new_quality);
    // 원본 이미지 리소스 종료
    imagedestroy($tmpCreation);
}


if ($command == 'join' || $command == 'update')
{

    if (!$dm_name)
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "이름을 입력해주세요", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_hp)
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "휴대폰번호를 입력해주세요", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_email)
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "이메일을 입력해주세요", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_nick)
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "닉네임을 입력해주세요", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if (!$dm_sex)
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "성별을 선택해주세요", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    //휴대폰 번호 검사
    $hp = preg_replace("/[^0-9]/", "", $dm_hp);
    if(!preg_match("/^01[0-9]{8,9}$/", $hp))
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "휴대폰번호를 정상적으로 입력해주세요", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    // 이메일
    $email= $dm_email."@".$dm_email1;

    if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email))
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "올바르지 않은 이메일 형식입니다.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $dm_id)) {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디는 영문 또는 숫자만 가능합니다.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    if ($command == 'join')
    {
        // validate
        if (!$dm_id)
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디를 입력해주세요", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        if (!$dm_password)
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호를 입력해주세요", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        if (!$dm_password_confirm)
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호 확인을 입력해주세요", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        // 아이디 중복 검사
        $query = "SELECT * FROM $member_table WHERE dm_id = '".$dm_id."'";

        $db->ExecSql($query, "S");

        if ($db->Num > 0) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디가 중복됩니다.", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        //비밀번호 같은지 검사
        if ($dm_password != $dm_password_confirm)
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호가 다릅니다.", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        //비밀번호 검사
        $result = passwordCheck($dm_password);
        if ($result[0] == false)
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $result[1], "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        $dm_password = sql_password($dm_password);

        $mAgent = array("iPhone","iPod","Android","Blackberry", "Opera Mini", "Windows ce", "Nokia", "sony" );
        $chkMobile = false;
        for($i=0; $i<sizeof($mAgent); $i++){
            if(stripos( $_SERVER['HTTP_USER_AGENT'], $mAgent[$i] )){
                $chkMobile = true;
                break;
            }
        }

        if ($chkMobile) {
            $is_mobile = 1;
        }

        @mkdir($_VAR_PATH_WEB_DATA.'file/member/', 0707);
        @chmod($_VAR_PATH_WEB_DATA.'file/member', 0707);
        $dm_4 = "";
        $dm_5 = "";

        $file_path = $_VAR_PATH_WEB_DATA.'file/member/';
        $join_type = getSession("join_type");

        if ($join_type == 2 || $join_type == 3) {
            if ($_FILES['upload']['error'] > 0) {
                switch ($_FILES['upload']['error']) {
                    case 1 :
                        $notice = "업로드 최대 용량을 초과하였습니다.";
                        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                        echo json_encode($arResult);
                        exit;
                    break;

                    case 2 :
                        $notice = "업로드 최대 용량을 초과하였습니다.";
                        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                        echo json_encode($arResult);
                        exit;
                    break;
                }
            }

            if ($_FILES['upload']['size'] > 2097152) {
                $notice = "파일 용량은 2MB 이하로 업로드 해주세요";
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            }

            $tmp = explode(".", $_FILES['dm_2']['name']);
            $ext = end($tmp);
            $ext = strtolower($ext);
            $haystack = array('jpg', 'jpeg', 'png', 'git', 'bmp');

            if (!in_array($ext, $haystack)) {
                $notice = "이미지 파일이 아니거나 첨부하지 않았습니다.";
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            }
        }

        if ($_FILES['dm_2']['name']) {
            $uploadName = explode('.', $_FILES['dm_2']['name']);
            $new_file_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
            $ori_file_name = $_FILES['dm_2']['name'];
            $dm_4 = $new_file_name;
            $dm_5 = $ori_file_name;

            $file_source = $_FILES['dm_2']['tmp_name']; //파일명
            $file_info = getimagesize($_FILES['dm_2']['tmp_name']);
            $file_width	= $file_info[0]; //이미지 가로 사이즈
            $file_height = $file_info[1]; //이미지 세로 사이즈
            $file_type	= $_FILES['dm_2']['type'];
            $error = $_FILES['dm_2']['error'];

            $new_file_width 	= 700; //이미지 가로 사이즈 지정
            $rate			= $new_file_width/$file_width; //이미지 세로 사이즈 및 파일 사이즈(quality) 조절을 위한 비율
            $new_file_height	= (int)($file_height*$rate);
            $new_quality	= (int)($_FILES['dm_2']['size']*$rate);
            if ($file_width > $new_file_width	){ //이미지 가로사이즈가 550보다 크면 사이즈 조절
                switch($file_type){
                    case "image/jpeg" :
                        $image = imagecreatefromjpeg($file_source);
                        break;

                    case "image/gif" :
                        $image = imagecreatefromgif($file_source);
                        break;

                    case "image/png" :
                        $image = imagecreatefrompng($file_source);
                        break;

                    default:
                        $image = "";
                        break;
                }
                if ($image != ""){
                    fn_imagejpeg($image, $file_path.$new_file_name,$new_file_width, $new_file_height,$file_width,$file_height,$new_quality);
                }
            }else{
                if(!move_uploaded_file($_FILES['dm_2']['tmp_name'], $file_path.$new_file_name)) {
                    $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "파일업로드에 실패했습니다.", "objName" => $_POST['objName'] );
                    echo json_encode( $arResult );
                    exit;
                }
            }
        }

        $query = "INSERT INTO $member_table (`dm_id`, `dm_password`, `dm_name`, `dm_nick` ,`dm_email`, `dm_level`, `dm_sex`, `dm_birth` ,`dm_hp`, `dm_sms`, `dm_join_os`, `dm_ip`, `dm_datetime`, `dm_3`, `dm_4`, `dm_5`, `dm_group_id`)
        VALUE ('".$dm_id."', '".$dm_password."', '".$dm_name."', '".$dm_nick."', '".$email."' , '1' , '".$dm_sex."', '".$dm_birth."',  '".$dm_hp."', '".$dm_sms."', '".$is_mobile."', '".$_SERVER['REMOTE_ADDR']."', now(), '".$join_type."', '".$dm_4."', '".$dm_5."', 'GROUP_0000000001')";

        if ($join_type == 1) {
            $notice = "회원가입이 완료 되었습니다.";
        } else if ($join_type == 2) {
            $notice = "현재는 일반회원으로 가입되었습니다. \n\n관리자 승인 후 기업회원으로 자동으로 변경됩니다.";
        } else if ($join_type == 3) {
            $notice = "현재는 일반회원으로 가입되었습니다. \n\n관리자 승인 후 전문가회원으로 자동으로 변경됩니다.";
        }
    }

    else if ($command == 'update')
    {
        if ($dm_password)
        {
            //비밀번호 같은지 검사
            if ($dm_password != $dm_password_confirm)
            {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호가 다릅니다.", "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            }

            //비밀번호 검사
            $result = passwordCheck($dm_password);
            if ($result[0] == false)
            {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $result[1], "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            }

            $dm_password = sql_password($dm_password);

            $query_option = ", `dm_password` = '".$dm_password."'";
        }

        @mkdir($_VAR_PATH_WEB_DATA.'file/member/', 0707);
        @chmod($_VAR_PATH_WEB_DATA.'file/member', 0707);
        $dm_4 = "";
        $dm_5 = "";

        $file_path = $_VAR_PATH_WEB_DATA.'file/member/';
        $join_type = $MEMBER['dm_3'];

        if ($join_type == 2 || $join_type == 3) {
            if ($_FILES['upload']['error'] > 0) {
                switch ($_FILES['upload']['error']) {
                    case 1 :
                        $notice = "업로드 최대 용량을 초과하였습니다.";
                        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                        echo json_encode($arResult);
                        exit;
                        break;

                    case 2 :
                        $notice = "업로드 최대 용량을 초과하였습니다.";
                        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                        echo json_encode($arResult);
                        exit;
                        break;
                }
            }

            if ($_FILES['upload']['size'] > 2097152) {
                $notice = "파일 용량은 2MB 이하로 업로드 해주세요";
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            }
        }

        if(isset($_REQUEST['del_image']) && $_REQUEST['del_image']) {
            if (is_file($file_path.$MEMBER['dm_4'])) {
                unlink($file_path.$MEMBER['dm_4']);
            }
        }

        if ($_FILES['dm_2']['name']) {
            $tmp = explode(".", $_FILES['dm_2']['name']);
            $ext = end($tmp);
            $ext = strtolower($ext);
            $haystack = array('jpg', 'jpeg', 'png', 'git', 'bmp');

            if (!in_array($ext, $haystack)) {
                $notice = "이미지 파일만 업로드 가능합니다.";
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            }

            $uploadName = explode('.', $_FILES['dm_2']['name']);
            $new_file_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
            $ori_file_name = $_FILES['dm_2']['name'];
            $dm_4 = $new_file_name;
            $dm_5 = $ori_file_name;

            $file_source = $_FILES['dm_2']['tmp_name']; //파일명
            $file_info = getimagesize($_FILES['dm_2']['tmp_name']);
            $file_width	= $file_info[0]; //이미지 가로 사이즈
            $file_height = $file_info[1]; //이미지 세로 사이즈
            $file_type	= $_FILES['dm_2']['type'];
            $error = $_FILES['dm_2']['error'];

            $new_file_width 	= 700; //이미지 가로 사이즈 지정
            $rate			= $new_file_width/$file_width; //이미지 세로 사이즈 및 파일 사이즈(quality) 조절을 위한 비율
            $new_file_height	= (int)($file_height*$rate);
            $new_quality	= (int)($_FILES['dm_2']['size']*$rate);
            if ($file_width > $new_file_width	){ //이미지 가로사이즈가 550보다 크면 사이즈 조절
                switch($file_type){
                    case "image/jpeg" :
                        $image = imagecreatefromjpeg($file_source);
                        break;

                    case "image/gif" :
                        $image = imagecreatefromgif($file_source);
                        break;

                    case "image/png" :
                        $image = imagecreatefrompng($file_source);
                        break;

                    default:
                        $image = "";
                        break;
                }
                if ($image != ""){
                    fn_imagejpeg($image, $file_path.$new_file_name,$new_file_width, $new_file_height,$file_width,$file_height,$new_quality);
                }
            }else{
                if(!move_uploaded_file($_FILES['dm_2']['tmp_name'], $file_path.$new_file_name)) {
                    $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "파일업로드에 실패했습니다.", "objName" => $_POST['objName'] );
                    echo json_encode( $arResult );
                    exit;
                }
            }
        } else {
            $dm_4 = $MEMBER['dm_4'];
            $dm_5 = $MEMBER['dm_5'];
        }

        $query = "UPDATE $member_table SET `dm_name` = '".$dm_name."', `dm_email` = '".$email."', `dm_sex` = '".$dm_sex."', `dm_hp` = '".$dm_hp."',
        dm_birth = '".$dm_birth."', dm_nick = '".$dm_nick."', dm_4 = '".$dm_4."', dm_5 = '".$dm_5."'
        $query_option
        WHERE `dm_id` = '".$dm_id."'";

        $notice = "회원정보 수정 완료";

    }

    $db->ExecSql($query, "I");

    if ($db->Num > 0)
    {
        setSession("join_type", "");
        $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }
    else
    {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "회원정보 저장 중 오류", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

}

else if ($command == 'check_id')
{
    if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $dm_id)) {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "아이디는 영문 또는 숫자만 가능합니다.", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

    $query = "SELECT * FROM $member_table WHERE dm_id = '".$dm_id."'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $arResult = array( "result" => "dup","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }
    else {
        $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

}

else if ($command == 'check_nick')
{
    $query = "SELECT * FROM $member_table WHERE dm_nick = '".$dm_nick."'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $arResult = array( "result" => "dup","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }
    else {
        $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "", "objName" => $_POST['objName'] );
        echo json_encode($arResult);
        exit;
    }

}

else if ($command == 'setClosePop') {
    setCloseLevelupPop();
}

else if ($command == 'leave_member') {
    $query = "UPDATE dm_member SET dm_leave_date = '".date("Ymd")."' WHERE dm_id = '".getSession("chk_dm_id")."'";
    $db->ExecSql($query, "U");
    setSession("chk_dm_level", "");
    setSession("chk_dm_name", "");
    setSession("chk_dm_id", "");
    setSession("is_member", false);
    setSession("is_admin", false);
    session_destroy();

    alert ("정상적으로 탈퇴처리 되었습니다. 이용해주셔서 감사합니다.", "/diam/web");
}