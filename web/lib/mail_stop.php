<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-10-30
 * Time: 오후 2:55
 */


include ('./lib.php');

$dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";
$dm_md5 = isset($_REQUEST['dm_md5']) ? $_REQUEST['dm_md5'] : "";

if (!$dm_id) {
    alert ("잘못된 접근입니다.");
    exit;
}
$db = new DBSQL();
$db->DBconnect();

$query = " select dm_id, dm_email, dm_datetime from dm_member where dm_id = '{$dm_id}' ";
$db->ExecSql($query, "S");
$row = $db->Fetch();


if (!$row['dm_id'])
    alert('존재하는 회원이 아닙니다.', '/diam/web');

if ($dm_md5) {
    $tmp_md5 = md5($row['dm_id'].$row['dm_email'].$row['dm_datetime']);
    if ($dm_md5 == $tmp_md5) {
        $query = " update dm_member set dm_mailling  = 0 where dm_id = '{$dm_id}' ";
        $db->ExecSql($query, "I");
        alert('정보메일을 보내지 않도록 수신거부 하였습니다.', '/diam/web');
        exit;
    }
}

alert('제대로 된 값이 넘어오지 않았습니다.', '/diam/web');