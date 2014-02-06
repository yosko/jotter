//constructor
var EditorHandler = function(saveButton, editor) {
    this.saveButton = document.getElementById(saveButton);
    this.saveImage = document.getElementById(saveButton+' img');
    this.editor = document.getElementById(editor);
    this.unsavedContent = false;
    this.currentlySaving = false;
    this.isCtrl = false;
};

//prototype
EditorHandler.prototype = function() {

    var init = function() {
        console.log('init editor');

        /**
         * EVENTS
         */

        document.onkeyup=function(e){
            if(e.keyCode == 17) this.isCtrl=false;
        };

        document.onkeydown=function(e){
            //if Ctrl
            if(e.keyCode == 17) {
                this.isCtrl=true;
            }

            //Ctrl+S
            if(e.keyCode == 83 && this.isCtrl === true) {
                if(this.unsavedContent)
                    saveNote.call(this);
                e.preventDefault();
                return false;
            }
        };

        //auto save every 30 seconds
        setInterval(function(){
            if(this.unsavedContent && !this.currentlySaving)
                saveNote.call(this);
        }, 30000);

        //click on save button
        this.saveButton.onclick = function(e) {
            if(this.unsavedContent)
                saveNote.call(this);
            e.preventDefault();
        };

        //avoid leaving page without saving
        var that = this;
        window.onbeforeunload = function(e) {
            checkIsUnsaved.call(that);
        };

        customInit.call(this);
    },
    customInit = function() {
        console.log('custom init (empty)');
        //abstract method that can be overwritten by specific editors (such as wysiwyg)
    },
    saveNote = function() {
        console.log('save note');
        this.currentlySaving = true;
        saveButton.setAttribute('title', 'Saving...');
        changeImageFile.call(this, saveImage, 'ajax-loader.gif');

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
            setUnsavedStatus.call(this, false);

        //error, the note wasn't saved
        } else {
            changeImageFile.call(this, saveImage, 'disk--exclamation.png');
            saveButton.setAttribute('title', 'Error: couldn\'t save this note.');
        }
        this.currentlySaving = false;
    },
    checkIsUnsaved = function() {
        console.log('unload - '+this.unsavedContent);
        if(this.unsavedContent)
            return "There is unsaved content. Do you still wish to leave this page?";
    },
    setUnsavedStatus = function(status) {
        console.log('change save status');
        this.unsavedContent = status;

        if(this.unsavedContent) {

            this.unsavedContent = true;

            saveButton.removeClass('disabled');
            saveButton.setAttribute('title', 'Save changes');
            changeImageFile.call(this, saveImage, 'disk.png');

            //when user delete everything inside the editor, make sure there is still a <p>
            //TODO: handle without jquery?
            editorNeverEmpty();
        } else {
            changeImageFile.call(this, saveImage, 'disk-black.png');
            
            saveButton.addClass('disabled');
            saveButton.setAttribute('title', 'Nothing to save');
        }
    },
    changeImageFile = function(image, newFileName) {
        console.log('change image');
        var dirPath = image.getAttribute('src').substring(0,image.getAttribute('src').lastIndexOf('/') +1 );
        image.setAttribute('src', dirPath+'/'+newFileName);
    };

    //return public members
    return {
        init: init,
        saveNote: saveNote
    };
} ();


window.addEventListener('load', function (){
    //instanciate editor tools
    var editorHandler = new EditorHandler('save-button', 'editor');
    editorHandler.init();
});
