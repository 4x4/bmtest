catalogPropertyPrototype = new Class({

    initialize: function (catalog) {

        this.catalog = catalog;

    },

    handleOnSaveSku: function (val, propData) {
        return val;

    },

    handleOnCollapse:function()
    {
        
    },

    handleOnPropertyEdit:function(params)
    {
        
    },
    
    handleOnCreate: function () {

    },

    handleOnSave: function (val, propData, prop) {
        return val;
    },


    handleOnList: function () {
        return {
            type: 'ro',
            width: '*',
            align: 'left'
        }
    }

});

inputProperty = new Class({

    Extends: catalogPropertyPrototype,

    initialize: function (catalog) {
        this.parent(catalog);
    },
    
     handleOnView: function (setItem, setName, PVCinstance) {
            pName = setName + '.' + setItem['basic'];
            inp = 'input[name="' + pName + '"]';
            if (obj = PVCinstance.objectData) {
                objectParams = obj.params;
            }
                                
            
            if(typeof setItem['options']!='undefined')
            {
                $(inp).attr('maxlength',setItem['options']['fieldLength']);
                $(inp).closest('.form-group').find('.charsMax').text(setItem['options']['fieldLength']);
            }else{
                
                $(inp).closest('.form-group').find('.charsMax').text('∞');
            }
    }
    
    

});

checkboxProperty = new Class({

    Extends: catalogPropertyPrototype,

    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnList: function () {
        return {
            type: 'ch',
            width: '*',
            align: 'center'
        }
    }
});

textareaProperty = new Class({

    Extends: catalogPropertyPrototype,

    initialize: function (catalog) {
        this.parent(catalog);
    },

    
     handleOnSave: function (val, propData, props, set) 
     {
         
        dta = set + '.' + propData['basic'];
        selector = 'textarea[name="' + dta + '"]';
        item = this.catalog.mainViewPortFind(selector);                
        id=item.attr('id');        
        if(typeof CKEDITOR.instances[id]!='undefined')return CKEDITOR.instances[id].getData();
        return val;

    },
    
    handleOnView: function (setItem, setName, PVCinstance) {
        pName = setName + '.' + setItem['basic'];
        textarea = 'textarea[name="' + pName + '"]';
        if (obj = PVCinstance.objectData) {
            objectParams = obj.params;
        }


        if(typeof setItem['options']!='undefined')
        {
            $(textarea).attr('maxlength',setItem['options']['fieldLength']);
            $(textarea).closest('.form-group').find('.charsMax').text(setItem['options']['fieldLength']);

        }else{

            $(textarea).closest('.form-group').find('.charsMax').text('∞');
        }

        if(typeof setItem['options']!='undefined'){
            if(setItem['options']['useSimpleEditor']){
                CKEDITOR.replace(pName,{
                      toolbarGroups :
                      [

                      { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                      { name: 'links' },
                      { name: 'insert' },
                      { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
                      { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] }

                      ],
                      removeButtons:'Underline,Subscript,Superscript,Youtube,Image,CreateDiv,SpecialChar,JustifyLeft,JustifyRight,JustifyCenter,JustifyBlock,Outdent,Indent'

                });

                //CKEDITOR.config.forcePasteAsPlainText = true;
                CKEDITOR.config.pasteFromWordRemoveStyles = true;
                CKEDITOR.config.pasteFromWordRemoveFontStyles = true;

            }
        }

    }

});

searchFormProperty = new Class({
    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnCreate: function () {

    },

    handleOnView: function (setItem, setName, PVCinstance) {

        pName = setName + '.' + setItem['basic'];
        selector = 'select[name="' + pName + '"]';


        if (obj = PVCinstance.objectData) {
            if (obj.params[pName] && setItem['defaultValues']) {

                Array.each(setItem['defaultValues'], function (items, i) {
                    if (obj.params[pName].indexOf('' + setItem['defaultValues'][i]['value']) != -1) {
                        setItem['defaultValues'][i]['selected'] = true;
                    }
                });

            }

            delete PVCinstance.objectData.params[pName];

        }

        form = $(selector).closest('form');
        data = {};
        data[setName + '.' + setItem['basic']] = setItem['defaultValues'];
        xoad.html.importForm(form.attr('id'), data);


    },


    handleOnList: function () {
        return {
            type: 'ro',
            width: '*',
            align: 'left'

        }
    }
});


imageProperty = new Class({
    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnCreate: function () {

    },


    handleOnList: function (viewType) {

        if (viewType == 'tree') {
            return {
                type: 'img',
                width: '100',
                align: 'center'
            }

        } else {

            return {
                type: 'imgsmall',
                width: '80',
                align: 'center'
            }

        }

    }
});


connectionProperty = new Class({

    Extends: catalogPropertyPrototype,
    datalists: {},
    backPropertyDatalist: {},

    initialize: function (catalog) {
        var that = this;
        catalogBack.implement({


                //this.sectonTplHB = TH.getTplHB('catalog', 'propertiesHolder');


                onConnectionDialogObjectClick: function (id) {

                    nameArr = this.catalog.connectionDialogGroupTree.getParentPath(id, 0);
                    name = nameArr.join('/');

                    inputData = $(this.catalog.connectionDialogContext.currentElement).data();


                    id = parseInt(id, 10);
                    this.datalists[inputData.connection].add({name: name, sid: id});
                    // this.catalog.connectionDialogContext.window.close();

                },

                onConnectionDialogDynamicXLS: function (id) {


                   var locker=false;
                    if(id==0&& typeof that.optionsSet!='undefined')
                    {
                        id=that.optionsSet.srcLockGroupId;
                        var locker=true;
                    }

                    this.connector.execute({
                        treeDynamicXLS: {
                            id: id,
                            getObjects: true
                        }
                    });
                    if (this.connector.result) {
                        if (id == 0 || locker) {
                            this.connectionDialogGroupTree.parse(this.connector.result.data_set, "xjson")
                        } else {
                            this.connectionDialogGroupTree.json_dataset = this.connector.result.data_set;
                        }
                    }
                    return true;
                },

                onConnectionDialog: function (dialogContext) {


                    this.connectionDialogContext = dialogContext;

                    this.connectionDialogGroupTree = dialogContext.window.attachGrid();
                    this.connectionDialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
                    this.connectionDialogGroupTree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
                    this.connectionDialogGroupTree.setInitWidths("330,*");
                    this.connectionDialogGroupTree.setColAlign("left,left");
                    this.connectionDialogGroupTree.setColTypes("tree,ro");
                    this.connectionDialogGroupTree.init();
                    this.connectionDialogGroupTree.kidsXmlFile = 1;
                    this.connectionDialogGroupTree.attachEvent("onDynXLS", this.onConnectionDialogDynamicXLS.bind(this));
                    this.connectionDialogGroupTree.setSkin("dhx_skyblue");
                    this.connectionDialogGroupTree.attachEvent("onRowDblClicked", this.onConnectionDialogObjectClick.bind(that));
                    this.onConnectionDialogDynamicXLS(0);


                }


            }
        );

        this.parent(catalog);

    },


    handleBackConnections: function (backObjects) {
        this.backConnectionsTPL = TH.getTplHB('catalog', 'backConnections');

        var html = '';

        Object.each(backObjects, function (obj, k) {

            $('.backConnectionsHolder').append(this.backConnectionsTPL(obj));

            selector = '#' + obj.id + '_backdatalist';

            this.backPropertyDatalist[obj.id] = new _dataList(selector);


            if (typeof obj['connected'] == 'object') {
                Object.each(obj['connected'], function (el, n) {

                    this.backPropertyDatalist[obj.id].add({
                        'sid': el.id,
                        'name': '<a target="_blank" href="admin.php?#e/catalog/edit_CATOBJ/?id=' + el.id + '">' + el.paramPathValue + '</a>'
                    }, this.options);

                }.bind(this))
            }


            $('#selector').hide().show(0);

        }.bind(this));


    },

    handleOnPropertyEdit:function(data,pwin)
    {

         lockers=false;

         if(typeof data['params']['options'] !='undefined')
         {
             if(typeof data['params']['options']['propertyLocker']!='undefined')
             {
                lockers=data['params']['options']['propertyLocker'];
             }
         }
         this.catalog.connector.execute({getPsetsGroupListFront:{selected:lockers}});

         var fObj={};fObj['options']={};

         fObj['options.propertyLocker']=this.catalog.connector.result.psetsGroupList;
         xoad.html.importForm('edit_PROPERTY',fObj);

         $(pwin.dhxcont).find('.chosen-select-custom').chosen();

    },

    handleOnCollapse:function(setItem, setName, PVCinstance)
    {
        pName = setName + '.' + setItem['basic'];
        this.datalists[pName].refresh();
    },

    handleOnView: function (setItem, setName, PVCinstance) {

        if(typeof window.datalists=='undefined')window.datalists={};

        pName = setName + '.' + setItem['basic'];
        selector = 'div[id="' + pName + '_datalist"]';
        obj = PVCinstance.objectData;

        if(typeof setItem['options']!='undefined')
        {
            this.optionsSet=setItem['options'];
        }

       this.datalists[pName] = new _dataList(selector, this.options);
        if (obj) {


            if (typeof obj['params'][pName] == 'object') {
                Object.each(obj['params'][pName], function (el, n) {

                    this.datalists[pName].add(el);

                }.bind(this))

                this.datalists[pName].refresh();

            }
        }
    },


    getAllViewData: function (view) {
        var count = view.dataCount();
        var newData = [];

        for (var i = 0; i < count; i++) {

            var id = view.idByIndex(i);
            newData.push(view.get(id).sid);
        }

        return newData;

    },

    handleOnSave: function (val, propData, props, set) {

        dta = set + '.' + propData['basic'];
        dataList = this.datalists[dta];

        var data = this.getAllViewData(dataList.dataView);
        data = data.map(function (x) {
            return x.toString();
        });

        return data;

    }


});


connectionSKUProperty = new Class({

    Extends: catalogPropertyPrototype,
    datalists: {},
    backPropertyDatalist: {},

    initialize: function (catalog) {
        var that = this;
        catalogBack.implement({


                //this.sectonTplHB = TH.getTplHB('catalog', 'propertiesHolder');


                onConnectionSKUDialogObjectClick: function (id) {

                    nameArr = this.catalog.connectionDialogSKUGroupTree.getParentPath(id, 0);
                    name = nameArr.join('/');

                    inputData = $(this.catalog.connectionDialogSKUContext.currentElement).data();

                    this.datalists[inputData.connection].add({name: name, sid: id});


                },

                onConnectionSKUDialogDynamicXLS: function (id) {
                    this.connector.execute({
                        treeDynamicXLSObjSKU: {
                            id: id

                        }
                    });
                    if (this.connector.result) {
                        if (id == 0) {
                            this.connectionDialogSKUGroupTree.parse(this.connector.result.data_set, "xjson")
                        } else {

                            this.connectionDialogSKUGroupTree.json_dataset = this.connector.result.data_set;
                        }
                    }
                    return true;
                },
                onConnectionSKUDialog: function (dialogContext) {


                    this.connectionDialogSKUContext = dialogContext;

                    this.connectionDialogSKUGroupTree = dialogContext.window.attachGrid();
                    this.connectionDialogSKUGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
                    this.connectionDialogSKUGroupTree.setHeader(AI.translate('catalog', 'category') + ',' + AI.translate('catalog', 'id'));
                    this.connectionDialogSKUGroupTree.setInitWidths("330,*");
                    this.connectionDialogSKUGroupTree.setColAlign("left,left");
                    this.connectionDialogSKUGroupTree.setColTypes("tree,ro");
                    this.connectionDialogSKUGroupTree.init();
                    this.connectionDialogSKUGroupTree.kidsXmlFile = 1;
                    this.connectionDialogSKUGroupTree.attachEvent("onDynXLS", this.onConnectionSKUDialogDynamicXLS.bind(this));
                    this.connectionDialogSKUGroupTree.setSkin("dhx_skyblue");

                    this.connectionDialogSKUGroupTree.attachEvent("onRowDblClicked", this.onConnectionSKUDialogObjectClick.bind(that));
                    this.onConnectionSKUDialogDynamicXLS(0);


                }


            }
        );

        this.parent(catalog);

    },


    handleOnView: function (setItem, setName, PVCinstance) {


        pName = setName + '.' + setItem['basic'];
        selector = 'div[id="' + pName + '_datalist"]';
        obj = PVCinstance.objectData;

        this.datalists[pName] = new _dataList(selector, this.options);
        if (obj) {


            if (typeof obj['params'][pName] == 'object') {
                Object.each(obj['params'][pName], function (el, n) {

                    this.datalists[pName].add(el);

                }.bind(this))
            }
        }
    },


    getAllViewData: function (view) {
        var count = view.dataCount();
        var newData = [];

        for (var i = 0; i < count; i++) {

            var id = view.idByIndex(i);
            newData.push(view.get(id).sid);
        }

        return newData;

    },

    handleOnSave: function (val, propData, props, set) {

        dta = set + '.' + propData['basic'];
        dataList = this.datalists[dta];

        var data = this.getAllViewData(dataList.dataView);
        data = data.map(function (x) {
            return x.toString();
        });

        return data;

    }


});


currencyIshopProperty = new Class({

    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnList: function () {
        return {
            type: 'ro',
            width: '*',
            align: 'left'
        }
    },

    handleOnView: function (setItem, setName, PVCinstance) {

        objectParams = null;

        if (PVCinstance.setsInfo[setName]['obj_type'] == '_SKUGROUP') {

            pName = setItem['basic'];

            if (obj = PVCinstance.objectData) {
                objectParams = obj;
            }

        } else {

            pName = setName + '.' + setItem['basic'];

            if (obj = PVCinstance.objectData) {
                objectParams = obj.params;
            }
        }

        selector = 'input[name="' + pName + '"]';


        if (objectParams) {
            if (objectParams[pName] && setItem['defaultValues']) {
                Array.each(setItem['defaultValues'], function (items, i) {
                    if (objectParams[pName].indexOf('' + setItem['defaultValues'][i]['value']) != -1) {
                        setItem['defaultValues'][i]['selected'] = true;
                    }
                });

            }


        }

        form = $(selector).closest('form');
        data = {};

        data[pName + '__currency'] = setItem['defaultValues'];
        xoad.html.importForm(form.attr('id'), data);

    },

    handleOnCreate: function () {

    }
});


selectorProperty = new Class({

    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnList: function () {
        return {
            type: 'ro',
            width: '*',
            align: 'left'
        }
    },


    handleOnSaveSku: function (val, propData, setData) {

        if (typeof setData[propData['basic'] + '__temp'] != 'undefined') {
            setData[propData['basic']] = setData[propData['basic'] + '__temp'];


            return setData[propData['basic']];
        } else {

            return '';
        }

    },

    handleOnSave: function (val, propData, props, set) {


        selector = 'select[name="' + propData['basic'] + '"]';
        item = this.catalog.mainViewPortFind(selector);
        item.attr('disable');
        return val;

    },


    handleOnView: function (setItem, setName, PVCinstance) {


        var objType = PVCinstance.setsInfo[setName]['obj_type'];

        if (objType == '_SKUGROUP') {
            pName = setItem['basic'];
            selector = 'select[name="' + pName + '"]';
            if (obj = PVCinstance.objectData) {
                _json = JSON.stringify(obj);
                objectParams = JSON.parse(_json);


                if (typeof objectParams[pName] != 'undefined') {
                    obj[pName + '__temp'] = objectParams[pName];

                } else {

                    if (typeof obj[pName + '__temp'] != 'undefined') {
                        objectParams[pName] = obj[pName + '__temp'];
                    }

                }


            }

            $(selector).attr('id', pName + '__temp');
            $(selector).attr('name', pName + '__temp');
            selector = 'select[name="' + pName + '__temp"]';


        } else {

            pName = setName + '.' + setItem['basic'];
            selector = 'select[name="' + pName + '"]';
            if (obj = PVCinstance.objectData) {
                objectParams = obj.params;
            }
        }

        newSet = null;
        if (setItem['defaultValues']) {
            str = JSON.stringify(setItem['defaultValues']);
            newSet = JSON.parse(str);
        }


        if (typeof objectParams != 'undefined' && objectParams != null) {
            if (objectParams[pName] && newSet) {
                Array.each(newSet, function (items, i) {
                    if (objectParams[pName].indexOf('' + newSet[i]['value']) != -1) {

                        newSet[i]['selected'] = true;

                    }
                });

            }

            if (objType != '_SKUGROUP') {
                delete objectParams[pName];
            }

        }


        if (newSet) {

            form = $(selector).closest('form');
            data = {};

            if (objType == '_SKUGROUP') {
                data[pName + '__temp'] = newSet;

            } else {
                data[pName] = newSet;
            }


            xoad.html.importForm(form.attr('id'), data);
        }


        if (objType == '_SKUGROUP' && this.catalog.SKUOBJ.id && (typeof this.catalog.SKUOBJ.skuListCurrent != 'undefined')) {


            if (typeof this.catalog.SKUOBJ.skuListCurrent[this.catalog.SKUOBJ.id] != 'undefined') {
                delete this.catalog.SKUOBJ.skuListCurrent[this.catalog.SKUOBJ.id][pName + '__temp'];
            }

        }


        chosen = $(selector).chosen({'width': '98%','allow_single_deselect':true});


        /*  if(PVCinstance.setsInfo[setName]['obj_type']=='_SKUGROUP')
         {
         setTimeout(function(){

         $(selector).trigger('chosen:updated');
         },1000);

         }*/


    },

    handleOnCreate: function () {
    }
});





dateProperty = new Class({

    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnList: function () {
        return {
            type: 'ro',
            width: '*',
            align: 'left'
        }
    },

    handleOnView: function (setItem, setName, PVCinstance) {


    },


    handleOnCreate: function () {

    }

});




stockProperty = new Class({

    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnSaveSku: function (val, propData, setData) {

        if (typeof setData[propData['basic'] + '__temp'] != 'undefined') {
            setData[propData['basic']] = setData[propData['basic'] + '__temp'];


            return setData[propData['basic']];
        } else {

            return '';
        }

    },

    handleOnSave: function (val, propData, props, set) {


        selector = 'select[name="' + propData['basic'] + '"]';
        item = this.catalog.mainViewPortFind(selector);
        item.attr('disable');
        return val;

    },


    handleOnView: function (setItem, setName, PVCinstance) {

            pName = setItem['basic'];


           stockHB='<div class="row"><div class="form-group"> <label class="col-sm-8 control-label">{{stockName}}</label> ' +
            '<div class="col-sm-4"> <input name="{{pName}}__{{stockId}}" class="form-control" type="text" value="{{stockValue}}"> </div> </div></div> <div class="line line-dashed b-b line-lg pull-in"></div>';


        var objType = PVCinstance.setsInfo[setName]['obj_type'];

        if (objType != '_SKUGROUP'){
            pName = setName + '.' + setItem['basic'];
        }

            selector="div[id='"+pName+"']";

            if(objectParams){
                stockData=objectParams[pName];
            }else{
                stockData=setItem['defaultValues'];
            }


            var template = Handlebars.compile(stockHB);
            if(stockData)
            {
                var that=this;
                Object.each(stockData,function(v,k)
                {
                    v['pName']=pName;
                    that.catalog.mainViewPortFind(selector).append(template(v));
                });
            }



    },

    handleOnCreate: function () {
    }
});

tableProperty = new Class({

    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnSaveSku: function (val, propData, setData) {



    },

    handleOnSave: function (val, propData, props, set) {

        selector = 'div[id="'+set+'_'+ propData['basic'] + '"]';
        dta=this.catalog.mainViewPortFind(selector).jexcel('getData', false);
        return dta;
    },


    handleOnView: function (setItem, setName, PVCinstance) {

            pName = setItem['basic'];

        var objType = PVCinstance.setsInfo[setName]['obj_type'];


        if (obj = PVCinstance.objectData) {
                objectParams = obj.params;
            }


        if (objType != '_SKUGROUP'){
            pNameTemp = setName + '.' + setItem['basic'];
            selector="div[id='"+pNameTemp+"']";
            pName = setName + '_' + setItem['basic'];

            this.catalog.mainViewPortFind(selector).attr('id',pName)
        }

            selector="div[id='"+pName+"']";
            data=objectParams[pNameTemp];

            var  options={};
            if(data)
            {
               options.data=data;

            }

           options.colHeaders=JSON.parse(setItem['options']['headerNames']);
           options.colWidths=JSON.parse(setItem['options']['columnWidths']);

           if(typeof setItem['options']['columns']!=='undefined')
           {
            options.columns=JSON.parse(setItem['options']['columns']);
                for (z in options.columns)
               {

                   if(options.columns[z].editor=='classyEditor')
                   {
                       options.columns[z].editor=classyEditor;
                   }
               }
           }

           options.allowInsertColumn=false;
           options.allowDeleteColumn=false;




           this.catalog.mainViewPortFind(selector).jexcel(options);
           this.catalog.mainViewPortFind(selector).closest('.left.col-md-12').removeClass('col-lg-6');

    },

    handleOnCreate: function () {
    }
});









fileFolderProperty = new Class({

    Extends: catalogPropertyPrototype,
    initialize: function (catalog) {
        this.parent(catalog);
    },

    handleOnList: function () {
        return {
            type: 'ro',
            width: '*',
            align: 'left'
        }
    },

    handleOnCreate: function () {

    }
});

propertyViewConstructor = new Class({

    initialize: function (catalog, doExpand) {
        if (doExpand) this.doExpand = true;
        else this.doExpand = false;
        this.viewContext = null;
        this.connector = catalog.connector;
        this.catalog = catalog;
        this.sectonTplHB = TH.getTplHB('catalog', 'propertiesHolder');
        this.setViewContext(this.catalog.mainViewPort);
        this.psetGroup = null;

    },
    setViewContext: function (viewContext) {
        this.viewContext = $(viewContext);
    },

    contextRender: function (selector, html) {

        this.viewContext.find(selector).append(html);
    },

    pushIt: function (html) {
        this.contextRender('.properties-holder .left', html);
    },


    clear: function () {
        this.viewContext.find('.properties-holder .left,.properties-holder .right').html('');
    },

    getPropertyHtml: function (set, setInfo) {
        psetBody = '';

        for (j in set) {
            property = this.catalog.propertiesHolder[set[j].params.type];
            tpl = Handlebars.compile(property.backTemplate);

            if (this.doExpand) {
                basic = set[j].basic;

            } else {

                basic = setInfo.basic + '.' + set[j].basic;
            }


            if(typeof set[j]['defaultValues']!='undefined'){
                defaultValues=set[j]['defaultValues'];

            }else{
                defaultValues=null;
            }


            psetBody += tpl({
                alias: set [j].params.alias,
                basic: basic,
                params: set[j].params,
                options: set[j].options,
                defaultValues: defaultValues
            });
        }
        return psetBody;

    },

    handleSaveEventSku: function (saveData) {

        if (typeof this.sets[this.activeSet] == 'object') {


            Object.each(this.sets[this.activeSet], function (el, k) {


                if (this.catalog.propertiesHolder[el.params.type].handler) {

                    saveData[el.basic] = this.catalog.propertiesHolder[el.params.type].handler.handleOnSaveSku(saveData[el.basic], el, saveData);
                }

            }.bind(this));

        }


        return saveData;

    },


    handleSaveEvent: function (saveData) {


        for (z in saveData) {

            if (typeof saveData[z] == 'object') {

                if (typeof this.sets[z] == 'object') {

                    Object.each(this.sets[z], function (el, k) {

                        if (this.catalog.propertiesHolder[el.params.type].handler) {

                            saveData[z][el.basic] = this.catalog.propertiesHolder[el.params.type].handler.handleOnSave(saveData[z][el.basic], el, saveData[z], z);
                        }

                    }.bind(this));

                }
            }
        }

        return saveData;

    },


    handleViewEvents: function (set, setName) {

        for (j in set) {

            if (typeof this.catalog.propertiesHolder[set[j].params.type].handler == 'object') {

                if (typeof this.catalog.propertiesHolder[set[j].params.type].handler.handleOnView == 'function') {


                    this.catalog.propertiesHolder[set[j].params.type].handler.handleOnView(set[j], setName, this);
                }
            }

        }

    },

    loadPropertySetData: function (psetGroupId,objectData) {

        this.connector.execute({
            getPropertyGroup: {
                psetGroupId: psetGroupId,
                objectData:objectData
            }
        });


        this.psetGroup = this.connector.result.psetGroup;
        this.setsInfo = this.connector.result.psetGroup.setsInfo;
        this.sets = this.connector.result.psetGroup.sets;
    },

    onCollapse:function(ev,d)
    {
        ind=$(d.target).closest('section').data('setindex');
        set=this.sets[ind];

        for (j in set)
         {
            if (typeof this.catalog.propertiesHolder[set[j].params.type].handler == 'object') {

                if (typeof this.catalog.propertiesHolder[set[j].params.type].handler.handleOnCollapse == 'function') {

                    this.catalog.propertiesHolder[set[j].params.type].handler.handleOnCollapse(set[j], ind, this);
                }
            }

        }

    },

    processView: function (psetGroupId, objectData) {
        this.clear();

        if (objectData) {
            this.objectData = objectData;
        } else {
            this.objectData = null;
        }

        this.loadPropertySetData(psetGroupId,this.objectData);

        size = Object.getLength(this.setsInfo);
        size = size % 2 + Math.floor(size / 2);
        k = 0;

        for (i in this.setsInfo) {
            k++;
            psetBody = this.getPropertyHtml(this.sets[i], this.setsInfo[i]);

                this.pushIt(this.sectonTplHB({
                    'psetAlias': this.setsInfo[i].params.alias,
                    'psetBasic': this.setsInfo[i].basic,
                    'setIndex': i,
                     doExpand:this.doExpand,
                     psetBody: psetBody
                }));




            this.handleViewEvents(this.sets[i], this.setsInfo[i].basic);

        }


        $(window).on('cardeon:open',this.onCollapse.bind(this));

        this.viewContext.find('.properties-holder .panel').addClass('expandUp');

        return this.psetGroup;

    }

});



