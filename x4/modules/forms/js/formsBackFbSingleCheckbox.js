/*
 * JQuery Form Builder - SingleCheckbox plugin.
 */

var FbSingleCheckbox = jQuery.extend({}, jQuery.fb.fbWidget.prototype,
{
    fbOptions: {
        Name: AI.translate('forms', 'SingleCheckbox'),
        belongsTo: jQuery.fb.formbuilder.prototype.fbOptions._standardFieldsPanel,
        _type: 'SingleCheckbox',
        _html : '<div class="SingleCheckbox col-lg-11 checkbox i-checks"> <input class="textInput" type="checkbox" checked="1" /><i></i> \
            <label class="control-label"><span></span><em></em></label> \
            <p class="help-block m-b-none formHint"></p></div>',
        _btnHtml: '<div class="checkbox i-checks"> \
            <label class="control-label">'+AI.translate('forms', 'SingleCheckbox')+'</label> \
            <input name="checkboxinput" type="checkbox" class="form-control m-b input-sm textInput" checked="1" style="cursor: pointer;" /> \
            <span class="help-block m-b-none">'+AI.translate('forms', 'SingleCheckbox_text_help')+'</span></div> \
            <div class="line line-dashed b-b line-lg pull-in"></div>',
        _counterField: 'label',
        //_languages: [ 'en_EN', 'ru_RU' ],
        settings: {
            label: 'SingleCheckbox',
            value: '',
            description: ''
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
        jQuery('input', $jqueryObject).val(fb.settings.value);
        jQuery('.formHint', $jqueryObject).text(fb.settings.description);
        return $jqueryObject;
    },
    _getFieldSettingsLanguageSection : function(event, fb) {
        var $label = fb.target._label({ label: AI.translate('forms','label'), name: 'field.label' })
                         .append('<input type="text" id="field.label" class="form-control inp" />');
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

        jQuery('input', $value).val(fb.settings.value)
         .keyup(function(event) {
          var value = jQuery(this).val();
          fb.item.find('.textInput').val(value);
          fb.settings.value = value;
          fb.target._updateSettings(fb.item);
        });

        var $description = fb.target._label({ label: AI.translate('forms','description'), name: 'field.description' })
          .append('<textarea id="field.description" rows="2" class="form-control"></textarea>');
        jQuery('textarea', $description).val(fb.settings.description)
            .keyup(function(event) {
              var value = jQuery(this).val();
              fb.item.find('.formHint').text(value);
              fb.settings.description = value;
              fb.target._updateSettings(fb.item);
        });

        return [fb.target._twoColumns($label, $value), fb.target._oneColumn($description)];
    },
    _getFieldSettingsGeneralSection : function(event, fb) {
        var $required = jQuery('<div><input type="checkbox" id="field.required" />&nbsp;'+AI.translate('forms','required')+'</div>');
        var $valuePanel = fb.target._fieldset({ text: AI.translate('forms','field_filling')})
                          .append(fb.target._oneColumn($required));
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

        return [$valuePanel];
    },
    _languageChange : function(event, fb) {

    }
});

jQuery.widget('fb.fbSingleCheckbox', FbSingleCheckbox);
