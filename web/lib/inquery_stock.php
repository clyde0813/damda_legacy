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

$filename = "stock.txt";

if($start_index !== false)
{
	
	$stock_file = fopen($filename, "r");
    $response_kospi_kosdaq = fread($stock_file, filesize($filename));
    fclose($stock_file);
}else
{
	$stock_file = fopen($filename, "w");
	fwrite($stock_file, $response_kospi_kosdaq);
	fclose($stock_file);
}


$datetime = selectData($response_kospi_kosdaq, "<span id=\"time\">", "</span>", 0);

$datetimes = explode("<span>", $datetime);
$datetime_1 = $datetimes[0];
$datetime_2 = $datetimes[1];
//증시 시간 정보
//echo $datetime_1."<br>";
//echo $datetime_2."<br>";

//echo "<br><br><br>";

$start_word = "kospi_area";
$base_index = strpos($response_kospi_kosdaq, $start_word);
if($base_index !== false)
{
	$out_word = substr($response_kospi_kosdaq, $base_index, 2000);

	//코스피 변동 구분
	$kospi_0 = selectData($out_word, "num_quot ", "\">", 0);
	//echo $kospi_0."<br>";

	//코스피 지수
	$kospi_1 = selectData($out_word, "<span class=\"num\">", "</span>", 0);
	//echo $kospi_1."<br>";

	//코스피 지수 변동
	$kospi_2 = selectData($out_word, "<span class=\"num2\">", "</span>", 0);
	//echo $kospi_2."<br>";

	//코스피 변동률
	$kospi_3 = selectData($out_word, "<span class=\"num3\">", "</span></span>", 0);
	$kospi_3_1 = selectData($kospi_3, "<span class=\"blind\">", "</span>", 0);
	$kospi_3_2 = selectData($kospi_3, "</span>", "<span", 0);
	//echo $kospi_3_1."<br>";
	//echo $kospi_3_2."<br>";

	//그래프 이미지
	$kospi_chart = selectData($out_word, "<img src=\"", "\" width", 0);
	//echo $kospi_chart."<br>";

	//코스피 세부
	$kospi_index = strpos($response_kospi_kosdaq, "kospi_area");
	$kospi_detail = substr($response_kospi_kosdaq, $kospi_index);
	$kospi_detail = selectData($response_kospi_kosdaq, "<div class=\"dsc_area\">", "</div>", 0);
	$kospi_detail2 = $kospi_detail;

	//코스피 세부 - 개인
	$kospi_detail_1_1 = selectData($kospi_detail, "<dd class=\"", "\">", 0);
	$kospi_detail_1_2 = selectData($kospi_detail, ";\">", "</a>", 0, $out);
	//echo $kospi_detail_1_1."<br>";
	//echo $kospi_detail_1_2."<br>";
	$kospi_detail = substr($kospi_detail, $out);

	//코스피 세부 - 외국인
	$kospi_detail_2_1 = selectData($kospi_detail, "<dd class=\"", "\">", 0);
	$kospi_detail_2_2 = selectData($kospi_detail, ";\">", "</a>", 0, $out);
	//echo $kospi_detail_2_1."<br>";
	//echo $kospi_detail_2_2."<br>";
	$kospi_detail = substr($kospi_detail, $out);

	//코스피 세부 - 기관
	$kospi_detail_3_1 = selectData($kospi_detail, "<dd class=\"", "\">", 0);
	$kospi_detail_3_2 = selectData($kospi_detail, ";\">", "</a>", 0, $out);
	//echo $kospi_detail_3_1."<br>";
	//echo $kospi_detail_3_2."<br>";

	//echo "<br><br>";
	//코스피 세부 2
	$kospi_detail_dd = selectData($kospi_detail2, "class=\"dd\"", ">", 0, $out);
	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_dd1 = strip_tags(selectData($kospi_detail2, ">", "</a", 0, $out));
	//echo $kospi_detail_dd1."<br>";

	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_temp = selectData($kospi_detail2, "class=\"dd2\"", ">", $out);
	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_dd2 = strip_tags(selectData($kospi_detail2, ">", "</a", 0, $out));
	//echo $kospi_detail_dd2."<br>";

	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_temp = selectData($kospi_detail2, "class=\"dd3\"", ">", $out);
	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_dd3 = strip_tags(selectData($kospi_detail2, ">", "</a", 0, $out));
	//echo $kospi_detail_dd3."<br>";

	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_temp = selectData($kospi_detail2, "class=\"dd4\"", ">", $out);
	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_dd4 = strip_tags(selectData($kospi_detail2, ">", "</a", 0, $out));
	//echo $kospi_detail_dd4."<br>";

	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_temp = selectData($kospi_detail2, "class=\"dd5\"", ">", $out);
	$kospi_detail2 = substr($kospi_detail2, $out);
	$kospi_detail_dd5 = strip_tags(selectData($kospi_detail2, ">", "</a", 0, $out));
	//echo $kospi_detail_dd5."<br>";


}
//echo "<br><br><br><br><br>";
$start_word = "kosdaq_area";
$base_index = strpos($response_kospi_kosdaq, $start_word);
if($base_index !== false)
{
	$out_word = substr($response_kospi_kosdaq, $base_index, 2000);

	//코스닥 변동 구분
	$kosdaq_0 = selectData($out_word, "num_quot ", "\">", 0);
	//echo $kosdaq_0."<br>";

	//코스닥 지수
	$kosdaq_1 = selectData($out_word, "<span class=\"num\">", "</span>", 0);
	//echo $kosdaq_1."<br>";

	//코스닥 지수 변동
	$kosdaq_2 = selectData($out_word, "<span class=\"num2\">", "</span>", 0);
	//echo $kosdaq_2."<br>";

	//코스닥 변동률
	$kosdaq_3 = selectData($out_word, "<span class=\"num3\">", "</span></span>", 0);
	$kosdaq_3_1 = selectData($kosdaq_3, "<span class=\"blind\">", "</span>", 0);
	$kosdaq_3_2 = selectData($kosdaq_3, "</span>", "<span", 0);
	//echo $kosdaq_3_1."<br>";
	//echo $kosdaq_3_2."<br>";

	//그래프 이미지
	$kosdaq_chart = selectData($out_word, "<img src=\"", "\" width", 0);
	//echo $kosdaq_chart."<br>";

	//코스닥 세부
	$kosdaq_index = strpos($response_kospi_kosdaq, "kosdaq_area");
	$kosdaq_detail = substr($response_kospi_kosdaq, $kosdaq_index);
	$kosdaq_detail = selectData($kosdaq_detail, "<div class=\"dsc_area\">", "</div>", 0);
	$kosdaq_detail2 = $kosdaq_detail;

	//echo $kosdaq_detail;

	//코스닥 세부 - 개인
	$kosdaq_detail_1_1 = selectData($kosdaq_detail, "<dd class=\"", "\">", 0);
	$kosdaq_detail_1_2 = selectData($kosdaq_detail, ";\">", "</a>", 0, $out);
	//echo $kosdaq_detail_1_1."<br>";
	//echo $kosdaq_detail_1_2."<br>";
	$kosdaq_detail = substr($kosdaq_detail, $out);

	//코스닥 세부 - 외국인
	$kosdaq_detail_2_1 = selectData($kosdaq_detail, "<dd class=\"", "\">", 0);
	$kosdaq_detail_2_2 = selectData($kosdaq_detail, ";\">", "</a>", 0, $out);
	//echo $kosdaq_detail_2_1."<br>";
	//echo $kosdaq_detail_2_2."<br>";
	$kosdaq_detail = substr($kosdaq_detail, $out);

	//코스닥 세부 - 기관
	$kosdaq_detail_3_1 = selectData($kosdaq_detail, "<dd class=\"", "\">", 0);
	$kosdaq_detail_3_2 = selectData($kosdaq_detail, ";\">", "</a>", 0, $out);
	//echo $kosdaq_detail_3_1."<br>";
	//echo $kosdaq_detail_3_2."<br>";

	//echo "<br><br>";
	//코스닥 세부 2
	$kosdaq_detail_dd = selectData($kosdaq_detail2, "class=\"dd\"", ">", 0, $out);
	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_dd1 = strip_tags(selectData($kosdaq_detail2, ">", "</a", 0, $out));
	//echo $kosdaq_detail_dd1."<br>";

	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_temp = selectData($kosdaq_detail2, "class=\"dd2\"", ">", $out);
	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_dd2 = strip_tags(selectData($kosdaq_detail2, ">", "</a", 0, $out));
	//echo $kosdaq_detail_dd2."<br>";

	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_temp = selectData($kosdaq_detail2, "class=\"dd3\"", ">", $out);
	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_dd3 = strip_tags(selectData($kosdaq_detail2, ">", "</a", 0, $out));
	//echo $kosdaq_detail_dd3."<br>";

	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_temp = selectData($kosdaq_detail2, "class=\"dd4\"", ">", $out);
	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_dd4 = strip_tags(selectData($kosdaq_detail2, ">", "</a", 0, $out));
	//echo $kosdaq_detail_dd4."<br>";

	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_temp = selectData($kosdaq_detail2, "class=\"dd5\"", ">", $out);
	$kosdaq_detail2 = substr($kosdaq_detail2, $out);
	$kosdaq_detail_dd5 = strip_tags(selectData($kosdaq_detail2, ">", "</a", 0, $out));
	//echo $kosdaq_detail_dd5."<br>";
}
?>
 <h2 class="today">오늘의 증시
	<div class="realtime"> 
		<span class="ico">실시간</span> 
		<span id="time"><?=$datetime_1?> <em><?=$datetime_2?></em></span> 
	</div>
</h2> 
<!--코스피S-->
<div class="kospi">
	<h3>코스피
	<span class="quot <?=$kospi_0?>"><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
		<span class="num"><?=$kospi_1?></span>
		<span class="num2"><?=$kospi_2?></span>
		<span class="num3"><?=$kospi_3_2?><em>%</em></span>
	</span>
	</h3>
	<p><img src="<?=$kospi_chart?>" alt="일별 차트"></p>
	<dl>
		<dt>개인</dt>
		<dd class="<?=$kospi_detail_1_1?>"><?=$kospi_detail_1_2?></dd><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
		<dt>외국인</dt>
		<dd class="<?=$kospi_detail_2_1?>"><?=$kospi_detail_2_2?></dd><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
		<dt>기관</dt>
		<dd class="<?=$kospi_detail_3_1?>"><?=$kospi_detail_3_2?></dd><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
	</dl>
	<dl>
		<dt>상한종목수</dt>
		<dd class="dd"><?=$kospi_detail_dd1?></dd>
		<dt>상승종목수</dt>
		<dd class="dd2"><?=$kospi_detail_dd2?></dd>
		<dt>보합종목수</dt>
		<dd class="dd3"><?=$kospi_detail_dd3?></dd>
		<dt>하락종목수</dt>
		<dd class="dd4"><?=$kospi_detail_dd4?></dd>
		<dt>하한종목수</dt>
		<dd class="dd5"><?=$kospi_detail_dd5?></dd>
	</dl>
</div>                    
<!--코스피E-->
<!--코스닥S-->
<div class="kosdaq">
	<h3>코스닥
	<span class="quot <?=$kosdaq_0?>"><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
		<span class="num"><?=$kosdaq_1?></span>
		<span class="num2"><?=$kosdaq_2?></span>
		<span class="num3"><?=$kosdaq_3_2?><em>%</em></span>
	</span>
	</h3>
	<p><img src="<?=$kosdaq_chart?>" alt="일별 차트"></p>
	<dl>
		<dt>개인</dt>
		<dd class="<?=$kosdaq_detail_1_1?>"><?=$kosdaq_detail_1_2?></dd><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
		<dt>외국인</dt>
		<dd class="<?=$kosdaq_detail_2_1?>"><?=$kosdaq_detail_2_2?></dd><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
		<dt>기관</dt>
		<dd class="<?=$kosdaq_detail_3_1?>"><?=$kosdaq_detail_3_2?></dd><!-- 지수가 상한가일때 class:up 하한가일때 class:dn -->
	</dl>
	<dl>
		<dt>상한종목수</dt>
		<dd class="dd"><?=$kosdaq_detail_dd1?></dd>
		<dt>상승종목수</dt>
		<dd class="dd2"><?=$kosdaq_detail_dd2?></dd>
		<dt>보합종목수</dt>
		<dd class="dd3"><?=$kosdaq_detail_dd3?></dd>
		<dt>하락종목수</dt>
		<dd class="dd4"><?=$kosdaq_detail_dd4?></dd>
		<dt>하한종목수</dt>
		<dd class="dd5"><?=$kosdaq_detail_dd5?></dd>
	</dl>
</div>
<!--코스닥E-->