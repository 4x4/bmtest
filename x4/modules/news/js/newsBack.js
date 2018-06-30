newsBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.categoriesListTemplate = '{{#each opts}}' +
            '<div class="checkbox i-checks"><label><input name="categories.{{@key}}" {{#ifCond @key "in" ../selected}} checked {{/ifCond}}  type="checkbox" value="{{@key}}"><i></i>{{this}}</label></div>' +
            '{{/each}}';

        this.categoriesListTemplateHB = Handlebars.compile(this.categoriesListTemplate);

        this.setLayoutScheme('treeView', {
            treeSize: 'xl',
            rightPanelWidth: '890px',
            customTreeHtml: this.getTpl('customNewsTree')
        });

        this.objTypeScope = new Array('_NEWSGROUP');
        this.pushToTreeClickMap('_NEWSGROUP', 'showNewsList');
    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },

    createCategoriesList: function (cats, selected) {
        return this.categoriesListTemplateHB({opts: cats, selected: selected});
    },


    CRUN: function () {
        _NEWSGROUP = new Class({
            Extends: CRUN,
            initialize: function (context) {
                this.parent(context, {
                    objType: '_NEWSGROUP',
                    autoCreateMethods: true
                });

            },

            createWindow: function (txt, header) {
                this.window = AI.dhxWins.createWindow("newsCategoryWindow", 20, 10, 450, 180, 1);
                this.window.button('park').hide();

                this.window.attachEvent("onHide", function (win) {
                    this.context.navigate('dummy');
                    win.close();

                }.bind(this));
                this.window.setText(header);
                this.window.attachHTMLString(txt);
                this.window.setModal(true);
                this.window.centerOnScreen();
                this.windowContext = this.window.dhxcont;

            },

            create: function (data) {

                this.context.preventGridView = true;
                txt = this.context.getTpl(this.options.objType);
                this.createWindow(txt, AI.translate('news', 'add-category'));
                this.form = jQuery("#create" + this.options.objType);
                this.form.validationEngine();
                $(this.windowContext).find('.save').click(this.save.bind(this));

            },


            save: function (e) {
                e.preventDefault();
                this.parent();


                if (this.validated) {
                    data = xoad.html.exportForm("create" + this.options.objType);
                    this.context.execute({onSave_NEWSGROUP: {data: data}});

                    if (this.context.connector.result.onSave_NEWSGROUP) {

                        AI.navigate(AI.navHashCreate(this.context.name, 'dummy'));
                        this.context.tree.refreshItem(1);
                        this.window.close();
                    }


                }
            },


            saveEdited: function (e) {
                e.preventDefault();
                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('edit_NEWSGROUP');
                    result = this.context.execute
                    ({
                        onSaveEdited_NEWSGROUP: {
                            id: this.selectedId,
                            data: data
                        }
                    });

                    if (result.onSaveEdited_NEWSGROUP) {
                        this.context.preventGridView = true;
                        AI.navigate(AI.navHashCreate(this.context.name, 'dummy'));
                        this.context.tree.refreshItem(1);
                        this.window.close();
                    }

                }

            },

            edit: function (data, id) {
                this.context.preventGridView = true;
                result = this.context.execute({
                    onEdit_NEWSGROUP: {
                        id: id
                    }
                });

                this.selectedId = id;
                txt = this.context.getTpl(this.options.objType + '@edit');
                this.createWindow(txt, AI.translate('news', 'edit-category'));
                this.form = jQuery("#edit" + this.options.objType);
                this.form.validationEngine();
                xoad.html.importForm("edit" + this.options.objType, result.data);

                $(this.windowContext).find('#Name').bind('keydown', 'return', this.saveEdited.bind(this));


                $(this.windowContext).find('.save').click(this.saveEdited.bind(this));
            }

        });

        _NEWS = new Class({
            Extends: CRUN,
            initialize: function (context) {
                this.parent(context, {
                    objType: '_NEWS',
                    autoCreateMethods: true
                });
            },


            create: function (data) {

                jQuery(this.context.mainViewPort).removeClass('grid-view');

                selectedId = this.context.tree.getSelectedRowId();
                parentData = {};

                if (selectedId) {
                    objType = this.context.tree.getRowAttribute(selectedId, "obj_type");
                    if (['_ROOT'].indexOf(objType) == -1) parentData = {
                        id: selectedId
                    }

                }

                this.parent(parentData);

                this.context.viewPort.find('input[name=basic]').val(Date.now());

                if (parentData.id) {
                    this.context.viewPort.find('input[name=ancestor]').val(this.context.getTreePathAncestor(null));

                    this.context.viewPort.find('input[name=ancestorId]').val(selectedId);

                }


                this.context.mainViewPortFind('.newsCategories').html(this.context.createCategoriesList(this.context.connector.result.categories));
                xoad.html.importForm('create_NEWS', this.context.connector.result.data);

                this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
                this.context.mainViewPortFind('.form-group select[name=Template]').chosen();
                tagManMonitor.applyTagMans();


            },

            saveEdited: function (e) {
                e.preventDefault();

                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('edit_NEWS');


                    this.context.execute({
                        onSaveEdited_NEWS: {
                            id: this.selectedId,
                            data: data
                        }
                    });

                    if ($(e.target).hasClass('saveback')) {
                        AI.navigate(AI.navHashCreate(this.context.name, 'showNewsList'));
                    }
                }

            },
            save: function (e) {
                e.preventDefault();
                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('create_NEWS');

                    result = this.context.execute({
                        onSave_NEWS: {
                            data: data
                        }
                    });

                    if (result.onSave_NEWS) {
                        this.context.navigate('showNewsList');
                    }
                }

            },

            edit: function (params) {

                this.parent(params);

                this.context.tabs.addTab({
                    id: 'teditnews',
                    name: AI.translate('news', 'edit_NEWS'),
                    temporal: true,
                    active: true
                }, true);


                result = this.context.execute({onEdit_NEWS: {id: params.id}});
                this.context.mainViewPortFind('.newsCategories').html(this.context.createCategoriesList(result.categories, result.selectedCategories));

                this.context.mainViewPortFind('.saveback').click(this.saveEdited.bind(this));

                xoad.html.importForm("edit" + this.options.objType, result.data);
                this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
                tagManMonitor.applyTagMans();

            }

        });

        this.NEWSGROUP = new _NEWSGROUP(this);
        this.NEWS = new _NEWS(this);
    },

    deleteNews: function (kid, id) {

        selected = this.gridlist.getSelectedRowId(true);
        if (selected.length > 0) {
            cells = [];
            for (i = 0; i < selected.length; i++) {
                cell = this.gridlist.cellById(selected[i], 0);
                cells.push(cell.getValue());
            }

            this.execute({deleteNews: {id: cells}});

        }


        if (this.connector.result.deleted) {
            this.gridlist.deleteSelectedRows();
        }

    },

    showNewsList: function (data) {

        this.tabs.addTab({
            id: 'tshowNewsList',
            name: AI.translate('news', 'news-list'),
            temporal: true,
            active: true
        }, true);


        this.setGridView('newsListContainer', (window.screen.availHeight - 300), true);

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteNews.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '',

            function (bid, kid) {
                cell = this.gridlist.cellById(kid, 0);
                this.navigate('edit_NEWS', {id: cell.getValue()})

            }.bind(this)
        );


        this.gridlist = new dhtmlXGridObject('newsListContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.enableMultiline(true);
        this.gridlist.setImagePath("/x4/adm/xres/ximg/green/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'date') + ',' + AI.translate('news', 'header') + ',' + AI.translate('news', 'author') + ',' + AI.translate('common', 'link') + ',' + AI.translate('common', 'active'));

        this.gridlist.setInitWidths("80,130,*,80,120,80");
        this.gridlist.setColAlign("center,left,left,center,center,left");
        this.gridlist.attachEvent("onCheck", this.onCheckBox.bind(this));

        //    this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");

        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            cell = this.gridlist.cellById(kid, 0);
            this.navigate('edit_NEWS', {id: cell.getValue()})

        }.bind(this));


        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ch");

        this.gridlist.enableAutoWidth(true);

        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");
        this.gridlist.onPage = 50;


        this.listNews(data.id, data.page);

        var pg = new paginationGrid(this.gridlist, {
            target: this.mainViewPortFind('.paginator'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'showNewsList', {id: data.id}) //,

        });


    },


    listNews: function (id, page) {

        this.connector.execute({
            newsTable: {
                id: id,
                page: page,
                onPage: this.gridlist.onPage
            }
        });

        if (this.connector.result.data_set) {

            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

    },


    tabsStart: function () {
        var oTabs = [
            {
                id: 'tcreate_NEWS',
                name: AI.translate('news', 'add_news'),
                href: AI.navHashCreate(this.name, 'create_NEWS')
            }

        ];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    deleteGroup: function () {
        this.deleteObjectGrid(this.tree);
    },


    onCheckBox: function (rId, cInd, state) {
        cell = this.gridlist.cellById(rId, 0);
        this.execute({setNewsActive: {id: cell.getValue(), state: state}});
    },


    onTreeDialogReturn: function (dialog, data) {

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
        this.execute({
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

    onDialogGroup: function (dialogContext) {

        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('news', 'news_categories'));
        this.dialogGroupTree.setInitWidths("*");
        this.dialogGroupTree.setColAlign("left");
        this.dialogGroupTree.setColTypes("tree");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        $(this.dialogGroupTree.entBox).find('.ev_dhx_skyblue').hide();
        this.dialogGroupTree.openItem(1);
    },


    onActionRender_showNewsInterval: function (context, data) {

        context.container.find('select[name=Categories]').chosen();
    },

    showAllNews: function () {
        data = {};
        data.id = null;
        data.page = 1;
        this.showNewsList(data);
    },


    buildInterface: function () {

        this.parent();
        /*--tabs--*/
        this.tabsStart();
        /*--menu--*/

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", AI.translate('common', 'console-it'), false, '', '', this.consoleIt.bind(this));
        }

        menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common', 'refresh'), false, '', '', this.refreshTree.bind(this));
        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteGroup.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '', this.NEWSGROUP.edit.bind(this.NEWSGROUP));

        //     $(this.treeViewPort).before('<a class="content-add-category" href="' + AI.navHashCreate(this.name, 'create_NEWSGROUP') + '">' + AI.translate('content', 'add-category') + '<a>');

        $(this.treeViewPort).css({minHeight: (window.screen.availHeight - 230)});
        this.tree = new dhtmlXGridObject(this.treeViewPort);
        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";
        this.tree.setHeader(AI.translate('news', 'group_name'));

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
        this.showAllNews();

    }

});