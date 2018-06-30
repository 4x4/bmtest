_FUSERSGROUP = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_FUSERSGROUP',
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
                onSave_FUSERSGROUP: {
                    data: data
                }
            });

        }
    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_FUSERSGROUP');
            result = this.context.execute
            ({
                onSaveEdited_FUSERSGROUP: {
                    id: this.selectedId,
                    data: data
                }
            });

        }

    },

    edit: function (data) {

        this.context.tabs.addTab({
            id: 'teditnews',
            name: AI.translate('fusers', 'edit_group'),
            temporal: true,
            active: true
        }, true);


        this.parent(data);

        result = this.context.execute
        ({
            onEdit_FUSERSGROUP: {
                id: data.id
            }
        });

        xoad.html.importForm('edit_FUSERSGROUP', this.context.connector.result.data);




    }

});


_FUSER = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_FUSER',
            autoCreateMethods: true
        });

        $(document).on('click', '#' + this.context.name + ' a.save-new-password', [], this.changePassword.bind(this));
        this.inputHB = TH.getTplHB('fusers', 'FUSEREXTDATA_input');
        this.checkboxHB = TH.getTplHB('fusers', 'FUSEREXTDATA_checkbox');
    },


    renderAdditionalFields:function()
    {
        this.context.execute({getAdditionalFields: true});


        if(typeof this.context.connector.result.additionalFields!='undefined'){

            html='';
            that=this;
            $(this.context.connector.result.additionalFields).each(function(k,v){

                if(v.type=='checkbox')
                {
                    html+=that.checkboxHB(v);
                }else{

                    html+=that.inputHB(v);
                }
            });

            this.context.mainViewPortFind('#additional_FUSEREXTDATA').html(html);
        }

    },

    changePassword: function (e) {
        e.preventDefault();
        data = xoad.html.exportForm('exportPassword_FUSER');
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
        xoad.html.importForm('create_FUSER', this.context.connector.result.data);
        this.renderAdditionalFields();
    },


    save: function (e) {
        e.preventDefault();
        this.parent();

        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            additionalFields = xoad.html.exportForm('additional_FUSEREXTDATA');
            this.context.connector.execute({
                onSave_FUSER: {
                    data: data,
                    additionalFields:additionalFields
                }
            });

        }

    },


    saveEdited: function (e) {
        e.preventDefault();


        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_FUSER');
            ishopData = xoad.html.exportForm('ishop_tunes');

            additionalFields = xoad.html.exportForm('additional_FUSEREXTDATA');
            result = this.context.execute
            ({
                onSaveEdited_FUSER: {
                    id: this.selectedId,
                    data: data,
                    ishopData:ishopData,
                    additionalFields:additionalFields

                }
            });

        }

    },

    edit: function (data) {


         if (data && data.id) {
                this.selectedId = data.id;
            } else if (this.context.tree)this.selectedId = this.context.tree.getSelectedRowId();

         tpl=TH.getTplHB(this.context.name, this.options.objType + '@edit');

         this.context.execute
            ({
                onEdit_FUSER: {
                    id: data.id
                }
            });


         this.context.setMainViewPort(tpl({"ishopPrices":this.context.connector.result.ishopPrices}));

         this.form = this.context.mainViewPortFind("#edit" + this.options.objType);
         this.form.validationEngine();
         this.context.mainViewPortFind('a.save').unbind('click').click(this.saveEdited.bind(this));

         xoad.html.importForm('ishop_tunes',this.context.connector.result.ishopData);

        additionalFields=this.context.connector.result.additionalFields;
        xoad.html.importForm('edit_FUSER', this.context.connector.result.data);
        this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();
        this.renderAdditionalFields();

        xoad.html.importForm('additional_FUSEREXTDATA', additionalFields);

    },

    deleteFuser: function () {
        this.context.deleteObjectGrid(this.gridlist, 'deleteFuser');
    },

    switchFuserActivity: function (rId, cInd, state) {
        cell = this.gridlist.cellById(rId, 0);
        this.context.execute({switchFuserActivity: {id: cell.getValue(), state: state}});
    },


    showFusersList: function (data) {

        this.context.tabs.addTab({
            id: 'tshowFusersList',
            name: AI.translate('fusers', 'fusers-list'),
            temporal: true,
            active: true
        }, true);


        this.context.setGridView('contentsListContainer', 750, true);
        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.context.consoleIt.bind(this));

        }

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteFuser.bind(this));
        this.gridlist = new dhtmlXGridObject('contentsListContainer');

        this.gridlist.setImagePath("x4/adm/xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('fusers', 'login') + ',' + AI.translate('fusers', 'name') + ',' + AI.translate('fusers', 'surname') + ',' + AI.translate('fusers', 'email') + ',' + AI.translate('common', 'active'));

        this.gridlist.setInitWidths("80,*,240,240,190,80");
        this.gridlist.setColAlign("center,left,left,left,left,center");
        this.gridlist.attachEvent("onCheckbox", this.switchFuserActivity.bind(this));
        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            this.context.navigate('edit_FUSER', {id: kid})
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


        this.listOrders(data.id, data.page);
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

    listOrders: function (id, page) {

        this.context.connector.execute({
            fusersTable: {
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
