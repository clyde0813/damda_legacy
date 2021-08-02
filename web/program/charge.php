<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-27
 * Time: 오후 3:21
 */

include ("../lib/lib.php");

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";

if ($type == "get_goods") {
    $dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

    $query = "SELECT * FROM dm_goods WHERE dm_id = '{$dm_id}'";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $row['dm_goods_price'] = number_format($row['dm_goods_price']);
    setSession("chk_goods_id", $row['dm_id']);

    echo json_encode($row);
}

else if ($type == "set_bank") {
    $goods_id = getSession("chk_goods_id");
    $dm_name = isset($_REQUEST['dm_name']) ? $_REQUEST['dm_name'] : getSession("chk_dm_name");
    setSession("chk_goods_id", "");

    if (!$goods_id) {
        $arResult = array("result" => "fail", "_return" => "", "notice" => "잘못된 접근입니다.", "rows" => $arReturn);
        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!getSession("chk_dm_id")) {
        $arResult = array("result" => "nomember", "_return" => "", "notice" => "로그인 후 이용해주세요", "url" => "?contentId=c13406bf526e9fee2bed34ab6f2125f6", "rows" => $arReturn);
        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $uid = md5(date("YmdHis").getSession("chk_dm_id"));

    $query = "SELECT * FROM dm_goods WHERE dm_id = '".$goods_id."'";
    $db->ExecSql($query, "S");
    $goodsInfo = $db->Fetch();

    $query = "INSERT INTO dm_bank (dm_name, mb_id, dm_amount, dm_uid, dm_state, dm_datetime, dm_goods_id, dm_goods_price, dm_goods_name, dm_bonus)
    VALUE ('".$dm_name."', '".getSession("chk_dm_id")."', '".$goodsInfo['dm_goods_price']."', '".$uid."', 1, now(), '".$goodsInfo['dm_id']."', 
    '".$goodsInfo['dm_goods_price']."', '".$goodsInfo['dm_goods_name']."','".$goodsInfo['dm_bonus']."')";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => "", "notice" => "로그인 후 이용해주세요", "url" => "?contentId=c13406bf526e9fee2bed34ab6f2125f6", "rows" => $arReturn);
    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    exit;

}

else if ($type == "get_mypoint") {
    $query = "select 
    (select sum(dm_point) FROM dm_point_log WHERE dm_id = '".getSession("chk_dm_id")."' and dm_kind = 0) as `use_point`,
    (select sum(dm_point) FROM dm_point_log WHERE dm_id = '".getSession("chk_dm_id")."' and dm_kind = 1) as `charge_point`,
    (select dm_datetime FROM dm_bank WHERE mb_id = '".getSession("chk_dm_id")."' order by dm_datetime desc limit 1) as `last_point_date`
    ;";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $row['use_point'] = number_format($row['use_point']);
    $row['charge_point'] = number_format($row['charge_point']);
    $row['remain_point'] = number_format($MEMBER['dm_point']);

    echo json_encode($row, JSON_UNESCAPED_UNICODE);
    exit;

}