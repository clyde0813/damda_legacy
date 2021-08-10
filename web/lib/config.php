<?php
/**
 * Created by PhpStorm.
 * User: 김무영
 * Date: 2020-05-19
 * Time: 오전 9:39
 * 용도 : 변수 정의
 */

// 현재 날짜
//$ytime  = mktime (0,0,0,date("m")  , date("d"), date("Y"));
$ytime  = time();
$ntime  = time();
$CurDate = date("Ymd",$ytime);
$CurMonth = date("Ym",$ytime);

$prevDate = date("Ymd", strtotime("-1 day"));

$prevDate30 = date("Ymd", strtotime("-30 day"));

//$wDate    = strtotime('-7days');
$wDate  = date("Ymd",strtotime("-7 day"));
//$wDate = date("Ymd",$wDate);

$cGDate = date("Y-m-d",$ytime);
$wGDate = date("Y-m-d",strtotime("-10 day"));

$CurDateTime = date("YmdHis",$ntime);
$Today = date("Y_m_d_His",$ntime);
$TodayTime = date("Y-m-d H:i:s",$ntime);
$aTime = date("Y.m.d H:i:s",$ntime);
$CurTime = date("H",$ntime);
$CurMit = date("i",$ntime);
$CurATime = date("His",$ntime);

$CurDate2 = date("Y-m-d",$ytime);

$_TITLE = "[디자인아이엠] CMS";
$_THEMES = "gray";

$_VAR_PATH_ROOT = $_SERVER['DOCUMENT_ROOT']."/diam/"; //루트경로
$_VAR_URL_ROOT = "http://".$_SERVER['HTTP_HOST']."/diam/"; //루트경로

//CMS 관련 전역변수
// -- 경로 --
$_VAR_PATH_CMS = $_VAR_PATH_ROOT."cms/console/"; //CMS루트 경로
$_VAR_PATH_JS = $_VAR_PATH_CMS."js/"; //JS
$_VAR_PATH_LIB = $_VAR_PATH_CMS."lib/"; //LIB
$_VAR_PATH_BIZ = $_VAR_PATH_CMS."biz/"; //BIZ
$_VAR_PATH_UI = $_VAR_PATH_CMS."ui/";  //UI
$_VAR_PATH_IMG = $_VAR_PATH_CMS."img/"; //IMAGES
$_VAR_PATH_CSS = $_VAR_PATH_CMS."css/"; //CSS

// -- URL --
$_VAR_URL_CMS = $_VAR_URL_ROOT."cms/console/"; //CMS루트 경로
$_VAR_URL_JS = $_VAR_URL_CMS."js/"; //JS
$_VAR_URL_LIB = $_VAR_URL_CMS."lib/"; //LIB
$_VAR_URL_BIZ = $_VAR_URL_CMS."biz/"; //BIZ
$_VAR_URL_UI = $_VAR_URL_CMS."ui/";  //UI
$_VAR_URL_IMG = $_VAR_URL_CMS."img/"; //IMAGES
$_VAR_URL_CSS = $_VAR_URL_CMS."css/"; //CSS

//WEB 관련 전역변수
// -- 경로 --
$_VAR_PATH_WEB = $_VAR_PATH_ROOT."web/"; //WEB루트 경로
$_VAR_PATH_WEB_BASE = $_VAR_PATH_WEB."base/"; //BASE 경로
$_VAR_PATH_WEB_DATA = $_VAR_PATH_WEB."data/"; //DATA 경로
$_VAR_PATH_WEB_LIB = $_VAR_PATH_WEB."lib/"; //LIB 경로
$_VAR_PATH_WEB_MENU = $_VAR_PATH_WEB."menu/"; //MENU 경로
$_VAR_PATH_WEB_TEMP = $_VAR_PATH_WEB."temp/"; //TEMP 경로
$_VAR_PATH_WEB_THEMA = $_VAR_PATH_WEB."thema/"; //THEMA 경로
$_VAR_PATH_WEB_UI = $_VAR_PATH_WEB."ui/"; //UI 경로
$_VAR_PATH_WEB_PAGE = $_VAR_PATH_WEB_UI."pages/"; //UI 경로
$_VAR_PATH_WEB_BOARD = $_VAR_PATH_WEB_BASE."board/";
$_VAR_PATH_WEB_BOARD_MOBILE = $_VAR_PATH_WEB_BASE."board/";
$_VAR_PATH_CERTIFICATE_IMAGE = $_VAR_PATH_WEB_DATA."certificate/";
$_VAR_PATH_WEB_LOGIN = $_VAR_PATH_WEB_BASE."login/";
$_VAR_PATH_WEB_MEMBER = $_VAR_PATH_WEB_BASE."member/";
$_VAR_PATH_WEB_LASTEST = $_VAR_PATH_WEB_BASE."latest/";
$_VAR_PATH_WEB_CERTIFICATE = $_VAR_PATH_WEB_BASE."certificate/";
$_VAR_PATH_WEB_HISTORY = $_VAR_PATH_WEB_BASE."history/";
$_VAR_PATH_WEB_FAQ = $_VAR_PATH_WEB_BASE."faq/";
$_VAR_PATH_WEB_POPUP = $_VAR_PATH_WEB_DATA."popup/";

// -- URL --
$_VAR_URL_WEB = $_VAR_URL_ROOT."web/"; //WEB루트 경로
$_VAR_URL_WEB_BASE = $_VAR_URL_WEB."base/"; //BASE 경로
$_VAR_URL_WEB_DATA = $_VAR_URL_WEB."data/"; //DATA 경로
$_VAR_URL_WEB_LIB = $_VAR_URL_WEB."lib/"; //LIB 경로
$_VAR_URL_WEB_MENU = $_VAR_URL_WEB."menu/"; //MENU 경로
$_VAR_URL_WEB_TEMP = $_VAR_URL_WEB."temp/"; //TEMP 경로
$_VAR_URL_WEB_THEMA = $_VAR_URL_WEB."thema/"; //THEMA 경로
$_VAR_URL_WEB_UI = $_VAR_URL_WEB."ui/"; //UI 경로
$_VAR_URL_WEB_PAGE = $_VAR_PATH_WEB_UI."pages/"; //UI 경로
$_VAR_URL_WEB_BOARD = $_VAR_URL_WEB_BASE."board/";
$_VAR_URL_WEB_BOARD_MOBILE = $_VAR_URL_WEB_BASE."board/";
$_VAR_URL_CERTIFICATE_IMAGE = $_VAR_URL_WEB_DATA."certificate/";
$_VAR_URL_WEB_LOGIN = $_VAR_URL_WEB_BASE."login/";
$_VAR_URL_WEB_MEMBER = $_VAR_URL_WEB_BASE."member/";
$_VAR_URL_WEB_LASTEST = $_VAR_URL_WEB_BASE."latest/";
$_VAR_URL_WEB_CERTIFICATE = $_VAR_URL_WEB_BASE."certificate/";
$_VAR_URL_WEB_HISTORY = $_VAR_URL_WEB_BASE."history/";
$_VAR_URL_WEB_FAQ = $_VAR_URL_WEB_BASE."faq/";
$_VAR_URL_WEB_POPUP = $_VAR_URL_WEB_DATA."popup/";

$_VAR_PROG_ROOT_PATH = $_VAR_PATH_WEB.'program/';
$_VAR_PROG_ROOT_URL = $_VAR_URL_WEB.'program/';
$_VAR_PROG_DATA_PATH = $_VAR_PROG_ROOT_PATH.'data/';
$_VAR_PROG_DISORDER_IMAGE_PATH = $_VAR_PROG_DATA_PATH.'disorder/';

$_VAR_PROG_ADMIN_ROOT = $_VAR_PROG_ROOT_PATH.'admin/';

// 상대경로
$_VAR_LAYOUT_PATH = "./thema/";
$_VAR_BOARD_RELATIVE_SKIN = "./base/board/";

$admin_email = $CONFIG['dm_ceo_email'];

$KAKAO_KEY = $CONFIG['dm_kakao_client_id'];
$KAKAO_SECRET_KEY = $CONFIG['dm_kakao_client_secret'];
$KAKAO_RETURN_URL = "http://192.168.0.48/diam/web/base/login/kakaologin.php";

$NAVER_KEY = $CONFIG['dm_naver_client_id'];
$NAVER_SECRET_KEY = $CONFIG['dm_naver_client_secret'];
$NAVER_RETURN_URL = "http://192.168.0.48/diam/web/base/login/naverlogin.php";

// 세션변수 세팅
$member_level = getSession('chk_dm_level');
$member_id = getSession('chk_dm_id');
$member_name = getSession('chk_dm_name');
$is_admin = getSession('is_admin');
$is_member = getSession('is_member');

?>