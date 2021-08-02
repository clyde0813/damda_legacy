<?php

$url_kospi = "https://finance.naver.com/";
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url_kospi,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));
$response = curl_exec($curl);
curl_close($curl);
$response_kospi_kosdaq = iconv("EUC-KR", "UTF-8", $response);

$start_index = strpos($response_kospi_kosdaq, "<div class=\"error_content\">", 0);


if($start_index !== false)
{
	$filename = "stock.txt";
	$stock_file = fopen($filename, "r");
    $response_kospi_kosdaq = fread($stock_file, filesize($filename));
    fclose($stock_file);
}

$start_word = "<div class=\"aside_area aside_popular\">";

$pupular = selectData($response_kospi_kosdaq, $start_word, "</div>", $out);

$start_word = "<tbody>";
$pupular = selectData($pupular, $start_word, "</tbody>", $out);

$pupular = str_replace("/item/main.nhn?code=", "index.html?contentId=148b6d56b1a24e8b8b82a3827b1d9e78&sCode=", $pupular);

echo $pupular;
//exit();
/*$pupularLists = explode("<tr", $pupular);
foreach($pupularLists as $key=>$data)
{
    //echo "=>".$data."<br>";
	if(strlen($data) > 200 && 13 > $key)
	{
		//echo "<".$key."=>".strlen($data)."><br>";
		$newsList = $data;
		$thumb = selectData($newsList, "<img src='", "' onerror", $out);
		//echo $thumb."<br>";

		$link = selectData($newsList, "<a href=\"", "\">", $out);
		//echo $link."<br>";
		
		if($thumb == "")
		{
			$out = 0;
			$subject = strip_tags(selectData($newsList, "<dt class=\"articleSubject\">", "</dt>", $out));
		}else
		{
			$subject = strip_tags(selectData($newsList, "<dd class=\"articleSubject\">", "</dd>", $out));
		}
		//echo $subject."<br>";

		$articleSummary = selectData($newsList, "<dd class=\"articleSummary\">", "<span", $out);
		//echo $articleSummary."<br>";

		$press = selectData($newsList, "class=\"press\">", "</span>", $out);
		
		$writeDt = selectData($newsList, "<span class=\"wdate\">", "</span>", $out);	
		//echo $writeDt."<br>";
		
		//echo "<br><br><br>-------------------------------<br>";
        $list['list'][$key]["thumb"] = $thumb;
		$list['list'][$key]["link"] = $link;
		$list['list'][$key]["wr_subject"] = $subject;
		$list['list'][$key]["articleSummary"] = $articleSummary;
		$list['list'][$key]["press"] = $press;
		$list['list'][$key]["wr_datetime"] = $writeDt;
		//exit();
	}
	
}*/
?>
 
<!--코스닥E-->