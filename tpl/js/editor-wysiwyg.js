
var WysiwygEditor = function () {
    
};

WysiwygEditor.prototype = new BaseEditor();

WysiwygEditor.prototype.customInit = function () {
    var editor = $('#editor');

    document.addEventListener('input', function (e) {
        //when user delete everything inside the editor, make sure there is still a <p>
        //TODO: handle without jquery?
        this.editorNeverEmpty.call(this);

        var html = $('#html');
        if(html !== null && html.length !== 0) {
            html.html( this.getEditorHtmlForDisplay.call(this) );
        }
    }.bind(this));

    //init editor
    editor.wysiwyg({
        activeToolbarClass: 'selected'
    }).focus();

    //doesn't seem to work in firefox, which still use <br>
    document.execCommand('defaultParagraphSeparator', false, 'p');

    //if note is empty on load, add a <p>
    this.editorNeverEmpty.call(this);

    //display html source
    $('#source-button').click(function(e){
        if($('#html').length === 0) {
            $('#editor').after( '<pre id="html" style="">'+this.getEditorHtmlForDisplay.call(this)+'</pre>' );
        } else {
            $('#html').remove();
        }
        e.preventDefault();
    }.bind(this));

    //insert an em dash
    $('#mdash-button').click(function(e){
        document.execCommand('insertHTML', false, '&nbsp;&mdash;&nbsp;');
        e.preventDefault();
    });

    $('#picture-button').click(function(e) {
        $('#hidden-picture-button').click();
        e.preventDefault();
    });

    //add 'http://' to link input
    $('#insertLink input').focus(function(e){
        var input = $(this);
        if(input.val().trim() === '') {
            input.val(input.attr('placeholder'));
        }
    });

    //show/hide subtoolbars
    $('#linkDropdown').click(function(e){
        $('#insertLink').toggle();
        e.preventDefault();
    });
    $('#insertLink').focusout(function(e){
        $('#insertLink').hide();
        e.preventDefault();
    });

    $('#headingDropDown').click(function(e){
        toggleHeadingButtons();
        e.preventDefault();
    });
    $('#headingButtons a').click(function(e){
        toggleHeadingButtons();
        e.preventDefault();
    });

    function toggleHeadingButtons() {
        if( $('#headingButtons').is(':hidden') ) {
            $('#headingButtons').show();
            $('#toolbar').height(48);
        } else {
            $('#headingButtons').hide();
            $('#toolbar').height(24);
        }
    }
};

WysiwygEditor.prototype.getEditorHtmlForDisplay = function () {
    //get note code from editor
    var html = $('#editor').html();
    //remove base64 code for display
    html = html.replace(/src="data:image[^"]*"/g, 'src="..."');
    return this.htmlEncode.call(this, html);
};

WysiwygEditor.prototype.htmlEncode = function (value) {
    if (value) {
        return jQuery('<div />').text(value).html();
    } else {
        return '';
    }
};

WysiwygEditor.prototype.htmlDecode = function (value) {
    if (value) {
        return $('<div />').html(value).text();
    } else {
        return '';
    }
};

WysiwygEditor.prototype.editorNeverEmpty = function () {
    var content = this.editor.innerHTML.trim();
    var previousState = this.unsavedContent;
    if(content === '' || content === '<br>') {
        //make sure it is completely empty
        this.editor.innerHTML = '';

        //now make the paragraph on the cursor position
        document.execCommand('formatBlock', false, 'p');
        if(previousState === false) {
            this.setUnsavedStatus.call(this, false);
        }
    }
};