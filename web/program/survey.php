<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-21
 * Time: 오전 11:25
 */



include ("../lib/lib.php");

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
$dm_id = isset($_REQUEST['dm_id']) ? $_REQUEST['dm_id'] : "";

$db = new DBSQL();
$db->DBconnect();

if ($type == "get_main_survey") {
    $arr = array();

    $query = "SELECT * FROM dm_survey WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() order by dm_modify_dt desc limit 1";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    echo json_encode($row);
}

else if ($type == "get_survey_list") {
    $db2 = new DBSQL();
    $db2->DBconnect();
    $arr = array();

    $query = "SELECT * FROM dm_survey WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() order by dm_modify_dt desc ";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        $answer = explode("|", $row['dm_answer']);
        $answer = array_filter($answer);
        $row['dm_answer'] = $answer;

        $query = "SELECT count(*) as a FROM dm_survey_log WHERE dm_survey_id = '".$row['dm_id']."'";
        $db2->ExecSql($query, "S");
        $total_count = $db2->Fetch();
        $total_count = $total_count['a'];

        foreach ($answer as $key => $value) {
            $query = "SELECT count(*) as a FROM dm_survey_log WHERE dm_survey_id = '".$row['dm_id']."' AND dm_item = '".($key+1)."'";
            $db2->ExecSql($query, "S");
            $answer_count = $db2->Fetch();
            $answer_count = $answer_count['a'];
            $answer_avg = 0;

            if ($answer_count > 0) {
                $answer_avg = round(($answer_count / ($total_count) * 100), 2);
            }

            $row['dm_answer_count'][] = number_format($answer_count['a']);
            $row['dm_answer_avg'][] = $answer_avg;
        }

        $row['select_answer'] = "";

        $query = "SELECT * FROM dm_survey_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_survey_id = '".$row['dm_id']."'";
        $db2->ExecSql($query, "S");
        $currentInfo = $db2->Fetch();

        $row['total_count'] = number_format($total_count);
        $row['select_answer'] = $currentInfo['dm_item'];

        $arr[] = $row;
    }
    echo json_encode($arr);
}

else if ($type == "get_survey") {
    $arr = array();

    $query = "SELECT * FROM dm_survey WHERE dm_status = 1 AND date_format(dm_start_dt, '%Y-%m-%d') <= CURDATE() AND date_format(dm_end_dt, '%Y-%m-%d') >= CURDATE() AND dm_id = '".$dm_id."' order by dm_modify_dt desc ";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $answer = explode("|", $row['dm_answer']);
    $answer = array_filter($answer);
    $row['dm_answer'] = $answer;
    $row['select_answer'] = "";

    $query = "SELECT * FROM dm_survey_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_survey_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    $row['select_answer'] = $currentInfo['dm_item'];

    echo json_encode($row);
}


else if ($type == "set_survey") {
    if (!getSession("chk_dm_id")) {
        $arResult = array("result" => "fail", "_return" => "", "notice" => "로그인 후 이용해주세요", "url" => "?contentId=c13406bf526e9fee2bed34ab6f2125f6", "rows" => $arReturn);
        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $item = isset($_REQUEST['survey_item']) ? $_REQUEST['survey_item'] : "";

    $query = "SELECT * FROM dm_survey_log WHERE mb_id = '".getSession("chk_dm_id")."' AND dm_survey_id = '".$dm_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    if ($currentInfo) {
        if ($item != $currentInfo['dm_item']) {
            $query = "UPDATE dm_survey_log SET dm_item = '".$item."', dm_datetime = now() WHERE dm_id = '".$currentInfo['dm_id']."'";
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
        $query = "INSERT INTO dm_survey_log (dm_survey_id, dm_item, mb_id, dm_ip, dm_datetime) VALUE ('".$dm_id."', '".$item."', '".getSession("chk_dm_id")."', '".$_SERVER['REMOTE_ADDR']."', now())";
        $db->ExecSql($query, "S");

        $arResult = array("result" => "success", "_return" => "", "notice" => "투표했습니다.", "rows" => $arReturn);
        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }
}