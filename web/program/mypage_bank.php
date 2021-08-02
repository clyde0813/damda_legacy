<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-27
 * Time: 오후 4:25
 */


$page_no = isset($_REQUEST['page_no']) ? intval($_REQUEST['page_no']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;

function get_bank_list($page_no, $rows) {
    $arr = array();
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT count(*) FROM dm_bank WHERE mb_id = '".getSession("chk_dm_id")."'";
    $db->ExecSql($query, "S");

    $row = $db->GetPosition(0);

    $total_count = $row[0];

    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

    $limit = "limit ".$rows*($page_no-1).", ".$rows;

    $query = "SELECT * FROM dm_bank WHERE mb_id = '".getSession("chk_dm_id")."' order by dm_datetime desc $limit";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        $row['dm_state_text'] = ($row['dm_state'] == 1) ? "입금확인전" : "입금확인완료";
        $row['dm_type_text'] = ($row['dm_type'] == 1) ? "무통장입금" : "카드결제";
        if ($row['dm_confirm_date'] != "0000-00-00 00:00:00" && $row['dm_confirm_date']) {
            $row['dm_confirm_date'] = date("Y-m-d", strtotime($row['dm_confirm_date']));
        }
        $arr['list'][] = $row;
    }
    $arr['total_page'] = $total_page;
    $arr['total_count'] = $total_count;
    return $arr;
}
$bank_list = get_bank_list($page_no, $rows);
?>
<div class="MypageBox">
<table class="tc">
    <thead>
    <tr>
        <th>번호</th>
        <th>종류</th>
        <th>상품명</th>
        <th>결제금액</th>
        <th>충전포인트</th>
        <th>상태</th>
        <th>결제신청일</th>
        <th>결제확인일</th>
    </tr>
    </thead>
    <tbody>
    <? if (count($bank_list['list']) > 0) {
        foreach ($bank_list['list'] as $key => $value) {
            $rowNum = ($bank_list['total_count'] - ($page_no - 1 ) * $rows) - $key;
            ?>
            <tr>
                <td><?=$rowNum?></td>
                <td><?=$value['dm_type_text']?></td>
                <td><?=$value['dm_goods_name']?></td>
                <td><?=number_format($value['dm_amount'])."원"?></td>
                <td><?=number_format($value['dm_goods_name']).$CONFIG['point_text']?></td>
                <td><?=$value['dm_state_text']?></td>
                <td><?=date("Y-m-d", strtotime($value['dm_datetime']));?></td>
                <td><?=$value['dm_confirm_date']?></td>
            </tr>
        <? }
    } else { ?>
        <tr>
            <td colspan="8">결제 내역이 없습니다.</td>
        </tr>
    <? }
    ?>
    </tbody>
</table>
</div>
<div class="paging">
    <?=get_paging($rows, $page_no, $point_list['total_page'], "?contentId=".$contentId."&sType=".$sType."&sValue=".$sValue."&sKind=".$sKind);?>
</div>
