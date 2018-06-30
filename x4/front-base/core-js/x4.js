fConnector.prototype = {
    execute: function(data, func) {
        d = [];
        d[this.module + '.xfront'] = data;
        this.result = null;
        xConnector['xroute'](d, func);
        this.result = xConnector.result;
        if(enableJsonDebugging)console.log(xConnector.result);
        
        if (xConnector.error) {
            this.onerror(xConnector.error);
        }

        if (xConnector.message) {
            this.onmessage(xConnector.onmessage);
        }

    },

    onerror: function(err) {

        if (window.console) {
            console.log(err);
        }

    },

    onmessage: function(mes) {

        if (window.console) {
            console.log(mes);
        }

    }
};

function fConnector(module) {
    this.module = module;
    this.result = null;
}



function declination(number,    // число
                     t1,        // месяц, день   
                     t2,        // месяца, дня
                     t3)        // месяцев, дней
{
    var titles = [];
    titles[0] = t1; titles[1] = t2; titles[2] = t3;
    var cases = [2, 0, 1, 1, 1, 2];
    return titles[ (number%100>4 && number%100<20)? 2 : cases[Math.min(number%10, 5)] ];
}



__moduleRegister.prototype = {
                modload: [],
                
                pushModule: function(mod) {

                    this.modload.push(mod)
                },
                
                get : function() {
                    return this.modload;
                }
            };

function __moduleRegister() {
}
var moduleRegister = new __moduleRegister();
var x4Base = new Class(
{
        isJquery: false,
        factoryStorage:{},
        
        initialize: function() 
        {
            modules=moduleRegister.get();
            if(modules.length>0)
            {
                
                for(i=0;i<modules.length;i++)
                {
               
                    this.factor(modules[i]+'Front',modules[i],true);    
                }    
            }
            this.isJquery = (typeof $ === 'undefined') ? false : true
            
            
        },
        
        getModule:function(classname)
        {                   
            return this.factoryStorage[classname];        
        },
        
        factor: function(classname, params, store) 
        {  
      
         
          this.isJquery = (typeof $ === 'undefined') ? false : true;
          if(typeof this.factoryStorage[classname]!=='undefined')return this.factoryStorage[classname];
          
          
          if(typeof window[classname]=='function')
          {
              inst=new window[classname](params);
              if(store)this.factoryStorage[classname] =inst;
                   
              if(this.isJquery&&typeof inst.jqueryRun!='undefined')
              {
                  inst.jqueryRun();
              }
              
              return inst;
          }
          
  
        }

});
                              

x4FrontModule = new Class({
    connector: null,
    initialize: function(name) { 
        this.connector = new fConnector(name);
    },

    execute:function(params)
    {
          this.connector.execute(params);    
          this.result=this.connector.result;
          return this.connector.result;
           
    }
    
});
