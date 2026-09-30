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
    return {
        classList: classes(),
        dataset: { index: String(index) },
        addEventListener() {},
        getAttribute: (name) => attributes.get(name),
        setAttribute: (name, value) => attributes.set(name, value)
    };
}

test('hero carousel advances the visible slide', () => {
    assert.equal(fs.existsSync(carouselPath), true, 'hero-carousel.js must exist');

    const source = fs.readFileSync(carouselPath, 'utf8');
    const slides = [item(0), item(1)];
    const controls = [item(0), item(1)];
    const listeners = new Map();
    let advance;

    vm.runInNewContext(source, {
        document: {
            querySelectorAll: () => [{
                addEventListener: (name, handler) => listeners.set(name, handler),
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
