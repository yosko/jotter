var unsavedContent  = false;
var currentlySaving = false;

$(function(){
    var editor = $('#editor');

    //doesn't seem to work in firefox, which still use <br>
    document.execCommand('defaultParagraphSeparator', false, 'p');

    //init editor
    editor.wysiwyg({
        activeToolbarClass: 'selected'
    }).focus();

    //bind save to shortcut Ctrl+S
    $(window).keypress(function(event) {
        if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;
        if(unsavedContent)
            saveNote();
        event.preventDefault();
        return false;
    });

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

    //auto save every 30 seconds
    setInterval(function(){
        if(unsavedContent && !currentlySaving)
            saveNote();
    }, 30000);

    $('#save-button').click(function(e){
        if(unsavedContent)
            saveNote();
        e.preventDefault();
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

    function saveNote() {
        currentlySaving = true;
        var button = $('#save-button');
        var image = $('#save-button img');
        button.attr('title', 'Saving...');
        image.changeImageFile('ajax-loader.gif');

        getParam=getUrlVars();
        var data=new Object();
        data['text'] = $('#editor').html();

        $.ajax({
            type: 'POST',
            url: '?action=save&nb='+getParam['nb']+'&item='+getParam['item'],
            data: data,
            success: function(response){
                //the note was saved
                if(response !== false) {
                    setUnsavedStatus(false);

                //error, the note wasn't saved
                } else {
                    image.changeImageFile('disk--exclamation.png');
                    button.attr('title', 'Error: couldn\'t save this note.');
                }
                currentlySaving = false;
            },
            dataType: 'json'
        });
    }

    function checkIsUnsaved() {
        if(unsavedContent)
            return "There is unsaved content. Do you still wish to leave this page?";
    }
    window.onbeforeunload = checkIsUnsaved;
});

function getEditorHtmlForDisplay() {
    //get note code from editor
    var html = $('#editor').html();
    //remove base64 code for display
    html = html.replace(/src="data:image[^"]*"/g, 'src="..."');
    return htmlEncode( html );
}

// from http://snipplr.com/view/799/get-url-variables/
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
     
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
     
    return vars;
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

function setUnsavedStatus(status) {
    var button = $('#save-button');
    var image = $('#save-button img');

    unsavedContent = status;

    if(unsavedContent) {

        unsavedContent = true;

        button.removeClass('disabled');
        button.attr('title', 'Save changes');
        image.changeImageFile('disk.png');

        //when user delete everything inside the editor, make sure there is still a <p>
        editorNeverEmpty();
    } else {
        image.changeImageFile('disk-black.png');
        
        button.addClass('disabled');
        button.attr('title', 'Nothing to save');
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

$.fn.changeImageFile = function(newFileName) {
    var dirPath = this.attr('src').substring(0,this.attr('src').lastIndexOf('/') +1 );
    this.attr('src', dirPath+'/'+newFileName);
}
