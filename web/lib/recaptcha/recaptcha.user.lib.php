<?php

// 캡챠 HTML 코드 출력
function captcha_html($class="captcha")
{

    global $CONFIG, $_VAR_PATH_WEB_LIB;

    /*
    #hl=ko 표시는 언어지정가능
    */
    $html = '<fieldset id="captcha" class="captcha recaptcha">';
    $html .= '<script src="https://www.google.com/recaptcha/api.js?hl=ko"></script>';
    $html .= '<script src="'.$_VAR_PATH_WEB_LIB.'/recaptcha/recaptcha.js"></script>';
    $html .= '<div class="g-recaptcha" data-sitekey="'.$CONFIG['dm_recaptcha_site_key'].'"></div>';
    $html .= '</fieldset>';

	return $html;
}

// 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함
function chk_captcha_js()
{
	return "if (!chk_captcha()) return false;\n";
}

function chk_captcha(){

    global $CONFIG;

    $resp = null;

    if ( isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]) ) {

        $reCaptcha = new ReCaptcha_GNU( $CONFIG['dm_recaptcha_secret_key'] );

        $resp = $reCaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
    }

    if( ! $resp ){
        return false;
    }

    if ($resp != null && $resp->success) {
        return true;
    }

    return false;
}
?>