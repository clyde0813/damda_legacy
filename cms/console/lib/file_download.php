<?php
include ("lib.php");
function mb_basename($path) { return end(explode('/',$path)); }
function utf2euc($str) { return iconv("UTF-8","cp949//IGNORE", $str); }
function is_ie() {
    if(!isset($_SERVER['HTTP_USER_AGENT']))return false;
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) return true; // IE8
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'Windows NT 6.1') !== false) return true; // IE11
    return false;
}
$filename = $_REQUEST['file_name'] ?  $_REQUEST['file_name'] : "";
$dm_table = $_REQUEST['dm_table'] ?  $_REQUEST['dm_table'] : "";
$ori_file_name = $_REQUEST['ori_file_name'] ?  $_REQUEST['ori_file_name'] : "";

$filepath = $_SERVER['DOCUMENT_ROOT'].'/diam/web/data/file/'.$dm_table.'/'.$filename;

$filesize = filesize($filepath);
$filename = mb_basename($filepath);
if( is_ie() ) $filename = utf2euc($filename);

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$ori_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");

readfile($filepath);