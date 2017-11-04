var loadImage = function (img) {
    var pos = img.getBoundingClientRect(),
        src = img.getAttribute('data-src');

    function loadImageWhenVisible() {
        return window.scrollY >= (pos.top - window.innerHeight * 2) ? (
            img.src = src,
                window.removeEventListener('scroll', loadImageWhenVisible)
        ) : false;
    }

    window.addEventListener('scroll', loadImageWhenVisible);
    loadImageWhenVisible();
};

$.each($("img[data-src]"), function (i, img) {
    loadImage(img);
});