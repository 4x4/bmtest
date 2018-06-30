
var __globalLogLevel=2;


debug.setLevel(__globalLogLevel);


Class.Mutators.jQuery = function(name){
var self = this;
jQuery.fn[name] = function (arg){
var instance = this.data(name);
if (typeOf(arg) == 'string'){
var prop = instance[arg];
if (typeOf(prop) == 'function'){
var returns = prop.apply(instance, Array.slice(arguments, 1));
return (returns == instance) ? this : returns;
} else if (arguments.length == 1){
return prop;
}
instance[arg] = arguments[1];
} else {
if (instance) return instance;
this.data(name, new self(this.selector, arg));
}
return this;
};
};





generateGUID = function() {
  return 'xxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
  }).toUpperCase();
};                                                                              



/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
 
var Base64 = {
 
    // private property
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
    // public method for encoding
    encode : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;
 
        input = Base64._utf8_encode(input);
 
        while (i < input.length) {
 
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
 
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
 
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
 
            output = output +
            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
        }
 
        return output;
    },
 
    // public method for decoding
    decode : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
 
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
        while (i < input.length) {
 
            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));
 
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
 
            output = output + String.fromCharCode(chr1);
 
            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }
 
        }
 
        output = Base64._utf8_decode(output);
 
        return output;
 
    },
 
    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
 
        for (var n = 0; n < string.length; n++) {
 
            var c = string.charCodeAt(n);
 
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
 
        }
 
        return utftext;
    },
 
    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
 
        while ( i < utftext.length ) {
 
            c = utftext.charCodeAt(i);
 
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
 
        }
 
        return string;
    }
 
};


Handlebars.registerHelper("ifCond",function(v1,operator,v2,options) {
    switch (operator)
    {
        
        case "in":
        if(v2 instanceof Array)
        {
            if(v2.indexOf(v1)!=-1)
            {
                return options.fn(this);        
            }
        }
        
        return options.inverse(this);

        
        case "==":
            return (v1==v2)?options.fn(this):options.inverse(this);

        case "!=":
            return (v1!=v2)?options.fn(this):options.inverse(this);

        case "===":
            return (v1===v2)?options.fn(this):options.inverse(this);

        case "!==":
            return (v1!==v2)?options.fn(this):options.inverse(this);

        case "&&":
            return (v1&&v2)?options.fn(this):options.inverse(this);

        case "||":
            return (v1||v2)?options.fn(this):options.inverse(this);

        case "<":
            return (v1<v2)?options.fn(this):options.inverse(this);

        case "<=":
            return (v1<=v2)?options.fn(this):options.inverse(this);

        case ">":
            return (v1>v2)?options.fn(this):options.inverse(this);

        case ">=":
         return (v1>=v2)?options.fn(this):options.inverse(this);

        default:
            return eval(""+v1+operator+v2)?options.fn(this):options.inverse(this);
    }
});


nullerizeObject=function(obj)
{
    sp={};for(i in obj){sp['0'+i]=obj[i];}return sp;
};

deNullerizeObject=function(obj)
{
    sp={};for(i in obj){if(i.charAt(0)=='0'){sp[i.substring(1)]=obj[i];}}
    return sp;   
};

String.prototype.trim = function ()
{
    return this.replace(/(^\s+)|(\s+$)/g, "");
 
};

String.prototype.translit = (function(){ 
    var L = {
'А':'A','а':'a','Б':'B','б':'b','В':'V','в':'v','Г':'G','г':'g',
'Д':'D','д':'d','Е':'E','е':'e','Ё':'Yo','ё':'yo','Ж':'Zh','ж':'zh',
'З':'Z','з':'z','И':'I','и':'i','Й':'Y','й':'y','К':'K','к':'k',
'Л':'L','л':'l','М':'M','м':'m','Н':'N','н':'n','О':'O','о':'o',
'П':'P','п':'p','Р':'R','р':'r','С':'S','с':'s','Т':'T','т':'t',
'У':'U','у':'u','Ф':'F','ф':'f','Х':'Kh','х':'kh','Ц':'Ts','ц':'ts',
'Ч':'Ch','ч':'ch','Ш':'Sh','ш':'sh','Щ':'Sch','щ':'sch','Ъ':'"','ъ':'"',
'Ы':'Y','ы':'y','Ь':"'",'ь':"'",'Э':'E','э':'e','Ю':'Yu','ю':'yu',
'Я':'Ya','я':'ya',' ':'-'
        },
        r = '',
        k;
        

    for (k in L) r += k;
    r = new RegExp('[' + r + ']', 'g');
    k = function(a){
        return a in L ? L[a] : '';
    };
    return function(){
        return this.replace(r, k);
    };
})();


function translitToLat(from, to, length)
{
    from = document.getElementById(from).value.toLowerCase();    
    if (from.length == 0){return;}
    to = document.getElementById(to);
    if(length)from = from.substring(0, length);
    to.value = from.translit(); 
}

              

 var bConnector = {
    "lct": null,
    "result": null,
    "__meta": {
        "lct": "null",
        "result": "null"
    },
    "__size": 2,
    "__class": "connector",
    "__url": "\/admin.php",
    "__uid": __uid,
    "__output": null,
    "__timeout": null,
    "xroute": function() {return xoad.call(this, "xroute", arguments)}
};


bConnector.clearModuleVars = function ()
{
    this.result = null;
};

bConnector.onexecuteError = function (error)
{
    alert(_lang_common['error_on_server'] + '\n\n' + error.message);
    return true;
};

//extending native prototype
String.prototype.toHashCode = function(){
        var hash = 0;
        if (this.length == 0) return hash;
        for (i = 0; i < this.length; i++) {
            char = this.charCodeAt(i);
            hash = ((hash<<5)-hash)+char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return 'h'+hash;
    };

    
var  Gpreloader;   

if(typeof jQuery!='undefined'){
jQuery(document).ready(function($) {
    Gpreloader = new $.materialPreloader({
        position: 'top',
        height: '3px',
        col_1: '#F0F0F0',
        col_2: '#de47ac',
        col_3: '#cb60b3',        
        fadeIn: 20,
        fadeOut: 20
    });
  
});    
}
var Connector = new Class({
    module: null,
    result: null,
    model:'.back',
    
    initialize: function (module,model)
    {
        this.module = module;
        if(model)
        { 
            this.model = model;        
        }
        
    },
    //function(data,(arg)) 
    //если  arg - то выполнение откладывается до первого вызова
    //arg (2) - модель роутинга
    onerror: function (error) {
        for (i = 0; i < error.length; i++)

        $.growler.error({message:error[i].message,title:_lang['common']['info']});
    },

    onmessage: function (message)
    {
        

        for (i = 0; i < message.length; i++)        
        $.growler.notice({message:message[i].message,title:_lang['common']['info']});

    },

    execute: function (data,func)
    {
        if(typeof Gpreloader!='undefined')Gpreloader.on();
        d = [];
        
        d[this.module+this.model] = data;
        
        this.result = null;
        bConnector['xroute'](d, func);

            if (bConnector.error != null)
            {
                this.onerror(bConnector.error);

            }

            if (bConnector.message != null)
            {
                this.onmessage(bConnector.message);

            }
        
            this.lct =  bConnector.lct;   
            
            this.result = bConnector.result;
            
            if(typeof Gpreloader!='undefined') Gpreloader.off();
            if(!func)return this.result;
    }
  
});



  
var _storage= new Class(
{

    detectLocalStorage : function ()
    {
        try {
        return !!localStorage.getItem;
      } catch(e) {
        return false;
      }

    },
    
    initialize:function()
    {
        this.storageDetected=false;

        if(this.detectLocalStorage())
        {
            this.localStorage = window.localStorage;
            debug.log(window.localStorage);
            this.storageDetected=true;                    
        }
    },

    
    set:function(key,value)
    { 

        if (typeof value == "object") 
                {
                    value = JSON.stringify(value);
                }
                this.localStorage.setItem(key, value);

        
    },
    
    hasItem: function( key )
    {
        
        return(
         this.localStorage.getItem( key ) != null
        );
    },

    remove:function(key)
    {
        
        var i = -1,
            key, len = this.localStorage.length;
            
            while (++i < len)
            {
                lkey = this.localStorage.key(i); // retrieve the value of each key at each index

                if(lkey.indexOf(key)==0)
                {
                    this.localStorage.removeItem(lkey);
                }
            }
             

        
    },

    clear :function()
    {
        this.localStorage.clear();   
    },
     

    get:function(key)
    {
        
                if((value = this.localStorage.getItem(key))!=null)
                {

                    return this.jsonCheck(value);                       
                     
                }else{
                    
                    if(value = this.getPartialKey(key+'>'))
                    {
                        return  value;    
                    }
                }
      
        return null;
                
    },
    
     each : function (key,callback) 
     {
         
            if(val=this.get(key))
            {
                Object.each(val,function(key,val)
                {
                    callback(key,val);   
                })
            }
             
     }
     ,
     

    setData:function(key,val,obj) 
    {
        
        if (!obj) obj = data; 
        var ka = key.split(/\>/); 
        if (ka.length < 2) { obj[ka[0]] = val; } 
        
        else {
            if (!obj[ka[0]]) obj[ka[0]] = {};
            obj = obj[ka.shift()]; 
            this.setData(ka.join(">"),val,obj); 
        }
     
     },

    
    getPartialKey:function(key)
    {
         var i = -1,
            key, len = this.localStorage.length,
            // the length property tells us 
            // how many items are in the storage
            res = {};

            keySplitted=key.split('>');
            
            while (++i < len)
            {
                lkey = this.localStorage.key(i); // retrieve the value of each key at each index

                if(lkey.indexOf(key)==0)
                {
                    item=this.localStorage.getItem(lkey); 
                  
                    this.setData(lkey,this.jsonCheck(item),res);
                }
            }

             if(res)
             {                                                            
                for(k=0;k < keySplitted.length-1;k++)
                {
                    res=res[keySplitted[k]];
                }
                return res;
             }

    },    
    
    jsonCheck:function(value)
    {
              if (value[0] == "{") {
                    value = JSON.parse(value);
                }
                return value;
    }
    
}); 


var _storageProxy = new Class(
{
    initialize:function(initialBranch)
    {
        lcSt=new _storage();
        this.initialBranch=initialBranch;
        this.initialBranchTimer=initialBranch+'Timer';
        this.storage ={};
        this.syncroTimer={};
        this.localStorageDetected=lcSt.storageDetected;
                        
        if(this.localStorageDetected)
        {
            this.localStorage=lcSt;
            
              
            if(initialBranch)
            {
                
                if(this.localStorage.get(initialBranch))
                {
                    
                    this.localStorage.each(initialBranch,function(k,v)
                    {
                        this.storage[v]=k;        
                    
                    }.bind(this));
                    
                                            
                    this.localStorage.each(this.initialBranchTimer,function(k,v)
                    {
                      
                        this.syncroTimer[v]=k;        
                    
                    }.bind(this));
                    
                }
            }
        
        }

    },
    
    clear:function()
    {
            this.storage = {};    
            if(this.localStorageDetected)
                {
                    this.localStorage.remove(this.initialBranch);
                }
    },
    
    set:function(key,val,timer)
    {
        
        this.storage[key]=val;    
        
        if(timer)this.syncroTimer[key]=timer;    
        
        if(this.localStorageDetected)
        {

            this.localStorage.set(this.initialBranch+'>'+key,val);                
            if(timer)this.localStorage.set(this.initialBranchTimer+'>'+key,timer);                
        }
        
    },
    
    getTimer:function(key)
    {                   

        return this.syncroTimer[key];    
    },
    
    get:function(key)
    {
        return this.storage[key];    
    }
   
});


var _templateHolder=new Class(
{
    Implements: Options,
    initialize: function (options)
    {
        this.setOptions(options);
        this.tplStorage= new _storageProxy('tplHolder');
        this.thisSessionLoaded={};
        this.connector= new Connector('AdminPanel');
    },
    
    
    setTpl:function(module,tplName,tplText,tplTime,isNew)
    {
           
        marker=module+'_'+tplName;
        
        if (isNew)        
        {
            this.thisSessionLoaded[marker]=true;
        }
        
   
        this.tplStorage.set(module+'_'+tplName.toHashCode(),tplText,tplTime);
    },
    
    
    getTpl:function(module,tplName)
    {
        tpl=[tplName];
        this.loadModuleTpls(module,tpl);
        
        if(txt=this.tplStorage.get(module+'_'+tplName.toHashCode()))
            {
             return txt;   
            }
            else{
                debug.log('no item in tpl storage for '+marker);
            }
         /*
        if(this.thisSessionLoaded[marker])
        {  
            tpl=this.tplStorage.get(module+'_'+tplName.toHashCode)
        }  */
        
    },

    getTplHB:function(module,tplName)
    {            
        return Handlebars.compile(this.getTpl(module,tplName));
    },
    
    loadModuleTpls: function (module, tplArr)
    {
        
     
        var tpls = [];
        
        if (tplArr)
        {
            Array.each(tplArr,function (tplName,index)
            {
                
                if (this.tplStorage.get(module+'_'+tplName.toHashCode()) == false)
                {
                    tpls.push(
                    {
                        tplName: tplName
                    });
                }
                else
                {
                    //шаблон  в сторадже проверяем его актуальность
                    
                    marker=module+'_'+tplName;    
                    syncromarker=module+'_'+tplName.toHashCode();
                    
                    if (!this.thisSessionLoaded[marker])
                        {
                            tpls.push(
                            {
                                tplName: tplName,
                                time: this.tplStorage.getTimer(syncromarker)
                            });
                        }
                    }
                }.bind(this));
            
            
            if (tpls.length > 0)
            {
                this.connector.execute(
                {
                    loadModuleTplsBack: 
                    {
                        module: module,
                        tpls: tpls
                    }
                });
                
                if(this.connector.lct)
                {
                    Object.each(this.connector.lct.templates,function (tplText,index)
                    {                        
                        this.setTpl(module,index,tplText,this.connector.lct.timers[index],true);
                    
                    }.bind(this));
        
        
            }
        }
    }
    }

    
});
