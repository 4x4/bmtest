fusersBack = new Class(
    {

        Extends: _xModuleBack,

        initialize: function (name) {

            this.setName(name);
            this.parent();


            this.setLayoutScheme('treeView', {
                treeSize: 'xl',
                rightPanelWidth: '890px'
            });

            AI.loadJs('/x4/modules/fusers/js/fusersBackObjects.js', false);
            this.objTypeScope = new Array('_FUSERSGROUP');
            this.pushToTreeClickMap('_FUSERSGROUP', 'showUsersList');

        },


        CRUN: function () {
            this.FUSER = new _FUSER(this);
            this.FUSERSGROUP = new _FUSERSGROUP(this);

        },


        showUsersList: function (data) {

            this.FUSER.showFusersList(data);

        },

        onHashDispatch: function (e, v) {
            this.tabs.makeActive('t' + e);
            return true;
        },

        tabsStart: function () {

            var oTabs = [
                {

                    id: 'tcreate_FUSER',
                    name: AI.translate('fusers', 'new_user'),
                    href: AI.navHashCreate(this.name, 'create_FUSER')
                },

                {
                    id: 'tcreate_FUSERSGROUP',
                    name: AI.translate('common', 'add_group'),
                    href: AI.navHashCreate(this.name, 'create_FUSERSGROUP')
                },
                {
                    id: 't_fusersTunes',
                    name: AI.translate('common', 'options'),
                    href: AI.navHashCreate(this.name, 'options')
                }

            ];

            this.tabs = new Tabs(this.tabsViewPort, oTabs);

        },


        onTreeDialogReturn: function (dialog, data) {
            jQuery('.hiddenTemplates').show(200);
            this.connector.execute({onObjectSituationChanged: {id: data.id}});
            form = jQuery(dialog.currentElement).parents('form');
            form.find('#Template option').remove();
            xoad.html.importForm(form.attr('id'), this.connector.result.data);
        },


        onDialogObjectClick: function (id) {
            nameArr = this.dialogGroupTree.getParentPath(id, 0);
            delete nameArr[0];
            name = nameArr.join('/');


            objTypesToSelect = this.dialogContext.info.split(',');
            objType = this.dialogGroupTree.getRowAttribute(id, "obj_type");

            if (objTypesToSelect.indexOf(objType) != -1) {
                this.dialogContext.returnData({id: id, name: name});

            }

        },


        onDialogGroupFusers: function (dialogContext) {
            this.dialogContext = dialogContext;
            this.dialogGroupTree = dialogContext.window.attachGrid();
            this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
            this.dialogGroupTree.setHeader(AI.translate('fusers', 'id'));
            this.dialogGroupTree.setInitWidths("*");
            this.dialogGroupTree.setColAlign("left");
            this.dialogGroupTree.setColTypes("tree");
            this.dialogGroupTree.init();
            this.dialogGroupTree.kidsXmlFile = 1;
            this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicXLS.bind(this));
            this.dialogGroupTree.setSkin("dhx_skyblue");
            this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
            this.dialogGroupTreeDynamicXLS(0);
            this.dialogGroupTree.openItem(1);
        },


        dialogGroupTreeDynamicXLS: function (id) {
            this.connector.execute({treeDynamicXLSFusers: {id: id}});
            if (this.connector.result) {
                if (id == 0) {
                    this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
                } else {
                    this.dialogGroupTree.json_dataset = this.connector.result.data_set;
                }
            }
            return true;
        },


        options: function () {
            this.setMainViewPort(this.getTpl('tunes'));
            result = this.execute({onOptions: true});
            xoad.html.importForm('options', this.connector.result.options);
            this.mainViewPortFind('.save').click(this.saveOptions.bind(this));
        },

        saveOptions: function (e) {
            e.preventDefault();
            data = xoad.html.exportForm("options");
            this.execute({
                onSaveOptions: {
                    data: data
                }
            });
        },

        deleteGroup: function () {

        },


        onSearchInModule: function (result) {

            this.tabs.addTab({
                id: 'tshowSearchResults',
                name: AI.translate('common', 'search-results'),
                temporal: true,
                active: true
            }, true);


            this.setGridView('searchResultsContainerFusers', 750, true);

            menu = new dhtmlXMenuObject();
            menu.renderAsContextMenu();

            if (__globalLogLevel == 9) {
                menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

            }

            //menu.addNewChild(menu.topId, 0, "delete", AI.translate('common','delete'), false, '', '', this.deleteContent.bind(this));

            this.sgridlist = new dhtmlXGridObject('searchResultsContainerFusers');

            this.sgridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
            this.sgridlist.setHeader('id,' + AI.translate('common', 'type') + ',' + AI.translate('fusers', 'login') + ',' + AI.translate('common', 'name') + ',' + AI.translate('fusers', 'surname') + ',' + AI.translate('fusers', 'email'));

            this.sgridlist.setInitWidths("100,0,200,150,150,*");

            this.sgridlist.setColAlign("center,left,left,left,left,left,left");
            this.sgridlist.setColTypes("ro,ro,ro,ro,ro,ro,ro");
            this.sgridlist.setColSorting("int,str,str,str,str,str,str");
            this.sgridlist.attachEvent("onRowDblClicked", this.searchGridObjectClicked.bind(this));
            this.sgridlist.enableAutoWidth(true);
            this.sgridlist.enableContextMenu(menu);
            this.sgridlist.init();
            this.sgridlist.setSkin("modern");
            this.sgridlist.parse(result, "xjson")
        },

        searchGridObjectClicked: function (id) {

            AI.navigate(AI.navHashCreate(this.name, 'edit_FUSER', {'id': id}));

        },


        buildInterface: function () {

            this.parent();
            this.tabsStart();

            menu = new dhtmlXMenuObject();
            menu.renderAsContextMenu();


            if (__globalLogLevel == 9) {
                menu.addNewChild(menu.topId, 0, "console-it", AI.translate('common', 'console-it'), false, '', '', this.consoleIt.bind(this));
            }

            menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common', 'new_user'), false, '', '', this.refreshTree.bind(this));
            menu.addNewChild(menu.topId, 0, "edit", AI.translate('fusers', 'edit_group'), false, '', '', function (id, kid) {
                this.navigate('edit_FUSERSGROUP', {id: kid});
            }.bind(this));
            menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.FUSERSGROUP.deleteGroup.bind(this.FUSERSGROUP));


            $(this.treeViewPort).css({minHeight: (window.screen.availHeight - 230)});

            this.tree = new dhtmlXGridObject(this.treeViewPort);
            this.tree.selMultiRows = true;
            this.tree.imgURL = "/x4/adm/xres/ximg/green/";
            this.tree.setHeader(AI.translate('fusers', 'group_name'));

            $(this.treeViewPort).find('.hdr').hide();

            this.tree.setInitWidths("*");
            this.tree.setColAlign("left");
            this.tree.setColTypes("tree");
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
            $(this.tree.entBox).find('.ev_dhx_skyblue ').hide();
            this.tree.openItem(1);


        }

    });
