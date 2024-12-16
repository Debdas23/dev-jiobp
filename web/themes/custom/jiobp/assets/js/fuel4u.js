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
    let overflow = overflowingEl.scrollHeight - parentHeight;
    stickyWrapperEl.style.height = parentHeight + overflowingEl.scrollHeight + "px";
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