templatesBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('treeView', {treeSize: 'xxl', treeHeight: '850px'});
        this.objTypeScope = ['_FILE', '_FOLDER'];
        ace.config.set("modePath", "/x4/adm/xjs/_components/ace");
        ace.config.set("themePath", "/x4/adm/xjs/_components/ace");
    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        jQuery(this.mainViewPort).removeClass('grid-view');
        return true;
    },

    CRUN: function () {

        _FILE = new Class({
            Extends: CRUN,
            initialize: function (context) {
                this.parent(context, {
                    objType: '_FILE',
                    autoCreateMethods: true
                });
                context.pushToTreeClickMap(this.options.objType, 'edit_FILE');


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

                if (parentData.id) {
                    this.context.viewPort.find('input[name=ancestor]').val(this.context.getTreePathAncestor(null));
                    this.context.viewPort.find('input[name=ancestorId]').val(selectedId);

                }

                xoad.html.importForm('create_CONTENT', this.context.connector.result.data);
                this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
                this.context.mainViewPortFind('.form-group select[name=Template]').chosen();
                this.context.viewPort.find('select[name=Template]').change(this.onTplChange.bind(this));


            },

            saveEdited: function () {
                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('edit_FILE');
                    data.filebody = this.editor.getValue();

                    this.context.connector.execute({
                        onSaveEdited_FILE: {
                            data: data

                        }
                    });
                }

            },
            save: function () {

                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('create_CONTENT');
                    dynamicFieldsForm = xoad.html.exportForm('dynamicFieldsForm');

                    result = this.context.connector.execute({
                        onSave_CONTENT: {
                            data: data,
                            dynamicFieldsForm: dynamicFieldsForm
                        }
                    });

                    if (result.onSave_CONTENT) {
                        this.context.navigate('showContentsList', {id: data.ancestorId});
                    }
                }

            },

            edit: function (params) {

                this.parent(params);
                this.context.tabs.addTab({
                    id: 'teditcontent',
                    name: AI.translate('templates', 'edit_template'),
                    temporal: true,
                    active: true
                }, true);


                result = this.context.connector.execute({onEdit_FILE: {id: params.id}});

                xoad.html.importForm('edit_FILE', this.context.connector.result.data);
                this.editor = ace.edit(this.context.mainViewPortFind('#filebody')[0]);
                this.editor.getSession().setUseWrapMode(true);
                this.editor.setTheme("ace/theme/textmate");
                this.editor.session.setMode("ace/mode/html");
                this.editor.setOptions({
                    fontSize: "12pt"
                });


            }

        });


        this._FILE = new _FILE(this);
    },

    deleteTemplate: function (kid, id) {
    },


    tabsStart: function () {
        var oTabs = [{
            id: 't_firstpage2',
            name: AI.translate('common', 'info'),
            temporal: true,
            active: true
        }

        ];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    deleteGroup: function () {
        this.deleteObjectGrid(this.tree);
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

        menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common', 'refresh'), false, '', '', this.refreshTree.bind(this));
        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteGroup.bind(this));

        //     $(this.treeViewPort).before('<a class="content-add-category" href="' + AI.navHashCreate(this.name, 'create_CONTENTGROUP') + '">' + AI.translate('content', 'add-category') + '<a>');

        this.tree = new dhtmlXGridObject(this.treeViewPort);
        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";

        this.tree.setHeader(AI.translate('templates', 'template_name') + ',' + AI.translate('templates', 'size'));
        this.tree.setInitWidths("300,120");
        this.tree.setColAlign("left,left");
        this.tree.setColTypes("tree,ro,ro");

        this.tree.enableDragAndDrop(true);

        this.tree.enableMultiselect(true);
        this.tree.enableContextMenu(menu);
        this.tree.init();
        this.tree.kidsXmlFile = 1;
        this.tree.attachEvent("onDynXLS", this.treeDynamicXLS.bind(this));
        this.tree.setSkin("dhx_skyblue");
        this.tree.attachEvent("onRowDblClicked", this.treeObjectClicked.bind(this));
        this.treeDynamicXLS(0);


    },
    onSearchInModule: function (result) {
        this.tabs.addTab({
            id: 'tshowSearchResults',
            name: AI.translate('common', 'search-results'),
            temporal: true,
            active: true
        }, true);

        jQuery(this.mainViewPort).addClass('grid-view');
        jQuery(this.mainViewPort).html('<div id="searchResultsContainer" style="height:800px"></div>');


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

        //menu.addNewChild(menu.topId, 0, "delete", AI.translate('common','delete'), false, '', '', this.deleteContent.bind(this));

        this.sgridlist = new dhtmlXGridObject('searchResultsContainer');

        this.sgridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.sgridlist.setHeader(AI.translate('templates', 'path') + ',' + AI.translate('templates', 'size'));
        //this.sgridlist.setHeader('Файл,Путь к файлу');

        this.sgridlist.setInitWidths("450,450,*");

        this.sgridlist.setColAlign("center,center,left");
        this.sgridlist.setColTypes("ro,ro,ro");
        this.sgridlist.setColSorting("str,str,str");
        this.sgridlist.attachEvent("onRowDblClicked", this.searchGridObjectClicked.bind(this));
        this.sgridlist.enableAutoWidth(true);
        this.sgridlist.enableContextMenu(menu);
        this.sgridlist.init();
        this.sgridlist.setSkin("modern");
        this.sgridlist.parse(result, "xjson")
    },
    searchGridObjectClicked: function (id) {

        AI.navigate(AI.navHashCreate(this.name, 'edit_FILE', {'id': id}));

    }

});