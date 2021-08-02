<?php



$url_news = "https://finance.naver.com/news/news_list.nhn?mode=LSS3D&section_id=101&section_id2=258&section_id3=401";

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url_news,
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
$response_news = iconv("EUC-KR", "UTF-8", $response);
//echo $response_news."<br>";
$start_word = "<dl>";
$news = selectData($response_news, $start_word, "</dl>", $out);
$newsLists = explode("<dt class=\"thumb\">", $news);
//echo $news."<br>";
foreach($newsLists as $key=>$data)
{
    //echo "=>".$data."<br>";
	if(strlen($data) > 200 && 9 > $key)
	{
		//echo "<".$key."=>".strlen($data)."><br>";
		$newsList = $data;
		$thumb = selectData($newsList, "<img src=\"", "\" alt=\"\"", $out);
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

}

?>

    <ul>
        <? if (count($list['list']) > 0 ){ 
            $i = 0;
        ?>
            <? foreach ($list['list'] as $value) { 
            //if($i < 7)
            {
                if($i == 0)
                {
                ?>
                <li class="thumbList">
                    <a href="https://finance.naver.com<?=$value['link']?>" target="_blank">
                        <?
                        if($value['thumb'] != "")
                        {
                        ?>
                        <div class="thumb"><img src="<?=$value['thumb']?>"></div>
                        <?
                        }
                        ?>

                        <div class="listbox">
                            <div class="tit"><?=$value['wr_subject']?></div>
                            <div class="list"><?=$value['articleSummary']?></div>
                            <div class="date">
                                <span><?=$value['press']?></span>
                                <span><?=date("Y-m-d", strtotime($value['wr_datetime']));?></span>
                            </div>
                        </div>
                    </a>
                </li>              
                <?
                }else
                {
                ?>            
                <li>                              
                    <div class="List">
                        <a href="https://finance.naver.com<?=$value['link']?>" target="_blank">
                            <p><?=$value['wr_subject']?></p>
                            <div class="date">
                                <span><?=$value['press']?></span>
                                <span><?=date("Y-m-d", strtotime($value['wr_datetime']));?></span>
                            </div>  
                        </a>    
                    </div>                             
                </li>
                <?
                }            
            }            
            $i+=1;
            ?>
              
            <? } ?>
        <? } else { ?>
            <li class="empty"><p>게시물이 없습니다.</p></li>
        <? } ?>
    </ul>

