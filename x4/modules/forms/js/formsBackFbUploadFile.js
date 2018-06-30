/*
 * JQuery Form Builder - UploadFile plugin.
 */

/*var FbUploadFile = jQuery.extend({}, jQuery.fb.fbWidget.prototype,
{
    fbOptions: { // default options. values are stored in widget's prototype
        Name: 'UploadFile',
        belongsTo: jQuery.fb.formbuilder.prototype.fbOptions._altFieldsPanel,
        _type: 'UploadFile',
        _html: '<div class="UploadFile col-lg-11"><label class="control-label"></label> \
              <input type="file" class="filestyle input-sm textInput" data-classinput="form-control inline v-middle input-s" data-classbutton="btn btn-default" data-icon="false" style="position: fixed; left: -500px;" /> \
              <div style="display: inline;" class="bootstrap-filestyle"> \
                <input type="text" disabled="" class="form-control inline v-middle input-s">\
                <label class="btn btn-default" for="filestyle-0"><span>Choose file</span><em></em></label>\
              </div>\
            <p class="help-block m-b-none formHint"></p></div>',
        _btnHtml: '<div class="form-group"> \
                    <label class="col-sm-2 control-label">File input</label> \
                    <div class="col-sm-10"> \
                      <input type="file" data-classinput="form-control inline v-middle input-s" data-classbutton="btn btn-default" data-icon="false" class="filestyle" id="filestyle-0" style="position: fixed; left: -500px;"><div style="display: inline;" class="bootstrap-filestyle"><input type="text" disabled="" class="form-control inline v-middle input-s"> <label class="btn btn-default" for="filestyle-0"><span>Choose file</span></label></div> \
                    </div> \
                  </div>',
        _counterField: 'label',
        settings: {
            label: 'UploadFile',
            value: '',
            description: ''
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
        jQuery('input', $jqueryObject).val(fb.settings.value);
        jQuery('.formHint', $jqueryObject).text(fb.settings.description);
        return $jqueryObject;
    },
    _getFieldSettingsLanguageSection : function(event, fb) {
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

        jQuery('input', $value).val(fb.settings.value)
         .keyup(function(event) {
          var value = jQuery(this).val();
          fb.item.find('.textInput').val(value);
          fb.settings.value = value;
          fb.target._updateSettings(fb.item);
        });

        var $description = fb.target._label({ label: AI.translate('forms','description'), name: 'field.description' })
          .append('<textarea id="field.description" rows="2" class="form-control"></textarea>');
        jQuery('textarea', $description).val(fb.settings.description).keyup(function(event) {
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
//Разрешённые типы файлов
//Запрещённые типы файлов

        return [$valuePanel];
    },
    _languageChange : function(event, fb) {

    }
});

jQuery.widget('fb.fbUploadFile', FbUploadFile);*/
