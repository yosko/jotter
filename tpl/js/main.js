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
        var button = $('#save-button');
        var image = $('#save-button img');
        var src = image.attr("src").replace("disk.png", "ajax-loader.gif");
        button.attr('title', 'Saving...');
        image.attr('src', src);

        getParam=getUrlVars();
        var data=new Object();
        data['text'] = $('#editor').html();

        $.ajax({
            type: 'POST',
            url: '?action=save&nb='+getParam['nb']+'&item='+getParam['item'],
            data: data,
            success: function(response){

                alert(response);

                src = image.attr("src").replace("ajax-loader.gif", "disk-black.png");
                image.attr('src', src);
                
                button.addClass('disabled');
                button.attr('title', 'Nothing to save');

                unsavedContent = false;
            }
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
