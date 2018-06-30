searchBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('listView', {});
        this.loadDefaultTpls = ['indexing'];

    },

    onHashDispatch: function (e, v) {
        this.tabs.makeActive('t' + e);
        return true;
    },


    CRUN: function () {
    },

    tabsStart: function () {


        var oTabs = [{
            id: 't_indexation',
            name: this.translate('indexation'),
            href: AI.navHashCreate(this.name, 'indexation')
        },

            {
                id: 't_current_indexes',
                name: this.translate('current_indexes'),
                href: AI.navHashCreate(this.name, 'currentIndexes')
            }
            /*,

             {
             id: 't_sitemaprules',
             name: AI.translate('common', 'options'),
             href: AI.navHashCreate(this.name, 'sitemaprules')
             } */


        ];


        this.tabs = new Tabs(this.tabsViewPort, oTabs);
        this.tabs.makeActive('t_orders');

    },


    buildInterface: function () {

        this.parent();
        this.tabsStart();
        this.navigate('indexation');

    },


    colorRows: function () {
        this.gridlist.forEachRow(function (id) {
            row = this.gridlist.getRowById(id);
            if (row._attrs.data[4] == '404') {
                row.className = 'red_modern';
            }
            if (row._attrs.data[4] == '301') {
                row.className = 'yellow_modern';
            }
        }.bind(this));
    },


    currentIndexesList: function (page) {
        this.connector.execute({indexesTable: {page: page}});

        if (this.connector.result.data_set) {
            this.gridlist.parse(this.connector.result.data_set, "xjson");

        }

    },


    currentIndexes: function (data) {

        this.setMainViewPort(this.getTpl('currentIndexes'));
        this.gridlist = new dhtmlXGridObject('currentIndexes');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + this.translate('link') + ',' + this.translate('title') + ',' + this.translate('body') + ',' + this.translate('index') + ',' + this.translate('status'));
        this.gridlist.setInitWidths("70,300,200,*,400,80");
        this.gridlist.setColAlign("center,left,left,left,left,center");

        this.gridlist.setColTypes("ro,ro,ro,ro,ro");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.setMultiLine(true);
        this.gridlist.init();
        this.gridlist.setSkin("modern");


        this.currentIndexesList(data.page);

        var pg = new paginationGrid(this.gridlist,
            {
                target: this.mainViewPortFind('.paginator'),
                pages: this.connector.result.pagesNum,
                url: AI.navHashCreate(this.name, 'currentIndexes', {id: 1})

            }
        );

    },

    indexation: function () {

        this.setMainViewPort(this.getTpl('indexing'));

        this.gridlist = new dhtmlXGridObject('indexingResults');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + this.translate('link') + ',' + this.translate('size') + ',' + this.translate('title') + ',' + this.translate('status'));
        this.gridlist.setInitWidths("70,350,80,*,80");
        this.gridlist.setColAlign("center,left,center,left,center");
        //    this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
        this.gridlist.setColTypes("ro,ro,ro,ro,ro");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.setMultiLine(true);
        // this.gridlist.enablePaging(true, 30, 8, "pagingArea", true, "recinfoArea");
        this.gridlist.init();
        this.gridlist.setSkin("modern");

    },

    onModuleInterfaceBuildedAfter: function () {


        $(document).on('click', '#' + this.name + ' .stopIndexing', [], this.stopIndexing.bind(this));
        $(document).on('click', '#' + this.name + ' .start-indexation', [], this.startIndexing.bind(this));
        $(document).on('click', '#' + this.name + ' .generate-sitemap', [], this.generateSitemap.bind(this));

    },


    indexingIterator: function (iterating) {
        this.connector.execute({
            indexing: {
                iterating: iterating
            }

        }, function (data, req) {

            if (indexed_pages = req.result.search.pages) {
                this.showIndexingResults(indexed_pages);
            }

            if (!req.result.finished) this.indexingIterator(req.result.iterating);


        }.bind(this));

    },


    stopIndexing: function (e) {
        e.preventDefault();
        this.connector.execute({
            stopIndexing: true
        });
    },

    startIndexing: function (e) {
        e.preventDefault();

        this.gridlist.clearAll();
        this.indexingIterator(false);

    },

    showIndexingResults: function (indexedPages) {
        this.gridlist.parse(indexedPages, "xjson")
    },

    generateSitemap: function (e) {
        e.preventDefault();
        this.connector.execute({
            generateSitemap: true
        });
    }


});


/*
 build_interface: function() {
 var oTabs = [
 {id:'t_index',          name: this.translate['indexation'],       callback: this.show_indexing.bind(this)},
 {id:'t_sitemaprules',   name: 'Настройки',                      callback: this.show_sitemap_rules.bind(this)},
 {id:'t_current_index',  name: this.translate['current_indexes'],  callback: this.current_indexing_results.bind(this)}

 ]

 this.tabs = new XTRFabtabs('bookmarkssv', oTabs);
 this.tabs.makeActiveById('t_index');
 XTR_main.switch_main_state('sv_container');
 this.show_indexing();
 toggle_main_menu(true);
 },

 tunes:function(){
 XTR_main.load_module_tpls(this.module_name, new Array('sitemap_tunes_header'));
 XTR_main.set_svside(XTR_main.get_tpl(this.module_name, 'sitemap_tunes_header'));

 },

 add_sitemap_rule:function()
 {
 params=xoad.html.exportForm('sitemap_tunes_header');
 $('sitemap_tunes_header').disable();
 this.connector.execute({add_sitemap_rule:params});
 this.refresh_sitemap_rules();
 $('sitemap_tunes_header').reset();
 $('sitemap_tunes_header').enable();
 },

 refresh_sitemap_rules:function()
 {
 this.connector.execute({sitemap_rules_table:true});
 this.gridlist_rules.clearAll();
 if(this.connector.result.data_set){
 this.gridlist_rules.parse(this.connector.result.data_set,"xjson");
 }
 },

 doOnSMRuleEdit:function(stage, rowId, cellInd)
 {
 if (stage == 2){
 var cellObj = this.gridlist_rules.cellById(rowId, cellInd);
 switch (cellInd) {
 case 1:
 this.connector.execute({save_sm_rule:{part:'url',       id:rowId,  text:cellObj.getValue()}});
 this.refresh_sitemap_rules();
 break;
 case 2:
 var v = parseFloat(cellObj.getValue());
 if(v>1){v=1;} if(v<0){v=0;}
 this.connector.execute({save_sm_rule:{part:'priority',  id :rowId,  text:v}});
 this.refresh_sitemap_rules();
 break;
 case 3:
 this.connector.execute({save_sm_rule:{part:'changefreq',id :rowId,  text:cellObj.getValue()}});
 this.refresh_sitemap_rules();
 break;
 }
 }
 return true;    
 },

 doOnCheckBoxChange:function(rowId, cellInd, state)
 {
 if (cellInd == 4){
 if(state){state=1}else{state=''}
 this.connector.execute({save_sm_rule:{part:'ignore',       id:rowId,  text:state}});
 this.refresh_sitemap_rules();
 }
 return true;    
 },

 doOnDrop:function(active, after)
 {
 if(!active || !after){return false;}
 this.connector.execute({update_rate:{active: active, after: after}});
 this.refresh_sitemap_rules();
 return true;
 },

 delete_rule:function(operation, id)
 {
 this.connector.execute({delete_sm_rule:{id :id}});
 this.refresh_sitemap_rules();
 if(this.connector.result.saved){
 XTR_main.set_result(_lang_pages['link_success_saved']);
 }else{
 XTR_main.set_result(_lang_common['save_error']);
 }  
 }, 

 add_rule_from_indexes:function(operation, id)
 {
 this.connector.execute({convert_row_to_rule:{operation: operation, id :id}});
 },

 show_sitemap_rules:function()
 {
 menu = new dhtmlXMenuObject();
 menu.renderAsContextMenu();
 menu.addNewChild(menu.topId, 0, "delete",_lang_common['delete'], false,'','',this.delete_rule.bind(this));

 XTR_main.load_module_tpls(this.module_name, new Array('sitemap_tunes_header')); 
 XTR_main.set_svside(XTR_main.get_tpl(this.module_name, 'sitemap_tunes_header'));


 this.gridlist_rules = new dhtmlXGridObject('rulez');
 this.gridlist_rules.selMultiRows = true;
 this.gridlist_rules.setImagePath("xres/ximg/grid/imgs/");
 this.gridlist_rules.setHeader('id,URL,priority,changefreq,'+this.translate['smr_ignore']);
 this.gridlist_rules.setInitWidths("70,650,100,150,100");
 this.gridlist_rules.setColAlign("center,left,center,left,center,center");
 this.gridlist_rules.attachHeader("#text_filter,#text_filter,#select_filter,#select_filter,#select_filter");
 this.gridlist_rules.setColTypes("ro,ed,ed,ed,ch");
 this.gridlist_rules.enableAutoWidth(true);
 this.gridlist_rules.setMultiLine(true);
 this.gridlist_rules.enableDragAndDrop(true);
 this.gridlist_rules.attachEvent("onEditCell", this.doOnSMRuleEdit.bind(this));
 this.gridlist_rules.attachEvent("onCheckbox", this.doOnCheckBoxChange.bind(this));
 this.gridlist_rules.attachEvent("onDrop", this.doOnDrop.bind(this));
 //                      this.gridlist_rules.enablePaging(true, 30, 8, "pagingArea", true, "recinfoArea");
 this.gridlist_rules.enableContextMenu(menu);  

 this.gridlist_rules.init();
 this.gridlist_rules.setSkin("modern");
 this.refresh_sitemap_rules();
 },

 show_indexing:function() {

 XTR_main.load_module_tpls(this.module_name, new Array('indexing'));
 XTR_main.set_svside(XTR_main.get_tpl(this.module_name, 'indexing'));

 this.gridlist = new dhtmlXGridObject('indexing_results');
 this.gridlist.selMultiRows = true;
 this.gridlist.setImagePath("xres/ximg/grid/imgs/");
 this.gridlist.setHeader('id,'+this.translate['link']+','+this.translate['size']+','+this.translate['title']+','+this.translate['status']);
 this.gridlist.setInitWidths("70,350,80,550,80");
 this.gridlist.setColAlign("center,left,center,left,center");
 //    this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
 this.gridlist.setColTypes("ro,ro,ro,ro,ro");
 this.gridlist.enableAutoWidth(true);
 this.gridlist.setMultiLine(true);
 //                       this.gridlist.enablePaging(true, 30, 8, "pagingArea", true, "recinfoArea");
 this.gridlist.init();
 this.gridlist.setSkin("modern");

 },

 start_indexing:function(flag) {
 this.gridlist.clearAll();
 this.flag = flag;
 setTimeout("XTR_search.indexing()",500);
 },

 force_sitemap:function() {
 this.connector.execute({generateSitemap:{}});
 },

 current_indexing_results:function() {
 menu_transport = new dhtmlXMenuObject();
 menu_transport.renderAsContextMenu();
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_mignore",  this.translate['smr_ignore_by_mask'], false,'','',this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_pignore",  this.translate['smr_ignore_by_path'], false,'','',this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_m01monthly", this.translate['smr_add_by_mask']+': priority 0.1, changefreq monthly',  false, '', '', this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_m1dayly",    this.translate['smr_add_by_mask']+': priority 1,   changefreq daily',    false, '', '', this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_m05weekly",  this.translate['smr_add_by_mask']+': priority 0.5, changefreq weekly',   false, '', '', this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_p01monthly", this.translate['smr_add_by_path']+': priority 0.1, changefreq monthly', false, '', '', this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_p1dayly",    this.translate['smr_add_by_path']+': priority 1,   changefreq daily',   false, '', '', this.add_rule_from_indexes.bind(this));
 menu_transport.addNewChild(menu_transport.topId, 0, "convert2rule_p05weekly",  this.translate['smr_add_by_path']+': priority 0.5, changefreq weekly',  false, '', '', this.add_rule_from_indexes.bind(this));


 XTR_main.load_module_tpls(this.module_name, new Array('current_indexes'));
 XTR_main.set_svside(XTR_main.get_tpl(this.module_name, 'current_indexes'));
 this.gridlist = new dhtmlXGridObject('indexing_results');

 this.gridlist.selMultiRows = true;
 this.gridlist.setImagePath("xres/ximg/grid/imgs/");
 this.gridlist.setHeader('id,'+this.translate['link']+','+this.translate['title']+','+this.translate['body']+','+this.translate['status']);
 this.gridlist.setInitWidths("70,350,200,400,80");
 this.gridlist.setColAlign("center,left,left,left,center");
 this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
 this.gridlist.setColTypes("ro,ro,ro,ro,ro");
 this.gridlist.enableAutoWidth(true);
 this.gridlist.setMultiLine(true);
 this.gridlist.enableContextMenu(menu_transport); 
 //                       this.gridlist.enablePaging(true, 30, 8, "pagingArea", true, "recinfoArea");
 this.gridlist.init();
 this.gridlist.setSkin("modern");

 this.connector.execute({indexes_table:true});

 if(this.connector.result.data_set)
 {
 this.gridlist.parse(this.connector.result.data_set,"xjson")
 this.color_rows();
 }
 },

 show_indexing_results:function(indexed_pages, k)
 {
 if(indexed_pages){
 this.gridlist.parse(indexed_pages,"xjson")
 }
 },

 color_rows:function()
 {
 this.gridlist.forEachRow(function(id){
 row=this.gridlist.getRowById(id);
 if(row._attrs.data[4]=='404'){
 row.className='red_modern';
 }
 if(row._attrs.data[4]=='301'){
 row.className='yellow_modern';
 }
 }.bind(this));
 },

 indexing:function(iterating) {
 iterating = Object.isUndefined(iterating) ? 0 : 1;
 this.connector.execute({indexing:{iterating:iterating}});
 if(!this.connector.result.finished)
 {
 if(indexed_pages = this.connector.result.search.pages)
 {
 this.show_indexing_results(indexed_pages,parseInt(this.connector.result.search.indexed_pages_count));
 }
 this.color_rows();
 setTimeout("XTR_search.indexing(2)", 1000);
 }else {

 if(indexed_pages = this.connector.result.search.pages)
 {
 this.show_indexing_results(indexed_pages,parseInt(this.connector.result.search.indexed_pages_count));
 if(this.flag)this.connector.execute({generateSitemap:true});


 }
 }
 },

 get_action_properties:function(_action) {
 this.connector.execute({get_action_properties:{Action:_action}});
 if(this.connector.result.action_properties) {
 $('action_properties').update(this.connector.lct.action_properties);
 }else {
 $('action_properties').update(_lang_common['properties_are_absent']);
 }

 xoad.html.importForm('tune_actions',this.connector.result.action_properties_form);
 this.validation=new Validation('tune_actions', {immediate : true});
 },

 destructor:function() {
 XTR_main.set_rightside();
 this.tabs.destructor();
 }
 });
 */