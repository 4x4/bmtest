var classyEditor = {
      
    // Methods
    closeEditor : function(cell, save) {
        // Get value
        $(classyEditor.cell).html(classyEditor.txt.find('textarea').val());
        classyEditor.txt.remove();
        $(classyEditor.cell).removeClass('edition');
      
        
    },
    openEditor : function(cell) {
        var main = this;
        // Get current content
        var html = $(cell).html();
        
        
        var textArea = $('<div style="background:white"><textarea cols="5"/><button style="width:100%">OK</button></div>').appendTo('body');
        
        var t = parseInt($(cell).offset().top) + parseInt($(cell).height()) + 15;
        var l = parseInt($(cell).offset().left);
        var width=parseInt($(cell).width());
        classyEditor.txt=$(textArea);
        classyEditor.cell=cell;
        // Place the corner in the correct place
        $(textArea).css('position', 'absolute');
        $(textArea).css('top', t);
        $(textArea).css('left', l);
        $(textArea).css('width', width);        
        $(textArea).find('textarea').val(html);
        $(textArea).find('textarea').ClassyEdit();
        $(textArea).find("button").click(classyEditor.closeEditor);
        
        
    },
    getValue : function(cell) {
        return $(cell).html();
    },
    setValue : function(cell, value) {
        $(cell).html(value);        
        return true;
    }
}
