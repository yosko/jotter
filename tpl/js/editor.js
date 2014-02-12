//constructor
var BaseEditor = function() {
    this.saveButton = null;
    this.saveImage = null;
    this.editor = null;
    this.unsavedContent = false;
    this.currentlySaving = false;
    this.cancelKeypress = false;   //workaround for Firefox bug
    this.isCtrl = false;
};

//prototype
BaseEditor.prototype = {
    init: function() {
        var that = this;

        this.saveButton = document.getElementById('save-button');
        this.saveImage = document.getElementById('save-button').querySelector('img'); //TODO fix this bug: point to the <img> inside ID 'save-button'
        this.editor = document.getElementById('editor');

        /**
         * EVENTS
         */
        
        document.addEventListener('input', function (e) {
            that.setUnsavedStatus.call(that, true);
            that.textareaFitToContent.call(that);
        });

        document.addEventListener('keydown', function (e) {
            if(e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
                e.preventDefault();
                if(that.unsavedContent) {
                    that.cancelKeypress = true;
                    that.saveNote.call(that);
                }
            }
        });

        /**
         * Workaround for Firefox bug:
         * e.preventDefault(); and e.stopPropagation(); won't suffice in the keydown
         * event, and Firefox will still propagate to keypress in a specific case
         * where some non-basic code is executed during the keydown handler..
         */
        document.addEventListener('keypress', function (e){
            if(that.cancelKeypress === true) {
                e.preventDefault();
                that.cancelKeypress = false;
            }
        });

        //auto save every 30 seconds
        setInterval(function(){
            if(this.unsavedContent && !this.currentlySaving)
                this.saveNote.call(this);
        }, 30000);

        //click on save button
        this.saveButton.onclick = function(e) {
            if(that.unsavedContent)
                that.saveNote.call(that);
            e.preventDefault();
        };

        //avoid leaving page without saving
        window.onbeforeunload = function(e) {
            that.checkIsUnsaved.call(that, e);
        };

        this.customInit.call(this);
    },
    customInit: function() {
        //markdown editor
        this.editor.setAttribute('contenteditable', true);
        this.textareaFitToContent.call(this);
    },
    textareaFitToContent: function() {
        var lineHeight = window.getComputedStyle(editor).lineHeight;
        lineHeight = parseInt(lineHeight.substr(0, lineHeight.length-2), 10);
        if (editor.clientHeight == editor.scrollHeight)
            editor.style.height = (lineHeight*4) + 'px';

        if ( editor.scrollHeight > editor.clientHeight ) {
            editor.style.height = (editor.scrollHeight + lineHeight) + "px";
        }
    },
    saveNote: function() {
        this.currentlySaving = true;
        this.saveButton.setAttribute('title', 'Saving...');
        this.changeImageFile.call(this, 'ajax-loader.gif');

        var text = '';
        if(this.editor.nodeName == 'ARTICLE') {
            text = this.editor.innerHTML;
        } else {
            text = this.editor.value;
        }

        var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
        var item = document.getElementById('selected').getAttribute('data-path');
        var data = new FormData();
        data.append('text', text);

        //send save request to server
        var request = new XMLHttpRequest();
        request.open('POST','?action=save&nb='+notebook+'&item='+item,false);
        request.send(data);
        response = JSON.parse(request.responseText);

        //the note was saved
        if(response === true) {
            this.setUnsavedStatus.call(this, false);

        //error, the note wasn't saved
        } else {
            this.changeImageFile.call(this, 'disk--exclamation.png');
            this.saveButton.setAttribute('title', 'Error: couldn\'t save this note.');
        }
        this.currentlySaving = false;
        return false;
    },
    checkIsUnsaved: function(e) {
        if(this.unsavedContent) {
            e.preventDefault();
            return "There is unsaved content. Do you still wish to leave this page?";
        }
    },
    setUnsavedStatus: function(status) {
        this.unsavedContent = status;

        if(this.unsavedContent) {

            this.unsavedContent = true;

            this.saveButton.classList.remove('disabled');
            this.saveButton.setAttribute('title', 'Save changes');
            this.changeImageFile.call(this, 'disk.png');
        } else {
            this.changeImageFile.call(this, 'disk-black.png');
            
            this.saveButton.classList.add('disabled');
            this.saveButton.setAttribute('title', 'Nothing to save');
        }
    },
    changeImageFile: function(newFileName) {
        var dirPath = this.saveImage.getAttribute('src').substring(0,this.saveImage.getAttribute('src').lastIndexOf('/') +1 );
        this.saveImage.setAttribute('src', dirPath+'/'+newFileName);
    }
};
