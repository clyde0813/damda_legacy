<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-10-12
 * Time: 오후 2:50
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();

$db2 = new DBSQL();
$db2->DBconnect();

$site_id = getSession('site_id');
$create_id = getSession('chk_dm_id');
$arData = array();
$arReturn = array();

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

if ($type == 'select') {
    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_start_date = isset($_REQUEST['search_start_date']) ? urldecode(trim($_REQUEST['search_start_date'])): "";
    $search_end_date = isset($_REQUEST['search_end_date']) ? urldecode(trim($_REQUEST['search_end_date'])): "";
	$search_board =  isset($_REQUEST['search_board']) ? $_REQUEST['search_board'] : "";

    $where = " WHERE (dm_table <> 'as' AND dm_table <> 'survey' AND dm_table <> 'operation_faq')";

    if ($search_board)
    {
        $where .= " AND dm_table = '".$search_board."'";
    }

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

    $where1 = ' WHERE wr_is_comment = 1 ';

    if ($search_type != "") {
        if ($search_type == "all") {
            $where1 .= " AND (wr_subject LIKE '%".$search_value."%' OR wr_content LIKE '%".$search_value."%' OR wr_name LIKE '%".$search_value."%' OR wr_datetime LIKE '%".$search_value."%')";
        } else {
            $where1 .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where1 .= " AND `wr_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where1 .= " AND wr_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    $i = 0;

    foreach ($arTable as $key => $value) {

        if ($i == 0)
        {
            $query = "SELECT 
            a.wr_subject as wr_subject, 
            a.wr_content as wr_content, 
            a.wr_name as wr_name, 
            a.wr_id as wr_id, 
            a.wr_datetime as wr_datetime, 
            a.wr_hit as wr_hit, 
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
            a.wr_id as wr_id, 
            a.wr_datetime as wr_datetime, 
            a.wr_hit as wr_hit, 
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
        $query = "SELECT * FROM dm_pages WHERE dm_board_id = '".$rowData['dm_board_id']."'";
        $db->ExecSql($query, "S");
        $pageInfo = $db->Fetch();
        if ($pageInfo) {
            $query = "SELECT * FROM dm_write_".$rowData['dm_table'] ." WHERE wr_id = '".$rowData['wr_parent']."'";
            $db->ExecSql($query, "S");
            $writeInfo = $db->Fetch();
            if ($writeInfo) {
                $rowData['dm_url'] = $_VAR_WEB_URL."?contentId=".$pageInfo['dm_uid']."&command=view&wr_id=".$rowData['wr_parent'];
            } else {
                $rowData['dm_url'] = "";
            }
        } else {
            $rowData['dm_url'] = "";
        }

        $arReturn[] = $rowData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'delete')
{
    $dm_table = isset($_REQUEST['dm_table']) ? $_REQUEST['dm_table'] : "";
    $wr_id = isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";

    if (!$dm_table) {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "테이블이 존재하지않습니다.", "objName" => $_POST['objName'] );
        echo json_encode( $arResult, JSON_UNESCAPED_UNICODE );
        exit;
    }

    $dm_table = 'dm_write_'.$dm_table;

    $query = "DELETE FROM `{$dm_table}` WHERE wr_id = '$wr_id'";

    $db->ExecSql($query, "I");

    $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 삭제했습니다.", "objName" => $_POST['objName'] );

    echo json_encode( $arResult, JSON_UNESCAPED_UNICODE );
}
