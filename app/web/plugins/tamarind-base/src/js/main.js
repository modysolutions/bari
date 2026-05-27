// Initialize Swiper for the Alerts Header Carousel.
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.header-alerts-carousel--slideshow', {
            direction: 'horizontal',
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            loop: true,
            speed: 500,
            slidesPerView: 'auto',
            spaceBetween: 20,
            allowTouchMove: false
        });
    }
});

/**
 * Add 'sticky-active' class to elements with 'position-sticky' when they become sticky.
 */
function initStickySimple() {
    const stickyElements = document.querySelectorAll('.position-sticky');
    
    stickyElements.forEach(element => {
        const marker = document.createElement('div');
        marker.style.height = '1px';
        marker.style.position = 'relative';
        marker.style.top = '-1px';
        element.parentNode.insertBefore(marker, element);
        
        let lastState = null;
        
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    const isStuck = entry.boundingClientRect.top < entry.rootBounds.top;
                    
                    if (isStuck && lastState !== true) {
                        element.classList.add('sticky-active');
                        lastState = true;
                    } else if (!isStuck && lastState !== false) {
                        element.classList.remove('sticky-active');
                        lastState = false;
                    }
                });
            },
            {
                rootMargin: '0px 0px 0px 0px',
                threshold: 0
            }
        );
        
        observer.observe(marker);
    });
}
document.addEventListener('DOMContentLoaded', initStickySimple);


// Toggle alert list items open/closed
document.addEventListener("click", (e) => {
	const btn = e.target.closest(".tm-list--alert-button");
	if (!btn) return;

	const li = btn.closest(".tm-list__item--alert");
	const icon = btn.querySelector("i");
	const targetId = btn.getAttribute("aria-controls");
	const target = document.getElementById(targetId);

	li.classList.toggle("is-open");

	const expanded = li.classList.contains("is-open");
	btn.setAttribute("aria-expanded", String(expanded));

	if (expanded) {
		icon.classList.replace("fa-plus", "fa-minus");
		btn.setAttribute("title", "Read less");
	} else {
		icon.classList.replace("fa-minus", "fa-plus");
		btn.setAttribute("title", "Read more");
	}
});
