var currentPoiCategoriesId=120677;
var idFavor = null;
var conformityNumbToStr = {};
var conformityStrToNumb = {};
var poiCategoriesStr = [];
var currFavorites = [];


function renderMangeFavorites(data){
    var templateScript = $('#FavoriteItemTemplate').html();
    var template = Handlebars.compile(templateScript);
    $("#favorites-items").html(template({result: data}));	
};

function getSelectFormat(category){
    var arr = [];
    for(var i = 0; i < category.length; i++)
        arr.push({'value':category[i], 'text': category[i], 'selected': true});
    return arr;
}

function replaceDotInUnderline(perem){
    var returnVar = {};
    var keyBuf = null;
    for (key in perem) {
        if (perem.hasOwnProperty(key)) {
            keyBuf = key.replace('_', ".");	
            returnVar[keyBuf] = perem[key];
        }     
    };
    return returnVar;
}

function getIconTypeFavorite(form){
    var src = $(form).find('.checked').siblings('img').attr('src');
    var type = "";

    if(!src) return null;

    for(var i = src.length-1; i >= 0; i--){
        if(src[i] === '/') break;
        type = src[i] + type;
    }
    return type;
}


function replaceConformity(favors){
    if(favors.length == 0) return [];

    var compileData = [];

    for(var i = 0; i < favors.length; i++){
        if(!favors[i].params) continue;
        if( typeof favors[i].params['favorite.category'] === 'string')
        favors[i].params['favorite.category'] = JSON.parse(favors[i].params['favorite.category']);	
                        
        for(var j = 0; favors[i].params['favorite.category'] && j < favors[i].params['favorite.category'].length; j++){	
            var key = favors[i].params['favorite.category'][j];
      
            if(key in conformityNumbToStr) // (number)-> (name)
                favors[i].params['favorite.category'][j] = conformityNumbToStr[favors[i].params['favorite.category'][j]];
                	
            else if(key in conformityStrToNumb) // (name)-> (number)
                favors[i].params['favorite.category'][j] = conformityStrToNumb[favors[i].params['favorite.category'][j]];        
        };

        if(favors[i].id)
            favors[i].params["id"] = favors[i].id;

        compileData.push(favors[i].params);
    }
    return compileData;
};


function createNewFavorite(data){
    var fdata = {};

    if(!data) return;

    fdata = data;
    fdata.ancestor=120684;
    fdata.basic="test1"+ Math.random();
    fdata.objType='_CATOBJ';
    fdata.params.PropertySetGroup=4028;		
    fdata.params['favorite.iconType'] =  getIconTypeFavorite("#form-manage-favorite");
    fdata['params'].id = fdata.basic;

    currFavorites.push(fdata.basic);
    $.ajax({
        type: "POST",
        url: host+'~api/json/catalog/createNewObject',             
        contentType: "application/json; charset=utf-8",
        traditional: true,
        data:JSON.stringify(fdata)

    }).done(function(data){
        if(typeof data.error!='undefined'){
            alert(data.error);
        }
        else{
            updateCurrSetFavorites();
            renderListOfFavorites();
        }	
    });
};
// add search filter to favorites
function renderListOfFavorites(fdata){

    apiGetChilds(currentChildFavoriteId, function(data){
        if(typeof data.error!='undefined'){
            alert(data.error);
        }
        else{	
            var fdata = []; 
            data = getCurrentFavorites(data);
            data = replaceConformity(data);
 
            for(var i = 0; i < data.length; i++){              
                fdata.push(data[i]); 
                fdata[i] = compileVar(fdata[i]);
            }   

            var templateScript = $('#listOfFavorites').html();
            var template = Handlebars.compile(templateScript);
            $("#list-of-favorites").html(template({result: fdata}));

            var templateScript = $('#listOfFavoritesHideDetails').html();
            var template = Handlebars.compile(templateScript);
            $(" #hidden-favorites-accardion").html(template({result: fdata}));
        }	
    })
}

function decreaseItemReview(){

    $('.POIListItems').find('span.text').each(function() {
        var title = $(this).text(); 
            textSelect = $(this).find('.more'); 
            textBlock = $(textSelect).text();
            text = textBlock; 
            link = $(textSelect).attr('href'); //
            moreText = '[...]'
            if(!link) link="'#'";
            more = "<a href='" + link + "'> more </a>";
            

        if (title.length >= 100) {

            if (text.length > 270) { 
                text = text.substr(0, 270);  //
                $(textSelect).html( text + more ); 
            } else {
                title = title.substr(0, 100);
                $(this).text(title);
                $(this).append( more );
            }

        } 
    });
}

function updateCurrSetFavorites(){
    apiGetChilds(currentChildFavoriteId, function(data){
        if(typeof data.error!='undefined'){
            alert(data.error);
        }
        else{	
            data = getCurrentFavorites(data);
            data = replaceConformity(data);
            for(var i = 0; i < data.length; i++){
                data[i] = compileVar(data[i]);
            }
            
            renderMangeFavorites(data); 
        }	
    })
}
(function(){

    apiGetChilds(currentPoiCategoriesId, function(data){
        if(typeof data.error!='undefined'){
            alert(data.error);
        }
        else{   
           
            for(var i = 0; i < data.length; i++){
                conformityNumbToStr[Number(data[i].basic)] = data[i].params.Name;
                poiCategoriesStr.push(data[i].params.Name);
            }
   
            for(var i = 0; i < data.length; i++)
                conformityStrToNumb[data[i].params.Name] = Number(data[i].basic); 
             
               // renderListOfFavorites();
        }
    });

    $(document).on('click', '#form-manage-favorite-submit',function(e){		
        e.preventDefault();	

        var data = xoad.html.exportForm('form-manage-favorite');	
            data = replaceDotInUnderline(data); 
            data = {'params': replaceConformity( [{params:data}] )[0]}; 

        createNewFavorite(data);

        $('.overlay').removeClass('is-active');
		$("#manageFavorites").removeClass('is-active');
    });
})();

function getCurrentFavorites(data){
    var fdata = [],
        max   = data.length;

     try {
        for(var i = 0; i < max; i++){
            if(currFavorites.indexOf(data[i].basic) != -1){
                fdata.push(data[i]);
            }
        }
    } catch (err) {
          console.log(err);
    }
    return fdata;
}

(function(){
    
    $(document).on('click', '#manFavorites',function(e){		
        e.preventDefault();	

        updateCurrSetFavorites();
    });


    //close
    var removeFavorite = null;
    var currFavoriteId = null;

    $(document).on('click', '.close-favorites',function(e){		
      
        removeFavorite = this;
        currFavoriteId =  $(this).parent().parent().parent().attr('data-id');

        var title =  $(this).parent().parent().children('.item').first().children('.favorites-names').text();

        $('#deleteFavorites .modal-title-span').empty();
        $('#deleteFavorites .modal-title-span').append(title);
        
        overlayTopWindow('deleteFavorites');
    });
    
    $('#deleteFavorites .deleteFavorites').click(function() {
        $.ajax({
            type: "DELETE",
            url: host+'~api/json/catalog/deleteObject/id/'+ currFavoriteId,            
            contentType: "application/json; charset=utf-8"
            }).done(function(data){
                        
                if(typeof data.error!='undefined'){
                    alert(data.error);
                }
                else{
                    $(removeFavorite).parent().parent().parent().remove();
                    overlayTopWindowClose('deleteFavorites');
                    renderListOfFavorites();
                }									
            });        
    });

    //edit
    function turnOnCheckedImg(path){
        $('#editFavorites').find('img').siblings('.checkbox').removeClass('checked');
        $('#editFavorites').find('img[src*="' + path + '"]').siblings('.checkbox').addClass('checked'); 
    }

    function resetInputForm(path){

        $("#edit-favorites-lang-select").prop('selectedIndex',2);
        $("#edit-favorites-search-select").prop('selectedIndex',2);
        $("#edit-favorites-language-select").prop('selectedIndex',0);
        
    }

    $(document).on('click', '.edit-favorites',function(e){
        idFavor = $(this).closest('.col').data('id');
  
        apiGetObject(idFavor, function(data){
            if(typeof data.error!='undefined'){
                alert(data.error);
            }
            else{	
                debugger;
                    resetInputForm('editFavorites');
                   
                    var params = data.params; 
                    if( params['favorite.category'].length > 0)
                        params['favorite.category']= JSON.parse(params['favorite.category']);

                    params = replaceConformity(new Array(data))[0];
                    if(params){
                        params['favorite.category'] = getSelectFormat(params['favorite.category']);
                    } 

                    turnOnCheckedImg(params['favorite.iconType']);
                    $('#editFavorites').find('option[value='+ params['favorite.language']+ ']').attr('selected', true);

                    xoad.html.importForm('editFavorites', compileVar(params));
                    
                    $('#editFavorites').find('select').trigger('refresh');
                    

                    overlayTopWindow('editFavorites');     
            }	
        });

    });

    $(document).on('click', '#btn-edit-save',function(e){	
        getEditFavorites();
    });
 
    $(document).on('click', '#btn-favorites-copy', function(e){	
        var href = '',
            form = {};
            
        // export data from active tabs    
        href = $('.deeplinked-tabs-pois').children('li.tabs-title.is-active');
        href = $(href).children('a').attr('href');
        form = $(href).children('form').attr('id');
        fdata = xoad.html.exportForm(form);	
        fdata = compileVarDot(fdata, "favorite");

        refreshFormSelect("#form-manage-favorite .select2-hidden-accessible");

        insertSelectedItems('#form-manage-favorite', fdata['favorite.language']);
        insertSelectedItems('#form-manage-favorite', fdata['favorite.category']);
        xoad.html.importForm('form-manage-favorite', {'favorite_what': fdata['favorite.what']});	
       
        $("#form-manage-favorite .select2-hidden-accessible").trigger("change");
        $('#form-manage-favorite').find('select').trigger('refresh');        
    });

    function refreshFormSelect(select){
        var sdata = [];

        $(select).empty().trigger("change");

        for(var i = 0; i < poiCategoriesStr.length; i++){
            var value = poiCategoriesStr[i];
            sdata.push(
                {
                    id : value,
                    text : value
                } 
            );
        } 
         
         $(select).select2( { data : sdata});
    }


    function insertSelectedItems(form, data){
       
        if(data instanceof Array){
            var max = data.length;
           
            for(var i = 0; i < max; i++){

                var value = data[i];
                    value = "option[value=\""+ value + "\"]";
                $(form).find(value).attr('selected', 'selected');
            }
        }
        else{
            $(form).find('option[value='+ data+ ']').attr('selected', 'selected');
        }
    }

    function getEditFavorites(){
        var data  = {},
            fdata = {};

        data=xoad.html.exportForm('editFavorites');
        data = replaceDotInUnderline(data);
        fdata = {'params':data};

        fdata.params = (replaceConformity(new Array(fdata)))[0];
        fdata.params.PropertySetGroup=4028;	
        fdata.params['favorite.iconType'] =  getIconTypeFavorite("#editFavorites");
      
        fdata['params'].id=idFavor;

            $.ajax({
                type: "PUT",
                url: host+'~api/json/catalog/setObjectParams/id/'+ idFavor,            
                contentType: "application/json; charset=utf-8",
                traditional: true,
                data: JSON.stringify(fdata.params)
                }).done(function(data){
                            
                    if(typeof data.error!='undefined'){
                        alert(data.error);
                    }
                    else{
                        overlayTopWindowClose('editFavorites');	
                        $("#manFavorites").trigger( "click" );
                        renderListOfFavorites();
                    }									
                }); 
    }

})()