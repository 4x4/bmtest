var _TREAD = new Class({
    Extends: CRUN,


    initialize: function (context) {

        this.parent(context, {
            objType: '_TREAD',
            autoCreateMethods: true
        });
        context.pushToTreeClickMap(this.options.objType, 'edit_TREAD');
    },


    create: function () {
        this.parent();
        xoad.html.importForm('create_TREAD', this.data);

    },

    edit: function (data) {
        this.parent(data);

        result = this.context.connector.execute({
            onEdit_TREAD: {
                id: data.id
            }
        });

        xoad.html.importForm(this.form.get(0).id, result.treadData);


    },

    save: function (e) {
        e.preventDefault();
        this.parent();


        if (this.validated) {
            data = xoad.html.exportForm("create" + this.options.objType);
            saveObject = {
                data: data
            };

            this.context.connector.execute({
                onSave_TREAD: saveObject
            });

            this.context.tree.refreshItem(ancestor);
        }
    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm("edit" + this.options.objType);
            saveObject = {
                id: this.selectedId,
                data: data
            };


            this.context.connector.execute({
                onSaveEdited_TREAD: saveObject
            });
        }

    }


});


var _COBJECT = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {
            objType: '_COBJECT',
            autoCreateMethods: true
        });
        context.pushToTreeClickMap(this.options.objType, 'edit_COBJECT');
    },


    createCommentEditorWindow: function (tpl, width) {
        if (!width) width = 600;
        this.commentEditor = AI.dhxWins.createWindow("propertyEditor", 20, 10, width, 650, 1);
        this.commentEditor.setModal(true);
        this.commentEditor.centerOnScreen();
        this.commentEditor.setText(AI.translate('comments', 'message'));
        this.commentEditor.attachEvent("onHide", function (win) {
            win.close();
        });
        this.commentEditor.attachHTMLString(TH.getTpl(this.context.name, tpl));
        this.commentEditor.button('park').hide();

        this.commentEditorContext = this.commentEditor.dhxcont;

    },

    create_COMMENT: function (e) {
        e.preventDefault();
        this.createCommentEditorWindow('_COMMENT');
        this.form = jQuery("#create_COMMENT");
        $(this.commentEditorContext).find('#create_COMMENT').validationEngine();
        $(this.commentEditorContext).find('.save').click(this.save.bind(this));
        $(this.commentEditorContext).find(".classy-editor").ClassyEdit();


    },


    edit_COMMENT: function (kid, id) {

        cell = this.gridlist.cellById(kid, 0);

        kid = cell.getValue();
        data = this.context.connector.execute(
            {
                onEdit_COMMENT: {data: {id: kid}}
            });

        this.createCommentEditorWindow('_COMMENT@edit');

        this.form = jQuery("#edit_COMMENT");

        $(this.commentEditorContext).find('#edit_COMMENT').validationEngine();
        $(this.commentEditorContext).find('.save').click(this.saveEdited.bind(this));

        this.currentId = kid;

        xoad.html.importForm('edit_COMMENT', data.commentData);
        $(this.commentEditorContext).find(".classy-editor").ClassyEdit();


    },


    switchComment: function (id, cid, state) {

        cell = this.gridlist.cellById(id, 0);
        id = cell.getValue();
        this.context.connector.execute({switchComment: {id: id, state: state}});


    },

    deleteComments: function (kid, id) {

        this.context.deleteObjectGrid(this.gridlist, 'deleteCommentsList', true);


    },

    onSaveEditedReply_COMMENT: function () {


        validated = this.context.mainViewPortFind("#reply_COMMENT").validationEngine('validate');
        if (validated) {
            data = xoad.html.exportForm("reply_COMMENT");
            saveObject = {
                id: this.currentReplyId,
                data: data
            };


            this.context.connector.execute({
                onSaveEditedReply_COMMENT: saveObject
            });

            this.commentsRefresh();
            this.commentEditor.close();

        }

    },
    onSaveReply: function (e) {

        e.preventDefault();

        validated = this.context.mainViewPortFind("#reply_COMMENT").validationEngine('validate');
        if (validated) {
            data = xoad.html.exportForm("reply_COMMENT");
            saveObject = {
                id: this.currentReplyId,
                data: data
            };


            this.context.connector.execute({
                onSaveReply_COMMENT: saveObject
            });

            this.commentsRefresh();
            this.commentEditor.close();

        }


    },

    replyComment: function (kid, id) {


        cell = this.gridlist.cellById(id, 0);
        kid = cell.getValue();


        this.createCommentEditorWindow('_COMMENT_reply', 990);

        data = this.context.connector.execute(
            {
                onEdit_COMMENT: {data: {id: kid}}
            });

        this.currentReplyId = kid;

        this.form = jQuery("#reply_COMMENT");

        $(this.commentEditorContext).find('#reply_COMMENT').validationEngine();

        $(this.commentEditorContext).find('.save').click(this.onSaveReply.bind(this));

        xoad.html.importForm('readonly_COMMENT', data.commentData);

        $(this.commentEditorContext).find(".classy-editor").ClassyEdit();


    },


    viewComments: function (id) {

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteComments.bind(this));
        menu.addNewChild(menu.topId, 0, "reply", AI.translate('comments', 'reply'), false, '', '', this.replyComment.bind(this));

        this.gridlist = new dhtmlXGridObject('cobjectsListContainer');

        this.gridlist.enableMultiline(true);
        this.gridlist.imgURL = "/x4/adm/xres/ximg/green/";
        this.gridlist.setHeader('id,' + AI.translate('common', 'date') + ',' + AI.translate('comments', 'username') + ',' + AI.translate('common', 'e-mail') + ',' + AI.translate('comments', 'message') + ',' + AI.translate('comments', 'rating') + ',' + AI.translate('common', 'active'));
        this.gridlist.setInitWidths("100,130,200,120,*,60,70");

        this.gridlist.setColAlign("center,center,left,left,left,left,center");
        this.gridlist.attachEvent("onCheckbox", this.switchComment.bind(this));


        this.gridlist.setColTypes("tree,ro,ro,ro,ro,ro,ch");


        this.gridlist.enableAutoWidth(true);
        //this.gridlist.attachEvent("onEditCell", this.doOnCellEdit.bind(this));


        this.gridlist.kidsXmlFile = 1;
        this.gridlist.attachEvent("onRowDblClicked", this.edit_COMMENT.bind(this));
        this.gridlist.attachEvent("onDynXLS", this.showReplies.bind(this));
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");

        this.currentCobjectId = id;
        this.commentsRefresh(0);

        this.context.mainViewPortFind('.add-comment').click(this.create_COMMENT.bind(this))


    },

    showReplies: function (kid, id) {

        cell = this.gridlist.cellById(kid, 0);
        kid = cell.getValue();

        this.context.connector.execute({commentsTable: {getReplies: true, id: kid}});

        if (this.context.connector.result) {
            this.gridlist.json_dataset = this.context.connector.result.data_set;
        }


        return true;

    },

    commentsRefresh: function (id) {
        this.gridlist.clearAll();
        this.context.connector.execute({commentsTable: {id: this.currentCobjectId}});
        if (this.context.connector.result.data_set) {
            this.gridlist.parse(this.context.connector.result.data_set, "xjson")
        }
    },


    edit: function (data) {
        this.parent(data);

        this.context.tabs.addTab({
            id: 'tcobjectsEdit',
            name: AI.translate('comments', 'edit_cobject'),
            temporal: true,
            active: true
        }, true);

        result = this.context.connector.execute({
            onEdit_COBJECT: {
                id: data.id
            }
        });


        this.context.mainViewPortFind('.cobjectName').text(result.cobjectData.marker);


        this.viewComments(data.id);


    },


    save: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm("create_COMMENT");
            data.cid = this.currentCobjectId;
            saveObject = {
                data: data
            };


            this.context.connector.execute({
                onSave_COMMENT: saveObject
            });

            this.commentsRefresh();
            this.commentEditor.close();

        }

    },

    saveEdited: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm("edit_COMMENT");
            saveObject = {
                id: this.currentId,
                data: data
            };


            this.context.connector.execute({
                onSaveEdited_COMMENT: saveObject
            });

            this.commentsRefresh();
            this.commentEditor.close();

        }

    }


});

