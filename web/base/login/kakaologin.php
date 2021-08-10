<script>
document.domain = "http://192.168.0.48/";
</script>
<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-02-03
 * Time: 오전 11:09
 */

include('../../lib/lib.php');

$code = trim($_GET["code"]);
$state = trim($_GET["state"]);


if(isset($_GET['code']) && isset($_GET["state"]))
{
    /*if(getSession("KakaoState") != $state)
    {
        //alert_close("토큰이 잘못됐습니다.kakaoState=".getSession("KakaoState")."/state=".$state);
		alert_close("토큰이 잘못됐습니다.");
        exit;
    }

    sessionUnset("KakaoState");*/

    //-- 접근 토큰 발급 요청
    $txtUrl = "";
    $txtUrl .= "https://kauth.kakao.com/oauth/token?";
    $txtUrl .= "client_id=".$KAKAO_KEY;
//    $txtUrl .= "&client_secret=jXtO3BPZye";
    $txtUrl .= "&redirect_uri=".$KAKAO_RETURN_URL;
    $txtUrl .= "&grant_type=authorization_code";
    $txtUrl .= "&state=".$state;
    $txtUrl .= "&code=".$code;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $txtUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt($ch, CURLOPT_COOKIE, '' );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);

    if (!$response)
    {
        alert_close("결과가 없습니다.");
        exit;
    }

    //-- json 형식으로 접근 토큰이 넘어 옴.
    @session_start();
    $_SESSION['kakao_access_token'] = json_decode($response, TRUE);

    //- access token 생성 시간을 첨부해줍니다. 유효 기간 판정할 때 사용합니다. */
    $_SESSION['kakao_access_token']['created'] = time();

    $access_token = $_SESSION['kakao_access_token']['access_token'];
    unset($_SESSION['kakao_access_token']);

    $header = "Bearer ".$access_token; // Bearer 다음에 공백 추가

    $url = "https://kapi.kakao.com/v2/user/me";
    $is_post = false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt($ch, CURLOPT_COOKIE, '' );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = array();
    $headers[] = "Authorization: ".$header;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);
    if($status_code == 200)
    {
        //로그인 처리
        $response = json_decode($response, TRUE);

        $code = 'login';
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = $_SERVER['HTTP_REFERER'];
        $agent = $_SERVER["HTTP_USER_AGENT"];

        $sns_id = $response['id'];
        $dm_name = $response['properties']['nickname'];
        $sns_nick = $response['properties']['nickname'];
        $sns_gender = $response['kakao_account']['gender'];
        $dm_email = $response['kakao_account']['email'];
        $dm_birth = $response['kakao_account']['birthday']; //TODO : 연도 추가
        $dm_gender = ($response['kakao_account']['gender'] == "male") ? "M" : "F";
        $sns_mobile = "010-1234-1234"; //TODO 비즈앱으로 바꾼 뒤 핸드폰번호 추가

        $query = "SELECT * FROM dm_sns_info WHERE sns_id = '".$sns_id."'";
        $db->ExecSql($query, "S");
        if ($db->Num > 0) {
            $sns = $db->Fetch();

            $query = "SELECT * FROM dm_member WHERE dm_id = '".$sns['mb_id']."'";
            $db->ExecSql($query, "S");
            $member = $db->Fetch();

            if (!$member['dm_leave_date'] && !$member['dm_intercept_date']) {
                setSession("chk_dm_level", $member['dm_level']);
                setSession("chk_dm_name", $member['dm_name']);
                setSession("chk_dm_id", $member['dm_id']);
                setSession("is_member", true);
                if ($member['dm_level'] == 10) {
                    setSession("is_admin", true);
                } else {
                    setSession("is_admin", false);
                }

                $result = '1';
                insert_log($member['dm_id'], $ip, $code, $result, $url, $agent);

                $visit_query = "";
                if (date("Y-m-d", strtotime($member['dm_today_login'])) < date("Y-m-d")) {
                    setExpCount($member['dm_id'], 'attend');
                    $visit_query = ", dm_visit_count = dm_visit_count + 1";
                }

                $query = "UPDATE dm_member SET dm_today_login = now() $visit_query WHERE dm_id ='".$member['dm_id']."'";
                $db->ExecSql($query, "I");


                echo "<script>";
                echo "opener.document.location.href='/diam/web';";
                echo "self.close();";
                echo "</script>";

            } else {
                $result = '0';
                insert_log($member['dm_id'], $ip, $code, $result, $url, $agent);

                alert ("사용할 수 없는 계정입니다.");
                echo "<script>";
                echo "self.close();";
                echo "</script>";
                exit;
            }
        } else {
            //닉네임 중복 체크
            $query = "SELECT count(*) as a FROM dm_member WHERE dm_nick = '".$sns_nick."'";
            $db->ExecSql($query, "S");
            $is_nick = $db->Fetch();

            if ($is_nick['a'] > 0) {
                $sns_nick = $sns_nick."_".($is_nick['a']+1);
            }

            //아이디 중복 체크
            $query = "SELECT count(*) as a FROM dm_member WHERE dm_id = '".$sns_id."'";
            $db->ExecSql($query, "S");
            $is_id = $db->Fetch();

            if ($is_id['a'] > 0) {
                $mb_id = $sns_id."_".($is_id['a']+1);
            } else {
                $mb_id = $sns_id;
            }

            $query = "SELECT * FROM dm_sns_info WHERE sns_id = '".$sns_id."'";
            $db->ExecSql($query, "S");
            $is_sns = $db->Fetch();

            if (!$is_sns) {
                $query = "INSERT INTO dm_sns_info (`sns_id`, `mb_id` , `sns_type`, `sns_name`, `sns_connect_date`) VALUE ('".$sns_id."', '".$mb_id."', 'kakao', '카카오', now())";
                $db->ExecSql($query, "I");
            }

            $rand_pw = mt_rand(30000, 50000);
            $password = sql_password($rand_pw);
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

            $query = "INSERT INTO dm_member (`dm_id`, `dm_password`, `dm_name`, `dm_nick` ,`dm_email`, `dm_level`, `dm_sex`, `dm_birth` ,`dm_hp`, `dm_join_os`, `dm_ip`, `dm_datetime`, `dm_group_id`, `dm_3`, `dm_today_login`)
        VALUE ('".$mb_id."', '".$password."', '".$dm_name."', '".$sns_nick."', '".$dm_email."' , '1' , '".$dm_gender."', '".$dm_birth."',  '".$sns_mobile."', '".$is_mobile."', '".$_SERVER['REMOTE_ADDR']."', now(), 'GROUP_0000000001', '1', now())";

            $db->ExecSql($query, "I");
            setSession("chk_dm_level", 1);
            setSession("chk_dm_name", $dm_name);
            setSession("chk_dm_id", $mb_id);
            setSession("is_member", true);

            $result = '1';
            insert_log($mb_id, $ip, $code, $result, $url, $agent);

            echo "<script>";
            echo "opener.document.location.href='/diam/web';";
            echo "self.close();";
            echo "</script>";


        }
    }
}
