//constructor
var EditorHandler = function(saveButton, editor) {
    this.saveButton = document.getElementById(saveButton);
    this.saveImage = document.getElementById(saveButton+' img');
    this.editor = document.getElementById(editor);
};

//prototype
EditorHandler.prototype = {
    var unsavedContent = false;
    var currentlySaving = false;
    var isCtrl = false;
    var init = function() {

            /**
             * EVENTS
             */

            document.onkeyup=function(e){
                if(e.keyCode == 17) isCtrl=false;
            };

            document.onkeydown=function(e){
                //if Ctrl
                if(e.keyCode == 17) {
                    isCtrl=true;
                }

                //Ctrl+S
                if(e.keyCode == 83 && isCtrl === true) {
                    if(unsavedContent)
                        saveNote();
                    event.preventDefault();
                    return false;
                }
            };

            //auto save every 30 seconds
            setInterval(function(){
                if(unsavedContent && !currentlySaving)
                    saveNote();
            }, 30000);

            //click on save button
            this.saveButton.onclick = function(e) {
                if(unsavedContent)
                    saveNote();
                e.preventDefault();
            };

            //avoid leaving page without saving
            window.onbeforeunload = checkIsUnsaved;
        },
        saveNote = function() {
            currentlySaving = true;
            saveButton.setAttribute('title', 'Saving...');
            changeImageFile(saveImage, 'ajax-loader.gif');

            var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
            var item = document.getElementById('selected').getAttribute('data-path');
            var data = new FormData();
            data.append('text', document.getElementById('editor').innerHTML);

            //send save request to server
            var request = new XMLHttpRequest();
            request.open('POST','?action=save&nb='+notebook+'&item='+item,false);
            request.send(data);
            response = JSON.parse(request.responseText);

            //the note was saved
            if(response === true) {
                setUnsavedStatus(false);

            //error, the note wasn't saved
            } else {
                changeImageFile(saveImage, 'disk--exclamation.png');
                saveButton.setAttribute('title', 'Error: couldn\'t save this note.');
            }
            currentlySaving = false;
        },
        checkIsUnsaved = function() {
            if(unsavedContent)
                return "There is unsaved content. Do you still wish to leave this page?";
        },
        setUnsavedStatus = function(status) {
            unsavedContent = status;

            if(unsavedContent) {

                unsavedContent = true;

                saveButton.removeClass('disabled');
                saveButton.setAttribute('title', 'Save changes');
                changeImageFile(saveImage, 'disk.png');

                //when user delete everything inside the editor, make sure there is still a <p>
                //TODO: handle without jquery?
                editorNeverEmpty();
            } else {
                changeImageFile(saveImage, 'disk-black.png');
                
                saveButton.addClass('disabled');
                saveButton.setAttribute('title', 'Nothing to save');
            }
        },
        changeImageFile = function(image, newFileName) {
            var dirPath = image.getAttribute('src').substring(0,image.getAttribute('src').lastIndexOf('/') +1 );
            image.setAttribute('src', dirPath+'/'+newFileName);
        };

    //return public methods
    return {
        init: init,
        saveNote: saveNote
    };
}();

//instance
var editorHandler = null;
window.onload = function () {
    editorHandler = new EditorHandler('save-button', 'editor');
    editorHandler.init();
}

window.addEventListener('load', function (){

});
