/*
* jquery-form-builder-plugin - JQuery WYSIWYG Web Form Builder
* http://code.google.com/p/jquery-form-builder-plugin/
*
* Revision: 121
* Version: 0.1
* Copyright 2011 Lim Chee Kin (limcheekin@vobject.com)
*
* Licensed under Apache v2.0 http://www.apache.org/licenses/LICENSE-2.0.html
*
* Date: Fri Feb 18 22:43:32 GMT+08:00 2011
*/

/*
 * Main component of JQuery Form Builder plugin, the Form Builder container itself
 * consists of builder palette contains widgets supported by the form builder and
 * builder panel where the constructed form display.
 *
 * Revision: 121
 * Version: 0.1
 * Copyright 2011 Lim Chee Kin (limcheekin@vobject.com)
 *
 * Licensed under Apache v2.0 http://www.apache.org/licenses/LICENSE-2.0.html
 *
 * Date: 16-Jan-2011
 */

var FormBuilder = {
    // default options. values are stored in prototype
  fbOptions: {
      fields: 'PlainText, SingleLineText, Textarea, SingleSelect, MultipleSelect, SingleCheckbox', //, UploadFile
      tabSelected: 0,
      readOnly: false,
      tabDisabled: [],
      settings: {
            Name: AI.translate('forms', 'default-form-name'),
            classes: ['leftAlign'],
            heading: 'h1'
      },
      fieldsets: [{}], //[{id: 'fieldset1', name: AI.translate('forms', 'default-fieldset-name'), type: 'FIELDSET'}],
      fieldsetClass: 'fieldsetPanel',
      fieldsetSelectedClass: 'current',
      _id: '#formBuilderContainer',
      //_languages: [ 'en_EN', 'ru_RU' ],
      _builderPanel: '#fieldsetPanel_0',
      _builderForm: '#builderForm',
      _builderFieldsetCurrent: '#fieldsetPanel_0',
      _emptyBuilderPanel: '#emptyBuilderPanel_0',
      _paletteTabs: '#paletteTabs',
      _standardFieldsPanel: '#standardFields',
      _altFieldsPanel: '#altFields',
      _fieldsetSettingsPanel: '#fieldsetSettings',
      _fieldSettingsPanel: '#fieldSettings',
      //_fieldsetSettingsLanguageSection: '#fieldsetSettings fieldset.language:first',
      _fieldsetSettingsGeneralSection: '#fieldsetSettings div.general:first',
      _fieldSettingsLanguageSection: '#fieldSettings fieldset.language:first',
      _fieldSettingsGeneralSection: '#fieldSettings div.general:first',
      //_formSettingsLanguageSection: '#formSettings fieldset.language:first',
      _formSettingsGeneralSection: '#formSettings div.general:first',
      //_languagesSupportIdGeneration: ['ru_RU'],
      _dragBoxCss: {
          opacity: 0.8,
          zIndex: 8888,
          border: '1px dashed #177BBB' //"1px solid #cccccc"
      },
      _formControls: '#fieldsetPanel_0 fieldset',
      _formControlCurrent: '#fieldsetPanel_0 fieldset',
      _draggableClass: 'draggable',
      _dropPlaceHolderClass: 'dropPlaceHolder',
      _dropOverElement:null
  },

  _create: function() {
      // called on construction
      // this._initBrowserDefaultSettings();
      this._initBuilderPalette();
      this._initBuilderPanel();
  },

  _initBrowserDefaultSettings: function() {},

  _initBuilderPalette: function() {
        // REF: http://www.webresourcesdepot.com/smart-floating-banners/
        jQuery(this.fbOptions._paletteTabs).tabs({
            selected: this.fbOptions.tabSelected,
            disabled: this.fbOptions.tabDisabled,
            select: this._isFieldSettingsTabCanOpen
        });

        jQuery('#show_form_settings').on('click', function(evn){
            evn.preventDefault(); //clip
            jQuery('#formSettings').slideDown('fast', function(){ jQuery(this).removeClass('nav-xs'); });
        });

        jQuery('#formSettingsClose').on('click', function(evn){
            evn.preventDefault();
            jQuery('#formSettings').hide();
        });

        var widgets = this.fbOptions.fields;
            widgets = widgets.split(',');
        var length = widgets.length;
        var widgetOptions;
        var widget;
        var i;

            for (i = 0; i < length; i++) {
                widgetOptions = jQuery['fb']['fb' + jQuery.trim(widgets[i])].prototype.fbOptions;
                  if(widgetOptions._btnHtml){
                      widget = jQuery('<div id="' + widgetOptions._type +  '" class="fbWidget component"></div>').append(widgetOptions._btnHtml);
                  } else {
                      widget = jQuery('<a id="' + widgetOptions._type +  '" href="#" class="fbWidget btn btn-default">' + widgetOptions.Name + '</a>');
                  }
                widget.button()['fb' + widgetOptions._type]().appendTo(widgetOptions.belongsTo);
                this._initDraggable(widget, widgetOptions._type);
            }
  },

  _isFieldSettingsTabCanOpen: function(event, ui) {
        if (ui.index == 2) { // Field Settings tab selected
            var options = jQuery.fb.formbuilder.prototype.fbOptions;
            var canOpen = true;
            if (jQuery(options._emptyBuilderPanel).is(':visible')) {
                canOpen = false;
            } else if (jQuery(options._builderForm + ' .' + jQuery.fb.fbWidget.prototype.fbOptions._selectedClass).length === 0) {
                canOpen = false;
            }
            if (!canOpen) {
                // activate Add Field tab
                jQuery(this).tabs('select', 0);
            }
            return canOpen;
        }
  },

  _initBuilderPanel: function() {
      this._initFormSettings();
      this._initFieldsets();
      if (!this.fbOptions.readOnly) {
        this._initSortableWidgets();
        this._initDroppable();
      } else {
            jQuery('input:not(div.buttons input, #id)').attr("disabled", true);
            jQuery('select:not(#language)').attr("disabled", true);
            jQuery('textarea').attr("disabled", true);
        }
      this._initWidgetsEventBinder();
  },

  _updateBuilderForm: function() {
      this._initFieldsets();
      this._initSortableWidgets();
      this._initDroppable();
      this._initWidgetsEventBinder();
  },

  _initDraggable: function(widget, type) {
      widget.draggable({
          cursor: 'move',
          distance: 10,
          helper: function (event, ui) {
              var helper = jQuery(this).data('fb' + type)
                ._createFbWidget(event).css(jQuery.fb.formbuilder.prototype.fbOptions._dragBoxCss)
                .css({width: jQuery('.fieldsetHeading').css('width')})
                .addClass(jQuery.fb.formbuilder.prototype.fbOptions._draggableClass);
              return helper;
          },
          drag: function(event, ui) {
            var $prevCtrlHolder = jQuery.fb.formbuilder.prototype._getPreviousCtrlHolder(ui);
            var fbOptions = jQuery.fb.formbuilder.prototype.fbOptions;
                if ($prevCtrlHolder && $prevCtrlHolder.attr('rel') != ui.helper.attr('rel')) {
                    ui.helper.attr('rel', $prevCtrlHolder.attr('rel'));
                    jQuery('.' + fbOptions._dropPlaceHolderClass).remove();
                  jQuery('<div></div>').addClass(fbOptions._dropPlaceHolderClass)
                   .css('height', '30px').insertAfter($prevCtrlHolder);
                } else {
                    var $ctrlHolder = jQuery('.' + jQuery.fb.fbWidget.prototype.fbOptions._styleClass +
                               ':visible:not(.' + fbOptions._draggableClass + '):first');

                    if ($ctrlHolder.length && ui.offset.top < $ctrlHolder.offset().top) {
                        jQuery('.' + fbOptions._dropPlaceHolderClass).remove();
                    }
                }
          },
          stop: function(event, ui) {
            jQuery('.' + jQuery.fb.formbuilder.prototype.fbOptions._dropPlaceHolderClass).remove();
          }
      });
  },

  _initDroppable: function() {
      var fbOptions = this.fbOptions;
      var $formControls = jQuery(fbOptions._formControls);
      $this = this;

      $formControls.droppable({
          drop: function(event, ui) {
              // to make sure the drop event is trigger by draggable instead of sortable
              if (ui.helper.attr('class').lastIndexOf(fbOptions._draggableClass) > -1) {
                   jQuery('.' + fbOptions._dropPlaceHolderClass).remove();
                 var $widget = ui.draggable.data('fb' + ui.draggable.attr('id'));
                 var $prevCtrlHolder = jQuery.fb.formbuilder.prototype._getPreviousCtrlHolder(ui, event);
                 var $ctrlHolder = $widget._createFbWidget(event);
                 var $dropOverFieldset = jQuery(event.target);
                 var $elements;
                     if ($prevCtrlHolder) {
                         $ctrlHolder.insertAfter($prevCtrlHolder);
                         $elements = $prevCtrlHolder.next().nextAll(); // $ctrlHolder.next() not works
                     } else {
                        if($dropOverFieldset){
                          $elements = jQuery('.' + jQuery.fb.fbWidget.prototype.fbOptions._styleClass +
                            ':visible:not(.' + fbOptions._draggableClass + ')', $dropOverFieldset);
                          //*** Скрываем текст "empty-fieldset" в слоте (_FIELDSET)
                          $dropOverFieldset.find('.emptyFieldsetPanel').hide();
                          $dropOverFieldset.prepend($ctrlHolder).sortable('refresh');
                        }
                     }

                 $ctrlHolder.toggle('slide', {direction: 'up'}, 'slow');

                    if ($elements.length) {
                        // set next widget's sequence as my sequence
                        jQuery.fb.formbuilder.prototype._getSequence($ctrlHolder).val(
                            jQuery.fb.formbuilder.prototype._getSequence($elements.first()).val()).change();
                        $elements.each(function(index) {
                          jQuery.fb.formbuilder.prototype._increaseSequence(jQuery(this));
                         });
                    }
              }
          },
          over: function(event, ui) {
              jQuery.fb.formbuilder.prototype.fbOptions._dropOverFieldset = jQuery(event.target);
          }
      });
  },

  _getPreviousCtrlHolder: function(ui, event) {
        var $this, $ctrlHolders, $prevCtrlHolder;
        var $dropOverFieldset = jQuery.fb.formbuilder.prototype.fbOptions._dropOverFieldset;

        if($dropOverFieldset){
          $ctrlHolders = $dropOverFieldset.find('.' + jQuery.fb.fbWidget.prototype.fbOptions._styleClass +
                     ':visible:not(.' + jQuery.fb.formbuilder.prototype.fbOptions._draggableClass + ')');
        } else {
          $ctrlHolders = jQuery('.' + jQuery.fb.fbWidget.prototype.fbOptions._styleClass +
                     ':visible:not(.' + jQuery.fb.formbuilder.prototype.fbOptions._draggableClass + ')');
        }

        $ctrlHolders.each(function(i) {
            $this = jQuery(this);
            if (ui.offset.top > $this.offset().top) {
                $prevCtrlHolder = $this;
            } else {
                return false;
            }
        });
        return $prevCtrlHolder;
  },

  _initFormSettings: function() {
      var $fbWidget = jQuery.fb.fbWidget.prototype;
      var options = this.fbOptions;
      var $builderPanel = jQuery(options._builderPanel);
      var $builderForm = jQuery(options._builderForm);
      var $formSettingsGeneralSection = jQuery(options._formSettingsGeneralSection);
      var settings;
      var $this = this;
      var $formHeading = jQuery('#header');
      var $fieldsetHeading = jQuery('.fieldsetHeading', $builderPanel);
      var $settings = jQuery('#settings', $builderForm);
        // first creation
        if ($settings.val() == '') {
            settings = options.settings;
            $formHeading.addClass(settings.classes[0]).html('<' + settings.heading + ' class="heading">' + settings.Name + '</' + settings.heading + '>');
            jQuery('#Name',$builderForm).val($fbWidget._propertyName(options.settings.Name));
            $this._updateSettings($this);
        } else {
            options.settings = jQuery.parseJSON(unescape($settings.val()));
            settings = options.settings;
        }

        if(!settings.subject) {
            settings.subject = settings.Name;
        }
        if(!settings.timeout) {
            settings.timeout = 60;
        }
        if(!settings.email) {
            settings.email = '';
        }
        if(!settings.message_after) {
            settings.message_after = '';
        }
        if(!settings.comment) {
            settings.comment = '';
        }

      var $name = $fbWidget._label({ label: AI.translate('forms', 'Name'), name: 'form.Name' })
          .append('<input type="text" placeholder="' +AI.translate('forms', 'Name') + '" id="form.Name" class="form-control validate[required]" value="' + settings.Name + '" />');
      jQuery('input', $name).keyup(function(event) {
          var value = jQuery(this).val();
          var name = $fbWidget._propertyName(value);
          jQuery('#Name',$builderForm).val(name).change();
          settings.Name = value;
          jQuery(settings.heading, $formHeading).text(value);
          $this._updateSettings($this);
      });

      var $disable = $fbWidget._label({label:'',name:'form.disable' }).append('<div class="checkbox i-checks">\
           <label><input type="checkbox" id="form.Disable" value="" /><i></i>' + AI.translate('forms', 'Disable') + '</label></div>');
        if(settings.disable == '1') { jQuery('input', $disable).attr('checked', 1); }

      var $subject = $fbWidget._label({label:AI.translate('forms','subject'),name:'form.subject'}).append('<input class="form-control input-sm validate[required]" type="text" id="form.subject" value="'+settings.subject+'" />');
      var $email = $fbWidget._label({label:AI.translate('forms','email'),name:'form.email' }).append('<input class="form-control input-sm validate[required]" type="text" id="form.email" value="'+settings.email+'" />');
      var $charset = $fbWidget._label({label: AI.translate('forms','charset'),name:'form.charset'}).append('<select class="form-control input-sm" id="form.charset"> \
                <option value="utf-8">utf-8</option> \
                <option value="windows-1251">windows-1251</option> \
      </select>');
        if(settings.charset) { jQuery('select', $charset).find('option[value="'+ settings.charset +'"]').attr('selected', 1); }

      var $timeout = $fbWidget._label({ label: AI.translate('forms', 'timeout'), name: 'form.timeout' }).append('<input class="form-control input-sm validate[required]" type="text" id="form.timeout" value="' + settings.timeout + '" />');
      var $comment = $fbWidget._label({ label: AI.translate('forms', 'comment'), name: 'form.comment' }).append('<textarea class="form-control input-sm" id="form.comment">'+settings.comment+'</textarea>');
      var $message_after = $fbWidget._label({ label: AI.translate('forms', 'message_after'), name: 'form.message_after' }).append('<textarea class="form-control input-sm" id="form.message_after">'+settings.message_after+'</textarea>');

      var $save_to_server = $fbWidget._label({ label: '', name: 'form.save_to_server' }).append('<div class="checkbox i-checks">\
            <label><input type="checkbox" id="form.save_to_server" checked="1" value="" /><i></i>' + AI.translate('forms', 'save_to_server') + '</label></div>');
            if(settings.save_to_server == '0') { jQuery('input', $save_to_server).removeAttr('checked'); }

      var $use_captcha = $fbWidget._label({ label: '', name: 'form.use_captcha' }).append('<div class="checkbox i-checks">\
            <label><input type="checkbox" id="form.use_captcha" checked="1" value="" /><i></i>' + AI.translate('forms', 'use_captcha') + '</label></div>');
            if(settings.use_captcha == '0') { jQuery('input', $use_captcha).removeAttr('checked'); }

      var $async = $fbWidget._label({ label: '', name: 'form.async' }).append('<div class="checkbox i-checks">\
            <label><input type="checkbox" id="form.async" checked="1" value="" /><i></i>' + AI.translate('forms', 'async') + '</label></div>');
            if(settings.async == '0') { jQuery('input', $async).removeAttr('checked'); }

      var $captcha_settings= $fbWidget._label({label:AI.translate('forms', 'captcha_settings'),name:'form.captcha_settings'}).append('<select class="form-control input-sm" id="form.captcha_settings"> \
                <option value="4-110">'+AI.translate('forms', 'captcha_easy')+'</option> \
                <option value="6-145">'+AI.translate('forms', 'captcha_medium')+'</option> \
                <option value="10-210">'+AI.translate('forms', 'captcha_high')+'</option> \
                <option value="14-265">'+AI.translate('forms', 'captcha_very_high')+'</option> \
            </select>');
            if(settings.captcha_settings) { jQuery('select', $captcha_settings).find('option[value="'+ settings.captcha_settings +'"]').attr('selected', 1); }

      var $heading = $fbWidget._label({label:'Heading',name:'form.heading'}).append('<select class="form-control input-sm"> \
                <option value="h1">Heading 1</option> \
                <option value="h2">Heading 2</option> \
                <option value="h3">Heading 3</option> \
                <option value="h4">Heading 4</option> \
                <option value="h5">Heading 5</option> \
                <option value="h6">Heading 6</option> \
      </select>');

      jQuery('select', $heading).val(settings.heading)
                .attr('id', 'form.heading') // unable to set value if specify in select tag
                .change(function(event) {
                    var heading = jQuery(this).val();
                    var text = jQuery(settings.heading, $formHeading).text();
                    var $heading = jQuery('<' + heading + ' class="heading">' + text + '</' + heading + '>');
                    jQuery(settings.heading, $formHeading).replaceWith($heading);
                    settings.heading = heading;
                    $this._updateSettings($this);
          });

      var $horizontalAlignment = $fbWidget._horizontalAlignment({name:'form.horizontalAlignment',value:settings.classes[0]});
      jQuery('select', $horizontalAlignment).change(function(event) {
                    var value = jQuery(this).val();
                    $formHeading.removeClass(settings.classes[0]).addClass(value);
                    settings.classes[0] = value;
                    $this._updateSettings($this);
      });

      var $templates = $fbWidget._label({label: AI.translate('forms', 'Templates'),name: 'Template'}).append('<select id="form.Template" class="form-control validate[required] input-sm" name="Template"></select>');
      //value: settings.classes[0]
      var $submitTemplate = $fbWidget._label({label: AI.translate('forms', 'template_for_submitting_form'),name: 'submitTemplate'}).append('<select id="form.submitTemplate" class="form-control validate[required] input-sm" name="submitTemplate"></select>');

      var $formId = (settings.id) ? $fbWidget._label({label:'id',name:'form.id'}).append('<input class="form-control input-sm" type="text" readonly="1" id="form.id" value="' + settings.id + '" />') : $fbWidget._label({label:'',name:'form.id'}).append('<input type="hidden" id="form.id" value="null" />');
      var $formBasic = (settings.basic) ? $fbWidget._label({label:'basic',name:'form.basic'}).append('<input class="form-control input-sm" type="hidden" readonly="1" id="form.basic" value="' + settings.basic + '" />') : $fbWidget._label({label:'',name:'form.basic'}).append('<input type="hidden" readonly="1" id="form.basic" value="null" />');


      $formSettingsGeneralSection.append($fbWidget._oneColumn($name))
          .append($fbWidget._twoColumns($disable, $formId))
          .append($fbWidget._oneColumn($formBasic.hide()))
          .append($fbWidget._oneColumn($templates))
          .append($fbWidget._oneColumn($submitTemplate))
          //.append($fbWidget._twoColumns($heading, $horizontalAlignment))
          //.append($fbWidget._twoColumns($subject, $email))
          .append($fbWidget._twoColumns($subject, $charset))
          .append($fbWidget._oneColumn($email))
          //.append($fbWidget._twoColumns($charset, $timeout))
          .append($fbWidget._oneColumn($timeout))
          .append($fbWidget._twoColumns($message_after, $comment))
          .append($fbWidget._twoColumns($save_to_server, $use_captcha))
          .append($fbWidget._twoColumns($async, $captcha_settings));
  },

  _initFieldsets: function() {
      var options = this.fbOptions;
      var $builderForm = jQuery(options._builderForm);
      var $formSettingsLanguageSection = jQuery(options._formSettingsLanguageSection);
      var $formSettingsGeneralSection = jQuery(options._formSettingsGeneralSection);
      var settings;
      var $this = this;
      var $settings = jQuery('#settings', $builderForm);
      var $dynamicFieldsets = jQuery('#dynamicFieldsets', $builderForm);
      var fieldsets = (options.fieldsets[0]['type'] == 'GROUP') ? options.fieldsets[0]['items'] : options.fieldsets;
      var builderPanels = [];
      var emptyBuilderPanels = [];
      var formControls = [];
      var fieldsetWidth = 874;

        //При смене шаблона обрабатываем значение basic в настройах формы
        if(options.fieldsets[0]['type'] == 'GROUP' && jQuery('input[id="form.basic"]', $formSettingsGeneralSection).length && typeof options.fieldsets[0]['id'] != 'undefined') {
            jQuery('input[id="form.basic"]', $formSettingsGeneralSection).val(options.fieldsets[0]['id']);
        } else if(options.fieldsets[0]['type'] == 'FIELDSET' && jQuery('input[id="form.basic"]', $formSettingsGeneralSection).length) {
            if(jQuery('input[id="form.id"]', $formSettingsGeneralSection).val() && jQuery('input[id="form.id"]', $formSettingsGeneralSection).val() != 'null' && (!options.fieldsets[0]['id'] || !Number(options.fieldsets[0]['id']))) {
                jQuery('input[id="form.basic"]', $formSettingsGeneralSection).val(jQuery('input[id="form.id"]', $formSettingsGeneralSection).val());
            } else if(!jQuery('input[id="form.id"]', $formSettingsGeneralSection).val() || jQuery('input[id="form.id"]', $formSettingsGeneralSection).val() == 'null') {
                jQuery('input[id="form.basic"]', $formSettingsGeneralSection).val('null');
            }
        } else {
            //Если fieldsets пуст
            jQuery('#formSettings').slideDown('fast', function(){ jQuery(this).removeClass('nav-xs'); });
            $dynamicFieldsets.html('\
                <section class="fieldsetPanel panel-default col-lg-12">\
                    <header class="panel-heading bg-light"><h3>'+AI.translate('forms', 'select_template')+'</h3></header>\
                </section>');
            return false;
        }

// first creation
        if ($settings.val() == '') {
            settings = options.settings;
        } else {
            options.settings = jQuery.parseJSON(unescape($settings.val()));
            settings = options.settings;
        }

      jQuery('.fieldsetPanel').remove();

      switch(typeof(fieldsets)){
          case 'object':
            i = 0;
            Object.each(fieldsets, function(item, key){
                $dynamicFieldsets.append($this._createFieldset(item, i, settings, options));
                builderPanels[i] = '#fieldsetPanel_'+i;
                formControls[i] = '#fieldsetPanel_'+i+' fieldset';
                emptyBuilderPanels[i] = '#emptyBuilderPanel_'+i;
                i++;
            });
            break;
          case 'array':
            Array.each(fieldsets, function (item, i) {
                $dynamicFieldsets.append($this._createFieldset(item, i, settings, options));
                builderPanels[i] = '#fieldsetPanel_'+i;
                formControls[i] = '#fieldsetPanel_'+i+' fieldset';
                emptyBuilderPanels[i] = '#emptyBuilderPanel_'+i;
            });
            break;
      }

      if(jQuery('section.'+options.fieldsetClass, $dynamicFieldsets).length==1){ //Пока сделаем так
          jQuery('section.'+options.fieldsetClass, $dynamicFieldsets).removeClass('col-lg-6').addClass('col-lg-12');
          jQuery('section.'+options.fieldsetClass, $dynamicFieldsets).css('width','98%');
      }

      jQuery('section.'+options.fieldsetClass, $dynamicFieldsets).toggle('slide', {direction: 'up'}, 'slow');

      this.fbOptions._builderPanel = builderPanels.join(',');
      this.fbOptions._emptyBuilderPanel = emptyBuilderPanels.join(',');
      this.fbOptions._formControls = formControls.join(',');
  },

  _createFieldset: function(item, i, settings, options) {
          var $this = this;
          var fieldsetId = 'fieldsets' + i + '.';
          var name = item['name'];
          var $ctrlFieldset = jQuery('<section class="'+options.fieldsetClass+' panel panel-default col-lg-6" style="padding-left:0; padding-right:0; margin:5px; width:48%;"></section>').hide();
              $ctrlFieldset.attr('id','fieldsetPanel_'+i);
          var $fieldsetHeading = jQuery('<header class="panel-heading bg-light fieldsetHeading"></header>');
              $fieldsetHeading.append('\
                    <ul class="nav nav-tabs pull-right">\
                        <li class="active"><a data-toggle="tab" href="javascript:void(0);"><i class="fa fa-tasks text-muted"></i> '+AI.translate('forms', 'fields_form')+'</a></li>\
                        <li><a data-toggle="tab" class="fieldsetEdit" href="'+AI.navHashCreate('forms', 'formBuilder')+'"><i class="fa fa-cog text-muted"></i></a></li>\
                    </ul>\
                    <span class="hidden-sm heading">' + name + '</span>')
                .unbind('hover').bind('click', $this._setCurrentFieldset)
                .parent('.' + options.fieldsetClass);

          var $fieldsetBuilderSection = jQuery('<div class="panel-body"></div>');
          var $fieldsetBuilderPanel = jQuery('\
                <div class="tab-content">\
                    <div class="tab-pane active form-horizontal">\
                        <fieldset class="connectedSortable">\
                            <div class="emptyFieldsetPanel dropfile"><small>'+AI.translate('forms', 'empty-fieldset')+'</small></div>\
                        </fieldset>\
                    </div>\
                    <div class="tab-pane">settings</div>\
                </div>');
              $fieldsetBuilderPanel.find('.emptyFieldsetPanel').attr('id','emptyBuilderPanel_'+i);

          $fieldsetBuilderSection.append($fieldsetBuilderPanel);
          $ctrlFieldset.append($fieldsetHeading);
          $ctrlFieldset.append($fieldsetBuilderSection);

          var $fieldsetProperties = jQuery('\
            <input type="hidden" id="' + fieldsetId + 'id" name="' + fieldsetId + 'id" value="null" /> \
            <input type="hidden" id="' + fieldsetId + 'basic" name="' + fieldsetId + 'basic" value="null" />  \
            <input type="hidden" id="' + fieldsetId + 'Name" name="' + fieldsetId + 'Name" value="' + name + '" /> \
            <input type="hidden" id="' + fieldsetId + 'type" name="' + fieldsetId + 'type" value="' + item['type'] + '" /> \
            <input type="hidden" id="' + fieldsetId + 'sequence" name="' + fieldsetId + 'sequence" value="' + i + '" /> \
            <input type="hidden" id="' + fieldsetId + 'settings" name="' + fieldsetId + 'settings" value="" /> \
          ');

          $ctrlFieldset.append($fieldsetProperties);
          $ctrlFieldset.find('input[id$="'+ fieldsetId +'settings"]').val(jQuery.toJSON(item));

              if(typeof item.id == 'number') {
                  $ctrlFieldset.find('input[id$="'+ fieldsetId +'id"]').val(item.id);
              }

              if(typeof item.basic == 'string') {
                  $ctrlFieldset.find('input[id$="'+ fieldsetId +'basic"]').val(item.basic);
              } else if(typeof item.id == 'number' || typeof item.id == 'string') {
                  $ctrlFieldset.find('input[id$="'+ fieldsetId +'basic"]').val(item.id);
              }

          jQuery('a.fieldsetEdit', $ctrlFieldset).unbind('click').bind('click', $this._createFieldsetSettings);

            if(i == 0) {
                $ctrlFieldset.addClass('current');
            }

            if(typeof item['fields'] != 'undefined') {
                var $fbWidget = jQuery.fb.fbWidget.prototype;
                var counter = jQuery('#builderForm div.ctrlHolder').size();
                    Object.each(item['fields'], function(field, key){
                        $fieldsetBuilderPanel.find('fieldset.connectedSortable').append($fbWidget._getFbWidget(field, counter));
                        counter++;
                    });
                //*** Скрываем текст "empty-fieldset" в слоте (_FIELDSET)
                $fieldsetBuilderPanel.find('.emptyFieldsetPanel').hide();
            }

          return $ctrlFieldset;
  },

  _setCurrentFieldset: function(event, $widget) {
      if (!$widget) { // calling from click event
          $widget = jQuery(this);
        }

        $widget = $widget.parent();

        var fbOptions = jQuery.fb.formbuilder.prototype.fbOptions;
        var selectedClass = fbOptions.fieldsetSelectedClass;
        jQuery(fbOptions._builderForm + ' .' + fbOptions.fieldsetClass + '.' + selectedClass).removeClass(selectedClass);

        var fieldsetCurrentID = '#' + $widget.addClass(selectedClass).attr('id');
        jQuery.fb.formbuilder.prototype.fbOptions._builderFieldsetCurrent = fieldsetCurrentID;
        jQuery.fb.formbuilder.prototype.fbOptions._formControlCurrent = fieldsetCurrentID + ' fieldset';

        if (event.type == 'click') {
            event.preventDefault(); event.stopPropagation();
          // activate add field settings tab
          jQuery(fbOptions._paletteTabs).tabs('select', 0);
        }
  },

  _createFieldsetSettings: function(event, $widget) {
      if (!$widget) { // calling from click event
          $widget = jQuery(this);
        }

        //Open Window
        var sEditorWindow = AI.dhxWins.createWindow("fieldsetSettingsPanel", 20, 10, 400, 160, 1);
            sEditorWindow.setModal(true);
            sEditorWindow.setText(AI.translate('forms', 'tunes'));
            sEditorWindow.attachEvent("onHide", function(win){win.close();});
            sEditorWindow.attachHTMLString('\
                <section class="panel panel-default">\
                    <div id="fieldsetSettings" class="settingsBlock panel-body">\
                        <div class="general" role="form"></div>\
                    </div>\
                </section>');
            sEditorWindow.button('park').hide();
            sEditorWindow.centerOnScreen();

        var $closeButton = jQuery('<a href="#" class="btn btn-sm btn-default green-button">'+AI.translate('forms', 'save_and_close')+'</a>')
                                .bind('click', function(e){
                                    e.preventDefault(); e.stopPropagation();
                                    sEditorWindow.close();
                                    this.unbind('click');
                                });

        var $currentTarget = $widget;
        var $fbWidget = jQuery.fb.fbWidget.prototype;
        var fbOptions = jQuery.fb.formbuilder.prototype.fbOptions;
        var $builderForm = jQuery(fbOptions._builderForm);

        var $fieldsetSettingsGeneralSection = jQuery(fbOptions._fieldsetSettingsGeneralSection);

        $widget = $widget.parents('.'+ fbOptions.fieldsetClass);
        var index = $('section.fieldsetPanel').index($widget);  //Пока сделаем так
        var settingsFieldset = jQuery.parseJSON(unescape($widget.find("input[id$='fieldsets" + index + ".settings']").val()));
        var settingsForm = jQuery.parseJSON(unescape(jQuery('#settings', $builderForm).val()));
        var $name = $fbWidget._label({ label: AI.translate('forms','Name'), name: 'fieldset.name' })
                .append('<input type="text" class="form-control inp" id="fieldset.name" value="'+ settingsFieldset.name +'" />');
        jQuery('input', $name).keyup(function(event){
                var value = jQuery(this).val();
                var name = $fbWidget._propertyName(value);
                    $widget.find("input[id$='fieldsets" + index + ".Name']").val(name).change();
                    settingsFieldset.name = value;
                    jQuery('.fieldsetHeading', $widget).find('.heading').remove();
                    jQuery('.fieldsetHeading', $widget).append('<span class="hidden-sm heading">' + value + '</span>');
                    jQuery('a.fieldsetEdit', $widget).unbind('click').bind('click', jQuery.fb.formbuilder.prototype._createFieldsetSettings);
                    $widget.find("input[id$='fieldsets" + index + ".settings']").val(jQuery.toJSON(settingsFieldset)).change();
        });


        if (fbOptions.readOnly) {
          var $fieldSettingsPanel = jQuery(fbOptions._fieldsetSettingsPanel);
          jQuery('input', $fieldSettingsPanel).attr("disabled", true);
          jQuery('select', $fieldSettingsPanel).attr("disabled", true);
          jQuery('textarea', $fieldSettingsPanel).attr("disabled", true);
        }

        // remote all child nodes except legend
        $fieldsetSettingsGeneralSection.children(':not(legend)').remove();
        $fieldsetSettingsGeneralSection.append($fbWidget._oneColumn($name));

        if (event.type == 'click') {
            event.preventDefault(); event.stopPropagation();
          // highlight and select the 1st input component
          jQuery('input:first', $fieldSettingsPanel).select();
        }

        jQuery(fbOptions._fieldsetSettingsPanel).append($closeButton);
  },

  _updateSettings: function($this) {
      jQuery('#settings').val(jQuery.toJSON($this.fbOptions.settings)).change();
  },

  _initWidgetsEventBinder: function() { // for widgets loaded from server
      var $ctrlHolders = jQuery('.' + jQuery.fb.fbWidget.prototype.fbOptions._styleClass);
      var size = $ctrlHolders.size();
        if (size > 0) {
            var $this, widget;
            var fieldsUpdateStatus = ['Name', 'settings', 'sequence'];

            //jQuery(this.fbOptions._emptyBuilderPanel + ':visible').hide();
            $ctrlHolders.each(function(i) {
                $this = jQuery(this);
                widget = $this.find("input[id$='fields" + i + ".type']").val();
                $this.click(jQuery['fb']['fb' + widget].prototype._createFieldSettings);
                    for (var j = 0; j < fieldsUpdateStatus.length; j++) {
                        $this.find("input[id$='fields" + i + "." + fieldsUpdateStatus[j] + "']")
                                          .change(jQuery.fb.fbWidget.prototype._updateStatus);
                    }
            });
            if (!this.fbOptions.readOnly) {
              $ctrlHolders.find(".closeButton").click(jQuery.fb.fbWidget.prototype._deleteWidget);
            }
        }
  },

  _initSortableWidgets: function() {
      var $formControls = jQuery(this.fbOptions._formControls);
      $formControls.sortable({
            //axis: 'y',
            //containment: 'parent',
            cursor: 'move',
            distance: 10,
            helper: function (event, ui) {
                return jQuery(ui).clone().css(jQuery.fb.formbuilder.prototype.fbOptions._dragBoxCss);
            },
            start: function (event, ui) {
                var $previousElement = jQuery(ui.item).prev();
                if ($previousElement.attr('rel')) {
                    ui.item.prevIndex = $previousElement.attr('rel');
                    ui.item.originalOffsetTop = $previousElement.offset().top;
                }
            },
            stop: function(event, ui) {
                //*** Проверяем fieldsets на наличие полей, если пусто, то показываем текст "empty-fieldset" в пустом слоте (_FIELDSET)
                $formControls.each(function(i){
                    var childs = jQuery(this).children(':visible');
                        if(childs.length == 0){
                            jQuery(this).find('.emptyFieldsetPanel').show();
                        } else if(childs.length > 0){
                            jQuery(this).find('.emptyFieldsetPanel').hide();
                        }
                });
            },
            update: jQuery.fb.formbuilder.prototype._updateSequence,
            connectWith: '.connectedSortable'
            });

        // Making text elements, or elements that contain text, not text-selectable.
      $formControls.disableSelection();
  },

  _updateSequence: function (event, ui) {
        var $uiItem = jQuery(ui.item);
        var $elements;
        var moveDown = ui.offset.top > ui.item.originalOffsetTop;
        if (ui.item.prevIndex) {
            var prevElementSelector = '[rel="' + ui.item.prevIndex + '"]';
            if (moveDown) {
            $elements = $uiItem.prevUntil(prevElementSelector);
            jQuery.fb.formbuilder.prototype._moveDown($uiItem, $elements);
            } else {
                // set next widget's sequence as my sequence
                jQuery.fb.formbuilder.prototype._getSequence($uiItem).val(
                    jQuery.fb.formbuilder.prototype._getSequence($uiItem.next()).val()).change();
                $elements = $uiItem.nextUntil(prevElementSelector);
                $elements.each(function(index) {
                  jQuery.fb.formbuilder.prototype._increaseSequence(jQuery(this));
                 });
                // process the last one
                jQuery.fb.formbuilder.prototype._increaseSequence(jQuery(prevElementSelector));
            }
        } else {
            $elements = $uiItem.prevAll();
            jQuery.fb.formbuilder.prototype._moveDown($uiItem, $elements);
        }
  },

  _init: function() {
      // called on construction and re-initialization
        this.method1('calling from FormBuilder._init');
  },

  destroy: function() {
    // called on removal
    // call the base destroy function.
        jQuery.Widget.prototype.destroy.call(this);
  },

  _moveDown: function($widget, $elements) {
        // set previous widget's sequence as my sequence
        jQuery.fb.formbuilder.prototype._getSequence($widget).val(
            jQuery.fb.formbuilder.prototype._getSequence($widget.prev()).val()).change();
      $elements.each(function(index) {
          jQuery.fb.formbuilder.prototype._decreaseSequence(jQuery(this));
        });
  },

  _getSequence: function($widget) {
        return $widget.find("input[id$='fields" + $widget.attr('rel') + ".sequence']");
  },

  _increaseSequence: function($widget) {
      if ($widget.is(":visible")) {
          var $sequence = jQuery.fb.formbuilder.prototype._getSequence($widget);
          $sequence.val($sequence.val() * 1 + 1);
          $sequence.change();
        }
  },

  _decreaseSequence: function($widget) {
      if ($widget.is(":visible")) {
        var $sequence = jQuery.fb.formbuilder.prototype._getSequence($widget);
        $sequence.val($sequence.val() - 1);
        $sequence.change();
      }
  },

  method1: function(params) {
        // plugin specific method
  }
};

jQuery.widget('fb.formbuilder', FormBuilder);
