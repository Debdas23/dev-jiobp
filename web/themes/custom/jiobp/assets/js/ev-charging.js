const swiperrrrr = new Swiper(".fleet_charging", {
  // Optional parameters
  direction: "horizontal",
  slidesPerView: 1,
  userSelect: false,
  simulateTouch:false,

  // if we navigate to the next slide, we want to slide to the left
  navigation: {
    nextEl: ".fleet_charging .swiper-button-next",
    prevEl: ".fleet_charging .swiper-button-prev",
  },
});
const swiperrrrr2 = new Swiper(".station_charging", {
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

$(document).ready(function () {
  $('[data-toggle="popover"]').popover();
  $(".fleet .map_btn").popover("show");
  $(".station .map_btn").popover("show");
});
