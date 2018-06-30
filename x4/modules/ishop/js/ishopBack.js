ishopBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        AI.loadJs('/x4/modules/ishop/js/ishopBackObjects.js', false);
        this.setLayoutScheme('listView', {});
        this.filter={};

    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },


    CRUN: function () {
        this.TUNES = new _TUNES(this);
        this.DELIVERY = new _DELIVERY(this);
        this.PAYSYSTEM = new _PAYSYSTEM(this);
        this.STATUS = new _STATUS(this);
        this.CURRENCY = new _CURRENCY(this);
        this.STORE = new _STORE(this);

    },

    deleteNews: function (kid, id) {
        this.deleteObjectGrid(this.gridlist);
    },


    deleteGroup: function () {
        this.deleteObjectGrid(this.tree);
    },


    onCheckBox: function (rId, cInd, state) {
        cell = this.gridlist.cellById(rId, 0);
     //   this.execute({setNewsActive: {id: cell.getValue(), state: state}});
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


    list_STATUS: function () {
        this.STATUS.list();
    },


    list_CURRENCY: function () {
        this.CURRENCY.list();
    },


    list_DELIVERY: function () {
        this.DELIVERY.list();
    },

    list_STORE: function () {
        this.STORE.list();
    },

    list_PAYSYSTEMS: function () {
        this.PAYSYSTEM.list();
    },


    tabsStart: function () {


        var oTabs = [
            {
                id: 't_showOrdersList',
                name: this.translate('orders'),
                href: AI.navHashCreate(this.name, 'orders')
            },


            {
                id: 't_payment_systems',
                name: this.translate('payment_systems'),
                href: AI.navHashCreate(this.name, 'list_PAYSYSTEMS')
            },
            {
                id: 't_list_DELIVERY',
                name: this.translate('delivery'),
                href: AI.navHashCreate(this.name, 'list_DELIVERY')
            },
            ,
            {
                id: 't_statuses',
                name: this.translate('order-statuses'),
                href: AI.navHashCreate(this.name, 'list_STATUS')
            },

            {
                id: 't_stores',
                name: this.translate('stores'),
                href: AI.navHashCreate(this.name, 'list_STORE')
            },

            {
                id: 't_currency',
                name: this.translate('currencies'),
                href: AI.navHashCreate(this.name, 'list_CURRENCY')
            },

              {
                id: 't_export',
                name: this.translate('export'),
                href: AI.navHashCreate(this.name, 'exportData')
            },

            {
                id: 't_tunes',
                name: this.translate('tunes'),
                href: AI.navHashCreate(this.name, 'edit_TUNES')
            }


        ];


        this.tabs = new Tabs(this.tabsViewPort, oTabs);
        this.tabs.makeActive('t_orders');

    },


    deleteOrder: function (id, kid) {


        if (selected = this.gridlist.getSelectedId()) {
            selected = selected.split(',');
        } else {
            return;
        }


        if (selected.length > 1) {
            result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_objects'));
        }
        else {
            result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_object'));
        }


        if (result) {


            reselected = [];

            for (i = 0; i < selected.length; i++) {
                cell = this.gridlist.cellById(selected[i], 0);
                reselected.push(cell.getValue());
            }


            result = this.execute({deleteOrder: {id: reselected}});

            if (this.connector.result.deletedList) {
                this.gridlist.deleteSelectedRows();
            }
        }


    },

    saveOrder: function (e) {
        e.preventDefault();

        order = xoad.html.exportForm("editOrderForm");
        result = this.execute({saveOrder: {id: this.orderId, data: order}});

    },


    editOrder: function (id) {

        result = this.execute({editOrder: {id: id.id}});
        this.setMainViewPort(result.order);
        this.orderId = id.id;

        this.mainViewPortFind('.save').click(this.saveOrder.bind(this));


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteOrderGood.bind(this));

        this.gridlist = new dhtmlXGridObject('xGoodsList');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader(AI.translate('ishop', 'number_in_catalog')
            + ',' + AI.translate('ishop', 'goods_name')
            + ',' + AI.translate('ishop', 'sku_descript')
            + ',' + AI.translate('ishop', 'quantity')
            + ',' + AI.translate('ishop', 'price')
            + ',' + AI.translate('ishop', 'sum'));

        this.gridlist.setInitWidths("150,*,200,130,150,140");
        this.gridlist.setColAlign("center,left,left,center,left,left");
        this.gridlist.setColTypes("ro,ed,ro,ed,ed,ro");
        this.gridlist.setColSorting("int,str,str,str,str,str");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.setMultiLine(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");
        this.gridlist.attachEvent("onEditCell", this.doOnCellOrderEdit.bind(this));
        this.gridlist.attachFooter(AI.translate('ishop', 'order_sum') + ",#cspan,#cspan,<div id='ishopCountTotal'>0</div>,#cspan,<div id='ishopSumTotal'>0</div>,#cspan", ["text-align:left;"]);

        this.connector.execute({loadGoodsData: {id: id.id}});
        this.gridlist.attachEvent("onEditCell", this.getOrderSum.bind(this));

        xoad.html.importForm("editOrderForm", result.formData);

        if (this.connector.result.data_set) {
            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

        this.getOrderSum();
    },


    doOnCellOrderEdit: function (stage, rowId, cellInd, nValue) {

        if (stage == 2) {
            var cellObj = this.gridlist.cellById(rowId, cellInd);

            rowId = this.gridlist.cellById(rowId, 0).getValue();

            value = cellObj.getValue();
            if (cellInd == 1) {
                part = 'name';

            }

            if (cellInd == 3) {
                part = 'count';
            }

            if (cellInd == 4) {
                part = 'price';
                value = value.replace(" ", "")
            }


            this.connector.execute({saveGoodsParts: {orderId: this.orderId, part: part, id: rowId, value: value}});
            return nValue;
        }

        return true;


    },


    onSearchInModule: function (result) {

        this.tabs.addTab({
            id: 'tshowSearchResults',
            name: AI.translate('common', 'search-results'),
            temporal: true,
            active: true
        }, true);


        this.setGridView('orderListContainer', (window.screen.availHeight - 300), true);

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteOrder.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '', function (bid, kid) {
                cell = this.gridlist.cellById(kid, 0);
                this.navigate('editOrder', {id: cell.getValue()})

            }.bind(this)
        );


        this.sgridlist = new dhtmlXGridObject('orderListContainer');
        this.sgridlist.selMultiRows = true;
        this.sgridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.sgridlist.setHeader('id,' + AI.translate('common', 'date') + ',' + AI.translate('ishop', 'customer') + ',' + AI.translate('common', 'address') + ',' + AI.translate('common', 'phone') + ',' + AI.translate('ishop', 'sum') + ',' + AI.translate('ishop', 'status'));
        this.sgridlist.setMultiLine(true);
        this.sgridlist.setInitWidths("80,130,210,290,180,120,120");
        this.sgridlist.setColAlign("center,left,left,left,left,left,left");
        this.sgridlist.attachEvent("onCheck", this.onCheckBox.bind(this));


        this.sgridlist.attachEvent("onRowDblClicked", function (kid) {
            cell = this.sgridlist.cellById(kid, 0);
            this.navigate('editOrder', {id: cell.getValue()})

        }.bind(this));


        this.sgridlist.setColTypes("ro,ro,ro,ro,ro,ro,ro");


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


    deleteOrderGood: function () {
        this.deleteObjectGrid(this.gridlist, 'deleteOrderGood');
        return this.refreshOrderList();
    },

    refreshOrderList: function () {

        this.sumRow(4);
        this.mainViewPortFind('#ishopSumTotal').html(this.sumColumn(5));
        this.mainViewPortFind('#ishopCountTotal').html(this.sumColumn(3));
        return true;
    },

    getOrderSum: function () {
        return this.refreshOrderList();
    },


    sumColumn: function (ind) {
        var out = 0;
        for (var i = 0; i < this.gridlist.getRowsNum(); i++) {
            value = this.gridlist.cells2(i, ind).getValue();
            value = value.replace(" ", "");
            out += parseFloat(value);
        }
        return out;
    },

    sumRow: function (ind) {

        var out = 0;

        for (var i = 0; i < this.gridlist.getRowsNum(); i++) {
            count = this.gridlist.cells2(i, ind - 1).getValue();
            value = this.gridlist.cells2(i, ind).getValue();
            value = value.replace(" ", "");
            count = count.replace(" ", "");
            this.gridlist.cells2(i, ind + 1).setValue(parseFloat(value) * parseFloat(count));
        }
        return out;

    },

    setupFilter:function(enableClick){

        this.connector.execute({getOrderFilterData:true});
        xoad.html.importForm("orderFilterData", this.connector.result.filterData);
        this.mainViewPortFind('.chosen-select').chosen({width:'300px'});

        if(enableClick)
        {
          this.mainViewPortFind('#orderFilterData .btn-success').click(this.onFilterClick.bind(this));
        }
    },


    onFilterClick:function(e)
    {
        e.preventDefault();
        this.filter=xoad.html.exportForm("orderFilterData");
        this.orders({innerCall:true});
    },

    renderOrderGridCombo:function(){


        this.connector.execute({getStatusList: true});
        var combobox = this.gridlist.getCombo(7);

        for(z in this.connector.result.statuses){
            combobox.put(z,this.connector.result.statuses[z]);
        }



    },

    renderOrderGrid:function(data)
    {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        this.tabs.makeActive('t_showOrdersList');

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteOrder.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '', function (bid, kid) {
                cell = this.gridlist.cellById(kid, 0);
                this.navigate('editOrder', {id: cell.getValue()})

            }.bind(this)
        );


        this.gridlist = new dhtmlXGridObject('orderListContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('common', 'date') +',' + AI.translate('ishop', 'order_type') +  ',' + AI.translate('ishop', 'customer') + ',' + AI.translate('common', 'address') + ',' + AI.translate('common', 'phone') + ',' + AI.translate('ishop', 'sum') + ',' + AI.translate('ishop', 'status'));
        this.gridlist.setMultiLine(true);
        this.gridlist.setInitWidths("80,130,210,210,330,180,120,*");
        this.gridlist.setColAlign("center,left,left,left,left,left,left,left");
        this.gridlist.attachEvent("onCheck", this.onCheckBox.bind(this));

        this.gridlist.attachEvent("onEditCell",function(stage,id,index,value){

            if (stage==2)
            {
                    if(index==7){


                        cell = this.gridlist.cellById(id, 0);
                        this.connector.execute({setStatusList: {id:cell.getValue(),
                                                                status:value}});

                    }

            }
            return true;
        }.bind(this));


     /*   this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            cell = this.gridlist.cellById(kid, 0);
            this.navigate('editOrder', {id: cell.getValue()})

        }.bind(this));
    */

        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ro,ro,coro");
        this.renderOrderGridCombo();

      

        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");
        this.gridlist.onPage = 50;


        this.listOrders(data.page);

        this.gridlist.forEachRow(function (id) {

            val = this.gridlist.cells(id, 6).getValue();
            if (val == 'Оплачен') this.gridlist.cells(id, 6).cell.style = 'background-color:#A0FFA0';
        }.bind(this));


        pg = new paginationGrid(this.gridlist, {
            target: this.mainViewPortFind('.paginator'),
            pages: this.connector.result.pagesNum,
            url: AI.navHashCreate(this.name, 'orders', {}) //,

        });

    },

    orders: function (data) {

      if(typeof data.innerCall=='undefined')
      {
        this.setMainViewPort(this.getTpl('orders'));
        this.setupFilter(true);
      }

      this.renderOrderGrid(data);


    },

    listOrders: function (page) {

        this.connector.execute({
            ordersTable: {
                page: page,
                onPage: this.gridlist.onPage,
                filter:this.filter

            }
        });

        if (this.connector.result.data_set) {

            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

    },


    exportData:function()
    {
        this.setMainViewPort(this.getTpl('exportOrders'));
        this.setupFilter(false);
        this.mainViewPortFind('#orderFilterData .btn-success').click(this.exportDataProcess.bind(this));
    },


    exportDataProcess:function(e)
    {

      e.preventDefault();

      filter=xoad.html.exportForm("orderFilterData");
      this.connector.execute({
            exportData:
            {
                filter:filter

            }
        });

        if(this.connector.result.file)
        {
          document.location.href='/media/export/'+this.connector.result.file;
        }

    },

    buildInterface: function () {

        this.parent();
        /*--tabs--*/
        this.tabsStart();
        this.orders({});

    }

});
