_DELIVERY = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_DELIVERY',
            autoCreateMethods: true
        });

    },

    deleteDelivery: function (kid, id) {
        this.context.deleteObjectGrid(this.gridlistDelivery, 'deleteDelivery');
    },

    list: function (data) {

        this.context.setMainViewPort(this.context.getTpl('_DELIVERY_list'));


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        // menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteDelivery.bind(this));

        this.gridlistDelivery = new dhtmlXGridObject('delivery_list');
        this.gridlistDelivery.selMultiRows = true;
        this.gridlistDelivery.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistDelivery.setHeader('id,' + AI.translate('common', 'name') + ',' + AI.translate('ishop', 'delivery_id') + ',' + AI.translate('common', 'description'));
        this.gridlistDelivery.setInitWidths("70,200,200,400");
        this.gridlistDelivery.setColAlign("center,left,left,left");
        this.gridlistDelivery.setColTypes("ro,ro,ro,ro,ro");
        this.gridlistDelivery.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlistDelivery.enableAutoWidth(true);
        this.gridlistDelivery.enableContextMenu(menu);
        this.gridlistDelivery.init();
        this.gridlistDelivery.setSkin("modern");


        this.context.connector.execute({deliveryList: true});

        if (this.context.connector.result.data_set) {
            this.gridlistDelivery.parse(this.context.connector.result.data_set, "xjson")
        }


    },

    create: function (data) {

        this.parent();

    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            this.context.connector.execute({
                onSave_DELIVERY: {
                    data: data
                }
            });

        }

    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_DELIVERY');
            result = this.context.execute
            ({
                onSaveEdited_DELIVERY: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (id, kid) {
        data = {id: id};
        this.parent(data);

        result = this.context.execute
        ({
            onEdit_DELIVERY: {
                id: id
            }
        });

        xoad.html.importForm('edit_DELIVERY', this.context.connector.result.data);


    }

});


_PAYSYSTEM = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_PAYSYSTEM',
            autoCreateMethods: true
        });

    },

    list: function (data) {

        this.context.setMainViewPort(this.context.getTpl('_PAYSYSTEM_list'));


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        this.gridlistPaysystems = new dhtmlXGridObject('paysystems_list');
        this.gridlistPaysystems.selMultiRows = true;
        this.gridlistPaysystems.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistPaysystems.setHeader('id,' + AI.translate('common', 'name') + ',' + this.context.translate('Priority') + ',' + this.context.translate('Active'));
        this.gridlistPaysystems.setInitWidths("120,500,90,90");
        this.gridlistPaysystems.setColAlign("center,left,left,left");
        this.gridlistPaysystems.setColTypes("ro,ro,ro,ch");
        this.gridlistPaysystems.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlistPaysystems.enableAutoWidth(true);
        this.gridlistPaysystems.enableContextMenu(menu);
        this.gridlistPaysystems.init();
        this.gridlistPaysystems.setSkin("modern");


        this.context.connector.execute({loadPaysystemsList: true});

        if (this.context.connector.result.data_set) {
            this.gridlistPaysystems.parse(this.context.connector.result.data_set, "xjson")
        }


    },


    save: function (e) {

        e.preventDefault();
        this.parent();

        if (this.validated) {

            data = xoad.html.exportForm("edit_PAYSYSTEM");
            this.context.connector.execute({
                onSave_PAYSYSTEM: {
                    data: data
                }
            });

        }

    },


    edit: function (id, kid) {
        data = {id: id};


        result = this.context.execute
        ({
            onEdit_PAYSYSTEM: {
                id: id
            }
        });

        this.context.setMainViewPort(this.context.connector.result.tpl);
        xoad.html.importForm('edit_PAYSYSTEM', this.context.connector.result.data);
        this.context.mainViewPortFind('a.save').unbind('click').click(this.save.bind(this));

    }

});


_TUNES = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_TUNES',
            autoCreateMethods: true
        });

    },

    save: function (e) {

        e.preventDefault();
        this.parent();

        if (this.validated) {

            data = xoad.html.exportForm("edit_TUNES");
            this.context.connector.execute({
                onSave_TUNES: {
                    data: data
                }
            });

        }

    },


    edit: function (id, kid) {
        data = {id: id};
        this.parent(data);

        result = this.context.execute
        ({
            onEdit_TUNES: {
                id: id
            }
        });

        xoad.html.importForm('edit_TUNES', this.context.connector.result.data);
        this.context.mainViewPortFind('a.save').unbind('click').click(this.save.bind(this));

    }

});

_STATUS = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_STATUS',
            autoCreateMethods: true
        });

    },

    deleteStatus: function (kid, id) {
        this.context.deleteObjectGrid(this.gridlistStatus, 'deleteStatus');
    },

    list: function (data) {

        this.context.setMainViewPort(this.context.getTpl('_STATUS_list'));


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        // menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteStatus.bind(this));

        this.gridlistStatus = new dhtmlXGridObject('statuses_list');
        this.gridlistStatus.selMultiRows = true;
        this.gridlistStatus.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistStatus.setHeader('id,' + AI.translate('common', 'name') + ',' + AI.translate('ishop', 'status_id'));
        this.gridlistStatus.setInitWidths("70,200,200");
        this.gridlistStatus.setColAlign("center,left,left");
        this.gridlistStatus.setColTypes("ro,ro,ro,ro");
        this.gridlistStatus.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlistStatus.enableAutoWidth(true);
        this.gridlistStatus.enableContextMenu(menu);
        this.gridlistStatus.init();
        this.gridlistStatus.setSkin("modern");


        this.context.connector.execute({statusList: true});

        if (this.context.connector.result.data_set) {
            this.gridlistStatus.parse(this.context.connector.result.data_set, "xjson")
        }


    },

    create: function (data) {

        this.parent();

    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            this.context.connector.execute({
                onSave_STATUS: {
                    data: data
                }
            });

        }

    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_STATUS');
            result = this.context.execute
            ({
                onSaveEdited_STATUS: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (id, kid) {
        data = {id: id};
        this.parent(data);

        result = this.context.execute
        ({
            onEdit_STATUS: {
                id: id
            }
        });

        xoad.html.importForm('edit_STATUS', this.context.connector.result.data);


    }

});


_CURRENCY = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_CURRENCY',
            autoCreateMethods: true
        });

    },

    deleteStatus: function (kid, id) {
        this.context.deleteObjectGrid(this.gridlistStatus, 'deleteCurrency');
    },

    getCurrentCourses: function (e) {
        e.preventDefault();

        this.context.connector.execute({getCurrentCourses: true});

    },
    list: function (data) {

        this.context.setMainViewPort(this.context.getTpl('_CURRENCY_list'));


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        // menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteStatus.bind(this));

        this.gridlistStatus = new dhtmlXGridObject('currency_list');
        this.gridlistStatus.selMultiRows = true;
        this.gridlistStatus.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistStatus.setHeader('id,' + AI.translate('common', 'name') + ',' + AI.translate('ishop', 'currency_id') + ',' + AI.translate('ishop', 'rate'), ',' + AI.translate('ishop', 'is_main'));
        this.gridlistStatus.setInitWidths("70,200,150,150,100");
        this.gridlistStatus.setColAlign("center,left,left,left,left");
        this.gridlistStatus.setColTypes("ro,ro,ro,ro,ro");
        this.gridlistStatus.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlistStatus.enableAutoWidth(true);
        this.gridlistStatus.enableContextMenu(menu);
        this.gridlistStatus.init();
        this.gridlistStatus.setSkin("modern");

        $(document).on('click', '.refreshCourses', [], this.getCurrentCourses.bind(this));
        this.context.connector.execute({currencyList: true});

        if (this.context.connector.result.data_set) {
            this.gridlistStatus.parse(this.context.connector.result.data_set, "xjson")
        }


    },

    create: function (data) {

        this.parent();

    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            this.context.connector.execute({
                onSave_CURRENCY: {
                    data: data
                }
            });

        }

    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_CURRENCY');
            result = this.context.execute
            ({
                onSaveEdited_CURRENCY: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (id, kid) {
        data = {id: id};
        this.parent(data);

        result = this.context.execute
        ({
            onEdit_CURRENCY: {
                id: id
            }
        });

        xoad.html.importForm('edit_CURRENCY', this.context.connector.result.data);


    }

});




_STORE = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_STORE',
            autoCreateMethods: true
        });

    },

    deleteStore: function (kid, id) {
        this.context.deleteObjectGrid(this.gridlistStore, 'deleteStore');
    },

    list: function (data) {

        this.context.setMainViewPort(this.context.getTpl('_STORE_list'));


        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        // menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));
        menu.addNewChild(menu.topId, 0, 'delete', AI.translate('common', "delete"), false, '', '', this.deleteStore.bind(this));

        this.gridlistStore = new dhtmlXGridObject('store_list');
        this.gridlistStore.selMultiRows = true;
        this.gridlistStore.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
        this.gridlistStore.setHeader('id,' + AI.translate('common', 'name') + ',' + AI.translate('ishop', 'store_id') + ',' + AI.translate('common', 'address'));
        this.gridlistStore.setInitWidths("70,200,200,400");
        this.gridlistStore.setColAlign("center,left,left,left");
        this.gridlistStore.setColTypes("ro,ro,ro,ro,ro");
        this.gridlistStore.attachEvent("onRowDblClicked", this.edit.bind(this));
        this.gridlistStore.enableAutoWidth(true);
        this.gridlistStore.enableContextMenu(menu);
        this.gridlistStore.init();
        this.gridlistStore.setSkin("modern");
        this.context.connector.execute({storeList: true});
        if (this.context.connector.result.data_set) {
            this.gridlistStore.parse(this.context.connector.result.data_set, "xjson")
        }


    },

    create: function (data) {

        this.parent();

    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            this.context.connector.execute({
                onSave_STORE: {
                    data: data
                }
            });

            this.context.navigate('list_STORE');

        }

    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_STORE');
            result = this.context.execute
            ({
                onSaveEdited_STORE: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (id, kid) {
        data = {id: id};
        this.parent(data);

        result = this.context.execute
        ({
            onEdit_STORE: {
                id: id
            }
        });

        xoad.html.importForm('edit_STORE', this.context.connector.result.data);


    }

});

        
        
        
        
        
        
        
        
