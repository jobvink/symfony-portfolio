$('.editable').on('click', function (target) {
    edit(this, this.dataset.type, this.dataset.path);
});

var edit = function (obj, type, url) {
    var $obj = (typeof obj !== 'string') ? $(obj) : $('#' + obj);
    $obj.attr('contenteditable', true);
    $obj.focus();
    $obj.addClass('editor');
    url = document.getElementById(url).dataset.path;
    submit = function (url, type, callback) {
        var value = $obj.html();
        $obj.unbind('keypress');
        document.removeEventListener('click', callback);
        $obj.attr('contenteditable', false);
        var data = {
            type: type,
            data: value
        };
        $.post(url, data, function (res) {
            console.log(res);
        });
    };
    var callback = function (e) {
        var target = $(e.target);
        if (!target.hasClass('editor') && !target.hasClass('editable')) {
            submit(url, type, callback);
        }
    };
    $obj.keypress(function (key) {
        if (key.key === "Enter") {
            submit(url, type, callback);
        }
    });
    document.addEventListener('click', callback);
};



$('.edit-image').on('click', function () {
    var input = document.getElementById(this.dataset.input);
    var target = document.getElementById(this.dataset.img);
    var that = this;
    input.click();
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(target).attr('src', e.target.result);
                $.post(document.getElementById(that.dataset.path).dataset.path,
                    {'type':'logo','data':e.target.result},function (res) {
                    console.log(res);
                })
            };
            reader.readAsDataURL(input.files[0]);
        }
    });
});

$('.new-image').on('click', function () {
    var input = document.getElementById(this.dataset.input);
    var target = document.getElementById(this.dataset.img);
    var that = this;
    input.click();
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            target = $(target);
            target.replaceWith('<img id="changement">');
            target = document.getElementById('changement');
            target.removeAttribute('id');
            var reader = new FileReader();
            reader.onload = function (e) {
                $(target).attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    });
});

$('.edit-dropdown').on('click', function () {
    var $obj = $(this);
    var type = $obj.data('type');
    var value = $obj.data('value');
    var time = $obj.data('time');
    var url = document.getElementById($obj.data('path')).dataset.path;
    console.log(url);
    var data = {
        type: type,
        data: value,
        extra: time
    };
    $.post(url, data, function (res) {
        console.log(res);
    });
    if (type === 'month') {
        var monthNames = ["Jannuari", "Februari", "Maart", "April", "Mei", "Juni",
            "Juli", "Augustus", "September", "Oktober", "November", "December"
        ];
        $('#' + $obj.data('id')).html(monthNames[value-1]);
    } else {
        $('#' + $obj.data('id')).html(value);

    }
});

