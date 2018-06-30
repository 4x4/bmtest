/*
 * JQuery Form Builder - Textarea plugin.
 */

var FbTextarea = jQuery.extend({}, jQuery.fb.fbWidget.prototype,
{
    fbOptions: {
        Name: AI.translate('forms', 'Textarea'),
        belongsTo: jQuery.fb.formbuilder.prototype.fbOptions._standardFieldsPanel,
        _type: 'Textarea',
        _html : '<div class="Textarea col-lg-11"><label class="control-label"><span></span><em></em></label> \
              <textarea class="form-control textInput" placeholder=""></textarea> \
            <p class="help-block m-b-none formHint"></p></div>',
        _btnHtml: '<div class="form-group"> \
            <label class="control-label">'+AI.translate('forms', 'Textarea')+'</label> \
            <textarea class="form-control input-sm textinput" readonly="1" style="background-color: #FFFFFF; cursor: pointer;"></textarea> \
            <span class="help-block m-b-none">'+AI.translate('forms', 'Textarea_text_help')+'</span></div> \
            <div class="line line-dashed b-b line-lg pull-in"></div>',
        _counterField: 'label',
        settings: {
            label: AI.translate('forms', 'Textarea'),
            value: '',
            description: '',
            placeholder: '',
            required: true,
            restriction: 'no'
        }
    },
    _init : function() {
        // calling base plugin init
        jQuery.fb.fbWidget.prototype._init.call(this);
        // merge base plugin's options
        this.fbOptions = jQuery.extend({}, jQuery.fb.fbWidget.prototype.fbOptions, this.fbOptions);
    },
    _getWidget : function(event, fb) {
        var $jqueryObject = jQuery(fb.target.fbOptions._html);
        jQuery('label span', $jqueryObject).text(fb.settings.label);
        if (fb._settings.required) {
            jQuery('label em', $jqueryObject).text('*');
        }
        jQuery('textarea', $jqueryObject).val(fb.settings.value);
        jQuery('textarea', $jqueryObject).attr('placeholder', fb.settings.placeholder);
        jQuery('.formHint', $jqueryObject).text(fb.settings.description);
        return $jqueryObject;
    },
    _getFieldSettingsLanguageSection : function(event, fb) {
        var $label = fb.target._label({ label: AI.translate('forms','label'), name: 'field.label' })
                         .append('<input type="text" id="field.label" class="form-control" />');
    jQuery('input', $label).val(fb.settings.label)
     .keyup(function(event) {
           var value = jQuery(this).val();
          fb.item.find('label span').text(value);
          fb.settings.label = value;
          fb.target._updateSettings(fb.item);
          fb.target._updateName(fb.item, value);
         });

      var $value = fb.target._label({ label: AI.translate('forms','default_value'), name: 'field.value' })
                              .append('<textarea id="field.value" rows="2" class="form-control textInput"></textarea>');

        jQuery('textarea', $value).val(fb.settings.value)
         .keyup(function(event) {
          var value = jQuery(this).val();
          fb.item.find('.textInput').val(value);
          fb.settings.value = value;
          fb.target._updateSettings(fb.item);
        });

        var $description = fb.target._label({ label: AI.translate('forms','description'), name: 'field.description' })
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

        return [fb.target._twoColumns($label, $value), fb.target._oneColumn($placeholder), fb.target._oneColumn($description)];
    },
    _getFieldSettingsGeneralSection : function(event, fb) {
        var $required = jQuery('<div><input type="checkbox" id="field.required" />&nbsp;'+AI.translate('forms','required')+'</div>');
        var $restriction = jQuery('<div><select id="field.restriction" class="form-control" style="width: 99%"> \
                <option value="no">'+AI.translate('forms','any_character')+'</option> \
                <option value="letterswithbasicpunc">'+AI.translate('forms','letters_and_punctuation_only')+'</option> \
                <option value="alphanumeric">'+AI.translate('forms','alphanumeric_only')+'</option> \
                <option value="onlyLetterSp">'+AI.translate('forms','onlyLetterSp')+'</option> \
                <option value="onlyLetterCyrillicSp">'+AI.translate('forms','onlyLetterCyrillicSp')+'</option> \
            </select></div>');

        var $valuePanel = fb.target._fieldset({ text: AI.translate('forms','field_filling')})
                          .append(fb.target._twoColumns($required, $restriction));
        jQuery('.col1', $valuePanel).css('width', '32%').removeClass('labelOnTop');
        //jQuery('.col2', $valuePanel).css('marginLeft', '34%').removeClass('labelOnTop');

        jQuery('input', $required).attr('checked', fb.settings.required)
         .change(function(event) {
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

jQuery.widget('fb.fbTextarea', FbTextarea);
