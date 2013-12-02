$(function(){
    var editor = $('#editor');

    //doesn't seem to work in firefox, which still use <br>
    document.execCommand('defaultParagraphSeparator', false, 'p');

    //init editor
    editor.wysiwyg({
        activeToolbarClass: 'selected'
    }).focus();

    //if note is empty on load, add a <p>
    editorNeverEmpty();

    //set status to unsaved on input
    //and update code if displayed
    editor.bind('input', function(e){
        setUnsavedStatus(true);
        if($('#html').length != 0) {
            $('#html').html( getEditorHtmlForDisplay() );
        }
    });

    //insert an em dash
    $('#mdash-button').click(function(e){
        document.execCommand('insertHTML', false, '&nbsp;&mdash;&nbsp;');
        e.preventDefault();
    });

    $('#picture-button').click(function(e) {
        $('#hidden-picture-button').click();
        e.preventDefault();
    });

    //display html source
    $('#source-button').click(function(e){
        if($('#html').length == 0) {
            $('#editor').after( '<pre id="html" style="">'+getEditorHtmlForDisplay()+'</pre>' );
        } else {
            $('#html').remove();
        }
        e.preventDefault();
    });

    //add 'http://' to link input
    $('#insertLink input').focus(function(e){
        var input = $(this);
        if(input.val().trim() == '') {
            input.val(input.attr('placeholder'));
        }
    });

    //show/hide subtoolbars
    $('#insertLink').hide();
    $('#linkDropdown').click(function(e){
        $('#insertLink').toggle();
        e.preventDefault();
    });
    $('#insertLink').focusout(function(e){
        $('#insertLink').hide();
        e.preventDefault();
    });

    toggleHeadingButtons();
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
});

function getEditorHtmlForDisplay() {
    //get note code from editor
    var html = $('#editor').html();
    //remove base64 code for display
    html = html.replace(/src="data:image[^"]*"/g, 'src="..."');
    return htmlEncode( html );
}

function htmlEncode(value){
    if (value) {
        return jQuery('<div />').text(value).html();
    } else {
        return '';
    }
}

function htmlDecode(value) {
    if (value) {
        return $('<div />').html(value).text();
    } else {
        return '';
    }
}

function editorNeverEmpty() {
    var content = $('#editor').html().trim();
    var previousState = unsavedContent;
    if(content == '' || content == '<br>') {
        //make sure it is completely empty
        $('#editor').empty();

        //now make the paragraph on the cursor position
        document.execCommand('formatBlock', false, 'p');
        if(previousState == false) {
            setUnsavedStatus(false);
        }
    }
}

function moveCursorToTop() {
    var pressHome = jQuery.Event("keypress");
    pressHome.ctrlKey = false;
    pressHome.which = 36;   //"home" key

    var pressEnd = jQuery.Event("keypress");
    pressEnd.ctrlKey = false;
    pressEnd.which = 35;   //"home" key

    $('#editor').trigger(pressEnd).trigger(pressHome);
}
