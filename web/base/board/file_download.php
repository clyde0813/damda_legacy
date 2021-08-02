<?php

function mb_basename($path) {
    $array = explode('/', $path);
    return end($array);
}
function utf2euc($str) { return iconv("UTF-8","cp949//IGNORE", $str); }
function is_ie() {
    if(!isset($_SERVER['HTTP_USER_AGENT']))return false;
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) return true; // IE8
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'Windows NT 6.1') !== false) return true; // IE11
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false) return true; // edge
    if(strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], "Trident") !== false) return true;
    return false;
}
$_VAR_PATH_WEB_DATA = $_SERVER['DOCUMENT_ROOT']."/diam/web/data/";

$filename = $_REQUEST['file_name'] ?  urldecode($_REQUEST['file_name']) : "";
$ori_file_name = $_REQUEST['ori_file_name'] ? urldecode($_REQUEST['ori_file_name']) : "";

$filepath = $_VAR_PATH_WEB_DATA.'file/'.$filename;
$filesize = filesize($filepath);

$ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false);
// EDGE인지 HTTP_USER_AGENT로 확인
$edge = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false);

if ($edge){
    // edge인경우 파일명 rowurlencode로 인코딩시킴
    $ori_file_name = rawurlencode($ori_file_name);
    $ori_file_name = preg_replace('/\./', '%2e', $ori_file_name, substr_count($ori_file_name, '.') - 1);
    // edge인 경우의 헤더 변경
    $header_cachecontrol = 'private, no-transform, no-store, must-revalidate';
    $header_pragma='no-cache';
}else{
    if($ie) {
        // UTF-8에서 EUC-KR로 캐릭터셋 변경
		$file_chk = iconv('utf-8', 'euc-kr', $ori_file_name);
		//일부 컴퓨터에서 한글변환 시 파일명이 빠져서 header로 보내지는 현상발생, 해당현상 방지를 위해 분기처리 추가
        if ($file_chk) {
			$ori_file_name = iconv('utf-8', 'euc-kr', $ori_file_name);
		}
        // IE인 경우 헤더 변경
        $header_cachecontrol = 'must-revalidate, post-check=0, pre-check=0';
        $header_pragma='public';

    }else{
        // IE가 아닌 경우 일반 헤더 적용
        $header_cachecontrol = 'private, no-transform, no-store, must-revalidate';
        $header_pragma='no-cache';
    }
}

header("Pragma: $header_pragma");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename={$ori_file_name};");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");
header("Cache-Control: $header_cachecontrol");

$fh = fopen($filepath, "r");
fpassthru($fh);
fclose($fh);

//readfile($filepath);