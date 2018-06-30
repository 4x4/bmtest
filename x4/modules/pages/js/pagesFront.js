moduleRegister.pushModule('pages');

var pagesFront = new Class({

    Extends: x4FrontModule,
    preventJquery: false,
    constructor: function () {
        this.parent('pages');
    },

    jqueryRun: function () {
    },

    renderSlot: function (url, slot, callback) {
        if (callback) {
            this.connector.execute({renderSlot: {url: url, slot: slot}}, callback);

        } else {
            this.connector.execute({renderSlot: {url: url, slot: slot}});
            return this.connector.result.slots;
        }
    }


});
