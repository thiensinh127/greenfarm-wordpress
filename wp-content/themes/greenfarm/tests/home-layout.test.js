const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

const homeCssPath = path.join(__dirname, '..', 'assets', 'css', 'home.css');

test('product categories use compact desktop spacing when few category cards exist', () => {
    const source = fs.readFileSync(homeCssPath, 'utf8');

    assert.match(
        source,
        /\.home-categories\s*\{[^}]*padding-bottom:\s*clamp\(2\.5rem,\s*5vw,\s*4\.5rem\);/s,
        'category section must not inherit the oversized general section bottom padding'
    );
});

test('homepage sections use a consistent compact desktop rhythm', () => {
    const source = fs.readFileSync(homeCssPath, 'utf8');

    assert.match(
        source,
        /\.home-section\s*\{[^}]*padding-block:\s*clamp\(3rem,\s*6vw,\s*5rem\);/s,
        'shared homepage sections must not create 8rem gaps after short content'
    );
});

test('farm stories do not add a second border directly beneath the farm card', () => {
    const source = fs.readFileSync(homeCssPath, 'utf8');

    assert.match(
        source,
        /\.home-section \+ \.home-stories--featured\s*\{[^}]*border:\s*0;[^}]*padding-top:\s*clamp\(2rem,\s*4vw,\s*3rem\);/s,
        'Farm stories need a selector that overrides the shared adjacent-section spacing rule'
    );
});

test('hero headlines preserve whole words in the editorial composition', () => {
    const source = fs.readFileSync(homeCssPath, 'utf8');

    assert.match(
        source,
        /\.home-hero h1\s*\{[^}]*overflow-wrap:\s*normal;[^}]*word-break:\s*normal;/s,
        'hero title must not split a brand or a word across lines'
    );
});

test('hero carousel controls have accessible touch targets', () => {
    const source = fs.readFileSync(homeCssPath, 'utf8');

    assert.match(
        source,
        /\.home-hero__carousel-control\s*\{[^}]*height:\s*1\.5rem;[^}]*width:\s*1\.5rem;/s,
        'carousel dots need a 24px interactive target'
    );
    assert.match(
        source,
        /\.home-hero__carousel-control::before\s*\{[^}]*height:\s*0\.6rem;[^}]*width:\s*0\.6rem;/s,
        'carousel dots should retain their compact visual size inside the target'
    );
});
