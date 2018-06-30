var currentPoiCategoriesId=120677;
var idFavor = null;
var conformityNumbToStr = {};
var conformityStrToNumb = {};

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
         keyBuf = key.replace('_', ".");	
         returnVar[keyBuf] = perem[key];
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


function replaceConformity(data, favors){
    var conformityNumbToStr = {};
    var conformityStrToNumb = {};
    var compileData = [];

    
    for(var i = 0; i < data.length; i++)
        conformityNumbToStr[Number(data[i].basic)] = data[i].params.Name;

    for(var i = 0; i < data.length; i++)
        conformityStrToNumb[data[i].params.Name] = Number(data[i].basic);            

    for(var i = 0; i < favors.length; i++){
        if(typeof favors[i].params['favorite.category'] === 'string')
        favors[i].params['favorite.category'] = JSON.parse(favors[i].params['favorite.category']);	
                        
        for(var j = 0; j < favors[i].params['favorite.category'].length; j++){	
            var key = favors[i].params['favorite.category'][j];
      
            if(key in conformityNumbToStr) // (number)-> (name)
                favors[i].params['favorite.category'][j] = conformityNumbToStr[favors[i].params['favorite.category'][j]];
                	
            else if(key in conformityStrToNumb) // (name)-> (number)
                favors[i].params['favorite.category'][j] = conformityStrToNumb[favors[i].params['favorite.category'][j]];        
        };

        favors[i].params["id"] = favors[i].id;
                        
        compileData.push(compileVar(favors[i].params))
    }
    return compileData;
};

function createNewFavorite(data){

    var fdata = {};
    fdata.params = data;
    fdata.ancestor=120684;
    fdata.basic="test1"+ Math.random();
    fdata.objType='_CATOBJ';
    fdata.params.PropertySetGroup=4028;		
    fdata.params['favorite.iconType'] =  getIconTypeFavorite("#form-manage-favorite");	

    fdata.params = replaceDotInUnderline(fdata.params);
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
          console.log("POST", data);
        }	
    });
};
// add search filter to favorites

(function(){
    $(document).on('click', '#form-manage-favorite-submit',function(e){		
        e.preventDefault();	
        var favoriteData = xoad.html.exportForm('form-manage-favorite');	
        favoriteData = replaceDotInUnderline(favoriteData); 


        apiGetChilds(currentPoiCategoriesId, function(data){
            if(typeof data.error!='undefined'){
                alert(data.error);
            }
            else{
                var arr = [];
                for(var i = 0; i < favoriteData['favorite.category'].length; i++){
                    var key = favoriteData['favorite.category'][i];
                    arr.push(key);
                }
                favoriteData['favorite.category'] = arr;

                var param = [{params:favoriteData}];
                favoriteData = replaceConformity(data, param); 
                console.log(favoriteData[0]);
                createNewFavorite(favoriteData[0]);

                $("#manFavorites").trigger( "click" );
            }
        });  
    });

})();


(function(){
    $(document).on('click', '#manFavorites',function(e){		
        e.preventDefault();	
        apiGetChilds(currentChildFavoriteId, function(data){
            if(typeof data.error!='undefined'){
                alert(data.error);
            }
            else{		
                getPOIcategories(data);		
            }	
        })
    });


    //close
    var removeFavorite = null;
    var currFavoriteId = null;

    $(document).on('click', '.close-favorites',function(e){		
      
        removeFavorite = this;
        currFavoriteId = 
            $(this).parent().parent().parent().attr('data-id');

        var currFavoriteTitle = 
            $(this).parent().parent().children('.item').first().children('.favorites-names').text();

        $('#deleteFavorites .modal-title-span').empty();
        $('#deleteFavorites .modal-title-span').append(currFavoriteTitle);
        
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
                    console.log("deleted");
                    $(removeFavorite).parent().parent().parent().remove();
                    overlayTopWindowClose('deleteFavorites');
                }									
            });        
    });

    //edit
    $(document).on('click', '.edit-favorites',function(e){
        idFavor = $(this).closest('.col').data('id');

        apiGetObject(idFavor, function(data){
            if(typeof data.error!='undefined'){
                alert(data.error);
            }
            else{	
                if(data.params['favorite.category'].length > 0)
                    data.params['favorite.category']= JSON.parse(data.params['favorite.category']);
                   
                    var params = data.params;

                    
                    apiGetChilds(currentPoiCategoriesId, function(data){     
                        var conformityNumbToStr = {};
                        var compileData = [];

                        if(params['favorite.category'].length > 0){
                            for(var i = 0; i < data.length; i++)
                                conformityNumbToStr[Number(data[i].basic)] = data[i].params.Name;

                            for(var i = 0; i < data.length; i++)
                                conformityStrToNumb[data[i].params.Name] = Number(data[i].basic);         
                            
                            for(var j = 0; j < params['favorite.category'].length; j++){	
                                if(params['favorite.category'][j] in conformityNumbToStr)
                                    params['favorite.category'][j] = 
                                        conformityNumbToStr[params['favorite.category'][j]];		
                            };
                            params['favorite.category'] = getSelectFormat(params['favorite.category']);
                        }

                        compileData.push(compileVar(params))         

                        xoad.html.importForm('editFavorites', params)
                        overlayTopWindow('editFavorites');    
                    });
                
            }	
        });

    });


    //save
    $(document).on('click', '#btn-edit-save',function(e){	
        getEditFavorites();
        //overlayTopWindow('confirmEditFavorites'); возможно открытие нескольких окон
    });
 

    $(document).on('click', '#btn-favorites-copy', function(e){	
        var arrFdata = [];
        arrFdata.push(xoad.html.exportForm('form-find-points-interest-position'));	
        arrFdata.push(xoad.html.exportForm('form-find-points-interest-address'));
        arrFdata.push(xoad.html.exportForm('form-find-points-interest-destination'));
        arrFdata.push(xoad.html.exportForm('form-find-points-interest-map'));

        if(arrFdata[0])
            fdata = arrFdata[0]; // default fdata

        // choose more complete form
        for(var i = 0; i < arrFdata.length; i++){
            for (var key in arrFdata[i]) {
                if( arrFdata[i].hasOwnProperty(key) ) {
                    if(arrFdata[i][key]){
                        fdata = arrFdata[i];
                        i = arrFdata.length;
                    }
                    break;
                } 
            }
        }
        fdata = compileVarDot(fdata, "favorite");
        fdata['favorite.category'] = getSelectFormat(fdata['favorite.category']);
        fdata = compileVar(fdata);
        xoad.html.importForm('form-manage-favorite', fdata);	
    });

    function getEditFavorites(){
        

        fdata=xoad.html.exportForm('editFavorites');
        fdata.params=compileVarDot(fdata.favorite,'favorite');
        delete fdata.favorite;
        
        fdata.params.PropertySetGroup=4028;	
        fdata.params['favorite.iconType'] =  getIconTypeFavorite("#editFavorites");
       
        apiGetChilds(currentPoiCategoriesId, function(data){
            if(typeof data.error!='undefined'){
                alert(data.error);
            }
            else{
                var arr = [fdata];
                fdata['params'] = (replaceConformity(data, arr))[0];
                fdata['params'].id=idFavor;
                fdata['params'] = replaceDotInUnderline(fdata['params']);
                
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
                                apiGetChilds(currentChildFavoriteId, function(data) {getPOIcategories(data); });
                            }									
                        });
                
            }
        });   
    }

    function getPOIcategories(arrFavorites){

        apiGetChilds(currentPoiCategoriesId, function(data){
            if(typeof data.error!='undefined'){
                alert(data.error);
            }
            else{
                renderMangeFavorites( replaceConformity(data, arrFavorites) ); 
            }
        });
    }

    function renderMangeFavorites(data){
        var templateScript = $('#FavoriteItemTemplate').html();
        var template = Handlebars.compile(templateScript);
        console.log("data", data);
        $("#favorites-items").html(template({result: data}));	
    };

})()