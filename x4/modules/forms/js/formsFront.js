moduleRegister.pushModule('forms');

var formsFront = new Class(
    {
        Extends: x4FrontModule,
        preventJquery: false,

        constructor: function() {
            this.parent('forms');
        },

        updateCaptcha:function(fid,captcha_inp) {
            if(typeof captcha_inp == 'undefined') return false;
            if(!fid && !captcha_inp) return false;
            if(!captcha_inp.length) return false;

            var captcha_img = jQuery('#'+jQuery.trim(captcha_inp.attr('id'))+'_img');

            if(!captcha_img.length) return false;

            var tmp = new Date();
            tmp = tmp.getTime();

            captcha_inp.val('');
            captcha_img.attr('src','/captcha.php?fid='+fid+'&rand='+tmp);
        },

        checkCaptcha:function(field, rules, i, options) {
            if(field.val() == '' && field.attr('maxlength') == field.val().length) {
                return options.allrules.validate2captcha.alertText;
            } else if(field.attr('maxlength') > field.val().length) {
                return options.allrules.minSize.alertText+field.attr('maxlength')+options.allrules.minSize.alertText2;
            }

            var formsId = jQuery.trim(field.attr('id'));
                formsId = formsId.substr(String('captcha_').length,formsId.length);

            if(!formsId) return false;

            var id_num = Number(formsId.substr(String('form').length,formsId.length));

            this.connector.execute({checkCaptcha:{form:{'id':id_num}, params:{'captcha':field.val()} }});

            if (this.connector.result.captcha == false) {
                this.updateCaptcha(formsId, field);
                return options.allrules.validate2captcha.alertText;
            }
        },

        jqueryRun:function() {
            if (this.preventJquery) return;

            var forms = this;

            jQuery.fn.updateFormCaptcha = function(options) {
                var defaults = {};
                var options = $.extend(defaults, options);

                    if(typeof options.formId == 'undefined') {
                        return false;
                    }

                jQuery(this).on('click', function(e) {
                    e.preventDefault();
                    forms.updateCaptcha('form'+options.formId, jQuery('#captcha_form'+options.formId));
                });
            }

            jQuery.fn.submitForm = function(options) {
                var defaults = {
                    validateOptions:{
                        promptPosition:"centerRight",
                        scroll:true,
                        validationEventTrigger:'blur keyup'
                    }
                };
                var options = $.extend(defaults, options);

                    if(typeof options.formId == 'undefined') {
                        return false;
                    }

                var validateForm = jQuery('#'+options.formId).validationEngine('attach', options.validateOptions);

                    if(typeof options.async != 'undefined' && Number(options.async) == 1) {
                        jQuery(this).on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            var formValid = validateForm.validationEngine('validate');

                            if (!formValid) {
                                return false;
                            }

                            var formData = xoad.html.exportForm(options.formId);

                            forms.connector.execute({sendFormData:{formId:options.formId, formData:formData}});

                            if(typeof forms.connector.result.success != 'undefined') {
                                alert(forms.connector.result.success);
                                jQuery('#'+options.formId)[0].reset();
                                forms.updateCaptcha('form'+options.formId, jQuery('#captcha_form'+options.formId));
                            } else if(typeof forms.connector.result.error == 'string') {
                                jQuery('.errors'+options.formId).html(forms.connector.result.error);
                            } else if(typeof forms.connector.result.error == 'number') {
                                switch (forms.connector.result.error) {
                                    case 1:
                                        jQuery('.errors'+options.formId).html('Ошибка! Отсутствуют данные для отправки.');
                                        break;
                                    case 2:
                                        jQuery('.errors'+options.formId).html('Ошибка! Отсутствует или не подключён шаблон отправки сообщений. Сообщите об ошибке администрации сайта.');
                                        break;
                                    default:
                                        jQuery('.errors'+options.formId).html('Произошла непридвиденная ошибка при отправке сообщения. Попробуйте отправить сообщение еще раз.');
                                        break;
                                }
                            }

                            if(typeof forms.connector.result.error != 'undefined') {
                                forms.updateCaptcha('form'+options.formId, jQuery('#captcha_form'+options.formId));
                            }
                        });
                    } else {
                        jQuery(this).on('click', function(e) {
                            var formValid = validateForm.validationEngine('validate');

                            if (!formValid) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }

                            jQuery(options.formId).on('submit');
                        });
                    }
            }
        }

    });
