$(document).ready(function() {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    let value = params.search;
    window.find(value);

    $('.search-container').hide();
    $("#search_txt").keyup(function() {
        if ($(this).val() != '') {
            $('.search-container').show();
            var data = new FormData();
            data.append("search", $(this).val());

            var xhr = new XMLHttpRequest();
            xhr.withCredentials = true;

            xhr.addEventListener("readystatechange", function() {
                if (this.readyState === 4) {
                    //console.log(this.responseText);
                    if (this.responseText != "") {
                        $('.search-result').html(this.responseText);
                    } else {
                        $('.search-result').html('<li class="result-item">No results found...</li>');
                    }
                }
            });

            xhr.open("POST", "/search.php");

            xhr.send(data);
        } else {
            $('.search-container').hide();
        }
    });
});

function playPause(ogg, mp4) {
    var videoMp4 = mp4;
    var videoOgg = ogg;
    var vid = $("#popupVideo")[0];
    $('#popupVSrc').attr('src', videoMp4);
    $('#popupOSrc').attr('src', videoOgg);
    $('#videoPopup').modal('show');

    $("#videoPopup").on('shown.bs.modal', function() {
        $('#popupVSrc').attr('src', videoMp4);
        $('#popupOSrc').attr('src', videoOgg);
        const swiper = document.querySelector('.bannerSwiperv2').swiper;
        swiper.disable()
        vid.autoplay = true;
        vid.load();
    });
};

function checkFooter() {
    if (window.innerWidth < 768) {
        document.querySelector(".footer.mobile").style.display = "block";
        document.querySelector(".footer.desktop").style.display = "none";
    } else {
        document.querySelector(".footer.mobile").style.display = "none";
        document.querySelector(".footer.desktop").style.display = "block";
    }
}

function scroll(element) {
    var ele = document.getElementById(element);
    window.scrollTo(ele.offsetLeft, ele.offsetTop);
}

$(document).ready(function() {

    checkFooter();
    var d = new Date();
    var year = d.getFullYear();
    document.getElementById("copyYear1").innerHTML = `${year}`;

    var d = new Date();
    var year = d.getFullYear();
    document.getElementById("copyYear").innerHTML = `${year}`;

    setTimeout(() => {
        var hash = window.location.hash.substr(1);
        //scroll(hash.replace("#", ""));
    }, 500);

    window.addEventListener("resize", checkFooter);

    jQuery.validator.addMethod("lettersonly", function(value, element) {
        return this.optional(element) || /^[a-z]+$/i.test(value);
    }, "Letters only please");
    $.validator.addMethod("alpha", function(value, element) {
        return this.optional(element) || value == value.match(/^[a-zA-Z\s]+$/);
    });
    //6LfJm0YgAAAAALf4taA6BFJCvYGvbD_GAgUnBce1
    $("#form_submit").on('click', function(e) {
        e.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('6LfrnqsdAAAAAFRLS6kXNaQGoVpkhtbGr_T5yehD', { action: 'request_submit' }).then(function(token) {
                $('#requestForm').prepend('<input type="hidden" name="token" id="token" value="' + token + '">');
                $('#requestForm').prepend('<input type="hidden" name="action" value="Customer Feedback ">');
                $('#requestForm').prepend('<input type="hidden" name="id" value="2">');

                $("#requestForm").trigger('submit');
                if ($("#requestForm").valid()) {
                    apirequestForm();
                }
            });
        });
    });

    $("#form_callbacksubmit").on('click', function(e) {
        e.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('6LfrnqsdAAAAAFRLS6kXNaQGoVpkhtbGr_T5yehD', { action: 'request_submit' }).then(function(token) {
                $('#requestcallbackForm').prepend('<input type="hidden" name="token" id="token" value="' + token + '">');
                $('#requestcallbackForm').prepend('<input type="hidden" name="action" value="Customer Feedback ">');
                $('#requestcallbackForm').prepend('<input type="hidden" name="id" value="2">');

                $("#requestcallbackForm").trigger('submit');
                if ($("#requestcallbackForm").valid()) {
                    callbackFormapi();
                }
            });
        });
    });

    function callbackFormapi() {
        var optionsvalue = [];
        $(':checkbox:checked').each(function(i) {
            optionsvalue[i] = $(this).val();
        });
        var request_data = {
            "name": $("#name").val(),
            "mobile": $("#mobile").val(),
            "state": $("#state").val(),
            "existingCustomer": $("#existingCustomer").val(),
            "options": optionsvalue.toString(),
            "message": $("#message").val(),
            "token": $("#token").val()
        }
        $('#form_submit').css('display', 'none');
        $('.spinner-border').css('display', 'block');
        var action = "https://pnoc.jiobp.com/jiobpapi/api/email/requestcallback";
        $.ajax({
            type: 'POST',
            url: action,
            data: JSON.stringify(request_data),
            dataType: 'json',
            contentType: 'application/json-patch+json',
            success: function(data) {
                if (data.isSuccess) {
                    $('#requestcallbackForm').hide();
                    $('.thanksMsg').css('display', 'flex');
                    $('.spinner-border').css('display', 'none');
                } else {
                    $('#validation_error').html(data.displayMessage);
                    $('#form_submit').css('display', 'inline-flex');
                    $('.spinner-border').css('display', 'none');
                }
            }

        });
    }

    $("#requestcallbackForm").validate({
        debug: true,
        errorClass: "has-error",
        errorElement: "span",
        success: function(label, element) {
            label.hide();
        },
        highlight: function(element, errorClass) {
            $(element).addClass(errorClass);
        },
        errorPlacement: function(error, element) {
            $("#" + element.attr("name") + "_error").addClass('redmessage');
            $("#" + element.attr("name") + "_error").text(error.text());
        },
        submitHandler: function() {

        },
        rules: {
            name: {
                required: true,
                alpha: true
            },
            mobile: {
                required: true,
                minlength: 10,
                maxlength: 10
            },
            city: {
                required: true
            },
            existingCustomer: {
                required: true
            },
            state: {
                required: true
            },
            message: {
                required: true
            }

        },
        messages: {
            name: {
                required: "Please enter your Name",
                alpha: "Please enter alphabets or spaces"
            },
            message: {
                required: "Please enter Message"
            },
            city: {
                required: "Please Select the City"
            },
            mobile: {
                required: "Please Enter Phone Number",
                minlength: "Please enter valid Phone number",
                maxlength: "Please enter valid Phone number"

            },
            existingCustomer: {
                required: "Please Select Existing customer or  not"
            },
            state: {
                required: "Please Select State"
            },
        }

    });

    function apirequestForm() {
        var feedback_data = {
            "name": $("#name").val(),
            "mobile": $("#mobile").val(),
            "email": $("#email").val(),
            "product": $("#product").val(),
            "rocode": $("#ro_code").val(), 
            "message": $("#message").val(),
            "token": $("#token").val(),
	    "cust_satisfaction_rating": $("#custSatisfactionRating").val(),
            "satisfaction_rating_feedback": $("#satisfactionFeedback").val(),
            "qnq_fuel_rating": $("#qnqFuelRatingFeedback").val(),
            "qnq_fuel_rating_feedback": $("#qnqFuelRating").val(),
            "staff_behaviour_rating": $("#staffBehaviourRatingFeedback").val(),
            "staff_behaviour_rating_feedback": $("#staffBehaviourRating").val(),
            "facilities_rating": $("#facilitiesRatingFeedback").val(),
            "facilities_rating_feedback": $("#facilitiesRating").val(),
            "cleanliness_rating": $("#cleanlinessRatingFeedback").val(),
            "cleanliness_rating_feedback": $("#cleanlinessRating").val(),
            "transactions_rating": $("#transactionsRatingFeedback").val(),
            "transactions_rating_feedback": $("#transactionsRating").val(),
            "innovation_rating": $("#innovationRatingFeedback").val(),
            "innovation_rating_feedback": $("#innovationRating").val(),
            "recommendation_rating": $("#recommendationRatingFeedback").val(),
            "recommendation_rating_feedback": $("#recommendationRating").val()
        }
        $('#form_submit').css('display', 'none');
        $('.spinner-border').css('display', 'block');
        //var action = "https://pnoc.jiobp.com/jiobpapi/api/email/customerfeedback";
//var action = "http://pnocqa.ril.com:9090/api/api/email/customerfeedback";
var action = "https://pnocuat.ril.com/jiobpapi/api/email/customerfeedback";
        $.ajax({
            type: 'POST',
            url: action,
            data: JSON.stringify(feedback_data),
            dataType: 'json',
            contentType: 'application/json-patch+json',
            success: function(data) {
                if (data.isSuccess) {
                    $('#requestForm').hide();
                    $('.thanksMsg').css('display', 'flex');
                    $('.spinner-border').css('display', 'none');
                } else {
                    $('#validation_error').html(data.displayMessage);
                    $('#form_submit').css('display', 'inline-flex');
                    $('.spinner-border').css('display', 'none');
                }
            }

        });
    }

    $("#requestForm").validate({
        debug: true,
        errorClass: "has-error",
        errorElement: "span",
        success: function(label, element) {
            label.hide();
        },
        highlight: function(element, errorClass) {
            $(element).addClass(errorClass);
        },
        errorPlacement: function(error, element) {
            $("#" + element.attr("name") + "_error").addClass('redmessage');
            $("#" + element.attr("name") + "_error").text(error.text());
        },
        submitHandler: function() {

        },
        rules: {
            name: {
                required: true,
                alpha: true
            },
            mobile: {
                required: true,
                minlength: 10,
                maxlength: 10
            },
            email: {
                required: true
            },
            product: {
                required: true
            },
            state: {
                required: true
            },
	    cust_satisfaction_rating: {
            required: true
            },
            message: {
                required: true
            }

        },
        messages: {
            name: {
                required: "Please enter your Name",
                alpha: "Please enter alphabets or spaces"
            },
            message: {
                required: "Please enter Message"
            },
            email: {
                required: "Please Provide email"
            },
            product: {
                required: "Please select Product"
            },
            mobile: {
                required: "Please Enter Phone Number",
                minlength: "Please enter valid Phone number",
                maxlength: "Please enter valid Phone number"

            },

            state: {
                required: "Please Select State"
            },
	    cust_satisfaction_rating: {
                required: "Please Rate your level of satisfaction with Jio-bp"
            }
        }
    });

    $("#queryform_submit").on('click', function(e) {
        e.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('6LfrnqsdAAAAAFRLS6kXNaQGoVpkhtbGr_T5yehD', { action: 'request_submit' }).then(function(token) {
                $('#queryForm').prepend('<input type="hidden" name="token" id="token" value="' + token + '">');
                $('#queryForm').prepend('<input type="hidden" name="action" value="Customer Feedback ">');
                $('#queryForm').prepend('<input type="hidden" name="id" value="2">');

                $("#queryForm").trigger('submit');
                if ($("#queryForm").valid()) {
                    apiqueryForm();
                }
            });
        });
    });

    function apiqueryForm() {
        var feedback_data = {
            "name": $("#name").val(),
            "email": $("#email").val(),
            "message": $("#message").val(),
            "token": $("#token").val()
        }

        $('#queryform_submit').css('display', 'none');
        $('.spinner-border').css('display', 'block');
        var action = "https://pnoc.jiobp.com/jiobpapi/api/email/newsroom";
        $.ajax({
            type: 'POST',
            url: action,
            data: JSON.stringify(feedback_data),
            dataType: 'json',
            contentType: 'application/json-patch+json',
            success: function(data) {
                if (data.isSuccess) {
                    $('#queryForm').hide();
                    $('.thanksMsg').css('display', 'flex');
                    $('.spinner-border').css('display', 'none');
                } else {
                    $('#validation_error').html(data.displayMessage);
                    $('#queryform_submit').css('display', 'inline-block');
                    $('.spinner-border').css('display', 'none');
                }
            }

        });
    }

    $("#queryForm").validate({
        debug: true,
        errorClass: "has-error",
        errorElement: "span",
        success: function(label, element) {
            label.hide();
        },
        highlight: function(element, errorClass) {
            $(element).addClass(errorClass);
        },
        errorPlacement: function(error, element) {
            $("#" + element.attr("name") + "_error").addClass('redmessage');
            $("#" + element.attr("name") + "_error").text(error.text());
        },
        submitHandler: function(form, event) {

        },
        rules: {
            name: {
                required: true,
                alpha: true
            },
            email: {
                required: true
            },
            message: {
                required: true
            }

        },
        messages: {
            name: {
                required: "Please enter your Name",
                alpha: "Please enter alphabets or spaces"
            },
            message: {
                required: "Please enter Message"
            },
            email: {
                required: "Please Provide email"
            }
        }
    });
});

$("#videoPopup").on('hide.bs.modal', function() {
    var vid = $("#popupVideo")[0];
    vid.pause();
    $('#popupVSrc').attr('src', '');
    $('#popupOSrc').attr('src', '');
    const swiper = document.querySelector('.bannerSwiperv2').swiper;
    swiper.enable()
});


$(window).on('load', function() {
    if ($('.loader').length) {
        $('.loader').delay(100).fadeOut('slow', function() {
            $(this).remove();
        });
    }

    $('.jio_news_each').hide();
    size_li = $(".jio_news_each").length;
    x = 4;
    $('.jio_news_each:lt(' + x + ')').show();
    $('#loadMoreBtn').click(function(e) {
        e.preventDefault();
        x = (x + 4 <= size_li) ? x + 4 : size_li;
        $('.jio_news_each:lt(' + x + ')').show();
        if (x == size_li) {
            $('#loadMoreBtn').hide();
        }
    });
});
$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
    localStorage.setItem('activeTab', $(e.target).attr('href'));
});
var activeTab = localStorage.getItem('activeTab');
if (activeTab) {
    $('#nav-tab a[href="' + activeTab + '"]').tab('show');
}


$('#muteBtn').click(function() {
    if ($("#main-video").prop('muted')) {
        $("#main-video").prop('muted', false);
        $(this).find('img').attr('src', 'themes/custom/jiobp/assets/images/icons/sound-on.svg')
    } else {
        $("#main-video").prop('muted', true);
        $(this).find('img').attr('src', 'themes/custom/jiobp/assets/images/icons/sound-off.svg')
    }
});
/*$( document ).ready(function() {
setTimeout(function(){
 	
if ($("#main-video").prop('muted')) {
console.log("mutebtn clicked!")
        $("#main-video").prop('muted', 'false');
        $('#muteBtn').find('img').attr('src', 'themes/custom/jiobp/assets/images/icons/sound-on.svg');
	
document.getElementById('main-video').play();
    }

}, 1000)
});*/

function reveal() {
    var reveals = document.querySelectorAll(".hp-about");

    for (var i = 0; i < reveals.length; i++) {
        var windowHeight = window.innerHeight;
        var elementTop = reveals[i].getBoundingClientRect().top;
        var elementVisible = 150;

        if (elementTop < windowHeight - elementVisible) {
            reveals[i].classList.add("active");
        } else {
            reveals[i].classList.remove("active");
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var open_count = document.querySelector(".open_count");
    var open_offers = document.querySelector("#open-now");
    if (open_offers != null) {
        var all_cards = open_offers.querySelectorAll(".card");
        open_count.textContent = `(${all_cards.length})`;
    }
});

var stickyMenu = document.querySelector(".sticky-menu");
if (stickyMenu != null) {
    var productsServices = document.querySelector(".productsServices"),
        windowScrollY = window.scrollY;
    productsServicesY = productsServices.offsetTop;
    window.addEventListener("scroll", function() {
        if (window.scrollY > productsServicesY - 400) {
            stickyMenu.classList.add("sticky-menu-active");
        } else {
            stickyMenu.classList.remove("sticky-menu-active");
        }
    });
}



document.addEventListener("DOMContentLoaded", function() {
    var mainBody = document.body;
    mainBody.setAttribute("data-spy", "scroll");
    if (mainBody.classList.contains("productServices")) {
        mainBody.setAttribute("data-target", ".sticky-menu");
        mainBody.setAttribute("data-offset", "400");
    } else {
        mainBody.setAttribute("data-target", ".__faqsticky");
        mainBody.setAttribute("data-offset", "500");
    }
})

window.addEventListener("scroll", reveal);

function filterdocs() {
    var d = $('.selectYearInput').val();
    var c = $('#' + $('#myTab .active').attr('aria-controls') + ' .card');
    var cc = 0;
    for (var i = 0; i < c.length; i++) {
        if (d == $(c[i]).attr('data-year')) {
            cc++;
        }
    }
    var c_tab = $('#myTab .active').attr('aria-controls');
    var getAllCards = Array.from(document.querySelectorAll("#" + c_tab + " .card"));
    if (cc == 0) {
        var filteredCards = getAllCards.filter(function(card) {
            if (card.dataset.year == 'all') {
                card.style.display = "flex";
            } else {
                card.style.display = "none";
            }
        });
    } else {
        var filteredCards = getAllCards.filter(function(card) {
            if (card.dataset.year == d) {
                card.style.display = "flex";
            } else {
                card.style.display = "none";
            }
        });
    }
}
filterdocs();


var investerpage = document.querySelector('body');
if (investerpage.classList.contains('invester')) {
    var selectYearInput = document.querySelector(".selectYearInput");
    selectYearInput.addEventListener("change", function(e) {
        var getAllCards = Array.from(document.querySelectorAll(".pdfs-content .card"));
        var filteredCards = getAllCards.filter(function(card) {
            card.style.display = "none";
        });
        setTimeout(() => {
            filterdocs();
        }, 200);
    });

    document.addEventListener("DOMContentLoaded", function() {
        var emailForm = document.querySelector(".verification_form");
        if (Pristine != undefined) {
            var pristine = new Pristine(emailForm, {
                errorTextTag: "p",
                errorTextClass: "text-danger text-left px-4 p-2",
            });
        }
        emailForm.addEventListener("submit", function(e) {
            e.preventDefault();
            var valid = pristine.validate();
            if (valid) {
                // post data to server or do something
                emailForm.submit();
            }
        });

        // add change event on .selectYearInput
        function filterCards(val) {
            var selectedYear = val;
            var getAllCards = Array.from(document.querySelectorAll(".pdfs-content .card"));
            // filter all cards data attribute year matching the selectYear
            var filteredCards = getAllCards.filter(function(card) {
                if (card.dataset.year == selectedYear) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        }
        var selectYearInput = document.querySelector(".selectYearInput");
        selectYearInput.addEventListener("change", function(e) {
            filterCards(e.target.value);
        });
        filterCards(selectYearInput.value);
    });
}


if (investerpage.classList.contains('ev-charging')) {
    const swiperrrrr8 = new Swiper(".fleet_charging", {
        // Optional parameters
        direction: "horizontal",
        slidesPerView: 1,
        userSelect: false,

        // if we navigate to the next slide, we want to slide to the left
        navigation: {
            nextEl: ".fleet_charging .swiper-button-next",
            prevEl: ".fleet_charging .swiper-button-prev",
        },
    });
    const swiperrrrr9 = new Swiper(".station_charging", {
        // Optional parameters
        direction: "horizontal",
        slidesPerView: 1,
        userSelect: false,

        // if we navigate to the next slide, we want to slide to the left
        navigation: {
            nextEl: ".station_charging .swiper-button-next",
            prevEl: ".station_charging .swiper-button-prev",
        },
    });

    $(document).ready(function() {
        var options = {
            placement: 'bottom',
            fallbackPlacement: ['bottom'],
        };
        // $('[data-toggle="popover"]').popover();
        $(".fleet .map_btn").popover(options).popover("show");
        $(".station .map_btn").popover(options).popover("show");
    });

    var appsectionbtns = document.querySelector(".appSection .btns");
    var appsectionimgcontainer = document.querySelector(".appSection_img");
    var appsectionbtnschilds = appsectionbtns.childElementCount;
    if (appsectionbtnschilds == 0) {
        appsectionimgcontainer.classList.add("-offset-top");
    }
}

if (investerpage.classList.contains('fuel4u')) {
    if (window.innerWidth < 768) {
        const swiperrrrr = new Swiper(".mediums_swiper", {
            // Optional parameters
            direction: "horizontal",
            slidesPerView: "auto",
            spaceBetween: 39.5,
            // grabCursor: true,
            // mousewheel: true,

            // If we need pagination
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    } else {
        function stickAnimate() {
            let overflowingEl = document.querySelector(".mediums_swiper");
            let stickyWrapperEl = document.querySelector(".stickyWrapper");
            let parentEl = document.querySelector(".parent_stick");
            let parentHeight = parentEl.getBoundingClientRect().height;
            let overflow = overflowingEl.scrollHeight - parentEl.offsetHeight;
            stickyWrapperEl.style.height = parentHeight + overflow + "px";
            let userScrolledPixels = 0;
            let programScrolledPixels = 0;
            let startY = parentEl.getBoundingClientRect().top + window.pageYOffset;
            let endY = startY + overflow;

            window.addEventListener("scroll", () => {
                let scrollY = window.pageYOffset;
                programScrolledPixels = gsap.utils.mapRange(startY, endY, 0, overflow, scrollY);
                programScrolledPixels = gsap.utils.clamp(0, overflow, programScrolledPixels);
                overflowingEl.scrollTop = programScrolledPixels;
            });

            overflowingEl.addEventListener("scroll", () => {
                userScrolledPixels = overflowingEl.scrollTop;
            });
        }

        window.addEventListener("load", () => {
            stickAnimate();
        });

        window.addEventListener("resize", () => {
            stickAnimate();
        });
    }

    function setHeightContent() {
        var capabilities_innerContent = document.querySelectorAll(".capabilities_innerContent");
        var capabilities_innerpills = document.querySelector(".capabilities_innerpills");
        if (window.innerWidth > 768) {
            capabilities_innerContent.forEach(ele => {
                ele.style.minHeight = (capabilities_innerpills.offsetHeight - 0.5) + "px";
            });
        } else {
            capabilities_innerContent.forEach(ele => {
                ele.style.minHeight = "auto";
            });
        }
    }
    setHeightContent();
    window.addEventListener("resize", setHeightContent);
}

var navlink = document.querySelectorAll(".pdfs-tabs .nav-link");
for (let i = 0; i < navlink.length; i++) {
    navlink[i].addEventListener("click", function() {
        var getAllCards = Array.from(document.querySelectorAll(".pdfs-content .card"));
        var filteredCards = getAllCards.filter(function(card) {
            card.style.display = "none";
        });
        setTimeout(() => {
            filterdocs();
        }, 200);
    });
}






        const div = document.querySelector('body');
        if(div.classList.contains('ev-charging'))
        {



var directionsDisplay, directionsService, map;
        var locations = [];
        var locations2 = [];

        function initialize() {
            var directionsService = new google.maps.DirectionsService();
            directionsDisplay = new google.maps.DirectionsRenderer();
            var chicago = new google.maps.LatLng(22.851787151520107, 79.1795339339829);
            var mapOptions = {
                zoom: 7,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: chicago
            }
            map = new google.maps.Map(document.getElementById("map_canvas1"), mapOptions);
            directionsDisplay.setMap(map);
        }
        var marker;
        var markersArray = [];

        function addMarker(locations, contentString) {
            if (marker && marker.setMap) {
                marker.setMap(null);
            }
            var infowindow = new google.maps.InfoWindow();
            var icons = {
                1: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                2: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                3: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                4: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                }
            };
            var i;
            for (i = 0; i < locations.length; i++) {

                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i][0], locations[i][1]),
                    animation: google.maps.Animation.DROP,
                    map: map,
                    icon: icons[locations[i][2]].icon,
                });
                markersArray.push(marker);
                map.setCenter(new google.maps.LatLng(locations[i][0], locations[i][1]));
                map.setZoom(9);

                google.maps.event.addListener(marker, 'click', (function (marker, i) {
                    return function () {
                        infowindow.setContent('<h5 class="map_pop_title">' + locations[i][4] + '</h5><p class="map_pop_text">' + locations[i][3] + '</p><a href="javascript:getlatlong(' + locations[i][0] + ',' + locations[i][1] + ');" class="btn map_pop_btn">Get Direction</a>');
                        infowindow.open(map, marker);
                    }
                })(marker, i));
            }
        }

        function getlatlong(lat, lon) {
console.log(lat);
console.log(lon);
            window.open(
                'https://maps.google.com/?q=' + lat + ',' + lon, '_blank'
            );

        }
        
        initialize();
	
        data3 = [];
        fuelData = [];

        var resultBtn1 = document.querySelector(".show_result1");
        var reset_btn1 = document.querySelector(".reset_btn1");
        var form__results1 = document.querySelector(".form__results1");
        var form__inputs1 = document.querySelector(".form__inputs1");
        var stateSelect1 = document.querySelector("#stateSelect1");
        var setstate1 = document.querySelector("#setstate1");
        var form__resetSec1 = document.querySelector(".form__resetSec1");
        resultBtn1.addEventListener("click", function () {
            if (stateSelect1.value !== "") {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                xhr.addEventListener("readystatechange", function () {
                    if (this.readyState === 4) {
                        setstate1.innerText = stateSelect1.value;
                        form__results1.classList.remove("d-none");
                        form__resetSec1.classList.remove("d-none");
                        form__inputs1.classList.add("d-none");
                        /* MAP */
                        var data_html = '';
                        var fuelLocation = JSON.parse(this.responseText);
                        for (i = 0; i < fuelLocation.length; i++) {
                            data_html += '<div class="result_card" onclick="javascript:addMarker1(\'' + fuelLocation[i]['field_latitude'] + '\', \'' + fuelLocation[i]['field_longitude'] + '\', 1, \'' + fuelLocation[i]['field_address'].replace(/<[^>]*>?/gm, '').replace(/^\s+|\s+$/g, '') + '\', \'' + fuelLocation[i]['title'] + '\');">';
                            data_html += '<h3 class = "result_card_title">' + fuelLocation[i]['title'] + '</h3>';
                            data_html += '<div class = "result_card_ro" >';
                            data_html += '<div class = "ro_">';
                            data_html += fuelLocation[i]['field_address']
                            data_html += '</div>';
                            data_html += '</div>';
                            data_html += '</div>';
                            locations.push({
                                "0": fuelLocation[i]['field_latitude'],
                                "1": fuelLocation[i]['field_longitude'],
                                "2": 1,
                                "3": fuelLocation[i]['field_address'],
                                "4": fuelLocation[i]['title']
                            });
                        }

			if(fuelLocation.length == 0)
			{
			   data_html = '<div class="result_card"><h3 class = "result_card_title">This area has no stations yet, please try another location</p></div>';
			}
                        form__results1.innerHTML = data_html;
                        addMarker(locations);
                        locations = [];
                    }
                });
                xhr.open("GET", "/api/locator/Fleet-Charging-Station/" + stateSelect1.value + "?_format=json");
                xhr.send();
            }
        });

        reset_btn1.addEventListener("click", function () {
            window.location.reload();
            //form__results1.classList.add("d-none");
            //form__inputs1.classList.remove("d-none");
            //form__resetSec1.classList.add("d-none");
        });
}

        if(div.classList.contains('ev-charging'))
        {
          function addMarker1(lat, lon, type, address, name, contentString) {
            locations.push({
                "0": lat,
                "1": lon,
                "2": type,
                "3": address,
                "4": name
            });
            if (marker && marker.setMap) {
                marker.setMap(null);
            }
            var infowindow = new google.maps.InfoWindow();
            var icons = {
                1: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                2: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                3: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                4: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                }
            };
            var i;
            for (i = 0; i < locations.length; i++) {
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(locations[i][0], locations[i][1]),
                    map: map,
                    icon: icons[locations[i][2]].icon,
                });
                markersArray.push(marker);
                map.setCenter(new google.maps.LatLng(locations[i][0], locations[i][1]));
                map.setZoom(15);

                google.maps.event.addListener(marker, 'click', (function (marker, i) {
                    var lName = locations[i][4]
                    var lAdd = locations[i][3]
                    var lLat = locations[i][0]
                    var lLon = locations[i][1]
                    return function () {
                        infowindow.setContent('<h5 class="map_pop_title">' + lName + '</h5><p class="map_pop_text">' + lAdd + '</p><a href="javascript:getlatlong(' + lLat + ',' + lLon + ');" class="btn map_pop_btn">Get Direction</a>');
                        infowindow.open(map, marker);
                    }
                })(marker, i));
            }
            locations = [];
        }

        var map2;
        var marker2;
        var markersArray2 = [];


        function initialize2() {
            var directionsService = new google.maps.DirectionsService();
            directionsDisplay = new google.maps.DirectionsRenderer();
            var chicago = new google.maps.LatLng(22.851787151520107, 79.1795339339829);
            var mapOptions = {
                zoom: 7,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: chicago
            }
            map2 = new google.maps.Map(document.getElementById("map_canvas2"), mapOptions);
            directionsDisplay.setMap(map);
        }




    function addMarker2(lat, lon, type, address, name, contentString, zoom) {
	locations2.push({
		"0": lat,
		"1": lon,
		"2": type,
		"3": address,
		"4": name
	});
	if (marker && marker.setMap) {
		marker.setMap(null);
	}
	var infowindow = new google.maps.InfoWindow();
	var icons = {
		1: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		},
		2: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		},
		3: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		},
		4: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		}
	};
	var i;
	for (i = 0; i < locations2.length; i++) {
		if(!Array.isArray(locations2[i][0]))
		{
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations2[i][0], locations2[i][1]),
			map: map2,
			icon: icons[locations2[i][2]].icon,
		});
		markersArray.push(marker2);
		map2.setCenter(new google.maps.LatLng(locations2[i][0], locations2[i][1]));
		map2.setZoom(15);

		google.maps.event.addListener(marker, 'click', (function (marker, i) {
			var lName = locations2[i][4]
			var lAdd = locations2[i][3]
			var lLat = locations2[i][0]
			var lLon = locations2[i][1]
			return function () {
				infowindow.setContent('<h5 class="map_pop_title">' + lName + '</h5><p class="map_pop_text">' + lAdd + '</p><a target="_blank" href="javascript:getlatlong(' + lLat + ',' + lLon + ');" class="btn map_pop_btn">Get Direction</a>');
				infowindow.open(map2, marker);
			}
		})(marker, i));
		}
	}
	locations2 = [];
     }




function addMarker3(lat, lon, type, address, name, contentString, zoom) {
	locations2.push({
		"0": lat,
		"1": lon,
		"2": type,
		"3": address,
		"4": name
	});
	if (marker && marker.setMap) {
		marker.setMap(null);
	}
	var infowindow = new google.maps.InfoWindow();
	var icons = {
		1: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		},
		2: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		},
		3: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		},
		4: {
			icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
		}
	};
	var i;
	for (i = 0; i < locations2.length; i++) {
		if(!Array.isArray(locations2[i][0]))
		{
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations2[i][0], locations2[i][1]),
			map: map2,
			icon: icons[locations2[i][2]].icon,
		});
		markersArray.push(marker2);
		map2.setCenter(new google.maps.LatLng(locations2[i][0], locations2[i][1]));
		map2.setZoom(9);

		google.maps.event.addListener(marker, 'click', (function (marker, i) {
			var lName = locations2[i][4]
			var lAdd = locations2[i][3]
			var lLat = locations2[i][0]
			var lLon = locations2[i][1]
			return function () {
				infowindow.setContent('<h5 class="map_pop_title">' + lName + '</h5><p class="map_pop_text">' + lAdd + '</p><a href="javascript:getlatlong(' + lLat + ',' + lLon + ');" class="btn map_pop_btn">Get Direction</a>');
				infowindow.open(map2, marker);
			}
		})(marker, i));
		}
	}
	locations2 = [];
}





        function addMarker2_old(lat, lon, type, address, name, contentString) {
            locations2.push({
                "0": lat,
                "1": lon,
                "2": type,
                "3": address,
                "4": name
            });
            if (marker2 && marker2.setMap) {
                marker2.setMap(null);
            }
            var infowindow = new google.maps.InfoWindow();
            var icons = {
                1: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                2: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                3: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                },
                4: {
                    icon: '/themes/custom/jiobp/assets/images/icons/map_circle.svg'
                }
            };
            var i;
            for (i = 0; i < locations2.length; i++) {

                marker2 = new google.maps.Marker({
                    position: new google.maps.LatLng(locations2[i][0], locations2[i][1]),
                    animation: google.maps.Animation.DROP,
                    map: map2,
                    icon: icons[locations2[i][2]].icon,
                });
                markersArray2.push(marker2);
                map2.setCenter(new google.maps.LatLng(locations2[i][0], locations2[i][1]));
                map2.setZoom(9);

                google.maps.event.addListener(marker2, 'click', (function (marker2, i) {
                    return function () {
                        infowindow.setContent('<h5 class="map_pop_title">' + locations2[i][4] + '</h5><p class="map_pop_text">' + locations2[i][3] + '</p><a href="javascript:getlatlong(' + locations2[i][0] + ',' + locations2[i][1] + ');" class="btn map_pop_btn">Get Direction</a>');
                        infowindow.open(map2, marker2);
                    }
                })(marker2, i));
            }
            locations2 = [];
        }

        initialize2();

        var resultBtn2 = document.querySelector(".show_result2");
        var reset_btn2 = document.querySelector(".reset_btn2");
        var form__results2 = document.querySelector(".form__results2");
        var form__inputs2 = document.querySelector(".form__inputs2");
        var stateSelect2 = document.querySelector("#stateSelect2");
        var setstate2 = document.querySelector("#setstate2");
        var form__resetSec2 = document.querySelector(".form__resetSec2");

        resultBtn2.addEventListener("click", function () {
            if (stateSelect2.value !== "") {

                var xhr = new XMLHttpRequest();
                xhr.withCredentials = true;
                xhr.addEventListener("readystatechange", function () {
                    if (this.readyState === 4) {
                        setstate2.innerText = stateSelect2.value;
                        form__results2.classList.remove("d-none");
                        form__resetSec2.classList.remove("d-none");
                        form__inputs2.classList.add("d-none");
                        /* MAP */
                        var data_html = '';
                        var fuelLocation = JSON.parse(this.responseText);
                        for (i = 0; i < fuelLocation.length; i++) {
                            data_html += '<div class="result_card" onclick="javascript:addMarker2(\'' + fuelLocation[i]['field_latitude'] + '\', \'' + fuelLocation[i]['field_longitude'] + '\', 1, \'' + fuelLocation[i]['field_address'].replace(/<[^>]*>?/gm, '').replace(/^\s+|\s+$/g, '') + '\', \'' + fuelLocation[i]['title'] + '\',15);">';
                            data_html += '<h3 class = "result_card_title">' + fuelLocation[i]['title'] + '</h3>';
                            data_html += '<div class = "result_card_ro" >';
                            data_html += '<div class = "ro_">';
                            data_html += fuelLocation[i]['field_address']
                            data_html += '</div>';
                            data_html += '</div>';
                            data_html += '</div>';
                            locations2.push({
                                "0": fuelLocation[i]['field_latitude'],
                                "1": fuelLocation[i]['field_longitude'],
                                "2": 1,
                                "3": fuelLocation[i]['field_address'],
                                "4": fuelLocation[i]['title'],
				"5": 10
                            });
                        }
			if(fuelLocation.length == 0)
			{
			   data_html = '<div class="result_card"><h3 class = "result_card_title">This area has no stations yet, please try another location</p></div>';
			}
                        form__results2.innerHTML = data_html;
                        addMarker3(locations2);
                        locations2 = [];
                    }
                });
                xhr.open("GET", "/api/locator/On-the-Go-charging-station/" + stateSelect2.value + "?_format=json");
                xhr.send();
            }
        });

        reset_btn2.addEventListener("click", function () {
            //form__results2.classList.add("d-none");
            //form__inputs2.classList.remove("d-none");
            //form__resetSec2.classList.add("d-none");
            window.location.reload();
        });
	}
        $(document).ready(function () {
            //$('[data-toggle="popover"]').popover();
        });

$(document).ready(function () {
    // Extract job_id from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const jobId = urlParams.get('id');
  //  $('#job-id').val(jobId);

    $.validator.addMethod("filesize", function(value, element, param) {
    if (element.files && element.files[0]) {
        var fileSize = element.files[0].size;  // Get the file size in bytes
        return this.optional(element) || fileSize < param;  // Return true if file size is less than 2 MB
    }
    return true; // If no file selected, return true (optional)
}, "File size must be less than 2 MB.");

    $('#resume-form').validate({
        rules: {
            'full-name': { required: true, minlength: 3 },
            'contact-number': { required: true, digits: true, minlength: 10, maxlength: 10 },
            'email-address': { required: true, email: true },
            dob: { required: true },
            'resume-upload': {
            required: true,
            accept: "docx,doc,pdf,rtf,txt",
            extension: "docx|doc|pdf|rtf|txt",
            filesize: 2097152 // 2MB in bytes (2 * 1024 * 1024)
        },
            'privacy-notice': { required: true }
        },
        messages: {
            'resume-upload': {
                required: "Please upload your resume.",
                accept: "Please upload a valid file (doc, docx, pdf, rtf, txt).",
                filesize: "File size must be less than 2 MB."
            }
        },
        errorPlacement: function (error, element) {
            error.addClass('text-danger');
            error.insertAfter(element.parent()); // Insert error message after the element's parent (form group)
        },
        submitHandler: function (form) {
            // Collect form data as FormData object
            const formData = new FormData(form);
            const jobTitle = $('#job-title').text();
            formData.append('job_title', jobTitle);
            document.getElementById("job-req-id").value = document.getElementById("job-id").value;
            document.getElementById("job-name").value = jobTitle;
            console.log($('#job-id').val());
            formData.append('job_id', $('#job-id').val());
            formData.append('resume-upload', $('#resume-upload')[0].files[0]); // Add file to FormData
            // AJAX call to submit the form data
            $.ajax({
                url: '/hrfitui/formSubmit.php', // Ensure this matches your route
                type: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from processing data
                contentType: false, // Prevent jQuery from setting Content-Type
                success: function (response) {
                    const parsedData = JSON.parse(response);

                    if (parsedData.message=="success") {
                        $('#resume-form').hide();
                        $('#head-modal').css('visibility', 'hidden');
                        $('#modal-cont').css('top', '25%');
                        $('#thank-you-message').show();
                        $('#app-id').text(parsedData.application_id);
                        document.getElementById("app-id").value = parsedData.application_id;
                        $('#job-name').text(jobTitle);
                        $('#job-req-id').text($('#job-id').val()); 
                   } else {
                        alert('Error: ' + response);
                    }
                },
                error: function (response) {
                    /*
                    $('#resume-form').hide();
                    $('#head-modal').css('visibility', 'hidden');
                    $('#modal-cont').css('top', '25%');
                    $('#thank-you-message').show();
                    $('#app-id').text("222222222222222222222222222222");
                    $('#job-name').text(jobTitle);
                    $('#job-req-id').text($('#job-id').val()); 
                    document.getElementById("app-id").value = '1234567890';
                    */
                    alert('An error occurred while submitting the form.');
                }
            });
        }
    });
});
