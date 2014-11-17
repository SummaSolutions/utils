jQuery.AutoSuggestGlobal = {
    initialized : false,
    templates : [],

    getTemplates : function (url) {
        if (jQuery.AutoSuggestGlobal.initialized == false) {
            jQuery.ajax({
                type: 'POST',
                url: url,
                async: false,
                dataType: 'json',
                success: function (jsonObject) {
                    jQuery.AutoSuggestGlobal.templates.products = jQuery(jsonObject.products);
                    jQuery.AutoSuggestGlobal.templates.categories = jQuery(jsonObject.categories);
                    jQuery.AutoSuggestGlobal.templates.pages = jQuery(jsonObject.pages);

                    jQuery.AutoSuggestGlobal.initialized = true;
                }
            });
        }

        return jQuery.AutoSuggestGlobal.templates;
    }
};

(function ($) {
    $.extend($.fn, {
        AutoSuggest: function (options) {
            options = $.extend({
                minLength: 3,
                delay: 200
            }, options);

            var searchTimeout = null;
            var ajaxRequest = null;

            var formElement = this;
            var inputElement = $('.autosuggest-input', $(this)).first();

            var currentBackground = inputElement.css('background-image');

            var resultsContainer = null;

            var templates = [];

            $(".autosuggest-results").first().clone(true).insertAfter(inputElement);

            resultsContainer = inputElement.siblings('.autosuggest-results').first();

            /**
             * Add the results below the input form.
             * Also, prevent the container to disappear if the user click on it.
             *
             * Why? Because line 24, that's why.
             */
            resultsContainer.click(function (event) {
                event.stopPropagation();
            });

            /**
             * Hide the results if the user click outside the container.
             */
            $('html').click(function () {
                resultsContainer.hide();
            });

            /**
             * Delete all the content from the template that will show the new results.
             */
            var resetTemplates = function () {
                resultsContainer.html("").hide();
            };

            /**
             * Process the jsonObject and append the results to the template.
             */
            var showResults = function (_jsonObject) {
                //Get into the JSON first level (Products, Categories and Pages)
                $.each(_jsonObject, function (name, results) {
                    //Check if has results and if the container exists
                    if ((results.length)) {
                        //Show the results container
                        resultsContainer.show();

                        //Get the template and append it to the results container
                        var template = templates[name].clone(true).appendTo(resultsContainer);


                        //Get the content of the template that will be constantly appended
                        var templateLi = template.find("li").clone(true);

                        //Remove that structure from the templates appended in the results container
                        template.find("li").remove();

                        //Get into each result for every JSON first level
                        $.each(results, function (index, result) {
                            //Clone the result template and put it in the results container
                            var currentLi = templateLi.clone(true).appendTo(template.find("ul"));

                            //Go and replace the properties with the correct values
                            $.each(result, function (property, value) {
                                if (property == 'image') {
                                    $('img', currentLi).attr('src', value);
                                } else {
                                    currentLi.html(currentLi.html().replace("{{" + property + "}}", value));
                                }
                            });
                        });
                    }

                    hideLoading();
                });
            };

            /**
             * Returns the JSON with the results
             */
            var getJson = function (_userInput) {
                if (ajaxRequest != null) {
                    ajaxRequest.abort();
                    ajaxRequest = null;
                }
                ajaxRequest = $.ajax({
                    type: 'POST',
                    url: options.url,
                    data: {'q': _userInput},
                    dataType: 'json',
                    success: function (jsonObject) {
                        //Go and show what you got
                        showResults(jsonObject);
                    },
                    complete: function () {
                        ajaxRequest = null;
                    }
                });
            };

            /**
             * Show the loading gif
             */
            var showLoading = function () {
                inputElement.css({'background-image': 'url("/skin/frontend/enterprise/lco/AutoSuggest/loading.gif")'});
            };

            /**
             * Hide the loading gif
             */
            var hideLoading = function () {
                inputElement.css('background-image', currentBackground);
            };

            /**
             * Get the JSON and then show the results.
             */
            var performSearch = function (_userInput) {
                //Check the minimum length of the customer input before proceed
                if (_userInput.length >= options.minLength) {
                    //Wait the specified ms before continue.
                    if (searchTimeout != null) {
                        clearTimeout(searchTimeout);
                        searchTimeout = null;
                    }

                    searchTimeout = setTimeout(function () {
                        //Show the loading gif so the customer know something is happening
                        showLoading();

                        //Delete all previous li if any
                        resetTemplates();

                        //Go and get that JSON
                        getJson(_userInput);
                    }, options.delay);
                }
            };

            /**
             * Open the result, not performing the default Magento search
             */
            var navigateIntoResult = function (_userInput) {
                //Detect the selected li
                var currentLi = $("li.selected_result");

                //Check if any
                if (currentLi.length > 0) {
                    //Cancel the original submit form
                    formElement.submit(function () {
                        return false;
                    });

                    //Get the result URL
                    var url = currentLi.find("a:first").attr("href");

                    //Go for it
                    window.location.assign(url);
                } else {
                    //Submit the form as usual
                }
            };

            /**
             * Navigate the results with the arrows key
             */
            var navigateWithArrows = function (_inputCode) {
                var currentLi = $("li.selected_result");

                //Save how many results
                var liLength = $("li", resultsContainer).length;

                /**
                 * Get next item
                 */
                var getNext = function () {
                    var flag = false;

                    //Navigate through all the items
                    $("li", resultsContainer).each(function (liNumber, liItem) {
                        //Save the first item in case of emergency
                        if (liNumber == 0) {
                            nextItem = $(liItem);
                        }

                        //Detect where it is the selected result
                        if ($(liItem).hasClass("selected_result")) {
                            //If it's the last one, make the first the next
                            if (liNumber == liLength - 1) {
                                return false;
                            } else {
                                flag = true;
                            }
                        } else if (flag) {
                            //If not, make the next, the next.
                            nextItem = $(liItem);
                            return false;
                        }
                    });

                    return nextItem;
                };

                /**
                 * Get previous item
                 */
                var getPrev = function () {
                    var flag = false;

                    //Navigate through all the items
                    $("li", resultsContainer).each(function (liNumber, liItem) {
                        //Detect where it is the selected result
                        if ($(liItem).hasClass("selected_result")) {
                            //If it's the first one, then you have to return the last li. Set a flag to do that.
                            if ((liNumber == 0) || (flag)) {
                                flag = true;
                                //Detect when you're standing in the last item
                                if (liNumber == liLength) {
                                    prevItem = $(liItem);
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        } else {
                            //Save the current item for the next iteration
                            prevItem = $(liItem);
                        }
                    });

                    return prevItem;
                };

                if (currentLi.length) {
                    if (_inputCode == 38) {
                        //Get the previous item
                        var prevLi = getPrev();

                        //Remove class for the current item
                        currentLi.removeClass("selected_result");

                        //Add class for the previous item
                        prevLi.addClass("selected_result");
                    } else if (_inputCode == 40) {
                        //Get the next item
                        var nextLi = getNext();

                        //Remove class for the current item
                        currentLi.removeClass("selected_result");

                        //Add class for the next item
                        nextLi.addClass("selected_result");
                    }
                } else {
                    if (_inputCode == 38) {
                        //Up
                        $("li", resultsContainer).last().addClass("selected_result");

                    } else if (_inputCode == 40) {
                        //Down
                        $("li", resultsContainer).first().addClass("selected_result");
                    }
                }
            };

            templates = $.AutoSuggestGlobal.getTemplates(options.templatesUrl);

            /**
             * Detect when a key is press and release inside the input form
             */
            inputElement.keyup(function (input) {
                //Retain what the user type
                var userInput = input.target.value;

                //And the key code
                var inputCode = input.which;

                //Detect if 0-9 or a-z
                if (
                    ((inputCode >= 48 && inputCode <= 57) || (inputCode >= 65 && inputCode <= 90) || (inputCode == 8) && !"ontouchstart" in document.documentElement)
                    || ("ontouchstart" in document.documentElement && inputCode != 0)
                    ) {
                    //Go and "search"
                    performSearch(userInput);
                } else if ((inputCode == 38) || (inputCode == 40)) {
                    //Up is 38, Down is 40
                    navigateWithArrows(inputCode);
                } else if (inputCode == 13) {
                    //The user press ENTER, do something
                    navigateIntoResult(userInput);
                    input.preventDefault();
                }
            });

            /**
             * Detect when a key is pressed down so the up and down arrow is detected
             */
            inputElement.on('keydown', function (input) {
                //Detect if the key pressed is Up or Down
                if ((input.which == 38) || (input.which == 40)) {
                    //Don't move the cursor at the beginning or end of the input
                    input.preventDefault();
                }
            });

            inputElement.closest('form').on('submit', function (e) {
                e.stopPropagation();
                var query = inputElement.val();
                if ($.trim(query) != "") {
                    return true;
                }

                return false;
            });

            return this;
        }
    });
})(jQuery);

