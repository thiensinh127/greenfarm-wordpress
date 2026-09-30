(function ($) {
    'use strict';

    var list = $('#greenfarm-hero-slider-list');
    var field = $('#greenfarm-hero-slider-ids');
    if (!list.length || !field.length) { return; }

    function sync() {
        field.val(list.children().map(function () { return $(this).data('id'); }).get().join(','));
    }

    function add(attachment) {
        if (list.children('[data-id="' + attachment.id + '"]').length) { return; }
        list.append($('<li>', { 'data-id': attachment.id }).append($('<img>', { src: attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url, alt: '' }), ' ', $('<button>', { type: 'button', class: 'button-link-delete', text: 'Remove', 'aria-label': 'Remove image' })));
    }

    list.sortable({ update: sync });
    list.on('click', '.button-link-delete', function () { $(this).closest('li').remove(); sync(); });
    $('#greenfarm-hero-slider-add').on('click', function () {
        var frame = wp.media({ title: 'Choose hero images', button: { text: 'Use these images' }, library: { type: 'image' }, multiple: true });
        frame.on('select', function () {
            frame.state().get('selection').each(function (attachment) { add(attachment.toJSON()); });
            sync();
        });
        frame.open();
    });
}(jQuery));
