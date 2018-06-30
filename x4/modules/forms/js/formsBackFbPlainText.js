/*
 * JQuery Form Builder - Plain Text plugin.
 *
 * Revision: 121
 * Version: 0.1
 * Copyright 2011 Lim Chee Kin (limcheekin@vobject.com)
 *
 * Licensed under Apache v2.0 http://www.apache.org/licenses/LICENSE-2.0.html
 *
 * Date: 16-Jan-2011
 */

// extends/inherits from superclass: FbWidget
var FbPlainText = jQuery.extend({}, jQuery.fb.fbWidget.prototype,
{
    fbOptions: { // default options. values are stored in widget's prototype
        Name: AI.translate('forms', 'PlainText'),
        belongsTo: jQuery.fb.formbuilder.prototype.fbOptions._altFieldsPanel,
        _type: 'PlainText',
        _html: '<div class="PlainText col-lg-11">'+AI.translate('forms', 'PlainText')+'</div>',
        _btnHtml: '<div class="form-group"> \
            <label class="control-label">'+AI.translate('forms', 'PlainText')+'</label> \
            <span class="help-block m-b-none">'+AI.translate('forms', 'PlainText_text_help')+'</span></div> \
            <div class="line line-dashed b-b line-lg pull-in"></div>',
        _counterField: 'text',
        settings: {
            text: AI.translate('forms', 'PlainText'),
            classes: [ 'leftAlign', 'middleAlign' ]
        }
    },
    _init: function() {
        jQuery.fb.fbWidget.prototype._init.call(this);
        this.fbOptions = jQuery.extend({}, jQuery.fb.fbWidget.prototype.fbOptions, this.fbOptions);
    },
    _getWidget: function(event, fb) {
        fb.item.addClass(fb.settings.classes[1]); // vertical alignment
        return jQuery(fb.target.fbOptions._html).text(fb.settings.text)
                .addClass(fb.settings.classes[0]);
    },
    _getFieldSettingsLanguageSection: function(event, fb) {
        var $text = fb.target._label({ label: AI.translate('forms', 'text'), name: 'field.text',
                             description: AI.translate('forms', 'PlainText_field_text_help') })
                   .append('<textarea class="form-control textInput" id="field.text"></textarea>');
                jQuery('textarea', $text).val(fb.settings.text)
                .keyup(function(event) {
                    var value = jQuery(this).val();
                    fb.item.find('div.PlainText').text(value);
                    fb.settings.text = value;
                    fb.target._updateSettings(fb.item);
                });
        var $verticalAlignment = fb.target._verticalAlignment({name: 'field.verticalAlignment', value: fb.settings.classes[1]})
        .change(function(event) {
            // jQuery(this).val() not work for select id that has '.'
                    var value = jQuery('option:selected', this).val();
                    fb.item.removeClass(fb.settings.classes[1]).addClass(value);
                    fb.settings.classes[1] = value;
                    fb.target._updateSettings(fb.item);
                });
        var $horizontalAlignment = fb.target._horizontalAlignment({ name: 'field.horizontalAlignment', value: fb.settings.classes[0] })
                   .change(function(event) {
                            var $text = fb.item.find('div.PlainText');
                            var value = jQuery('option:selected', this).val();
                            $text.removeClass(fb.settings.classes[0]).addClass(value);
                            fb.settings.classes[0] = value;
                            fb.target._updateSettings(fb.item);
                        });

        return [fb.target._oneColumn($text), fb.target._twoColumns($horizontalAlignment, $verticalAlignment)];

    },
    _getFieldSettingsGeneralSection : function(event, fb) {
        return [];
    }
});

jQuery.widget('fb.fbPlainText', FbPlainText);
