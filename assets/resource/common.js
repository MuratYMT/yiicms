/**
 * Created by admin on 07.09.2015.
 */

(function ($) {
    $.fn.outerHTML = function (s) {
        var p = document.createElement('p'),
            c = this.eq(0).clone();
        p.appendChild(c[0]);
        return (s) ? this.before(s).remove() : p.innerHTML;
    };

    $.fn.addHideField = function (name, value) {
        var $this = $(this);
        name = rtrim(name, '[]');
        var hidefield = $this.find('#' + name);
        if (hidefield.length > 0) {
            hidefield.remove();
        }
        $this.append($('<input/>', {id: name, name: name, type: "hidden", value: value}));
    };
})(jQuery);

yii.yiicms = (function ($) {
    var pub = {
        init: function () {
            initAjax();
        },

        /**
         * выполняет перезагрузку контента при смене языка в выпадающем списке
         * @param obj
         * @returns {boolean}
         */
        changeLang: function (obj) {
            var value = obj.value,
                form = $(obj).closest('form'),
                action = $(form).attr("action"),
                link = action + (action.indexOf('?') == -1 ? "?" : "&") + 'lang=' + value;
            yii.yiicms.getAjax(link);
            return false;
        },

        /**
         * выполняет GET запрос к серверу обрабатывая параметры строки запроса и формы
         * @param obj объект вызвавший сабмит
         * @returns {boolean}
         */
        submitForm: function (obj) {
            var form = $(obj).closest('form'),
                formAction = $(form).attr('action'),
                actionParams = yii.getQueryParams(formAction),
                formParams = form.formToArray(),
                params = [],
                clearParamName = [];

            $(form).find('select[multiple]').each(function (i) {
                clearParamName.push($(this).attr('name'));
            });

            $.each(actionParams, function (name, value) {
                if ($.inArray(name, clearParamName) == -1) {
                    params.push({'name': name, 'value': value});
                }
            });

            $.each(formParams, function (name, value) {
                params.push({'name': value['name'], 'value': value['value']});
            });

            getMethod(form, params);

            return false;
        },

        /**
         * выполняет ajax get запрос на сервер
         * @param url адрес запроса
         */
        getAjax: function (url) {
            beforeAjax(url);
            $.ajax({
                dataType: "text",
                url: url,
                headers: {'HTTP_X_REQUESTED_WITH': 'XMLHttpRequest'},
                success: processAjaxResponse,
                error: processAjaxError
            });
            return false;
        },

        /**
         * перед выполнением ajax get запроса на сервер выдает окно с требованием подтверждения
         * @param url адрес запроса
         * @param confirmMessage сообщение выдаваемое перед выполнением запроса
         */
        getAjaxConfirm: function (url, confirmMessage) {
            bootbox.confirm(confirmMessage, function (result) {
                if (result) {
                    pub.getAjax(url);
                }
            });
            return false;
        },

        /**
         * выполняет отправку формы ajax'ом
         * @param form отправляемая форма
         */
        sendFormAjax: function (form) {
            form = $(form).closest('form');
            var url = $(form).attr("action");
            beforeAjax(url);
            form.ajaxSubmit({
                data: {_csrf: yii.getCsrfToken()},
                dataType: 'text',
                success: processAjaxResponse,
                error: processAjaxError,
                headers: {'HTTP_X_REQUESTED_WITH': 'XMLHttpRequest'}
            });
        },
        /**
         * перед отправкой формы выдает запрос на подтверждение
         * @param form
         * @param message
         * @returns {boolean}
         */
        sendFormAjaxConfirm: function (form, message) {
            bootbox.confirm(message, function (result) {
                if (result) {
                    pub.sendFormAjax(form);
                }
            });
            return false;
        },

        /**
         * закрытие всплывающего окна
         * @param id идентификатор окна
         */
        closePopup: function (id) {
            var modal = $("#" + id);
            modal.modal("hide");
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
        addHideFieldA: function (hideFields) {
            $.each(hideFields, function (container, fields) {
                var form = $("#" + container);
                if (form.length == 0) {
                    return;
                }
                if (!form.is("form")) {
                    form = form.find("form");
                    if (form.length == 0) {
                        return;
                    }
                }

                $.each(fields, function (name, value) {
                    form.addHideField(name, value);
                });
            });
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
                req = vl.pop(); //нужен только последний элемент
            req = req.trim();

            if (req.length >= 2) {
                $.ajax(
                    {
                        url: url + req,
                        success: function (data) {
                            data = $.parseJSON(data);
                            if (!data.error) {
                                value = vl.join(',');
                                var ret = [],
                                    i = 0;
                                for (i in data.result) {
                                    if (value != '') {
                                        ret[i] = value + ', ' + data.result[i];
                                    }
                                    else {
                                        ret[i] = data.result[i];
                                    }
                                }
                            }

                            response(ret);
                        }
                    });
            }
        }
    };

    function getMethod(form, params) {
        var queryString = buildQueryString(params),
            formAction = $(form).attr("action"),
            url = formAction.split('?')[0] + (queryString.length > 0 ? ('?' + queryString) : ''),
            inAjaxBlock = form.closest("div[class='ajax-links']").length > 0,
            ajaxForm = $(form).attr("data-ajaxform") == 1,
            noAjax = form.attr("data-noajax");

        if ((inAjaxBlock || ajaxForm) && (!noAjax)) {
            yii.yiicms.getAjax(url);
        } else {
            window.location.href = url;
        }
    }

    /**
     * формирует строку запроса из переданного массива параметров
     * @param params key-value массив параметров
     * @returns {string}
     */
    function buildQueryString(params) {
        var queryString = [],
            uniqueParams = [];

        $.each(params, function (name, value) {
            name = value['name'];
            value = value['value'];
            if (name && name.length > 0) {
                if (name.charAt(0) != '_') {

                    if ($.isArray(value)) {
                        $.each(value, function (name2, value2) {
                            if (value2 && value2.length > 0) {
                                if (name.indexOf('[]') != -1) {
                                    uniqueParams[name + '=' + value2] = {'name': name, 'value': value2};
                                } else {
                                    uniqueParams[name] = {'name': name, 'value': value2};
                                }
                            }
                        })
                    } else {
                        if (name.indexOf('[]') != -1) {
                            uniqueParams[name + '=' + value] = {'name': name, 'value': value};
                        } else {
                            uniqueParams[name] = {'name': name, 'value': value};
                        }
                    }
                }
            }
        });

        for (p in uniqueParams) {
            queryString.push(uniqueParams[p]['name'] + '=' + uniqueParams[p]['value']);
        }

        return queryString.join('&');
    }

    function initAjax() {
        //обработка отправки форм яксом
        $(document).off("beforeSubmit", "*[data-ajaxform]")
            .on("beforeSubmit", "*[data-ajaxform]", function () {
                var form = $(this);
                return ajaxForm(form);
            });
        //обработка фильтра yiiGrid
        $(document).on("beforeFilter", function () {
            var target = $(arguments[0].target),
                form = target.find("form").first(),
                inAjaxBlock = form.closest("div[class='ajax-links']"),
                noAjax = form.attr("data-noajax"),
                needAjax = (inAjaxBlock.length > 0) && (!noAjax);
            if (needAjax) {
                return ajaxForm(form);
            } else {
                return true;
            }
        });

        //ссылки которые преобразовываются в post запросы
        $(document).off("click", "a[data-fmethod]").on("click", "a[data-fmethod]", link2Form);
    }

    function ajaxForm(obj) {
        obj = obj.closest("form");
        var message = obj.data("message");
        if (message) {
            pub.sendFormAjaxConfirm(obj, message);
        } else {
            pub.sendFormAjax(obj);
        }
        return false;
    }

    /**
     * Функция выполяет преобразование ссылки в post запрос к сайту
     * У сслыки могут использоваться следующие аттрибуты
     * data-message - текст для окна поддтвержения дейтсивя
     * data-ajax="1" - использовать ajax для post запроса
     * Так же если ссылка находится внутри блока <div class="ajax-links"></div>, то также используется ajax
     * @returns {boolean}
     */
    function link2Form() {
        var $this = $(this),
            url = $this.attr("href"),
            message = $this.data("message"),
            method = $this.data("fmethod"),
            inAjaxBlock = $this.closest("div[class='ajax-links']").length > 0,
            noAjax = $this.data("noajax"),
            needAjax = $this.data("ajax"),
            useAjax = (inAjaxBlock || needAjax) && (!noAjax),
            form = $('<form/>', {action: url, method: method});

        if (method == 'get') {
            var actionParams = yii.getQueryParams(url);
            $.each(actionParams, function (name, value) {
                form.addHideField(name, value);
            });
        } else {
            form.addHideField(yii.getCsrfParam(), yii.getCsrfToken());
        }

        if (message) {
            //формируем модальное окно с запросом
            var div_body = $('<div/>', {class: "modal-body"})
                    .append(message),
                div_foter = $('<div/>', {class: "modal-footer"})
                    .append($('<button/>', {"data-dismiss": "modal", type: "button", class: "btn btn-default"}).append('Cancel')),
                ok_button = $('<button/>', {type: "submit", class: "btn btn-primary confirm-btn"});

            if (useAjax) {
                ok_button.attr("onclick", "yii.yiicms.sendFormAjax(this)");
            }
            ok_button.append("OK").appendTo(div_foter);

            form.append($('<div/>', {class: "modal-content"}).append(div_body).append(div_foter));

            openPopup("confirm-modal", $('<div/>', {class: 'modal-dialog'}).append(form).outerHTML());
        } else {
            if (useAjax) {
                ajaxForm(form);
            } else {
                form.hide().appendTo("body").submit();
            }
        }
        return false;
    }

    function beforeAjax(url) {
        /*if (history.pushState) {
         history.pushState(null, null, url);
         }*/
        showAjaxProcess();
    }

    function afterAjax() {
        hideAjaxProcess();
    }

    function showAjaxProcess() {
        if (window.showedAjaxStack) {
            window.showedAjaxStack++;
        } else {
            window.showedAjaxStack = 1;
            $('body').append('<div id="ajax-loader"><i class="fa fa-spinner fa-pulse fa-5x"> </i><div class="modal-backdrop fade in"></div></div>');
        }
    }

    function hideAjaxProcess() {
        if (window.showedAjaxStack) {
            window.showedAjaxStack--;
        } else {
            window.showedAjaxStack = 0;
        }

        if (window.showedAjaxStack <= 0) {
            window.showedAjaxStack = 0;
            $('#ajax-loader').remove();
        }
    }

    function processAjaxError(xhr, status, form) {
        afterAjax();
        showAjaxMessage(form);
    }

    /**
     * обработка ajax ответа сервера
     * @param ajaxResponse
     */
    function processAjaxResponse(ajaxResponse) {
        afterAjax();
        ajaxResponse = $.parseJSON(ajaxResponse);

        if (ajaxResponse["cssFiles"]) {
            $.each(ajaxResponse["cssFiles"], function (i, val) {
                if ($('link[href="' + val + '"]').length == 0) {
                    $('head').append('<link rel="stylesheet" href="' + val + '"/>');
                }
            });
        }

        if (ajaxResponse["jsFiles"]) {
            $.each(ajaxResponse["jsFiles"], function (i, val) {
                if ($('script[src="' + val + '"]').length == 0) {
                    $('head').append('<script src="' + val + '"></script>');
                }
            });
        }

        //закрытие всплывающих окон
        if (ajaxResponse["closePopup"]) {
            closePopup(ajaxResponse["closePopup"]);
        }

        if (ajaxResponse["hideFields"]) {
            addHideFieldA(ajaxResponse["hideFields"]);
        }

        //всплывающие окна
        if (ajaxResponse["openPopup"]) {
            $.each(ajaxResponse["openPopup"], function (i, object) {
                openPopup(object.id, object.content);
            });
        }

        //контент на замену
        if (ajaxResponse["objects"]) {
            $.each(ajaxResponse["objects"], function (i, object) {
                replaceContent(object.id, object.content);
            });
        }

        if (ajaxResponse["js"]) {
            eval(ajaxResponse["js"]);
        }

        //переход по ссылке после закрытия окна
        if (ajaxResponse["message"] && ajaxResponse["message"]["text"]) {
            showAjaxMessage(ajaxResponse["message"]["text"], ajaxResponse["message"]["goUrl"]);
        }
        //переход
        if (ajaxResponse["message"] && ajaxResponse["message"]["goUrl"] && !ajaxResponse["message"]["text"]) {
            ajaxGo(ajaxResponse["message"]["goUrl"]);
        }

        if (ajaxResponse["ajaxRedirect"]) {
            $.each(ajaxResponse["ajaxRedirect"], function (i, url) {
                pub.getAjax(url);
            });
        }
    }


    function openPopup(id, content) {
        var modalId = id + "Modal",
            selector = $("#" + modalId);

        if (selector.length) {
            replaceContent(id, content);
        } else {
            $('body').append('<div id="' + modalId + '" class="modal fade">' + content + '</div>');
            selector = $("#" + modalId);
            selector.modal();
            selector.on("hidden.bs.modal", function () {
                $("#" + modalId).remove();
            })
        }
    }

    /**
     * закрытие всплывающего окна
     * @param id идентификатор окна
     */
    function closePopup(id) {
        $("#" + id).modal("hide");
    }

    function replaceContent(id, content) {
        var obj = $('#' + id);
        if (obj.length > 0) {
            obj.outerHTML(content);
        }
    }

    /**
     * показывает окно с сообщением
     * @param message
     * @param url
     */
    function showAjaxMessage(message, url) {
        bootbox.alert(message, function () {
            ajaxGo(url);
        });
    }

    /**
     * выполняет переход страницы по указанному урлу
     * @param url
     */
    function ajaxGo(url) {
        if (url) {
            /*костыль если в текущем урле есть якорь то при попытке перейти
             на эту же страницу с этим же урлом и якорем браузер проигнорирует
             поэтому надо заставить сделать перезагрузку страницы с сервера*/
            var newUrlParts = url.split("#");
            var currentUrlParts = window.location.href.split("#");
            window.location.assign(url);
            if (newUrlParts[0] == currentUrlParts[0]) {
                window.location.reload(true);
            }
        }
    }

    /**
     *  Base64 encode / decode
     *  http://www.webtoolkit.info/
     **/
    var Base64 = {
        // private property
        _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // public method for encoding
        encode: function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;

            input = Base64._utf8_encode(input);

            while (i < input.length) {

                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

            }

            return output;
        },

        // public method for decoding
        decode: function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            input = input.replace(/[^A-Za-z0-9\+\/=]/g, "");

            while (i < input.length) {

                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }

            }

            output = Base64._utf8_decode(output);

            return output;

        },

        // private method for UTF-8 encoding
        _utf8_encode: function (string) {
            string = string.replace(/\r\n/g, "\n");
            var utftext = "";

            for (var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if ((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        },

        // private method for UTF-8 decoding
        _utf8_decode: function (utftext) {
            var string = "";
            var i = 0;
            var c = 0;
            var c3 = 0;
            var c2 = 0;

            while (i < utftext.length) {

                c = utftext.charCodeAt(i);

                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                }
                else if ((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i + 1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                }
                else {
                    c2 = utftext.charCodeAt(i + 1);
                    c3 = utftext.charCodeAt(i + 2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }

            }

            return string;
        }

    };

    return pub;
})
(jQuery);


function var_dump() {
    var output = '', pad_char = ' ', pad_val = 4, lgth = 0, i = 0, d = this.window.document;
    var getFuncName = function (fn) {
        var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
        if (!name) {
            return '(Anonymous)';
        }
        return name[1];
    };
    var repeat_char = function (len, pad_char) {
        var str = '';
        for (var i = 0; i < len; i++) {
            str += pad_char;
        }
        return str;
    };
    var getScalarVal = function (val) {
        var ret = '';
        if (val === null) {
            ret = 'NULL';
        } else if (typeof val === 'boolean') {
            ret = 'bool(' + val + ')';
        } else if (typeof val === 'string') {
            ret = 'string(' + val.length + ') "' + val + '"';
        } else if (typeof val === 'number') {
            if (parseFloat(val) == parseInt(val, 10)) {
                ret = 'int(' + val + ')';
            } else {
                ret = 'float(' + val + ')';
            }
        } else if (val === undefined) {
            ret = 'UNDEFINED'; // Not PHP behavior, but neither is undefined as value
        } else if (typeof val === 'function') {
            ret = 'FUNCTION'; // Not PHP behavior, but neither is function as value
            ret = val.toString().split("\n");
            txt = '';
            for (var j in ret) {
                txt += (j != 0 ? thick_pad : '') + ret[j] + "\n";
            }
            ret = txt;
        } else if (val instanceof Date) {
            val = val.toString();
            ret = 'string(' + val.length + ') "' + val + '"'
        }
        else if (val.nodeName) {
            ret = 'HTMLElement("' + val.nodeName.toLowerCase() + '")';
        }
        return ret;
    };
    var formatArray = function (obj, cur_depth, pad_val, pad_char) {
        var someProp = '';
        if (cur_depth > 0) {
            cur_depth++;
        }
        base_pad = repeat_char(pad_val * (cur_depth - 1), pad_char);
        thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char);
        var str = '';
        var val = '';
        if (typeof obj === 'object' && obj !== null) {
            if (obj.constructor && getFuncName(obj.constructor) === 'PHPJS_Resource') {
                return obj.var_dump();
            }
            lgth = 0;
            for (someProp in obj) {
                lgth++;
            }
            str += "array(" + lgth + ") {\n";
            for (var key in obj) {
                if (typeof obj[key] === 'object' && obj[key] !== null && !(obj[key] instanceof Date) && !obj[key].nodeName) {
                    str += thick_pad + "[" + key + "] =>\n" + thick_pad + formatArray(obj[key], cur_depth + 1, pad_val, pad_char);
                } else {
                    val = getScalarVal(obj[key]);
                    str += thick_pad + "[" + key + "] =>\n" + thick_pad + val + "\n";
                }
            }
            str += base_pad + "}\n";
        } else {
            str = getScalarVal(obj);
        }
        return str;
    };
    output = formatArray(arguments[0], 0, pad_val, pad_char);
    for (i = 1; i < arguments.length; i++) {
        output += '\n' + formatArray(arguments[i], 0, pad_val, pad_char);
    }
    return output;
}

function rtrim(str, charlist) {	// Strip whitespace (or other characters) from the end of a string
    //
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +	  input by: Erkekjetter
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

    charlist = !charlist ? ' \s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
    var re = new RegExp('[' + charlist + ']+$', 'g');
    return str.replace(re, '');
}


$(document).ready(function ($) {

});
