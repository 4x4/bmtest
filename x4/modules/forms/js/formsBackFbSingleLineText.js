/*
 * JQuery Form Builder - Single Line Text plugin.
 *
 * Revision: 121
 * Version: 0.1
 * Copyright 2011 Lim Chee Kin (limcheekin@vobject.com)
 *
 * Licensed under Apache v2.0 http://www.apache.org/licenses/LICENSE-2.0.html
 *
 * Date: 10-Feb-2011
 */

var FbSingleLineText = jQuery.extend({}, jQuery.fb.fbWidget.prototype,
{
    fbOptions: { // default options. values are stored in widget's prototype
        Name: AI.translate('forms', 'SingleLineText'),
        belongsTo: jQuery.fb.formbuilder.prototype.fbOptions._standardFieldsPanel,
        _type: 'SingleLineText',
        _html: '<div class="SingleLineText col-lg-11"><label class="control-label"><span></span><em></em></label> \
              <input type="text" class="form-control input-sm textInput" placeholder="" /> \
            <p class="help-block m-b-none formHint"></p></div>',
        _btnHtml: '<div class="form-group"> \
            <label class="control-label">'+AI.translate('forms', 'SingleLineText')+'</label> \
            <input name="textinput" type="text" class="form-control input-sm" readonly="1" style="background-color: #FFFFFF; cursor: pointer;" /> \
            <span class="help-block m-b-none">'+AI.translate('forms', 'SingleLineText_text_help')+'</span></div> \
            <div class="line line-dashed b-b line-lg pull-in"></div>',
        _counterField: 'label',
        settings: {
            label: AI.translate('forms', 'SingleLineText'),
            value: '',
            description: '',
            placeholder: '',
            type: 'text',
            _persistable: true,
            required: true,
            restriction: 'no'
        }
    },
    _init: function() {
        // calling base plugin init
        jQuery.fb.fbWidget.prototype._init.call(this);
        // merge base plugin's options
        this.fbOptions = jQuery.extend({}, jQuery.fb.fbWidget.prototype.fbOptions, this.fbOptions);
    },
    _getWidget: function(event, fb) {
        var $jqueryObject = jQuery(fb.target.fbOptions._html);
        jQuery('label span', $jqueryObject).text(fb.settings.label);
        if (fb._settings.required) {
            jQuery('label em', $jqueryObject).text('*');
        }
        if(fb.settings.type == 'hidden') {
            jQuery('label', $jqueryObject).css('color','#b9b6b9');
        }
        jQuery('input', $jqueryObject).val(fb.settings.value);
        jQuery('input', $jqueryObject).attr('placeholder', fb.settings.placeholder);
        jQuery('.formHint', $jqueryObject).text(fb.settings.description);
        return $jqueryObject;
    },
    _getFieldSettingsLanguageSection: function(event, fb) {
        var $label = fb.target._label({ label: AI.translate('forms','label'), name: 'field.label' })
                         .append('<input type="text" class="form-control inp" id="field.label" />');
    jQuery('input', $label).val(fb.settings.label)
     .keyup(function(event) {
           var value = jQuery(this).val();
          fb.item.find('label span').text(value);
          fb.settings.label = value;
          fb.target._updateSettings(fb.item);
          fb.target._updateName(fb.item, value);
         });

      var $value = fb.target._label({ label: AI.translate('forms','default_value'), name: 'field.value' })
                              .append('<input type="text" class="form-control inp" id="field.value" />');
        jQuery('input', $value).val(fb.settings.value).keyup(function(event) {
          var value = jQuery(this).val();
          fb.item.find('.textInput').val(value);
          fb.settings.value = value;
          fb.target._updateSettings(fb.item);
        });

        var $description = fb.target._label({label: AI.translate('forms','description'), name: 'field.description'})
          .append('<textarea id="field.description" rows="2" class="form-control textInput"></textarea>');
        jQuery('textarea', $description).val(fb.settings.description)
            .keyup(function(event) {
              var value = jQuery(this).val();
              fb.item.find('.formHint').text(value);
              fb.settings.description = value;
              fb.target._updateSettings(fb.item);
        });

        var $placeholder = fb.target._label({label: AI.translate('forms','placeholder'), name: 'field.placeholder'})
          .append('<input type="text" class="form-control inp" id="field.placeholder" />');
        jQuery('input', $placeholder).val(fb.settings.placeholder).keyup(function(event) {
          var value = jQuery(this).val();
          fb.item.find('.textInput').attr('placeholder', value);
          fb.settings.placeholder = value;
          fb.target._updateSettings(fb.item);
        });

        var $type = fb.target._label({label: AI.translate('forms','field_type'), name: 'field.type'})
          .append('<select id="field.type" class="form-control inp"> \
            <option value="text">'+AI.translate('forms','standart_text')+'</option> \
            <option value="hidden">'+AI.translate('forms','hidden')+'</option> \
          </select>');
        jQuery("select option[value='" + fb.settings.type + "']", $type).attr('selected', 'true');
        jQuery('select', $type).change(function(event) {
            fb.settings.type = jQuery(this).val();
            fb.target._updateSettings(fb.item);

            if(fb.settings.type == 'hidden') {
                jQuery('label', fb.item).css('color','#b9b6b9');
            } else {
                jQuery('label', fb.item).removeAttr('style');
            }
        });

        return [fb.target._twoColumns($label, $value), fb.target._twoColumns($placeholder, $type), fb.target._oneColumn($description)];
    },
    _getFieldSettingsGeneralSection: function(event, fb) {
        var $required = jQuery('<div><input type="checkbox" id="field.required" />&nbsp;'+AI.translate('forms','required')+'</div>');
        var $restriction = jQuery('<div><select id="field.restriction" class="form-control inp" style="width: 99%"> \
                <option value="no">'+AI.translate('forms','any_character')+'</option> \
                <option value="letterswithbasicpunc">'+AI.translate('forms','letters_and_punctuation_only')+'</option> \
                <option value="onlyLetterNumber">'+AI.translate('forms','alphanumeric_only')+'</option> \
                <option value="onlyLetterSp">'+AI.translate('forms','onlyLetterSp')+'</option> \
                <option value="onlyLetterCyrillicSp">'+AI.translate('forms','onlyLetterCyrillicSp')+'</option> \
                <option value="onlyNumberSp">'+AI.translate('forms','onlyNumberSp')+'</option> \
                <option value="phone">'+AI.translate('forms','phone')+'</option> \
                <option value="email">'+AI.translate('forms','email')+'</option> \
                <option value="url">'+AI.translate('forms','url')+'</option> \
            </select></div>');

        var $valuePanel = fb.target._fieldset({ text: AI.translate('forms','field_filling')}).append(fb.target._twoColumns($required, $restriction));
        jQuery('.col1', $valuePanel).css('width', '32%').removeClass('labelOnTop');
        //jQuery('.col2', $valuePanel).css('marginLeft', '34%').removeClass('labelOnTop');

        jQuery('input', $required).attr('checked', fb.settings.required).change(function(event) {
            if (jQuery(this).attr('checked')) {
                fb.item.find('em').text('*');
                fb.settings.required = true;
            } else {
                fb.item.find('em').text('');
                fb.settings.required = false;
            }
            fb.target._updateSettings(fb.item);
        });

        jQuery("select option[value='" + fb.settings.restriction + "']", $restriction).attr('selected', 'true');
        jQuery('select', $restriction).change(function(event) {
            fb.settings.restriction = jQuery(this).val();
            fb.target._updateSettings(fb.item);
        });

        return [$valuePanel];
    },
    _languageChange : function(event, fb) {

    }
});

jQuery.widget('fb.fbSingleLineText', FbSingleLineText);
