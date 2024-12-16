const swiperrrrr = new Swiper(".mediums_swiper", {
  // Optional parameters
  direction: window.innerWidth < 768 ? "horizontal" : "vertical",
  slidesPerView: "auto",
  spaceBetween: 39.5,
  grabCursor: true,

  // If we need pagination
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
});
