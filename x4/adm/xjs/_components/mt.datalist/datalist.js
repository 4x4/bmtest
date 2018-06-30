
    var dataListWatcher= new Class({

    Implements: [Options],
    
    dataListStorage:{},
    options: {                
        selector: '.datalist',
        currentView:null,
        autoShow:false
        
    },
    
    
    initialize: function (options) 
    {
     
        this.setOptions(options); 
        if(this.options.autoShow)
        {
            jQuery(window).on('mutate',this.autoShow.bind(this));
        }

        
    },
    
    autoShow: function()
    {
        

     jQuery(this.options.currentView).find(this.options.selector).each(function(n,element)
        {      
            if(jQuery(element).length>0&&((typeof this.dataListStorage[jQuery(element).attr('id')])=='undefined'))
            {
                this.dataListStorage[jQuery(element).attr('id')] = new dataList(element,this.options);
            }
        }.bind(this)); 
    },
    


});



var _dataList= new Class({
     Implements: [Options],   
     elementData:null,
     initialize: function (element,options) 
    {
        this.setOptions(options); 
        this.element=$(element);
        this.elementData=$(element).data();
        this.build();
        this.attachEvents();
    },
    
    
    
    onRemove:function(e)
    {
            e.preventDefault();
            target=$(e.target);
            parent=target.parents('.connitem');
            itemid=parent.attr('dhx_f_id');
            this.dataView.remove(itemid);
    },
    
    attachEvents:function()
    {
        
        this.element.on('click','a.remove',[],this.onRemove.bind(this));    
    },
    
    
    refresh:function(){

        setTimeout(function(){
        this.dataView.refresh();        
        }.bind(this),100);
        
        
    },
    
    add:function(element)
    {
        
         this.dataView.add(element);

    },
    
    build:function()
    {
        height=this.options.height?this.options.height:'auto';        
        
        this.dataView = new dhtmlXDataView(        
            
            {"container":this.element.attr('id'),
                              auto_scroll:true,
                              height:300,
							  edit:true,
                              drag:true,
								  type:{
									template_edit:"<textarea class='dhx_item_editor' bind='obj.Package'>"
								  }
							  }
        );
        
        var elid=this.element.attr('id');
      
        
     //   this.dataView.attachEvent('onItemDblClick', this.onItemDblClick.bind(this));
        
        this.dataView.template_item_start=dhtmlx.Template.fromHTML("<div class='list-group-item connitem' data-itemid='{-obj.id}' dhx_f_id='{-obj.id}'  style='overflow:hidden;'>");
        this.dataView.template_item_end=dhtmlx.Template.fromHTML("</div>");
		
        
        this.dataView.define('type', 'fdatalistitem');
        this.dataView.customize({
             icons_src_dir: '/x4/adm/xjs/_components/dhtmlx/dataview/codebase/imgs',
           
                 });
        
        this.dataView.clearAll();        
        this.dataView.refresh();
        
       
    }
    
    
});
