document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const image = gallery.querySelector('[data-main-photo]');
    const thumbnails = gallery.querySelectorAll('[data-photo]');

    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            image.src = thumbnail.dataset.photo;
            image.alt = thumbnail.dataset.alt;

            thumbnails.forEach((button) => {
                button.setAttribute('aria-pressed', String(button === thumbnail));
            });
        });
    });
});
