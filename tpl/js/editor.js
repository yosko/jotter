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
        this.saveButton = document.getElementById('save-button');
        this.saveImage = document.getElementById('save-button').querySelector('img'); //TODO fix this bug: point to the <img> inside ID 'save-button'
        this.editor = document.getElementById('editor');

        /**
         * EVENTS
         */
        
        document.addEventListener('input', function (e) {
            this.setUnsavedStatus.call(this, true);
            this.textareaFitToContent.call(this);
        }.bind(this));

        document.addEventListener('keydown', function (e) {
            if(e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
                e.preventDefault();
                if(this.unsavedContent) {
                    this.cancelKeypress = true;
                    this.saveNote.call(this);
                }
            }
        }.bind(this));

        /**
         * Workaround for Firefox bug:
         * e.preventDefault(); and e.stopPropagation(); won't suffice in the keydown
         * event, and Firefox will still propagate to keypress in a specific case
         * where some non-basic code is executed during the keydown handler..
         */
        document.addEventListener('keypress', function (e){
            if(this.cancelKeypress === true) {
                e.preventDefault();
                this.cancelKeypress = false;
            }
        }.bind(this));

        //auto save every 30 seconds
        setInterval(function(){
            if(this.unsavedContent && !this.currentlySaving)
                this.saveNote.call(this);
        }, 30000);

        //click on save button
        this.saveButton.addEventListener('click', function (e){
            if(this.unsavedContent)
                this.saveNote.call(this);
            e.preventDefault();
        }.bind(this));

        //avoid leaving page without saving
        window.addEventListener('beforeunload', function (e){
            this.checkIsUnsaved.call(this, e);
        }.bind(this));

        this.customInit.call(this);
    },
    customInit: function() {
        //markdown editor
        this.editor.setAttribute('contenteditable', true);
        this.textareaFitToContent.call(this);

        document.getElementById('preview-button').addEventListener('click', function (e){
            var preview = null;
            var button = document.getElementById('preview-button');
            e.preventDefault();
            button.parentNode.classList.toggle('active');
            if(button.parentNode.classList.contains('active')) {
                //prepare preview container
                this.editor.style.display = 'none';
                preview = document.createElement('div');
                preview.setAttribute('id','preview');
                this.editor.parentNode.insertBefore(preview, this.editor.nextSibling);

                //show a loading gif
                var loadingGif = document.createElement('img');
                loadingGif.setAttribute('src', 'tpl/img/ajax-loader.gif');
                loadingGif.setAttribute('alt', 'Loading...');
                loadingGif.setAttribute('id', 'loadingGif');
                preview.appendChild(loadingGif);

                //send preview request to server
                var request = new XMLHttpRequest();
                var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
                var item = document.getElementById('selected').getAttribute('data-path');
                request.open('GET','?action=ajax&option=preview&nb='+notebook+'&item='+item,false);
                request.send();
                response = JSON.parse(request.responseText);

                //replace gif with the parsed note
                if(response !== false) {
                    preview.innerHTML = response;
                }
            } else {
                this.editor.style.display = 'block';
                preview = document.getElementById('preview');
                preview.parentNode.removeChild(preview);
            }
        }.bind(this));

    },
    textareaFitToContent: function() {
        var lineHeight = window.getComputedStyle(this.editor).lineHeight;
        lineHeight = parseInt(lineHeight.substr(0, lineHeight.length-2), 10);
        if (this.editor.clientHeight == this.editor.scrollHeight)
            this.editor.style.height = (lineHeight*4) + 'px';

        if ( this.editor.scrollHeight > this.editor.clientHeight ) {
            this.editor.style.height = (this.editor.scrollHeight + lineHeight) + "px";
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
        request.open('POST','?action=ajax&option=save&nb='+notebook+'&item='+item,false);
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
