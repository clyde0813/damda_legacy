$(function(){ 
    
	bsLoad();
	bsSize();
});
$(window).load(function() {
	bsLoad();
	bsSize();
});
$(window).resize(function () {
	bsSize();
});

function Chtab(layername,xid,len){
	try{
		for(i=1;i <= len; i++){
			if (i==xid)	document.getElementById(layername+i).style.display="block";
			else	document.getElementById(layername+i).style.display="none";
		}
	} catch (e)	{}
}

function Ch_Class(layername,xid,len,class_name){
	try{
		for(i=1;i <= len; i++){
			if (i==xid)	document.getElementById(layername+i).className=class_name;
			else	document.getElementById(layername+i).className="";
		}
	} catch (e)	{}
}

function bsLoad() {
    
}

function bsSize() {	
	$('.popLayer').css('margin-top',-$('.popLayer').height()*0.5).css('margin-left',-$('.popLayer').width()*0.5);
	
	//2차메뉴 넓이 자동계산
	if($(window).outerWidth() >= 768) {
		$('.subMenu').find('ul > li').css('width', 100/$(".subMenu").find("ul > li").length + "%");
		$('.subMenu').show();
	}
	else $('.subMenu').hide(); //모바일 접근시 서브메뉴 출력안되도록
	
	//3차메뉴 넓이 자동계산
	if($(".TabMenu").find('li').length > 0) {
		$('.TabMenu a').width(100/$(".TabMenu").find("a").length - 2 + "%");	
	} else {
		$(".TabMenu").remove();
	}
	
	if($(window).outerWidth() >= $('.container').width()){
		
		if($(window).outerWidth() <= 992) $gap = $(window).outerWidth() - $('.container').width();
		else var $gap = $(window).outerWidth() - $('.container').width();
		
		$('.imgBg').css('margin-left',-$gap*0.5).css('margin-right',-$gap*0.5);		
	}
}