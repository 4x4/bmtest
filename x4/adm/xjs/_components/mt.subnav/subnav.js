var Subnav= new Class({

    Implements: [Options, Events],

    options: {
        
        template: '<div class="wrapper b-b header">{{header}}</div><ul class="nav">'+
            '{{#each items}}<li class="b-b"><a class="subNavItem" href="{{this.link}}">'+
            '<i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>{{this.name}}</a></li>{{/each}}'+
            '</ul>'
    },
    

    initialize: function (selector,header,items, options) {
        this.setOptions(options); 
        this.container = jQuery(selector); 
        this.tpl=Handlebars.compile(this.options.template);
        this.items = items;             
        this.header=header;
        this.setup();
    },
      
  
    setup: function () {     
        
         r=this.tpl({items:this.items,header:this.header});
         console.log(r);            
         this.container.html(r);
        
    }
    

});