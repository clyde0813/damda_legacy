<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-10-20
 * Time: 오전 11:38
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

$db = new DBSQL();
$db->DBconnect();


if ($type == "select") {
    $arData = array();
    $arReturn = array();

    $query = " SELECT * FROM `dm_common_code` WHERE dm_code_group = 1002";

    $db->ExecSql($query, "S");

    while ($arData = $db->Fetch()) {
        $arReturn[] = $arData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}
?>