
filterloaderBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('listView', {});
        this.connector.module='plugin.catalog.filterloader';
        this.startTpl='<form id="filterForm">';
        this.tpl= Handlebars.compile('<ul style="list-style-type:none">{{#each items}}<li><input  data-group="{{../z}}" value="{{this}}" type="checkbox"> <span>{{this}}</span></li>{{/each}}</ul>');
        this.endTpl='<button class="saveFilterList">save</button></form>';

        
    },

    setupListeners:function(){

        jQuery(window).on('interfaceBuilded',this.onModuleInterfaceBuildedAfter.bind(this));
        jQuery(document).on('click','.saveFilterList',this.onSavePropertyList.bind(this));


    },

      onSavePropertyList:function(e)
	{
        e.preventDefault();

        let data=[];

        $('#filterForm input').each((k,v)=>{

            if($(v).prop('checked'))
            {
                group = $(v).data('group');

                if (!data[group]) data[group] = [];

                data[group].push($(v).val());
            }

        });


        this.connector.execute({
            fetchProperty: {properties:data,kid:this.kid}
        });


    },

      createPropertyEditorWindow: function (data) {
          this.propertyEditorWin = AI.dhxWins.createWindow("propertyEditorFilters", 20, 10, 600, 600, 1);
          this.propertyEditorWin.setModal(true);
          this.propertyEditorWin.setText('Properties list');
          this.propertyEditorWin.attachEvent("onHide", function (win) {
              win.close();
          });


          let html=this.startTpl;

          for(z in data){
              html+=this.tpl({z:z,items:data[z]});
          }

          html+=this.endTpl;

          this.propertyEditorWin.attachHTMLString(html);
          this.propertyEditorWin.button('park').hide();
          this.propertyEditorWin.centerOnScreen();
          this.propertyEditorContext = this.propertyEditorWin.dhxcont;

      },


      addFilters:function(id,kid)
    {
        this.connector.execute({
            analyzeCategory: {id:id,kid:kid}
        });

        this.kid=kid;

        this.createPropertyEditorWindow(this.connector.result.techInput);





    },
    
    onModuleInterfaceBuildedAfter:function(data,b)
    {            

        if(b.module.name=='catalog')
        {
            b.module.treeMenu.addNewChild(b.module.treeMenu.topId, 0, 'filtertune', 'Add filters', false, '', '', this.addFilters.bind(this));    
        }
         
    }
    

});
    
        