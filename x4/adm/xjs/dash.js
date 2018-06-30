
var dashboardBack = new Class(
    {
        Extends: _xModuleBack,
        widgetManager:null,
        
        
        initialize: function (name) {

            this.setName(name);
            this.setLayoutScheme('emptyView', {});
            this.parent();
            
        },

        onHashDispatch: function (e, v) {
            return true;
        },


        buildInterface: function () {
            this.parent();
            this.viewPort.html(TH.getTpl('AdminPanel', 'dash'));
            AI.widgetManager= new _widgetManager();    
            AI.widgetManager.addWidget(new catalogStat({name:'catalogStat',module:"catalog"}));
            AI.widgetManager.addWidget(new ishopWidgetSales({name:'ishopStat',module:"ishop"}));
            AI.widgetManager.addWidget(new pagesCacheStat({name:'pagesCacheStat',module:"pages"}));
            
        }

    });
    