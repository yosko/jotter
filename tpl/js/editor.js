var unsavedContent  = false;
var currentlySaving = false;
var isCtrl = false;

window.addEventListener('load', function (){
    var saveButton = document.getElementById('save-button');
    var saveImage = document.getElementById('save-button img');

    /**
     * Handle shortcuts with Ctrl
     */
    document.onkeyup=function(e){
        if(e.keyCode == 17) isCtrl=false;
    }

    /**
     * Handle shortcuts with Ctrl
     */
    document.onkeydown=function(e){
        //if Ctrl
        if(e.keyCode == 17) {
            isCtrl=true;
        }

        //Ctrl+S
        if(e.keyCode == 83 && isCtrl == true) {
            if(unsavedContent)
                saveNote();
            event.preventDefault();
            return false;
        }
    }

    //auto save every 30 seconds
    setInterval(function(){
        if(unsavedContent && !currentlySaving)
            saveNote();
    }, 30000);

    //click on save button
    saveButton.onclick = function(e) {
        if(unsavedContent)
            saveNote();
        e.preventDefault();
    }

    //avoid leaving page without saving
    window.onbeforeunload = checkIsUnsaved;



    /**
     * TODO : convert code to remove jquery
     */

    function saveNote() {
        currentlySaving = true;
        saveButton.setAttribute('title', 'Saving...');
        saveImage.changeImageFile('ajax-loader.gif');

        var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
        var item = document.getElementById('selected').getAttribute('data-path');
        var data=new Object();
        data['text'] = document.getElementById('editor').innerHTML;

        $.ajax({
            type: 'POST',
            url: '?action=save&nb='+notebook+'&item='+item,
            data: data,
            success: function(response){
                //the note was saved
                if(response !== false) {
                    setUnsavedStatus(false);

                //error, the note wasn't saved
                } else {
                    changeImageFile(saveImage, 'disk--exclamation.png');
                    saveButton.setAttribute('title', 'Error: couldn\'t save this note.');
                }
                currentlySaving = false;
            },
            dataType: 'json'
        });
    }

    function setUnsavedStatus(status) {
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
    }

    function checkIsUnsaved() {
        if(unsavedContent)
            return "There is unsaved content. Do you still wish to leave this page?";
    }

    function changeImageFile(image, newFileName) {
        var dirPath = image.getAttribute('src').substring(0,image.getAttribute('src').lastIndexOf('/') +1 );
        image.setAttribute('src', dirPath+'/'+newFileName);
    }
});
