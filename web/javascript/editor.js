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
    var type = this.dataset.type;
    var that = this;
    input.click();
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(target).attr('src', e.target.result);
                var url = that.dataset.path;
                $.post(url,
                    {'type': type, 'data': e.target.result}, function (res) {
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
    var replacement = $(replacer(type, portfolio));
    replacement.addClass('holder');
    $('.holder_' + portfolio).replaceWith(replacement);
    var $input = replacement.find('.editor#' + type + '_' + portfolio);
    var upload = null;
    var name = null;
    if (type === 'image') {
        var image = $('.image-holder');
        console.log('image: ', image);
        image.on('click', function () {
            var input = image.data('input');
            console.log('input: ', input);
            $input = document.getElementById(input);
            $input.click();
            $input.addEventListener('change', function () {
                if ($input.files && $input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var target = image.find('.image-target');
                        upload = e.target.result;
                        target.replaceWith('<img class="image-target" src="' + upload + '">');
                    };
                    reader.readAsDataURL($input.files[0]);
                    name = $('.image-name').val();
                }
            });
        });
    } else if (type === 'link') {
        name = replacement.find('.addition_' + portfolio + '_input_name');
        $input = replacement.find('.addition_' + portfolio + '_input_body');
    } else if (type === 'video') {
        $input = replacement.find('addition_video_' + portfolio + '_input_body');
        console.log($input);
    }
    console.log($('.addition_submit_1'));
    $('.addition_submit_' + portfolio).on('click', function () {
        console.log('save');
        var inputval = null;
        try {
            inputval = $input.val()
        } catch (e) {
            inputval = upload
        }
        try {
            name = typeof name === 'object' ? name.val() : name;
        } catch (e) {

        }
        name = name !== null ? name : portfolio + type + 'noname';
        var data = {
            data: inputval,
            name: name,
            type: type,
            portfolio: portfolio
        };
        $.post(url, data, function (res) {
            var resolvmentBag = res['resolver'];
            var resolver = new Resolver(type, resolvmentBag);
            replacement.replaceWith(resolver.resolve());
            $('.addition_' + portfolio).before('<div class="holder_'+portfolio+'"></div>')
        });

    });

});

var Resolver = function (type, bag) {
    this.resolvement = null;
    this.resolve = function () {
        switch (type) {
            case 'paragraph':
                this.resolvement = $('<p>');
                this.resolvement.html(bag['body']);
                this.resolvement.addClass('editable');
                break;
            case 'image':
                this.resolvement = $('<img>');
                this.resolvement.attr('src', '/img/Rolling.gif');
                this.resolvement.attr('data-src', bag['base-path'] + bag['data-src']);
                this.resolvement.attr('alt', bag['name']);
                break;
            case 'video':
                this.resolvement = $('<div class="video-wrapper"><iframe width="560" height="315" src="' + bag['body'] + '" frameborder="0" allowfullscreen></iframe></div>');
                break;
            case 'link':
                this.resolvement = $('<a>');
                this.resolvement.attr('href', bag['body']);
                this.resolvement.html(bag['name']);
                break;
            default:
                this.resolvement = $('<p>');
                this.resolvement.html(bag['body']);
                break;
        }
        console.log(bag['type']);
        console.log(bag['edit']);
        this.resolvement.attr('data-type', bag['type']);
        this.resolvement.attr('data-path', bag['edit']);
        this.resolvement.on('click', function () {
            edit(this);
        });
        return this.resolvement;
    };
};

var replacer = function (type, portfolio) {
    switch (type) {
        case 'link':
            return '<div class="addition_'+portfolio+'_link">\n' +
                '    <label for="addition_'+portfolio+'_input_name">Naam</label>\n' +
                '    <input class="addition_'+portfolio+'_input_name" type="text">\n' +
                '    <label for="addition_'+portfolio+'_input_body" >Target</label>\n' +
                '    <input type="text" class="addition_'+portfolio+'_input_body">\n' +
                '    <button class="addition_submit_'+portfolio+'">Save</button>\n' +
                '</div>\n';
            break;
        case 'paragraph':
            return '<div class="addition_'+portfolio+'_paragraph">\n' +
                '    <textarea class="editor" name="paragraph" id="paragraph_'+portfolio+'" cols="30" rows="10"></textarea>\n' +
                '    <button class="addition_submit_'+portfolio+'">Save</button>\n' +
                '    <p class="addition_resolve_'+portfolio+'_paragraph"></p>\n' +
                '</div>\n';
            break;
        case 'image':
            return '<div class="addition_'+portfolio+'_image">\n' +
                '    <div class="image-holder" data-input="modal_file_'+portfolio+'">\n' +
                '        <div class="image-holder-target image-target">\n' +
                '            Upload image\n' +
                '        </div></div>\n' +
                '    <input id="modal_file_'+portfolio+'" type="file" style="display: none;">\n' +
                '    <input type="text" class="image-name" placeholder="Afbeeldings naam">\n' +
                '    <button class="addition_submit_'+portfolio+'">Save</button>\n';
            break;
        case video:
            return '</div>\n' +
                '<div class="addition_'+portfolio+'_video">\n' +
                '    <input type="text" class="addition_video_'+portfolio+'_input_body">\n' +
                '    <button class="addition_submit_'+portfolio+'">Save</button>\n' +
                '</div>';
            break;
        default:
            return '';
    }
};
