const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const carouselPath = path.join(__dirname, '..', 'assets', 'js', 'hero-carousel.js');

function classes() {
    const values = new Set();
    return {
        add: (...names) => names.forEach((name) => values.add(name)),
        contains: (name) => values.has(name),
        toggle: (name, force) => force ? values.add(name) : values.delete(name)
    };
}

function item(index) {
    const attributes = new Map();
    const listeners = new Map();
    return {
        classList: classes(),
        dataset: { index: String(index) },
        addEventListener: (name, handler) => listeners.set(name, handler),
        getAttribute: (name) => attributes.get(name),
        setAttribute: (name, value) => attributes.set(name, value),
        listeners
    };
}

test('hero carousel advances the visible slide', () => {
    assert.equal(fs.existsSync(carouselPath), true, 'hero-carousel.js must exist');

    const source = fs.readFileSync(carouselPath, 'utf8');
    const slides = [item(0), item(1)];
    const controls = [item(0), item(1)];
    const pauseControl = item(0);
    const listeners = new Map();
    let advance;

    vm.runInNewContext(source, {
        document: {
            querySelectorAll: () => [{
                addEventListener: (name, handler) => listeners.set(name, handler),
                querySelector: (selector) => selector === '.home-hero__carousel-toggle' ? pauseControl : null,
                querySelectorAll: (selector) => selector === '.home-hero__slide' ? slides : controls
            }]
        },
        window: {
            clearInterval() {},
            matchMedia: () => ({ matches: false }),
            setInterval: (handler) => { advance = handler; return 1; }
        }
    });

    assert.equal(slides[0].classList.contains('is-active'), true);
    assert.equal(slides[1].classList.contains('is-active'), false);
    advance();
    assert.equal(slides[1].classList.contains('is-active'), true);
    assert.equal(controls[1].getAttribute('aria-current'), 'true');
});

test('hero carousel pause control stops and resumes automatic rotation', () => {
    const source = fs.readFileSync(carouselPath, 'utf8');
    const slides = [item(0), item(1)];
    const controls = [item(0), item(1)];
    const pauseControl = item(0);
    let advance;

    vm.runInNewContext(source, {
        document: {
            querySelectorAll: () => [{
                addEventListener() {},
                querySelector: (selector) => selector === '.home-hero__carousel-toggle' ? pauseControl : null,
                querySelectorAll: (selector) => selector === '.home-hero__slide' ? slides : controls
            }]
        },
        window: {
            clearInterval: () => { advance = undefined; },
            matchMedia: () => ({ matches: false }),
            setInterval: (handler) => { advance = handler; return 1; }
        }
    });

    pauseControl.listeners.get('click')();
    assert.equal(pauseControl.getAttribute('aria-pressed'), 'true');
    assert.equal(pauseControl.getAttribute('aria-label'), 'Play carousel');
    assert.equal(advance, undefined);

    pauseControl.listeners.get('click')();
    assert.equal(pauseControl.getAttribute('aria-pressed'), 'false');
    assert.equal(pauseControl.getAttribute('aria-label'), 'Pause carousel');
    assert.equal(typeof advance, 'function');
});
