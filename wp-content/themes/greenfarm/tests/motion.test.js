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

function styles() {
    const values = new Map();
    return {
        setProperty: (name, value) => values.set(name, value),
        getPropertyValue: (name) => values.get(name) || ''
    };
}

function section(children = []) {
    return {
        classList: classes(),
        querySelectorAll: () => children
    };
}

test('reveals intersecting sections once and stops observing them', () => {
    assert.equal(fs.existsSync(motionPath), true, 'motion.js must exist');

    const source = fs.readFileSync(motionPath, 'utf8');
    const first = section();
    const second = section();
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
    const child = { style: styles() };
    const first = section([child]);
    const second = section();
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
    assert.equal(child.style.getPropertyValue('--reveal-delay'), '');
});

test('stagger delays reveal children and caps the sequence at 180ms', () => {
    const source = fs.readFileSync(motionPath, 'utf8');
    const children = Array.from({ length: 5 }, () => ({ style: styles() }));
    const parent = section(children);

    class IntersectionObserver {
        observe() {}
    }

    vm.runInNewContext(source, {
        document: {
            documentElement: { classList: classes() },
            querySelectorAll: () => [parent]
        },
        window: { matchMedia: () => ({ matches: false }) },
        IntersectionObserver
    });

    assert.deepEqual(
        children.map((child) => child.style.getPropertyValue('--reveal-delay')),
        ['0ms', '60ms', '120ms', '180ms', '180ms']
    );
});

test('missing IntersectionObserver leaves content visible without enabling motion', () => {
    const source = fs.readFileSync(motionPath, 'utf8');
    const root = { classList: classes() };
    const parent = section();

    assert.doesNotThrow(() => {
        vm.runInNewContext(source, {
            document: {
                documentElement: root,
                querySelectorAll: () => [parent]
            },
            window: { matchMedia: () => ({ matches: false }) }
        });
    });
    assert.equal(root.classList.contains('has-reveal-motion'), false);
    assert.equal(parent.classList.contains('is-visible'), false);
});
