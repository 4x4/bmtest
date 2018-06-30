_SCHEMEGROUP = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_SCHEMEGROUP', autoCreateMethods: true});

    },

    create: function (data) {

        this.context.tabs.addTab({
            id: 'tcreateschemegroup',
            name: AI.translate('xpe', 'add_personalization_group'),
            temporal: true,
            active: true
        }, true);

        this.parent(data);
        xoad.html.importForm('create_SCHEMEGROUP', this.context.connector.result.data);
        this.context.schemesItems = {};
        this.context.SCHEMEITEM.initSchemeElementList()
    },

    saveEdited: function (e) {
        e.preventDefault();

        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm('edit_SCHEMEGROUP');

            if(jQuery.isEmptyObject(this.context.schemesItems))
            {
                $.growler.error({message: AI.translate('xpe','scheme-items-are-empty'),
                    duration: 4500,
                    opacity: 1
                });
                return;
            }

            this.context.execute({
                onSaveEdited_SCHEMEGROUP: {
                    schemeItems: this.context.schemesItems,
                    data: data,
                    id: this.selectedId
                }
            });

            if ($(e.target).hasClass('saveback')) {
                this.context.navigate('showPersonalizationSchema');
            }
        }

    },
    save: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('create_SCHEMEGROUP');

            if(jQuery.isEmptyObject(this.context.schemesItems))
            {
                $.growler.error({message: AI.translate('xpe','scheme-items-are-empty'),
                    duration: 4500,
                    opacity: 1
                });
                return;
            }

            this.context.execute({
                onSave_SCHEMEGROUP: {
                    schemeItems: this.context.schemesItems,
                    data: data
                }
            });

            if (this.context.connector.result.onSave_SCHEMEGROUP) {
                this.context.navigate('showPersonalizationSchema');
            }
        }

    },

    edit: function (params) {

        this.parent(params);
        this.context.schemesItems = {};
        this.context.tabs.addTab({
            id: 'teditschemegroup',
            name: AI.translate('xpe', 'edit_SCHEMEGROUP'),
            temporal: true,
            active: true
        }, true);

        this.context.execute({onEdit_SCHEMEGROUP: {id: params.id}});

        this.context.mainViewPortFind('.saveback').click(this.saveEdited.bind(this));
        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);

        this.context.SCHEMEITEM.initSchemeElementList();

        if (typeof this.context.connector.result != 'undefined') {

            $(this.context.connector.result.schemesItems).each(function (i, k) {

                this.context.schemesItems[i] = k;

            }.bind(this));
        }


        this.context.SCHEMEITEM.renderTypeList();

    }

});


_SCHEMEITEM = new Class({
    Extends: CRUN,

    initialize: function (context) {
        this.parent(context, {
            objType: '_SCHEMEITEM',
            autoCreateMethods: true
        });

        $(document).on('click', '#' + this.context.name + ' .addSchemaElement', [], this.create.bind(this));

    },

    create: function () {


        this.createSchemeItemWindow(this.options.objType);

        this.form = jQuery("#edit" + this.options.objType);
        this.form.validationEngine();

        this.context.connector.execute({onCreate_SCHEMEITEM: true});

        this.form.find('select[name=Type]').change(this.onTypeChangeSFE.bind(this));
        this.form.find('select[name=Options]').change(this.onOptionsChangeSFE.bind(this));
        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);

        $(this.schemeItemEditorContext).find('.save').click(this.save.bind(this));
    },

    edit: function (data) {

        data = {id: data};

        TH.getTpl(this.context.name, this.options.objType + '@edit');
        this.createSchemeItemWindow(this.options.objType + '@edit');

        pdata = this.context.schemesItems[data.id];

        this.context.connector.execute({
            onEdit_SCHEMEITEM: true
        });

        this.form = jQuery("#edit" + this.options.objType);
        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);


        this.typeChangeSFE(pdata.params.Type);
        if(typeof pdata.params.Options!='undefined') {
            this.OptionsChangeSFE(pdata.params.Options);
        }
        xoad.html.importForm("edit" + this.options.objType, pdata.params);


        this.id = data.id;
        this.form.find('select[name=Type]').change(this.onTypeChangeSFE.bind(this));
        this.form.find('select[name=Options]').change(this.onOptionsChangeSFE.bind(this));
        $(this.schemeItemEditorContext).find('#edit' + this.options.objType).validationEngine();
        $(this.schemeItemEditorContext).find('.save').click(this.saveEdited.bind(this));

    },

    save: function (e) {
        e.preventDefault();
        this.validated = $(this.schemeItemEditorContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_SCHEMEITEM');
            id = '0' + generateGUID();

            if (data.Type != 'pipelineField') {
                delete data.Options;
            }

            this.context.schemesItems[id] = {
                isNew: true,
                id: id,
                params: data
            };

            this.renderTypeList();
            this.schemeItemEditorWin.close();

        }
    },

    saveEdited: function (e) {


        e.preventDefault();
        this.validated = $(this.searchElementEditorContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_SCHEMEITEM');

            this.context.schemesItems[this.id] = ({
                isNew: this.context.schemesItems[this.id].isNew,
                id: this.id,
                params: data
            });

            this.renderTypeList();
            this.schemeItemEditorWin.close();

        }

    },

    typeChangeSFE:function(type)
    {



        if (type == 'pipelineField') {
            $(this.schemeItemEditorContext).find('.schemeOptions').show();

        } else {
            $(this.schemeItemEditorContext).find('.schemeOptions').hide();
        }

    },

    onTypeChangeSFE: function (e) {

            this.typeChangeSFE($(e.target).val())
    },


    OptionsChangeSFE:function(option)
    {

        this.context.connector.execute({
            optionChangeSFE: {option:option}
        });

        $(this.schemeItemEditorContext).find('select[name=OptionsData]').children().remove();


        if(this.context.connector.result.data)
        {
            $(this.schemeItemEditorContext).find('.schemeOptionsData').show();
            xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);
        }else{
            $(this.schemeItemEditorContext).find('.schemeOptionsData').hide();
        }


    },



    onOptionsChangeSFE: function (e) {

        this.OptionsChangeSFE($(e.target).val())
    },

    renderTypeList: function () {
        var dataset = [];

        Object.each(this.context.schemesItems, function (val, id) {
            vp = val.params;

            dataset[id] = {
                data: [id, vp.Alias, vp.Name, this.context.translate(vp.Type), this.context.translate(vp.Options)]
            }

        }.bind(this));
        this.gridlist.clearAll();
        this.gridlist.parse({
            rows: dataset
        }, "xjson");
    },


    createSchemeItemWindow: function (tpl) {
        this.schemeItemEditorWin = AI.dhxWins.createWindow("schemeItemEditor", 20, 10, 600, 670, 1);
        this.schemeItemEditorWin.setModal(true);
        this.schemeItemEditorWin.setText(AI.translate('xpe', 'add_scheme_item'));
        this.schemeItemEditorWin.attachEvent("onHide", function (win) {
            win.close();
        });

        this.schemeItemEditorWin.attachHTMLString(TH.getTpl(this.context.name, this.options.objType));
        this.schemeItemEditorWin.button('park').hide();
        this.schemeItemEditorWin.centerOnScreen();
        this.schemeItemEditorContext = this.schemeItemEditorWin.dhxcont;

    },

    copySchemeItem: function (kid, id) {
        newid = '0' + generateGUID();
        newObj = Object.clone(this.context.schemesItems[id]);
        newObj.id = newid;
        newObj.isNew = true;
        newObj.basic = this.context.schemesItems[id].basic + '_copy';
        this.context.schemesItems[newid] = newObj;

        this.renderTypeList();
    },

    deleteSchemeItem: function (id) {

        if (selected = this.gridlist.getSelectedId()) {
            selected = selected.split(',');
        } else {
            return;
        }
        if (selected.length > 1) {
            result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_objects'));
        } else {
            result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_object'));
        }

        for (i in selected) {
            kid = selected[i];
            delete this.context.schemesItems[kid];

        }

        this.gridlist.deleteSelectedRows();

    },


    SchemeItemListDragger: function () {
        var rowsAll = this.gridlist.getAllRowIds().split(',');
        var tempProperties = {};
        Array.each(rowsAll, function (val) {
            tempProperties[val] = this.context.schemesItems[val];

        }.bind(this));

        this.context.schemesItems = tempProperties;

    },

    initSchemeElementList: function (id) {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteSchemeItem.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copySchemeItem.bind(this));


        this.gridlist = new dhtmlXGridObject('SchemeItemList');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('common', 'name') + ',' + AI.translate('common', 'type') + ',' + AI.translate('common', 'options'));
        this.gridlist.setInitWidths("70,300,220,180,180");

        this.gridlist.setColAlign("center,left,left,left,left");
        this.gridlist.setColTypes("ro,ro,ro,ro,ro");
        this.gridlist.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.attachEvent("onDrop", this.SchemeItemListDragger.bind(this));
        this.gridlist.init();
        this.gridlist.setSkin("modern");

    }

});


_XPEROLE = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_XPEROLE', autoCreateMethods: true});

    },


    checkDuplicateRoles:function(field, rules, i, options)
    {

            window.module.execute({
            checkDuplicateRoles: {
                field:field.val(),
                currentId: window.module.XPEROLE.selectedId
            }
        });


        if( window.module.connector.result.isDuplicate)return AI.translate('xpe','role-is-duplicate');

    },

    initConditionsList: function (data) {

        var filtersItems = [];
        this.conditionsList = $('#conditionList');

        filtersItems = data.items;
        optGroups = data.optgroups;

        var options = {
            optgroups: optGroups,
            plugins: {
                //   'bt-tooltip-errors': { delay: 100 },
                // 'sortable': null,
                'filter-description': {mode: 'bootbox'},
                // 'bt-selectpicker': null,
                //  'unique-filter': null,
                // 'bt-checkbox': { color: 'primary' },
                // 'invert': null,
                // 'not-group': null
            },


            // standard operators in custom optgroups
            operators: [
                {type: 'equal', optgroup: 'basic'},
                {type: 'not_equal', optgroup: 'basic'},
                {type: 'in', optgroup: 'basic'},
                {type: 'not_in', optgroup: 'basic'},
                {type: 'less', optgroup: 'numbers'},
                {type: 'greater', optgroup: 'numbers'},
                {type: 'between', optgroup: 'numbers'},
                {type: 'contains', optgroup: 'strings'},
                {type: 'is_empty', optgroup: 'strings'},
                {type: 'is_not_empty', optgroup: 'strings'}
            ],

            filters: filtersItems
        };

        this.conditionsList.queryBuilder(options);

        if (typeof data.data != 'undefined') {
            if (data.data.conditions != '') {
                conditions = JSON.parse(data.data.conditions);
                this.conditionsList.queryBuilder('setRules', conditions);
            }
        }

    },

    create: function (data) {

        this.context.tabs.addTab({
            id: 'tcreatexperole',
            name: AI.translate('xpe', 'add_role'),
            temporal: true,
            active: true
        }, true);

        this.parent(data);
        this.context.affectors = [];
        this.selectedId=data.id;

        xoad.html.importForm('create_XPEROLE', this.context.connector.result.data);
        this.initConditionsList(this.context.connector.result);
        this.context.AFFECTOR.initAffectorsList();

        this.context.mainViewPortFind('.saveback').click(this.save.bind(this));
        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
    },

    saveEdited: function (e) {

        e.preventDefault();
        this.parent();

        qvalid=this.conditionsList.queryBuilder('validate');

        if(!qvalid)return;

        var rules = this.conditionsList.queryBuilder('getRules',
            {
                get_flags: true,
                skip_empty: true
            });


        if (this.validated) {
            data = xoad.html.exportForm('edit_XPEROLE');
            data['conditions'] = rules;
            affectors = this.context.affectors;

            if(Object.keys(affectors).length==0)
            {
                $.growler.error({message: AI.translate('xpe','affectors-are-empty'),
                    duration: 4500,
                    opacity: 1
                });
                this.context.mainViewPortFind("button[data-target='affectors']").click();
                return;
            }


            this.context.execute({
                onSaveEdited_XPEROLE: {
                    data: data,
                    affectors: affectors,
                    id: this.selectedId
                }
            });

            if ($(e.target).hasClass('saveback')) {
                this.context.navigate('showXpeRoles');
            }
        }

    },
    save: function (e) {

        e.preventDefault();
        this.parent();
        qvalid=this.conditionsList.queryBuilder('validate');
        if(!qvalid)return;

        var rules = this.conditionsList.queryBuilder('getRules',
            {
                get_flags: true,
                skip_empty: true
            });


        if (this.validated) {
            data = xoad.html.exportForm('create_XPEROLE');
            data['conditions'] = rules;
            affectors = this.context.affectors;

            if(Object.keys(affectors).length==0)
            {
                $.growler.error({message: AI.translate('xpe','affectors-are-empty'),
                    duration: 4500,
                    opacity: 1
                });

                this.context.mainViewPortFind("button[data-target='affectors']").click();

                return;
            }

            this.context.execute({
                onSave_XPEROLE: {
                    parent:this.selectedId,
                    data: data,
                    affectors: affectors

                }
            });

            if (this.context.connector.result.onSave_XPEROLE) {
                this.context.navigate('showXpeRoles');
            }
        }

    },

    edit: function (params) {

        this.parent(params);


        this.context.tabs.addTab({
            id: 'teditroles',
            name: AI.translate('xpe', 'edit_XPEROLE'),
            temporal: true,
            active: true
        }, true);

        this.context.execute({onEdit_XPEROLE: {id: params.id}});

        this.context.affectors = [];

        if (typeof this.context.connector.result.affectors != 'undefined') {
            this.context.affectors = this.context.connector.result.affectors;
        }

        this.context.mainViewPortFind('.saveback').click(this.saveEdited.bind(this));

        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);

        this.initConditionsList(this.context.connector.result);

        this.context.AFFECTOR.initAffectorsList();
        this.context.AFFECTOR.renderTypeList();


        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();

    }

});


_AFFECTOR = new Class({
    Extends: CRUN,

    initialize: function (context) {
        this.parent(context, {
            objType: '_AFFECTOR',
            autoCreateMethods: true
        });

        $(document).on('click', '#' + this.context.name + ' .add-affector', [], this.create.bind(this));

    },

    create: function (e) {

        e.preventDefault();


        this.createAffectorWindow(this.options.objType);
        this.form = jQuery("#edit" + this.options.objType);
        this.form.validationEngine();

        this.context.connector.execute({onCreate_AFFECTOR: true});

        this.form.find('select[name=affector]').change(this.onAffectorChangeEvent.bind(this));

        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);

        $(this.affectorEditorContext).find('.save').click(this.save.bind(this));


    },

    edit: function (data) {

        data = {id: data};

        TH.getTpl(this.context.name, this.options.objType + '@edit');
        this.createAffectorWindow(this.options.objType + '@edit');


        pdata = this.context.affectors[data.id];

        this.context.connector.execute({
            onEdit_AFFECTOR: true
        });

        this.form = jQuery("#edit" + this.options.objType);
        this.form.validationEngine();

        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);
        this.id = data.id;
        xoad.html.importForm("edit" + this.options.objType, pdata.params);

        this.form.find('select[name=affector]').change(this.onAffectorChangeEvent.bind(this));
        this.onAffectorChange(pdata.params.affector);

        xoad.html.importForm("edit" + this.options.objType, pdata.params);

        this.context.connector.execute({
            onEdit_AFFECTOR_after: pdata.params
        });

        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.affectorParams);

        $(this.affectorEditorContext).find('#edit' + this.options.objType).validationEngine();
        $(this.affectorEditorContext).find('.save').click(this.saveEdited.bind(this));


    },

    save: function (e) {
        e.preventDefault();
        this.validated = $(this.affectorEditorContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_AFFECTOR');
            id = '0' + generateGUID();

            this.context.affectors[id] = {
                isNew: true,
                id: id,
                params: data
            };

            this.renderTypeList();
            this.affectorEditorWin.close();

        }
    },

    saveEdited: function (e) {

        e.preventDefault();
        this.validated = $(this.affectorEditorContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_AFFECTOR');

            this.context.affectors[this.id] = ({
                isNew: this.context.affectors[this.id].isNew,
                id: this.id,
                params: data
            });

            this.renderTypeList();
            this.affectorEditorWin.close();

        }

    },


    onAffectorChangeEvent: function (e) {

        this.onAffectorChange($(e.target).val());
    },

    onAffectorChange: function (val) {

        if (val) {
            $(this.affectorEditorContext).find('#affectorOptions').html(TH.getTpl(this.context.name, val));

        }

    },


    renderTypeList: function () {
        var dataset = [];

        Object.each(this.context.affectors, function (val, id) {
            vp = val.params;

            dataset[id] = {
                data: [id, this.context.translate(vp.affector), vp.value, AI.translate('xpe',vp.affector)]
            }

        }.bind(this));

        this.gridlist.clearAll();
        this.gridlist.parse({
            rows: dataset
        }, "xjson");
    },


    createAffectorWindow: function (tpl) {

        this.affectorEditorWin = AI.dhxWins.createWindow("affectorEditor", 20, 10, 700, 670, 1);
        this.affectorEditorWin.setModal(true);
        this.affectorEditorWin.setText(AI.translate('xpe', 'add_affector'));
        this.affectorEditorWin.attachEvent("onHide", function (win) {
            win.close();
        });

        this.affectorEditorWin.attachHTMLString(TH.getTpl(this.context.name, this.options.objType));
        this.affectorEditorWin.button('park').hide();
        this.affectorEditorWin.centerOnScreen();
        this.affectorEditorContext = this.affectorEditorWin.dhxcont;

    },

    copyAffector: function (kid, id) {
        newid = '0' + generateGUID();
        newObj = Object.clone(this.context.affectors[id]);
        newObj.id = newid;
        newObj.isNew = true;
        newObj.basic = this.context.affectors[id].basic + '_copy';
        this.context.affectors[newid] = newObj;

        this.renderTypeList();
    },

    deleteAffector: function (id) {

        if (selected = this.gridlist.getSelectedId()) {
            selected = selected.split(',');
        } else {
            return;
        }
        if (selected.length > 1) {
            result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_objects'));
        } else {
            result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_object'));
        }

        for (i in selected) {
            kid = selected[i];
            delete this.context.affectors[kid];

        }

        this.gridlist.deleteSelectedRows();

    },

    initAffectorsList: function () {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteAffector.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyAffector.bind(this));

        this.gridlist = new dhtmlXGridObject('affectorsList');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('xpe', 'affector') + ',' + AI.translate('xpe', 'value') + ',' + AI.translate('common', 'options'));

        this.gridlist.setInitWidths("70,300,250,180");

        this.gridlist.setColAlign("center,left,left,left");
        this.gridlist.setColTypes("ro,ro,ro,ro");
        this.gridlist.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.init();
        this.gridlist.setSkin("modern");


    }


});



_CAMPAIGN = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_CAMPAIGN', autoCreateMethods: true});

    },

    create: function (data) {

        this.context.tabs.addTab({
            id: 'tcreateschemegroup',
            name: AI.translate('xpe', 'add_campaign'),
            temporal: true,
            active: true
        }, true);

        this.parent(data);
    },

    saveEdited: function (e) {
        e.preventDefault();

        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm('edit_CAMPAIGN');

            this.context.execute({
                onSaveEdited_CAMPAIGN: {
                    data: data,
                    id: this.selectedId
                }
            });

            if ($(e.target).hasClass('saveback')) {
                this.context.navigate('showXpeRoles');
            }
        }

    },
    save: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('create_CAMPAIGN');

            this.context.execute({
                onSave_CAMPAIGN: {
                    data: data
                }
            });

            if (this.context.connector.result.onSave_CAMPAIGN) {
                this.context.navigate('showXpeRoles');
            }
        }

    },

    edit: function (params) {

        this.parent(params);

        this.context.tabs.addTab({
            id: 'teditschemegroup',
            name: AI.translate('xpe', 'edit_CAMPAIGN'),
            temporal: true,
            active: true
        }, true);

        this.context.execute({onEdit_CAMPAIGN: {id: params.id}});

        this.context.mainViewPortFind('.saveback').click(this.saveEdited.bind(this));
        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);


    }

});

