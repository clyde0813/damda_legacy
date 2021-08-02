<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2021-01-27
 * Time: 오후 3:03
 */


$page_no = isset($_REQUEST['page_no']) ? intval($_REQUEST['page_no']) : 1;
$rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 10;

function get_goods_list($page_no, $rows) {
    $arr = array();
    $db = new DBSQL();
    $db->DBconnect();

    $query = "SELECT count(*) FROM dm_goods WHERE dm_state = 1";
    $db->ExecSql($query, "S");

    $row = $db->GetPosition(0);

    $total_count = $row[0];

    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

    $limit = "limit ".$rows*($page_no-1).", ".$rows;

    $query = "SELECT * FROM dm_goods WHERE dm_state = 1 order by dm_goods_price asc $limit";
    $db->ExecSql($query, "S");

    while ($row = $db->Fetch()) {
        $arr['list'][] = $row;
    }
    $arr['total_page'] = $total_page;
    $arr['total_count'] = $total_count;
    return $arr;
}
$point_list = get_goods_list($page_no, $rows);
?>

<table id="goods_list">
    <colgroup><col width="25%"><col><col width="10%;"></colgroup>
    <thead>
    <tr>
        <th>가격</th>
        <th>충전 <?=$CONFIG['point_text']?></th>
        <th>충전</th>
    </tr>
    </thead>
    <tbody>
    <?
    if (count($point_list['list'])) {
    foreach ($point_list['list'] as $value) { ?>
        <tr>
            <td class="tc poi"><?=number_format($value['dm_goods_price'])."원"?></td>
            <td class="tc">
                <?
                echo number_format($value['dm_goods_name']).$CONFIG['point_text'];
                if ($value['dm_bonus']) {
                    echo " + ".number_format($value['dm_bonus']).$CONFIG['point_text'];
                }
                ?>
            </td>
            <td class="tc"><a class="btn" onclick="charge(<?=$value['dm_id']?>);">충전하기</a></td>
        </tr>
    <? }
    } else { ?>
        <tr><td colspan="3">등록된 상품이 없습니다.</td></tr>
    <? } ?>
    </tbody>
</table>

<div id="bank" class="paybank">
    <h3>결제하기</h3>
    <dl>
        <dt>결제 금액</dt>
        <dd class="price"><p id="amount"></p></dd>
    </dl>
    <dl>
        <dt>결제은행</dt>
        <dd><?=explode("|", $CONFIG['dm_account_number'])[0]?> <?=explode("|", $CONFIG['dm_account_number'])[1]?></dd>
    </dl>
    <dl>
        <dt>입금자명</dt>
        <dd>
            <input type="text" class="" name="dm_name" value="<?=$MEMBER['dm_name']?>" size="16">
            <span class="etc"> *입금자명을 정확하게 입력하셔야 확인이 가능합니다.</span>
        </dd>
    </dl>
    <div class="txt">무통장 입금 신청을 하시겠습니까?</div>
    <div class="btnbox">
        <a class="cbtn" id="bank_confirm">결제</a>
        <a class="bbtn" id="bank_cancel">취소</a>
    </div>
</div>