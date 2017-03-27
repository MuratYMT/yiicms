/**
 * Created by Admin on 13.03.2017.
 */

window.yii.yiicms = (function ($) {
    var pub = {
        init: function () {
            initAjax();
        },
        /**
         * вставляет картинку из файлового менеджера в tinymce
         * @param imageAllow
         */
        insertImage: function (imageAllow) {
            var text = "",
                f = new RegExp("\.(" + imageAllow + ")$");

            $.each($('.for-select'), function () {
                if ($(this).prop("checked")) {
                    var title = $(this).attr("data-title"),
                        src = $(this).attr("data-src");
                    if (f.test(src)) {
                        text += '<p><img alt="' + title + '" style="max-width: 98%" src="' + src + '"/></p> ';
                    } else {
                        text += '<a href="' + src + '">' + title + '</a>';
                    }
                }
            });

            top.tinymce.activeEditor.execCommand('mceInsertContent', false, text);
            top.tinymce.activeEditor.windowManager.close();
        },

        /**
         * Добавляет скрытое поля к форме
         * @param hideFields массив значений
         */
        addHideField: function (hideFields) {
            $.each(hideFields, function (container, fields) {
                var form = $("#" + container);
                if (form.length === 0) {
                    return;
                }
                if (!form.is("form")) {
                    form = form.find("form");
                    if (form.length === 0) {
                        return;
                    }
                }

                $.each(fields, function (name, value) {
                    var id = window.yii.yiicms.rtrim(name, '[]');
                    var hidefield = form.find('input[id="' + id + '"][type="hidden"][value="' + value + '"]');
                    if (hidefield.length > 0) {
                        hidefield.remove();
                    }
                    form.append($('<input/>', {id: id, name: name, type: "hidden", value: value}));
                });
            });
        },
        rtrim: function (str, charlist) {	// Strip whitespace (or other characters) from the end of a string
            //
            // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +	  input by: Erkekjetter
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

            charlist = !charlist ? ' \s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
            var re = new RegExp('[' + charlist + ']+$', 'g');
            return str.replace(re, '');
        },
        attachImage: function (panelId, image, delMessage) {
            window.yii.yiicms.addHideField(image["id"]);
            $("#" + panelId)
                .append($("<div/>", {class: "icon-list image"})
                    .append($("<img/>", {src: image["thumb"], id: image["imageid"]}))
                    .append($("<p/>").append(image["title"]))
                    .append($("<div/>", {class: "manage-box btn-group"})
                        .append($("<a/>", {
                            class: "btn btn-default pull-right",
                            "data-id": image["imageid"],
                            "data-message": delMessage,
                            href: "#", "data-unlink": 1
                        })
                            .append("<span class=\"fa fa-trash-o\"> </span>"))))
            ;
        },

        removeAttachedImage: function () {
            var $this = $(this),
                id = $this.data('id'),
                confirmMessage = $this.data('message');
            bootbox.confirm(confirmMessage, function (result) {
                if (result) {
                    $('img[id="' + id + '"]').parent().remove();
                    $('input[value="' + id + '"]').remove();
                }
            });
            return false;
        },

        /**
         * загружает теги для автокомплита в котором множество значений разбиваются по запятым
         *
         * @param url урл для ajax запроса
         * @param value строка тегов
         * @param response
         */
        loadTag: function (url, value, response) {
            //разбиваем по запятым
            var vl = value.split(','),
                req = vl.pop().trim(); //нужен только последний элемент

            if (req.length >= 2) {
                $.ajax({
                    url: url + req,
                    success: function (data) {
                        data = $.parseJSON(data);
                        if (!data.error) {
                            value = vl.join(',');
                            var ret = [];
                            for (var i in data.result) {
                                ret[i] = (value === '') ? ret[i] = data.result[i] : ret[i] = value + ', ' + data.result[i];
                            }
                        }

                        response(ret);
                    }
                });
            }
        }
    };

    function initAjax() {
        //ссылки которые преобразовываются в post запросы
        $(document).off("click", "a[data-modal]").on("click", "a[data-modal]", openModal);
        //удаление прикрепленных к странице файлов
        $(document).off("click", ".linked-image a[data-unlink]").on("click", ".linked-image a[data-unlink]", window.yii.yiicms.removeAttachedImage);
    }

    function openModal() {
        var $this = $(this),
            id = $this.data("modal"),
            modalId = id + "-modal",
            selector = $("#" + modalId),
            url = $this.attr("href");

        if (selector.length === 0) {
            var modal = $("<div/>", {id: modalId, class: "modal fade"})
                .append($("<div/>", {class: "modal-dialog"})
                    .append($("<div/>", {class: "modal-content"})
                        .append($("<div/>", {class: "modal-body"})
                            .append($('<div/>', {id: id})))))
                .hide();

            $("body").append(modal);
            $.pjax({url: url, container: "#" + id});
            modal.modal();
            selector.on("hidden.bs.modal", function () {
                modal.remove();
            })
        }
        return false;
    }

    return pub;
})
(jQuery);
