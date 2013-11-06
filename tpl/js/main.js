$(function(){
    //doesn't seem to work in firefox, which still use <br>
    document.execCommand('defaultParagraphSeparator', false, 'p');

    //init editor
    $('#editor').wysiwyg().bind('input', function(e){
        //TODO: turn save button to unsaved status
        $(this).removeClass('disabled');
        $(this).attr('title', 'Save changes');
        var src = $('#save-button img').attr("src").replace("disk-black.png", "disk.png");
        $('#save-button img').attr('src', src);
    }).focus();

    $('#save-button').click(function(e){
        //TODO: turn save button to saved status
        $(this).addClass('disabled');
        $(this).attr('title', 'Nothing to save');
        var src = $('#save-button img').attr("src").replace("disk.png", "disk-black.png");
        $('#save-button img').attr('src', src);
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