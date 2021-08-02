<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-22
 * Time: 오후 2:52
 */

$page_no = isset($_REQUEST['page_no']) ? intval($_REQUEST['page_no']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;

$search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
$search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
$search_start_date = isset($_REQUEST['search_start_date']) ? urldecode(trim($_REQUEST['search_start_date'])): "";
$search_end_date = isset($_REQUEST['search_end_date']) ? urldecode(trim($_REQUEST['search_end_date'])): "";
$search_board = isset($_REQUEST['search_board']) ? urldecode(trim($_REQUEST['search_board'])): "";

$search = array(
    'search_type' => $search_type,
    'search_value' => $search_value,
    'search_start_date' => $search_start_date,
    'search_end_date' => $search_end_date,
    'search_board' => $search_board,
);

$boardList = array();

$query = "SELECT * FROM dm_board WHERE 1 = 1 AND dm_table <> 'as' AND dm_table <> 'survey'";
$db->ExecSql($query, "S");
while ($row = $db->Fetch()) {
    $boardList[] = $row;
}

function get_write_list($page_no, $rows, $search=array()) {
    $db = new DBSQL();
    $db->DBconnect();
    $db2 = new DBSQL();
    $db2->DBconnect();
    $arReturn = array();

    $search_type = isset($search['search_type']) ? urldecode(trim($search['search_type'])) : "";
    $search_value = isset($search['search_value']) ? urldecode(trim($search['search_value'])): "";
    $search_start_date = isset($search['search_start_date']) ? urldecode(trim($search['search_start_date'])): "";
    $search_end_date = isset($search['search_end_date']) ? urldecode(trim($search['search_end_date'])): "";
    $search_board = isset($search['search_board']) ? urldecode(trim($search['search_board'])): "";

    $where = " WHERE 1 = 1 AND dm_table <> 'as' AND dm_table <> 'survey'";
    $countQuery = "";

    if ($search_board && $search_board != "전체") {
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

    $where1 = ' WHERE wr_is_comment = 0 AND mb_id ="'.getSession("chk_dm_id").'" ';

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

    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

    $limit = "limit ".$rows*($page_no-1).", ".$rows;

    $order = " ORDER BY b.wr_datetime desc";

    $query = "SELECT * FROM (" . $query .") as b ". $order ." ". $limit;

    $db2->ExecSql($query, "S");

    while ($rowData = $db2->Fetch())
    {
        $arReturn['list'][] = $rowData;
    }
    $arReturn['total_page'] = $total_page;
    $arReturn['total_count'] = $total_count;

    return $arReturn;
}
$write_list = get_write_list($page_no, $rows, $search);

?>
<div class="mysrhbox">
    <form>
        <input type="hidden" name="contentId" value="<?=$contentId?>" />
        <label>게시판</label>
        <div class="selectbox">
            <select name="search_board">
                <option value="전체">전체게시판</option>
                <? foreach ($boardList as $value) { ?>
                    <option value="<?=$value['dm_table']?>" <? if ($search_board == $value['dm_table']) echo "selected" ?>><?=$value['dm_subject']?></option>
                <? } ?>
            </select>
            <select name="search_type" id="search_type">
                <option value="all">통합검색</option>
                <option value="wr_subject" <? if ($search_type == "wr_subject") echo "selected" ?>>제목</option>
                <option value="wr_content" <? if ($search_type == "wr_content") echo "selected" ?>>내용</option>
                <option value="wr_datetime" <? if ($search_type == "wr_datetime") echo "selected" ?>>작성일</option>
            </select>
        </div>
        <input type="text" name="search_value" id="search_value" value="<?=$search_value?>"/>
        <input type="submit" class="submit" value="검색" />
    </form>
</div>

<table class="AS">
    <thead>
    <tr>
        <th>번호</th>
        <th>게시판명</th>
        <th>제목</th>
        <th>작성일</th>
        <th>조회수</th>
    </tr>
    </thead>
    <tbody>
    <? if (count($write_list['list']) > 0) {
        foreach ($write_list['list'] as $key => $value) {
            $rowNum = ($write_list['total_count'] - ($page_no - 1 ) * $rows) - $key;
    ?>
        <tr>
            <td class="tc num"><?=$rowNum?></td>
            <td><?=$value['dm_subject']?></td>
            <td class="subject"><a href="?contentId=<?=$value['dm_page_uid']?>&wr_id=<?=$value['wr_id']?>&command=view"><?=$value['wr_subject']?></a></td>
            <td class="tc date"><?=date("Y-m-d H:i", strtotime($value['wr_datetime']))?></td>
            <td class="tc num"><?=number_format($value['wr_hit'])?></td>
        </tr>
    <?  }
    } else {
    ?>
        <tr><td colspan="5" class="none tc">등록한 게시물이 없습니다.</td></tr>
    <?
    }
    ?>
    </tbody>
</table>

<div class="paging">
    <?=get_paging($rows, $page_no, $write_list['total_page'], "?contentId=".$contentId."&sType=".$sType."&sValue=".$sValue."&sKind=".$sKind);?>
</div>
