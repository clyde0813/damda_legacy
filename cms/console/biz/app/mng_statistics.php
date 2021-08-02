<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-06-22
 * Time: 오전 10:45
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$search_start_date = isset($_REQUEST['search_start_date']) ? trim($_REQUEST['search_start_date']) : "";
$search_end_date = isset($_REQUEST['search_end_date']) ? trim($_REQUEST['search_end_date']) : "";
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

$site_id = getSession('site_id');
$db = new DBSQL();
$db->DBconnect();

$arReturn = array();

if ($type == "select_login")
{
    $where = "WHERE `dm_fn_code` = 'login' AND dm_login_id <> 'admin'";

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_login_id LIKE '%".$search_value."%' OR dm_type LIKE '%".$search_value."%' OR dm_datetime LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($site_id) {
        $where .= " AND dm_domain = '".$site_id."'";
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    $query = "SELECT count(*) FROM `dm_web_log`  ".$where;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = "SELECT * FROM `dm_web_log` $where  $limit";

    $db->ExecSql($query, "S");

    $selectCodeStatus = selectCommonCode('1023');

    while ($arData = $db->Fetch()) {
        $arData['dm_status'] = $selectCodeStatus[$arData['dm_fn_result']];
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);


}

else if ($type == "select_accessor")
{
    $where = "";

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    $j = 0;
    for ($i=0; $i<24; $i++) {
        $tt = str_pad($i,"2","0",STR_PAD_LEFT);
        $search_time = $tt;

        $query = "SELECT
        date_format(dm_datetime, '%H:00') as `dm_time`,
        sum(dm_count) as `dm_visit`,
        (select count(*) FROM dm_web_log as l where dm_1 = 0 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%H') = date_format(v.dm_datetime, '%H')) as `dm_re_visit`,
        (select count(*) FROM dm_web_log as l where dm_1 = 1 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%H') = date_format(v.dm_datetime, '%H')) as `dm_new_visit`,
        sum(dm_count) as `dm_page_view`
        FROM
            dm_visit as v
        WHERE
            date_format(dm_datetime, '%H') = '".$search_time."' $where
        GROUP BY
            dm_datetime";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        if ($row) {
            $arReturn[$j] = $row;
        } else {
            $arReturn[$j]['dm_time'] = $tt.":00";
            $arReturn[$j]['dm_visit'] = 0;
            $arReturn[$j]['dm_re_visit'] = 0;
            $arReturn[$j]['dm_new_visit'] = 0;
            $arReturn[$j]['dm_page_view'] = 0;
        }
        $j++;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'total_accessor') {
    $where = " WHERE 1 = 1";
    $where1 = "";

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";
        $where1 .= " AND `dm_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
        $where1 .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    $query = "SELECT sum(dm_count) as `today` FROM dm_visit WHERE date_format(dm_datetime, '%Y-%m-%d') = CURRENT_DATE()";
    $db->ExecSql($query, "S");
    $today = $db->Fetch();
    $today = $today['today'];
    $today_date = date("Y-m-d");

    $query = "SELECT max(b.total) as `max_count`, b.dm_datetime as `max_date` FROM ( select sum(dm_count) as total, dm_datetime from dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') ) as b";
    $db->ExecSql($query, "S");
    $max = $db->Fetch();
    $max_count = $max['max_count'];
    $query = "SELECT date_format(dm_datetime, '%Y-%m-%d') as max_date FROM dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') having sum(dm_count)  = '".$max_count."'";
    $db->ExecSql($query, "S");
    $max = $db->Fetch();
    $max_date = $max['max_date'];

    $query = "SELECT min(b.total) as `min_count`  FROM ( select sum(dm_count) as total, dm_datetime from dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') ) as b";
    $db->ExecSql($query, "S");
    $min = $db->Fetch();
    $min_count = $min['min_count'];
    $query = "SELECT date_format(dm_datetime, '%Y-%m-%d') as min_date FROM dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') having sum(dm_count)  = '".$min_count."'";
    $db->ExecSql($query, "S");
    $min = $db->Fetch();
    $min_date = $min['min_date'];

    $query = "SELECT count(*) as `total_pv` FROM dm_web_log WHERE dm_fn_code = 'common'";
    $db->ExecSql($query, "S");
    $total_pv = $db->Fetch();
    $total_pv = $total_pv['total_pv'];

    $query = "SELECT min(b.total) as `min_pv_count` FROM ( select count(*) as total, dm_datetime from dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') ) as b";
    $db->ExecSql($query, "S");
    $min_pv = $db->Fetch();
    $min_pv_count = $min_pv['min_pv_count'];
    $query = "SELECT date_format(dm_datetime, '%Y-%m-%d') as min_pv_date FROM dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') having count(*) = '".$min_pv_count."' ";
    $db->ExecSql($query, "S");
    $min_pv = $db->Fetch();
    $min_pv_date = $min_pv['min_pv_date'];

    $query = "SELECT max(b.total) as `max_pv_count` FROM ( select count(*) as total, dm_datetime from dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') ) as b";
    $db->ExecSql($query, "S");
    $max_pv = $db->Fetch();
    $max_pv_count = $max_pv['max_pv_count'];
    $query = "SELECT date_format(dm_datetime, '%Y-%m-%d') as max_pv_date FROM dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') having count(*) = '".$max_pv_count."' ";
    $db->ExecSql($query, "S");
    $max_pv = $db->Fetch();
    $max_pv_date = $max_pv['max_pv_date'];

    $result['today'] = $today;
    $result['today_date'] = $today_date;
    $result['max_count'] = $max_count;
    $result['max_date'] = date("Y-m-d", strtotime($max_date));;
    $result['min_count'] = $min_count;
    $result['min_date'] = date("Y-m-d", strtotime($min_date));
    $result['min_pv_count'] = $min_pv_count;
    $result['min_pv_date'] = date("Y-m-d H", strtotime($min_pv_date));
    $result['max_pv_count'] = $max_pv_count;
    $result['max_pv_date'] = date("Y-m-d H", strtotime($max_pv_date));

    echo json_encode($result);

}

else if ($type == "day_accessor") {
    $where = " where dm_fn_code = 'common'";

    $search_start_date = isset($_REQUEST['search_start_date']) ? $_REQUEST['search_start_date'] : "";
    $search_end_date = isset($_REQUEST['search_end_date']) ? $_REQUEST['search_end_date'] : "";

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
        }
    }

    $query = "select count(DISTINCT dm_ip) as `visitor` from dm_web_log $where"; //방문자수

    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $visitor = $row['visitor'];

    $query = "select count(*) as `visit_count` from dm_web_log $where"; //방문횟수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $visit_count = $row['visit_count'];

    $query = "select count(DISTINCT dm_ip) as `new_count` from dm_web_log $where and dm_1 = 1"; //신규방문자
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $new_count = $row['new_count'];

    $query = "select count(DISTINCT dm_ip) as `re_count` from dm_web_log $where AND dm_1 = 0"; //재방문자
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $re_count = $row['re_count'];

    if ($visitor != 0 && $visit_count != 0) {
        $pv = ceil($visit_count / $visitor);
    } else {
        $pv = 0;
    }

    $result['visitor'] = $visitor;
    $result['visit_count'] = $visit_count;
    $result['new_count'] = $new_count;
    $result['re_count'] = $re_count;
    $result['pv'] = $pv;

    echo json_encode($result);

}

else if ($type == 'week_accessor' || $type == 'week_accessor_table') {
    $visitor = array();
    $visit_count = array();
    $new_count = array();
    $re_count = array();
    $pv_array = array();

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));

            $query = "select count(DISTINCT dm_ip) as `visitor` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common'"; //방문자수
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $visitor[] = $row['visitor'];
            $visitor_count = $row['visitor'];

            $query = "select count(*) as `visit_count` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common'"; //방문횟수
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $visit_count[] = $row['visit_count'];
            $visit = $row['visit_count'];

            $query = "select count(DISTINCT dm_ip) as `new_count` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common' AND dm_1 = 1"; //신규방문자
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $new_count[] = $row['new_count'];

            $query = "select count(DISTINCT dm_ip) as `re_count` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common' AND dm_1 = 0"; //재방문자
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $re_count[] = $row['re_count'];

            if ($visitor_count != 0 && $visit != 0) {
                $pv = ceil($visit / $visitor_count);
            } else {
                $pv = 0;
            }
            $pv_array[] = $pv;

            $result['date'][] = ${"day".$i};
        }
    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            $query = "select count(DISTINCT dm_ip) as `visitor` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common'"; //방문자수
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $visitor[] = $row['visitor'];
            $visitor_count = $row['visitor'];

            $query = "select count(*) as `visit_count` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common'"; //방문횟수
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $visit_count[] = $row['visit_count'];
            $visit = $row['visit_count'];

            $query = "select count(DISTINCT dm_ip) as `new_count` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common' AND dm_1 = 1"; //신규방문자
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $new_count[] = $row['new_count'];

            $query = "select count(DISTINCT dm_ip) as `re_count` from dm_web_log where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_fn_code = 'common' AND dm_1 = 0"; //재방문자
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $re_count[] = $row['re_count'];

            if ($visitor_count != 0 && $visit != 0) {
                $pv = ceil($visit / $visitor_count);
            } else {
                $pv = 0;
            }
            $pv_array[] = $pv;

        }

        $result['date'] = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }

    if ($type == 'week_accessor') {
        $result['visitor'] = $visitor;
        $result['visit_count'] = $visit_count;
        $result['new_count'] = $new_count;
        $result['re_count'] = $re_count;
        $result['pv'] = $pv_array;

        echo json_encode($result);

    } else {
        $returnArray = array();

        foreach ($result['date'] as $key=>$value) {
            $returnArray[$key]['dm_date'] = $value;
            $returnArray[$key]['dm_visitor'] = $visitor[$key];
            $returnArray[$key]['visit_count'] = $visit_count[$key];
            $returnArray[$key]['new_count'] = $new_count[$key];
            $returnArray[$key]['re_count'] = $re_count[$key];
            $returnArray[$key]['pv'] = $pv_array[$key];
        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $returnArray);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }


}

else if ($type == 'day_of_week_accessor') {
    $where = " WHERE 1 = 1";
    $where1 = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";
        $where1 .= " AND `dm_datetime` >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
            $where1 .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
        }
    }

    $query = "SELECT max(b.total) as `max_count`FROM ( select sum(dm_count) as total, dm_datetime from dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') ) as b"; //최대 방문자수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_count = $row['max_count'];
    $query = "SELECT case WHEN DAYOFWEEK(dm_datetime) = 1 THEN '일요일' WHEN DAYOFWEEK(dm_datetime) = 2 THEN '월요일' WHEN DAYOFWEEK(dm_datetime) = 3 THEN '화요일' WHEN DAYOFWEEK(dm_datetime) = 4 THEN '수요일' WHEN DAYOFWEEK(dm_datetime) = 5 THEN '목요일' WHEN DAYOFWEEK(dm_datetime) = 6 THEN '금요일' WHEN DAYOFWEEK(dm_datetime) = 7 THEN '토요일' END as `max_date` FROM dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') having sum(dm_count)  = '".$max_count."'"; //최대 방문자수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_date = $row['max_date'];

    $query = "SELECT min(b.total) as `min_count` FROM ( select sum(dm_count) as total, dm_datetime from dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') ) as b"; //최소 방문자수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_count = $row['min_count'];
    $query = "SELECT case WHEN DAYOFWEEK(dm_datetime) = 1 THEN '일요일' WHEN DAYOFWEEK(dm_datetime) = 2 THEN '월요일' WHEN DAYOFWEEK(dm_datetime) = 3 THEN '화요일' WHEN DAYOFWEEK(dm_datetime) = 4 THEN '수요일' WHEN DAYOFWEEK(dm_datetime) = 5 THEN '목요일' WHEN DAYOFWEEK(dm_datetime) = 6 THEN '금요일' WHEN DAYOFWEEK(dm_datetime) = 7 THEN '토요일' END as `min_date` FROM dm_visit $where group by date_format(dm_datetime, '%Y-%m-%d') having sum(dm_count)  = '".$min_count."'"; //최대 방문자수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_date = $row['min_date'];

    $query = "SELECT min(b.total) as `min_pv_count` FROM ( select count(*) as total, dm_datetime from dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') ) as b";
    $db->ExecSql($query, "S");
    $min_pv = $db->Fetch();
    $min_pv_count = $min_pv['min_pv_count'];
    $query = "SELECT case WHEN DAYOFWEEK(dm_datetime) = 1 THEN '일요일' WHEN DAYOFWEEK(dm_datetime) = 2 THEN '월요일' WHEN DAYOFWEEK(dm_datetime) = 3 THEN '화요일' WHEN DAYOFWEEK(dm_datetime) = 4 THEN '수요일' WHEN DAYOFWEEK(dm_datetime) = 5 THEN '목요일' WHEN DAYOFWEEK(dm_datetime) = 6 THEN '금요일' WHEN DAYOFWEEK(dm_datetime) = 7 THEN '토요일' END as `min_pv_date` FROM dm_web_log WHERE dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') having count(*)  = '".$min_pv_count."'"; //최대 방문자수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_pv_date = $row['min_pv_date'];

    $query = "SELECT max(b.total) as `max_pv_count` FROM ( select count(*) as total, dm_datetime from dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') ) as b";
    $db->ExecSql($query, "S");
    $max_pv = $db->Fetch();
    $max_pv_count = $max_pv['max_pv_count'];
    $query = "SELECT case WHEN DAYOFWEEK(dm_datetime) = 1 THEN '일요일' WHEN DAYOFWEEK(dm_datetime) = 2 THEN '월요일' WHEN DAYOFWEEK(dm_datetime) = 3 THEN '화요일' WHEN DAYOFWEEK(dm_datetime) = 4 THEN '수요일' WHEN DAYOFWEEK(dm_datetime) = 5 THEN '목요일' WHEN DAYOFWEEK(dm_datetime) = 6 THEN '금요일' WHEN DAYOFWEEK(dm_datetime) = 7 THEN '토요일' END as `max_pv_date` FROM dm_web_log WHERE dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m-%d') having count(*)  = '".$max_pv_count."'"; //최대 방문자수
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_pv_date = $row['max_pv_date'];

    $result['max_count'] = $max_count;
    $result['max_date'] = $max_date;
    $result['min_count'] = $min_count;
    $result['min_date'] = $min_date;
    $result['min_pv_count'] = $min_pv_count;
    $result['min_pv_date'] = $min_pv_date;
    $result['max_pv_count'] = $max_pv_count;
    $result['max_pv_date'] = $max_pv_date;

    echo json_encode($result);

}

else if ($type == 'day_of_week_accessor_chart' || $type == 'day_of_week_accessor_table') {
    $visitor_array = array();
    $visit_count_array = array();
    $new_count_array = array();
    $re_count_array = array();
    $pv_array = array();
    $sat_array = array();
    $result = array();
    $date_array = array("일요일", "월요일", "화요일", "수요일","목요일", "금요일", "토요일" );

    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
        }
    }

    for ($i=1; $i<=7; $i++) {
        $query = "select count(DISTINCT dm_ip) as `visitor` from dm_web_log where DAYOFWEEK(dm_datetime) = $i and dm_fn_code = 'common' $where"; //요일 방문자수
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $visitor = $row['visitor'];

        $visitor_array[] = $visitor;

        $query = "select count(*) as `visit_count` from dm_web_log where DAYOFWEEK(dm_datetime) = $i and dm_fn_code = 'common' $where"; //요일 방문횟수
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $visit_count = $row['visit_count'];

        $visit_count_array[] = $visit_count;

        $query = "select count(DISTINCT dm_ip) as `new_count` from dm_web_log where DAYOFWEEK(dm_datetime) = $i and dm_fn_code = 'common' AND dm_1 = 1 $where"; //신규방문자
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $new_count = $row['new_count'];

        $new_count_array[] = $new_count;

        $query = "select count(DISTINCT dm_ip) as `re_count` from dm_web_log where DAYOFWEEK(dm_datetime) = $i and dm_fn_code = 'common' AND dm_1 = 0 $where"; //재방문자
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $re_count = $row['re_count'];

        $re_count_array[] = $re_count;

        if ($visit_count != 0 && $visitor != 0) {
            $pv = ceil($visit_count / $visitor);
        } else {
            $pv = 0;
        }

        $pv_array[] = $pv;
    }

    if ($type == 'day_of_week_accessor_chart') {
        $result['visitor'] = $visitor_array;
        $result['visit_count'] = $visit_count_array;
        $result['new_count'] = $new_count_array;
        $result['re_count'] = $re_count_array;
        $result['pv'] = $pv_array;

        echo json_encode($result);
    } else {
        foreach ($date_array as $key=>$value) {
            $result[$key]['dm_date'] = $value;
            $result[$key]['dm_visitor'] = $visitor_array[$key];
            $result[$key]['visit_count'] = $visit_count_array[$key];
            $result[$key]['new_count'] = $new_count_array[$key];
            $result[$key]['re_count'] = $re_count_array[$key];
            $result[$key]['pv'] = $pv_array[$key];
        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }
}

else if ($type == 'month_accessor') {
    $where = " WHERE 1 = 1";
    $where1 = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m') >= '{$search_start_date}'";
        $where1 .= " AND date_format(`dm_datetime`, '%Y-%m') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m') <= '".date("Y-m", strtotime($search_end_date))."'";
            $where1 .= " AND date_format(`dm_datetime`, '%Y-%m') <= '".date("Y-m", strtotime($search_end_date))."'";
        }
    }

    $query = "SELECT max(b.total) as `max_count`, date_format(b.dm_datetime, '%Y-%m') as `max_date` FROM ( select sum(dm_count) as total, dm_datetime from dm_visit $where group by date_format(dm_datetime, '%Y-%m') ) as b"; //최대 방문자
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_count = $row['max_count'];
    $query = " select sum(dm_count) as total, date_format(dm_datetime, '%Y-%m') as max_date from dm_visit $where group by date_format(dm_datetime, '%Y-%m') having sum(dm_count) = '".$max_count."'"; //최대 방문자
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_date = $row['max_date'];

    $query = "SELECT min(b.total) as `min_count`, date_format(b.dm_datetime, '%Y-%m') as `min_date` FROM ( select sum(dm_count) as total, dm_datetime from dm_visit $where group by date_format(dm_datetime, '%Y-%m') ) as b"; //최소 방문자
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_count = $row['min_count'];
    $query = " select sum(dm_count) as total, date_format(dm_datetime, '%Y-%m') as min_date from dm_visit $where group by date_format(dm_datetime, '%Y-%m') having sum(dm_count) = '".$min_count."'"; //최대 방문자
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_date = $row['min_date'];

    $query = "SELECT min(b.total) as `min_pv_count` FROM ( select count(*) as total from dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m') ) as b";
    $db->ExecSql($query, "S");
    $min_pv = $db->Fetch();
    $min_pv_count = $min_pv['min_pv_count'];
    $query = "SELECT date_format(dm_datetime, '%Y-%m') as `min_pv_date` FROM dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m') having count(*) = '".$min_pv_count."'";
    $db->ExecSql($query, "S");
    $min_pv = $db->Fetch();
    $min_pv_date = $min_pv['min_pv_date'];

    $query = "SELECT max(b.total) as `max_pv_count` FROM ( select count(*) as total, dm_datetime from dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m') ) as b";
    $db->ExecSql($query, "S");
    $max_pv = $db->Fetch();
    $max_pv_count = $max_pv['max_pv_count'];
    $query = "SELECT date_format(dm_datetime, '%Y-%m') as `max_pv_date` FROM dm_web_log where dm_fn_code = 'common' $where1 group by date_format(dm_datetime, '%Y-%m') having count(*) = '".$max_pv_count."'";
    $db->ExecSql($query, "S");
    $max_pv = $db->Fetch();
    $max_pv_date = $max_pv['max_pv_date'];

    $result['max_count'] = $max_count;
    $result['max_date'] = $max_date;
    $result['min_count'] = $min_count;
    $result['min_date'] = $min_date;
    $result['min_pv_count'] = $min_pv_count;
    $result['min_pv_date'] = $min_pv_date;
    $result['max_pv_count'] = $max_pv_count;
    $result['max_pv_date'] = $max_pv_date;
    $result['minus_count'] = ($max_count != 0 && $min_count != 0) ? $max_count - $min_count : 0;
    $result['minus_pv'] = ($max_pv_count != 0 && $min_pv_count != 0) ? $max_pv_count - $min_pv_count : 0;

    echo json_encode($result);

}

else if ($type == 'month_accessor_chart' || $type == 'month_accessor_table') {
    $date_array = array();
    $visitor_array = array();
    $visit_count_array = array();
    $new_count_array = array();
    $re_count_array = array();
    $pv_array = array();
    $sat_array = array();
    $result = array();

    $where = " WHERE 1 = 1";
    if ($search_start_date) {
        $search_start_date = date("Y-m", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m') <= '".date("Y-m", strtotime($search_end_date))."'";
        }
    }

    $query = "SELECT date_format(dm_datetime, '%Y-%m') as dm_date FROM dm_visit $where GROUP BY date_format(dm_datetime, '%Y-%m')";
    $db->ExecSql($query, "S");
    while($row = $db->Fetch()) {
        $date_array[] = $row;
    }

    foreach ($date_array as $key => $value) {
        $query = "select count(DISTINCT dm_ip) as `visitor` from dm_web_log where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_fn_code = 'common'"; //방문자수
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $visitor = $row['visitor'];
        $visitor_array[] = $visitor;

        $query = "select count(*) as `visit_count` from dm_web_log where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_fn_code = 'common'"; //방문횟수
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $visit_count = $row['visit_count'];
        $visit_count_array[] = $visit_count;

        $query = "select count(DISTINCT dm_ip) as `new_count` from dm_web_log where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_fn_code = 'common' AND dm_1 = 1"; //신규방문자
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $new_count = $row['new_count'];
        $new_count_array[] = $new_count;

        $query = "select count(DISTINCT dm_ip) as `re_count` from dm_web_log where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_fn_code = 'common' AND dm_1 = 0"; //재방문자
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $re_count = $row['re_count'];
        $re_count_array[] = $re_count;

        if ($visit_count != 0 && $visitor != 0) {
            $pv = ceil($visit_count / $visitor);
        } else {
            $pv = 0;
        }

        $pv_array[] = $pv;
        $result['date'][] = $value['dm_date'];
    }

    if ($type == 'month_accessor_chart') {
        $result['visitor'] = $visitor_array;
        $result['visit_count'] = $visit_count_array;
        $result['new_count'] = $new_count_array;
        $result['re_count'] = $re_count_array;
        $result['pv'] = $pv_array;

        echo json_encode($result);
    } else {
        unset($result['date']);
        foreach ($date_array as $key=>$value) {
            $result[$key]['dm_date'] = $value['dm_date'];
            $result[$key]['dm_visitor'] = $visitor_array[$key];
            $result[$key]['visit_count'] = $visit_count_array[$key];
            $result[$key]['new_count'] = $new_count_array[$key];
            $result[$key]['re_count'] = $re_count_array[$key];
            $result[$key]['pv'] = $pv_array[$key];
        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }

}

else if ($type == 'all_orgin') {
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    $arDara = array();

    $query = "SELECT sum(dm_count) as `naver` FROM dm_visit_orgin WHERE dm_orgin LIKE '%naver%' $where"; //네이버
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $naver_count = $row['naver'];

    $query = "SELECT sum(dm_count) as `daum` FROM dm_visit_orgin WHERE dm_orgin LIKE '%daum%' $where"; //다음
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $daum_count = $row['daum'];

    $query = "SELECT sum(dm_count) as `google` FROM dm_visit_orgin WHERE dm_orgin LIKE '%google%' $where"; //구글
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $google_count = $row['google'];

    $query = "SELECT sum(dm_count) as `nate` FROM dm_visit_orgin WHERE dm_orgin LIKE '%nate%' $where"; //네이트
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $nate_count = $row['nate'];

    $query = "SELECT sum(dm_count) as `bing` FROM dm_visit_orgin WHERE dm_orgin LIKE '%bing%' $where"; //빙
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $bing_count = $row['bing'];

    $query = "SELECT sum(dm_count) as `etc` FROM dm_visit_orgin WHERE (dm_orgin not LIKE '%bing%' and dm_orgin not LIKE '%nate%' and dm_orgin not LIKE '%google%' and dm_orgin not LIKE '%daum%' and dm_orgin not LIKE '%naver%') $where";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $etc_count = $row['etc'];

    $naver_count = ($naver_count) ? $naver_count : 0;
    $daum_count = ($daum_count) ? $daum_count : 0;
    $google_count = ($google_count) ? $google_count : 0;
    $nate_count = ($nate_count) ? $nate_count : 0;
    $bing_count = ($bing_count) ? $bing_count : 0;
    $etc_count = ($etc_count) ? $etc_count : 0;

    $total = $naver_count + $daum_count + $google_count + $nate_count + $bing_count + $etc_count;

    $temp = array ('네이버' => $naver_count, '다음' => $daum_count, '구글' => $google_count, '네이트' => $nate_count, '빙' => $bing_count, '기타' => $etc_count);
    arsort($temp);

    $i = 0;
    foreach ($temp as $key => $value) {
        $result[$i]['number'] = $i+1;
        $result[$i]['site'] = $key;
        $result[$i]['count'] = $value;
        if ($value) {
            $result[$i]['percent'] = sprintf('%0.2f', $value / $total * 100) . "%";
        } else {
            $result[$i]['percent'] = "0%";
        }
        $i++;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

else if ($type == "weekend_orgin") {
    $naver = array();
    $nate = array();
    $daum = array();
    $google = array();
    $bing = array();
    $etc = array();

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%naver%'"; //네이버
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $naver[] = ($row['count']) ? (int)$row['count'] : 0;
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%nate%'"; //네이트
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $nate[] = ($row['count']) ? (int)$row['count'] : "0";
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%google%'"; //구글
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $google[] = ($row['count']) ? (int)$row['count'] : 0;
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%daum%'"; //다음
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $daum[] = ($row['count']) ? (int)$row['count'] : 0;
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%bing%'"; //bing
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $bing[] = ($row['count']) ? (int)$row['count'] : "0";
            }

            $query = "SELECT sum(dm_count) as `etc` FROM dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and (dm_orgin not LIKE '%bing%' and dm_orgin not LIKE '%nate%' and dm_orgin not LIKE '%google%' and dm_orgin not LIKE '%daum%' and dm_orgin not LIKE '%naver%')";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $etc[] = ($row['etc']) ? (int)$row['etc'] : "0";
            }

            $result['date'][] = ${"day".$i};
        }
    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%naver%'"; //네이버
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $naver[] = ($row['count']) ? (int)$row['count'] : 0;
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%nate%'"; //네이트
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $nate[] = ($row['count']) ? (int)$row['count'] : "0";
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%google%'"; //구글
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $google[] = ($row['count']) ? (int)$row['count'] : 0;
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%daum%'"; //다음
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $daum[] = ($row['count']) ? (int)$row['count'] : 0;
            }

            $query = "select sum(dm_count) as `count` from dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_orgin LIKE '%bing%'"; //bing
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $bing[] = ($row['count']) ? (int)$row['count'] : "0";
            }

            $query = "SELECT sum(dm_count) as `etc` FROM dm_visit_orgin where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and (dm_orgin not LIKE '%bing%' and dm_orgin not LIKE '%nate%' and dm_orgin not LIKE '%google%' and dm_orgin not LIKE '%daum%' and dm_orgin not LIKE '%naver%')";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $etc[] = ($row['etc']) ? (int)$row['etc'] : "0";
            }
        }

        $result['date'] = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }

    if ($type == 'weekend_orgin') {
        $result['naver'] = $naver;
        $result['nate'] = $nate;
        $result['daum'] = $daum;
        $result['google'] = $google;
        $result['bing'] = $bing;
        $result['etc'] = $etc;

        echo json_encode($result);
    }
}

else if ($type == 'orgin_info') {

    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    $query = "select sum(dm_count) as `count` from dm_visit_orgin where (dm_orgin LIKE '%naver%' or dm_orgin LIKE '%nate%' or dm_orgin LIKE '%daum%' or dm_orgin LIKE '%google%' or dm_orgin LIKE '%bing%') $where";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $search_count = ($row['count']) ? $row['count'] : 0;

    $query = "select sum(dm_count) as `count` from dm_visit_orgin WHERE 1 = 1 $where";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $total_count = $row['count'];
    $search_percent = sprintf( "%0.2f", $search_count / $total_count * 100);

    $query = "SELECT sum(dm_count) as `naver` FROM dm_visit_orgin WHERE dm_orgin LIKE '%naver%' $where"; //네이버
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $naver_count = $row['naver'];

    $query = "SELECT sum(dm_count) as `daum` FROM dm_visit_orgin WHERE dm_orgin LIKE '%daum%' $where"; //다음
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $daum_count = $row['daum'];

    $query = "SELECT sum(dm_count) as `google` FROM dm_visit_orgin WHERE dm_orgin LIKE '%google%' $where"; //구글
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $google_count = $row['google'];

    $query = "SELECT sum(dm_count) as `nate` FROM dm_visit_orgin WHERE dm_orgin LIKE '%nate%' $where"; //네이트
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $nate_count = $row['nate'];

    $query = "SELECT sum(dm_count) as `bing` FROM dm_visit_orgin WHERE dm_orgin LIKE '%bing%' $where"; //빙
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $bing_count = $row['bing'];

    $query = "SELECT sum(dm_count) as `etc` FROM dm_visit_orgin WHERE (dm_orgin not LIKE '%bing%' and dm_orgin not LIKE '%nate%' and dm_orgin not LIKE '%google%' and dm_orgin not LIKE '%daum%' and dm_orgin not LIKE '%naver%') $where";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $etc_count = $row['etc'];

    $naver_count = ($naver_count) ? $naver_count : 0;
    $daum_count = ($daum_count) ? $daum_count : 0;
    $google_count = ($google_count) ? $google_count : 0;
    $nate_count = ($nate_count) ? $nate_count : 0;
    $bing_count = ($bing_count) ? $bing_count : 0;
    $etc_count = ($etc_count) ? $etc_count : 0;

    $total = $naver_count + $daum_count + $google_count + $nate_count + $bing_count + $etc_count;

    $temp = array ('네이버' => $naver_count, '다음' => $daum_count, '구글' => $google_count, '네이트' => $nate_count, '빙' => $bing_count, '기타' => $etc_count);
    arsort($temp);

    $i = 0;
    foreach ($temp as $key => $value) {
        if ($value) {
            $res[$i]['percent'] = sprintf('%0.2f', $value / $total * 100);
        } else {
            $res[$i]['percent'] = "0";
        }
        $i++;
    }

    $result['search_count'] = $search_count;
    $result['total_count'] = $total_count;
    $result['search_percent'] = $search_percent;
    $result['top_eng'] = $res[0]['percent'];

    echo json_encode($result);
}

else if ($type == 'get_env') {
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    $query = "SELECT count(*) as `count` FROM dm_visit_env WHERE dm_type = 1 $where"; //모바일
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $mobile_count = $row['count'];

    $query = "SELECT count(*) as `count` FROM dm_visit_env WHERE dm_type = 0 $where"; //pc
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $pc_count = $row['count'];

    $total = $pc_count + $mobile_count;

    $pc_percent = sprintf('%0.2f', $pc_count / $total * 100);
    $mobile_percent = sprintf('%0.2f', $mobile_count / $total * 100);

    $query = "select sum(dm_count) as dm_count, dm_os from dm_visit_env where dm_type = 0 $where group by dm_os order by max(dm_count) desc limit 1";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_pc_count = $row['dm_count'];
    $max_pc_name = $row['dm_os'];

    $query = "select sum(dm_count) as dm_count, dm_os from dm_visit_env where dm_type = 1 $where group by dm_os order by max(dm_count) desc limit 1";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_mobile_count = ($row['dm_count']) ? $row['dm_os'] : 0;
    $max_mobile_name = ($row['dm_os']) ? $row['dm_os'] : "-";

    $result['pc_percent'] = $pc_percent;
    $result['mobile_percent'] = $mobile_percent;
    $result['max_pc_count'] = $max_pc_count;
    $result['max_pc_name'] = $max_pc_name;
    $result['max_mobile_count'] = $max_mobile_count;
    $result['max_mobile_name'] = $max_mobile_name;

    echo json_encode($result);

}

else if ($type == "weekend_env") {
    $naver = array();
    $nate = array();
    $daum = array();
    $google = array();
    $bing = array();
    $etc = array();
    $os_array = array();

    $query = "select DISTINCT dm_os from dm_visit_env";
    $db->ExecSql($query, "S");

    while ($row=$db->Fetch()) {
        $os_array[] = $row;
    }

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));
            foreach ($os_array as $value) {
                $query = "select sum(dm_count) as `count` from dm_visit_env where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_os LIKE '%".$value['dm_os']."%'";
                $db->ExecSql($query, "S");
                $row = $db->Fetch();
                $result['os'][$value['dm_os']]['count'][] = ($row['count']) ? (int)$row['count'] : 0;
            }
            $result['date'][] = ${"day".$i};
        }
    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            foreach ($os_array as $value) {
                $query = "select sum(dm_count) as `count` from dm_visit_env where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_os LIKE '%".$value['dm_os']."%'";
                $db->ExecSql($query, "S");
                $row = $db->Fetch();
                $result['os'][$value['dm_os']]['count'][] = ($row['count']) ? (int)$row['count'] : 0;
            }
        }

        $result['date'] = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }

    echo json_encode($result);

}

else if ($type == 'weekend_env_table') {
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    $os_array = array();
    $res = array();
    $total = 0;

    $query = "select DISTINCT dm_os from dm_visit_env where 1 =1 $where";
    $db->ExecSql($query, "S");

    while ($row=$db->Fetch()) {
        $os_array[] = $row;
    }

    foreach ($os_array as $key => $value) {
        $query = "select sum(dm_count) as `count` from dm_visit_env WHERE dm_os LIKE '%".$value['dm_os']."%' $where";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $res['os'][$value['dm_os']] = $row['count'];

        if ($row['count']) {
            $total += $row['count'];
        }
    }

    arsort($res['os']);

    $i = 0;
    foreach ($res['os'] as $key => $value) {
        $result[$i]['number'] = ($i+1);
        $result[$i]['dm_os'] = $key;
        $result[$i]['count'] = $value;
        if ($value) {
            $result[$i]['percent'] = sprintf('%0.2f', $value / $total * 100) . "%";
        } else {
            $result[$i]['percent'] = "0%";
        }
        $i++;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

else if ($type == "weekend_env_browser") {
    $os_array = array();

    $query = "select DISTINCT dm_brower from dm_visit_env";
    $db->ExecSql($query, "S");

    while ($row=$db->Fetch()) {
        $os_array[] = $row;
    }

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));
            foreach ($os_array as $value) {
                $query = "select sum(dm_count) as `count` from dm_visit_env where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_brower LIKE '%".$value['dm_brower']."%'";
                $db->ExecSql($query, "S");
                $row = $db->Fetch();
                $result['browser'][$value['dm_brower']]['count'][] = ($row['count']) ? (int)$row['count'] : 0;
            }
            $result['date'][] = ${"day".$i};
        }
    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            foreach ($os_array as $value) {
                $query = "select sum(dm_count) as `count` from dm_visit_env where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_brower LIKE '%".$value['dm_brower']."%'";
                $db->ExecSql($query, "S");
                $row = $db->Fetch();
                $result['browser'][$value['dm_brower']]['count'][] = ($row['count']) ? (int)$row['count'] : 0;
            }
        }

        $result['date'] = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }

    echo json_encode($result);
}

else if ($type == 'weekend_env_browser_table') {
    $os_array = array();
    $res = array();
    $total = 0;
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    $query = "select DISTINCT dm_brower from dm_visit_env where 1=1 $where";
    $db->ExecSql($query, "S");

    while ($row=$db->Fetch()) {
        $os_array[] = $row;
    }

    foreach ($os_array as $key => $value) {
        $query = "select sum(dm_count) as `count` from dm_visit_env WHERE dm_brower LIKE '%".$value['dm_brower']."%' $where";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $res['browser'][$value['dm_brower']] = $row['count'];

        if ($row['count']) {
            $total += $row['count'];
        }
    }

    arsort($res['browser']);

    $i = 0;
    foreach ($res['browser'] as $key => $value) {
        $result[$i]['number'] = ($i+1);
        $result[$i]['browser'] = $key;
        $result[$i]['count'] = $value;
        if ($value) {
            $result[$i]['percent'] = sprintf('%0.2f', $value / $total * 100) . "%";
        } else {
            $result[$i]['percent'] = "0%";
        }
        $i++;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

else if ($type == "day_member")
{
    $yoil = array ("일요일", "월요일",  "화요일",  "수요일",  "목요일",  "금요일",  "토요일" );

    $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = CURRENT_DATE()) as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = CURRENT_DATE() and dm_join_os = 0) as `pc_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = CURRENT_DATE() and dm_join_os = 1) as `mobile_count`";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $total = $row['total_count'];
    $pc_count = $row['pc_count'];
    $mobile_count = $row['mobile_count'];

    $query = "SELECT count(*) AS `count`, date_format(dm_datetime, '%Y-%m-%d %H') as `dm_datetime`, case WHEN DAYOFWEEK(dm_datetime) = 1 THEN '일요일' WHEN DAYOFWEEK(dm_datetime) = 2 THEN '월요일' WHEN DAYOFWEEK(dm_datetime) = 3 THEN '화요일' WHEN DAYOFWEEK(dm_datetime) = 4 THEN '수요일' WHEN DAYOFWEEK(dm_datetime) = 5 THEN '목요일' WHEN DAYOFWEEK(dm_datetime) = 6 THEN '금요일' WHEN DAYOFWEEK(dm_datetime) = 7 THEN '토요일' END as `max_yoil` FROM dm_member WHERE dm_id <> 'admin' GROUP BY date_format(dm_datetime, '%Y-%m-%d') HAVING count(*) = ( SELECT max(mycount) FROM ( SELECT dm_datetime, count(*) AS mycount FROM dm_member GROUP BY date_format(dm_datetime, '%Y-%m-%d')) as b)";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_count = $row['count'];
    $max_date = $row['dm_datetime'];
    $max_yoil = $row['max_yoil'];

    $query = "SELECT count(*) AS `count`, date_format(dm_datetime, '%Y-%m-%d %H')  as `dm_datetime`, case WHEN DAYOFWEEK(dm_datetime) = 1 THEN '일요일' WHEN DAYOFWEEK(dm_datetime) = 2 THEN '월요일' WHEN DAYOFWEEK(dm_datetime) = 3 THEN '화요일' WHEN DAYOFWEEK(dm_datetime) = 4 THEN '수요일' WHEN DAYOFWEEK(dm_datetime) = 5 THEN '목요일' WHEN DAYOFWEEK(dm_datetime) = 6 THEN '금요일' WHEN DAYOFWEEK(dm_datetime) = 7 THEN '토요일' END as `min_yoil` FROM dm_member WHERE dm_id <> 'admin' GROUP BY date_format(dm_datetime, '%Y-%m-%d') HAVING count(*) = ( SELECT min(mycount) FROM ( SELECT dm_datetime, count(*) AS mycount FROM dm_member GROUP BY date_format(dm_datetime, '%Y-%m-%d')) as b)";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_count = $row['count'];
    $min_date = $row['dm_datetime'];
    $min_yoil = $row['min_yoil'];

    $result['total_count'] = $total;
    $result['pc_count'] = $pc_count;
    $result['mobile_count'] = $mobile_count;
    $result['max_count'] = $max_count;
    $result['max_date'] = $max_date;
    $result['min_count'] = $min_count;
    $result['min_date'] = $min_date;
    $result['max_yoil'] = $max_yoil;
    $result['min_yoil'] = $min_yoil;

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

else if ($type == "day_member_chart" || $type == "day_member_table")
{
    $pc_count_array = array();
    $mobile_count_array = array();
    $total_count_array = array();
    $percent_count_array = array();

    $total = 0;

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));
            $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_join_os = 0) as `pc_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_join_os = 1) as `mobile_count`";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $pc_count_array[] = $row['pc_count'];
            $mobile_count_array[] = $row['mobile_count'];
            $total_count_array[] = $row['total_count'];
            $total += $row['total_count'];
            $result['date'][] = ${"day".$i};
        }
    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_join_os = 0) as `pc_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_join_os = 1) as `mobile_count`";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $pc_count_array[] = $row['pc_count'];
            $mobile_count_array[] = $row['mobile_count'];
            $total_count_array[] = $row['total_count'];
            $total += $row['total_count'];
        }

        foreach ($total_count_array as $value) {
            if ($value) {
                $percent_count_array[] = sprintf('%0.2f', $value / $total * 100)."%";
            } else {
                $percent_count_array[] = "0%";
            }
        }
        $result['date'] = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }

    if ($type == 'day_member_chart') {
        $result['pc_count'] = $pc_count_array;
        $result['mobile_count'] = $mobile_count_array;
        $result['total_count'] = $total_count_array;
        $result['percent'] = $percent_count_array;

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    } else {
        foreach ($pc_count_array as $key=>$value) {
            $result[$key]['dm_date'] = $result['date'][$key];
            $result[$key]['pc_count'] = $value;
            $result[$key]['mobile_count'] = $mobile_count_array[$key];
            $result[$key]['total_count'] = $total_count_array[$key];
            $result[$key]['percent'] = $percent_count_array[$key];
        }
        unset($result['date']);
        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);
        echo json_encode($arResult);
    }
}

else if ($type == 'time_member_chart' || $type == 'time_member_table') {
    $j = 0;
    $total = 0;
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    for ($i=0; $i<24; $i++) {
        $tt = str_pad($i,"2","0",STR_PAD_LEFT);
//        $search_time = date("Y-m-d")." ".$tt;
        $search_time = $tt;

        $query = "SELECT
        (select count(*) from dm_member where date_format(dm_datetime, '%H') = '".$search_time."' $where) as `total_count`,
        (select count(*) from dm_member where date_format(dm_datetime, '%H') = '".$search_time."' and dm_join_os = 0 $where) as `pc_count`,
        (select count(*) from dm_member where date_format(dm_datetime, '%H') = '".$search_time."' and dm_join_os = 1 $where) as `mobile_count`
        FROM
            dm_member as v";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();


        if ($row) {
            $arReturn[$j]['total_count'] = ($row['total_count']) ? $row['total_count'] : 0;
            $arReturn[$j]['pc_count'] = ($row['pc_count']) ? $row['pc_count'] : 0;
            $arReturn[$j]['mobile_count'] = ($row['mobile_count']) ? $row['mobile_count'] : 0;
            $arReturn[$j]['dm_time'] = $tt.":00";
        } else {
            $arReturn[$j]['dm_time'] = $tt.":00";
            $arReturn[$j]['total_count'] = 0;
            $arReturn[$j]['pc_count'] = 0;
            $arReturn[$j]['mobile_count'] = 0;
            $arReturn[$j]['dm_page_view'] = 0;
        }
        $j++;
        $total += $row['total_count'];
    }

    if ($type == 'time_member_table') {

        $k = 0;
        foreach ($arReturn as $value) {
            $result[$k] = sprintf('%0.2f', $value['total_count'] / $total * 100) . "%";
            $k++;
        }

        $j = 0;
        foreach ($result as $value) {
            $arReturn[$j]['percent'] = $value;
            $j++;
        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    } else {

        $total_array = array();
        $pc_array = array();
        $mobile_array = array();

        foreach ($arReturn as $key => $value) {
            $total_array[] = $value['total_count'];
            $pc_array[] = $value['pc_count'];
            $mobile_array[] = $value['mobile_count'];

        }
        $result['total_count'] = $total_array;
        $result['pc_count'] = $pc_array;
        $result['mobile_count'] = $mobile_array;

        echo json_encode($result);
    }

}

else if ($type == 'week_member_chart' || $type == 'week_member_table') {
    $total_array = array();
    $pc_array = array();
    $mobile_array = array();
    $result = array();
    $date_array = array("일요일", "월요일", "화요일", "수요일","목요일", "금요일", "토요일" );
    $total = 0;

    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    for ($i=1; $i<=7; $i++) {
        $query = "select  (select count(*) from dm_member where DAYOFWEEK(dm_datetime) = $i $where) as `total_count`,
        (select count(*) from dm_member where DAYOFWEEK(dm_datetime) = $i and dm_join_os = 0 $where) as `pc_count`,
        (select count(*) from dm_member where DAYOFWEEK(dm_datetime) = $i and dm_join_os = 1 $where) as `mobile_count` from dm_member"; //요일 방문자수
        $db->ExecSql($query, "S");
        $row = $db->Fetch();

        $total_array[] = $row['total_count'];
        $pc_array[] = $row['pc_count'];
        $mobile_array[] = $row['mobile_count'];
        $total += $row['total_count'];
    }

    if ($type == 'week_member_chart') {
        $result['total'] = $total_array;
        $result['pc'] = $pc_array;
        $result['mobile'] = $mobile_array;

        echo json_encode($result);
    } else {
        foreach ($date_array as $key=>$value) {
            $result[$key]['dm_date'] = $value;
            $result[$key]['total'] = $total_array[$key];
            $result[$key]['pc'] = $pc_array[$key];
            $result[$key]['mobile'] = $mobile_array[$key];
            if ($total_array[$key]) {
                $result[$key]['pv'] = sprintf('%0.2f',$total_array[$key] / $total * 100)."%";
            } else {
                $result[$key]['pv'] = "0%";
            }

        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }
}

else if ($type == 'month_member') {
    $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = date_format(now(), '%Y-%m')) as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = date_format(now(), '%Y-%m') and dm_join_os = 0) as `pc_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = date_format(now(), '%Y-%m') and dm_join_os = 1) as `mobile_count`";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $total = $row['total_count'];
    $pc_count = $row['pc_count'];
    $mobile_count = $row['mobile_count'];

    $query = "SELECT count(*) AS `count`, date_format(dm_datetime, '%Y-%m') AS `dm_datetime` FROM dm_member WHERE dm_id <> 'admin' GROUP BY date_format(dm_datetime, '%Y-%m') HAVING count(*) = (SELECT max(mycount) FROM ( SELECT dm_datetime, count(*) AS mycount FROM dm_member GROUP BY date_format(dm_datetime, '%Y-%m')) AS b)";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $max_count = $row['count'];
    $max_date = $row['dm_datetime'];

    $query = "SELECT count(*) AS `count`, date_format(dm_datetime, '%Y-%m') AS `dm_datetime` FROM dm_member WHERE dm_id <> 'admin' GROUP BY date_format(dm_datetime, '%Y-%m') HAVING count(*) = (SELECT min(mycount) FROM ( SELECT dm_datetime, count(*) AS mycount FROM dm_member GROUP BY date_format(dm_datetime, '%Y-%m')) AS b)";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $min_count = ($row['count']) ? $row['count'] : 0;
    $min_date = $row['dm_datetime'];

    $result['total_count'] = $total;
    $result['pc_count'] = $pc_count;
    $result['mobile_count'] = $mobile_count;
    $result['max_count'] = $max_count;
    $result['max_date'] = $max_date;
    $result['min_count'] = $min_count;
    $result['min_date'] = $min_date;

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'month_member_chart' || $type == 'month_member_table') {
    $date_array = array();
    $total = array();
    $pc_count = array();
    $mobile_count = array();
    $date = array();
    $total_count = 0;

    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m') <= '".date("Y-m", strtotime($search_end_date))."'";
        }
    }

    $query = "SELECT date_format(dm_datetime, '%Y-%m') as dm_date FROM dm_member where dm_id <> 'admin' $where GROUP BY date_format(dm_datetime, '%Y-%m')";
    $db->ExecSql($query, "S");
    while($row = $db->Fetch()) {
        $date_array[] = $row;
    }

    foreach ($date_array as $value) {
        $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."') as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_join_os = 0) as `pc_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_join_os = 1) as `mobile_count`";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $total[] = $row['total_count'];
        $pc_count[] = $row['pc_count'];
        $mobile_count[] = $row['mobile_count'];
        $date[] = $value['dm_date'];
        $total_count += $row['total_count'];
    }

    if ($type == 'month_member_chart') {
        $result['date'] = $date;
        $result['total'] = $total;
        $result['pc'] = $pc_count;
        $result['mobile'] = $mobile_count;

    } else {
        foreach ($total as $key => $value) {
            $result[$key]['total'] = $value;
            $result[$key]['pc'] = $pc_count[$key];
            $result[$key]['mobile'] = $mobile_count[$key];
            $result[$key]['date'] = $date[$key];
            if ($value) {
                $result[$key]['avg'] = sprintf('%0.2f',$value / $total_count * 100)."%";
            } else {
                $result[$key]['avg'] = "0%";
            }
        }
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'all_day_member') {
    $query = "SELECT (select count(*) from dm_member where dm_id <> 'admin') as total_count, (select count(*) from dm_member where dm_datetime >= date_add(now(), interval -1 month)) as new_count, (select count(*) from dm_member where dm_intercept_date <> '' ) as intercept_count, (select count(*) from dm_member where dm_leave_date <> '' ) as leave_count";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    echo json_encode($row);

}

else if ($type == 'all_day_member_chart' || $type == 'all_day_member_table') {
    $total = array();
    $leave = array();
    $intercept = array();

    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));
            $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_leave_date <> '') as `leave_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_intercept_date <> '') as `intercept_count`";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $total[] = $row['total_count'];
            $leave[] = $row['leave_count'];
            $intercept[] = $row['intercept_count'];
            $result['date'][] = ${"day".$i};
        }
    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_leave_date <> '') as `leave_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' and dm_intercept_date <> '') as `intercept_count`";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $total[] = $row['total_count'];
            $leave[] = $row['leave_count'];
            $intercept[] = $row['intercept_count'];
        }
        $result['date'] = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }



    if ($type == 'all_day_member_chart') {
        $result['total'] = $total;
        $result['leave'] = $leave;
        $result['intercept'] = $intercept;
    } else {
        foreach ($result['date'] as $key => $value) {
            $result[$key]['date'] = $value;
            $result[$key]['leave'] = $leave[$key];
            $result[$key]['intercept'] = $intercept[$key];
            $result[$key]['total'] = $total[$key];
        }
        unset($result['date']);
    }
    echo json_encode($result);
}

else if ($type == 'all_month_member_chart' || $type == 'all_month_member_table') {
    $total = array();
    $leave = array();
    $intercept = array();
    $date_array = array();
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m') <= '".date("Y-m", strtotime($search_end_date))."'";
        }
    }

    $query = "SELECT date_format(dm_datetime, '%Y-%m') as dm_date FROM dm_member where dm_id <> 'admin' $where GROUP BY date_format(dm_datetime, '%Y-%m')";
    $db->ExecSql($query, "S");
    while($row = $db->Fetch()) {
        $date_array[] = $row;
    }

    foreach ($date_array as $value) {
        $query = "select (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_id <> 'admin') as `total_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_leave_date <> '') as `leave_count`, (select count(*) from dm_member where date_format(dm_datetime, '%Y-%m') = '".$value['dm_date']."' and dm_intercept_date <> '') as `intercept_count`";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        $total[] = $row['total_count'];
        $leave[] = $row['leave_count'];
        $intercept[] = $row['intercept_count'];
        $date[] = $value['dm_date'];
    }

    if ($type == 'all_month_member_chart') {
        $result['date'] = $date;
        $result['total'] = $total;
        $result['leave'] = $leave;
        $result['intercept'] = $intercept;

    } else {
        foreach ($total as $key => $value) {
            $result[$key]['total'] = $value;
            $result[$key]['leave'] = $leave[$key];
            $result[$key]['intercept'] = $intercept[$key];
            $result[$key]['date'] = $date[$key];
        }
    }

    echo json_encode($result);
}

else if ($type == 'all_sex_member') {

    $query = "SELECT (select count(*) from dm_member where dm_id <> 'admin') as total_count, (select count(*) from dm_member where dm_sex = 'M'  AND dm_id <>'admin') as man_count, (select count(*) from dm_member where dm_sex = 'F'  AND dm_id <>'admin' ) as woman_count, (select count(*) from dm_member where (dm_sex <> 'F' AND dm_sex <> 'M') AND dm_id <>'admin') as undefined_count";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    if ($row['man_count']) {
        $man_percent = sprintf('%0.2f',$row['man_count'] / $row['total_count'] * 100)."%";
    } else {
        $man_percent = "0%";
    }

    if ($row['woman_count']) {
        $woman_percent = sprintf('%0.2f',$row['woman_count'] / $row['total_count'] * 100)."%";
    } else {
        $woman_percent = "0%";
    }

    if ($row['undefined_count']) {
        $undefined_percent = sprintf('%0.2f',$row['undefined_count'] / $row['total_count'] * 100)."%";
    } else {
        $undefined_percent = "0%";
    }

    $row['undefined_percent'] = $undefined_percent;
    $row['man_percent'] = $man_percent;
    $row['woman_percent'] = $woman_percent;

    echo json_encode($row);
}

else if ($type == 'all_sex_member_chart' || $type == 'all_sex_member_table') {
    $where = "";

    $current_date = date("Y-m-d");
    $man_array = array();
    $total_array = array();
    $woman_array = array();
    $undefined_array = array();
    $date = array();
    $total = 0;

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));
            $tt = str_pad($i,"2","0",STR_PAD_LEFT);

            $query = "SELECT (SELECT count(*) FROM dm_member where dm_id <> 'admin' and date_format(dm_datetime, '%Y-%m-%d') <= '".${"day".$i}."') as total_count, 
        (select count(*) from dm_member where dm_sex = 'M'  AND dm_id <>'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as man_count, 
        (select count(*) from dm_member where dm_sex = 'F'  AND dm_id <>'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as woman_count, 
        (select count(*) from dm_member where (dm_sex <> 'F' AND dm_sex <> 'M') AND dm_id <>'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."') as undefined_count";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $total_array[] = $row['total_count'];
            $woman_array[] = $row['woman_count'];
            $man_array[] = $row['man_count'];
            $undefined_array[] = $row['undefined_count'];
            $date[] =${"day".$i};
            $total += $row['total_count'];
        }

    } else {
        for ($i=1; $i<31; $i++) {
            $tt = str_pad($i,"2","0",STR_PAD_LEFT);
            if (date("Y-m")."-".$tt > $current_date) break;
            $query = "SELECT (SELECT count(*) FROM dm_member where dm_id <> 'admin' and date_format(dm_datetime, '%Y-%m-%d') <= '".date("Y-m")."-".$tt."') as total_count, 
        (select count(*) from dm_member where dm_sex = 'M'  AND dm_id <>'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".date("Y-m")."-".$tt."') as man_count, 
        (select count(*) from dm_member where dm_sex = 'F'  AND dm_id <>'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".date("Y-m")."-".$tt."') as woman_count, 
        (select count(*) from dm_member where (dm_sex <> 'F' AND dm_sex <> 'M') AND dm_id <>'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".date("Y-m")."-".$tt."') as undefined_count";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $total_array[] = $row['total_count'];
            $woman_array[] = $row['woman_count'];
            $man_array[] = $row['man_count'];
            $undefined_array[] = $row['undefined_count'];
            $date[] = date("Y-m")."-".$tt;
            $total += $row['total_count'];
        }
    }

    if ($type == 'all_sex_member_chart') {
        $result['date'] = $date;
        $result['total_count'] = $total_array;
        $result['woman_count'] = $woman_array;
        $result['man_count'] = $man_array;
        $result['undefined_count'] = $undefined_array;

        echo json_encode($result);
    } else {

        foreach ($date as $key => $value) {
            $result[$key]['date'] = $value;
            $result[$key]['total'] = $total_array[$key];
            $result[$key]['man_count'] = $man_array[$key];
            $result[$key]['woman_count'] = $woman_array[$key];
            $result[$key]['undefined_count'] = $undefined_array[$key];
            if ($total_array[$key]) {
                $result[$key]['pv'] = sprintf('%0.2f',$man_array[$key] / $total * 100)."%";
            } else {
                $result[$key]['pv'] = "0%";
            }
        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }
}

else if ($type == 'all_age_member') {
    $query = "SELECT floor((YEAR(now()) - YEAR(dm_birth)) / 10) * 10 age, count(dm_birth) AS cnt FROM dm_member a WHERE dm_birth != '' AND dm_id <> 'admin' GROUP BY age";
    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $arData[] = $row;
    }

    $query = "SELECT count(*) as `total` FROM dm_member WHERE dm_id <> 'admin'";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $total = $row['total'];

    $query = "SELECT count(*) as `total` FROM dm_member WHERE dm_id <> 'admin' and dm_birth = ''";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    $undefined_count = $row['total'];

    foreach ($arData as $key=>$value) {
        $result['age'.$value['age']] = $value['cnt'];
        if ($value['cnt']) {
            $result['age'.$value['age'].'_avg'] = sprintf('%0.2f',$value['cnt'] / $total * 100)."%";
        } else {
            $result['age'.$value['age'].'_avg'] = "0%";
        }
    }
    $result['undefined_count'] = $undefined_count;
    if ($undefined_count) {
        $result['undefined_avg'] = sprintf('%0.2f',$undefined_count / $total * 100)."%";
    } else {
        $result['undefined_avg'] = "0%";
    }
    $result['total'] = $total;

    echo json_encode($result);
}

else if ($type == 'all_age_member_chart'|| $type == 'all_age_member_table') {
    $age10 = array();
    $age20 = array();
    $age30 = array();
    $age40 = array();
    $age50 = array();
    $age60 = array();
    $age70 = array();
    $total = array();
    $undefined = array();

    if ($search_start_date != $search_end_date) {
        $firstDate  = new DateTime($search_start_date);
        $secondDate = new DateTime($search_end_date);
        $intvl = $firstDate->diff($secondDate);

        for ($i=0; $i<=$intvl->days; $i++) {
            if ($i == 0) $day0 = date("Y-m-d", strtotime($search_start_date));
            else ${"day".$i} = date("Y-m-d", strtotime($day0 . "+".$i." day"));
            $query = "SELECT sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 10 and 19 , 1, 0)) as age_10,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 20 and 29 , 1, 0)) as age_20,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 30 and 39 , 1, 0)) as age_30,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 40 and 49 , 1, 0)) as age_40,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 50 and 59 , 1, 0)) as age_50,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 60 and 69 , 1, 0)) as age_60,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 60 and 69 , 1, 0)) as age_70 FROM dm_member a WHERE dm_birth != '' AND dm_id <> 'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."'";

            $db->ExecSql($query, "S");

            while($row = $db->Fetch()) {
                $age10[] = ($row['age_10']) ? $row['age_10'] : 0;
                $age20[] = ($row['age_20']) ? $row['age_20'] : 0;
                $age30[] = ($row['age_30']) ? $row['age_30'] : 0;
                $age40[] = ($row['age_40']) ? $row['age_40'] : 0;
                $age50[] = ($row['age_50']) ? $row['age_50'] : 0;
                $age60[] = ($row['age_60']) ? $row['age_60'] : 0;
                $age70[] = ($row['age_70']) ? $row['age_70'] : 0;
            }

            $query = "SELECT count(*) as `total` FROM dm_member WHERE dm_id <> 'admin' and dm_birth = '' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."'";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $undefined[] = $row['total'];
            }

            $query = "SELECT count(*) as `total` FROM dm_member WHERE dm_id <> 'admin' and date_format(dm_datetime, '%Y-%m-%d') <= '".${"day".$i}."'";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $total[] = $row['total'];
            }
            $date[] = ${"day".$i};
        }

    } else {
        $day0 = date("Y-m-d");
        $day1 = date("Y-m-d", strtotime($day0 . "-1 day"));
        $day2 = date("Y-m-d", strtotime($day0 . "-2 day"));
        $day3 = date("Y-m-d", strtotime($day0 . "-3 day"));
        $day4 = date("Y-m-d", strtotime($day0 . "-4 day"));
        $day5 = date("Y-m-d", strtotime($day0 . "-5 day"));
        $day6 = date("Y-m-d", strtotime($day0 . "-6 day"));

        for ($i=6; $i>=0; $i--) {
            $query = "SELECT sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 10 and 19 , 1, 0)) as age_10,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 20 and 29 , 1, 0)) as age_20,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 30 and 39 , 1, 0)) as age_30,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 40 and 49 , 1, 0)) as age_40,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 50 and 59 , 1, 0)) as age_50,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 60 and 69 , 1, 0)) as age_60,
sum(if(date_format(now(),'%Y') - substring(dm_birth,1,4) between 60 and 69 , 1, 0)) as age_70 FROM dm_member a WHERE dm_birth != '' AND dm_id <> 'admin' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."'";

            $db->ExecSql($query, "S");

            while($row = $db->Fetch()) {
                $age10[] = ($row['age_10']) ? $row['age_10'] : 0;
                $age20[] = ($row['age_20']) ? $row['age_20'] : 0;
                $age30[] = ($row['age_30']) ? $row['age_30'] : 0;
                $age40[] = ($row['age_40']) ? $row['age_40'] : 0;
                $age50[] = ($row['age_50']) ? $row['age_50'] : 0;
                $age60[] = ($row['age_60']) ? $row['age_60'] : 0;
                $age70[] = ($row['age_70']) ? $row['age_70'] : 0;
            }

            $query = "SELECT count(*) as `total` FROM dm_member WHERE dm_id <> 'admin' and dm_birth = '' and date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."'";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $undefined[] = $row['total'];
            }

            $query = "SELECT count(*) as `total` FROM dm_member WHERE dm_id <> 'admin' and date_format(dm_datetime, '%Y-%m-%d') <= '".${"day".$i}."'";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            if ($row) {
                $total[] = $row['total'];
            }
        }

        $date = [$day6, $day5, $day4, $day3, $day2, $day1, $day0];
    }

    if ($type == 'all_age_member_chart') {
        $result['date'] = $date;
        $result['age10'] = $age10;
        $result['age20'] = $age20;
        $result['age30'] = $age30;
        $result['age40'] = $age40;
        $result['age50'] = $age50;
        $result['age60'] = $age60;
        $result['age70'] = $age70;

        $result['undefined_count'] = $undefined;
        $result['total'] = $total;

        echo json_encode($result);
    } else {

        foreach ($date as $key => $value) {
            $result[$key]['date'] = $value;
            $result[$key]['total_count'] = $total[$key];
            $result[$key]['age10'] = $age10[$key];
            $result[$key]['age20'] = $age20[$key];
            $result[$key]['age30'] = $age30[$key];
            $result[$key]['age40'] = $age40[$key];
            $result[$key]['age50'] = $age50[$key];
            $result[$key]['age60'] = $age60[$key];
            $result[$key]['age70'] = $age70[$key];
            $result[$key]['undefined_count'] = $undefined[$key];
        }

        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);

        echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
    }

}

else if ($type == 'page_view') {
    $total = 0;
    $where = "";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') >= '{$search_start_date}'";
        if ($search_end_date) {
            $where .= " AND date_format(`dm_datetime`, '%Y-%m-%d') <= '".date("Y-m-d", strtotime($search_end_date))."'";
        }
    }

    $query ="select *, count(*) as page_count  from dm_web_log where (dm_type <> '관리자페이지' and dm_type != '') $where group by dm_type order by page_count desc";
    $db->ExecSql($query, "S");
    while ($row = $db->Fetch()) {
        $arData[] = $row;
        $total += $row['page_count'];
    }

    foreach ($arData as $key=>$value) {
        if ($value['page_count']) {
            $value['avg'] = sprintf('%0.2f',$value['page_count'] / $total * 100)."%";
        } else {
            $value['avg'] = "0%";
        }
        $value['number'] = ($key+1);
        $result[] = $value;

    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $result);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}

else if ($type == "today_accessor")
{
    $returnArray = array();
    $current_date = date("Y-m-d");
    $current_month = date("m");
    $month_count = 0;
    $search_count = 0;

    $query = "SELECT dm_count FROM dm_visit WHERE dm_datetime = '".$current_date."' AND dm_type = 'page'";

    $db->ExecSql($query, "S");

    $current_date_count = $db->Fetch();

    if ($current_date_count)
    {
        $returnArray['current_date_count'] = $current_date_count['dm_count'];
    }
    else
    {
        $returnArray['current_date_count'] = 0;
    }


    $query = "SELECT dm_count FROM dm_visit WHERE MONTH(dm_datetime) = '".$current_month."' AND dm_type = 'page'";

    $db->ExecSql($query, "S");

    while ($row = $db->Fetch())
    {
        $month_count += $row['dm_count'];
    }

    $returnArray['current_month'] = $month_count;

    $where = "WHERE 1 = 1 AND dm_type = 'page'";
    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where .= " AND `dm_datetime` >= '{$search_start_date}'";

        if ($search_end_date) {
            $where .= " AND dm_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
        }

        $query = "SELECT * FROM dm_visit $where";

        $db->ExecSql($query, "S");

        while ($row = $db->Fetch())
        {
            $search_count += $row['dm_count'];
        }
    }

    $returnArray['search_count'] = $search_count;

    echo json_encode($returnArray, JSON_UNESCAPED_UNICODE);
}


else if ($type == "select_board")
{
    $arReturn = array();

    $db2 = new DBSQL();
    $db2->DBconnect();
    $where = " WHERE (dm_table <> 'doctors' AND dm_table <> 'nonsalary' AND dm_table <> 'operation_faq')";
    $where2 = " WHERE wr_is_comment = 0";
    $where3 = " WHERE 1 = 1";
    $total_write_count = 0;
    $total_access_count = 0;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";

    if ($search_type != "") {
        if ($search_type == "all") {
            $where .= " AND (dm_subject LIKE '%".$search_value."%')";
        } else {
            $where .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where2 .= " AND `wr_datetime` >= '{$search_start_date}'";
        $where3 .= " AND `dm_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where2 .= " AND `wr_datetime` <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
        $where3 .= " AND `dm_datetime` <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    $query = "SELECT count(*) FROM dm_board  $where";

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $query = "SELECT * FROM dm_board $where";

    $db->ExecSql($query, "S");

    //전체 글 등록수
    while ($row = $db->Fetch()) {
        $dm_table = "dm_write_" . $row['dm_table'];

        $query = "SELECT count(*) as write_count FROM $dm_table $where2";
        $db2->ExecSql($query, "S");
        $write_count = $db2->Fetch();
        $total_write_count = $total_write_count + $write_count['write_count'];

        $query = "SELECT sum(dm_count) as board_count FROM dm_visit $where3 AND dm_type = '".$row['dm_table']."'";
        $db2->ExecSql($query, "S");
        $access_count = $db2->Fetch();
        if (!$access_count)
            $access_count['board_count'] = 0;

        $total_access_count = $total_access_count + $access_count['board_count'];

    }

    $query = "SELECT * FROM dm_board $where";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        $dm_table = "dm_write_".$arData['dm_table'];
        $arData['dm_write_ratio'] = 0;
        $dm_write_count = 0;
        $dm_access_count = 0;

        $query = "SELECT count(*) as write_count FROM $dm_table $where2";
        $db2->ExecSql($query, "S");
        $write_count = $db2->Fetch();

        if ($total_write_count != 0)
            $dm_write_count = round(($write_count['write_count'] / $total_write_count * 100), 2);

        $arData['dm_write_count'] = $write_count['write_count'];
        $arData['dm_write'] = $dm_write_count;

        $query = "SELECT sum(dm_count) as board_count FROM dm_visit $where3 AND dm_type = '".$arData['dm_table']."'";
        $db2->ExecSql($query, "S");
        $access_count = $db2->Fetch();

        if (!$access_count['board_count']) {
            $access_count['board_count'] = 0;
        }
        if ($total_access_count != 0)
            $dm_access_count = round(($access_count['board_count'] / $total_access_count * 100), 2);

        $arData['dm_accessor_count'] = $access_count['board_count'];
        $arData['dm_accessor'] = $dm_access_count;

        if ($arData['dm_accessor_count'] != 0 && $arData['dm_write_count'] != 0)
            $arData['dm_write_ratio'] = round(($arData['dm_write_count'] / $arData['dm_accessor_count'] * 100));

        $arReturn[] = $arData;

    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "dash_board") {
    $yester_day = date("Y-m-d", strtotime("- 1 day"));
    $month = date("Y-m", strtotime("- 1 month"));

    $query = "SELECT 
      (SELECT sum(dm_count) FROM dm_visit WHERE date_format(dm_datetime, '%Y-%m-%d') = '".$yester_day."')  as `today` ,
      (SELECT count(*) FROM dm_member WHERE dm_id <> 'admin' AND dm_level < 6 AND date_format(dm_datetime, '%Y-%m') = '".$month."') as member_count,
      (SELECT count(*) FROM dm_member WHERE dm_id <> 'admin' AND dm_group_id = 'GROUP_0000000001' AND dm_3 = '2' AND date_format(dm_datetime, '%Y-%m') = '".$month."') as business_count,
      (SELECT count(*) FROM dm_member WHERE dm_id <> 'admin' AND dm_group_id = 'GROUP_0000000001' AND dm_3 = '3' AND date_format(dm_datetime, '%Y-%m') = '".$month."') as expert_count
      ";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();

    $query = "SELECT 
      (SELECT sum(dm_count) FROM dm_visit WHERE date_format(dm_datetime, '%Y-%m-%d') = CURRENT_DATE())  as `today` , 
      (SELECT count(*) FROM dm_member WHERE dm_id <> 'admin' AND dm_level < 6 )  as member_count,
      (SELECT count(*) FROM dm_member WHERE dm_id <> 'admin' AND dm_group_id = 'GROUP_0000000001' AND dm_3 = '2') as business_count,
      (SELECT count(*) FROM dm_member WHERE dm_id <> 'admin' AND dm_group_id = 'GROUP_0000000001' AND dm_3 = '3') as expert_count
      ";
    $db->ExecSql($query, "S");
    $today = $db->Fetch();

    if ($row['today'] > 0) {
        $today['today_compare'] = round($today['today'] / $row['today'] * 100, "2");
    } else {
        $today['today_compare'] = 0;
    }

    if ($row['business_count'] > 0) {
        $today['business_compare'] = round($today['business_count'] / $row['business_count'] * 100, "2");
    } else {
        $today['business_compare'] = 0;
    }


    if ($row['expert_count'] > 0) {
        $today['expert_compare'] = round($today['expert_count'] / $row['expert_count'] * 100, "2");
    } else {
        $today['expert_compare'] = 0;
    }


    if ($row['member_count'] > 0) {
        $today['member_compare'] = round($today['member_count'] / $row['member_count'] * 100, "2");
    } else {
        $today['member_compare'] = 0;
    }

    echo json_encode($today, JSON_UNESCAPED_UNICODE);
}

else if ($type == "visitor_weekly") {
    $search_date = isset($_REQUEST['search_date']) ? $_REQUEST['search_date'] : "";

    if ($search_date == 'today') {
        $s = date("Y-m-d");

        $query = "SELECT
        (select count(*) FROM dm_web_log as l where l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_visit`,
        (select count(*) FROM dm_web_log as l where dm_1 = 0 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_re_visit`,
        (select count(*) FROM dm_web_log as l where dm_1 = 1 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_new_visit`
        FROM
            dm_visit as v
        WHERE
            date_format(dm_datetime, '%Y-%m-%d') = '".date("Y-m-d")."'
        GROUP BY
            date_format(dm_datetime, '%Y-%m-%d')";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

    } else if ($search_date == 'weekend') {
        $today = time();
        $week = date("w");

        $week_first = $today-($week*86400);
        $week_last = $week_first+(6*86400);
        $week = date("Y-m-d",$week_first)." ~ ".date("Y-m-d",$week_last);
        $s = $week;

        $start_date = date("Y-m-d",$week_first);
        $end_date = date("Y-m-d", $week_last);
        $new_date = date("Y-m-d", strtotime("-1 day", strtotime($start_date)));

        $dm_visit = 0;
        $dm_re_visit = 0;
        $dm_new_visit = 0;

        while (1) {
            $new_date = date("Y-m-d", strtotime("+1 day", strtotime($new_date)));
            $query = "SELECT
            (select count(*) FROM dm_web_log as l where l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_visit`,
            (select count(*) FROM dm_web_log as l where dm_1 = 0 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_re_visit`,
            (select count(*) FROM dm_web_log as l where dm_1 = 1 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_new_visit`
            FROM
                dm_visit as v
            WHERE
                date_format(dm_datetime, '%Y-%m-%d') = '".$new_date."'
            GROUP BY
                date_format(dm_datetime, '%Y-%m-%d')";

            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $dm_visit += $row['dm_visit'];
            $dm_re_visit += $row['dm_re_visit'];
            $dm_new_visit += $row['dm_new_visit'];
            if ($new_date == $end_date) break;
        }


        $row['dm_visit'] = $dm_visit;
        $row['dm_re_visit'] = $dm_re_visit;
        $row['dm_new_visit'] = $dm_new_visit;

    } else if ($search_date == 'lastweek') {
        $date['ago1week']['week'] = date('w', strtotime('-1 week'));
        $date['ago1week']['start'] = date('Y-m-d 00:00:00', strtotime('-'.($date['ago1week']['week'] + 7).' day'));
        $date['ago1week']['end'] = date('Y-m-d 23:59:59', strtotime('-'.($date['ago1week']['week'] + 1).' day'));
        $where = "date_format(dm_datetime, '%Y-%m-%d') <= '".$date['ago1week']['start']. "' AND date_format(dm_datetime, '%Y-%m-%d') >= '".$date['ago1week']['end']."'";
        $s = date("Y-m-d", strtotime($date['ago1week']['start'])) . " ~ " . date("Y-m-d", strtotime($date['ago1week']['end']));

        $start_date = date("Y-m-d",strtotime($date['ago1week']['start']));
        $end_date = date("Y-m-d", strtotime($date['ago1week']['end']));
        $new_date = date("Y-m-d", strtotime("-1 day", strtotime($start_date)));

        $dm_visit = 0;
        $dm_re_visit = 0;
        $dm_new_visit = 0;

        while (1) {
            $new_date = date("Y-m-d", strtotime("+1 day", strtotime($new_date)));
            $query = "SELECT
            (select count(*) FROM dm_web_log as l where l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_visit`,
            (select count(*) FROM dm_web_log as l where dm_1 = 0 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_re_visit`,
            (select count(*) FROM dm_web_log as l where dm_1 = 1 and l.dm_fn_code = 'common' and date_format(l.dm_datetime, '%Y-%m-%d') = date_format(v.dm_datetime, '%Y-%m-%d')) as `dm_new_visit`
            FROM
                dm_visit as v
            WHERE
                date_format(dm_datetime, '%Y-%m-%d') = '".$new_date."'
            GROUP BY
                date_format(dm_datetime, '%Y-%m-%d')";

            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $dm_visit += $row['dm_visit'];
            $dm_re_visit += $row['dm_re_visit'];
            $dm_new_visit += $row['dm_new_visit'];

            if ($new_date == $end_date) break;
        }

        $row['dm_visit'] = $dm_visit;
        $row['dm_re_visit'] = $dm_re_visit;
        $row['dm_new_visit'] = $dm_new_visit;

    }

    if (!$row['dm_visit']) {
        $row['dm_visit'] = 0;
    }

    if ($row['dm_re_visit'] > 0) {
        $row['dm_re_visit'] = round($row['dm_re_visit'] / $row['dm_visit'] * 100 , 2);
    } else {
        $row['dm_re_visit'] = 0;
    }

    if ($row['dm_new_visit'] > 0) {
        $row['dm_new_visit'] = round($row['dm_new_visit'] / $row['dm_visit'] * 100 , 2);
    } else {
        $row['dm_new_visit'] = 0;
    }

    $row['search_date'] = $s;

    echo json_encode($row, JSON_UNESCAPED_UNICODE);

}

else if ($type == "weekly_new_member") {
    $day1 = date("Y-m-d");
    $day1 = date("Y-m-d", strtotime("-1 day"));
    $day2 = date("Y-m-d", strtotime("-2 day"));
    $day3 = date("Y-m-d", strtotime("-3 day"));
    $day4 = date("Y-m-d", strtotime("-4 day"));
    $day5= date("Y-m-d", strtotime("-5 day"));
    $day6 = date("Y-m-d", strtotime("-6 day"));
    $day7 = date("Y-m-d", strtotime("-7 day"));

    for ($i=1; $i<=7; $i++) {
        $mobile_count = 0;
        $pc_count = 0;

        $query = "SELECT count(*) as `new_member` FROM dm_member WHERE date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' AND dm_join_os = 1";
        $db->ExecSql($query,"S");
        $row = $db->Fetch();
        $mobile_count = $row['new_member'];

        $query = "SELECT count(*) as `new_member` FROM dm_member WHERE date_format(dm_datetime, '%Y-%m-%d') = '".${"day".$i}."' AND dm_join_os = 0";
        $db->ExecSql($query,"S");
        $row = $db->Fetch();
        $pc_count = $row['new_member'];

        $arResult[($i-1)]['date'] = ${"day".$i};
        $arResult[($i-1)]['mobile_count'] = $mobile_count;
        $arResult[($i-1)]['pc_count'] = $pc_count;
    }

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "board_total") {
    $board = array();
    $arResult = array();

    $query = "SELECT * FROM dm_board";

    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        $board[] = $row;
    }

    foreach ($board as $key => $value) {
        $arResult[$key]['dm_table'] = $value['dm_subject'];

        $query = "SELECT count(*) as write_count FROM dm_write_".$value['dm_table'];
        $db->ExecSql($query, "S");
        $write_count = $db->Fetch();
        $arResult[$key]['write_count'] = $write_count['write_count'];

        $query = "SELECT count(*) as write_count FROM dm_write_".$value['dm_table'] . " WHERE date_format(wr_datetime, '%Y-%m-%d') = '".date("Y-m-d")."' AND wr_is_comment <> 1";
        $db->ExecSql($query, "S");
        $write_count = $db->Fetch();
        $arResult[$key]['day_count'] = $write_count['write_count'];

        $query = "SELECT count(*) as write_count FROM dm_write_".$value['dm_table'] . " WHERE date_format(wr_datetime, '%Y-%m-%d') = '".date("Y-m-d")."' AND wr_is_comment <> 0";
        $db->ExecSql($query, "S");
        $write_count = $db->Fetch();
        $arResult[$key]['day_comment_count'] = $write_count['write_count'];
    }

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}
?>