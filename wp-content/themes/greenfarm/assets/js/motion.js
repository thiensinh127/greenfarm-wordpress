(function () {
    'use strict';

    var sections = Array.from(document.querySelectorAll('[data-reveal]'));

    if (!sections.length) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        sections.forEach(function (section) {
            section.classList.add('is-visible');
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.12
    });

    document.documentElement.classList.add('has-reveal-motion');
    sections.forEach(function (section) {
        observer.observe(section);
    });
}());
