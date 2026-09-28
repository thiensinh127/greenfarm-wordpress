const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const galleryPath = path.join(__dirname, '..', 'assets', 'js', 'product-gallery.js');

function element(selectors = {}) {
    const listeners = new Map();
    const node = {
        children: [],
        dataset: {},
        value: '',
        querySelector: (selector) => selectors[selector] || null,
        addEventListener: (type, handler) => listeners.set(type, handler),
        dispatch(type, event = {}) {
            listeners.get(type)?.({ preventDefault() {}, target: node, ...event });
        },
        click() {
            node.dispatch('click');
        },
        append(...children) {
            children.forEach((child) => {
                child.parentElement = node;
                node.children.push(child);
            });
        },
        replaceChildren(...children) {
            node.children = [];
            node.append(...children);
        },
        remove() {
            if (node.parentElement) {
                node.parentElement.children = node.parentElement.children.filter((child) => child !== node);
            }
        },
        closest(selector) {
            return '[data-greenfarm-gallery-remove-item]' === selector && undefined !== node.dataset.greenfarmGalleryRemoveItem ? node : null;
        }
    };
    return node;
}

function galleryEnvironment() {
    const input = element();
    input.value = '5,2';
    const preview = element();
    const select = element();
    const clear = element();
    const root = element({
        '[data-greenfarm-gallery-input]': input,
        '[data-greenfarm-gallery-preview]': preview,
        '[data-greenfarm-gallery-select]': select,
        '[data-greenfarm-gallery-remove]': clear
    });
    const handlers = new Map();
    const preselected = [];
    let selected = [];
    let mediaOptions = null;

    const selection = {
        reset: () => { preselected.length = 0; },
        add: (attachment) => preselected.push(attachment.id),
        toJSON: () => selected
    };
    const frame = {
        on: (name, handler) => handlers.set(name, handler),
        open: () => handlers.get('open')?.(),
        state: () => ({ get: () => selection })
    };
    const media = (options) => {
        mediaOptions = options;
        return frame;
    };
    media.attachment = (id) => ({ id, fetch() {} });

    const document = {
        querySelectorAll: () => [root],
        createElement: () => element()
    };

    return {
        clear,
        document,
        frame,
        handlers,
        input,
        media,
        getMediaOptions: () => mediaOptions,
        preselected,
        preview,
        select,
        setSelected: (attachments) => { selected = attachments; }
    };
}

test('selecting and removing gallery images keeps ordered IDs and previews synchronized', () => {
    assert.equal(fs.existsSync(galleryPath), true, 'product-gallery.js must exist');
    const source = fs.readFileSync(galleryPath, 'utf8');
    const env = galleryEnvironment();

    vm.runInNewContext(source, {
        document: env.document,
        window: {
            greenfarmProductGallery: {
                chooseImages: 'Choisir des images',
                remove: 'Retirer',
                removeImage: 'Retirer cette image',
                useImages: 'Utiliser ces images'
            },
            wp: { media: env.media }
        }
    });

    env.select.click();
    assert.deepEqual(env.preselected, [5, 2]);
    assert.equal(env.getMediaOptions().title, 'Choisir des images');
    assert.equal(env.getMediaOptions().button.text, 'Utiliser ces images');

    env.setSelected([
        { id: 9, alt: 'Tomatoes', url: 'tomatoes.jpg', sizes: { thumbnail: { url: 'tomatoes-thumb.jpg' } } },
        { id: 4, alt: '', url: 'herbs.jpg', sizes: {} }
    ]);
    env.handlers.get('select')();

    assert.equal(env.input.value, '9,4');
    assert.equal(env.preview.children.length, 2);
    assert.equal(env.preview.children[0].children[0].src, 'tomatoes-thumb.jpg');
    assert.equal(env.preview.children[0].children[0].alt, 'Tomatoes');
    assert.equal(env.preview.children[0].children[1].textContent, 'Retirer');
    assert.equal(env.preview.children[0].children[1].ariaLabel, 'Retirer cette image');

    const removeFirst = env.preview.children[0].children[1];
    env.preview.dispatch('click', { target: removeFirst });
    assert.equal(env.input.value, '4');
    assert.equal(env.preview.children.length, 1);

    env.select.click();
    assert.deepEqual(env.preselected, [4]);
    env.clear.click();
    assert.equal(env.input.value, '');
    assert.equal(env.preview.children.length, 0);
});

test('missing gallery hooks or Media Library exits without an error', () => {
    assert.equal(fs.existsSync(galleryPath), true, 'product-gallery.js must exist');
    const source = fs.readFileSync(galleryPath, 'utf8');

    assert.doesNotThrow(() => {
        vm.runInNewContext(source, {
            document: {
                querySelectorAll: () => [element()]
            },
            window: {}
        });
    });
});
