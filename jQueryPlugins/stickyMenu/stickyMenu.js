// Authored by Summa Solutions
// Developed by Juan J Garay & Alejandro Borria

(function ($){

	var errorLog = '';
	var errorState = false;
    //state vars
    var isFixed;
    var areLinksShown;
	
	// Required params
    var menu;
    var title;
    var list;
    var top;
    var links;
    var wScrollTop;
    var initialTop;
    var smoothScrolling;
    var scrollingSpeed;
    var extraTopMargin;

    $.fn.stickyMenu = function( options ){
	menu = $(this);

	init(options);
    
	if (!errorState){
		title.bind('click',function(){
			if (isFixed){
				if (!areLinksShown){
					menu.addClass('open');
					list.slideDown().fadeIn();
					areLinksShown = true;
				}else{
					menu.removeClass('open');
					list.slideUp().fadeOut();
					areLinksShown = false;
				}
			}
		});
        $(window).scroll(function(){
            checkToggle();
        });

        $(document).ready(function(){
            checkToggle();
            if (smoothScrolling){
                links.bind('click',function(e){
                    targetLink = jQuery(this).attr('href');
                    if (targetLink.indexOf('#')==0){
                        e.preventDefault();
                        jQuery('html, body').animate({
                            scrollTop: jQuery(jQuery(this).attr('href')).offset().top - (top + extraTopMargin)
                        }, scrollingSpeed);
                    }
                });
            }
        });
	}
	else{
		console.log(errorLog);
	}

return this;

};

    function init(options){

    // state vars
    isFixed = false;
    areLinksShown = false;
	initialTop = menu.offset().top;
	
	// title
	if (options.title && options.title != 'undefined'){
		title = $(options.title);
	}
	else{
		errorLog += "Undefined required Title.\n";
		errorState = true;
	}
		
	// list
	if (options.list && options.list != 'undefined'){
		list = $(options.list);
	}
	else{
		errorLog += "Undefined required List.\n";
		errorState = true;
	}

	// smoothScrolling
	if (options.smoothScrolling && options.smoothScrolling != 'undefined'){
		smoothScrolling = true;
		if (options.links && options.links != 'undefined'){
			links = $(options.links);	
			if (options.scrollingSpeed && options.scrollingSpeed != 'undefined'){
				scrollingSpeed = $(options.scrollingSpeed);
			}
			else{
				scrollingSpeed = 500;
			}
		}
		else{
			errorLog += "Undefined required Links.\n";
			errorState = true;
		}
	}
	else{
		smoothScrolling = false;
	}
	
	if (options.top && options.top != 'undefined'){
		top = options.top;
	}
	else{
		top = 0;
	}

	if (options.extraTopMargin && options.extraTopMargin != 'undefined'){
        extraTopMargin = options.extraTopMargin;
	}
	else{
        extraTopMargin = 0;
	}
}

    function toggleLinksFix(toggleFix){
        if (toggleFix){
            isFixed = true;
            menu.css({'top':top,  'z-index': '9999', 'cursor' : 'pointer', 'position' : 'fixed'}).addClass('sticky').removeClass('open');
            title.css('cursor', 'pointer');
            list.slideUp().fadeOut();
            areLinksShown = false;
        }else{
            isFixed = false;
            menu.removeAttr('style').removeClass('sticky open');
            title.removeAttr('style');
            list.slideDown().fadeIn();
        }
	}

    function checkToggle(){
        wScrollTop = $(window).scrollTop();

        if (wScrollTop > initialTop && !isFixed){
            toggleLinksFix(true);
        }
        else
        {
            if (wScrollTop <= initialTop && isFixed){
                toggleLinksFix(false);
            }
        }
    }

}(jQuery));
