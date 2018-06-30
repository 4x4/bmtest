_TASK = new Class({
    Extends: CRUN,
    initialize: function (context) {
        this.parent(context, {objType: '_TASK', autoCreateMethods: true});
    },

    create: function (data) {
        this.context.tabs.addTab({
            id: 'ttaskcreate',
            name: AI.translate('tasks', 'create_task'),
            temporal: true,
            active: true
        }, true);

        this.parent();
        xoad.html.importForm('create_TASK', this.context.connector.result.data);

    },

    saveEdited: function (e) {
        e.preventDefault();

        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('edit_TASK');


            this.context.execute({
                onSaveEdited_TASK: {
                    id: this.selectedId,
                    data: data
                }
            });

            if ($(e.target).hasClass('saveback')) {
                AI.navigate(AI.navHashCreate(this.context.name, 'showTasksList'));
            }
        }

    },
    save: function (e) {
        e.preventDefault();
        this.parent();
        if (this.validated) {
            data = xoad.html.exportForm('create_TASK');

            result = this.context.execute({
                onSave_TASK: {
                    data: data
                }
            });

            if (result.onSave_TASK) {
                this.context.navigate('showTasksList');
            }
        }

    },

    edit: function (params) {

        this.parent(params);


        this.context.tabs.addTab({
            id: 'tedittask',
            name: AI.translate('tasks', 'edit_TASK'),
            temporal: true,
            active: true
        }, true);

        result = this.context.execute({onEdit_TASK: {id: params.id}});


        this.context.mainViewPortFind('.saveback').click(this.saveEdited.bind(this));
        xoad.html.importForm("edit" + this.options.objType, result.data);


    }

});