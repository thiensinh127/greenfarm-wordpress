(function () {
    'use strict';

    var header = document.querySelector('.site-header');
    var navigation = document.getElementById('primary-navigation');
    var toggle = document.querySelector('.site-header__menu-toggle');

    if (!header || !toggle) {
        return;
    }

    function setMenuState(open) {
        header.classList.toggle('is-menu-open', open);
        toggle.setAttribute('aria-expanded', String(open));
    }

    toggle.addEventListener('click', function () {
        setMenuState(toggle.getAttribute('aria-expanded') !== 'true');
    });

    navigation.addEventListener('click', function (event) {
        if (event.target.closest('a')) {
            setMenuState(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setMenuState(false);
            toggle.focus();
        }
    });
}());
