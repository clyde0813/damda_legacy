<?php

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$create_id = getSession("chk_dm_id");

$db = new DBSQL();
$db->DBconnect();

if ($type == 'select') {
    $arData = array();
    $query = "SELECT * FROM dm_member_config order by dm_id desc limit 1";

    $db->ExecSql($query, "S");

    $arData = $db->Fetch();
    $level_array = selectCommonCode('1002');
    $arData['level'] = $level_array;

    $query = "SELECT * FROM dm_member_levelup";

    $db->ExecSql($query, "S");

    while($row=$db->Fetch()) {
        $arData['levelup'][] = $row;
    }


    if ($db-Num > 0) {
        $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arData);
    } else {
        $arResult = array( "result" => "success", "_return" => "","total" => $total_count, "rows" => $arData);
    }

    echo json_encode($arResult);

}
else if ($type == 'insert' || $type == 'update') {

    $dm_id = isset($_POST['dm_id']) ? trim($_POST['dm_id']) : "";

    $dm_is_member = isset($_POST['dm_is_member']) ? trim($_POST['dm_is_member']) : "";
    $dm_is_adult = isset($_POST['dm_is_adult']) ? trim($_POST['dm_is_adult']) : "";
    $dm_join_type = isset($_POST['dm_join_type']) ? $_POST['dm_join_type'] : array();
    $dm_certificate_type = isset($_POST['dm_certificate_type']) ? $_POST['dm_certificate_type'] : array();

    $dm_use_nick = isset($_POST['dm_use_nick']) ? trim($_POST['dm_use_nick']) : "n";
    $dm_require_nick = isset($_POST['dm_require_nick']) ? trim($_POST['dm_require_nick']) : "n";
    $dm_use_homepage = isset($_POST['dm_use_homepage']) ? trim($_POST['dm_use_homepage']) : "n";
    $dm_require_homepage = isset($_POST['dm_require_homepage']) ? trim($_POST['dm_require_homepage']) : "n";
    $dm_use_addr = isset($_POST['dm_use_addr']) ? trim($_POST['dm_use_addr']) : "n";
    $dm_require_addr = isset($_POST['dm_require_addr']) ? trim($_POST['dm_require_addr']) : "n";
    $dm_use_email = isset($_POST['dm_use_email']) ? trim($_POST['dm_use_email']) : "n";
    $dm_require_email = isset($_POST['dm_require_email']) ? trim($_POST['dm_require_email']) : "n";
    $dm_use_hp = isset($_POST['dm_use_hp']) ? trim($_POST['dm_use_hp']) : "n";
    $dm_require_hp = isset($_POST['dm_require_hp']) ? trim($_POST['dm_require_hp']) : "n";
    $dm_use_tel = isset($_POST['dm_use_tel']) ? trim($_POST['dm_use_tel']) : "n";
    $dm_require_tel = isset($_POST['dm_require_tel']) ? trim($_POST['dm_require_tel']) : "n";
    $dm_use_introduce = isset($_POST['dm_use_introduce']) ? trim($_POST['dm_use_introduce']) : "n";
    $dm_require_introduce = isset($_POST['dm_require_introduce']) ? trim($_POST['dm_require_introduce']) : "n";
    $dm_use_recom = isset($_POST['dm_use_recom']) ? trim($_POST['dm_use_recom']) : "n";
    $dm_require_recom = isset($_POST['dm_require_recom']) ? trim($_POST['dm_require_recom']) : "n";

    $dm_member_txt_1_name = isset($_POST['dm_member_txt_1_name']) ? trim($_POST['dm_member_txt_1_name']) : "";
    $dm_use_member_txt_1 = isset($_POST['dm_use_member_txt_1']) ? trim($_POST['dm_use_member_txt_1']) : "";
    $dm_use_levelup = isset($_POST['dm_use_levelup']) ? trim($_POST['dm_use_levelup']) : "";
    $dm_require_member_txt_1 = isset($_POST['dm_require_member_txt_1']) ? trim($_POST['dm_require_member_txt_1']) : "";
    $dm_member_txt_2_name = isset($_POST['dm_member_txt_2_name']) ? trim($_POST['dm_member_txt_2_name']) : "";
    $dm_use_member_txt_2 = isset($_POST['dm_use_member_txt_2']) ? trim($_POST['dm_use_member_txt_2']) : "";
    $dm_require_member_txt_2 = isset($_POST['dm_require_member_txt_2']) ? trim($_POST['dm_require_member_txt_2']) : "";
    $dm_level1_name = isset($_POST['dm_level1_name']) ? trim($_POST['dm_level1_name']) : "1";
    $dm_level2_name = isset($_POST['dm_level2_name']) ? trim($_POST['dm_level2_name']) : "2";
    $dm_level3_name = isset($_POST['dm_level3_name']) ? trim($_POST['dm_level3_name']) : "3";
    $dm_level4_name = isset($_POST['dm_level4_name']) ? trim($_POST['dm_level4_name']) : "4";
    $dm_level5_name = isset($_POST['dm_level5_name']) ? trim($_POST['dm_level5_name']) : "5";
    $dm_level6_name = isset($_POST['dm_level6_name']) ? trim($_POST['dm_level6_name']) : "6";
    $dm_level7_name = isset($_POST['dm_level7_name']) ? trim($_POST['dm_level7_name']) : "7";
    $dm_level8_name = isset($_POST['dm_level8_name']) ? trim($_POST['dm_level8_name']) : "8";
    $dm_level9_name = isset($_POST['dm_level9_name']) ? trim($_POST['dm_level9_name']) : "9";

    $join_type = "";
    $certificate_type = "";

    $join_count = count($dm_join_type);
    $certificate_count = count($dm_certificate_type);

    foreach ($dm_join_type as $key => $value) {
        if (($key+1) == $join_count) {
            $join_type .= $value;
        } else {
            $join_type .= $value.',';
        }
    }

    foreach ($dm_certificate_type as $key => $value) {
        if (($key+1) == $certificate_count) {
            $certificate_type .= $value;
        } else {
            $certificate_type .= $value.',';
        }
    }

    for ($i=1; $i<=9; $i++) {
        $query = "UPDATE dm_common_code SET dm_code_name = '".${"dm_level".$i."_name"}."' WHERE dm_code_value = '".$i."' AND dm_code_group = '1002'";
        $db->ExecSql($query, "I");
    }


    $query = "INSERT INTO dm_member_config (`dm_id`, `dm_is_member`, `dm_is_adult`, `dm_join_type`, `dm_certificate_type`, `dm_use_nick`, `dm_require_nick`, `dm_use_addr`, `dm_require_addr`, `dm_use_introduce`, `dm_require_introduce`,
    `dm_use_homepage`, `dm_require_homepage`, `dm_use_email`, `dm_require_email`,`dm_use_tel`, `dm_require_tel`, `dm_use_recom`, `dm_require_recom`, `dm_member_txt_1_name`, `dm_use_member_txt_1`, `dm_require_member_txt_1`, `dm_member_txt_2_name`,
    `dm_use_member_txt_2`, `dm_require_member_txt_2`, `dm_create_dt`, `dm_create_id`,
    `dm_modify_dt`, `dm_modify_id`, `dm_use_hp`, `dm_require_hp`, `dm_use_levelup`)
    VALUE ('".$dm_id."','".$dm_is_member."','".$dm_is_adult."','".$join_type."','".$certificate_type."','".$dm_use_nick."','".$dm_require_nick."','".$dm_use_addr."','".$dm_require_addr."','".$dm_use_introduce."','".$dm_require_introduce."',
    '".$dm_use_homepage."','".$dm_require_homepage."','".$dm_use_email."','".$dm_require_email."','".$dm_use_tel."','".$dm_require_tel."','".$dm_use_recom."','".$dm_require_recom."','".$dm_member_txt_1_name."','".$dm_use_member_txt_1."','".$dm_require_member_txt_1."', '".$dm_member_txt_2_name."',
    '".$dm_use_member_txt_2."','".$dm_require_member_txt_2."', now(), '".$create_id."', now(), '".$create_id."','".$dm_use_hp."','".$dm_require_hp."', '".$dm_use_levelup."')
     ON DUPLICATE KEY UPDATE `dm_is_member` = '".$dm_is_member."', `dm_is_adult` = '".$dm_is_adult."', `dm_join_type` = '".$join_type."', `dm_certificate_type` = '".$certificate_type."', `dm_use_nick` = '".$dm_use_nick."', 
     `dm_require_nick` = '".$dm_require_nick."', `dm_use_addr` = '".$dm_use_addr."', `dm_require_addr` = '".$dm_require_addr."', `dm_use_introduce` = '".$dm_use_introduce."', `dm_require_introduce` = '".$dm_require_introduce."',
     `dm_use_homepage` = '".$dm_use_homepage."', `dm_require_homepage` = '".$dm_require_homepage."', `dm_use_email` = '".$dm_use_email."', `dm_require_email` = '".$dm_require_email."', `dm_use_tel` = '".$dm_use_tel."',
     `dm_require_tel` = '".$dm_require_tel."', `dm_use_recom` = '".$dm_use_recom."', `dm_require_recom` = '".$dm_require_recom."', `dm_member_txt_1_name` = '".$dm_member_txt_1_name."', `dm_use_member_txt_1` = '".$dm_use_member_txt_1."',
     `dm_require_member_txt_1` = '".$dm_require_member_txt_1."', `dm_use_member_txt_2` = '".$dm_use_member_txt_2."', `dm_require_member_txt_2` = '".$dm_require_member_txt_2."', `dm_member_txt_2_name` = '".$dm_member_txt_2_name."',
      `dm_modify_dt` = now(), `dm_modify_id` = '".$create_id."',`dm_use_hp` = '".$dm_use_hp."',`dm_require_hp` = '".$dm_require_hp."',`dm_use_levelup` = '".$dm_use_levelup."'";

    $db->ExecSql($query, "I");

    if (!$dm_id) $dm_id = $db->InsertId();

    for ($i=1; $i<=8; $i++) {
        ${"dm_write_count".$i} = isset($_POST['dm_write_count'.$i]) ? trim($_POST['dm_write_count'.$i]) : "";
        ${"dm_comment_count".$i} = isset($_POST['dm_comment_count'.$i]) ? trim($_POST['dm_comment_count'.$i]) : "";
        ${"dm_attend_count".$i} = isset($_POST['dm_attend_count'.$i]) ? trim($_POST['dm_attend_count'.$i]) : "";
        ${"dm_point".$i} = isset($_POST['dm_point'.$i]) ? trim($_POST['dm_point'.$i]) : "";
        ${"dm_id_".$i} = isset($_POST['dm_id_'.$i]) ? trim($_POST['dm_id_'.$i]) : "";

        $query = "INSERT INTO dm_member_levelup (dm_id, dm_write_count, dm_comment_count, dm_attend_count,  dm_point, dm_kind)
        VALUE ('".${"dm_id_".$i}."','".${"dm_write_count".$i}."', '".${"dm_comment_count".$i}."', '".${"dm_attend_count".$i}."', '".${"dm_point".$i}."', '".$i."')
        ON DUPLICATE KEY UPDATE dm_write_count = '".${"dm_write_count".$i}."', dm_comment_count = '".${"dm_comment_count".$i}."', dm_attend_count = '".${"dm_attend_count".$i}."',
        dm_point = '".${"dm_point".$i}."'";

        $db->ExecSql($query, "I");
    }

    $arResult = array("result" => "success", "_return" => $dm_no, "notice" => "저장했습니다.");

    echo json_encode($arResult);
}
?>
