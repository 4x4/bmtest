commentsBack = new Class({
    Extends: _xModuleBack,


    initialize: function (name) {
        this.setName(name);
        this.parent();
        this.onPageDefault = 20;
        this.objTypeScope = ['_TREAD', '_COBJECT'];
        this.loadDefaultTpls = ['_COBJECT_list'];

        this.setLayoutScheme('treeView', {
            treeSize: 'md'

        });

        AI.loadJs('/x4/modules/comments/js/commentsBackObjects.js', true);


    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },

    CRUN: function () {
        this.TREAD = new _TREAD(this);
        this.COBJECT = new _COBJECT(this);

    },


    switchCobject: function (id, cid, state) {
        if (cid == 3) {
            this.connector.execute({switchCobjectActive: {id: id, state: state}});
        }

        if (cid == 4) {
            this.connector.execute({switchCobjectClosed: {id: id, state: state}});
        }

    },

    doOnCellEdit: function (stage, rowId, cellInd) {
        if (stage == 2) {
            var cellObj = this.gridlist.cellById(rowId, cellInd);

            if (cellInd == 3) {
                this.connector.execute({save_comment_part: {part: 'Header', id: rowId, value: cellObj.getValue()}});
            }

            if (cellInd == 4) {
                this.connector.execute({save_comment_part: {part: 'Message', id: rowId, value: cellObj.getValue()}});
            }

            this.connector.execute({save_comment_part: {part: 'Header', id: rowId, value: cellObj.getValue()}});
        }
        return true;
    },

    view_comments_external: function (id, module) {
        this.connector.execute({get_comment_by_module: {id: id, module: module}});

        if (this.connector.result.id) {
            this.view_comments(this.connector.result.id);
        }

    },


    new_comments: function (id) {
        XTR_main.set_rightside_eform(XTR_main.get_tpl(this.module_name, 'show_tread', true), 'b');
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();
        menu.addNewChild(menu.topId, 0, "delete", _lang_common['delete'], false, '', '', this.del_comment.bind(this));

        this.gridlist = new dhtmlXGridObject('t-container');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + _lang_common['date'] + ',' + _lang_comments['comment_object'] + ',' + _lang_comments['UserName'] +
            ',' + _lang_comments['Header'] + ',' + _lang_comments['Message'] + ',' + _lang_common['active']);

        this.gridlist.setInitWidths("70,80,100,150,200,*,70");
        this.gridlist.setColAlign("center,center,center,center,center,left");
        this.gridlist.attachEvent("onCheckbox", this.switch_comment.bind(this));
        this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
        this.gridlist.setSelectFilterLabel(5, _select_filter_yes_no);
        this.gridlist.setColTypes("ro,ro,ro,ro,ed,txt,ch");
        this.gridlist.setColSorting("int,date_rus,,str,str,str,str");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.attachEvent("onEditCell", this.doOnCellEdit.bind(this));
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        this.connector.execute({new_comments_table: true});
        if (this.connector.result.data_set) {
            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }
    },


    showCobjectsList: function (data) {

        this.tabs.addTab({
            id: 'tcobjectsListContainer',
            name: AI.translate('comments', 'comments_list'),
            temporal: true,
            active: true
        }, true);


        this.setGridView('cobjectsListContainer', 750, true);
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteCobject.bind(this));
        this.gridlist = new dhtmlXGridObject('cobjectsListContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('comments', 'module') + ',' + AI.translate('comments', 'comment_object') + ',' + AI.translate('common', 'active') + ',' + AI.translate('comments', 'closed'));

        this.gridlist.setInitWidths("100,150,*,140,140");
        this.gridlist.setColAlign("center,left,left,center,center");
        this.gridlist.attachEvent("onCheckbox", this.switchCobject.bind(this));
        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            this.navigate('edit_COBJECT', {id: kid})
        }.bind(this));
        this.gridlist.setColTypes("ro,ro,ro,ch,ch");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");


        this.listCobjects(data.id, data.page);

        var pg = new paginationGrid(this.gridlist, {
            target: this.mainViewPortFind('.paginator'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'showCobjectsList', {id: data.id}) //,

        });


    },


    listCobjects: function (id, page) {

        this.connector.execute({
            cobjectsTable: {
                id: id,
                page: page,
                onPage: this.gridlist.onPage
            }
        });

        if (this.connector.result.data_set) {

            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

    },


    lastAddedCommentes: function () {

    },


    tabsStart: function () {
        var oTabs = [
            {
                id: 'tcreate_TREAD',
                name: AI.translate('comments', 'add_tread'),
                href: AI.navHashCreate(this.name, 'create_TREAD')
            }

        ];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    deleteTread: function (kid, id) {

        this.deleteObjectGrid(this.tree, 'deleteTread');

    },

    deleteCobject: function (kid, id) {

        this.deleteObjectGrid(this.gridlist, 'deleteCobject');


    },


    buildInterface: function () {

        this.parent();
        this.tabsStart();


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", AI.translate('common', 'console-it'), false, '', '', this.consoleIt.bind(this));
        }

//        menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common','refresh'), false, '', '', this.refreshTree.bind(this));
        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteTread.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '', function (kid, id) {
            this.navigate('edit_TREAD', {id: id})
        }.bind(this));

        //     $(this.treeViewPort).before('<a class="content-add-category" href="' + AI.navHashCreate(this.name, 'create_NEWSGROUP') + '">' + AI.translate('content', 'add-category') + '<a>');

        $(this.treeViewPort).css({minHeight: (window.screen.availHeight - 230)});
        this.tree = new dhtmlXGridObject(this.treeViewPort);
        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";
        this.tree.setHeader(AI.translate('comments', 'treads'));

        //$(this.treeViewPort).find('.hdr').hide();

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
        this.tree.attachEvent("onRowDblClicked", function (kid) {
            this.navigate('showCobjectsList', {id: kid})
        }.bind(this));
        this.treeDynamicXLS(0);
        $(this.tree.entBox).find('.ev_dhx_skyblue ').hide();
        this.tree.openItem(1);


    },

    destructor: function () {

        $(this.module_name + "_treebox").hide();
        this.tabs.destructor();
        XTR_main.set_rightside('');
    }


});