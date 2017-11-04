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

$('.editable').on('click', function (target) {
    var obj = $(this);
    edit(this, obj.data('type'), obj.data('path'));
});

var edit = function (obj, type, url) {
    var css = ['font-family', 'font-size', 'font-weight', 'font-style', 'color',
        'text-transform', 'text-decoration', 'varter-spacing', 'word-spacing',
        'line-height', 'text-align', 'vertical-align', 'direction', 'background-color',
        'background-image', 'background-repeat', 'background-position',
        'background-attachment', 'opacity', 'width', 'height', 'top', 'right', 'bottom',
        'left', 'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'padding-top', 'padding-right', 'padding-bottom', 'padding-left', 'position',
        'display', 'visibility', 'z-index', 'overflow-x', 'overflow-y', 'white-space',
        'clip', 'float', 'clear', 'cursor', 'list-style-image', 'list-style-position',
        'list-style-type', 'marker-offset'];
    var $obj = (typeof obj !== 'string') ? $(obj) : $('#' + obj);
    var $replacement = $('<textarea>');
    $replacement.html($obj.html());
    $replacement.attr('class', $obj.attr('class') + ' editor');
    css.forEach(function (t) {
        $replacement.css(t, $obj.css(t));
    });
    $replacement.width($obj.width() + 5);
    $replacement.height($replacement.height() + 5);
    var callback = function (e) {
        var target = $(e.target);
        if (!target.hasClass('editor') && !target.hasClass('editable')) {
            submit($obj, $replacement, url, type, callback);
        }
    };
    $replacement.keypress(function (key) {
        if (key.key === "Enter") {
            submit($obj, $replacement, url, type, callback);
        }
    });
    $obj.replaceWith($replacement);
    document.addEventListener('click', callback);
};

submit = function (base, context, url, type, callback) {
    var $original = $('<' + base.prop('tagName') + '>');
    $original.attr('class', context.attr('class'));
    $original.attr('data-type', type);
    $original.attr('data-path', url);
    $original.html(context.val());
    $original.removeClass('editor');
    $original.on('click', function (target) {
        var obj = $(this);
        edit(this, obj.data('type'), obj.data('path'));
    });
    context.unbind('keypress');
    document.removeEventListener('click', callback);
    context.replaceWith($original);
    var data = {
        type: type,
        data: context.val()
    };
    $.post(url, data, function (res) {
        console.log(res);
    });
};

$('.edit-image').on('click', function () {
    var input = document.getElementById(this.dataset.target);
    input.click();
    var that = this;
    input.addEventListener('change', function (e) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(that).attr('src', e.target.result);
                $.post($(that).data('path'), {'type':'logo','data':e.target.result},function (res) {
                    console.log(res);
                })
            };
            reader.readAsDataURL(input.files[0]);
        }
    });
});