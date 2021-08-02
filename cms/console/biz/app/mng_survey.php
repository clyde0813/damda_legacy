<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-08
 * Time: 오전 9:42
 */


include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();
$db2 = new DBSQL();
$db2->DBconnect();
$site_id = getSession('site_id');

if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_status = isset($_REQUEST['search_status']) ? urldecode(trim($_REQUEST['search_status'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = "WHERE 1 = 1 ";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_survey_name LIKE '%".$search_value."%' OR dm_start_dt LIKE '%".$search_value."%' OR dm_end_dt LIKE '%".$search_value."%' OR dm_create_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_status) {
        $where .= " AND dm_status = '".$search_status."'";
    }

    $query = "SELECT count(*) FROM `dm_survey` ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT * FROM `dm_survey` $where ORDER BY `dm_id` DESC";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $arData['dm_status_text'] = $selectCodeStatus[$arData['dm_status']];
            $arData['dm_start_dt'] = date("Y-m-d", strtotime($arData['dm_start_dt']));
            $arData['dm_end_dt'] = date("Y-m-d", strtotime($arData['dm_end_dt']));
            $answer = explode("|", $arData['dm_answer']);
            $answer = array_filter($answer);
            $arData['dm_answer'] = $answer;
            $survey_total = 0;
            foreach ($answer as $key => $value) {
                $query = "SELECT count(*) as a FROM dm_survey_log WHERE dm_survey_id = '".$arData['dm_id']."' AND dm_item = '".($key+1)."'";
                $db2->ExecSql($query, "S");
                $row = $db2->Fetch();
                $arData['dm_answer'.($key+1)."_count"] = $row['a'];
                $survey_total += $row['a'];
            }
            $arData['survey_total'] = $survey_total;
            $arReturn[] = $arData;
        }
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);

} else if ($type == "insert" || $type == 'update') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";
    $dm_survey_name = isset($_REQUEST['dm_survey_name']) ? trim($_REQUEST['dm_survey_name']) : "";
    $dm_survey_question = isset($_REQUEST['dm_survey_question']) ? trim($_REQUEST['dm_survey_question']) : "";
    $dm_start_dt = isset($_REQUEST['dm_start_dt']) ? trim($_REQUEST['dm_start_dt']) : "";
    $dm_end_dt = isset($_REQUEST['dm_end_dt']) ? trim($_REQUEST['dm_end_dt']) : "";
    $dm_status = isset($_REQUEST['dm_status']) ? trim($_REQUEST['dm_status']) : "";
    $create_id = getSession("chk_dm_id");
    $answer_0 = $answer_1 = $answer_2 = $answer_3 = $answer_4 = $answer_5 = $answer_6 = $answer_7 = $answer_8 = $answer_9 = "";

    $append_value = "";

    for ($i=0; $i<10; $i++) {
        ${"answer".$i} = isset($_REQUEST['dm_answer_'.$i]) ? $_REQUEST['dm_answer_'.$i] : "";
        if (${"answer".$i}) {
            $append_value .= ${"answer".$i}."|";
        }
    }

    $query = "INSERT INTO `dm_survey` (`dm_id`, `dm_survey_name`, `dm_survey_question`, `dm_start_dt`, `dm_end_dt`, `dm_status`, `dm_create_dt`, `dm_create_id`, `dm_modify_dt`, `dm_modify_id`, `dm_ip`, `dm_answer`
    ) VALUE ('".$dm_id."', '".$dm_survey_name."', '".$dm_survey_question."', '".$dm_start_dt."', '".$dm_end_dt."', '".$dm_status."', now(), '".$create_id."', now(), '".$create_id."', '".$_SERVER['REMOTE_ADDR']."', '".$append_value."'
    ) ON DUPLICATE KEY UPDATE `dm_survey_name` = '".$dm_survey_name."', `dm_survey_question` = '".$dm_survey_question."', `dm_start_dt` = '".$dm_start_dt."', `dm_end_dt` = '".$dm_end_dt."', `dm_status` = '".$dm_status."',
    `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."', `dm_answer` = '".$append_value."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    $arResult = array("result" => "success", "_return" => $dm_id, "notice" => "등록했습니다.");

    echo json_encode($arResult);

} else if ($type == 'delete') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_survey` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}

else if ($type == "survey_detail") {
    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_survey = isset($_REQUEST['search_survey']) ? urldecode(trim($_REQUEST['search_survey'])): "";

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $where = "WHERE 1 = 1 ";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (a.mb_id LIKE '%".$search_value."%' OR b.dm_survey_name LIKE '%".$search_value."%' OR b.dm_start_dt LIKE '%".$search_value."%' OR b.dm_end_dt LIKE '%".$search_value."%' OR b.dm_create_dt LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_survey && $search_survey != "전체") {
        $where .= " AND dm_survey_id = '".$search_survey."'";
    }

    $query = "SELECT count(*) FROM dm_survey_log as a INNER JOIN dm_survey AS b ON a.dm_survey_id = b.dm_id ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $selectCodeStatus = selectCommonCode('1001');

    $query = " SELECT a.*, b.dm_answer, b.dm_survey_name, b.dm_survey_question FROM dm_survey_log as a INNER JOIN dm_survey AS b ON a.dm_survey_id = b.dm_id $where ORDER BY `dm_id` DESC";

    $db->ExecSql($query, "S");

    if ($db -> Num >0)  {
        while ($arData = $db->Fetch()) {
            $answer = explode("|", $arData['dm_answer']);
            $answer = array_filter($answer);

            foreach ($answer as $key => $value) {
                if (($key+1) == $arData['dm_item']) {
                    $arData['dm_answer'] = $value;
                }
            }
            $arReturn[] = $arData;
        }
    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'delete_detail') {
    $dm_id = isset($_REQUEST['dm_id']) ? trim($_REQUEST['dm_id']) : "";

    $query = "DELETE FROM `dm_survey_log` WHERE `dm_id` = '$dm_id'";
    $db->ExecSql($query, "I");

    $arResult = array("result" => "success", "_return" => $dm_id);

    echo json_encode($arResult);
}