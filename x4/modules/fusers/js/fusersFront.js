moduleRegister.pushModule('fusers');

var fusersFront = new Class(
    {
        Extends: x4FrontModule,
        preventJquery: false,

        constructor: function() {
            this.parent('fusers');
        },

        jqueryRun:function() {},

        addToFavorite:function(obj_id, del) {
            if(del) {
                this.connector.execute({delFavorite:{obj_id:Number(obj_id)}});
            } else {
                this.connector.execute({addFavorite:{obj_id:Number(obj_id)}});
            }
        },

        delFavorite:function(id,obj_id) {
            if(typeof id != 'undefined' && Number(id) > 0) {
                this.connector.execute({delFavorite:{id:Number(id)}});
            } else if(typeof obj_id != 'undefined' && Number(obj_id) > 0) {
                this.connector.execute({delFavorite:{obj_id:Number(obj_id)}});
            }
        },

        delAllFavorites:function() {
            this.connector.execute({delAllFavorites:{}});
        }

    });
