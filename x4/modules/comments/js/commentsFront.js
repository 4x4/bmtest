moduleRegister.pushModule('comments');
//validate = $("#userData").validationEngine();
var commentsFront = new Class({

    Extends: x4FrontModule,
    preventJquery: false,
    onBeforeSubmit: null,

    constructor: function () {
        this.parent('comments');

    },

    setOnBeforeSumbit: function (func) {
        this.onBeforeSubmit = func;
    },

    addCommentRoute: function (route, id, tread, marker, comment) {
        this.connector.execute(
            {
                addCommentRoute: {route: route, id: id, tread: tread, marker: marker, comment: comment}
            }
        );

    },


    autoSubmit: function (e) {
        e.preventDefault();


        element = $(e.target);
        form = element.closest('form');
        data = element.data('comment');

        if (typeof this.onBeforeSubmit == 'function') {
            if (!this.onBeforeSubmit(form, e)) {
                return;
            }
        }

        formData = xoad.html.exportForm(form.get(0));
        this.addCommentRoute(data['route'], data['cobjectId'], data['tread'], data['marker'], formData);

        location.reload(true);
    },

    jqueryRun: function () {

        if (this.preventJquery) return;

        if ($('.x4-add-comment-form').length > 0) {
            $('.x4-add-comment-form').find('.add-comment').click(this.autoSubmit.bind(this));
        }

    }

});


//('ds').catalogAddComparse({maxCount:5,onMaxCount:function(){alert('max!')}});

