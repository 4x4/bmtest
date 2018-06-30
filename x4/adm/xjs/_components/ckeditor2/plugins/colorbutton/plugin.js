(function(){
//Section 1 : Code to execute when the toolbar button is pressed
var a= {
exec:function(editor){
 var theSelectedText = editor.getSelection().getNative();
 alert(theSelectedText);
}
},

//Section 2 : Create the button and add the functionality to it
b='colorbutton';
CKEDITOR.plugins.add(b,{
init:function(editor){
    debugger;
editor.addCommand(b,a);
editor.ui.addButton("colorbutton",{
    label:'Add Tag', 
    command:b
    });
}
}); 
})();