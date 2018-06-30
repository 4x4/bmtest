_CATOBJ = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_CATOBJ',
            autoCreateMethods: true
        });
        
      
        
        context.pushToTreeClickMap(this.options.objType, 'edit_CATOBJ');
    },

    positionHandle: function () {
        this.positionHandler = this.context.mainViewPortFind('#Position').chosen({'width': '98%'});
    },

    positionPush: function (id, name) {
        this.context.mainViewPortFind('#Position').append('<option selected value="' + id + '">' + name + '</option>');
        this.positionHandler.trigger("chosen:updated");
    },

    onDialogObjectClick: function (id) {
        nameArr = this.context.dialogGroupTree.getParentPath(id, 0);
        name = nameArr.join('/');
        this.positionPush(id, name);

    },

    edit: function (data) {
        this.parent(data);



        result = this.context.connector.execute({
            onEdit_CATOBJ: {
                id: data.id
            }
        });
        pset = result.catObjData.params.__PropertySetGroup;
        psetGroup = this.context.propertyViewConstructor.processView(pset, result.catObjData);


        this.context.currentSkuLink = psetGroup.setGroupParams.skuLink;
        xoad.html.importForm(this.form.get(0).id, result.catObjData);
        xoad.html.importForm(this.form.get(0).id, result.catObjData.params);


        

        if (!result.PSG.skuLink) {

            this.context.mainViewPortFind('button[data-target=sku-data]').hide();

        } else {

            this.context.SKUOBJ.gridSkuList = null;
            this.context.SKUOBJ.skuListCurrent = [];
            this.context.SKUOBJ.getPropertySetSKU(result.PSG.skuLink);
            this.context.SKUOBJ.loadSkuList(result.skuData);
        }


        if (result.backConnections) {
            this.context.propertiesHolder['connection'].handler.handleBackConnections(result.backConnections);
        }



        this.positionHandle();
        this.context.tree.loadByPath(result.catObjData.path);
        
        jQuery(window).trigger('catalog.CATOBJ.edit',result.catObjData);
        
        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();

    },

    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            saveObject = {
                catObjData: data
            };

            saveObject.catObjData = this.context.propertyViewConstructor.handleSaveEvent(saveObject.catObjData);

            if (this.context.SKUOBJ.skuListCurrent) {
                saveObject['skuData'] = this.context.SKUOBJ.skuListCurrent;
                saveObject['skuLink'] = this.context.currentSkuLink;
            }

            
            data=jQuery(window).trigger('catalog.CATOBJ.save',saveObject).data();
            
            if(data.preventSave)return;
            
            this.context.connector.execute({
                onSave_CATOBJ: saveObject
            });


            ancestor = data.ancestorId;
            this.context.tree.refreshItem(ancestor);
        }
    },

    saveEdited: function (e) {				
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm("edit" + this.options.objType);
            saveObject = {
                id: this.selectedId,
                catObjData: data
            };

            if (this.context.SKUOBJ.skuListCurrent) {
                saveObject['skuData'] = this.context.SKUOBJ.skuListCurrent;
                saveObject['skuLink'] = this.context.currentSkuLink;
            }


            saveObject.catObjData = this.context.propertyViewConstructor.handleSaveEvent(saveObject.catObjData);
            
            data=jQuery(window).trigger('catalog.CATOBJ.saveEdited',saveObject).data();
            
            if(data.preventSave)return;
            
            this.context.connector.execute({
                onSaveEdited_CATOBJ: saveObject
            });
        }

    },

    create: function () {

        selectedId = this.context.tree.getSelectedRowId();
        parentData = {};

        if (selectedId) {
            objType = this.context.tree.getRowAttribute(selectedId, "obj_type");
            if (['_ROOT'].indexOf(objType) == -1) parentData = {
                id: selectedId
            }
        }

        this.parent(parentData);
        this.context.mainViewPortFind('button[data-target=sku-data]').hide();

        if (parentData.id) {
            this.context.mainViewPortFind('#ancestor').val(this.context.getTreePathAncestor(['_CATOBJ']));
            this.context.mainViewPortFind('#ancestorId').val(selectedId);

        }

        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
        this.context.SKUOBJ.gridSkuList = null;
        this.context.SKUOBJ.skuListCurrent = [];
        xoad.html.importForm('create_CATOBJ', this.data);

    },

});

_CATGROUP = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_CATGROUP',
            autoCreateMethods: true
        });
        context.pushToTreeClickMap(this.options.objType, 'list_CATGROUP');
    },

    create: function () {

        selectedId = this.context.tree.getSelectedRowId();
        parentData = {};

        if (selectedId) {
            objType = this.context.tree.getRowAttribute(selectedId, "obj_type");
            if (['_ROOT'].indexOf(objType) == -1) parentData = {
                id: selectedId
            }
        }

        this.parent(parentData);

        if (parentData.id) {
            this.context.mainViewPortFind('#ancestor').val(this.context.getTreePathAncestor(['_CATOBJ']));
            this.context.mainViewPortFind('#ancestorId').val(selectedId);

        }

        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
        xoad.html.importForm('create_CATGROUP', this.data);

    },
    edit: function (data) {

        this.parent(data);

        result = this.context.connector.execute({
            onEdit_CATGROUP: {
                id: data.id
            }
        });

        pset = result.catGroupData.params.__PropertySetGroup;


        this.context.propertyViewConstructor.processView(pset, result.catGroupData);
        xoad.html.importForm(this.form.get(0).id, result.catGroupData);
        xoad.html.importForm(this.form.get(0).id, result.catGroupData.params);
        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
        result.catGroupData.path.push(data.id);
        this.context.tree.loadByPath(result.catGroupData.path);
        jQuery(window).trigger('catalog.CATGROUP.edit',result.catGroupData);        
        this.context.mainViewPortFind('a.index').click(this.indexingCatalog.bind(this))

    },


    indexingStep: function (result, obj) {


        if (obj) {

            this.currentIndexing = obj.result.indexed;
            this.ready = obj.result.ready;
        }

        if (!this.ready) {
            object = {};
            object['fastIndexing'] = {
                start: this.currentIndexing,
                id: this.selectedId,
                IndexParamsSku: ips,
                IndexParams: is,
                isFullIndex: isFullIndex
            };
            this.context.connector.execute(object, this.indexingStep.bind(this));


        } else {

            $.growler.notice({message: AI.translate('catalog', 'indexation-finished')});

        }

    },

    indexingCatalog: function (e) {
        e.preventDefault();

        this.context.mainViewPortFind('a.index');

        ips = this.context.mainViewPortFind('input[name=IndexParamsSku]').val();
        is = this.context.mainViewPortFind('input[name=IndexParams]').val();
        isFullIndex = this.context.mainViewPortFind('input[name=isFullIndex]').is(":checked");
        params = this.context.execute({getIndexingParams: {id: this.selectedId}});
        this.ready = false;
        this.indexingItemsCount = params.items;
        this.currentIndexing = 0;
        this.indexingStep(null, null);

    },


    save: function (e) {
        e.preventDefault();
        this.parent();


        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            data = this.context.propertyViewConstructor.handleSaveEvent(data);
            
            zdata=jQuery(window).trigger('catalog.CATOBJ.save',data).data();            
            if(zdata.preventSave)return;
             
            this.context.connector.execute({
                onSave_CATGROUP: {
                    catGroupData: data
                }
            });
            this.context.tree.refreshItem(data.ancestorId);
        }
    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm("edit" + this.options.objType);

            data = this.context.propertyViewConstructor.handleSaveEvent(data);
            
            zdata=jQuery(window).trigger('catalog.CATGROUP.saveEdited',data).data();
            if(zdata.preventSave)return;
            
            result = this.context.connector.execute({
                onSaveEdited_CATGROUP: {
                    id: this.selectedId,
                    catGroupData: data
                }
            });

            this.context.tree.refreshItem(result.ancestor);
        }

    }

});

_PROPERTY = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_PROPERTY',
            autoCreateMethods: true
        });

    },

    create: function () {

        TH.getTpl(this.context.name, this.options.objType);
        this.createPropertyEditorWindow(this.options.objType);
        this.form = jQuery("#create" + this.options.objType);
        this.form.validationEngine();
        xoad.html.importForm("create" + this.options.objType, {
            type: this.context.propertiesSelector
        });
        $(this.propertyEditorContext).find('.propertyEditor .save').click(this.save.bind(this));
    },

    edit: function (data) {

        data = {
            id: data
        };
        TH.getTpl(this.context.name, this.options.objType + '@edit');
        this.createPropertyEditorWindow(this.options.objType + '@edit');

        xoad.html.importForm("edit" + this.options.objType, {
            type: this.context.propertiesSelector
        });
        
        pdata = this.context.properties[data.id];
        
        this.setPropertyTypeHtml(pdata.params.type);

           
        this.context.propertiesHolder[pdata.params.type].handler.handleOnPropertyEdit(pdata,this.propertyEditorWin);
        
        xoad.html.importForm("edit" + this.options.objType, pdata.params);

        if (typeof pdata.params.options == 'object') {
            xoad.html.importForm("edit" + this.options.objType, this.transformToLine('options', pdata.params.options));
        }




        xoad.html.importForm("edit" + this.options.objType, {
            basic: pdata.basic
        });

        this.id = data.id;
        this.form = jQuery("#edit" + this.options.objType);



        $(this.propertyEditorContext).find('#edit_PROPERTY').validationEngine();

        $(this.propertyEditorContext).find('.propertyEditor .saveEdited').click(this.saveEdited.bind(this));

    },

    save: function (e) {
        e.preventDefault();
        this.validated = $(this.propertyEditorContext).find("#create" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('create_PROPERTY');
            id = '0' + generateGUID();
            basic = data.basic;
            delete data.basic;
            this.context.properties[id] = ({
                isNew: true,
                basic: basic,
                id: id,
                params: data
            });
            this.renderPropertiesList();
            this.propertyEditorWin.close();

        }
    },

    saveEdited: function (e) {

        e.preventDefault();
        this.validated = $(this.propertyEditorContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_PROPERTY');
            basic = data.basic;

            delete data.basic;
            this.context.properties[this.id] = ({
                isNew: this.context.properties[this.id].isNew,
                basic: basic,
                id: this.id,
                params: data
            });
            this.renderPropertiesList();
            this.propertyEditorWin.close();

        }

    },

    transformToLine: function (prefix, listTransform) {
        transformed = {};
        for (i in listTransform) {
            transformed[prefix + '.' + i] = listTransform[i];
        }

        return transformed;
    },

    onChangePropertyType: function (e) {
        v = $(e.target);
        if (val = v[0].selectedOptions[0].value) {
            this.setPropertyTypeHtml(val);
        }

    },

    setPropertyTypeHtml: function (type) {
        $('.propertyEditor .propertyTypeEditor').html(this.context.propertiesHolder[type].backOptionsTemplate);
        this.context.fireEvent('propertyTypeEditorReady', {
            type: type
        });
    },

    createPropertyEditorWindow: function (tpl) {
        this.propertyEditorWin = AI.dhxWins.createWindow("propertyEditor", 20, 10, 600, 600, 1);
        this.propertyEditorWin.setModal(true);
        this.propertyEditorWin.setText(AI.translate('catalog', 'add_property'));
        this.propertyEditorWin.attachEvent("onHide", function (win) {
            win.close();
        });
        this.propertyEditorWin.attachHTMLString(TH.getTpl('catalog', tpl));
        this.propertyEditorWin.button('park').hide();
        this.propertyEditorWin.centerOnScreen();
        this.propertyEditorContext = this.propertyEditorWin.dhxcont;

    },

    copyProperty: function (kid, id) {
        newid = '0' + generateGUID();
        newObj = Object.clone(this.context.properties[id]);
        newObj.id = newid;
        newObj.isNew = true;
        newObj.basic = this.context.properties[id].basic + '_copy';
        this.context.properties[newid] = newObj;

        this.renderPropertiesList();
    },

    deleteProperty: function (id) {

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
            delete this.context.properties[kid];
            if ((this.propertiesVals) && (typeof this.propertiesVals[kid] == 'object')) delete this.propertiesVals[kid];
        }

        this.gridlist.deleteSelectedRows();

    },

    renderPropertiesList: function () {
        var dataset = [];
        Object.each(this.context.properties, function (val, id) {

            vp = val.params;
            dataset[id] = {
                data: [id, vp.alias, val.basic, AI.translate('catalog', vp.type), vp.isObligatorily, vp.isComparse, 'edit']
            }

        });
        this.gridlist.clearAll();
        this.gridlist.parse({
            rows: dataset
        }, "xjson");
    },

    propertiesListDragger: function () {
        var rowsAll = this.gridlist.getAllRowIds().split(',');
        var tempProperties = {};
        Array.each(rowsAll, function (val) {
            tempProperties[val] = this.context.properties[val];

        }.bind(this));

        this.context.properties = tempProperties;

    },

    initPropertiesList: function (id) {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        // menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteProperty.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyProperty.bind(this));
        menu.addNewChild(menu.topId, 0, 'refresh', AI.translate('common', "refresh"), false, '', '', this.renderPropertiesList.bind(this));

        this.gridlist = new dhtmlXGridObject('properties');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('common', 'name') + ',' + AI.translate('common', 'type') + ',' + AI.translate('catalog', 'isObligatorily') + ',' + AI.translate('catalog', 'isComparse'));
        this.gridlist.setInitWidths("70,190,180,140,110,120");

        this.gridlist.setColAlign("center,left,left,left,center,center");
        this.gridlist.setColTypes("ro,ro,ro,ro,ch,ch");
        this.gridlist.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlist.attachEvent("onCheckbox", this.onPropertiesCheckbox.bind(this));
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.attachEvent("onDrop", this.propertiesListDragger.bind(this));
        this.gridlist.init();
        this.gridlist.setSkin("modern");

    },

    onPropertiesCheckbox: function (id, num, val) {
        val = val ? 1 : 0;
        if (num == 4) this.context.properties[id].params['isObligatorily'] = val;
        if (num == 5) this.context.properties[id].params['isComparse'] = val;

    }

});

_PROPERTYSETGROUP = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_PROPERTYSETGROUP',
            autoCreateMethods: true
        });
    },

    create: function () {
        this.parent();
        xoad.html.importForm(this.form.get(0).id, this.data);
        this.context.initPropertiesGroupsLists();

    },

    edit: function (data) {

        this.parent(data);
        result = this.context.connector.execute({
            onEdit_PROPERTYSETGROUP: {
                id: data.id
            }
        });
        this.context.initPropertiesGroupsLists(data.id);
        data = {
            skuLink: result['propertySetGroupData'].skuLink,
            alias: result['propertySetGroupData'].params.alias,
            basic: result['propertySetGroupData'].basic
            
        };

        if(typeof result['propertySetGroupData'].params.listSequence!='undefined'){          
         
        data['listSequence']=result['propertySetGroupData'].params.listSequence;
        data['itemSequence']=result['propertySetGroupData'].params.itemSequence;
        }
        
        xoad.html.importForm(this.form.get(0).id, data);

    },

    save: function (e) {
        e.preventDefault();

        ids = this.context.exportGridListGroups();
        propertySetGroupData = xoad.html.exportForm(this.form.get(0).id);
        this.context.connector.execute({
            onSave_PROPERTYSETGROUP: {
                ids: ids,
                propertySetGroupData: xoad.html.exportForm(this.form.get(0).id)
            }
        });

        if (this.context.connector.result.onSave_PROPERTYSETGROUP) {
            AI.navigate(AI.navHashCreate(this.context.name, 'propertyGroupsList'));
        }
    },

    saveEdited: function (e) {
        e.preventDefault();

        ids = this.context.exportGridListGroups();
        propertySetGroupData = xoad.html.exportForm(this.form.get(0).id);
        this.context.connector.execute({
            onSaveEdited_PROPERTYSETGROUP: {
                id: this.selectedId,
                ids: ids,
                propertySetGroupData: xoad.html.exportForm(this.form.get(0).id)
            }
        });

    }

});

_PROPERTYSET = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_PROPERTYSET',
            autoCreateMethods: true
        });
        this.context.addInnerRoute('propertySetsList', 'propertySetsList', this);
    },

    create: function () {
        this.parent();
        this.context.PROPERTY.initPropertiesList();
        this.context.properties = {};
        //     this.context.propertiesVals=[];

    },

    edit: function (data) {

        this.parent(data);
        this.context.PROPERTY.initPropertiesList();

        this.context.connector.execute({
            propertiesList: {
                id: data.id
            },
            onEdit_PROPERTYSET: {
                id: data.id
            }
        });
        psetData = this.context.connector.result.propertySetData;

        data = {
            alias: psetData.params.alias,
            basic: psetData.basic,
            isSKU: psetData.params.isSKU
        };

        xoad.html.importForm(this.form.get(0).id, data);
        this.context.properties = nullerizeObject(this.context.connector.result.property);

        //   this.context.propertiesVals= this.context.connector.result.propertyVals;
        this.context.PROPERTY.renderPropertiesList();

    },

    save: function (e) {

        e.preventDefault();
        propertySetData = xoad.html.exportForm(this.form.get(0).id);
        this.context.connector.execute({
            onSave_PROPERTYSET: {
                properties: this.context.properties,
                propertySetData: propertySetData
            }
        });

        if (this.context.connector.result.onSave_PROPERTYSET) {
            AI.navigate(AI.navHashCreate(this.context.name, 'propertySetsList'));
        }

    },

    saveEdited: function (e) {
        e.preventDefault();

        propertySetData = xoad.html.exportForm(this.form.get(0).id);
        this.context.connector.execute({
            onSaveEdited_PROPERTYSET: {
                id: this.selectedId,
                properties: this.context.properties,
                propertySetData: propertySetData
            }
        });

    },

    deletePropertySet: function (kid, id) {
        this.context.deleteObjectGrid(this.gridlist, 'deletePropertySet');
    },
   copyPropertySet: function () {

        this.context.copyObjectToBufferGrid(this.gridlist);

    },

    pastePropertySet: function (id, kid, keys, gc) {
        this.context.pasteObjectGrid(this.gridlist, kid, 'copyPropertySet');
        AI.refreshPage();
    },
    
    propertySetsList: function () {

        this.context.setMainViewPort(this.context.getTpl('_PROPERTYSET_list'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deletePropertySet.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyPropertySet.bind(this));
        menu.addNewChild(menu.topId, 0, 'paste', AI.translate('common', "paste"), false, '', '', this.pastePropertySet.bind(this));
        
        this.gridlist = new dhtmlXGridObject('propertySetsList');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('common', 'name') + ',' + AI.translate('catalog', 'isSKU'));
        this.gridlist.setInitWidths("100,300,180,*");

        this.gridlist.setColAlign("center,left,left");
        this.gridlist.setColTypes("ro,ro,ro,ro");
        this.gridlist.setColSorting("int,str,str,str");
        this.gridlist.attachEvent("onRowDblClicked", function (id) {
            AI.navigate(AI.navHashCreate(this.context.name, 'edit_PROPERTYSET', {
                'id': id
            }));

        }.bind(this));
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        this.context.connector.execute({
            propertySetsList: id
        });

        if (this.context.connector.result.data_set) {
            this.gridlist.parse(this.context.connector.result.data_set, "xjson")
        }

    }

});

_SKUOBJ = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_SKUOBJ'
        });
        this.skuWindowContext = null;
        this.skuListCurrent = [];
        this.propertyViewConstructorSku = new propertyViewConstructor(this.context, true);
        var that = this;


        this.propertyViewConstructorSku.loadPropertySetData = function (psetId) {


            this.sets = that.skuPsetInfo.sets;
            this.setsInfo = that.skuPsetInfo.setsInfo;
            this.activeSet = Object.keys(this.sets)[0];

        }.bind(this.propertyViewConstructorSku);

    },

    getPropertySetSKU: function (psetId) {
        this.context.connector.execute({
            getPropertySetSKU: {
                setId: psetId
            }
        });
        this.skuPsetInfo = this.context.connector.result.psetGroup;
    },

    edit: function (id, kid) {


        this.id = id;
        this.createSkuWindow(TH.getTpl(this.context.name, this.options.objType + '@edit'));
        this.propertyViewConstructorSku.setViewContext(this.skuWindowContext);
        this.form = jQuery("#edit" + this.options.objType);


        this.propertyViewConstructorSku.processView(this.context.currentSkuLink, this.skuListCurrent[id]);
        this.form.validationEngine();


        xoad.html.importForm("edit" + this.options.objType, this.skuListCurrent[id]);
        $(this.skuWindowContext).find('.save').click(this.saveEdited.bind(this));

    },

    save: function (e) {
        e.preventDefault();
        this.parent();


        if (this.validated) {

            data = xoad.html.exportForm("create" + this.options.objType);
            data = this.propertyViewConstructorSku.handleSaveEventSku(data);


            this.skuWindow.close();
            id = '0' + generateGUID();
            this.addToSkuList(id, data);
            this.renderSkuList();
        }
    },

    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {


            data = xoad.html.exportForm("edit" + this.options.objType);

            data = this.propertyViewConstructorSku.handleSaveEventSku(data);


            this.skuListCurrent[this.id] = data;
            this.skuWindow.close();
            this.renderSkuList();

        }

    },

    create: function (e) {


        e.preventDefault();

        this.createSkuWindow(TH.getTpl(this.context.name, this.options.objType));
        this.propertyViewConstructorSku.setViewContext(this.skuWindowContext);
        this.form = jQuery("#create" + this.options.objType);

        this.propertyViewConstructorSku.processView(this.context.currentSkuLink);
        this.form.validationEngine();
        xoad.html.importForm("create" + this.options.objType, {
            type: this.context.propertiesSelector
        });

        $(this.skuWindowContext).find('.save').click(this.save.bind(this));

    },

    createSkuWindow: function (html) {

        this.skuWindow = AI.dhxWins.createWindow("skuWin", 20, 10, 1020, 800, 1);
        this.skuWindow.setModal(true);
        this.skuWindow.setText(AI.translate('catalog', 'sku_window'));
        this.skuWindow.attachEvent("onHide", function (win) {
            win.close();
        });
        this.skuWindow.attachHTMLString(html);
        this.skuWindow.button('park').hide();
        this.skuWindow.centerOnScreen();
        this.skuWindowContext = this.skuWindow.dhxcont;

    },

    getFirst: function (data) {
        var columns;

        for (var prop in data) {
            columns = data[prop];
            break;
        }
        return columns;
    },

    onTreeGridDragSKU: function (idNode, idTo, drop, bex, zer) {

        parentSource = this.context.tree.getParentId(idNode);

        if (bex.dragContext.dropmode != 'child') {
            parentTarget = this.context.tree.getParentId(idTo);

        } else {
            parentTarget = idTo;
        }


        if (parentSource != parentTarget) {
            ancestorChanged = true;
        } else {
            ancestorChanged = false;
        }

        this.context.connector.execute(
            {
                changeAncestorGridSku: {
                    id: idNode,
                    pointNode: idTo,
                    ancestor: parentTarget,
                    ancestorChanged: ancestorChanged,
                    relative: bex.dragContext.dropmode
                }
            });

        if (this.context.connector.result['dragOK']) {
            return true;
        }

        return false;
    },

    addToSkuList: function (id, data) {
        this.skuListCurrent[id] = data;
    },

     loadSkuList: function (skuList) {
        if (skuList) {
		
                for (i in skuList) {
                    if(skuList.hasOwnProperty(i))
                    {
                        this.addToSkuList(i, skuList[i]);
                    }
                }

        }

        this.renderSkuList();
    },

    deleteSkuObj: function () {


        if (selected = this.gridSkuList.getSelectedId()) {
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
            delete this.skuListCurrent[kid];
        }

        this.gridSkuList.deleteSelectedRows();

    },

    copySkuObj: function () {
        if (selected = this.gridSkuList.getSelectedId(true)) {
            this.gridSkuList.selectedBuffer = selected;
        }
    },


    pasteSkuObj: function (gridContext, ancestor) {

        for (i = 0; i < this.gridSkuList.selectedBuffer.length; i++) {
            bid = this.gridSkuList.selectedBuffer[i];
            guid = '0' + generateGUID();

            str = JSON.stringify(this.skuListCurrent[bid]);


            this.skuListCurrent[guid] = JSON.parse(str);


            this.renderSkuList();
        }


    },

    renderSkuList: function () {

        if (!this.gridSkuList) {

            menu = new dhtmlXMenuObject();
            menu.renderAsContextMenu();

            menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteSkuObj.bind(this));
            menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copySkuObj.bind(this));
            menu.addNewChild(menu.topId, 0, 'paste', AI.translate('common', "paste"), false, '', '', this.pasteSkuObj.bind(this));
            menu.addNewChild(menu.topId, 0, 'refresh', AI.translate('common', "refresh"), false, '', '', this.renderSkuList.bind(this));

            this.gridSkuList = new dhtmlXGridObject('skuList');
            this.gridSkuList.selMultiRows = true;
            this.gridSkuList.setMultiLine(true);
            this.gridSkuList.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
            this.gridSkuList.setHeader('id,' + AI.translate('common', 'name'));
            this.gridSkuList.setInitWidths("70,105,85");
            this.gridSkuList.setColAlign("left,left");
            this.gridSkuList.setColTypes("ro,ro");
            this.gridSkuList.attachEvent("onRowDblClicked", this.edit.bind(this));

            this.gridSkuList.enableDragAndDrop(true);
            this.gridSkuList.enableContextMenu(menu);
            this.gridSkuList.init();
            this.gridSkuList.setSkin("modern");
            this.gridSkuList.attachEvent("onDrag", this.onTreeGridDragSKU.bind(this));


            columns = this.getFirst(this.skuPsetInfo.sets);
            this.columnsList = {};

            for (cname in columns) {

                column = columns[cname];

                if (column['params']['isListingReady']) {
                    this.columnsList[cname] = {
                        alias: column.params['alias'],
                        type: column.params['type']
                    }
                }
            }

            this.context.columnBuilder(this.columnsList, this.gridSkuList, 1, 'non');
        }

        this.list_SKUXLS();
    },

    list_SKUXLS: function () {

        var dataset = [];
        Object.each(this.skuListCurrent, function (val, id) {

            data = [id, val.Name];
            Object.each(this.columnsList, function (cval, key) {
                data.push(val[key]);
            });

            dataset[id] = {
                data: data
            }

        }.bind(this));
        this.gridSkuList.clearAll();
        this.gridSkuList.parse({
            rows: dataset
        }, "xjson");

    }


});


_SEARCHFORM = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_SEARCHFORM',
            autoCreateMethods: true
        });
        this.context.addInnerRoute('searchFormList', 'searchFormList', this);
    },

    create: function () {
        this.parent();
        this.context.comparsions = {};
        this.context.SEARCHELEMENT.initComparsionsList();
    },


    copySearchForm: function () {

        this.context.copyObjectToBufferGrid(this.gridlist);

    },

    pasteSearchForm: function (id, kid, keys, gc) {
        this.context.pasteObjectGrid(this.gridlist, kid, 'copySearchForm');
        AI.refreshPage();
    },


    edit: function (data) {

        this.parent(data);

        this.context.SEARCHELEMENT.initComparsionsList();

        this.context.comparsions = {};

        this.context.connector.execute({
            searchElementList: {
                id: data.id
            },
            onEdit_SEARCHFORM: {
                id: data.id
            }
        });


        xoad.html.importForm(this.form.get(0).id, this.context.connector.result.searchFormData.params);

        this.context.comparsions = nullerizeObject(this.context.connector.result.comparsions);

        this.context.SEARCHELEMENT.renderComparsionList();


    },

    save: function (e) {

        e.preventDefault();
        searchFormData = xoad.html.exportForm(this.form.get(0).id);
        this.context.connector.execute({
            onSave_SEARCHFORM: {
                comparsions: this.context.comparsions,
                searchFormData: searchFormData
            }
        });

        if (this.context.connector.result.onSave_SEARCHFORM) {
            AI.navigate(AI.navHashCreate(this.context.name, 'searchFormList'));
        }

    },

    saveEdited: function (e) {
        e.preventDefault();

        searchFormData = xoad.html.exportForm(this.form.get(0).id);


        this.context.connector.execute({
            onSaveEdited_SEARCHFORM: {
                id: this.selectedId,
                comparsions: this.context.comparsions,
                searchFormData: searchFormData
            }
        });

    },

    deleteSearchForms: function (kid, id) {
        this.context.deleteObjectGrid(this.gridlist, 'deleteSearchForms');
    },

    searchFormList: function () {

        this.context.setMainViewPort(this.context.getTpl('_SEARCHFORM_list'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteSearchForms.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copySearchForm.bind(this));
        menu.addNewChild(menu.topId, 0, 'paste', AI.translate('common', "paste"), false, '', '', this.pasteSearchForm.bind(this));

        this.gridlist = new dhtmlXGridObject('searchFormList');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'name'));
        this.gridlist.setInitWidths("100,*");

        this.gridlist.setColAlign("center,left");
        this.gridlist.setColTypes("ro,ro");
        this.gridlist.setColSorting("int,str");
        this.gridlist.attachEvent("onRowDblClicked", function (id) {
            AI.navigate(AI.navHashCreate(this.context.name, 'edit_SEARCHFORM', {
                'id': id
            }));

        }.bind(this));
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        this.context.connector.execute({
            searchFormList: id
        });

        if (this.context.connector.result.data_set) {
            this.gridlist.parse(this.context.connector.result.data_set, "xjson")
        }

    }

});


_SEARCHELEMENT = new Class({
    Extends: CRUN,

    initialize: function (context) {
        this.parent(context, {
            objType: '_SEARCHELEMENT',
            autoCreateMethods: true
        });

    },

    getPropertySetChangeSFE: function (plistId) {
        select = this.form.find('select[name=property]');

        if (plistId != '_main' && plistId != '') {
            this.context.connector.execute({
                propertiesList: {basic: plistId}
            });

            select.children().remove();

            Object.each(this.context.connector.result.property, function (val, id) {
                select.append($("<option></option>").attr("value", val.basic).text(val.params.alias));
            });
        } else if (plistId == '_main') {
            select.children().remove();

            select.append($("<option></option>").attr("value", 'Name').text('Имя объекта каталога'));
        }
    },

    onPropertySetChangeSFE: function (e) {

        this.getPropertySetChangeSFE($(e.target).val());


    },

    create: function () {

        TH.getTpl(this.context.name, this.options.objType);
        this.createSearchElementEditorWindow(this.options.objType);
        this.form = jQuery("#create" + this.options.objType);
        this.form.validationEngine();


        this.context.connector.execute({onCreate_SEARCHELEMENT: true});

        this.form.find('select[name=propertySet]').change(this.onPropertySetChangeSFE.bind(this));

        xoad.html.importForm("create" + this.options.objType, this.context.connector.result.data);

        $(this.searchElementEditorContext).find('.save').click(this.save.bind(this));
    },

    edit: function (data) {

        data = {id: data};

        TH.getTpl(this.context.name, this.options.objType + '@edit');
        this.createSearchElementEditorWindow(this.options.objType + '@edit');

        pdata = this.context.comparsions[data.id];

        this.context.connector.execute({
            onEdit_SEARCHELEMENT: true
        });

        this.form = jQuery("#edit" + this.options.objType);
        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);


        this.getPropertySetChangeSFE(pdata.params.propertySet);
        xoad.html.importForm("edit" + this.options.objType, pdata.params);


        this.id = data.id;
        this.form.find('select[name=propertySet]').change(this.onPropertySetChangeSFE.bind(this));
        $(this.searchElementEditorContext).find('#edit' + this.options.objType).validationEngine();
        $(this.searchElementEditorContext).find('.saveEdited').click(this.saveEdited.bind(this));

    },

    save: function (e) {
        e.preventDefault();
        this.validated = $(this.searchElementEditorContext).find("#create" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('create_SEARCHELEMENT');
            id = '0' + generateGUID();

            this.context.comparsions[id] = ({
                isNew: true,
                id: id,
                params: data
            });

            this.renderComparsionList();
            this.searchElementEditorWin.close();

        }
    },

    saveEdited: function (e) {

        e.preventDefault();
        this.validated = $(this.searchElementEditorContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_SEARCHELEMENT');

            this.context.comparsions[this.id] = ({
                isNew: this.context.comparsions[this.id].isNew,
                id: this.id,
                params: data
            });

            this.renderComparsionList();
            this.searchElementEditorWin.close();

        }

    },

    /*
     transformToLine: function (prefix, listTransform) {
     transformed = {};
     for (i in listTransform) {
     transformed[prefix + '.' + i] = listTransform[i];
     }

     return transformed;
     },

     onChangePropertyType: function (e) {
     v = $(e.target);
     if (val = v[0].selectedOptions[0].value) {
     this.setPropertyTypeHtml(val);
     }

     },

     setPropertyTypeHtml: function (type) {
     $('.propertyEditor .propertyTypeEditor').html(this.context.propertiesHolder[type].backOptionsTemplate);
     this.context.fireEvent('propertyTypeEditorReady', {
     type: type
     });
     },*/

    createSearchElementEditorWindow: function (tpl) {
        this.searchElementEditorWin = AI.dhxWins.createWindow("searchElementEditor", 20, 10, 600, 670, 1);
        this.searchElementEditorWin.setModal(true);
        this.searchElementEditorWin.setText(AI.translate('catalog', 'add_searchform_element'));
        this.searchElementEditorWin.attachEvent("onHide", function (win) {
            win.close();
        });
        this.searchElementEditorWin.attachHTMLString(TH.getTpl('catalog', tpl));
        this.searchElementEditorWin.button('park').hide();
        this.searchElementEditorWin.centerOnScreen();
        this.searchElementEditorContext = this.searchElementEditorWin.dhxcont;

    },

    copyComparsion: function (kid, id) {
        newid = '0' + generateGUID();
        newObj = Object.clone(this.context.comparsions[id]);
        newObj.id = newid;
        newObj.isNew = true;
        newObj.basic = this.context.comparsions[id].basic + '_copy';
        this.context.comparsions[newid] = newObj;

        this.renderComparsionList();
    },

    deleteComparsion: function (id) {

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
            delete this.context.comparsions[kid];

        }

        this.gridlist.deleteSelectedRows();

    },

    renderComparsionList: function () {
        var dataset = [];

        Object.each(this.context.comparsions, function (val, id) {


            vp = val.params;

            dataset[id] = {
                data: [id, vp.alias, vp.propertySet, vp.property, AI.translate('catalog', vp.comparsionType), vp.priority]
            }

        });
        this.gridlist.clearAll();
        this.gridlist.parse({
            rows: dataset
        }, "xjson");
    },

    propertiesListDragger: function () {
        var rowsAll = this.gridlist.getAllRowIds().split(',');
        var tempProperties = {};
        Array.each(rowsAll, function (val) {
            tempProperties[val] = this.context.comparsions[val];

        }.bind(this));

        this.context.comparsions = tempProperties;

    },

    initComparsionsList: function (id) {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        // menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteComparsion.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyComparsion.bind(this));
        menu.addNewChild(menu.topId, 0, 'refresh', AI.translate('common', "refresh"), false, '', '', this.renderComparsionList.bind(this));

        this.gridlist = new dhtmlXGridObject('comparsions');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('catalog', 'property_set') + ',' + AI.translate('common', 'name') + ',' + AI.translate('common', 'type') + ',' + AI.translate('common', 'priority'));
        this.gridlist.setInitWidths("70,300,220,180,140,80");

        this.gridlist.setColAlign("center,left,left,left,left,center");
        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ro");
        this.gridlist.attachEvent("onRowDblClicked", this.edit.bind(this));

        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.attachEvent("onDrop", this.propertiesListDragger.bind(this));
        this.gridlist.init();
        this.gridlist.setSkin("modern");

    }

});


_SEARCHFORMGROUP = new Class({
    Extends: CRUN,

    initialize: function (context) {
        this.parent(context, {
            objType: '_SEARCHFORMGROUP',
            autoCreateMethods: true
        });

    },


    create: function () {

        TH.getTpl(this.context.name, this.options.objType);
        this.createSearchElementGroupEditorWindow(this.options.objType);
        this.form = jQuery("#create" + this.options.objType);
        this.form.validationEngine();
        $(this.searchGroupEditorWinContext).find('.save').click(this.save.bind(this));
    },

    edit: function (data) {

        data = {id: data};

        TH.getTpl(this.context.name, this.options.objType + '@edit');
        this.createSearchElementGroupEditorWindow(this.options.objType + '@edit');

        pdata = this.context.comparsions[data.id];

        this.context.connector.execute({
            onEdit_SEARCHFORMGROUP: true
        });

        this.form = jQuery("#edit" + this.options.objType);
        xoad.html.importForm("edit" + this.options.objType, this.context.connector.result.data);


        this.id = data.id;

        $(this.searchGroupEditorWinContext).find('.saveEdited').click(this.saveEdited.bind(this));

    },

    save: function (e) {
        e.preventDefault();
        this.validated = $(this.searchGroupEditorWinContext).find("#create" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('create_SEARCHFORMGROUP');

            this.context.connector.execute(
                {
                    onSave_SEARCHFORMGROUP: {data: data}
                });

            this.searchElementEditorWin.close();

        }
    },

    saveEdited: function (e) {

        e.preventDefault();
        this.validated = $(this.searchGroupEditorWinContext).find("#edit" + this.options.objType).validationEngine('validate');
        if (this.validated) {
            data = xoad.html.exportForm('edit_SEARCHFORMGROUP');

            this.context.connector.execute(
                {
                    onSaveEdited_SEARCHFORMGROUP: {data: data}
                });

        }

    },


    createSearchElementGroupEditorWindow: function (tpl) {
        this.searchGroupEditorWin = AI.dhxWins.createWindow("searchElementGroupEditor", 20, 10, 600, 200, 1);
        this.searchGroupEditorWin.setModal(true);
        this.searchGroupEditorWin.setText(AI.translate('catalog', 'add_searchformgroup'));
        this.searchGroupEditorWin.attachEvent("onHide", function (win) {
            win.close();
        });
        this.searchGroupEditorWin.attachHTMLString(TH.getTpl('catalog', tpl));
        this.searchGroupEditorWin.button('park').hide();
        this.searchGroupEditorWin.centerOnScreen();
        this.searchGroupEditorWinContext = this.searchGroupEditorWin.dhxcont;

    },

    copySearchGroup: function (kid, id) {

    },

    deleteSearchGroup: function (id) {

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
            delete this.context.comparsions[kid];

        }

        this.gridlist.deleteSelectedRows();

    }


});


_URLTRANSFORM = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_URLTRANSFORM',
            autoCreateMethods: true
        });
        this.context.addInnerRoute('urlTransformList', 'urlTransformList', this);
        this.context.addInnerRoute('testTransform', 'testTransform', this);
    },

    create: function () {
        this.parent();
        xoad.html.importForm("create" + this.options.objType, this.context.connector.result.data);
        this.context.mainViewPortFind('select[name=propertySet]').change(this.onPropertySetChangeSFE.bind(this));

    },

    testTransform: function () {
        this.context.setMainViewPort(this.context.getTpl('_URLTRANSFORM_test'));
        this.context.mainViewPortFind('.transformButton').click(this.testTransformGo.bind(this));
    },


    testTransformGo: function (e) {
        e.preventDefault();
        form = this.context.mainViewPortFind('#testTransform');
        data = xoad.html.exportForm(form.get(0).id);
        this.context.connector.execute({
            testTransformGo: {
                data: data
            }
        });

        form.find('input#outputTransform').val(this.context.connector.result.transformed);

    },


    onPropertySetChangeSFE: function (e) {
        this.getPropertySetChange($(e.target).val());

    },

    getPropertySetChange: function (plistId) {
        select = this.form.find('select[name=property]');
        this.context.connector.execute({
            propertiesList: {basic: plistId}
        });

        select.children().remove();
        Object.each(this.context.connector.result.property, function (val, id) {
            select.append($("<option></option>").attr("value", val.basic).text(val.params.alias + ' (' + val.basic + ')'));
        });

    },


    duplicateTransform: function () {
        this.context.copyObjectToBufferGrid(this.gridlist);
        AI.refreshPage();
    },

    edit: function (data) {

        this.parent(data);
        this.context.mainViewPortFind('select[name=propertySet]').change(this.onPropertySetChangeSFE.bind(this));

        this.context.connector.execute({
            onEdit_URLTRANSFORM: {
                id: data.id
            }
        });

        xoad.html.importForm(this.form.get(0).id, this.context.connector.result.data);
        data = this.context.connector.result.data;
        this.getPropertySetChange(data.propertySet);
        xoad.html.importForm(this.form.get(0).id, {property: data.property});
    },

    save: function (e) {


        e.preventDefault();
        data = xoad.html.exportForm(this.form.get(0).id);
        this.context.connector.execute({
            onSave_URLTRANSFORM: {
                data: data
            }
        });

        if (this.context.connector.result.onSave_URLTRANSFORM) {
            AI.navigate(AI.navHashCreate(this.context.name, 'urlTransformList'));

        }

    },

    saveEdited: function (e) {
        e.preventDefault();

        data = xoad.html.exportForm(this.form.get(0).id);

        this.context.connector.execute({
            onSaveEdited_URLTRANSFORM: {
                data: data,
                id: this.selectedId
            }

        });


    },

    deleteTransform: function (kid, id) {


        selected = this.gridlist.getSelectedRowId(true);
        if (selected.length > 0) {
            cells = [];
            for (i = 0; i < selected.length; i++) {
                cell = this.gridlist.cellById(selected[i], 0);
                cells.push(cell.getValue());
            }

            this.context.execute({deleteTransform: {id: cells}});

        }


        if (this.context.connector.result.deleted) {
            this.gridlist.deleteSelectedRows();
        }


    },

    urlTransformList: function () {

        this.context.setMainViewPort(this.context.getTpl('_URLTRANSFORM_list'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.duplicateTransform.bind(this));
        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteTransform.bind(this));

        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '',
            function (bid, kid) {

                cell = this.gridlist.cellById(kid, 0);

                AI.navigate(AI.navHashCreate(this.context.name, 'edit_URLTRANSFORM', {id: cell.getValue()}));
            }.bind(this)
        );


        this.gridlist = new dhtmlXGridObject('urlTransformList');
        this.gridlist.selMultiRows = true;
        this.gridlist.enableMultiline(true);
        this.gridlist.setImagePath("/_adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('catalog', 'tree') + ',' + AI.translate('catalog', 'comparsion') + ',' + AI.translate('catalog', 'field') + ',' + AI.translate('catalog', 'value') + ',' + AI.translate('common', 'priority'));

        this.gridlist.setInitWidths("80,120,200,160,*,200,20");
        this.gridlist.setColAlign("center,left,left,center,center,left,left");

        this.gridlist.attachEvent("onRowDblClicked", function (kid) {

            cell = this.gridlist.cellById(kid, 0);


            AI.navigate(AI.navHashCreate(this.context.name, 'edit_URLTRANSFORM', {id: cell.getValue()}));

        }.bind(this));


        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ro,ro");

        this.gridlist.enableAutoWidth(true);

        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");
        this.gridlist.onPage = 50;

        this.listUrlTransforms(data.id, data.page);

        var pg = new paginationGrid(this.gridlist, {
            target: this.context.mainViewPortFind('.paginator'),
            pages: this.context.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'urlTransformList', {id: data.id}) //,

        });


    },

    listUrlTransforms: function (id, page) {
        this.context.connector.execute({
            urlTransformTable: {
                id: id,
                page: page,
                onPage: this.gridlist.onPage
            }
        });

        if (this.context.connector.result.data_set) {

            this.gridlist.parse(this.context.connector.result.data_set, "xjson")
        }

    }


});
