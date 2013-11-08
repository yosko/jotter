var unsavedContent  = false;
var currentlySaving = false;

$(function(){
    var editor = $('#editor');

    //doesn't seem to work in firefox, which still use <br>
    document.execCommand('defaultParagraphSeparator', false, 'p');

    //init editor
    editor.wysiwyg().focus();

    //if note is empty on load, add a <p>
    editorNeverEmpty();

    //set status to unsaved on input
    editor.bind('input', function(e){
        setUnsavedStatus(true);
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

    $('#source-button').click(function(e){
        if($('#html').length == 0) {
            $('#editor').after( '<pre id="html" style="">'+htmlEncode( $('#editor').html() )+'</pre>' );
        } else {
            $('#html').remove();
        }
        e.preventDefault();
    });

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
        //for now: ask the user 
        //TODO: auto save
        if(unsavedContent)
            return "There is unsaved content. Do you still wish to leave this page?";
    }
    window.onbeforeunload = checkIsUnsaved;
});

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
