_PAGE = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_PAGE', autoCreateMethods: true});
        context.pushToTreeClickMap(this.options.objType, 'edit_PAGE');
    },

    create: function (data) {

        selectedId = this.context.tree.getSelectedRowId();
        parentData = {};


        if (selectedId) {
            objType = this.context.tree.getRowAttribute(selectedId, "obj_type");
            if (['_ROOT', '_DOMAIN'].indexOf(objType) == -1) parentData = {id: selectedId}
        }

        this.parent(parentData);

        if (parentData.id) {
            $(this.mainViewPort).find('#ancestor').val(this.context.getTreePathAncestor(['_PAGE']));
            $(this.mainViewPort).find('#ancestorId').val(selectedId);
            $(this.mainViewPort).find('.hiddenTemplates').show();

        }

        xoad.html.importForm('create_PAGE', this.context.connector.result.data);
    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {

            modules = this._slotz.exportModules();
            pageData = xoad.html.exportForm('edit_PAGE');
            this.context.connector.execute({
                onSaveEdited_PAGE: {
                    id: this.selectedId,
                    data: pageData,
                    modules: modules
                }
            });
        }

    },

    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {

            modules = this._slotz.exportModules();
            pageData = xoad.html.exportForm('create_PAGE');
            ancestor = pageData.ancestorId;
            delete   pageData.ancestorId;
            this.context.connector.execute({onSave_PAGE: {ancestor: ancestor, data: pageData, modules: modules}});
            this.context.tree.refreshItem(ancestor);

        }
    },

    edit: function (data) {
        this.parent(data);
        this.context.tabs.addTab({
            id: 'teditpage',
            name: AI.translate('pages', 'page_editing'),
            temporal: true,
            active: true
        }, true);

        data.id = parseInt(data.id);

        this.context.connector.execute({
            onEdit_PAGE: {id: data.id},
            getSlotz: {id: data.id},
            getModules: {id: data.id}
        });

        this.currentPageId = data.id;

        this._slotz = new Slotz({
            connector: this.context.connector,
            slotsInstance: this.context.connector.result.slotz,
            modulesInstance: this.context.connector.result.modules
        });
        xoad.html.importForm('edit_PAGE', this.context.connector.result.data);
        this.context.mainViewPortFind('a.showOnSite').attr('href', 'http://' + this.context.connector.result.data.pageFullPath);

        this.context.tree.loadByPath(this.context.connector.result.data.path);

    }

});

_GROUP = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_GROUP', autoCreateMethods: true});
        context.pushToTreeClickMap(this.options.objType, 'edit_GROUP');
    },


    saveEdited: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {

            modules = this.context._slotz.exportModules();
            pageData = xoad.html.exportForm('edit_GROUP');
            this.context.connector.execute({
                onSaveEdited_GROUP: {
                    id: this.selectedId,
                    data: pageData,
                    modules: modules
                }
            });
        }

    },

    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {

            modules = this.context._slotz.exportModules();
            pageData = xoad.html.exportForm('create_GROUP');
            ancestor = pageData.ancestorId;
            delete   pageData.ancestorId;
            this.context.connector.execute({onSave_GROUP: {ancestor: ancestor, data: pageData, modules: modules}});
            this.context.tree.refreshItem(ancestor);
        }
    },


    create: function (data) {

        selectedId = this.context.tree.getSelectedRowId();
        parentData = {};

        if (selectedId) {
            objType = this.context.tree.getRowAttribute(selectedId, "obj_type");
            if (['_ROOT', '_DOMAIN'].indexOf(objType) == -1) parentData = {id: selectedId}
        }

        this.parent(parentData);


        if (parentData.id) {
            $(this.mainViewPort).find('#ancestor').val(this.context.getTreePathAncestor(['_GROUP']));
            $(this.mainViewPort).find('#ancestorId').val(selectedId);
            $(this.mainViewPort).find('.hiddenTemplates').show();


        }

        xoad.html.importForm('create_GROUP', this.context.connector.result.data);
        this.context._slotz = new Slotz({connector: this.context.connector});
    },

    edit: function (data) {

        this.parent(data);
        this.context.tabs.addTab({
            id: 'teditgroup',
            name: AI.translate('pages', 'group_editing'),
            temporal: true,
            active: true
        }, true);

        data.id = parseInt(data.id);

        this.context.connector.execute({
            onEdit_GROUP: {id: data.id},
            getSlotzAll: {id: data.id},
            getModules: {id: data.id}
        });
        this.context._slotz = new Slotz({
            connector: this.context.connector,
            slotsInstance: this.context.connector.result.slotz,
            modulesInstance: this.context.connector.result.modules
        });
        xoad.html.importForm('edit_GROUP', this.context.connector.result.data);
        this.context.tree.loadByPath(this.context.connector.result.data.path);


    }

});

_DOMAIN = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_DOMAIN', autoCreateMethods: true});
        context.pushToTreeClickMap(this.options.objType, 'edit_DOMAIN');
    },

    edit: function (data) {
        this.parent(data);
        this.context.tabs.addTab({
            id: 'teditdomain',
            name: AI.translate('pages', 'domain_editing'),
            temporal: true,
            active: true
        }, true);

        data.id = parseInt(data.id);
        this.context.connector.execute({
            onEdit_DOMAIN: {id: data.id},
            getSlotzAll: {id: data.id},
            getModules: {id: data.id}
        });
        this.context._slotz = new Slotz({
            connector: this.context.connector,
            slotsInstance: this.context.connector.result.slotz,
            modulesInstance: this.context.connector.result.modules
        });


        xoad.html.importForm('edit_DOMAIN', this.context.connector.result.data);


    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            pageData = xoad.html.exportForm('create_DOMAIN');


            this.context.connector.execute({onSave_DOMAIN: {data: data}});
        }
    },

    create: function (data) {
        this.parent(data);
        xoad.html.importForm('create_DOMAIN', this.context.connector.result.data);
    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_DOMAIN');
            data.id = this.selectedId;
            modules = this.context._slotz.exportModules();

            this.context.connector.execute({onSaveEdited_DOMAIN: {data: data, modules: modules}});
        }

    }
});


_LVERSION = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_LVERSION', autoCreateMethods: true});
        context.pushToTreeClickMap(this.options.objType, 'edit_LVERSION');
    },

    edit: function (data) {

        this.parent(data);
        this.context.tabs.addTab({
            id: 'teditlang',
            name: AI.translate('pages', 'lang_editing'),
            temporal: true,
            active: true
        }, true);

        data.id = parseInt(data.id);
        this.context.connector.execute({
            onEdit_LVERSION: {id: data.id},
            getSlotzAll: {id: data.id},
            getModules: {id: data.id}
        });


        this.context._slotz = new Slotz({
            connector: this.context.connector,
            slotsInstance: this.context.connector.result.slotz,
            modulesInstance: this.context.connector.result.modules
        });

        xoad.html.importForm('edit_LVERSION', this.context.connector.result.data);


    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm('create_LVERSION');
            this.context.connector.execute({onSave_LVERSION: {data: data}});
        }
    },


    create: function (data) {
        this.parent(data);
        xoad.html.importForm('create_LVERSION', this.context.connector.result.data);
    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_LVERSION');
            data.id = this.selectedId;
            modules = this.context._slotz.exportModules();
            this.context.connector.execute({onSaveEdited_LVERSION: {data: data, modules: modules}});
        }

    }
});


_ROUTES = new Class(
    {
        Extends: CRUN,
        initialize: function (context) {
            this.parent(context, {autoCreateMethods: true});
        },

        route301switch: function (id, cid, state) {

            this.context.connector.execute({route301Switch: {id: id, state: state}});
        },


        save: function (e) {

            e.preventDefault();
            params = xoad.html.exportForm('new_route');
            this.context.connector.execute({createNewRoute: params});
            this.refreshRoutes();

        },

        del: function (kid, id) {

            this.context.connector.execute({deleteRoute: {id: id}});
            this.context.gridlistRoutes.deleteSelectedRows();
            this.refreshRoutes();

        },

        refreshRoutes: function () {
            this.context.connector.execute({routesTable: true});

            this.context.gridlistRoutes.clearAll();

            if (this.context.connector.result.data_set) {
                this.context.gridlistRoutes.parse(this.context.connector.result.data_set, "xjson")
            }
        },


        doOnCellEdit: function (stage, rowId, cellInd) {

            if (stage == 2) {
                var cellObj = this.context.gridlistRoutes.cellById(rowId, cellInd);


                if (cellInd == 1) {
                    this.context.connector.execute({
                        saveRoutePart: {
                            part: 'from',
                            id: rowId,
                            text: cellObj.getValue()
                        }
                    });
                }

                if (cellInd == 2) {
                    this.context.connector.execute({saveRoutePart: {part: 'to', id: rowId, text: cellObj.getValue()}});
                }
            }
            return true;
        }


    });


_LINK = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_LINK', autoCreateMethods: true});
        context.pushToTreeClickMap(this.options.objType, 'edit_LINK');
    },

    edit: function (data) {

        this.parent(data);
        this.context.tabs.addTab({
            id: 'teditlink',
            name: AI.translate('pages', 'link_editing'),
            temporal: true,
            active: true
        }, true);

        data.id = parseInt(data.id);
        this.context.connector.execute({onEdit_LINK: {id: data.id}});
        xoad.html.importForm('edit_LINK', this.context.connector.result.data);


    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm('create_LINK');
            this.context.connector.execute({onSave_LINK: {data: data}});
        }
    },


    create: function (data) {
        this.parent(data);
        xoad.html.importForm('create_LINK', this.context.connector.result.data);
    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_LINK');
            data.id = this.selectedId;
            this.context.connector.execute({onSaveEdited_LINK: {data: data}});
        }

    }
});
                
       