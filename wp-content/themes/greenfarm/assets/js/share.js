(function () {
    'use strict';

    document.querySelectorAll('[data-share]').forEach(function (share) {
        var button = share.querySelector('[data-share-button]');
        var status = share.querySelector('[data-share-status]');

        if (!button || (!navigator.share && !navigator.clipboard)) {
            return;
        }

        button.hidden = false;
        button.addEventListener('click', async function () {
            try {
                if (navigator.share) {
                    await navigator.share({
                        title: button.dataset.title,
                        url: button.dataset.url
                    });
                    return;
                }

                await navigator.clipboard.writeText(button.dataset.url);
                status.textContent = 'Link copied.';
            } catch (error) {
                if ('AbortError' !== error.name) {
                    status.textContent = 'Unable to share this link.';
                }
            }
        });
    });
}());
