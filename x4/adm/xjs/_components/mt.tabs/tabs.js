var Tabs = new Class({

    Implements: [Options, Events],

    options: {
        tab: '.tab',
        subTabClass:'subTab',
        currentClass: 'selected',
        template: '<li><a href="{href}"  id="{id}" class="button {subclass}">{name}{caret}</a>{subTabs}</li>',
        subTabTemplate:'<li><a href="{href}"  id="{id}" class="button">{name}</a></li>'
    },

    jQuery: 'tabs',

    initialize: function (selector, tabs, options) {
        this.setOptions(options); 
        this.container = jQuery(selector); 
        this.tabs = {};
        this.tabRoutes={};
        this.instTabs = tabs;
        this.setup();
        jQuery(this.tabs[0]).trigger('click', true);
    },

    addTabRoutes:function(source,dest)
    {
      Array.each(source, function (item,num) 
                    {
                         this.tabRoutes['t'+item]=dest; 
                                   
                    }.bind(this));
      
    },
    
    addTab: function (item, upPosition) {

        if(!this.tabs[item.id])
        {
                this.tabs[item.id] = item;
               
                if(item.routes)
                {
                  this.addTabRoutes(item.routes,item.id);  
                }
                
                if(item.subTabs)
                {
                   subHtml='<ul style="display:none" class="'+this.options.subTabClass+' dropdown-menu text-left">';
                  
                       item.subTabs.each(function(subTab)
                       {
                             subHtml+=  $.nano(this.options.subTabTemplate, subTab);
                             
                             subTab.subTab=true;
                             this.tabs[subTab.id] = subTab;
                           
                       }.bind(this));
                   
                   subHtml+='</ul>';
                   item['subTabs']=subHtml;
                //   item['subclass']='btn-info';
                   item['caret']='<b class="caret"></b>';
                }
                
                
                htmlStr = $.nano(this.options.template, item);
              
                if (!upPosition) {
                    this.container.append(htmlStr);
                } else {
                    jQuery(htmlStr).insertBefore(this.container.find('li:first'));
                }

                if (item.active) {
                    this.makeActive(item.id);
                }
        }

    },

    removeTab: function (id) {
        this.container.find('#' + id).hide(300,function(){$(this).remove()});
        delete this.tabs[id];

    },

    makeActive: function (id) 
    {
        if(!(this.currentTab)||(this.currentTab.attr('id')!=id))
        {
            if(!this.tabs[id])  
            {
                if(!(id=this.tabRoutes[id]))
                {
                   return; 
                } 
            }  
            
            this.container.find('#' + id).trigger('click');
        }
    },

    setup: function () {
        
        $(this.container).on('click','li',this.tabClickHandler.bind(this));         
        $(this.container).on('click','li a .caret',function(event){
                
                  event.preventDefault();
        
        });         
        
        this.instTabs.each(function (item) {
            this.addTab(item);
        }.bind(this));

    },

   checkForTemporal:function()
   {
        if (this.currentTab&&this.tabs[this.currentTab.attr('id')]) {
            this.currentTab.parent().removeClass('active');
            
            if (this.tabs[this.currentTab.attr('id')].temporal) {
                this.removeTab(this.currentTab.attr('id'))
            }
        } 
       
   },
    
    tabClickHandler: function (event) 
    {
             currentTab= $(event.target);
             if($(event.target).parent().hasClass('active')&&!this.tabs[currentTab.attr('id')]['subTab']){event.preventDefault();return;}
             
             this.checkForTemporal(); 
         
             this.currentTab = currentTab;
             this.currentTab.parent().addClass('active');
        
        if( this.currentTab.next().length>0)
        {       $('.subTab').hide();  
               this.currentTab.next().show();     
               event.preventDefault();
               return;
        }else{
            $('.subTab').hide();   
        }
        

        if(this.tabs[this.currentTab.attr('id')]['preventDefault'])event.preventDefault();
                
        if(this.tabs[this.currentTab.attr('id')]['subTab']){
            this.currentTab.parent().parent().hide();
        }
        
        if (this.tabs[this.currentTab.attr('id')]['callback']) 
        {
            this.tabs[this.currentTab.attr('id')]['callback'](event);
        }
    }


});
