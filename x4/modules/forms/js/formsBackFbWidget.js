var FbWidget = {
  fbOptions: { // default options. values are stored in widget's prototype
      _styleClass: 'ctrlHolder',
      _selectedClass: 'ctrlHolderSelected'
    },
  // logging to the firebug's console, put in 1 line so it can be removed
    // easily for production
    _create: function() {
    },
  _init: function() {
    this.element.click(this._createFbWidget);
    },
    destroy: function() {
      this.element.button('destroy');
      // call the base destroy function.
        jQuery.Widget.prototype.destroy.call(this);

    },
  _getFbOptions: function() {
      return jQuery.fb.formbuilder.prototype.fbOptions
  },
  _getFbLocalizedSettings: function() {
      return jQuery.fb.formbuilder.prototype.fbOptions.settings;
  },
  /*
  Создаём новое поле
  */
  _createField: function(name, widget, options, settings) {
      var fbOptions = jQuery.fb.formbuilder.prototype.fbOptions;
      var index = (options.index > 0) ? options.index : jQuery('#builderForm div.ctrlHolder').size();
      widget.attr('rel', index);
      widget.append(jQuery.fb.fbWidget.prototype._createFieldProperties(name, options, settings, index));
      jQuery('<a class="ui-corner-all closeButton" href="#"><i class="i i-cross2"></i></a>')
        .prependTo(jQuery('.fieldProperties', widget))
        .click(jQuery.fb.fbWidget.prototype._deleteWidget);
  },
  _propertyName: function (value) {
      return value;
  },
  _deleteWidget: function(event) {
      event.preventDefault(); event.stopPropagation();

      var options = jQuery.fb.fbWidget.prototype.fbOptions;
      var fbOptions = jQuery.fb.formbuilder.prototype.fbOptions;
      var $widget = jQuery(event.target).parents('.'+options._styleClass); //parent().parent();
      var index = $widget.attr('rel');
      var $formControls = jQuery(fbOptions._formControls);

       // new record that not stored in database
     if ($widget.find("input[id$='fields" + index + ".id']").val() == 'null') {
         $widget.remove();
     } else {
         $widget.find("input[id$='fields" + index + ".status']").val('D');
         $widget.hide();
     }

     if ($widget.attr('class').indexOf(options._selectedClass) > -1) {
           // activate Add Field tab
           jQuery(fbOptions._paletteTabs).tabs('select', 0);
     }
      //*** Показываем текст "empty-fieldset" в пустом слоте (_FIELDSET)
      $formControls.each(function(i){
          var childs = jQuery(this).children(':visible');
              if(childs.length == 0){
                  jQuery(this).find('.emptyFieldsetPanel').show();
              }
      });
     event.stopPropagation();
  },
  _createFieldProperties: function(name, options, settings, index) {
        var fieldId = 'fields' + index + '.';
        var $fieldProperties = jQuery('<div class="fieldProperties col-lg-1"> \
        <input type="hidden" id="' + fieldId + 'id" name="' + fieldId + 'id" value="null" /> \
        <input type="hidden" id="' + fieldId + 'ancestor" name="' + fieldId + 'ancestor" value="null" /> \
        <input type="hidden" id="' + fieldId + 'Name" name="' + fieldId + 'Name" value="' + name + '" /> \
        <input type="hidden" id="' + fieldId + 'type" name="' + fieldId + 'type" value="' + options._type + '" /> \
        <input type="hidden" id="' + fieldId + 'settings" name="' + fieldId + 'settings" /> \
        <input type="hidden" id="' + fieldId + 'sequence" name="' + fieldId + 'sequence" value="' + index + '" /> \
        <input type="hidden" id="' + fieldId + 'status" name="' + fieldId + 'status" value="null" /> \
        </div>');
        $fieldProperties.find("input[id$='" + fieldId + "settings']").val(jQuery.toJSON(settings));
        return $fieldProperties;
    },
  _updateStatus: function(event) {
      $widget = jQuery(event.target);
      if ($widget.parent().find('input:first').val() != 'null') {
        $widget.parent().find('input:last').val('U');
        }
  },
  /*
  Добавляем новое поле в слот (_FIELDSET)
  */
  _createFbWidget: function(event) {
        var $this;
        if (this.fbOptions) { // from draggable, event.type == 'mousedown'
            $this = this;
        } else { // from click event
            var type = 'fb' + jQuery(this).attr('id');
            $this = jQuery(this).data(type);
        }

        var custom_name = AI.translate('forms', $this.fbOptions._type);
        // Clone an instance of plugin's option settings.
        // From: http://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-clone-a-javascript-object
        var settings = jQuery.extend(true, {}, $this.fbOptions.settings);
        var counter = $this._getCounter($this);
            if(custom_name) {
                settings[$this.fbOptions._counterField] = custom_name;
            } else {
                settings[$this.fbOptions._counterField] += ' ' + counter;
            }

        var $ctrlHolder = jQuery('<div class="' + $this.fbOptions._styleClass + ' form-group"></div>').hide();
        // store settings to be used in _createFieldSettings() and _languageChange()
        $ctrlHolder.data('fbWidget', settings);
        var fb = {target: $this, item: $ctrlHolder, settings: settings, _settings: settings};
        var $widget = $this._getWidget(event, fb);
        $ctrlHolder.append($widget);
        if (event.type == 'click' || event.type == 'drop') {
            var name = (custom_name) ? $this._propertyName(custom_name) : $this._propertyName($this.fbOptions._type + counter);
            //*** Добавляем событие новому полю - отображение/открытие настроек поля по клику
            $widget.click($this._createFieldSettings);
            //*** Создаём новое поле
            $this._createField(name, $ctrlHolder, $this.fbOptions, settings);
            if (event.type == 'click') {
                //*** Обновляем сортировку для полей
                //*** Так как у нас может быть несколько "_formControls", т.е. слотов (_FIELDSET), то вводим отдельную переменную (свойство) - "_formControlCurrent".
                //*** Которое будет определять текущий слот для вставки нового поля
                jQuery(jQuery.fb.formbuilder.prototype.fbOptions._formControlCurrent).append($ctrlHolder).sortable('refresh');
                //*** Отображаем новое поле пользователю в слоте (_FIELDSET)
                $ctrlHolder.toggle('slide', {direction: 'up'}, 'slow');
                //$this._scroll(event);
                //*** Скрываем текст "Start adding some fields from the menu on the left" в слоте (_FIELDSET)
                jQuery(jQuery.fb.formbuilder.prototype.fbOptions._formControlCurrent).find('.emptyFieldsetPanel').hide();
                event.preventDefault(); event.stopPropagation();
            } else {
                return $ctrlHolder;
            }
        } else {
            return $ctrlHolder.show();
        }
    },
    /*
    Создаём поле из данных загруженных из БД в слот (_FIELDSET)
    */
  _getFbWidget: function(field, index) {
        var type = 'fb' + field.params.type;
        var $this = jQuery('#'+field.params.type).data(type);
            $this.fbOptions.id = field.id;
            $this.fbOptions.index = index;
            $this.fbOptions.Name = field.params.Name;
            $this.fbOptions.settings = field.params.settings;

        var settings = jQuery.extend(true, {}, $this.fbOptions.settings);
        var $ctrlHolder = jQuery('<div class="' + $this.fbOptions._styleClass + ' form-group"></div>').hide();
            $ctrlHolder.data('fbWidget', settings);
        var fb = {target: $this, item: $ctrlHolder, settings: settings, _settings: settings};
        var $widget = $this._getWidget({type:'drop'}, fb);
            $ctrlHolder.append($widget);
        var name = field.params.Name;
        //*** Создаём поле
        $this._createField(name, $ctrlHolder, $this.fbOptions, settings);
        $ctrlHolder.find("input[id$='fields" + index + ".id']").val(field.id);
        $ctrlHolder.find("input[id$='fields" + index + ".ancestor']").val(field.ancestor);
        $ctrlHolder.find("input[id$='fields" + index + ".sequence']").val(field.rate);
        //*** Вставляем поле в текущий слот
        delete $this.fbOptions.id;
        delete $this.fbOptions.index;
        $this.fbOptions.settings.value = '';
        $this.fbOptions.settings.description = '';
        //*** Возвращаем поле для вставки в слот (_FIELDSET)
        return $ctrlHolder.show();
  },
  _scroll: function(event) {
         var $builderPanel = jQuery(jQuery.fb.formbuilder.prototype.fbOptions._builderPanel);
         var minHeight = $builderPanel.css('minHeight');
         var height = $builderPanel.css('height');
         minHeight = minHeight.substring(0, minHeight.lastIndexOf('px')) * 1;
         height = height.substring(0, height.lastIndexOf('px')) * 1;

         if (height > minHeight) {
             var y = height - minHeight;
             //window.scrollTo(0, y);
             // From: http://tympanus.net/codrops/2010/06/02/smooth-vertical-or-horizontal-page-scrolling-with-jquery/
             jQuery('html, body').stop().animate({
                 scrollTop: y
             }, 1500,'easeInOutExpo');
         }
    },
  _createFieldSettings: function(event, $widget) {
      if (!$widget) { // calling from click event
          $widget = jQuery(this);
        }

        //Open Window
        var sEditorWindow = AI.dhxWins.createWindow("fieldSettingsPanel", 20, 10, 800, 550, 1);
            sEditorWindow.setModal(true);
            sEditorWindow.setText(AI.translate('forms', 'tunes'));
            sEditorWindow.attachEvent("onHide", function(win){win.close();});
            sEditorWindow.attachHTMLString(
                '<section class="panel panel-default">'+
                    '<div id="fieldSettings" class="settingsBlock panel-body"><fieldset class="language"><legend></legend></fieldset>'+
                        '<div class="general" role="form"></div>'+
                    '</div>'+
                '</section>');
            sEditorWindow.button('park').hide();
            sEditorWindow.centerOnScreen();

        var $closeButton = jQuery('<br /><br /><a href="#" class="btn btn-sm btn-default green-button">'+AI.translate('forms', 'save_and_close')+'</a>')
                                .bind('click', function(e){
                                    e.preventDefault(); e.stopPropagation();
                                    sEditorWindow.close();
                                    $closeButton.unbind('click');
                                });

        var selectedClass = jQuery.fb.fbWidget.prototype.fbOptions._selectedClass;
        $widget = $widget.attr('class').indexOf(jQuery.fb.fbWidget.prototype.fbOptions._styleClass) > -1 ? $widget : $widget.parent();
        $widget.parent().find('.' + selectedClass).removeClass(selectedClass);
        $widget.addClass(selectedClass);
        var type = $widget.find("input[id$='fields" + $widget.attr('rel') + ".type']").val();
        var $this = jQuery('#' + type).data('fb' + type);
        var fbOptions = $this._getFbOptions();

            if (!$widget.data('fbWidget')) { // widgets loaded from server
                var $settings = $widget.find("input[id$='fields[" + $widget.attr('rel') + "].settings']");
                // settings is JavaScript encoded when return from server-side
                $widget.data('fbWidget', jQuery.parseJSON(unescape($settings.val())));
            }

        var settings = $widget.data('fbWidget');
        var $languageSection = jQuery(fbOptions._fieldSettingsLanguageSection);
        var fbLanguageSection = {target: $this, item: $widget, settings: settings};
        var fieldSettings = $this._getFieldSettingsLanguageSection(event, fbLanguageSection);
        // remote all child nodes except legend
        $languageSection.children(':not(legend)').remove();
            for (var i=0; i<fieldSettings.length; i++) {
                $languageSection.append(fieldSettings[i]);
            }
        var fbGeneralSection = {target: $this, item: $widget, settings: settings};
        fieldSettings = $this._getFieldSettingsGeneralSection(event, fbGeneralSection);
        var $generalSection = jQuery(fbOptions._fieldSettingsGeneralSection);
        // remote all child nodes
        $generalSection.children().remove();
            for (var i=0; i<fieldSettings.length; i++) {
              $generalSection.append(fieldSettings[i]);
            }

            if (fbOptions.readOnly) {
              var $fieldSettingsPanel = jQuery(fbOptions._fieldSettingsPanel);
              jQuery('input', $fieldSettingsPanel).attr("disabled", true);
              jQuery('select', $fieldSettingsPanel).attr("disabled", true);
              jQuery('textarea', $fieldSettingsPanel).attr("disabled", true);
            }

            if (event.type == 'click') {
              // highlight and select the 1st input component
              jQuery('input:first', $fieldSettingsPanel).select();
            }

        jQuery(fbOptions._fieldSettingsPanel).append($closeButton);
    },
    _getCounter: function($this) {
          var $ctrlHolders = jQuery('.' + $this.fbOptions._styleClass + ':visible:not(.' + this._getFbOptions()._draggableClass + ')');
          var counter = 1;
          if ($ctrlHolders.size() > 0) {
                var $ctrlHolder, index, name, widgetCounter = 0;
                var propertyName = $this._propertyName($this.fbOptions._type);
                    $ctrlHolders.each(function(i) {
                        $ctrlHolder = jQuery(this);
                        index = $ctrlHolder.attr('rel');
                        name = $ctrlHolder.find("input[id$='fields" + index + ".Name']").val();
                        if (name.indexOf(propertyName) > -1) {
                            widgetCounter = name.substring(propertyName.length) * 1;
                            if (widgetCounter > counter) {
                                counter = widgetCounter;
                            }
                        }
                    });
                    if (widgetCounter > 0) counter++;
             }
          return counter;
    },
  _updateSettings: function($widget) {
      var settings = $widget.data('fbWidget');
      var $settings = $widget.find("input[id$='fields" + $widget.attr('rel') + ".settings']");
      $settings.val(jQuery.toJSON(settings)).change();
  },
  _updateName: function($widget, value) {
      var fbOptions = this._getFbOptions();
      // disabledNameChange option for edit view.
      var disabledNameChange = fbOptions.disabledNameChange;
      var index = $widget.attr('rel');
      if (disabledNameChange) {
          // disabledNameChange apply for fields loaded from server-side only
          disabledNameChange = $widget.find("input[id$='fields" + index + ".id']").val() != 'null';
      }
      var name = this._propertyName(value);
      $widget.find("input[id$='fields" + index + ".Name']").val(name).change();
    },
  _threeColumns: function($e1, $e2, $e3) {
        return jQuery('<div class="threeCols row"></div>')
              .append($e1.addClass('col1 col-sm-12 col-md-4'))
              .append($e2.addClass('col2 col-sm-12 col-md-4'))
              .append($e3.addClass('col3 col-sm-12 col-md-4'));
       } ,
  _twoColumns: function($e1, $e2) {
      return jQuery('<div class="twoCols row"></div>')
            .append($e1.addClass('labelOnTop col1 noPaddingBottom col-sm-12 col-md-6'))
            .append($e2.addClass('labelOnTop col2 col-sm-12 col-md-6'));
     } ,
  _oneColumn: function($e) {
      return $e.addClass('labelOnTop');
     },
  _help: function(options) {
         var $help;
         if (options.description) {
           $help = jQuery('<span>&nbsp;(<a href="#" title="' + options.description + '">?</a>)</span>');
            var $link = jQuery('a', $help);
         }
         return $help;
     },
  _label: function(options) {
         var $label = jQuery('<div class="form-group"><label for="' + options.Name + '">' + options.label + '</label></div>')
                .append(this._help(options));
         if (!options.nobreak) $label.append('<br />');
         return $label;
     },
  _horizontalAlignment: function(options) {
         var o = jQuery.extend({}, options);
         o.label = o.label ? o.label : AI.translate('forms', 'horizontal_align');
         var $horizontalAlignment = this._label(o)
        .append('<select class="inp"> \
            <option value="leftAlign">'+AI.translate('forms', 'left')+'</option> \
            <option value="centerAlign">'+AI.translate('forms', 'center')+'</option> \
            <option value="rightAlign">'+AI.translate('forms', 'right')+'</option> \
        </select>');
        jQuery('select', $horizontalAlignment).val(o.value).attr('id', o.name);
        return $horizontalAlignment;
     },
  _verticalAlignment: function(options) {
         var o = jQuery.extend({}, options);
         o.label = o.label ? o.label : AI.translate('forms', 'vertical_align');
         var $verticalAlignment = this._label(o)
         .append('<select class="inp"> \
                <option value="topAlign">'+AI.translate('forms', 'top')+'</option> \
                <option value="middleAlign">'+AI.translate('forms', 'middle')+'</option> \
                <option value="bottomAlign">'+AI.translate('forms', 'bottom')+'</option> \
            </select></div>');
        jQuery('select', $verticalAlignment).val(o.value).attr('id', o.name);
        return $verticalAlignment;
     },
  _name: function($widget) {
         var index = $widget.attr('rel');
         var $name = jQuery('<label for="field.name">'+AI.translate('forms', 'Name')+'</label><br/> \
                   <input type="text" class="inp" id="field.name" />');
        jQuery("input[id$='field.name']", $name)
        .val($widget.find("input[id$='fields" + index + ".Name']").val())
        .keyup(function(event) {
          $widget.find("input[id$='fields" + index + ".Name']")
                     .val(jQuery(event.target).val()).change();
        });
         return $name;
     },
  _twoRowsOneRow: function(row1col1, row2col1, row1col2) {
      var $twoRowsOneRow = jQuery('<div class="twoRowsOneRow"> \
                <div class="row1col1"> \
                  <div class="row2col1"> \
                  </div> \
                </div> \
                <div class="row1col2"> \
                </div> \
              </div>');
        jQuery('.row1col1',$twoRowsOneRow).prepend(row1col1);
        jQuery('.row2col1',$twoRowsOneRow).append(row2col1);
        jQuery('.row1col2',$twoRowsOneRow).append(row1col2);
        return $twoRowsOneRow;
  },
  _fieldset: function(options) {
      return jQuery('<fieldset><legend>' + options.text + '</legend></fieldset>');
  },
  _getWidget: function(event, fb) {
   },
  _getFieldSettingsLanguageSection: function(event, fb) {
    },
    _getFieldSettingsGeneralSection: function(event, fb) {
    }
};

jQuery.widget('fb.fbWidget', FbWidget);
