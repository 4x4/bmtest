dhtmlXDataView.prototype.types.ficon={
	css:"ficon",
	template:dhtmlx.Template.fromHTML("<div align='center'><img onmousedown='return false;' border='0' src='{common.image()}'><div class='dhx_item_text'>{obj.name}</div>"
    +"<div class='dhx_item_text_small'>{common.size()}</div></div>"),
    
	template_edit:dhtmlx.Template.fromHTML("<div align='center'><img onmousedown='return false;' border='0' src='{common.image()}'><input class='dhx_item_editor' bind='obj.name'></div>"),
	template_loading:dhtmlx.Template.fromHTML(""),
	width:100,
	height:110,
	margin:1,
	padding:0,
	drag_marker:"dnd_selector_cells.png",
	//custom properties
	icons_src_dir:"./",
	image:function(obj){
		return this.icons_src_dir + "/"+obj.type+".png";
	},
    size:function(obj)
    {
        return obj.size?obj.size:'';
    },
	text:function(obj){
		return obj.name.split(".")[0];
	}
};

dhtmlXDataView.prototype.types.ftiles={
	css:"ftiles",
	template:dhtmlx.Template.fromHTML("<img onmousedown='return false;' style='width:48px; float: left;' border='0' src='{common.image()}'><div style='margin-top: 10px;' class='dhx_item_text'>{common.text()}</div><div class='dhx_item_text_gray'>{common.size()}</div>"),
	template_edit:dhtmlx.Template.fromHTML("<img onmousedown='return false;' style='width:48px; float: left;' border='0' src='{common.image()}'><textarea class='dhx_item_editor' bind='obj.name'></textarea></div>"),
	template_loading:dhtmlx.Template.fromHTML(""),
	width:140,
	height:58,
	margin:1,
	padding:4,
	drag_marker:"dnd_selector_cells.png",
	//custom properties
	icons_src_dir:"./",
	image:function(obj){
        return this.icons_src_dir + "/"+obj.type+".png";
    },
	  size:function(obj)
    {
        return obj.size?obj.size:'';
    },
    text:function(obj){
        return obj.name.split(".")[0];
    }
};

dhtmlXDataView.prototype.types.ftable={
	css:"ftable",
	template:dhtmlx.Template.fromHTML("<div style='float: left; width: 70px;'><img onmousedown='return false;' border='0' src='{common.image()}'></div><div style='float: left; padding:18px 0px 0px 0px;  width: 45%; overflow:hidden;' class='dhx_item_text'><span style='padding 0px 2px 0px 2px;'>{obj.name}</span></div><div style='float: left; width: 60px; text-align: right;' class='dhx_item_text'>{common.size()}</div><div style='float: left; width: 130px; padding-left: 10px;' class='dhx_item_text'>{common.date()}</div>"),
	template_edit:dhtmlx.Template.fromHTML("<div style='float: left; width: 17px;'><img onmousedown='return false;' border='0' src='{common.image()}'></div><div style='float: left; width: 115px;' class='dhx_item_text'><span style='padding-left: 2px; padding-right: 2px;'><input type='text' style='width:100%; height:100%;' bind='obj.name'></span></div><div style='float: left; width: 60px; text-align: right;' class='dhx_item_text'>{common.size()}</div><div style='float: left; width: 130px; padding-left: 10px;' class='dhx_item_text'>{common.date()}</div>"),
	template_loading:dhtmlx.Template.fromHTML(""),
	width:800,
	height:55,
	margin:1,
	padding:0,
	drag_marker:"dnd_selector_lines.png",
	//custom properties
	icons_src_dir:"./",
    image:function(obj){
        return this.icons_src_dir + "/"+obj.type+".png";
    },
      size:function(obj)
    {
        return obj.size?obj.size:'';
    },
    text:function(obj){
        return obj.name.split(".")[0];
    },
	date:function(obj){
        
		return obj.mod;
	}
};

dhtmlXDataView.prototype.types.fthumbs={
	css:"fthumbs",
	template:dhtmlx.Template.fromHTML("<div align='center'><img border='0' width='50px' src='{common.image()}'><div class='dhx_item_text'><span style='font-size:9px'>{common.text()}</span></div></div>"),
	width:85,
	height:85,
	margin:15,
	padding:4,
    image_types:['png','jpg','gif','jpeg'],
	//custom properties
	thumbs_creator_url:"./",
	photos_rel_dir:"./",
	image:function(obj){
        
        
        
        if(this.image_types.indexOf(obj.type)!=-1)
        {
            
		    return obj.fullpath;
            
        }else{
            
              return this.icons_src_dir + "/"+obj.type+".png";
        }
	},
	text:function(obj){
		return obj.name.split(".")[0];
	}
};




dhtmlXDataView.prototype.types.fdatalistitem={
    css:"fdatalistitem",
    
    template:dhtmlx.Template.fromHTML("<span class='pull-right'> <a class='remove' href='#'><i class='fa fa-times icon-muted fa-fw'></i></a> </span> <div class='clear'>{common.sid()} | {common.text()} </div>"),

    template_edit:dhtmlx.Template.fromHTML("<div style='float: left; width: 17px;'><img onmousedown='return false;' border='0' src='{common.image()}'></div><div style='float: left; width: 115px;' class='dhx_item_text'><span style='padding-left: 2px; padding-right: 2px;'><input type='text' style='width:100%; height:100%;' bind='obj.name'></span></div><div style='float: left; width: 60px; text-align: right;' class='dhx_item_text'>{common.size()}</div><div style='float: left; width: 130px; padding-left: 10px;' class='dhx_item_text'>{common.date()}</div>"),
    template_loading:dhtmlx.Template.fromHTML(""),
    width:800,
    height:55,
    margin:1,
    padding:0,
    drag_marker:"dnd_selector_lines.png",
    //custom properties
    icons_src_dir:"./",
    image:function(obj){
      
    },
      sid:function(obj)
    {
        return obj.sid;
    },
    text:function(obj){
        return obj.name;
    },
    
};