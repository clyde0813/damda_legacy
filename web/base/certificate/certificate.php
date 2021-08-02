<?

include('../../lib/lib.php');

$contentId =  isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";
$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "list";
$wr_id =  isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";
$cate =  isset($_REQUEST['cate']) ? $_REQUEST['cate'] : "";
$sType =isset($_REQUEST['sType']) ? $_REQUEST['sType'] : "";
$sValue =isset($_REQUEST['sValue']) ? $_REQUEST['sValue'] : "";

$query = "SELECT * FROM `dm_certificate`";
//$countQuery = "SELECT count(*) FROM `dm_certificate`";

$whereQuery = " WHERE dm_status = 1";
$orderQuery = "order by dm_id ";
//$pageQuery = "limit ".$BBS_VAL["dm_page_rows"]*($page-1).", ".$BBS_VAL["dm_page_rows"];

$query = $query.$whereQuery;

$db->ExecSql($query, "S");

while ($row = $db->Fetch())
{
    $certificateList[] = $row;
}

?>