
var settingsBack = new Class(
    {
        Extends: _xModuleBack,     
        
        
        initialize: function (name) {

            this.setName(name);
            this.setLayoutScheme('menuListView', {});
            this.parent();
            
        },

        onHashDispatch: function (e, v) {
            return true;
        },


        buildInterface: function () {
            this.parent();  
             
                   
            items=[             
             {
              link:AI.navHashCreate(this.name, 'settings'),
              name:AI.translate('common', 'settings')
             },       
             {
              link:AI.navHashCreate(this.name, 'install'),
              name:AI.translate('common', 'install_modules_and_plugins')
             },
			 
			 {
              link:AI.navHashCreate(this.name, 'migration'),
              name:AI.translate('common', 'migration')
             }  
                
            ];
            
    
            this.nav=new Subnav('#settings #subNav',AI.translate('common', 'settings'),items);
            
            
        }

    });


    