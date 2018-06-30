fronteditorBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();

    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    }

});