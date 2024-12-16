
// on scroll navbar
$(window).scroll(function () {
    var scroll = $(window).scrollTop();

    if (scroll >= 100) {
        $(".jio_navbar").addClass("onscroll");
    } else {
        $(".jio_navbar").removeClass("onscroll");
    }
});

var customMenuBtn = document.querySelector("[data-target='#navbarSupportedContent']");
var megaMenuBtn = document.querySelector(".tb-megamenu .btn-navbar");
customMenuBtn.addEventListener("click", function(){
    megaMenuBtn.click();
});

var megaSubMenuBtn = document.querySelectorAll(".tb-megamenu-item.mega.dropdown .tb-megamenu-no-link");
megaSubMenuBtn.forEach(function(ele){
    var cdropdown = ele.parentElement.querySelector(".tb-megamenu-submenu");
	
//if (window.innerWidth < 768){
//	   ele.addEventListener("click", () => {
//        cdropdown.classList.toggle("d-block");
//    })
//}

    ele.addEventListener("mouseover", () => {
        cdropdown.classList.add("d-block");
    })
  ele.addEventListener("mouseout", () => {
        cdropdown.classList.remove("d-block");
    })
});

// aos
AOS.init({
    // Global settings:
    disable: false, // accepts following values: 'phone', 'tablet', 'mobile', boolean, expression or function
    startEvent: 'DOMContentLoaded', // name of the event dispatched on the document, that AOS should initialize on
    initClassName: 'aos-init', // class applied after initialization
    animatedClassName: 'aos-animate', // class applied on animation
    useClassNames: false, // if true, will add content of `data-aos` as classes on scroll
    disableMutationObserver: false, // disables automatic mutations' detections (advanced)
    debounceDelay: 50, // the delay on debounce used while resizing window (advanced)
    throttleDelay: 99, // the delay on throttle used while scrolling the page (advanced)


    // Settings that can be overridden on per-element basis, by `data-aos-*` attributes:
    offset: 0, // offset (in px) from the original trigger point
    delay: 0, // values from 0 to 3000, with step 50ms
    duration: 1200, // values from 0 to 3000, with step 50ms
    easing: 'ease-in-out', // default easing for AOS animations
    once: true, // whether animation should happen only once - while scrolling down
    mirror: false, // whether elements should animate out while scrolling past them
    anchorPlacement: 'top-bottom', // defines which position of the element regarding to window should trigger the animation

});


// Replace all SVG images with inline SVG
function svgInline() {
    jQuery('img.svg').each(function () {
        var $img = jQuery(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        jQuery.get(imgURL, function (data) {
            // Get the SVG tag, ignore the rest
            var $svg = jQuery(data).find('svg');

            // Add replaced image's ID to the new SVG
            if (typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if (typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass + ' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Replace image with new SVG
            $img.replaceWith($svg);

        }, 'xml');
    });
}


// serve swiper starts
var serveSwiper = new Swiper(".serveSwiper", {
    slidesPerView: 1.1,
    spaceBetween: 14,
    autoHeight: false,
    speed: 800,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        768: {
            slidesPerView: 3,
            spaceBetween: 40,
        }
    },
});
// serve swiper ends



// news swiper starts
var newsSwiper = new Swiper(".newsSwiper", {
    // slidesPerView: 1.15,
    spaceBetween: 0,
    autoHeight: false,
    speed: 800,
    enabled: false,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        768: {
            slidesPerView: 3,
            spaceBetween: 30,
            enabled: true,
            direction: "horizontal"
        }
    },
});
// news swiper ends



// banner swiperv1 starts
var bannerSwiperv1 = new Swiper(".bannerSwiperv1", {
    slidesPerView: 1,
    spaceBetween: 0,
    autoHeight: false,
    speed: 800,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        768: {
            slidesPerView: 1,
            spaceBetween: 0,
        }
    },
});
// banner swiperv1 ends

// banner swiperv2 starts
var bannerSwiperv2 = new Swiper(".bannerSwiperv2", {
    slidesPerView: 1,
    spaceBetween: 0,
    autoHeight: false,
    speed: 800,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        768: {
            slidesPerView: 1,
            spaceBetween: 0,
            pagination:false,
            // pagination: false,
        }
    },
});
// banner swiperv2 ends

// banner swiperv2 starts
var bannerSwiperv3 = new Swiper(".bannerSwiperv3", {
    slidesPerView: 1,
    spaceBetween: 30,
    autoHeight: false,
    speed: 800,
    pagination: false,
    enabled: true,
    breakpoints: {
        768: {
            slidesPerView: 1,
            spaceBetween: 30,
        }
    },
});
// banner swiperv2 ends

// press release swiper starts
var pressReleaseSwiper = new Swiper(".press-releaseSwiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    autoHeight: false,
    speed: 800,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    enabled: true,
    breakpoints: {
        768: {
            slidesPerView: 1,
            spaceBetween: 30,
        }
    },
});
// press release swiper ends


// press release swiper starts
var queriesSwiper = new Swiper(".queriesSwiper", {
    slidesPerView: 1,
    spaceBetween: 0,
    autoHeight: false,
    speed: 800,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        768: {
            slidesPerView: 1,
            spaceBetween: 0,
        }
    },
});
// press release swiper ends

const swiperMoreSlidar = new Swiper(".get-moreSlidar", {
    slidesPerView: 1,
    spaceBetween: 30,
    grabCursor: true,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        640: {
            slidesPerView: 1,
        },
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 2,
        },
    },
});

// window load 
$(window).on('load', function () {
    svgInline();
    if (localStorage.getItem("modalSeen") != 'shown') {
        //localStorage.setItem("modalSeen", "shown")
        $('#fraudAdvisory').modal('show');
    }
})
// why jio swiper ends
// var swiper = new Swiper(".whyJioSwiper", {
//     direction: "vertical",
//     slidesPerView: 1,
//     mousewheel: true,
//     reachEnd: true,
//     freeMode: {
//         enabled: true,
//         sticky: true,
//         minimumVelocity: 0
//     },
//     autoplay: {
//         delay: 8000,
//         disableOnInteraction: false,
//     },
//     breakpoints: {
//         768: {
//             pagination: {
//                 el: ".swiper-pagination",
//                 clickable: true,
//             },
//         }
//     }

// });

$('select').on('change', function () {
    if ($(this).val() == '') {
        $(this).removeClass('active')
    }
    else {
        $(this).addClass('active');
    }
})

var investorSwiper = new Swiper(".investorSwiper", {
    slidesPerView: 1.15,
    spaceBetween: 10,
    autoHeight: false,
    speed: 800,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    breakpoints: {
        768: {
            slidesPerView: 1,
            spaceBetween: 30,
            // centeredSlides: true,
        }
    },
});

var swiper = new Swiper(".whyJioSwiper", {
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    mousewheel: true,
    reachEnd: true,
autoplay: {
    delay: 2000,
  },

    freeMode: {
        enabled: true,
        sticky: true,
        minimumVelocity: 0
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    spaceBetween: 20,
    breakpoints: {
        640: {
            slidesPerView: 1,
autoplay: {
    delay: 2000,
  },

        },
        768: {
            slidesPerView: 2,
autoplay: {
    delay: 2000,
  },

        },
        1024: {
            slidesPerView: 2.5,
            spaceBetween: 38,
            pagination:false,
autoplay:false,
        },
    },
});


// serve swiper starts
var swiper = new Swiper(".fuelSwiper", {
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    slidesPerView: 1,
    spaceBetween: 10,
    autoHeight: false,
    speed: 800,
    breakpoints: {
        640: {
            slidesPerView: 1,
        },
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 2.2,
            spaceBetween: 17,
            pagination:false,
        },
    },
});
// serve swiper ends

$(document).on('click', '.searchLink', function(e) {
    e.preventDefault();
    console.log("hih");
    console.log(this);
    $(this).find('img').attr('src','/themes/custom/jiobp/assets/images/icons/close-search-box.svg');
    $(this).addClass('closeSearch');
    $('.search-bar').slideDown().css('display', 'flex');
   $(".search-container").addClass('show');
})

$(document).on('click', '.closeSearch', function(e) {
    e.preventDefault();
    $('.search-bar').slideUp();
    $('.searchLink').find('img').attr('src','/themes/custom/jiobp/assets/images/icons/search-icon.svg');
    $(this).removeClass('closeSearch');
    $(".search-container").removeClass('show');
});

//var searchInput = document.querySelector(".search-bar input");
//var searchContainer = document.querySelector(".search-container");

//searchInput.addEventListener("focus", function() {
//  searchContainer.classList.add("show");
//  searchContainer.classList.add("show");
//});
//searchInput.addEventListener("blur", function() {
//  searchContainer.classList.remove("show");
//});
//searchContainer.addEventListener("mouseover", function() {
//  searchContainer.classList.add("show");
//});
//searchContainer.addEventListener("mouseout", function() {
//  searchContainer.classList.remove("show");
//});


function reveal() {
    var reveals = document.querySelectorAll(".curveAnim");

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

window.addEventListener("scroll", reveal);

if(document.body.classList.contains("wild-bean-cafe")){
var bannerContainer = document.querySelector(".jbp-commonBanner .topShift");
var bannerTitle = document.querySelector(".jbp-commonBanner .block_info--title");
var bannerCol = bannerTitle.parentElement;
bannerCol.classList.add("col-md-9");
bannerContainer.classList.add("-custom");
}

if(window.innerWidth > 1023) {
var appSections = document.querySelectorAll(".appSection");
if(appSections) {
appSections.forEach(function(ele){
    var appCont = ele.querySelector(".appSection_container");
    var appImg = ele.querySelector(".appSection_img img");
    var calcDiff = appImg.naturalHeight - appCont.offsetHeight;
    ele.style.paddingTop = calcDiff + "px";
})
}
}


if(document.body.classList.contains("loyalty-program-V3")) {


        function random(min,max){
             return Math.round(Math.random() * (max-min) + min);
        }
        
        
        
        function dropBox(){
          var length = random(5, ($(".coins").width()));
          var velocity = random(8500, 12000);
          var gift_velocity = random(6500, 12000);
          var size = random(50, 90);
          var thisBox = $("<div/>", {
            class: "box",
            style:  "width:" +size+ "px; height:"+size+"px; left:" + length+  "px; transition: transform " +velocity+ "ms linear;"
          });
          var thisBox1 = $("<div/>", {
            class: "box",
            style:  "width:" +size+ "px; height:"+size+"px; left:" + length+  "px; transition: transform " +gift_velocity+ "ms linear;"
          });

          var this1Box = $("<div/>", {
            class: "box",
            style:  "width:" +size+ "px; height:"+size+"px; left:" + length+  "px; transition: transform " +velocity+ "ms linear;"
          });
          var this1Box1 = $("<div/>", {
            class: "box",
            style:  "width:" +size+ "px; height:"+size+"px; left:" + length+  "px; transition: transform " +gift_velocity+ "ms linear;"
          });
          
          //set data and bg based on data
          thisBox.data("test", Math.round(Math.random()));
          if(thisBox.data("test")){
            thisBox.css({"background": "url('/themes/custom/jiobp/assets/images/coins_bg1.png')", "background-size":"contain"});
          } else {
            thisBox.css({"background": "url('/themes/custom/jiobp/assets/images/coins_bg2.png')", "background-size":"contain"});
    
          }

          thisBox1.data("test", Math.round(Math.random()));
          if(thisBox1.data("test")){
            thisBox1.css({"background": "url('/themes/custom/jiobp/assets/images/gift_bg1.png')", "background-size":"contain"});
          } else {
            thisBox1.css({"background": "url('/themes/custom/jiobp/assets/images/gift_bg2.png')", "background-size":"contain"});
    
          }


          this1Box.data("test", Math.round(Math.random()));
          if(this1Box.data("test")){
            this1Box.css({"background": "url('/themes/custom/jiobp/assets/images/coins_bg1.png')", "background-size":"contain"});
          } else {
            this1Box.css({"background": "url('/themes/custom/jiobp/assets/images/coins_bg2.png')", "background-size":"contain"});
    
          }

          this1Box1.data("test", Math.round(Math.random()));
          if(this1Box1.data("test")){
            this1Box1.css({"background": "url('/themes/custom/jiobp/assets/images/gift_bg1.png')", "background-size":"contain"});
          } else {
            this1Box1.css({"background": "url('/themes/custom/jiobp/assets/images/gift_bg2.png')", "background-size":"contain"});
    
          }


          $(".coins").append(thisBox);
          $(".coins1").append(this1Box);
          $(".gifts").append(thisBox1);
          $(".gifts1").append(this1Box1);
          
          //random start for animation
          setTimeout(function(){
            thisBox.addClass("move");
            thisBox1.addClass("move");
            this1Box.addClass("move");
            this1Box1.addClass("move");
          }, random(0, 8000) );
          
          //remove this object when animation is over
          thisBox.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
                      function(event) {
            $(this).remove();
          });
          thisBox1.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
                      function(event) {
            $(this).remove();
          });


          this1Box.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
                      function(event) {
            $(this).remove();
          });
          this1Box1.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
                      function(event) {
            $(this).remove();
          });


        }
        
        for (i = 0; i < 2; i++) { 
          dropBox();
        }
        
        
        
        var runGame = setInterval(function(){
        for (i = 0; i < 2; i++) { 
            dropBox();
        }  
        }, 5000);
        
        $('#stars').sparkle({ size: 20 }).sparkle({
            size: 20
        }).sparkle({
            size: 20
        }).sparkle({
            size: 20
        }).sparkle({
            size: 20
        }).sparkle({
            size: 20
        }).sparkle({
            delay: 50,
            pause: 50,
            size: 20
        }).sparkle({
            delay: 100,
            pause: 100,
            size: 20
        }).sparkle({
            delay: 150,
            pause: 150,
            size: 20
        }).sparkle({
            delay: 200,
            pause: 200,
            size: 20
        }).sparkle({
            delay: 250,
            pause: 250,
            size: 20
        }).sparkle({
            delay: 300,
            pause: 300,
            size: 20
        }).sparkle({
            delay: 50,
            pause: 50,
            size: 20
        }).sparkle({
            delay: 100,
            pause: 100,
            size: 20
        }).sparkle({
            delay: 150,
            pause: 150,
            size: 20
        }).sparkle({
            delay: 200,
            pause: 200,
            size: 20
        }).sparkle({
            delay: 250,
            pause: 250,
            size: 20
        }).sparkle({
            delay: 300,
            pause: 300,
            size: 20
        });
        

}

if(document.body.classList.contains("jio-bp-home")){

console.log(":: Home ::");

function setSlider() {
var homeSlideMobile = document.querySelector(".homeSlideMobile");
var homeSlideDesktop = document.querySelector(".homeSlideDesktop");

if(window.innerWidth < 768){
homeSlideMobile.style.display = "block";
homeSlideDesktop.style.display = "none";
}else{
homeSlideMobile.style.display = "none";
homeSlideDesktop.style.display = "block";
}

}
setSlider();
window.addEventListener("resize", setSlider);

}
