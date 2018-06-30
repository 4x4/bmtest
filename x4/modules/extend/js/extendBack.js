extendBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('listView', {});
    },

    onHashDispatch: function (e, v) {
        return true;
    },

    buildInterface: function () {
        
        this.parent();
    }

});
