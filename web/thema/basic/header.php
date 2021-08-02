
<!doctype html>
<html lang="ko"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" /> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0, user-scalable=yes">
    <meta name="format-detection" content="telephone=no, address=no, email=no" />
    <meta name="Author" content="<?=$CONFIG['dm_site_name']?> | http://<?=$CONFIG['dm_url']?>/" />
    <meta name="Keywords" content="<?=$CONFIG['dm_meta_keyword']?>">
    <meta name="Description" content="<?=$CONFIG['dm_meta_desc']?>">
    <meta name="naver-site-verification" content="<?=$CONFIG['dm_naver_site_verification']?>" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?=$CONFIG['dm_title']?>">
    <meta property="og:image" content="<?=$ogg_file?>">
    <meta property="og:url" content="http://<?=$CONFIG['dm_url']?>">
    <meta property="og:description" content="<?=$CONFIG['dm_meta_desc']?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?=$CONFIG['dm_title']?>">
    <meta name="twitter:image" content="<?=$ogg_file?>">
    <meta name="twitter:description" content="<?=$CONFIG['dm_meta_desc']?>">
    <meta name="robots" content="ALL" />
    <meta name="Generator" content="ICMS1.0.0" />
    <meta name="publisher" content="디자인아이엠" />
    <link rel="canonical" href="http://<?=$CONFIG['dm_url']?>">
    <link rel="shortcut icon" href="/favicon.ico">
    <title><?=$TopText?></title>
    
    <? require($_VAR_PATH_WEB_LIB.'common_top.php'); ?>    
    <link rel="stylesheet" href="<?=$_VAR_URL_WEB?>common/bxslider/dist/jquery.bxslider.min.css">
    <link rel="stylesheet" href="<?=$layout_path?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$layout_path?>css/owl.carousel.min.css">
    <link rel="stylesheet" href="<?=$layout_path?>css/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css">  
    <link rel="stylesheet" href="<?=$_VAR_URL_WEB?>common/css/reset.css">
    <link rel="stylesheet" href="<?=$layout_path?>css/layout.css">   
    <link rel="stylesheet" href="<?=$layout_path?>css/bootstrap.offcanvas.css"> 
	<link rel="stylesheet" href="<?=$_VAR_URL_WEB?>common/css/common.css"/>
	<link rel="stylesheet" href="<?=$layout_path?>css/swiper_4.5.1.css"/>
  
    <!-- 페이지 타입이 BOARD 일때-->
    <? if ($PAGE_VAL["dm_page_type"] == "BOARD") { ?>
        <link rel="stylesheet" href="<?=$_VAR_URL_WEB?>common/css/bbs.css">    
    <!-- 페이지 타입이 PAGE 일때-->
    <? } else if ($PAGE_VAL["dm_page_type"] == "PAGE") {
        if ($PAGE_VAL['dm_main_content'] == 1) { ?> 
            <link rel="stylesheet" href="<?=$layout_path?>css/main.css">            
        <? } else { ?>
            <link rel="stylesheet" href="<?=$layout_path?>css/page.css">        
        <? }
    } else { ?>
    <!-- 페이지 타입이 PAGE, BOARD 외(조직도, 인증서, 회원가입....) 일때-->
    <link rel="stylesheet" href="<?=$layout_path?>css/page.css"> 
    <? }?>    
    <script>
        var web_url = '<?=$_VAR_URL_WEB?>';
        var login_url = '<?=$_VAR_URL_WEB_LOGIN?>';
        var lib_url = '<?=$_VAR_URL_WEB_LIB?>';
        var board_url = '<?=$_VAR_URL_WEB_BOARD?>';
        var member_url = '<?=$_VAR_URL_WEB_MEMBER?>';
        var content_id = '<?=$contentId?>';
        var web_data_url = '<?=$_VAR_URL_WEB_DATA?>';
        var layout_path = '<?=$layout_path?>';
        var prgm_url = '<?=$_VAR_PROG_ROOT_URL?>';
    </script>
    <script src="<?=$layout_path?>js/common.js"></script>
    <script src="<?=$_VAR_WEB_PATH?>js/common.js"></script>
    <script src="<?=$_VAR_WEB_PATH?>js/diam.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.3/waypoints.min.js"></script>
	<script src="<?=$layout_path?>js/jquery.counterup.min.js"></script>		
	<script src="<?=$layout_path?>js/owl.carousel.min.js"></script>   
    <script src="<?=$layout_path?>js/jquery.scrolla.min.js"></script> 
    <script src="<?=$layout_path?>js/bootstrap.min.js"></script>
    <script src="<?=$layout_path?>js/bootstrap.offcanvas.min.js"></script>
	<script src="<?=$layout_path?>js/swiper_4.5.1.js"></script>
    <script>
        $(function () {
            $("input[name='order']").off().on('change', function () {
                var query_string = "?contentId="+content_id+"&sType=<?=$sType?>&sValue=<?=$sValue?>&";
                switch ($(this).val()) {
                    case "last":
                        query_string += 'orderKind=wr_datetime&sOrder=desc';
                        break;

                    case "good":
                        query_string += 'orderKind=wr_good&sOrder=desc';
                        break;

                    case "hit":
                        query_string += 'orderKind=wr_hit&sOrder=desc';
                        break;

                    case "comment":
                        query_string += 'orderKind=com_count&sOrder=desc';
                        break;
                }
                // console.log(query_string);
                location.href=query_string;
            });
        });
    </script>
</head>
<body>
    <div id="wrap">