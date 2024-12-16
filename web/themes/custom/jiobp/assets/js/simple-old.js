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

    $("#videoPopup").on('shown.bs.modal', function () {
        $('#popupVSrc').attr('src', videoMp4);
        $('#popupOSrc').attr('src', videoOgg);
        const swiper = document.querySelector('.bannerSwiperv2').swiper;
        swiper.disable()
        vid.autoplay = true;
        vid.load();
    });
};

function checkFooter() {
if(window.innerWidth < 768) {
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

$(document).ready(function () {

checkFooter();

    /*var selectYearInput = document.querySelector(".selectYearInput");
    selectYearInput.addEventListener("change", function (e) {
        filterCards(e.target.value);
    });
    filterCards(selectYearInput.value);*/


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

    jQuery.validator.addMethod("lettersonly", function (value, element) {
        return this.optional(element) || /^[a-z]+$/i.test(value);
    }, "Letters only please");
    $.validator.addMethod("alpha", function (value, element) {
        return this.optional(element) || value == value.match(/^[a-zA-Z\s]+$/);
    });
    //6LfJm0YgAAAAALf4taA6BFJCvYGvbD_GAgUnBce1
    $("#form_submit").on('click', function (e) {
        e.preventDefault();
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfJm0YgAAAAAFCJLESxnqiJ1-Xp6SlBB28EdM96', { action: 'request_submit' }).then(function (token) {
                $('#requestForm').prepend('<input type="hidden" name="token" id="token" value="' + token + '">');
                $('#requestForm').prepend('<input type="hidden" name="action" value="Customer Feedback ">');
                $('#requestForm').prepend('<input type="hidden" name="id" value="2">');

                $("#requestForm").trigger('submit');
            });
        });
    });

$("#form_callbacksubmit").on('click', function (e) {
        e.preventDefault();
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfJm0YgAAAAAFCJLESxnqiJ1-Xp6SlBB28EdM96', { action: 'request_submit' }).then(function (token) {
                $('#requestcallbackForm').prepend('<input type="hidden" name="token" id="token" value="' + token + '">');
                $('#requestcallbackForm').prepend('<input type="hidden" name="action" value="Customer Feedback ">');
                $('#requestcallbackForm').prepend('<input type="hidden" name="id" value="2">');

                $("#requestcallbackForm").trigger('submit');
            });
        });
    });

	$("#requestcallbackForm").validate({
                debug: true,
                errorClass: "has-error",
                errorElement: "span",
                success: function (label, element) {
                    label.hide();
                    //var parent = $('.success').parent().get(0); // This would be the <a>'s parent <li>.
                    //$(parent).addClass('has-success');
                },
                highlight: function (element, errorClass) {
                    $(element).addClass(errorClass);
                },
                errorPlacement: function (error, element) {
                    $("#" + element.attr("name") + "_error").addClass('redmessage');
                    $("#" + element.attr("name") + "_error").text(error.text());
                },
                submitHandler: function (form, event) {
                    var optionsvalue = [];
                    $(':checkbox:checked').each(function (i) {
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
                    event.preventDefault();
                    $('#form_submit').css('display', 'none');
                    $('.spinner-border').css('display', 'block');
                    var action = "/api/api/email/requestcallback";
                    $.ajax({
                        type: 'POST',
                        url: action,
                        data: JSON.stringify(request_data),
                        dataType: 'json',
                        contentType: 'application/json-patch+json',
                        success: function (data) {
                            //if (data.code === 200) {
                            if (data.isSuccess) {
                                // location.reload();
                                // alert("mail sent");
                                $('#requestcallbackForm').hide();
                                $('.thanksMsg').css('display', 'flex');
                                $('.spinner-border').css('display', 'none');
                            } else {
                                // $('.validation_error').html(data.error);
                                $('.spinner-border').css('display', 'none');
                                // location.reload();
                                // alert("mail not sent");
                            }
                        }

                    });


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

    $("#requestForm").validate({
        debug: true,
        errorClass: "has-error",
        errorElement: "span",
        success: function (label, element) {
            label.hide();
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass);
        },
        errorPlacement: function (error, element) {
            $("#" + element.attr("name") + "_error").addClass('redmessage');
            $("#" + element.attr("name") + "_error").text(error.text());
        },
        submitHandler: function (form, event) {
            var feedback_data = {
                "name": $("#name").val(),
                "mobile": $("#mobile").val(),
                "state": $("#state").val(),
                "email": $("#email").val(),
                "feedback": $("#feedback").val(),
                "product": $("#product").val(),
                "message": $("#message").val(),
                "token": $("#token").val()
            }

            event.preventDefault();
            $('#form_submit').css('display', 'none');
            $('.spinner-border').css('display', 'block');
            var action = "https://jiobp.com/api/api/email/customerfeedback";
            $.ajax({
                type: 'POST',
                url: action,
                data: JSON.stringify(feedback_data),
                dataType: 'json',
                contentType: 'application/json-patch+json',
                success: function (data) {
                    if (data.isSuccess) {
                        $('#requestForm').hide();
                        $('.thanksMsg').css('display', 'flex');
                        $('.spinner-border').css('display', 'none');
                    } else {
                        $('#validation_error').html(data.error);
                        $('#form_submit').css('display', 'inline-flex');
                        $('.spinner-border').css('display', 'none');
                    }
                }

            });


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
            }
        }
    });

$("#queryform_submit").on('click', function (e) {
        e.preventDefault();
        grecaptcha.ready(function () {
            grecaptcha.execute('6LfJm0YgAAAAAFCJLESxnqiJ1-Xp6SlBB28EdM96', { action: 'request_submit' }).then(function (token) {
                $('#queryForm').prepend('<input type="hidden" name="token" id="token" value="' + token + '">');
                $('#queryForm').prepend('<input type="hidden" name="action" value="Customer Feedback ">');
                $('#queryForm').prepend('<input type="hidden" name="id" value="2">');

                $("#queryForm").trigger('submit');
            });
        });
    });

    $("#queryForm").validate({
        debug: true,
        errorClass: "has-error",
        errorElement: "span",
        success: function (label, element) {
            label.hide();
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass);
        },
        errorPlacement: function (error, element) {
            $("#" + element.attr("name") + "_error").addClass('redmessage');
            $("#" + element.attr("name") + "_error").text(error.text());
        },
        submitHandler: function (form, event) {
            var feedback_data = {
                "name": $("#name").val(),
                "email": $("#email").val(),
                "message": $("#message").val(),
                "token": $("#token").val()
            }

            event.preventDefault();
            $('#queryform_submit').css('display', 'none');
            $('.spinner-border').css('display', 'block');
            var action = "https://jiobp.com/api/api/email/newsroom";
            $.ajax({
                type: 'POST',
                url: action,
                data: JSON.stringify(feedback_data),
                dataType: 'json',
                contentType: 'application/json-patch+json',
                success: function (data) {
                    if (data.isSuccess) {
                        $('#queryForm').hide();
                        $('.thanksMsg').css('display', 'flex');
                        $('.spinner-border').css('display', 'none');
                    } else {
                        $('#validation_error').html(data.error);
                        $('#queryform_submit').css('display', 'inline-flex');
                        $('.spinner-border').css('display', 'none');
                    }
                }

            });


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

$("#videoPopup").on('hide.bs.modal', function () {
    var vid = $("#popupVideo")[0];
    vid.pause();
    $('#popupVSrc').attr('src', '');
    $('#popupOSrc').attr('src', '');
    const swiper = document.querySelector('.bannerSwiperv2').swiper;
    swiper.enable()
});


$(window).on('load', function () {
    if ($('.loader').length) {
        $('.loader').delay(100).fadeOut('slow', function () {
            $(this).remove();
        });
    }

    $('.jio_news_each').hide();
    size_li = $(".jio_news_each").length;
    x=4;
    $('.jio_news_each:lt('+x+')').show();
    $('#loadMoreBtn').click(function (e) {
        e.preventDefault();
        x= (x+4 <= size_li) ? x+4 : size_li;
        $('.jio_news_each:lt('+x+')').show();
        if(x == size_li){
            $('#loadMoreBtn').hide();
        }
    });
});
$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
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

document.addEventListener("DOMContentLoaded", function () {
        var open_count = document.querySelector(".open_count");
        var open_offers = document.querySelector("#open-now");
	if(open_offers != null)
{
        var all_cards = open_offers.querySelectorAll(".card");
        open_count.textContent = `(${all_cards.length})`;
}
    });

var stickyMenu = document.querySelector(".sticky-menu");
if(stickyMenu != null)
{
   var productsServices = document.querySelector(".productsServices"),
    windowScrollY = window.scrollY;
    productsServicesY = productsServices.offsetTop;
    window.addEventListener("scroll", function () {
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
    if(mainBody.classList.contains("productServices")){
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

/*function filterCards(val) {
        var selectedYear = val;
        var getAllCards = Array.from(document.querySelectorAll(".pdfs-content .card"));
        // filter all cards data attribute year matching the selectYear
        var filteredCards = getAllCards.filter(function (card) {
        if (card.dataset.year == selectedYear) {
            card.style.display = "flex";
        } else {
            card.style.display = "none";
        }
        });
    }*/