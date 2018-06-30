/*
 * JQuery Form Builder - Select plugin.
 */

var FbSingleSelect = jQuery.extend({}, jQuery.fb.fbWidget.prototype,
{
    fbOptions: {
        Name: AI.translate('forms', 'SingleSelect'),
        belongsTo: jQuery.fb.formbuilder.prototype.fbOptions._standardFieldsPanel,
        _type: 'SingleSelect',
        _html : '<div class="SingleSelect col-lg-11"><label class="control-label"><span></span><em></em></label> \
              <select class="form-control input-sm textInput"><option>'+AI.translate('forms', 'option')+' 1</option></select> \
            <p class="help-block m-b-none formHint"></p></div>',
        _btnHtml: '<div class="form-group"> \
            <label class="control-label">'+AI.translate('forms', 'SingleSelect')+'</label> \
            <select name="selectbasic" class="form-control m-b input-sm" disabled="1" style="background-color: #FFFFFF; cursor: pointer;"> \
                <option>'+AI.translate('forms', 'option')+' 1</option> \
                <option>'+AI.translate('forms', 'option')+' 2</option> \
                <option>'+AI.translate('forms', 'option')+' 3</option> \
            </select> \
            <span class="help-block m-b-none">'+AI.translate('forms', 'SingleSelect_text_help')+'</span></div> \
            <div class="line line-dashed b-b line-lg pull-in"></div>',
        _counterField: 'label',
        settings: {
            label: AI.translate('forms', 'SingleSelect'),
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
            if(fb.settings.value){
                $jqueryObject.find('option').remove();
                var arr_value = fb.settings.value.split("\n");
                Array.each(arr_value, function (val, i) {
                    if(val.indexOf(":") > -1){
                        dval = val.split(":");
                        $jqueryObject.find('select').append('<option value="'+jQuery.trim(dval[0])+'">'+jQuery.trim(dval[1])+'</option>');
                    } else {
                        $jqueryObject.find('select').append('<option value="'+jQuery.trim(val)+'">'+jQuery.trim(val)+'</option>');
                    }
                });
            }
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

      var $value = fb.target._label({ label: AI.translate('forms','value')+'<br /><i style="font-size:12px;">'+AI.translate('forms','elements_of_the_list')+'</i>', name: 'field.value' })
                              .append('<textarea id="field.value" type="text" rows="2" class="form-control"></textarea>');

        jQuery('textarea', $value).val(fb.settings.value).keyup(function(event) {
            fb.item.find('.textInput').html('');
            var value = jQuery(this).val();
            var arr_value = value.split("\n"); //\r\n
                Array.each(arr_value, function (val, i) {
                    if(val.indexOf(":") > -1){
                        dval = val.split(":");
                        fb.item.find('.textInput').append('<option value="'+jQuery.trim(dval[0])+'">'+jQuery.trim(dval[1])+'</option>');
                    } else {
                        fb.item.find('.textInput').append('<option value="'+jQuery.trim(val)+'">'+jQuery.trim(val)+'</option>');
                    }
                });
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

        return [fb.target._oneColumn($label), fb.target._oneColumn($value), fb.target._oneColumn($description)];
    },
    _getFieldSettingsGeneralSection : function(event, fb) {
        var $required = jQuery('<div><input type="checkbox" id="field.required" />&nbsp;'+AI.translate('forms','required')+'</div>');
        var $valuePanel = fb.target._fieldset({ text: AI.translate('forms','field_filling')}).append(fb.target._oneColumn($required));
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
    _languageChange : function(event, fb) {}
});

jQuery.widget('fb.fbSingleSelect', FbSingleSelect);
