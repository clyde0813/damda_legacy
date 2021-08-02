<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-22
 * Time: 오후 2:52
 */

$page_no = isset($_REQUEST['page_no']) ? intval($_REQUEST['page_no']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;

function get_point_list($page_no, $rows) {
    $arr = array();
    $db = new DBSQL();
    $db->DBconnect();
    $db2 = new DBSQL();
    $db2->DBconnect();
    $where = "WHERE dm_id = '".getSession("chk_dm_id")."'";

    $query = "SELECT count(*) FROM dm_point_log $where";
    $db->ExecSql($query, "S");

    $row = $db->GetPosition(0);

    $total_count = $row[0];

    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

    $limit = "limit ".$rows*($page_no-1).", ".$rows;

    $query = "SELECT * FROM dm_point_log $where order by dm_no desc $limit";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        $query = "SELECT * FROM dm_board WHERE dm_table = '".$row['dm_table']."'";
        $db2->ExecSql($query, "S");
        $boardInfo = $db2->Fetch();
        $row['dm_table_text'] = $boardInfo['dm_subject'];

        if ($row['dm_kind'] == 0) {
            $row['dm_kind_text'] = "차감";
        } else if ($row['dm_kind'] == 1) {
            $row['dm_kind_text'] = "지급";
        }

        if ($row['dm_type'] == 1) {
            $row['dm_type_text'] = "게시글 읽기";
        } else if ($row['dm_type'] == 2) {
            $row['dm_type_text'] = "게시글 등록";
        } else if ($row['dm_type'] == 3) {
            $row['dm_type_text'] = "포인트 충전";
        }  else if ($row['dm_type'] == 4) {
            $row['dm_type_text'] = "댓글작성";
        } else if ($row['dm_type'] == 5) {
            $row['dm_type_text'] = "관리자 ".$row['dm_kind_text'];
        }
        $arr['list'][] = $row;
    }
    $arr['total_page'] = $total_page;
    $arr['total_count'] = $total_count;
    return $arr;
}
$point_list = get_point_list($page_no, $rows);
?>

<div id="Mypage">
    <div class="titbox">
        <h3 class="mypage_subtitle">포인트 사용내역</h3>
        <p class="etctxt">가로로 스크롤 할 수 있습니다.</p>
    </div>    
    <div class="MypageBox">
        <table>
            <thead>
            <tr>
                <th>번호</th>
                <th>지급액</th>
                <th>차감액</th>
                <th>잔여포인트</th>
                <th>사유</th>
                <th>게시판명</th>
                <th>지급/차감일</th>
            </tr>
            </thead>
            <tbody>
            <?
            if (count($point_list['list']) > 0 ) {
                foreach ($point_list['list'] as $key => $value) {
                    $rowNum = ($point_list['total_count'] - ($page_no - 1 ) * $rows) - $key;
                    $dm_point = number_format($value['dm_point']);
            ?>
                <tr>
                    <td class="tc"><?=$rowNum?></td>
                    <td class="tc mu"><?=($value['dm_kind'] == 1) ? "<em>+</em> ".$dm_point : "-";?></td>
                    <td class="tc pu"><?=(!$value['dm_kind']) ? "<em>-</em> ".$dm_point : "-";?></td>
                    <td class="tc"><?=number_format($value['dm_remain_point'])?></td>
                    <td class="tc"><?=$value['dm_type_text']?></td>
                    <td class="tc"><?=$value['dm_table_text']?></td>
                    <td class="tc"><?=date("Y-m-d H:i", strtotime($value['dm_datetime']));?></td>
                </tr>
            <?
                }
            } else { ?>
                <tr><td colspan="7" class="none">포인트 사용내역이 없습니다.</td></tr>
            <? } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="paging">
    <?=get_paging($rows, $page_no, $point_list['total_page'], "?contentId=".$contentId."&sType=".$sType."&sValue=".$sValue."&sKind=".$sKind);?>
</div>
