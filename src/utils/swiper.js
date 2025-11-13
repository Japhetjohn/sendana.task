// Initialize Swiper for login/signup pages
export const initSwiper = () => {
  // Wait for Swiper library to be loaded
  if (typeof window !== 'undefined' && window.Swiper) {
    const swiper = new window.Swiper('.loginart', {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
    return swiper;
  }
  return null;
};
