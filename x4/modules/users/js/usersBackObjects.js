_USERSGROUP = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_USERSGROUP',
            autoCreateMethods: true
        });


    },

    deleteGroup: function () {
        this.context.deleteObjectGrid(this.context.tree);
    },

    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            this.context.connector.execute({
                onSave_USERSGROUP: {
                    data: data
                }
            });

            this.context.tree.refreshItem(1);

        }
    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_USERSGROUP');
            result = this.context.execute
            ({
                onSaveEdited_USERSGROUP: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (data) {

        this.context.tabs.addTab({
            id: 'tedituser',
            name: AI.translate('users', 'edit_group'),
            temporal: true,
            active: true
        }, true);


        this.parent(data);

        result = this.context.execute
        ({
            onEdit_USERSGROUP: {
                id: data.id
            }
        });

        xoad.html.importForm('edit_USERSGROUP', this.context.connector.result.data);


    }

});


_USER = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_USER',
            autoCreateMethods: true
        });

        $(document).on('click', '#' + this.context.name + ' a.save-new-password', [], this.changePassword.bind(this));
    },


    changePassword: function (e) {
        e.preventDefault();
        data = xoad.html.exportForm('exportPassword_USER');
        data.id = this.selectedId;

        this.context.connector.execute({
            changeUserPassword: {
                data: data

            }


        });

        this.context.mainViewPortFind('#newPassword').val('');
    },


    create: function (data) {

        this.parent();
        xoad.html.importForm('create_USER', this.context.connector.result.data);
    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            this.context.connector.execute({
                onSave_USER: {
                    data: data
                }
            });

        }

    },


    saveEdited: function (e) {
        e.preventDefault();
        
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_USER');

            data['permissions']=xoad.html.exportForm('permissions');
            result = this.context.execute
            ({
                onSaveEdited_USER: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (data) {

        result = this.context.execute
        ({
            onEdit_USER: {
                id: data.id
            }
        });

        this.selectedId=data.id;
        
        tpl=TH.getTplHB(this.context.name, this.options.objType + '@edit');

        this.context.setMainViewPort(tpl({"modules":this.context.connector.result.modules}));

        this.form = this.context.mainViewPortFind("#edit" + this.options.objType);
        this.form.validationEngine();
        //this.context.mainViewPortFind("#edit"+this.options.objType+' a.save').unbind('click').click(this.saveEdited.bind(this));
        this.context.mainViewPortFind('a.save').unbind('click').click(this.saveEdited.bind(this));

        xoad.html.importForm('permissions', this.context.connector.result.data.permissions);

        xoad.html.importForm('edit_USER', this.context.connector.result.data);
        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();

    },

    deleteUser: function () {
        this.context.deleteObjectGrid(this.gridlist, 'deleteUser');
    },


    showUsersList: function (data) {

        this.context.tabs.addTab({
            id: 'tshowUsersList',
            name: AI.translate('users', 'fusers-list'),
            temporal: true,
            active: true
        }, true);


        this.context.setGridView('contentsListContainer', 750, true);
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.context.consoleIt.bind(this));

        }

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteUser.bind(this));
        this.gridlist = new dhtmlXGridObject('contentsListContainer');

        this.gridlist.setImagePath("x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('users', 'login') + ',' + AI.translate('users', 'name') + ',' + AI.translate('users', 'surname') + ',' + AI.translate('users', 'email') + ',' + AI.translate('common', 'active'));

        this.gridlist.setInitWidths("80,200,240,240,190,80");
        this.gridlist.setColAlign("center,left,left,left,left,center");
        //this.gridlist.attachEvent("onCheckbox",this.switchUserActivity.bind(this));
        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            this.context.navigate('edit_USER', {id: kid})
        }.bind(this));
        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ch");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.enableMultiselect(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.onPage = 200;
        this.gridlist.setSkin("modern");


        this.gridlist.rowToDragElement = function (id) {
            if (this.cells(id, 2).getValue() != "") {
                return this.cells(id, 2).getValue() + "/" + this.cells(id, 1).getValue();
            } else {
                return this.cells(id, 1).getValue();
            }
        };


        this.listUsers(data.id, data.page);
        var pg = new paginationGrid(this.gridlist, {
            target: this.context.mainViewPortFind('.paginator'),
            pages: this.context.connector.result.pagesNum,
            url: AI.navHashCreate(this.context.name, 'showUsersList', {id: data.id}) //,

        });

        this.gridlist.gridToTreeElement = function (tree, fakeID, gridID, treeID) {
            /*                        this.connector.execute({changeAncestor:{ancestor:treeID,id:gridID}});            
             if(this.connector.result.dragOK)
             {
             XTR_main.set_result(_lang_fusers['user_moved']);
             return true;
             }else{*/
            return -1;
            /*}*/

        }.bind(this);

    },

    listUsers: function (id, page) {

        this.context.connector.execute({
            usersTable: {
                id: id,
                page: page,
                onPage: this.gridlist.onPage
            }
        });

        if (this.context.connector.result.data_set) {

            this.gridlist.parse(this.context.connector.result.data_set, "xjson")
        }

    }


});