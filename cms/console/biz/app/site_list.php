<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-03-13
 * Time: 오전 10:20
 */

include "../../lib/lib.php";

$db = new DBSQL();
$db->DBconnect();

$search_site = isset($_REQUEST['search_name']) ? $_REQUEST['search_name'] : "";
$search_site = isset($_REQUEST['search_status']) ? $_REQUEST['search_status'] : "";

$Query = "SELECT * FROM `dm_domain_list`";


//$db->writeLog($Query);
$db->ExecSql($Query, "S" );

$ar_dm_status = selectCommonCode('1001');

$arData = array();

if ( $db->Num > 0 )
{

    while ( $arItem = $db->Fetch() )
    {

        $arItem['dm_domain_status_text'] = $ar_dm_status[$arItem['dm_domain_status']];
        array_push($arData, $arItem);
    }

}
echo json_encode( $arData );

?>