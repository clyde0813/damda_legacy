<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-25
 * Time: 오후 1:23
 */

include ("../lib/lib.php");

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";

$db = new DBSQL();
$db2 = new DBSQL();
$db->DBconnect();
$db2->DBconnect();

if ($type == "get_search_all") {
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;
    $search_value = isset($_REQUEST['search_value']) ? trim(urldecode($_REQUEST['search_value'])) : "";

    $where = " WHERE 1 = 1 AND (dm_table <> 'as' AND dm_table <> 'survey')";

    $query = "SELECT * FROM dm_board $where";

    $db->ExecSql($query, "S");

    $total_count = 0;

    $arTable = array();

    $dm_subject = "";
    $i = 0;
    while ($arData = $db->Fetch())
    {
        $arTable[$i]['dm_table'] = $arData['dm_table'];
        $arTable[$i]['dm_board_id'] = $arData['dm_id'];
        $i++;
    }

    $where1 = " WHERE 1 = 1";

    $where1 .= " AND (wr_subject LIKE '%".$search_value."%' OR wr_content LIKE '%".$search_value."%' OR wr_name LIKE '%".$search_value."%' OR wr_datetime LIKE '%".$search_value."%')";

    $i = 0;
    foreach ($arTable as $key => $value) {
        if ($i == 0)
        {
            $query = "SELECT
            a.wr_subject as wr_subject,
            a.wr_content as wr_content, 
            a.wr_name as wr_name, 
            a.mb_id as mb_id, 
            a.wr_id as wr_id, 
            a.wr_datetime as wr_datetime, 
            a.wr_hit as wr_hit, 
            a.wr_is_comment as wr_is_comment,
            a.wr_option as wr_option,
            a.wr_parent as wr_parent,
            (SELECT dm_table FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_table, 
            (SELECT dm_subject FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_subject, 
            (SELECT dm_id FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_board_id,
            (SELECT distinct(dm_uid) FROM dm_pages WHERE dm_board_id = '".$value['dm_board_id']."' ) as dm_page_uid 
            FROM dm_write_".$value['dm_table']." as `a` ". $where1;
        }
        else
        {
            $query .= " UNION ( SELECT 
            a.wr_subject as wr_subject,
            a.wr_content as wr_content,
            a.wr_name as wr_name, 
            a.mb_id as mb_id, 
            a.wr_id as wr_id, 
            a.wr_datetime as wr_datetime, 
            a.wr_hit as wr_hit, 
            a.wr_is_comment as wr_is_comment, 
            a.wr_option as wr_option,
            a.wr_parent as wr_parent, 
            (SELECT dm_table FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_table, (SELECT dm_subject FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_subject, 
            (SELECT dm_id FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_board_id,
            (SELECT distinct(dm_uid) FROM dm_pages WHERE dm_board_id = '".$value['dm_board_id']."') as dm_page_uid
            FROM dm_write_".$value['dm_table'] . " as `a` ". $where1." )";
        }
        $countQuery = " SELECT count(*) as `count` FROM dm_write_".$value['dm_table'] . $where1;
        $db->ExecSql($countQuery, "S");
        $count = $db->Fetch();
        $total_count += $count['count'];
        $i++;
    }

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $order = " ORDER BY b.wr_datetime desc";

    $query = "SELECT * FROM (" . $query .") as b ". $order ." ". $limit;

    $db2->ExecSql($query, "S");

    while ($rowData = $db2->Fetch())
    {
        $option_array = $rowData['wr_option'];
        $option_array = explode(",", $option_array);
        $is_secret = false;
        $query = "SELECT * FROM dm_pages WHERE dm_board_id = '".$rowData['dm_board_id']."'";
        $db->ExecSql($query, "S");
        $pageInfo = $db->Fetch();
        if ($pageInfo) {
            $rowData['dm_url'] = "?contentId=".$pageInfo['dm_uid']."&command=view&wr_id=".$rowData['wr_id'];
            if ($rowData['wr_is_comment'] == 1) {
                $rowData['dm_url'] = "?contentId=".$pageInfo['dm_uid']."&command=view&wr_id=".$rowData['wr_parent'];
            }
        } else {
            $rowData['dm_url'] = "";
        }

        foreach ($option_array as $value)
        {
            if ($value == 'secret')
            {
                if (!getSession("is_admin")) {
                    if ($rowData['mb_id'] != getSession("chk_dm_id")) {
                        $is_secret = true;
                        $rowData['wr_content'] = $rowData['wr_link1'] = $rowData['wr_link2'] ='';
                        $rowData['dm_url'] = "javascript:alert('비밀글은 해당 게시판에서 확인할 수 있습니다');";
                        $rowData['wr_subject'] = "비밀글입니다.";
                        $rowData['wr_content'] = "비밀글입니다.";
                    }
                }
            }
        }

        if ($rowData['mb_id']) {
            $query = "SELECT * FROM dm_member WHERE dm_id = '".$rowData['mb_id']."'";
            $db->ExecSql($query, "S");
            $mb = $db->Fetch();
            $rowData['dm_nick'] = $mb['dm_nick'];
        }

        $word_leng = mb_strlen($search_value);

          if (mb_strpos($rowData['wr_subject'], $search_value) !== false && !$is_secret) {
            $subject_start = mb_strpos($rowData['wr_subject'], $search_value, 0, "UTF-8");
            $neddle_head = mb_substr($rowData['wr_subject'], 0, $subject_start);
            $neddle_tail = mb_substr($rowData['wr_subject'], $subject_start+$word_leng);
            $cont = "<strong class='poi'>".mb_substr($rowData['wr_subject'], $subject_start, $word_leng)."</strong>";
        } else {
            $neddle_head = "";
            $neddle_tail = "";
            $cont = $rowData['wr_subject'];
        }

        $rowData['temp'] =  $subject_start;
        $rowData['subject_cont'] =  $neddle_head.$cont.$neddle_tail;

        if (mb_strpos($rowData['wr_content'], $search_value) !== false && !$is_secret) {
            $content_start = mb_strpos($rowData['wr_content'], $search_value, 0, "UTF-8");
            $neddle_head = mb_substr($rowData['wr_content'], 0, $content_start);
            $neddle_tail = mb_substr($rowData['wr_content'], $content_start+$word_leng);
            $cont = "<strong class='poi'>".mb_substr($rowData['wr_content'], $content_start, $word_leng)."</strong>";
        } else {
            $neddle_head = "";
            $neddle_tail = "";
            $cont = $rowData['wr_content'];
        }

        $rowData['content_cont'] =  $neddle_head.$cont.$neddle_tail;

        if (mb_strpos($rowData['wr_name'], $search_value) !== false) {
            $name_start = mb_strpos($rowData['wr_name'], $search_value, 0, "UTF-8");
            $neddle_head = mb_substr($rowData['wr_name'], 0, $name_start);
            $neddle_tail = mb_substr($rowData['wr_name'], $name_start+$word_leng);
            $cont = "<strong class='poi'>".mb_substr($rowData['wr_name'], $name_start, $word_leng)."</strong>";
        } else {
            $neddle_head = "";
            $neddle_tail = "";
            $cont = $rowData['wr_name'];
        }

        $rowData['name_cont'] =  $neddle_head.$cont.$neddle_tail;

        $arReturn[$rowData['dm_subject']][] = $rowData;
    }

    setSession("search_result", $arReturn);
    setSession("search_value", $search_value);

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);


}