tasksBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('listView', {});
        this.loadDefaultTpls = ['_TASK', 'tasksList'];
        AI.loadJs('/x4/modules/tasks/js/tasksBackObjects.js', true);
    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },


    CRUN: function () {
        this.TASK = new _TASK(this);
    },

    deleteTasks: function (kid, id) {

        selected = this.gridlist.getSelectedRowId(true);
        if (selected.length > 0) {
            cells = [];
            for (i = 0; i < selected.length; i++) {
                cell = this.gridlist.cellById(selected[i], 0);
                cells.push(cell.getValue());
            }

            this.execute({deleteTasks: {id: cells}});

        }

        if (this.connector.result.deleted) {
            this.gridlist.deleteSelectedRows();
        }

    },

    onCheckBox: function () {


    },

    showTasksList: function (data) {

        this.setMainViewPort(this.getTpl('tasksList'));
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteTasks.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '',

            function (bid, kid) {
                cell = this.gridlist.cellById(kid, 0);
                this.navigate('edit_TASK', {id: cell.getValue()})

            }.bind(this)
        );

        this.gridlist = new dhtmlXGridObject('tasksListContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.enableMultiline(true);
        this.gridlist.setImagePath("/_adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('tasks', 'task_name') + ',' + AI.translate('tasks', 'task_method') + ',' + AI.translate('tasks', 'task_period') + ',' + AI.translate('tasks', 'last_launch'), +AI.translate('common', 'active'));

        this.gridlist.setInitWidths("80,300,*,110,280,180");
        this.gridlist.setColAlign("center,left,left,left,left,left");
        this.gridlist.attachEvent("onCheck", this.onCheckBox.bind(this));

        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            cell = this.gridlist.cellById(kid, 0);
            this.navigate('edit_TASK', {id: cell.getValue()})

        }.bind(this));


        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ch");

        this.gridlist.enableAutoWidth(true);

        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");
        this.gridlist.onPage = 50;

        this.listTasks(data.page);

        var pg = new paginationGrid(this.gridlist, {
            target: this.mainViewPortFind('.paginator'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'tasks', {id: data.id}) //,

        });


    },


    listTasks: function (page) {

        this.connector.execute({
            tasksTable: {
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
                id: 'tshowTasksList',
                name: AI.translate('tasks', 'tasks-list'),
                href: AI.navHashCreate(this.name, 'showTasksList'),
                active: true
            }

        ];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },


    buildInterface: function () {

        this.parent();
        /*--tabs--*/
        this.tabsStart();
        /*--menu--*/


        this.showTasksList({data: {page: 1}});

    }

});