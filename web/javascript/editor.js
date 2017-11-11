
var edit = function (obj) {
    var $obj = (typeof obj !== 'string') ? $(obj) : $('#' + obj);
    var type = obj.dataset.type;
    var url = obj.dataset.path;
    $obj.attr('contenteditable', true);
    $obj.focus();
    $obj.addClass('editor');
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

$('.editable').on('click', function () {
    edit(this);
});



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
                    {'type': 'logo', 'data': e.target.result}, function (res) {
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
        $('#' + $obj.data('id')).html(monthNames[value - 1]);
    } else {
        $('#' + $obj.data('id')).html(value);

    }
});

$('.addition').on('click', 'button', function () {
    var obj = $(this);
    var type = obj.data('type');
    var portfolio = obj.data('portfolio');
    var url = obj.data('path');
    console.log(obj, type);
    var replacement = $('.addition_' + portfolio + '_' + type);
    replacement = replacement.clone();
    replacement.addClass('holder');
    console.log('.addition_submit_' + portfolio + '_' + type);
    $('.holder').replaceWith(replacement);
    var $input = replacement.find('.editor#' + type + '_' + portfolio);
    console.log($input);
    replacement.find('.addition_submit_' + portfolio + '_' + type).on('click', function () {
        var data = {
            data: $input.val(),
            name: '',
            type: type,
            portfolio: portfolio
        };
        console.log(url);
        $.post(url, data, function (res) {
            var resolvmentBag = res['resolver'];
            var resolver = new Resolver(type, resolvmentBag);
            replacement.replaceWith(resolver.resolve());
        });

    });

});

var Resolver = function (type, bag) {
    this.resolvement = null;
    this.resolve = function () {
        switch (type) {
            case 'paragraph':
                this.resolvement = $('<p>');
                this.resolvement.html(bag['html']);
                this.resolvement.addClass('editable');
                this.resolvement.attr('data-type', bag['type']);
                this.resolvement.attr('data-path', bag['edit']);
                this.resolvement.on('click', function () {
                    edit(this);
                });
                break;
            case 'image':
                this.resolvement = $('<img>');
                this.resolvement.attr('src' , '/img/Rolling.gif');
                this.resolvement.attr('data-src', bag['base-path']+bag['data-src']);
                this.resolvement.attr('alt',bag['name']);
            case 'video':

            case 'link':

            default:
                this.resolvement = $('<p>');
                this.resolvement.html(bag['html']);
                break;
        }
        return this.resolvement;
    };
};
