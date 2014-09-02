/* Change font size */
function resizeText(multiplier, target)
{
    var font_units = "px";
    var min_font_size = 16;

    if ($(target).css("fontSize") == "") {
        $(target).css("fontSize", min_font_size+font_units);
    }
    var new_size = parseFloat($(target).css("fontSize")) + (multiplier * 2);
    if (new_size < min_font_size) {
        new_size = min_font_size;
    }
    $(target).css("fontSize", new_size+font_units);
}



/* Stuff to do after the page DOM is loaded */
$(document).ready(function() {

    $("#accordion").accordion(
        {
            animated: 'slide',
            autoHeight: false,
            clearStyle: true,
            collapsible: true,
            header: 'h3',
            active: false
        }
    );
    // Initialize a accordion with the active option specified.
    // $( "#accordion" ).accordion({ active: 2 });


    $('.search-box input[type=text]').addClass("resting-state");

    function expandSearch()
    {
        /* $('#promo-banner').css('display', 'none'); */
        $('.search-box input[type=text]').removeClass('resting-state');
        $('.search-box input[type=text]').addClass("bg");
        $('.search_btn').unbind('click');
        $('.search_btn').click(function(event){
            submitSearch(event)
        });

        // Submit search if Enter key pressed while in search box
        $('.search-box input[type=text]').keyup(function(event){
            if (event.keyCode == 13) {
                $(".search_btn").click();
            }
        });

        if ($('.search-box input[type=text]').val() == '') {
            $('.search-box input[type=text]').val('Search...');
        }
    }

    function submitSearch(event)
    {
        // Submit search terms to Drupal node search
        var terms = $('.search-box input[type=text]').val();
        location.href = '/search/node/'+terms;
        event.stopPropagation();
    }

    function collapseSearch() {
        var search = $('.search-box input[type=text]');
        var value = search.val();
        search.val('');
        search.addClass('resting-state');

        $('.search_btn').unbind('click');
        $('.search_btn').click(function(event){
            expandSearch()
            event.stopPropagation();
        });
        setTimeout(function(){
            search.removeClass("bg");
        },400);

        search.val(value);
        /*		setTimeout(function(){
         $('#promo-banner').css('display', 'inline-block');
         },500);
         */
    }

    $('html').click(collapseSearch);

    $('.search-box input[type=text]').click(function(event){
        event.stopPropagation();
    });

    $('.search_btn').click(function(event){
        event.stopPropagation();
        expandSearch();
    });

    $('.search-box input[type=text]').focus(function(e) {
        if ($('.search-box input[type=text]').val() == this.defaultValue) {
            $('.search-box input[type=text]').val('');
        }

        if ($('.search-box input[type=text]').val() != $('.search-box input[type=text]').defaultValue) {
            $('.search-box input[type=text]').select();
        }

        event.stopPropagation();
    });

    /* Add class to menu's partners suckerfish */
    $("#partners-sf .partner:first-child").addClass("first");

    // Label the "Apply for a Job" buttons
    $("#edit-submitted-upload-resume-upload-button").val("Upload Résumé");
    $("#edit-submitted-upload-letter-upload-button").val("Upload Cover Letter");

    /* Calculate margin-left for hero images */
    if ($('.hero-img').val())
    {
        var getMarginLeftValue = ($(".hero-img").find("img").css('width').replace(/[^-\d\.]/g, ''))/2;
        var valueToPX = '-' + getMarginLeftValue + 'px';
        $(".hero-img img").css({
            'marginLeft': valueToPX
        });
    }

});
