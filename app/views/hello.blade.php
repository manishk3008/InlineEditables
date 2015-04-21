<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test</title>
    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:700);

        body {
            margin: 0;
            font-family: 'Lato', sans-serif;
            text-align: center;
            color: #999;
        }

        input.inline-edit {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: inherit;
            bottom: 0;
        }

        .dv-editable {
            position: relative;
        }
    </style>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
</head>
<body>
<div>

    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
        @for($i=0;$i<10;$i++)
            <tr class="inline-editable-row">
                <td class="dv-editable" data-dv-name="name">
                    {{str_random(5)}}
                </td>
                <td class="dv-editable" data-dv-name="email">
                    {{str_random(5)}}@ {{ str_random(3)}}.com
                </td>
                <td>
                    <button class="dv-edit-trigger">Edit</button>
                </td>
            </tr>

        @endfor
    </table>

</div>
<script>
    function cl(msg) {
        console.log(msg);
    }
    $(function () {
        $(".inline-editable-row").defaultPluginName();

//        $(".inline-editable-row").each(function (i,e) {
//            e.data('plugin_defaultPluginName');
//        });
    });

</script>
<script>
    ;
    (function ($, window, document, undefined) {

        "use strict";

        // undefined is used here as the undefined global variable in ECMAScript 3 is
        // mutable (ie. it can be changed by someone else). undefined isn't really being
        // passed in so we can ensure the value of it is truly undefined. In ES5, undefined
        // can no longer be modified.

        // window and document are passed through as local variable rather than global
        // as this (slightly) quickens the resolution process and can be more efficiently
        // minified (especially when both are regularly referenced in your plugin).

        // Create the defaults once
        var pluginName = "defaultPluginName",
                defaults = {
                    trigger: ".dv-edit-trigger",
                    activateOnClick: true,
                    editable: ".dv-editable",
                    callbacks: {
                        onFieldChange: function (instance, field) {
//                            cl('onFieldChange');
//                            cl(field);
                        },
                        onActivateEditable: function (instance) {
//                            cl('onActivateEditable');
//                            cl(instance);
                        },
                        onDeactivateEditable: function (instance) {
                            cl('onDeactivateEditable');
//                            cl(instance);
                        }
                    },
                    classes: {
                        activeEditable: 'active-editable',
                        inActiveEditable: 'inactive-editable',
                        inline_editable: 'inline-edit'
                    }

                };

        // The actual plugin constructor
        function Plugin(element, options) {
            this.element = element;
            this.elems = {};
            // jQuery has an extend method which merges the contents of two or
            // more objects, storing the result in the first object. The first object
            // is generally empty as we don't want to alter the default options for
            // future instances of the plugin
            this.settings = $.extend({}, defaults, options);
            this._defaults = defaults;
            this._name = pluginName;
            this.init();
        }


        // Avoid Plugin.prototype conflicts
        $.extend(Plugin.prototype, {
            init: function () {
                this.elems.trigger = $(this.element).find(this.settings.trigger);
                this.elems.editables = $(this.element).find(this.settings.editable);

                var _editables = this.elems.editables;
                var _this = this;

                this.attachInputs();
                this.bindChangeEvent();
                this.bindKeyPressEvent();

                this.elems.trigger.click(function (e) {
                    _this.activateEditable();
                });

                if (this.settings.activateOnClick) {
                    this.elems.editables.click(function () {
                        if (!$(this).hasClass(_this.settings.classes.activeEditable)) {
                            _this.activateEditable($(this));
                        }
                    });
                }
                ;

            },
            attachInputs: function (elem) {
                var _this = this;
                var _inputs = Array();
                this.elems.editables.each(function (i, e) {
                    var fName = $(e).data('dv-name'),
                            fValue = $.trim($(e).text()),
                            input = $("<input type='text' class='" + _this.settings.classes.inline_editable + "' name='" + fName + "' value='" + fValue + "'/>");
                    input.hide();
                    $(e).append(input).addClass(_this.settings.classes.inActiveEditable);
                    _inputs.push(input);
                });
                this.elems.inputs = _inputs;
            },
            detachInputs: function () {
                $.each(this.elems.inputs, function (i, e) {
                    $(e).remove();
                });
            },
            activateEditable: function (inFocus) {
                this.showInputs(inFocus);
                this.addActiveEditableClass();
                this.settings.callbacks.onActivateEditable(this);
            },
            deActivateEditable: function () {
                this.hideInputs();
                this.removeActiveEditableClass();
                this.unBindBlurEvent();
                this.settings.callbacks.onDeactivateEditable(this);
            },
            showInputs: function (inFocus) {
                var _this = this;
                $.each(this.elems.inputs, function (i, e) {
                    e.show();
                    if (i == 0) {
                        e.focus();
                    }
                });

                if (inFocus) {
                    this.getInputElem(inFocus).focus();
                }

                this.bindBlurEvent();
            },
            hideInputs: function () {
                $.each(this.elems.inputs, function (i, e) {
                    $(e).hide();
                });
            },
            addActiveEditableClass: function () {
                var _this = this;
                $.each(this.elems.editables, function (i, e) {
                    $(e).addClass(_this.settings.classes.activeEditable)
                            .removeClass(_this.settings.classes.inActiveEditable);
                });
            },
            removeActiveEditableClass: function () {
                var _this = this;
                $.each(this.elems.editables, function (i, e) {
                    $(e).addClass(_this.settings.classes.inActiveEditable)
                            .removeClass(_this.settings.classes.activeEditable);
                });
            },
            bindBlurEvent: function () {
                var _this = this;
                $.each(this.elems.inputs, function (i, e) {
                    e.on('blur',function (e) {
                        if ($(e.relatedTarget).hasClass(_this.settings.classes.inline_editable)) {
                            e.preventDefault();
                            return;
                        }
                        _this.unBindBlurEvent();
                        _this.deActivateEditable();
                    });
                });

                // $('.inline-edit').on('blur',function(){
                //     _this.deActivateEditable();
                //     $('.inline-edit').off('blur');
                // });
            },
            unBindBlurEvent: function () {
                $('.'+this.settings.classes.inline_editable).off('blur');
                // var _this = this;
                // $.each(this.elems.inputs, function (i, e) {
                //     e.off('blur');
                //     cl('dsd');
                // });
            },
            bindChangeEvent: function () {
                var _this = this;
                $.each(this.elems.inputs, function (i, e) {
                    e.change(function (e) {
                        _this.mapValues();
                        _this.settings.callbacks.onFieldChange(_this, e.target);
                    });
                });
            },
            bindKeyPressEvent: function () {

                $.each(this.elems.inputs, function (i, e) {
                    e.keypress(function (event) {
                        var keycode = (event.keyCode ? event.keyCode : event.which);
                        if (keycode == '13') {
                            $(this).blur();
                        }
                    });
                });
            }
            ,
            mapAndRedraw: function () {
                this.mapValues();
                this.hideInputs();
            },
            mapValues: function () {
                var _this = this;
                $.each(this.elems.editables, function (i, e) {
                    var relInput = _this.elems.inputs[i],
                            relValue = relInput.val();
                    relInput.attr('value', relValue);
                    $(e).text(relValue).append(relInput);

                });
                this.bindBlurEvent();
                this.bindChangeEvent();
                this.bindKeyPressEvent();
            },

            getValues: function () {
                var values = {};
                $.each(this.elems.inputs, function (i, e) {
                    values[e.attr('name')] = e.val();
                });

                return values;
            },
            getInputElem: function (editableElem) {
                return $(editableElem).find('.' + this.settings.classes.inline_editable)
            }

        });

        // A really lightweight plugin wrapper around the constructor,
        // preventing against multiple instantiations
        $.fn[pluginName] = function (options) {
            return this.each(function () {
                if (!$.data(this, "plugin_" + pluginName)) {
                    $.data(this, "plugin_" + pluginName, new Plugin(this, options));
                }
            });
        };

    })(jQuery, window, document);

</script>
</body>
</html>
