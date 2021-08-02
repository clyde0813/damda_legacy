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

function get_write_list($page_no, $rows, $search=array()) {
    $db = new DBSQL();
    $db->DBconnect();
    $arReturn = array();

    $where1 = ' WHERE wr_is_comment = 0 AND mb_id ="'.getSession("chk_dm_id").'" ';
    /*
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
    */

    $query = "SELECT count(*) FROM dm_write_as  ".$where1;

    $db->ExecSql($query, "S");
    $row = $db->GetPosition(0);
    $total_count = $row[0];

    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

    $limit = "limit ".$rows*($page_no-1).", ".$rows;

    $order = " ORDER BY wr_datetime desc";

    $query = "SELECT * FROM dm_write_as $where1 ". $order ." ". $limit;

    $db->ExecSql($query, "S");

    while ($rowData = $db->Fetch())
    {
        if($rowData['wr_2'] == 1) {
            $rowData['dm_status_text'] = "답변준비중";
            $rowData['dm_answer_date'] = "";
        } else if ($rowData['wr_2'] == 2) {
            $rowData['dm_status_text'] = "답변완료";
            $rowData['dm_answer_date'] = date("Y-m-d H:i", strtotime($rowData['wr_3']));
        } else {
            $rowData['dm_status_text'] = "";
            $rowData['dm_answer_date'] = "";
        }

        $arReturn['list'][] = $rowData;
    }
    $arReturn['total_page'] = $total_page;
    $arReturn['total_count'] = $total_count;

    return $arReturn;
}
$write_list = get_write_list($page_no, $rows, $search);

?>
<table class="AS">
    <thead>
    <tr>
        <th width="5%">번호</th>
        <th>제목</th>
        <th width="150px">작성일</th>
        <th width="80px">답변상태</th>
    </tr>
    </thead>
    <tbody>
    <? if (count($write_list['list']) > 0) {
        foreach ($write_list['list'] as $key => $value) {
            $rowNum = ($write_list['total_count'] - ($page_no - 1 ) * $rows) - $key;
    ?>
        <tr>
            <td class="tc num"><?=$rowNum?></td>
            <td class="subject"><a class="flip" data-id="<?=$value['wr_id']?>"><?=$value['wr_subject']?></a></td>
            <td class="tc date"><?=date("Y-m-d H:i", strtotime($value['wr_datetime']))?></td>
            <td class="tc <?=($value['wr_2'] == 2) ? "poi state" : "state" ?>"><?=$value['dm_status_text']?></td>
        </tr>
        <tr>
            <td class="answer" id="answer<?=$value['wr_id']?>" colspan="4">
                <em>답변</em> <?=($value['wr_4']) ? $value['wr_4'] : "답변 준비중 입니다."?>
            </td>
        </tr>
    <?  }
    } else {
    ?>
        <tr><td class="tc" colspan="5">등록한 문의가 없습니다.</td></tr>
    <?
    }
    ?>
    </tbody>
</table>
<div class="paging">
    <?=get_paging($rows, $page_no, $point_list['total_page'], "?contentId=".$contentId."&sType=".$sType."&sValue=".$sValue."&sKind=".$sKind);?>
</div>

<script>
$(document).ready(function(){
    $(".flip").click(function(){
        var id = $(this).data("id");
        $("#answer"+id).slideToggle(0);
    });
});
</script>