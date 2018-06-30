catalogBack = new Class({
    Extends: _xModuleBack,
    properties: null,
    propertiesVals: null,
    propertiesSelector: [],
    propertiesHolder: null,

    initialize: function (name) {
        this.setName(name);
        this.parent();
        this.onPageCatListDefault = 20;
        this.objTypeScope = ['_CATOBJ', '_CATGROUP'];
        this.loadDefaultTpls = ['_PROPERTYSET_list', 'propertiesHolder', 'customCatalogTree'];


        this.setLayoutScheme('treeView', {
            treeSize: 'mxl',
            rightPanelWidth: '890px',
            customTreeHtml: this.getTpl('customCatalogTree')
        });

        AI.loadJs('/x4/modules/catalog/js/catalogBackProperties.js', true);
        AI.loadJs('/x4/modules/catalog/js/catalogBackObjects.js', true);        

        
        this.loadPropertiesData();
        this.addEvent('propertyTypeEditorReady', this.callForOptionsTypeHandler.bind(this));


    },

    callForOptionsTypeHandler: function (data) {
        ph = this.propertiesHolder[data.type];
        if (typeof ph.handler == 'object') {
            if (typeof ph.handler.handleOnCreate == 'function') {
                this.propertiesHolder[data.type].handler.handleOnCreate(data);
            }
        }
    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },

    onDialogObjectClick: function (id) {
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
    
    dialogObjectTreeDynamicXLS: function (id) {
        
        
        this.connector.execute({
            treeDynamicXLSPosition: {
                id: id,
                getObjects: true
                
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

    dialogGroupTreeDynamicXLS: function (id) {
        
        
        this.connector.execute({
            treeDynamicXLSPosition: {
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
    
    onDialogObject: function (dialogContext) {    
        
        
        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
        this.dialogGroupTree.setInitWidths("330,*");
        this.dialogGroupTree.setColAlign("left,left");
        this.dialogGroupTree.setColTypes("tree,ro");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogObjectTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        this.dialogGroupTree.openItem(1);
    },



    onDialogGroup: function (dialogContext) {
        
        
        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
        this.dialogGroupTree.setInitWidths("330,*");
        this.dialogGroupTree.setColAlign("left,left");
        this.dialogGroupTree.setColTypes("tree,ro");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        this.dialogGroupTree.openItem(1);
    },

    onDialogGroupPosition: function (dialogContext) {
        
        
        
        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
        this.dialogGroupTree.setInitWidths("330,*");
        this.dialogGroupTree.setColAlign("left,left");
        this.dialogGroupTree.setColTypes("tree,ro");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.CATOBJ.onDialogObjectClick.bind(this.CATOBJ));
        this.dialogGroupTreeDynamicXLS(0);
        this.dialogGroupTree.openItem(1);
    },

    onPropertySetGroupChange: function (e) {

        psetGroup = this.propertyViewConstructor.processView(e.target.value);
        this.currentSkuLink = psetGroup.setGroupParams.skuLink;

        if (psetGroup.setGroupParams.skuLink) {
            this.mainViewPortFind('button[data-target=sku-data]').show();
            this.SKUOBJ.getPropertySetSKU(this.currentSkuLink);
        } else {
            this.mainViewPortFind('button[data-target=sku-data]').hide();
        }
    },

    CRUN: function () {
        //classes defined in catalogExtended.js
        this.CATGROUP = new _CATGROUP(this);
        this.CATOBJ = new _CATOBJ(this);
        this.PROPERTYSET = new _PROPERTYSET(this);
        this.PROPERTY = new _PROPERTY(this);
        this.PROPERTYSETGROUP = new _PROPERTYSETGROUP(this);
        this.SKUOBJ = new _SKUOBJ(this);
        this.SEARCHFORM = new _SEARCHFORM(this);
        this.SEARCHFORMGROUP = new _SEARCHFORMGROUP(this);
        this.SEARCHELEMENT = new _SEARCHELEMENT(this);
        this.URLTRANSFORM = new _URLTRANSFORM(this);

    },

    loadPropertiesData: function () {
        if (!this.propertiesHolder) {
            this.connector.execute({
                getPropertiesData: true
            });
            this.propertiesHolder = this.connector.result.propertiesData;
            this.propertiesSelector.push({
                value: "",
                text: ""
            });
            for (i in this.propertiesHolder) {
                this.propertiesHolder[i].handler = AI.factor(i + 'Property', this, true);
                this.propertiesSelector.push({
                    value: i,
                    text: AI.translate(this.name, i)
                });
            }
        }

    },

    filterIndexes:function(data)
    {
        this.connector.execute({
            filterIndexes: true
        });


    },

    exportGridListGroups: function () {
        var ids = {};
        for (var i = 0; i < this.gridlistGroups.getRowsNum(); i++) {
            id = this.gridlistGroups.getRowId(i);
            ids[id] = id;
        }
        return ids;
    },

    initPropertiesGroupsLists: function (id) {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        }

        this.gridlistGroups = new dhtmlXGridObject('propertyLinkList');
        this.gridlistGroups.selMultiRows = true;
        this.gridlistGroups.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistGroups.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('common', 'name'));
        this.gridlistGroups.setInitWidths("70,150,*");
        this.gridlistGroups.setColAlign("center,left,left");
        this.gridlistGroups.setColTypes("ro,ro,ro");
        this.gridlistGroups.setColSorting("int,str,str");
        this.gridlistGroups.enableAutoWidth(true);
        this.gridlistGroups.enableDragAndDrop(true);

        this.gridlistGroups.enableContextMenu(menu);
        this.gridlistGroups.init();
        this.gridlistGroups.setSkin("modern");
        this.connector.execute({
            propertyLinksList: {
                id: id
            }
        });

        if (this.connector.result.propertyLinksList) {

            this.connector.result.propertyLinksList.data_set.rows = nullerizeObject(this.connector.result.propertyLinksList.data_set.rows);
            this.gridlistGroups.parse(this.connector.result.propertyLinksList.data_set, "xjson")
        }

        this.gridlist = new dhtmlXGridObject('propertySetsList');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('common', 'name'));
        this.gridlist.setInitWidths("70,150,*");
        this.gridlist.setColAlign("center,left,left");
        this.gridlist.setColTypes("ro,ro,ro");
        this.gridlist.setColSorting("int,str,str");
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.enableAutoWidth(true);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        if (this.connector.result.propertySetsList) {

            this.connector.result.propertySetsList.data_set.rows = nullerizeObject(this.connector.result.propertySetsList.data_set.rows);
            this.gridlist.parse(this.connector.result.propertySetsList.data_set, "xjson")
        }

    },

    deletePropertySetGroup: function (kid, id) {
        this.deleteObjectGrid(this.gridlistGroup, 'deletePropertySetGroup');
    },

    deleteCatObj: function (kid, id) {
        this.deleteObjectGrid(this.gridCatGroup, 'deleteCatObj');
        this.refreshCatObj();

    },

    deleteCatGroup: function (kid, id) {
        this.deleteObjectGrid(this.tree, 'deleteCatObj');

    },

    propertyGroupsList: function () {

        this.setMainViewPort(this.getTpl('_PROPERTYGROUP_list'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deletePropertySetGroup.bind(this));

        this.gridlistGroup = new dhtmlXGridObject('propertyGroupList');
        this.gridlistGroup.selMultiRows = true;
        this.gridlistGroup.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistGroup.setHeader('id,' + AI.translate('common', 'alias') + ',' + AI.translate('common', 'name'));
        this.gridlistGroup.setInitWidths("100,300,*");

        this.gridlistGroup.setColAlign("center,left,left");
        this.gridlistGroup.setColTypes("ro,ro,ro");
        this.gridlistGroup.setColSorting("int,str,str");
        this.gridlistGroup.attachEvent("onRowDblClicked", function (id) {
            AI.navigate(AI.navHashCreate(this.name, 'edit_PROPERTYSETGROUP', {
                'id': id
            }));

        }.bind(this));
        this.gridlistGroup.enableAutoWidth(true);
        this.gridlistGroup.enableContextMenu(menu);
        this.gridlistGroup.init();
        this.gridlistGroup.setSkin("modern");

        this.connector.execute({
            propertyGroupsList: id
        });

        if (this.connector.result.data_set) {
            this.gridlistGroup.parse(this.connector.result.data_set, "xjson")
        }

    },

    tabsStart: function () {
        var oTabs = [{
            id: 't_firstpage',
            name: AI.translate('common', 'info'),
            temporal: true,
            active: true
        } 

        ];
        
        
        
        
        
        if(this.getPermission('createNewObject')) 
        {
            oTabs.push(
            {
                    id: 'tcreate_OBJ',
                    name: AI.translate('common', 'add'),
                    subTabs: [
                        {
                            id: 'tcreate_CATOBJ',
                            name: AI.translate('catalog', 'add_object'),
                            href: AI.navHashCreate(this.name, 'create_CATOBJ'),
                        },

                        {
                            id: 'tcreate_CATGROUP',
                            name: AI.translate('catalog', 'add_category'),
                            href: AI.navHashCreate(this.name, 'create_CATGROUP')
                        }

                    ]
            }
        );
        
        }
        
          
        if(this.getPermission('editProperties')) 
        {
            oTabs.push(
            {
                id: 'tproperties_OBJ',
                name: this.translate('properties'),
                subTabs: [
                    {
                        id: 'tpropertyGroupsList',
                        name: AI.translate('catalog', 'propertysets_groups'),
                        href: AI.navHashCreate(this.name, 'propertyGroupsList'),
                        routes: ['edit_PROPERTYSETGROUP', 'create_PROPERTYSETGROUP']
                    },


                    {
                        id: 'tpropertySetsList',
                        name: AI.translate('catalog', 'propertysets_list'),
                        href: AI.navHashCreate(this.name, 'propertySetsList'),
                        routes: ['edit_PROPERTYSET', 'create_PROPERTYSET']
                    }

                ]


            });
        
        }
        
        
        
        
        if(this.getPermission('editSearchForms')) 
        {
            oTabs.push(
            {
                id: 'tsearchFormList',
                name: AI.translate('catalog', 'search_forms'),
                href: AI.navHashCreate(this.name, 'searchFormList'),
                routes: ['edit_SEARCHFORM', 'create_SEARCHFORM']
            });
        
        }
        
        
           
        if(this.getPermission('urlTransformationEdit')) 
        {
            oTabs.push(
              {
                id: 'tUrlTransformerList',
                name: AI.translate('catalog', 'urlTransformer'),
                href: AI.navHashCreate(this.name, 'urlTransformList'),
                routes: ['edit_URLTRANSFORM', 'create_URLTRANSFORM']
            }
            );        
        }

        

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },


    copyCatObjTree: function (id, kid) {

        this.copyObjectToBufferGrid(this.tree);

    },

    copyCatObj: function (id, kid, src, tree) {

        this.copyObjectToBufferGrid(this.gridCatGroup);

    },

    pasteCatObj: function (id, kid, keys, gc) {
        this.pasteObjectGrid(this.gridCatGroup, this.gridCatGroupSelectedId, 'copyCatObj');
        this.refreshCatObj();
    },

    pasteCatObjTree: function (id, kid) {

        this.pasteObjectGrid(this.tree, kid, 'copyCatObj');
        this.refreshCatObj();
        this.tree.refreshItem(kid);
    },

    pasteSkuMove: function () {

        nodes = this.skuSelectedBuffer;
        parentTarget = this.currentSkuListIdGroup;
        this.connector.execute(
            {
                changeAncestorGridSku: {
                    id: nodes,
                    ancestor: parentTarget,
                    relative: 'child'
                }
            });

    },


    copySkuObjMove: function (id, kid, src, tree) {
        if (selected = this.gridSkuGroup.getSelectedId(true)) {

            this.skuSelectedBuffer = selected;
        }

    },

    deleteSkuObj:function()
    {
        this.deleteObjectGrid(this.gridSkuGroup, 'deleteSku');
    },

    gotoObject:function(id, kid, src, tree){

        this.connector.execute({
           getObjectBySku: {
                id: id,
                kid: kid
            }
        });

        AI.navigate(AI.navHashCreate(this.name, 'edit_CATOBJ', {
            'id': this.connector.result.netid
        }));

    },

    list_SKUGROUP: function (data) {

        if (this.mainViewPortFind('#skuGroupList').length == 0) {

            this.tabs.addTab({
                id: 'list_SKUGROUP',
                name: AI.translate('catalog', 'list_SKUGROUP'),
                href: '#',
                temporal: true,
                active: true
            }, true);

            this.setMainViewPort(this.getTpl('_SKUGROUP_list'));

            menu = new dhtmlXMenuObject();
            menu.renderAsContextMenu();

            if (__globalLogLevel == 9) {
                menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

            }

            menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteSkuObj.bind(this));
            menu.addNewChild(menu.topId, 0, 'refresh', AI.translate('common', "refresh"), false, '', '', this.refreshCatObj.bind(this));
            menu.addNewChild(menu.topId, 0, 'copySkuObjMove', AI.translate('catalog', "copySkuObjMove"), false, '', '', this.copySkuObjMove.bind(this));
            menu.addNewChild(menu.topId, 0, 'pasteSkuMove', AI.translate('catalog', "pasteSkuMove"), false, '', '', this.pasteSkuMove.bind(this));
            menu.addNewChild(menu.topId, 0, 'gotoObject', AI.translate('catalog', "gotoObject"), false, '', '', this.gotoObject.bind(this));


            this.gridSkuGroup = new dhtmlXGridObject('skuGroupList');
            this.gridSkuGroup.selMultiRows = true;
            this.gridSkuGroup.setMultiLine(true);
            this.gridSkuGroup.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
            this.gridSkuGroup.setHeader(' ,id,' + AI.translate('common', 'name'));
            this.gridSkuGroup.setInitWidths("35,85,160");
            this.gridSkuGroup.iconURL = "/x4/adm/xres/ximg/green/";
            this.gridSkuGroup.setColAlign("left,left,left");
            this.gridSkuGroup.setColTypes("img,ro,ro");
            /*            this.gridCatGroup.attachEvent("onRowDblClicked", function (id) {
             AI.navigate(AI.navHashCreate(this.name, 'edit_CATOBJ', {
             'id': id
             }));

             }.bind(this));
             */
            this.gridSkuGroup.enableDragAndDrop(true);
            this.gridSkuGroup.enableContextMenu(menu);
            this.gridSkuGroup.init();
            this.gridSkuGroup.setSkin("modern");


        } else {

            this.gridSkuGroup.clearAll();
            this.gridSkuGroup._cMod = null;
            c = this.gridSkuGroup.getColumnsNum() - 1;
            while (c != 2) {
                this.gridSkuGroup.deleteColumn(c);
                c = this.gridSkuGroup.getColumnsNum() - 1;
            }
            this.gridSkuGroup._cMod = null;

        }

        this.gridSkuGroupSelectedId = data.id;


        paginationGrid.setOnPage('list_SKUGROUP', this.onPageSkuListDefault);

        this.list_SKUGROUPXLS(data.id, data.page);

        pg = new paginationGrid('list_SKUGROUP', {
            target: this.mainViewPortFind('.panel-footer'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'list_SKUGROUP', {
                id: data.id
            }) //,

        });

    },


    list_SKUGROUPXLS: function (id, page) {

        this.connector.execute({
            skuGroupList: {
                id: id,
                page: page,
                onPage: paginationGrid.getOnPage('list_SKUGROUP')
            }
        });

        this.currentSkuListIdGroup = id;

        if (this.connector.result.data_set) {

            this.columnBuilder(this.connector.result.columnsInfo, this.gridSkuGroup, 2, 'sku');

            this.gridSkuGroup.parse(this.connector.result.data_set, "xjson")

        }

    },


    enableCatObject: function () {
        this.enableObject(this.gridCatGroup, 'enableCatObject');

        this.refreshCatObj();
    },

    disableCatObject: function () {
        this.disableObject(this.gridCatGroup, 'disableCatObject');
        this.refreshCatObj();
    },


    checkForSegmentation:function(property,cInd,state,rId)
    {
        if(property.indexOf('segmentation.')!==false)
        {
            this.gridCatGroup.forEachRow(function(id){
                
                 if(id==rId)return;
                 check=this.gridCatGroup.cellById(id,cInd);
                 check.setChecked(false);
                 this.execute({setSingleProperty: {id:id, property:property, value: false}});
                 
                }.bind(this));
                
        }
    },
    
    onCheckBox: function (rId, cInd, state) 
    {
        
        cell = this.gridCatGroup.cellById(rId, 1);
        property=this.managerColumns[cInd];
        
        id=cell.getValue();
        this.checkForSegmentation(property,cInd,state,rId);
        
        this.execute({setSingleProperty: {id:id, property:property, value: state}});
    },

    list_CATGROUP: function (data) {


        if (this.mainViewPortFind('#catGroupList').length == 0) {

            this.tabs.addTab({
                id: 'list_CATGROUP',
                name: AI.translate('catalog', 'list_CATGROUP'),
                href: '#',
                temporal: true,
                active: true
            }, true);

            this.setMainViewPort(this.getTpl('_CATGROUP_list'));

            menu = new dhtmlXMenuObject();
            menu.renderAsContextMenu();

            if (__globalLogLevel == 9) {
                menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

            }
            menu.addNewChild(menu.topId, 0, 'enable', AI.translate('common', "enable"), false, '', '', this.enableCatObject.bind(this));
            menu.addNewChild(menu.topId, 0, 'disable', AI.translate('common', "disable"), false, '', '', this.disableCatObject.bind(this));
            
            if(this.getPermission('deleteObjectsRights')) 
            {
                menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteCatObj.bind(this));
            }
            
            if(this.getPermission('copyObjectsRights')) 
            {
                menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyCatObj.bind(this));
            }
            
            
            menu.addNewChild(menu.topId, 0, 'paste', AI.translate('common', "paste"), false, '', '', this.pasteCatObj.bind(this));
            menu.addNewChild(menu.topId, 0, 'refresh', AI.translate('common', "refresh"), false, '', '', this.refreshCatObj.bind(this));


            this.gridCatGroup = new dhtmlXGridObject('catGroupList');
            this.gridCatGroup.selMultiRows = true;
            this.gridCatGroup.setMultiLine(true);
            this.gridCatGroup.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
            this.gridCatGroup.setHeader(' ,id,' + AI.translate('common', 'disabled-short') + ',' + AI.translate('common', 'name'));
            this.gridCatGroup.setInitWidths("35,55,0,180");
            this.gridCatGroup.iconURL = "/x4/adm/xres/ximg/green/";
            this.gridCatGroup.setColAlign("left,left,center,left");
            this.gridCatGroup.setColTypes("img,ro,ch,ro");

            this.gridCatGroup.attachEvent("onCheck", this.onCheckBox.bind(this));
            
            this.gridCatGroup.attachEvent("onRowDblClicked", function (id) {
                AI.navigate(AI.navHashCreate(this.name, 'edit_CATOBJ', {
                    'id': id
                }));

            }.bind(this));

            this.gridCatGroup.enableDragAndDrop(true);
            this.gridCatGroup.enableContextMenu(menu);
            this.gridCatGroup.init();
            this.gridCatGroup.setSkin("modern");
            this.gridCatGroup.attachEvent("onDrag", this.onTreeGridDrag.bind(this));

            this.gridCatGroup.rowToDragElement = function (id) {
                if (this.cells(id, 2).getValue() != "") {
                    return this.cells(id, 2).getValue() + "/" + this.cells(id, 1).getValue();
                } else {
                    return this.cells(id, 1).getValue();
                }
            }

        } else {

            this.gridCatGroup.clearAll();
            this.gridCatGroup._cMod = null;
            c = this.gridCatGroup.getColumnsNum() - 1;
            while (c != 3) {
                this.gridCatGroup.deleteColumn(c);
                c = this.gridCatGroup.getColumnsNum() - 1;
            }
            this.gridCatGroup._cMod = null;

        }

        this.gridCatGroupSelectedId = data.id;

        paginationGrid.setOnPage('list_CATGROUP', this.onPageCatListDefault);

        this.list_CATGROUPXLS(data.id, data.page);

        pg = new paginationGrid('list_CATGROUP', {
            target: this.mainViewPortFind('.panel-footer'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'list_CATGROUP', {
                id: data.id
            })

        });

    },

    refreshCatObj: function () {
        AI.refreshPage();

    },

    list_CATGROUPXLS: function (id, page) {

        this.connector.execute({
            catGroupList: {
                id: id,
                page: page,
                onPage: paginationGrid.getOnPage('list_CATGROUP')
            }
        });
        if (this.connector.result.data_set) {

            this.columnBuilder(this.connector.result.columnsInfo, this.gridCatGroup, 3);
            this.gridCatGroup.parse(this.connector.result.data_set, "xjson");


            for (var i = 0; i < this.gridCatGroup.getRowsNum(); i++) {
                value = this.gridCatGroup.cells2(i, 2).getValue();

                if (value == '1') {
                    this.gridCatGroup.setRowColor('0' + this.gridCatGroup.cells2(i, 1).getValue(), "#DDD");
                }

            }

        }

    },

    columnBuilder: function (columns, grid, num, listType) {

        this.managerColumns = [];
        i = num;
        if (!listType) listType = 'tree';

        for (column in columns) {
            i++;
            this.managerColumns[i] = column;

            if (columns[column].alias) {

                handlerData = {
                    type: "ro",
                    width: "*"
                };

                if (typeof this.propertiesHolder[columns[column].type].handler == 'object') {
                    handlerData = this.propertiesHolder[columns[column].type].handler.handleOnList(listType);
                }

                handlerData.align = (handlerData.align || 'left');
                grid.insertColumn(i, columns[column].alias,
                    handlerData.type,
                    handlerData.width,
                    'str',
                    handlerData.align);
            }
        }

    },

    importDataJson: function (id, kid) {
        var filename = prompt('File name', 100);
        if (filename) {
            this.connector.execute({
                importDataJson: {
                    id: kid,
                    name: filename
                }
            });
        }

    },

    exportDataJson: function (id, kid) {

        this.connector.execute({
            exportDataJson: {
                id: kid

            }
        });


    },


    startImportData: function () {
        data = xoad.html.exportForm("importXLS");
        this.connector.execute({importData: data});
    },


    importData: function (id, kid) {
        this.tabs.addTab({
            id: 'tshowSearchResults',
            name: AI.translate('catalog', 'import'),
            temporal: true,
            active: true
        }, true);


        this.connector.execute({getImportData: {id: kid}});
        this.setMainViewPort(this.getTpl('catalogImport'));
        xoad.html.importForm('importXLS', this.connector.result.importData);


    },


    buildInterface: function () {

        //данная конструкция вызовет метод класса предка _xModuleBack.buildInterface
        //такой вызов обязателен!              
        this.parent();
        this.tabsStart();
        this.propertyViewConstructor = new propertyViewConstructor(this);

        this.treeMenu=menu = new dhtmlXMenuObject();
        
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

         if(this.getPermission('copyObjectsRights')) 
            {
                menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyCatObjTree.bind(this)); 
            }
        
        menu.addNewChild(menu.topId, 0, 'paste', AI.translate('common', "paste"), false, '', '', this.pasteCatObjTree.bind(this));
        menu.addNewChild(menu.topId, 0, 'edit', AI.translate('common', "edit"), false, '', '', function (id, kid) {

                AI.navigate(AI.navHashCreate(this.name, 'edit_CATGROUP', {
                    'id': kid
                }));

            }.bind(this)
        );
        
         if(this.getPermission('deleteObjectsRights')) 
            {
                menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteCatGroup.bind(this));
            }
                   
        
        
        menu.addNewChild(menu.topId, 0, 'import', AI.translate('catalog', "import"), false, '', '', this.importData.bind(this));
        menu.addNewChild(menu.topId, 0, 'exportjson', AI.translate('catalog', "exportJSON"), false, '', '', this.exportDataJson.bind(this));
        menu.addNewChild(menu.topId, 0, 'importjson', AI.translate('catalog', "importJSON"), false, '', '', this.importDataJson.bind(this));

        menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common', "refresh"), false, '', '', this.refreshTree.bind(this));

        this.tree = new dhtmlXGridObject(this.treeViewPort);
        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";
        this.tree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
        this.tree.setInitWidths("280,*");
        this.tree.setColAlign("left,center");
        this.tree.setColTypes("tree,ro");
        this.tree.enableDragAndDrop(true);
        //   tree.enableEditEvents(false,false,true);
        this.tree.attachEvent("onDrag", this.onTreeGridDrag.bind(this));

        this.tree.setDragBehavior('complex-next');

        this.tree.enableMultiselect(true);
        this.tree.enableContextMenu(menu);

        this.tree.init();
        this.tree.kidsXmlFile = 1;

        this.tree.attachEvent("onDynXLS", this.treeDynamicXLS.bind(this));
        this.tree.setSkin("dhx_skyblue");

        this.tree.attachEvent("onRowDblClicked", this.treeObjectClicked.bind(this));
        this.treeDynamicXLS(0);


        this.tree.openItem('01');

        $(this.tree.entBox).addClass('catalog-tree');

        menusku = new dhtmlXMenuObject();
        menusku.renderAsContextMenu();

        menusku.addNewChild(menu.topId, 0, "refresh", 'refresh', false, '', '', this.refreshSku.bind(this));
        menusku.addNewChild(menu.topId, 0, "clear", AI.translate('common', "clear"), false, '', '', this.clearSku.bind(this));
        menusku.addNewChild(menu.topId, 0, "clear", AI.translate('common', "delete"), false, '', '', this.deleteSkuGroup.bind(this));

        this.skutree = new dhtmlXGridObject($('.sku-tree')[0]);
        this.skutree.selMultiRows = true;
        this.skutree.imgURL = "/x4/adm/xres/ximg/green/";
        this.skutree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
        this.skutree.setInitWidths("280,*");
        this.skutree.setColAlign("left,center");
        this.skutree.setColTypes("tree,ro");

        this.skutree.enableMultiselect(true);
        this.skutree.enableContextMenu(menusku);

        this.skutree.init();
        this.skutree.kidsXmlFile = 1;

        this.skutree.attachEvent("onDynXLS", this.treeDynamicXLSsku.bind(this));
        this.skutree.setSkin("dhx_skyblue");

        this.skutree.attachEvent("onRowDblClicked", function (kid) {

            AI.navigate(AI.navHashCreate(this.name, 'list_SKUGROUP', {'id': kid}));

        }.bind(this));

        this.treeDynamicXLSsku(0);
        this.skutree.openItem(1);


        $(this.skutree.entBox).addClass('sku-tree');
        $(this.skutree.entBox).addClass('hide');

        $('#catalog .btn-group .btn-switch-tree').btnSwitch();

    },

    deleteSkuGroup: function (id, kid) {

        this.deleteObjectGrid(this.skutree, 'deleteSku');
    },

    onSearchInModule: function (result) {

        this.tabs.addTab({
            id: 'tshowSearchResults',
            name: AI.translate('common', 'search-results'),
            temporal: true,
            active: true
        }, true);

        jQuery(this.mainViewPort).addClass('grid-view');
        jQuery(this.mainViewPort).html('<div id="searchResultsContainerCatalog" style="height:700px"></div>');


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

        //menu.addNewChild(menu.topId, 0, "delete", AI.translate('common','delete'), false, '', '', this.deleteContent.bind(this));

        this.sgridlist = new dhtmlXGridObject('searchResultsContainerCatalog');
        this.sgridlist.selMultiRows = true;
        this.sgridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.sgridlist.setHeader('id,' + AI.translate('common', 'type') + ',' + AI.translate('common', 'name') + ',' + AI.translate('common', 'link'));
        this.sgridlist.setInitWidths("80,100,600,*");

        this.sgridlist.setColAlign("center,left,left,left");
        this.sgridlist.setColTypes("ro,ro,ro,ro");
        this.sgridlist.setColSorting("int,str,str,str");
        this.sgridlist.attachEvent("onRowDblClicked", this.searchGridObjectClicked.bind(this));
        this.sgridlist.enableAutoWidth(true);
        this.sgridlist.enableContextMenu(menu);
        this.sgridlist.init();
        this.sgridlist.setSkin("modern");
        this.sgridlist.parse(result, "xjson")
    },


    searchGridObjectClicked: function (id) {


        objType = this.sgridlist.cellById(id, 1).getValue();

        if (this.treeClickMap[objType]) {
            AI.navigate(AI.navHashCreate(this.name, this.treeClickMap[objType], {'id': id}));
        }
    },

    clearSku: function (id) {


    },

    refreshSku: function (id) {

    },

    treeDynamicXLSsku: function (id) {
        this.connector.execute({
            treeDynamicXLSsku: {
                id: id
            }
        });
        if (this.connector.result) {
            if (id == 0) {
                this.skutree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.skutree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },

    onModuleInterfaceBuildedAfter: function () {

        this.dataListWatcher = new dataListWatcher({currentView: this.viewPort});

        $(document).on('click', '#' + this.name + ' .addProperty', [], this.create_PROPERTY.bind(this));
        $(document).on('click', '#' + this.name + ' .addSearchProperty', [], this.create_SEARCHELEMENT.bind(this));
        $(document).on('click', '#' + this.name + ' .addNewSku', [], this.SKUOBJ.create.bind(this.SKUOBJ));
        $(document).on('click', '#' + this.name + ' .importFilesCatalog', [], this.startImportData.bind(this));
        $(document).on('change', '#' + this.name + ' #PropertySetGroup', [], this.onPropertySetGroupChange.bind(this));

        //$(document).on('click','#'+this.name+' .addNewPropertySet', [],this.create_PROPERTYSET.bind(this));             
        //  $(document).on('click','#'+this.name+' .savePropertyGroup', [],this.save_PROPERTYSETGROUP.bind(this));             

        $(document).on('change', '.propertyEditor select[name=type]', [], this.PROPERTY.onChangePropertyType.bind(this.PROPERTY));

    },

    onActionRender_showCategory: function (data, actionData, moduleData) {
        if (moduleData) {
            delete moduleData['showGroup'];
        }


    }


}); 
