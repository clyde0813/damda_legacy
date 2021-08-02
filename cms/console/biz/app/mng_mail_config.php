<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-10-22
 * Time: 오후 2:23
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$create_id = getSession('chk_dm_id');
$db = new DBSQL();
$db->DBconnect();

if($type == "select") {
    $query = "SELECT * FROM dm_mail_config";
    $db->ExecSql($query, "S");

    while($row = $db->Fetch()) {
        $arData[] = $row;
    }

    $arResult = array( "result" => "success", "_return" => "","total" => $total_count, "rows" => $arData);

    echo json_encode($arResult);
}

else if ($type == 'insert') {
    $dm_no = isset($_REQUEST['dm_no']) ? $_REQUEST['dm_no'] : "";

    $dm_use_join_mail = isset($_REQUEST['dm_use_join_mail']) ? $_REQUEST['dm_use_join_mail'] : "";
    $dm_use_password_mail = isset($_REQUEST['dm_use_password_mail']) ? $_REQUEST['dm_use_password_mail'] : "";
    $dm_use_reply_mail = isset($_REQUEST['dm_use_reply_mail']) ? $_REQUEST['dm_use_reply_mail'] : "";
    $dm_use_out_mail = isset($_REQUEST['dm_use_out_mail']) ? $_REQUEST['dm_use_out_mail'] : "";

    $dm_join_email = isset($_REQUEST['dm_join_email']) ? $_REQUEST['dm_join_email'] : "";
    $dm_password_email = isset($_REQUEST['dm_password_email']) ? $_REQUEST['dm_password_email'] : "";
    $dm_reply_email = isset($_REQUEST['dm_reply_email']) ? $_REQUEST['dm_reply_email'] : "";
    $dm_out_email = isset($_REQUEST['dm_out_email']) ? $_REQUEST['dm_out_email'] : "";

    $dm_join_subject = isset($_REQUEST['dm_join_subject']) ? $_REQUEST['dm_join_subject'] : "";
    $dm_password_subject = isset($_REQUEST['dm_password_subject']) ? $_REQUEST['dm_password_subject'] : "";
    $dm_reply_subject = isset($_REQUEST['dm_reply_subject']) ? $_REQUEST['dm_reply_subject'] : "";
    $dm_out_subject = isset($_REQUEST['dm_out_subject']) ? $_REQUEST['dm_out_subject'] : "";

    $dm_join_content = isset($_REQUEST['dm_join_content']) ? $_REQUEST['dm_join_content'] : "";
    $dm_reply_content = isset($_REQUEST['dm_reply_content']) ? $_REQUEST['dm_reply_content'] : "";
    $dm_password_content = isset($_REQUEST['dm_password_content']) ? $_REQUEST['dm_password_content'] : "";
    $dm_out_content = isset($_REQUEST['dm_out_content']) ? $_REQUEST['dm_out_content'] : "";

    $dm_join_content = addslashes($dm_join_content);
    $dm_reply_content = addslashes($dm_reply_content);
    $dm_password_content = addslashes($dm_password_content);
    $dm_out_content = addslashes($dm_out_content);

    $query = "INSERT INTO dm_mail_config (`dm_no`, `dm_use_join_mail`, `dm_use_password_mail`, `dm_use_reply_mail`, `dm_use_out_mail`, `dm_join_email`, `dm_password_email`, `dm_reply_email`, `dm_out_email`, `dm_join_subject`, 
    `dm_join_content`, `dm_password_subject`, `dm_password_content`, `dm_reply_subject`, `dm_reply_content`, `dm_out_subject`, `dm_out_content`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`)
    VALUE ('".$dm_no."', '".$dm_use_join_mail."', '".$dm_use_password_mail."', '".$dm_use_reply_mail."', '".$dm_use_out_mail."', '".$dm_join_email."', '".$dm_password_email."', '".$dm_reply_email."',
    '".$dm_out_email."', '".$dm_join_subject."', '".$dm_join_content."', '".$dm_password_subject."', '".$dm_password_content."', '".$dm_reply_subject."','".$dm_reply_content."','".$dm_out_subject."',
     '".$dm_out_content."', now(),'".$create_id."', now(), '".$create_id."') ON DUPLICATE KEY UPDATE `dm_use_join_mail` = '".$dm_use_join_mail."',  `dm_use_password_mail` = '".$dm_use_password_mail."',
      `dm_use_reply_mail` = '".$dm_use_reply_mail."', `dm_use_out_mail` = '".$dm_use_out_mail."',  `dm_join_email` = '".$dm_join_email."',  `dm_password_email` = '".$dm_password_email."',  `dm_reply_email` = '".$dm_reply_email."',
       `dm_out_email` = '".$dm_out_email."', `dm_join_subject` = '".$dm_join_subject."',  `dm_join_content` = '".$dm_join_content."',  `dm_password_subject` = '".$dm_password_subject."',  `dm_password_content` = '".$dm_password_content."',
        `dm_reply_subject` = '".$dm_reply_subject."', `dm_reply_content` = '".$dm_reply_content."', `dm_out_subject` = '".$dm_out_subject."', `dm_out_content` = '".$dm_out_content."', `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."'";

    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_no);

    echo json_encode($arResult);
}