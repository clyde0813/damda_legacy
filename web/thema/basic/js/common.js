var browser_width;
// 메뉴
$(function(){

	if($("#menu2").length > 0) {
		$('.Menu > ul > li').hover(function(){
			if($(this).find('ul').length > 0) {
				$('.Menu > ul > li > .headlink').find('span').show();
				$(this).find('ul').slideDown(0);
			} else {
				$('.Menu > ul > li > .headlink').find('span').hide();
				if($(this).find('a').hasClass('headlink') == false) {
					
					$(this).parent().parent().find('.headlink').find('span').show();
				}
				$(this).find('ul').slideUp(0);
			}
		}, function(){
			$(this).find('ul').slideUp(0);
		});
	}

    $("#Mobile ul.depth2 li a").each(function () {
        if ($(this).siblings("ul.depth3").length > 0) {
            $(this).addClass("on");
        }
    });

    $("#Mobile ul.depth2 li").each(function () {
        if ($(this).hasClass("open")) {
            $(this).parent().parent("li").addClass("open");
            $(this).parent().parent().parent().parent().addClass("open");
        }
    });

    $("#Mobile .depth1 li a").off().on('click', function (e) {
        var a = $(this);
        if (a.siblings("ul.depth2").length >= 1) {
            e.preventDefault();
            if(a.parent("li").hasClass('open')) {
                a.siblings("ul.depth2").slideUp(300);
                a.parent("li").removeClass('open')
            } else {
                a.siblings("ul.depth2").slideDown(300);
                a.parent("li").addClass('open')

            }
        }
    });

    $("#Mobile .depth2 li a").off().on('click', function (e) {
        var a = $(this);

        if (a.siblings("ul.depth3").length >= 1) {
            e.preventDefault();
            if(a.parent("li").hasClass('open')) {
                a.siblings("ul.depth3").slideUp(300);
                a.parent("li").removeClass('open');
                a.addClass("off");
            } else {
                a.siblings("ul.depth3").slideDown(300);
                a.parent("li").addClass('open');
                a.removeClass("off");
            }
        }
    });

    $("#Mobile .depth1 > li").each(function (key, value) {
        if ($(this).children("ul").length > 0 ) {
            $(this).addClass("keep");
        }
    });
});

$(document).ready(function() {	
    $( window ).scroll( function() {
      if ( $( this ).scrollTop() > 200 ) {
        $( '#top_btn' ).fadeIn();
      } else {
        $( '#top_btn' ).fadeOut();
      }
    } );
    $( '#top_btn' ).click( function() {
      $( 'html, body' ).animate( { scrollTop : 0 }, 400 );
      return false;
    } );
});


	  

