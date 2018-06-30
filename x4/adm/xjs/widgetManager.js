
var _widget=new Class({
    
        Implements: [Options],      
        module:null,    
        connector:null,                         
        options: 
        {
            size:6 
        }
        ,
          
        initialize: function(options)
        {        
            this.setOptions(options);                     
            this.module=AI.loadModule(this.options.module, 'silent', true);        
            this.instanceName='#widget_'+this.options.name;
            this.connector=this.module.connector;
        },
        
        build:function()
        {
            
        },
        
        destroy:function()
        {
            
        }
        
           
 });

 var _widgetManager= new Class({
    
        Implements: [Options],      
        widgetStorage:{},
        options: {
            target:'#widgetContainer', 
        }
        ,
        
        get:function(name)
        {            
            if(Object.contains(this.widgetStorage,name))
            {
                return this.widgetStorage[name];             
            }
               
        },
        initialize: function(options)
        {        
            this.setOptions(options);
            this.container=$(this.options.target);            
        },   
        
        addWidget:function(instance)
        {
             this.widgetStorage[instance.options.module]=instance;   
             this.container.append(instance.buildHtml());
             instance.domInstance=$(instance.instanceName);
             instance.attach(this);
        },
        
        removeWidget:function()
        {
            if(Object.contains(this.widgetStorage,name))
            {
                this.widgetStorage[name].destroy();                
                delete this.widgetStorage[name];             
            }
              
        }
    
 });
 
 
 var ishopWidgetSales=new Class({
    
        
        Extends: _widget,        
        domInstance:null,  
        initialize: function(options)
        {        
            this.parent(options);
            this.template=TH.getTplHB('AdminPanel', 'ishopStat_widget');
        },
        
        buildHtml:function()
        {            
            data=this.connector.execute({getWidgetStat:true});            
            data.name=this.options.name;            
            return this.template({data:data});
        },
        
        attach:function(widgetManager)
        {
                  this.connector.execute({getWidgetGraph:true});  
                  
                  data=this.connector.result.data;
                  
                  var chart = AmCharts.makeChart("ishopStatGraph", {
                      
                       "minWidth": 200,
                       "maxWidth": 400,
                       "maxHeight": 400,
                       "minHeight": 200,
                       "pathToImages": '/x4/adm/xjs/_components/amcharts/images/',
                    "type": "serial",
                    "theme": "light",                    
                    "autoMarginOffset": 10,
                    "dataDateFormat": "YYYY-MM-DD",
                    "valueAxes": [{
                        "id": "v1",
                        "axisAlpha": 0,
                        "position": "left"
                    }],
                    "balloon": {
                        "borderThickness": 1,
                        "shadowAlpha": 0
                    },
                    "graphs": [{
                        "id": "g1",
                        "bullet": "round",
                        "bulletBorderAlpha": 1,
                        "bulletColor": "#FFFFFF",
                        "bulletSize": 5,
                        "hideBulletsCount": 30,
                        "lineThickness": 2,
                        "title": "red line",
                        "useLineColorForBulletBorder": true,
                        "valueField": "sum",
                        "balloonText": "<div style='margin:5px; font-size:19px;'><span style='font-size:13px;'>[[date]]</span><br>[[sum]]</div>"
                    }],
                    "chartScrollbar": {
                        "graph": "g1",
                        "oppositeAxis":false,
                        "offset":10,
                        "scrollbarHeight": 40,
                        "backgroundAlpha": 0,
                        "selectedBackgroundAlpha": 0.1,
                        "selectedBackgroundColor": "#888888",
                        "graphFillAlpha": 0,
                        "graphLineAlpha": 0.5,
                        "selectedGraphFillAlpha": 0,
                        "selectedGraphLineAlpha": 1,
                        "autoGridCount":true,
                        "color":"#AAAAAA"
                    },
                    "chartCursor": {
                        "pan": true,
                        "valueLineEnabled": true,
                        "valueLineBalloonEnabled": true,
                        "cursorAlpha":0,
                        "valueLineAlpha":0.2
                    },
                    "categoryField": "date",
                    "categoryAxis": {
                        "parseDates": true,
                        "dashLength": 1,
                        "minorGridEnabled": true
                    },
                     "responsive": {
                      "enabled": true
                     },
                   
                    "dataProvider": data
                });

chart.addListener("rendered", zoomChart);

zoomChart();

        function zoomChart() {
            if(typeof chart.dataProvider!='undefined'){
            chart.zoomToIndexes(chart.dataProvider.length - 40, chart.dataProvider.length - 1);
            }
        }
            
        },
        
        
        destroy:function()
        {
            
        }
        
           
 });
      
      
      
      
var pagesCacheStat=new Class({
    
        
        Extends: _widget,        
        domInstance:null,  
        initialize: function(options)
        {        
            this.parent(options);
            this.template=TH.getTplHB('AdminPanel', 'pagesCacheStat_widget');
        },
        
        buildHtml:function()
        {         
            
            return this.template();
        },
        
        
        refresh:function(){
            
            setInterval(function(){
                
                  this.connector.execute({getWidgetCacheStat:true},function(data,con)
                  {
                      
                    
                     data=con.result.data;
                  this.chart.dataProvider = data;    
                  this.chart.validateData();      
                      
                  }.bind(this));   
                  
                  
            }.bind(this),60000);
            
        },
        
        
        attach:function(widgetManager)
        {
                  this.connector.execute({getWidgetCacheStat:true});  
                  
                  data=this.connector.result.data;
                  
                  this.chart = AmCharts.makeChart("pagesCacheStat", {
                                 
                      "type": "serial",
                      "theme": "light",
                      "marginRight": 70,
                      "dataProvider": data,
                      "valueAxes": [{
                        "axisAlpha": 0,
                        "position": "left",
                        "title": "Размера кеша,Mb"
                      }],
                      "startDuration": 1,
                      "graphs": [{
                        "balloonText": "<b>[[folder]]: [[size]]</b>",                        
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "type": "column",
                        "valueField": "size"
                      }],
                      "chartCursor": {
                        "categoryBalloonEnabled": false,
                        "cursorAlpha": 0,
                        "zoomable": false
                      },
                      "categoryField": "folder",
                      "categoryAxis": {
                        "gridPosition": "start",
                        "labelRotation": 45
                      },
                      "export": {
                        "enabled": true
                      }

                    });

                   this.refresh();
                  
            
        },
        
        
        destroy:function()
        {
            
        }
        
           
 });      
 
 
 
 
 var catalogStat=new Class({
    
        
        Extends: _widget,        
        domInstance:null,  
        initialize: function(options)
        {        
            this.parent(options);
            this.template=TH.getTplHB('catalog', 'catalogStat_widget');
            this.data=null;
        },
        
        buildHtml:function()
        {                     
            this.data=this.connector.execute({getWidgetStat:true});                                            
            return this.template(this.data);
        },
        
        
        refresh:function(){
            
          
            
        },
        
        
        attach:function(widgetManager)
        {
                                
            
        },
        
        
        destroy:function()
        {
            
        }
        
           
 });      
