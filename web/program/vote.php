<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-08
 * Time: 오전 11:18
 */


include ("../lib/lib.php");

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
$dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

$db = new DBSQL();
$db->DBconnect();

if ($type == "get_main_vote_list") {
    $arr = array();
    $arr['cate1'] = array();
    $arr['cate2'] = array();

    $query = "SELECT * FROM dm_vote WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() and dm_vote_category = 1 order by dm_modify_dt desc limit 0, 3";
    $db->ExecSql($query, "S");
    
    while ($row = $db->Fetch()) {
        if ($row['dm_vote1_count'] > 0) {
            $row['dm_vote1_percent'] = round(($row['dm_vote1_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote2_percent'] = 100 - $row['dm_vote1_percent'];
        } else if ($row['dm_vote1_count'] <= 0 && $row['dm_vote2_count'] > 0){
            $row['dm_vote2_percent'] = round(($row['dm_vote2_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote1_percent'] = 100 - $row['dm_vote2_percent'];
        }
        $row['total_count'] = number_format($row['dm_vote1_count'] + $row['dm_vote2_count']);
        $arr['cate1'][] = $row;
    }

    $query = "SELECT * FROM dm_vote WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() and dm_vote_category = 2 order by dm_modify_dt desc limit 0, 3";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        if ($row['dm_vote1_count'] > 0) {
            $row['dm_vote1_percent'] = round(($row['dm_vote1_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote2_percent'] = 100 - $row['dm_vote1_percent'];
        } else if ($row['dm_vote1_count'] <= 0 && $row['dm_vote2_count'] > 0){
            $row['dm_vote2_percent'] = round(($row['dm_vote2_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote1_percent'] = 100 - $row['dm_vote2_percent'];
        }
        $row['total_count'] = number_format($row['dm_vote1_count'] + $row['dm_vote2_count']);
        $arr['cate2'][] = $row;
    }
    
    echo json_encode($arr);
    
}

if ($type == "get_vote_list") {
    $db2 = new DBSQL();
    $db2->DBconnect();

    $arr = array();
    $arr['cate1'] = array();
    $arr['cate2'] = array();

    $query = "SELECT * FROM dm_vote WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() and dm_vote_category = 1 order by dm_modify_dt desc";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        // 추가한 소스, 찬반 둘다 0일경우 계산을 할수가 없어 위쪽에 추가
		$row['dm_vote1_count'] = empty($row['dm_vote1_count']) ? 0 : number_format($row['dm_vote1_count']);
        $row['dm_vote2_count'] = empty($row['dm_vote2_count']) ? 0 : number_format($row['dm_vote2_count']);
		
		if ($row['dm_vote1_count'] > 0) {
            $row['dm_vote1_percent'] = round(($row['dm_vote1_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote2_percent'] = 100 - $row['dm_vote1_percent'];
        } else if ($row['dm_vote1_count'] <= 0 && $row['dm_vote2_count'] > 0){
            $row['dm_vote2_percent'] = round(($row['dm_vote2_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote1_percent'] = 100 - $row['dm_vote2_percent'];
        } else {
			$row['dm_vote2_percent'] = 0;
			$row['dm_vote1_percent'] = 0;
		}
        $row['total_count'] = number_format($row['dm_vote1_count'] + $row['dm_vote2_count']);
        // 원래 되어있었던 소스, 0일때 정수형 변환을 정상적으로 할수없어 변경
		/*$row['dm_vote1_count'] = number_format($row['dm_vote1_count']);
        $row['dm_vote2_count'] = number_format($row['dm_vote2_count']);*/
		$query = "SELECT * FROM dm_vote_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_vote_id = '".$row['dm_id']."'";
        $db2->ExecSql($query, "S");
        $currentInfo = $db2->Fetch();
        $row['select_answer'] = $currentInfo['dm_item'];
        $arr['cate1'][] = $row;
    }

    $query = "SELECT * FROM dm_vote WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() and dm_vote_category = 2 order by dm_modify_dt desc";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        // 추가한 소스, 찬반 둘다 0일경우 계산을 할수가 없어 위쪽에 추가
		$row['dm_vote1_count'] = empty($row['dm_vote1_count']) ? 0 : number_format($row['dm_vote1_count']);
        $row['dm_vote2_count'] = empty($row['dm_vote2_count']) ? 0 : number_format($row['dm_vote2_count']);
		
		if ($row['dm_vote1_count'] > 0) {
            $row['dm_vote1_percent'] = round(($row['dm_vote1_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote2_percent'] = 100 - $row['dm_vote1_percent'];
        } else if ($row['dm_vote1_count'] <= 0 && $row['dm_vote2_count'] > 0){
            $row['dm_vote2_percent'] = round(($row['dm_vote2_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
            $row['dm_vote1_percent'] = 100 - $row['dm_vote2_percent'];
        }
        $row['total_count'] = number_format($row['dm_vote1_count'] + $row['dm_vote2_count']);
        // 원래 되어있었던 소스, 0일때 정수형 변환을 정상적으로 할수없어 변경
		/*$row['dm_vote1_count'] = number_format($row['dm_vote1_count']);
        $row['dm_vote2_count'] = number_format($row['dm_vote2_count']);*/
        $query = "SELECT * FROM dm_vote_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_vote_id = '".$row['dm_id']."'";
        $db2->ExecSql($query, "S");
        $currentInfo = $db2->Fetch();
        $row['select_answer'] = $currentInfo['dm_item'];
        $arr['cate2'][] = $row;
    }

    echo json_encode($arr);

}

if ($type == "get_vote") {
    $query = "SELECT * FROM dm_vote WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() AND dm_id = '".$dm_id."' order by dm_modify_dt desc";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    if ($row['dm_vote1_count'] > 0) {
        $row['dm_vote1_percent'] = round(($row['dm_vote1_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
        $row['dm_vote2_percent'] = 100 - $row['dm_vote1_percent'];
    } else if ($row['dm_vote1_count'] <= 0 && $row['dm_vote2_count'] > 0){
        $row['dm_vote2_percent'] = round(($row['dm_vote2_count'] / ($row['dm_vote1_count'] + $row['dm_vote2_count']) * 100), 2);
        $row['dm_vote1_percent'] = 100 - $row['dm_vote2_percent'];
    }

    $row['total_count'] = number_format($row['dm_vote1_count'] + $row['dm_vote2_count']);

    $query = "SELECT * FROM dm_vote_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_vote_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    $row['select_answer'] = $currentInfo['dm_item'];

    echo json_encode($row);
}

else if ($type == "set_vote") {
    if (!getSession("chk_dm_id")) {
        $arResult = array("result" => "fail", "_return" => "", "notice" => "로그인 후 이용해주세요", "url" => "?contentId=c13406bf526e9fee2bed34ab6f2125f6", "rows" => $arReturn);
        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $item = isset($_REQUEST['vote_item']) ? $_REQUEST['vote_item'] : "";

    $query = "SELECT * FROM dm_vote_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_vote_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    if ($currentInfo) {
        if ($item != $currentInfo['dm_item']) {
            $query = "UPDATE dm_vote_log SET dm_item = '".$item."', dm_datetime = now() WHERE dm_id = '".$currentInfo['dm_id']."'";
            $db->ExecSql($query, "S");

            $other_item = ($item == 1) ? "2" : "1";

            $query = "UPDATE dm_vote SET `dm_vote".$item."_count` = `dm_vote".$item."_count` + 1,
            `dm_vote".$other_item."_count` = `dm_vote".$other_item."_count` - 1
            WHERE dm_id = '".$dm_id."'";
            $db->ExecSql($query, "S");

            $arResult = array("result" => "success", "_return" => "", "notice" => "투표했습니다.", "rows" => $arReturn);
            echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $arResult = array("result" => "fail", "_return" => "", "notice" => "동일한 항목에 투표하실 수 없습니다.", "rows" => $arReturn);
            echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        $query = "INSERT INTO dm_vote_log (dm_vote_id, dm_item, mb_id, dm_ip, dm_datetime) VALUE ('".$dm_id."', '".$item."', '".getSession("chk_dm_id")."', '".$_SERVER['REMOTE_ADDR']."', now())";
        $db->ExecSql($query, "S");

        $query = "UPDATE dm_vote SET `dm_vote".$item."_count` = `dm_vote".$item."_count` + 1  WHERE dm_id = '".$dm_id."'";
        $db->ExecSql($query, "S");

        $arResult = array("result" => "success", "_return" => "", "notice" => "투표했습니다.", "rows" => $arReturn);
        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }


}