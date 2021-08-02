<?php
include('../../lib/lib.php');
$contentId =  isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";
$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "list";
$page_no =  isset($_REQUEST['page_no']) ? $_REQUEST['page_no'] : "1";
$sType =isset($_REQUEST['sType']) ? $_REQUEST['sType'] : "";
$sValue =isset($_REQUEST['sValue']) ? $_REQUEST['sValue'] : "";
$page_row = 15;

$countQuery = "SELECT count(*) FROM `dm_faq`";
$selectQuery = "SELECT * FROM `dm_faq`";

$whereQuery = "WHERE dm_status = 1";
$orderQuery = "ORDER BY dm_order";
$pageQuery = "LIMIT ".$page_row*($page_no-1).", ".$page_row;

if ($sType && $sType != "전체")
{
    if ($sType == "both") {
        $whereQuery .= " AND (dm_question LIKE '%$sValue%' OR dm_answer LIKE '%$sValue%')";
    } else {
        $whereQuery .= " AND `$sType` LIKE '%".$sValue."%'" ;
    }
}

$cQuery = $countQuery." ".$whereQuery."";
$Query = $selectQuery." ".$whereQuery." ".$orderQuery." ".$pageQuery;

$db->ExecSql($cQuery, "S");
$row = $db -> GetPosition(0);

$total_row_count = $row[0];
$page_index = $page_no;
$rows = $page_row;
$total_page  = ceil($row[0] / $page_row);  // 전체 페이지 계산

$db->ExecSql($Query, "S");
while ($arData = $db->Fetch())
{
    $getData[] = $arData;
}

?>