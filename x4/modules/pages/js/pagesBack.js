pagesBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {


        AI.loadJs('*_components/mt.slotz/slotz.js');

        this.setName(name);
        this.parent();
        this.setLayoutScheme('treeView', {
            searchplaceholder: 'введите слово для поиска',
            plugins: 'Плагины',
            treeSize: 'xxl',
            treeHeight: '750px'
        });
        this.objTypeScope = ['_PAGE', '_GROUP', '_LINK'];
        this.loadDefaultTpls = ['slotzInterface'];
        AI.loadJs('/x4/modules/pages/js/pagesBackObjects.js', true);

//        AI.xListServer.pushCallBack('onTreeDialogReturn',this.onTreeDialogReturn.bind(this));

    },


    onModuleInterfaceBuildedAfter: function () {
        $(document).on('click', '#' + this.name + ' .generate-link', [],
            function (e) {
                e.preventDefault();

                translitToLat('Name', 'basic');
            }
        );


        $(document).on('change', '#' + this.name + ' #create_PAGE #Template', [],
            function (e) {
                id = $('#create_PAGE #ancestorId').val();
                this.connector.execute({getSlotz: {tplName: $(e.target).val(), id: id}});
                this.PAGE._slotz = new Slotz({connector: this.connector, slotsInstance: this.connector.result.slotz});

            }.bind(this)
        );


        $(document).on('change', '#' + this.name + ' #edit_PAGE #Template', [],
            function (e) {

                if (!this.PAGE.currentPageId) {
                    id = $('#create_PAGE #ancestorId').val();
                } else {
                    id = this.PAGE.currentPageId;
                }
                this.connector.execute({
                    getSlotz: {tplName: $(e.target).val(), id: id},
                    getModules: {id: this.PAGE.currentPageId}
                });
                this.PAGE._slotz = new Slotz({
                    connector: this.connector,
                    slotsInstance: this.connector.result.slotz,
                    modulesInstance: this.connector.result.modules
                });


            }.bind(this)
        );


        $(this.mainViewPort).find('Template');


        $(document).on('click', '#' + this.name + ' #create_GROUP #ancestor', [],
            function (e) {


                result = this.connector.execute({getSlotzAll: {id: $(this.mainViewPort).find('#ancestorId').val()}});
                if (result.slotz.length > 0) {
                    this._slotz.initiateSlotz(result.slotz, true);
                }

            }.bind(this));


        $(document).on('click', '.route-add', this.ROUTES.save.bind(this.ROUTES));


    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },


    CRUN: function () {
        this.GROUP = new _GROUP(this);
        this.PAGE = new _PAGE(this);
        this.DOMAIN = new _DOMAIN(this);
        this.LVERSION = new _LVERSION(this);
        this.ROUTES = new _ROUTES(this);
        this.LINK = new _LINK(this);
    },


    dialogGroupTreeDynamicXLS: function (id) {
        this.connector.execute({treeDynamicXLSGroupsOnly: {id: id}});
        if (this.connector.result) {
            if (id == 0) {
                this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.dialogGroupTree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },

    dialogGroupPageTreeDynamicXLS: function (id) {
        this.connector.execute({treeDynamicXLS: {id: id}});
        if (this.connector.result) {
            if (id == 0) {
                this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.dialogGroupTree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },

    onTreeDialogReturn: function (dialog, data) {
        jQuery('.hiddenTemplates').show(200);
        this.connector.execute({onObjectSituationChanged: {id: data.id}});
        form = jQuery(dialog.currentElement).parents('form');
        form.find('#Template option').remove();
        xoad.html.importForm(form.attr('id'), this.connector.result.data);
    },


    allSlotzLoader: function (id) {
        result = this.connector.execute({getSlotzAll: {id: id}});

        if (typeof result.slotz != 'undefined') {
            this._slotz.initiateSlotz(result.slotz, true);
        } else {
            this._slotz.removeSlotz();
        }

    },


    onTreeDialogReturnGroup: function (dialog, data) {
        this.allSlotzLoader($(this.mainViewPort).find('#ancestorId').val());
    },


    onDialogObjectClick: function (id) {
        nameArr = this.dialogGroupTree.getParentPath(id, 0);
        name = nameArr.join('/');


        objTypesToSelect = this.dialogContext.info.split(',');
        objType = this.dialogGroupTree.getRowAttribute(id, "obj_type");

        if (objTypesToSelect.indexOf(objType) != -1) {
            this.dialogContext.returnData({id: id, name: name});

        }

    },

    onDialogGroup: function (dialogContext) {
        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('pages', 'page_name') + ',' + AI.translate('pages', 'link'));
        this.dialogGroupTree.setInitWidths("330,*");
        this.dialogGroupTree.setColAlign("left,left");
        this.dialogGroupTree.setColTypes("tree,ed");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        this.dialogGroupTree.openItem('01');
    },

    onDialogGroupPage: function (dialogContext) {
        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('pages', 'page_name') + ',' + AI.translate('pages', 'link'));
        this.dialogGroupTree.setInitWidths("330,*");
        this.dialogGroupTree.setColAlign("left,left");
        this.dialogGroupTree.setColTypes("tree,ed");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupPageTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupPageTreeDynamicXLS(0);
        this.dialogGroupTree.openItem('01');
    },


    routes: function () {

        this.setMainViewPort(TH.getTpl('pages', 'routes'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();
        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.ROUTES.del.bind(this.ROUTES));
        this.gridlistRoutes = new dhtmlXGridObject('routesTable');
        this.gridlistRoutes.selMultiRows = true;
        this.gridlistRoutes.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistRoutes.setHeader('id,' + AI.translate('common', 'source') + ',' + AI.translate('common', 'target') + ',' + AI.translate('pages', '301_redirect'));
        this.gridlistRoutes.setInitWidths("70,200,*,120");
        this.gridlistRoutes.enableContextMenu(menu);
        this.gridlistRoutes.setColAlign("center,left,left,center");
        this.gridlistRoutes.setColTypes("ed,ed,ed,ch");

        this.gridlistRoutes.attachEvent("onCheckbox", this.ROUTES.route301switch.bind(this.ROUTES));
        this.gridlistRoutes.enableAutoWidth(true);
        this.gridlistRoutes.enableContextMenu(menu);
        this.gridlistRoutes.init();
        this.gridlistRoutes.attachEvent("onEditCell", this.ROUTES.doOnCellEdit.bind(this.ROUTES));
        this.gridlistRoutes.setSkin("modern");
        this.ROUTES.refreshRoutes();

    },


    start: function () {


    },


    tabsStart: function () {

        var oTabs = [{
            id: 't_firstpage',
            name: AI.translate('common', 'info'),
            temporal: true,
            active: true
        }, {
            id: 'tcreate_PAGE',
            name: AI.translate('common', 'add'),
            href: '#',

            subTabs: [{
                id: 'tcreate_PAGE',
                name: AI.translate('pages', 'add_page'),
                href: AI.navHashCreate(this.name, 'create_PAGE'),
            },

                {
                    id: 'tcreate_GROUP',
                    name: AI.translate('common', 'add_group'),
                    href: AI.navHashCreate(this.name, 'create_GROUP')
                },

                {
                    id: 'addLink',
                    name: AI.translate('pages', 'add_link'),
                    href: AI.navHashCreate(this.name, 'create_LINK')

                },

                {
                    id: 'addLversion',
                    name: AI.translate('pages', 'add_lang'),
                    href: AI.navHashCreate(this.name, 'create_LVERSION')

                },


                {
                    id: 'addDomain',
                    name: AI.translate('pages', 'add_domain'),
                    href: AI.navHashCreate(this.name, 'create_DOMAIN')

                }

            ]
        },

            /* {
             id: 't_userMenu',
             name:AI.translate('pages','menu'),
             href: AI.navHashCreate(this.name, 'userMenu')
             },*/


            {
                id: 't_routes',
                name: AI.translate('pages', 'routes'),
                href: AI.navHashCreate(this.name, 'routes')
            }];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    deletePages: function (kid, id) {
        this.deleteObjectGrid(this.tree);
    },


    copyObj: function (id, kid) {

        this.copyObjectToBufferGrid(this.tree);

    },

    pasteObj: function (id, kid, keys, gc) {

        this.pasteObjectGrid(this.tree, kid);
        this.tree.refreshItem(kid);
    },

    buildInterface: function () {


        this.parent();
        /*--tabs--*/
        this.tabsStart();

        /*--menu--*/

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        }
        menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common', "refresh"), false, '', '', this.refreshTree.bind(this));
        menu.addNewChild(menu.topId, 0, 'copy', AI.translate('common', "copy"), false, '', '', this.copyObj.bind(this));
        menu.addNewChild(menu.topId, 0, 'paste', AI.translate('common', "paste"), false, '', '', this.pasteObj.bind(this));


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deletePages.bind(this));


        this.tree = new dhtmlXGridObject(this.treeViewPort);
        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";
        this.tree.setHeader(AI.translate('pages', 'page_name') + ',' + AI.translate('pages', 'link'));
        this.tree.setInitWidths("300,120");
        this.tree.setColAlign("left,left");
        this.tree.setColTypes("tree,ed");
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

    },

    onSearchInModule: function (result) {

        this.tabs.addTab({
            id: 'tshowSearchResults',
            name: AI.translate('common', 'search-results'),
            temporal: true,
            active: true
        }, true);

        jQuery(this.mainViewPort).addClass('grid-view');
        jQuery(this.mainViewPort).html('<div id="searchResultsContainer" style="height:500px"></div>');


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

        //menu.addNewChild(menu.topId, 0, "delete", AI.translate('common','delete'), false, '', '', this.deleteContent.bind(this));

        this.sgridlist = new dhtmlXGridObject('searchResultsContainer');
        this.sgridlist.selMultiRows = true;
        this.sgridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.sgridlist.setHeader('id,' + AI.translate('common', 'type') + ',' + AI.translate('common', 'name') + ',' + AI.translate('common', 'link'));
        this.sgridlist.setInitWidths("80,0,300,300");

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
    }


    /*  onActionRender_showLevelMenu:function(context,data)
     {

     }
     */

});
    
        