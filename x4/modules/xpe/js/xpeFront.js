moduleRegister.pushModule('xpe');

var xpeFront = new Class({

    Extends: x4FrontModule,
    preventJquery: true,
    constructor: function () {

        this.parent('xpe');

    },

    sb:function()
    {
        sbjs.init({
            lifetime: 3,
            promocode: true,
            callback: function(data){

                this.connector.execute({setSbSession: {data: data}});
            }.bind(this)
        });


    },
    jqueryRun:function(){}

});

var xpe=new xpeFront();