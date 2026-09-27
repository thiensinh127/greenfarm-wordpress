(function () {
    'use strict';

    if (!window.wp || !window.wp.media) {
        return;
    }

    document.querySelectorAll('[data-greenfarm-gallery]').forEach(function (gallery) {
        var input = gallery.querySelector('[data-greenfarm-gallery-input]');
        var preview = gallery.querySelector('[data-greenfarm-gallery-preview]');
        var selectButton = gallery.querySelector('[data-greenfarm-gallery-select]');
        var clearButton = gallery.querySelector('[data-greenfarm-gallery-remove]');

        if (!input || !preview || !selectButton || !clearButton) {
            return;
        }

        var frame;

        function ids() {
            return input.value
                .split(',')
                .map(function (value) { return Number.parseInt(value, 10); })
                .filter(function (value, index, values) {
                    return value > 0 && values.indexOf(value) === index;
                });
        }

        function render(attachments) {
            input.value = attachments.map(function (attachment) { return attachment.id; }).join(',');

            var items = attachments.map(function (attachment) {
                var item = document.createElement('span');
                var image = document.createElement('img');
                var remove = document.createElement('button');
                var thumbnail = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                item.className = 'greenfarm-gallery-item';
                item.dataset.attachmentId = String(attachment.id);
                image.src = thumbnail;
                image.alt = attachment.alt || '';
                image.loading = 'lazy';
                remove.type = 'button';
                remove.className = 'button-link-delete';
                remove.dataset.greenfarmGalleryRemoveItem = '';
                remove.dataset.attachmentId = String(attachment.id);
                remove.textContent = 'Remove';
                remove.ariaLabel = 'Remove image';
                item.append(image, remove);

                return item;
            });

            preview.replaceChildren.apply(preview, items);
        }

        selectButton.addEventListener('click', function (event) {
            event.preventDefault();

            if (!frame) {
                frame = window.wp.media({
                    title: 'Choose product images',
                    button: { text: 'Use these images' },
                    library: { type: 'image' },
                    multiple: true
                });

                frame.on('open', function () {
                    var selection = frame.state().get('selection');
                    selection.reset();
                    ids().forEach(function (id) {
                        var attachment = window.wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment);
                    });
                });

                frame.on('select', function () {
                    render(frame.state().get('selection').toJSON());
                });
            }

            frame.open();
        });

        preview.addEventListener('click', function (event) {
            var button = event.target.closest('[data-greenfarm-gallery-remove-item]');
            if (!button) {
                return;
            }

            event.preventDefault();
            var attachmentId = Number.parseInt(button.dataset.attachmentId, 10);
            input.value = ids().filter(function (id) { return id !== attachmentId; }).join(',');
            button.parentElement.remove();
        });

        clearButton.addEventListener('click', function (event) {
            event.preventDefault();
            input.value = '';
            preview.replaceChildren();
        });
    });
}());
