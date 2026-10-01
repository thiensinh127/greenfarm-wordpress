(function () {
    'use strict';

    document.querySelectorAll('[data-hero-carousel]').forEach(function (carousel) {
        var slides = Array.from(carousel.querySelectorAll('.home-hero__slide'));
        var controls = Array.from(carousel.querySelectorAll('.home-hero__carousel-control'));
        var pauseControl = carousel.querySelector('.home-hero__carousel-toggle');
        var active = 0;
        var isPaused = false;
        var timer;

        function show(index) {
            active = (index + slides.length) % slides.length;
            slides.forEach(function (slide, slideIndex) {
                var isActive = slideIndex === active;
                slide.classList.toggle('is-active', isActive);
                slide.setAttribute('aria-hidden', String(!isActive));
            });
            controls.forEach(function (control, controlIndex) {
                control.setAttribute('aria-current', controlIndex === active ? 'true' : 'false');
            });
        }

        function stop() {
            window.clearInterval(timer);
            timer = undefined;
        }
        function start() {
            if (isPaused || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
            stop();
            timer = window.setInterval(function () { show(active + 1); }, 5000);
        }

        controls.forEach(function (control) {
            control.addEventListener('click', function () { show(Number(control.dataset.index)); start(); });
        });
        if (pauseControl) {
            pauseControl.addEventListener('click', function () {
                isPaused = !isPaused;
                pauseControl.setAttribute('aria-pressed', String(isPaused));
                pauseControl.setAttribute('aria-label', isPaused ? 'Play carousel' : 'Pause carousel');

                if (isPaused) {
                    stop();
                    return;
                }

                start();
            });
        }
        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);
        show(0);
        start();
    });
}());
