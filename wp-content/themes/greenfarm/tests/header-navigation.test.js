const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const navigationPath = path.join(__dirname, '..', 'assets', 'js', 'header-navigation.js');
const stylePath = path.join(__dirname, '..', 'style.css');

function classList() {
    const values = new Set();
    return {
        add: (name) => values.add(name),
        contains: (name) => values.has(name),
        remove: (name) => values.delete(name),
        toggle: (name, force) => {
            const active = force === undefined ? !values.has(name) : force;
            active ? values.add(name) : values.delete(name);
            return active;
        }
    };
}

test('mobile header toggle keeps its open state and aria state synchronized', () => {
    assert.equal(fs.existsSync(navigationPath), true, 'header-navigation.js must exist');

    const source = fs.readFileSync(navigationPath, 'utf8');
    const listeners = new Map();
    const navigationListeners = new Map();
    const toggle = {
        attributes: new Map([['aria-expanded', 'false']]),
        addEventListener: (event, handler) => listeners.set(event, handler),
        focus: () => {},
        getAttribute: (name) => toggle.attributes.get(name),
        setAttribute: (name, value) => toggle.attributes.set(name, value)
    };
    const header = { classList: classList() };
    const navigation = {
        addEventListener: (event, handler) => navigationListeners.set(event, handler)
    };

    vm.runInNewContext(source, {
        document: {
            getElementById: () => navigation,
            querySelector: (selector) => selector === '.site-header' ? header : toggle,
            addEventListener: () => {}
        }
    });

    listeners.get('click')();
    assert.equal(header.classList.contains('is-menu-open'), true);
    assert.equal(toggle.attributes.get('aria-expanded'), 'true');

    listeners.get('click')();
    assert.equal(header.classList.contains('is-menu-open'), false);
    assert.equal(toggle.attributes.get('aria-expanded'), 'false');

    listeners.get('click')();
    navigationListeners.get('click')({ target: { closest: () => ({}) } });
    assert.equal(header.classList.contains('is-menu-open'), false);
    assert.equal(toggle.attributes.get('aria-expanded'), 'false');
});

test('mobile navigation panel overlays content instead of changing page layout', () => {
    const source = fs.readFileSync(stylePath, 'utf8');

    assert.match(
        source,
        /@media \(max-width: 47\.99rem\)[\s\S]*?\.primary-navigation\s*\{[^}]*opacity:\s*0;[^}]*position:\s*absolute;[^}]*transform:\s*translateY\(-0\.75rem\);[^}]*visibility:\s*hidden;/s,
        'closed mobile menu must remain an animated overlay rather than take up document space'
    );
    assert.match(
        source,
        /\.site-header\.is-menu-open \.primary-navigation\s*\{[^}]*opacity:\s*1;[^}]*transform:\s*none;[^}]*visibility:\s*visible;/s,
        'open mobile menu must fade and slide into view'
    );
});
