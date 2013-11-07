$(function(){
    var unsavedContent  = false;

    //doesn't seem to work in firefox, which still use <br>
    document.execCommand('defaultParagraphSeparator', false, 'p');

    //init editor
    $('#editor').wysiwyg().bind('input', function(e){
        var editor = $('#save-button');
        var image = $('#save-button img');
        var src = image.attr("src").replace("disk-black.png", "disk.png");

        unsavedContent = true;

        editor.removeClass('disabled');
        editor.attr('title', 'Save changes');
        image.attr('src', src);
    }).focus();

    $('#save-button').click(function(e){
        var editor = $('#save-button');
        var image = $('#save-button img');
        var src = image.attr("src").replace("disk.png", "disk-black.png");

        unsavedContent = false;

        editor.addClass('disabled');
        editor.attr('title', 'Nothing to save');
        image.attr('src', src);
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

    function checkIsUnsaved() {
        //for now: ask the user 
        //TODO: auto save
        if(unsavedContent)
            return "There is unsaved content. Do you still wish to leave this page?";
    }
    window.onbeforeunload = checkIsUnsaved;
});

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
