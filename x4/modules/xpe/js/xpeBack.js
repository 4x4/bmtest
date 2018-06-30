xpeBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('listView', {});
        this.loadDefaultTpls = ['_XPEROLE', 'xpeUsers', 'xpePersonalizationSheme'];
        AI.loadJs('/x4/modules/xpe/js/xpeBackObjects.js', true);
        AI.loadJs('/x4/adm/xjs/_components/jq.qb/query-builder.min.js', true);
        AI.loadJs('/x4/adm/xjs/_components/jq.qb/query-builder.ru.js', true);

        $("<link/>", {
            rel: "stylesheet",
            type: "text/css",
            href: "/x4/adm/xjs/_components/jq.qb/pluginSrc/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css"
        }).appendTo("head");

        $("<link/>", {
            rel: "stylesheet",
            type: "text/css",
            href: "/x4/adm/xjs/_components/jq.qb/pluginSrc/selectize/css/selectize.bootstrap3.css"
        }).appendTo("head");

        AI.loadJs('/x4/adm/xjs/_components/jq.qb/pluginSrc/bootbox/bootbox.js', true);

        AI.loadJs('/x4/adm/xjs/_components/jq.qb/pluginSrc/selectize/js/standalone/selectize.min.js', true);

        AI.loadJs('/x4/adm/xjs/_components/jq.qb/pluginSrc/bootstrap-select/js/bootstrap-select.min.js', true);

        AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/bt-selectpicker/plugin.js', true);

        AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/bt-checkbox/plugin.js', true);


        /*AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/bt-tooltip-errors/plugin.js', true);        
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/sortable/plugin.js', true);
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/filter-description/plugin.js', true);
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/bt-selectpicker/plugin.js', true);
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/unique-filter/plugin.js', true);
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/bt-checkbox/plugin.js', true);
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/invert/plugin.js', true);
         AI.loadJs('/x4/adm/xjs/_components/jq.qb/plugins/not-group/plugin.js', true);      */

        this.schemesItems = {};
        this.objTypeScope = ['_CAMPAIGN','_XPEROLE'];
        this.pushToTreeClickMap('_CAMPAIGN', 'edit_CAMPAIGN');
        this.pushToTreeClickMap('_XPEROLE', 'edit_XPEROLE');
    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },


    CRUN: function () {
        this.SCHEMEGROUP = new _SCHEMEGROUP(this);
        this.SCHEMEITEM = new _SCHEMEITEM(this);
        this.XPEROLE = new _XPEROLE(this);
        this.AFFECTOR = new _AFFECTOR(this);
        this.CAMPAIGN= new _CAMPAIGN(this);

    },


    showXpeUsersList: function (data) {

        this.setMainViewPort(this.getTpl('tasksList'));
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        //menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteTasks.bind(this));
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

        this.gridlist.setInitWidths("80,300,*,110,180,180");
        this.gridlist.setColAlign("center,left,left,left,center,left");
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


    copyObj: function (id, kid, src, tree) {

        this.copyObjectToBufferGrid(this.gridlist);

    },

    pasteObj: function (id, kid, keys, gc) {
        this.pasteObjectGrid(this.gridlist);
    },


    deleteScheme: function () {
        this.deleteObjectGrid(this.gridlist);
    },


    deleteXpeRole: function () {
        this.deleteObjectGrid(this.gridlistxpe, 'deleteXpeRole');
    },


    campaignDynamicXLS: function (id) {
        this.connector.execute({campaignDynamicXLS: {id: id}});
        if (this.connector.result) {
            if (id == 0) {
                this.tree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.tree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },



    deleteCampaigns:function(){
        this.deleteObjectGrid(this.tree,'deleteCampaigns');
    },

    showXpeRoles: function () {

        this.setMainViewPort(this.getTpl('showXpeRoles'));
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteCampaigns.bind(this));

        menu.addNewChild(menu.topId, 0, "edit", AI.translate('xpe', 'add_xperole'), false, '', '', function (id, kid) {
            this.navigate('create_XPEROLE', {id: kid});
        }.bind(this));




        ps=this.mainViewPortFind('#showXpeRoles');
        this.tree = new dhtmlXGridObject(ps[0]);

        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";
        this.tree.setHeader(AI.translate('xpe', 'page_name') + ',' + AI.translate('xpe', 'link')+',' + AI.translate('xpe', 'link')+',' + AI.translate('xpe', 'link'));

            ps.find('.hdr').hide();

        this.tree.setInitWidths("550,60,200,200");
        this.tree.setColAlign("left,left,left,left");
        this.tree.setColTypes("tree,ro,ro,ro");
        this.tree.enableDragAndDrop(true);
        //   tree.enableEditEvents(false,false,true);
        this.tree.attachEvent("onDrag", this.onTreeGridDrag.bind(this));
        this.tree.setDragBehavior('complex-next');
        this.tree.enableMultiselect(true);
        this.tree.enableContextMenu(menu);

        this.tree.init();
        this.tree.kidsXmlFile = 1;

        this.tree.attachEvent("onDynXLS", this.campaignDynamicXLS.bind(this));
        this.tree.setSkin("dhx_skyblue");
        this.tree.attachEvent("onRowDblClicked", this.treeObjectClicked.bind(this));
        this.campaignDynamicXLS(0);
        $(this.tree.entBox).find('.ev_dhx_skyblue ').hide();
        this.tree.openItem('01');






        /*this.setMainViewPort(this.getTpl('showXpeRoles'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteXpeRole.bind(this));

        this.gridlistxpe = new dhtmlXGridObject('showXpeRoles');
        this.gridlistxpe.selMultiRows = true;
        this.gridlistxpe.setImagePath("xres/ximg/grid/imgs/");
        this.gridlistxpe.setHeader('id,' + AI.translate('xpe', 'xpe-name-roles'));

        this.gridlistxpe.setInitWidths("120,450");
        this.gridlistxpe.setColAlign("center,left");
        this.gridlistxpe.attachEvent("onRowDblClicked", function (kid) {
            this.navigate('edit_XPEROLE', {id: kid})
        }.bind(this));
        this.gridlistxpe.setColTypes("ro,ro");
        this.gridlistxpe.setColSorting("int,str");
        this.gridlistxpe.enableAutoWidth(true);
        this.gridlistxpe.enableDragAndDrop(true);
        this.gridlistxpe.enableContextMenu(menu);
        this.gridlistxpe.init();
        this.gridlistxpe.setSkin("modern");

        this.refreshXpeRolesList();*/

    },

    refreshXpeRolesList: function () {
        this.gridlistxpe.clearAll();
        this.connector.execute({xpeRolesTable: true});
        if (this.connector.result.data_set) {
            this.gridlistxpe.parse(this.connector.result.data_set, "xjson")
        }

    },


    showPersonalizationSchema: function () {

        this.setMainViewPort(this.getTpl('xpePersonalizationSheme'));

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        menu.addNewChild(menu.topId, 0, "copy", AI.translate('common', 'copy'), false, '', '', this.copyObj.bind(this));
        menu.addNewChild(menu.topId, 0, "paste", AI.translate('common', 'paste'), false, '', '', this.pasteObj.bind(this));
        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteScheme.bind(this));

        this.gridlist = new dhtmlXGridObject('personalizationScheme');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('xpe', 'xpe-name') + ',' + AI.translate('xpe', 'xpe-alias'));

        this.gridlist.setInitWidths("120,330,*");
        this.gridlist.setColAlign("center,left,left");

        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            this.navigate('edit_SCHEMEGROUP', {id: kid})
        }.bind(this));
        this.gridlist.setColTypes("ro,ro,ro");
        this.gridlist.setColSorting("int,str,str");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        this.refreshContentsList();

    },


    refreshContentsList: function () {
        this.gridlist.clearAll();
        this.connector.execute({schemeGroupsTable: 1});
        if (this.connector.result.data_set) {
            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

    },

    showXpeActiveUsersList:function()
    {

    },

    tabsStart: function () {

        var oTabs = [


            {
                id: 'tshowPersonalizationSchema',
                name: AI.translate('xpe', 'personalization-sсhema'),
                href: AI.navHashCreate(this.name, 'showPersonalizationSchema'),
                active: true
            },

            {
                id: 'tXpeRoles',
                name: AI.translate('xpe', 'xpe-roles'),
                href: AI.navHashCreate(this.name, 'showXpeRoles'),

            },
            {
                id: 'tstatistics',
                name: AI.translate('xpe', 'statistics'),
                href: AI.navHashCreate(this.name, 'statistics')

            },

            {
                id: 'tXpeActiveUsersList',
                name: AI.translate('xpe', 'users-list'),
                href: AI.navHashCreate(this.name, 'showXpeActiveUsersList')

            }

        ];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    statistics:function()
    {

        this.setMainViewPort(this.getTpl('statistics'));

        this.connector.execute({getRolesStats: true});
        if (this.connector.result.stats) {
            var chart = AmCharts.makeChart( "statGraph", {
                "type": "serial",
                "theme": "none",
                "dataProvider":this.connector.result.stats,
                "valueAxes": [ {
                    "gridColor": "#FFFFFF",
                    "gridAlpha": 0.2,
                    "dashLength": 0
                } ],
                "gridAboveGraphs": true,
                "startDuration": 1,
                "graphs": [ {
                    "balloonText": "[[category]]: <b>[[value]]</b>",
                    "fillAlphas": 0.8,
                    "lineAlpha": 0.2,
                    "type": "column",
                    "valueField": "visits"
                } ],
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                "categoryField": "role",
                "categoryAxis": {
                    "gridPosition": "start",
                    "gridAlpha": 0,
                    "tickPosition": "start",
                    "tickLength": 20
                },
                "export": {
                    "enabled": true
                }

            } );


        }




    },

    start: function () {

        this.navigate('showPersonalizationSchema');
    },

    buildInterface: function () {

        this.parent();
        /*--tabs--*/
        this.tabsStart();
        /*--menu--*/


    }

});