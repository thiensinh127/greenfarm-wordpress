const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const motionPath = path.join(__dirname, '..', 'assets', 'js', 'motion.js');

function classes() {
    const values = new Set();
    return {
        add: (...names) => names.forEach((name) => values.add(name)),
        contains: (name) => values.has(name)
    };
}

test('reveals intersecting sections once and stops observing them', () => {
    assert.equal(fs.existsSync(motionPath), true, 'motion.js must exist');

    const source = fs.readFileSync(motionPath, 'utf8');
    const first = { classList: classes() };
    const second = { classList: classes() };
    const root = { classList: classes() };
    const observed = [];
    const unobserved = [];
    let callback;

    class IntersectionObserver {
        constructor(handler) {
            callback = handler;
        }
        observe(section) {
            observed.push(section);
        }
        unobserve(section) {
            unobserved.push(section);
        }
    }

    vm.runInNewContext(source, {
        document: {
            documentElement: root,
            querySelectorAll: () => [first, second]
        },
        window: { matchMedia: () => ({ matches: false }) },
        IntersectionObserver
    });

    assert.equal(root.classList.contains('has-reveal-motion'), true);
    assert.deepEqual(observed, [first, second]);

    callback([
        { isIntersecting: true, target: first },
        { isIntersecting: false, target: second }
    ]);

    assert.equal(first.classList.contains('is-visible'), true);
    assert.equal(second.classList.contains('is-visible'), false);
    assert.deepEqual(unobserved, [first]);
});

test('reduced motion shows every section without creating an observer', () => {
    assert.equal(fs.existsSync(motionPath), true, 'motion.js must exist');

    const source = fs.readFileSync(motionPath, 'utf8');
    const first = { classList: classes() };
    const second = { classList: classes() };
    let observerCreated = false;

    class IntersectionObserver {
        constructor() {
            observerCreated = true;
        }
    }

    vm.runInNewContext(source, {
        document: {
            documentElement: { classList: classes() },
            querySelectorAll: () => [first, second]
        },
        window: { matchMedia: () => ({ matches: true }) },
        IntersectionObserver
    });

    assert.equal(first.classList.contains('is-visible'), true);
    assert.equal(second.classList.contains('is-visible'), true);
    assert.equal(observerCreated, false);
});
