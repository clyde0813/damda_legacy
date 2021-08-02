$(function(){    
    $('body').delegate('.Tooltip .icon', 'click', function(e){
		var findSection = $(this).parents('.section:first');
		var findTarget = $($(this).siblings('.tip'));
		var findTooltip = $('.tip');
		$('.Tooltip').removeClass('show');
		$(this).parents('.tip:first').addClass('show');
		$('.section').css({'zIndex':0, 'position':'static'});
		findSection.css({'zIndex':100, 'position':'relative'});

		var bodyWidth = $('body').width();
		var targetWidth = findTarget.outerWidth();
		var offsetLeft = $(this).offset().left;
		var posWidth = targetWidth + offsetLeft;
		if(bodyWidth < posWidth){
			findTarget.addClass('posRight').css({'marginLeft': '-'+  targetWidth +'px' });
		} else {
			findTarget.removeClass('posRight').css({'marginLeft': 0 });
		}

		findTooltip.hide();
		findTarget.show();
		e.preventDefault();
	});

	$('body').delegate('.Tooltip .close', 'click', function(e){
		var findSection = $(this).parents('.section:first');
		var findTarget = $(this).parents('.tip:first');
		$('.Tooltip').removeClass('show');
		findTarget.hide();
		findSection.css({'zIndex':0, 'position':'static'});
		e.preventDefault();
	});
    
    // Tooltip2
	$('body').delegate('.Tooltip2 .icon', 'mouseover', function(e){
		var findSection = $(this).parents('.section:first');
		var findTarget = $($(this).siblings('.tip'));
		var findTooltip = $('.tip');
		$('.Tooltip2').removeClass('show');
		$(this).parents('.Tooltip2:first').addClass('show');
		$('.section').css({'zIndex':0, 'position':'static'});
		findSection.css({'zIndex':100, 'position':'relative'});

		var bodyWidth = $('body').width();
		var targetWidth = findTarget.outerWidth();
		var offsetLeft = $(this).offset().left;
		var posWidth = targetWidth + offsetLeft;
		if(bodyWidth < posWidth){
			findTarget.addClass('posRight').css({'marginLeft': '-'+  targetWidth +'px' });
		} else {
			findTarget.removeClass('posRight').css({'marginLeft': 0 });
		}

		var bodyHeight = $('body').height();
		var targetHeight = findTarget.outerHeight();
		var offsetTop = $(this).offset().top;
		var posHeight = targetHeight + offsetTop;
		if(bodyHeight < posHeight){
			findTarget.addClass('posTop').css({'marginTop': '-'+  targetHeight +'px' });
			findTarget.find('.explain:before').css('top',targetHeight);
		} else {
			findTarget.removeClass('posTop').css({'marginTop': 0 });
		}

		findTooltip.hide();
		findTarget.show();
		e.preventDefault();
	});
	$('body').delegate('.Tooltip2 .icon', 'mouseleave', function(e){
		$('.Tooltip2').removeClass('show');
		var findTarget = $($(this).siblings('.tip'));
		findTarget.hide();
	});
});    