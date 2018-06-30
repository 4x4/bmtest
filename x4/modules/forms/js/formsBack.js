formsBack = new Class({
    Extends: _xModuleBack,
    initialize: function(name)
    {
        this.setName(name);
        this.parent();
        this.setLayoutScheme('listView', { rightPanelWidth: '100%' });
        this.objTypeScope = new Array('_FORM');
        this.loadDefaultTpls=['_FORM.html','_INBOX_list.html','formbuilder.html','tunes.html'];
        AI.loadJs('/x4/modules/forms/js/formsBackFormBuilder.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbWidget.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbPlainText.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbSingleLineText.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbTextarea.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbSingleSelect.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbMultipleSelect.js', true);
        AI.loadJs('/x4/modules/forms/js/formsBackFbSingleCheckbox.js', true);
        //AI.loadJs('/x4/modules/forms/js/formsBackFbUploadFile.js', true);
    },

    onHashDispatch: function(e,v)
    {
        this.tabs.makeActive('t'+e); return true;
    },

    CRUN: function()
    {
        _FORM = new Class({
            Extends: CRUN,
            initialize: function(context)
            {
                this.parent(context,{objType:'_FORM',autoCreateMethods:true});
                context.pushToTreeClickMap(this.options.objType,'edit_FORM');
            },

            onTplChange: function(e)
            {
                el = $(e.target);

                this.context.connector.execute({parseTemplate:{Template:el.val()}});

                if(fieldsets = this.context.connector.result.fieldsets) {
                    jQuery.fb.formbuilder.prototype.fbOptions.fieldsets = fieldsets;
                    jQuery.fb.formbuilder.prototype._updateBuilderForm();
                } else {
                    if(jQuery('.fieldsetPanel').length) {
                        jQuery('.fieldsetPanel').remove();
                    }

                    jQuery('#builderForm').find('#dynamicFieldsets').html('\
                        <section class="fieldsetPanel panel-default col-lg-12">\
                            <header class="panel-heading bg-light"><h3>'+AI.translate('forms', 'select_template')+'</h3></header>\
                        </section>');
                }
            },

            create: function(data)
            {
                jQuery.widget('fb.formbuilder', new Function());
                jQuery.widget('fb.formbuilder', FormBuilder);

                this.parent(data);
                this.context.tabs.addTab({
                    id: 'tcreate_FORM',
                    name: AI.translate('forms', 'add-form'),
                    href: AI.navHashCreate('forms', 'create_FORM'),
                    temporal: true,
                    active: true
                }, true);

                jQuery.fb.formbuilder.prototype.fbOptions.settings = {
                    Name:       AI.translate('forms', 'default-form-name'),
                    classes:    ['leftAlign'],
                    heading:    'h3'
                };
                jQuery('#formBuilderContainer').formbuilder();
                xoad.html.importForm('create_FORM', this.context.connector.result.data);
                jQuery("#saveForms").unbind('click').click(this.save.bind(this));
                this.context.viewPort.find('select[name=Template]').change( this.onTplChange.bind(this) );
                jQuery('#create_FORM').validationEngine('attach', {
                    promptPosition:"bottomLeft",
                    scroll:true,
                    validationEventTrigger:'blur keyup'
                });
            },

            edit: function(kid, id)
            {
                if(Number(kid) && typeof Number(kid) == 'number') {
                    id = kid;
                } else if(typeof kid == 'object' && typeof Number(kid.id) == 'number') {
                    id = kid.id;
                } else {
                    alert(AI.translate('forms', 'form_load_error'));
                    return false;
                }

                jQuery.widget('fb.formbuilder', new Function());
                jQuery.widget('fb.formbuilder', FormBuilder);

                this.parent(id);
                this.context.tabs.addTab({
                    id: 'tedit_FORM',
                    name: AI.translate('forms', 'edit-form'),
                    href: AI.navHashCreate('forms', 'edit_FORM'),
                    temporal: true,
                    active: true
                }, true);


                this.context.connector.execute({getForm:{id:id}});

                    if(this.context.connector.result.form)
                    {
                        var form = this.context.connector.result.form.data;
                        var fieldsets = [];
                            if(this.context.connector.result.form.fieldsets != false)
                            {
                                i = 0;
                                Object.each(this.context.connector.result.form.fieldsets, function(item, key){
                                    fieldsets[i] = {id: item.id, basic: item.basic, name: item.params.Name, type: 'FIELDSET', fields: item.fields};
                                    i++;
                                });

                                jQuery.fb.formbuilder.prototype.fbOptions.fieldsets = fieldsets;
                            }

                        this.context.connector.execute({onEdit_FORM:{}});

                        jQuery.fb.formbuilder.prototype.fbOptions.settings = {
                            id:                 form.id,
                            basic:              form.basic,
                            Name:               form.params.Name,
                            classes:            ['leftAlign'],
                            heading:            'h3',
                            comment:            form.params.Description,
                            disable:            form.params.Disable,
                            subject:            form.params.Subject,
                            //Template:           this.context.connector.result.data,
                            email:              form.params.Emails,
                            charset:            form.params.Charset,
                            save_to_server:     form.params.save_to_server,
                            use_captcha:        form.params.use_captcha,
                            captcha_settings:   form.params.captcha_settings,
                            async:              form.params.Async,
                            timeout:            form.params.Timeout,
                            message_after:      form.params.message_after
                        };

                        jQuery('#formBuilderContainer').formbuilder();
                        xoad.html.importForm('edit_FORM', this.context.connector.result.data);
                        jQuery("#saveForms").unbind('click').click(this.save.bind(this));
                        this.context.viewPort.find('select[name=Template]').find("option[value$='" + form.params.Template + "']").attr('selected', 1);
                        this.context.viewPort.find('select[name=Template]').change( this.onTplChange.bind(this) );
                        this.context.viewPort.find('select[name=submitTemplate]').find("option[value$='" + form.params.submitTemplate + "']").attr('selected', 1);
                        //xoad.html.importForm('edit_FORM', data);
                        jQuery('#edit_FORM').validationEngine('attach', {
                            promptPosition:"bottomLeft",
                            scroll:true,
                            validationEventTrigger:'blur keyup'
                        });
                    }
                //jQuery.fb.formbuilder.prototype._updateBuilderForm();
            },

            save: function(e, ui)
            {
                e.preventDefault(); e.stopPropagation();

                var action = (jQuery(e.target).hasClass('edit')) ? 'edit' : 'create';
                var data = (action == 'edit') ? xoad.html.exportForm('edit_FORM') : xoad.html.exportForm('create_FORM');
                    data['form']['Template'] = data.Template;
                    data['form']['submitTemplate'] = data.submitTemplate;
                    data['form']['fieldsets'] = {};
                    delete data['form']['settings'];
                    delete data.Template;
                    delete data.submitTemplate;
                var dynamicFieldsForm = xoad.html.exportForm('builderForm');
                data['form']['fieldsets'] = {};

                jQuery('section.fieldsetPanel').each(function(index, fieldset){
                    var $fieldset = jQuery(fieldset);
                    var fieldsetKey = 'fieldsets'+index;
                        if(dynamicFieldsForm[fieldsetKey]){
                            data['form']['fieldsets'][fieldsetKey] = dynamicFieldsForm['fieldsets'+index];
                            delete data['form']['fieldsets'][fieldsetKey]['settings'];
                            data['form']['fieldsets'][fieldsetKey]['fields'] = {};

                            jQuery($fieldset).find('div.ctrlHolder').each(function(i, field){
                                var $field = jQuery(field);
                                var fieldKey = 'fields'+$field.attr('rel');
                                    if(dynamicFieldsForm[fieldKey]){
                                        data['form']['fieldsets'][fieldsetKey]['fields'][i] = dynamicFieldsForm[fieldKey];
                                        data['form']['fieldsets'][fieldsetKey]['fields'][i]['settings'] = jQuery.parseJSON(unescape($field.find("input[id$='" + fieldKey + ".settings']").val()));
                                    }
                            });
                        }
                });
                delete dynamicFieldsForm;

                var validated = this.context.mainViewPortFind("#"+action+'_FORM').validationEngine('validate');

                if(!validated) {
                    return false;
                } else {
                    //Если форма с настройками скрыта, то предыдущий валидатор возвращает true (не срабатывает)
                    //Перепроверяем обязательные поля
                    validated = this.checkFormSettings(action);

                    if(!validated) {
                        this.context.mainViewPortFind("#"+action+'_FORM').validationEngine('validate');
                        return false;
                    }
                }

                if(action == 'edit') {
                    this.context.connector.execute({ onSaveEdited_FORM: { data: data } });
                } else {
                    this.context.connector.execute({ onSave_FORM: { data: data } });
                }

                    if(Number(this.context.connector.result.save) > 0){
                        alert($.nano(AI.translate('forms', 'form_success_saved'), {name:data['form']['Name']}));
                            if(action == 'edit') {
                                AI.refreshPage();
                            } else {
                                AI.navigate(AI.navHashCreate('forms','edit_FORM',{'id':Number(this.context.connector.result.save)}));
                            }
                    } else {
                        alert($.nano(AI.translate('forms', 'form_success_error'), {name:data['form']['Name']}));
                    }
            },

            switchForm: function(id, cid, state)
            {
                //this.connector.execute({switchForm:{id:id, state:state}});
            },

            checkFormSettings: function(action)
            {
                if(typeof action == 'undefined' || !action) return false;

                var requiredFields = jQuery('#'+action+'_FORM').find('[class*=validate]').not('[type=checkbox]').not('[type=radio]');
                var errorFields = new Array();

                if(requiredFields.length) {
                    requiredFields.each(function(i){
                        if(!jQuery(this).val()) {
                            errorFields.push(jQuery(this));
                        }
                    });

                    if(errorFields.length) {
                        jQuery('#formSettings').slideDown('fast', function(){ jQuery(this).removeClass('nav-xs'); });
                        delete errorFields;
                        return this.context.mainViewPortFind("#"+action+'_FORM').validationEngine('validate');
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        });

        /*_FIELDSET = new Class({
            Extends:CRUN,
            initialize:function(context)
            {
                this.parent(context,{objType:'_FIELDSET',autoCreateMethods:true});
                //context.pushToTreeClickMap(this.options.objType,'edit_FIELDSET');
            },

            edit:function(data)
            {
                this.parent(id);
            }
        });

        _FIELD = new Class({
            Extends:CRUN,
            initialize:function(context)
            {
                this.parent(context,{objType:'_FIELD',autoCreateMethods:true});
                //context.pushToTreeClickMap(this.options.objType,'edit_FIELD');
            },

            edit:function(data)
            {
                this.parent(id);
            }
        });*/

        FORM        = new _FORM(this);
        //FIELDSET    = new _FIELDSET(this);
        //FIELD       = new _FIELD(this);
    },

    tabsStart: function()
    {
        var oTabs = [{
                id: 't_firstpage',
                name: AI.translate('forms', 'forms_list'),
                href: AI.navHashCreate(this.name, 'showFormsList'),
                active: true
            // }, {
                // id: 't_inbox',
                // name: AI.translate('forms', 'incoming_messages'),
                // href: AI.navHashCreate(this.name, 'showFormsInbox')
            }, {
                id: 't_inboxlt',
                name: AI.translate('forms', 'incoming_messages'),
                href: AI.navHashCreate(this.name, 'showFormsMessages')
            }];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    deleteForm: function(kid, id)
    {
        if(Number(kid) && typeof Number(kid) == 'number') { id = kid; }
        if(id)
        {
            this.connector.execute({ delete_FORM:{id: id} });

                if(this.connector.result.del) {
                    this.gridlist.deleteSelectedRows();
                    alert($.nano(AI.translate('forms', 'form_delete'), {name:this.connector.result.del}));
                }
        }
    },

    buildInterface: function()
    {
        this.parent();
        /*--tabs--*/
        this.tabsStart();
        //show forms list
        this.showFormsList();
    },

    showFormsList: function(id)
    {
        /*--menu--*/
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common','edit'), false, '', '', function(bid,kid){
            cell=this.gridlist.cellById(kid,0);
            this.navigate('edit_FORM',{id:cell.getValue()})
        }.bind(this));
        menu.addNewChild(menu.topId, 1, "delete", AI.translate('common','delete'), false, '', '', this.deleteForm.bind(this));

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
            menu.addNewChild(menu.topId, 0, "refresh", 'refresh', false, '', '', this.refreshTree.bind(this));
        }

        jQuery(this.mainViewPort).addClass('grid-view');
        jQuery(this.mainViewPort).html('<div><a class="btn btn-primary m-b-sm" href="'+AI.navHashCreate(this.name, 'create_FORM')+'"> \
                <i class="fa fa-plus text"></i> <span class="text">'+AI.translate('forms', 'add-form')+'</span></a> \
            </div> \
            <div id="formsListContainer" style="width:100%; height:500px;"></div>');

        this.gridlist = new dhtmlXGridObject('formsListContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader(AI.translate('forms', 'id') + ','
                              + AI.translate('forms', 'date-change') + ',' //__nodeChanged
                              + AI.translate('forms', 'form-name') + ',' //Name
                              + AI.translate('forms', 'author') + ','  //Author
                              + AI.translate('forms', 'description') + ',' //Description
                              + AI.translate('forms', 'active'));  //Disable

        this.gridlist.setInitWidths("60,130,*,80,160,60");
        this.gridlist.setColAlign("center,left,left,left,left,center");
        this.gridlist.attachEvent("onCheckbox", FORM.switchForm.bind(FORM));
        this.gridlist.attachEvent("onRowDblClicked", function(id){
            AI.navigate(AI.navHashCreate(this.name,'edit_FORM',{'id':id}));
        }.bind(this));
        //this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ch");
        this.gridlist.setColSorting("int,date_rus,str,str,str");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        this.connector.execute({formsTable: 1});
            if (this.connector.result.data_set){
                this.gridlist.parse(this.connector.result.data_set, "xjson");
                this.gridlist.sortRows(0, 'int', 'des');
            }
    },

    showFormsInbox: function()
    {
        status = 0;
        this.setMainViewPort(this.getTpl('_INBOX_list'));
        this.connector.execute({loadMessages: 1});
            if (this.connector.result.messages){
                var messages = this.connector.result.messages;
                delete this.connector.result.messages;

                    Object.each(messages, function(msg, key){
                        jQuery('#msg_list').append('<li class="list-group-item"> <a id="'+msg['id']+'" rel="'+key+'" href="#" class="msg clear text-ellipsis"> <small class="pull-right">1 hour ago</small> <strong class="block">'+msg['date']+'</strong> <small>'+msg['Name']+'</small> </a></li>');
                    });

                    jQuery('.msg').on('click', function(evn){
                        evn.preventDefault();
                        jQuery('#msg_title').text( messages[jQuery(this).attr('rel')]['Name'] );
                        jQuery('#msg_date').html( messages[jQuery(this).attr('rel')]['date']+' (<em>4 дня назад</em>)' );
                        jQuery('#msg_text').html( messages[jQuery(this).attr('rel')]['message'] );
                    });
            }
    },

    showFormsMessages: function(data)
    {
        this.setGridView('formsInboxContainer', (window.screen.availHeight - 300), true);
        this.tabs.makeActive('t_inboxlt');

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();
        menu.addNewChild(menu.topId, 0, "show_message", AI.translate('forms', 'show_message'), false, '', '', this.showSelectedMessage.bind(this));
        menu.addNewChild(menu.topId, 1, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteMessage.bind(this));

        this.gridlist = new dhtmlXGridObject('formsInboxContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader(AI.translate('forms', 'id')+',' +
                                AI.translate('forms', 'formId')+','+
                                AI.translate('forms', 'form-name')+','+
                                AI.translate('common', 'date')+','+
                                AI.translate('forms', 'message_text')+','+
                                AI.translate('forms', 'read')
        );

        this.gridlist.setMultiLine(true);
        this.gridlist.setInitWidths("100,100,200,160,*,100");
        this.gridlist.setColAlign("center,center,left,left,left,center");
        this.gridlist.attachEvent("onRowDblClicked", this.showSelectedMessage.bind(this));
        this.gridlist.attachEvent("onCheckbox",this.setReadStatus.bind(this));
        this.gridlist.setColTypes("ed,ed,ed,ed,ed,ch");
        this.gridlist.setColSorting("int,int,str,str,str,str");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.customGroupFormat = function(text, count){return text + ", (<b>" + count + "</b>)";};
        this.gridlist.init();
        this.gridlist.setSkin("modern");
        this.gridlist.onPage = 20;

        this.listMessages(data.page);

        this.gridlist.forEachRow(function (kid) {
            val = this.gridlist.cells(kid, 5).getValue();
            if (val == 0) {
                this.gridlist.cells(kid, 0).cell.style = 'background-color:#1ccacc';
                this.gridlist.cells(kid, 1).cell.style = 'background-color:#1ccacc';
            }
        }.bind(this));

        pg = new paginationGrid(this.gridlist, {
            target: this.mainViewPortFind('.paginator'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'showFormsMessages', {})
        });
    },

    listMessages: function(page)
    {
        this.connector.execute({
            messagesTable: {
                page: page,
                onPage: this.gridlist.onPage
            }
        });

        if (this.connector.result.data_set) {
            this.gridlist.parse(this.connector.result.data_set, "xjson");
            this.gridlist.groupBy(2);
        }
    },

    showSelectedMessage:function(kid,cid)
    {
        if(!Number(kid) && kid != 0) {
            kid = cid;
        }

        cell = this.gridlist.cellById(kid, 0);
        this.connector.execute({openSelectedMessage:{id:Number(cell.getValue())}});

            if(typeof this.connector.result.message != 'undefined') {
                //Open Window
                this._win = AI.dhxWins.createWindow("message_window", 20, 10, 800, 550, 1);
                this._win.setModal(true);
                this._win.setText(this.connector.result.message.date +' '+ this.connector.result.message.Name);
                this._win.centerOnScreen();
                this._win.attachHTMLString('<section class="panel panel-default"><div class="panel-body">'+this.connector.result.message.message+'</div></section>');
                this._win.button('park').hide();

                this.setReadStatus(kid,null,true);
            }
    },

    setReadStatus:function(kid, cInd, state) {
        cell = this.gridlist.cellById(kid, 0);

        if(state == true) {
            this.gridlist.cells(kid, 0).cell.style = '';
            this.gridlist.cells(kid, 1).cell.style = '';
        } else {
            this.gridlist.cells(kid, 0).cell.style = 'background-color:#1ccacc';
            this.gridlist.cells(kid, 1).cell.style = 'background-color:#1ccacc';
        }

        state = (state == true) ? 1 :0;

        this.connector.execute({setReadStatus:{id:Number(cell.getValue()), state:state}});

    },

    deleteMessage: function(evt)
    {
        var id = this.gridlist.getSelectedRowId(1);
        var that = this;

        if(id) {
            id.each(function(i,k) {
                cell = that.gridlist.cellById(i, 0);
                id[k]=Number(cell.getValue());
            });

            this.connector.execute({deleteMessage:{id:id}});

            if(this.connector.result.del == true) {
                this.gridlist.deleteSelectedRows();
            }
        }
    },

    dialogGroupTreeDynamicXLS: function (id) {
        this.connector.execute({
            treeDynamicXLS: {
                id: id
            }
        });
        if (this.connector.result) {
            if (id == 0) {
                this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.dialogGroupTree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },

    dialogGroupTreeDynamicFullXLS: function(id)
    {
        this.connector.execute({
            treeDynamicFullXLS: {
                id: id
            }
        });

        if (this.connector.result) {
            if (id == 0) {
                this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.dialogGroupTree.json_dataset = this.connector.result.data_set;
            }
        }

        return true;
    },

    onDialogObjectClick: function(id)
    {
        nameArr = this.dialogGroupTree.getParentPath(id, 0);
        name = nameArr.join('/');
        objTypesToSelect = this.dialogContext.info.split(',');
        objType = this.dialogGroupTree.getRowAttribute(id, "obj_type");

            if (objTypesToSelect.indexOf(objType) != -1) {
                this.dialogContext.returnData({
                    id: id,
                    name: name
                });
            }
    },

    onDialogGroupFull: function(dialogContext)
    {
        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('forms', 'forms-groups'));
        this.dialogGroupTree.setInitWidths("*");
        this.dialogGroupTree.setColAlign("left");
        this.dialogGroupTree.setColTypes("tree");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS",this.dialogGroupTreeDynamicFullXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        $(this.dialogGroupTree.entBox).find('.ev_dhx_skyblue').hide();
        this.dialogGroupTree.openItem(1);
    },

    onActionRender_showForms:function(context,data)
    {
        context.container.find('select[name=Template]').chosen();
    }
});



//jquery.json-2.2.min.js
(function($){$.toJSON=function(o)
{if(typeof(JSON)=='object'&&JSON.stringify)
return JSON.stringify(o);var type=typeof(o);if(o===null)
return"null";if(type=="undefined")
return undefined;if(type=="number"||type=="boolean")
return o+"";if(type=="string")
return $.quoteString(o);if(type=='object')
{if(typeof o.toJSON=="function")
return $.toJSON(o.toJSON());if(o.constructor===Date)
{var month=o.getUTCMonth()+1;if(month<10)month='0'+month;var day=o.getUTCDate();if(day<10)day='0'+day;var year=o.getUTCFullYear();var hours=o.getUTCHours();if(hours<10)hours='0'+hours;var minutes=o.getUTCMinutes();if(minutes<10)minutes='0'+minutes;var seconds=o.getUTCSeconds();if(seconds<10)seconds='0'+seconds;var milli=o.getUTCMilliseconds();if(milli<100)milli='0'+milli;if(milli<10)milli='0'+milli;return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
if(o.constructor===Array)
{var ret=[];for(var i=0;i<o.length;i++)
ret.push($.toJSON(o[i])||"null");return"["+ret.join(",")+"]";}
var pairs=[];for(var k in o){var name;var type=typeof k;if(type=="number")
name='"'+k+'"';else if(type=="string")
name=$.quoteString(k);else
continue;if(typeof o[k]=="function")
continue;var val=$.toJSON(o[k]);pairs.push(name+":"+val);}
return"{"+pairs.join(", ")+"}";}};$.evalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);return eval("("+src+")");};$.secureEvalJSON=function(src)
{if(typeof(JSON)=='object'&&JSON.parse)
return JSON.parse(src);var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,'@');filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']');filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered))
return eval("("+src+")");else
throw new SyntaxError("Error parsing JSON, source is not valid.");};$.quoteString=function(string)
{if(string.match(_escapeable))
{return'"'+string.replace(_escapeable,function(a)
{var c=_meta[a];if(typeof c==='string')return c;c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+string+'"';};var _escapeable=/["\\\x00-\x1f\x7f-\x9f]/g;var _meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};})(jQuery);
