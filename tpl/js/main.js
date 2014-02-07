window.addEventListener('load', function (){
    var arrows, i;

    // show/hide subtree when clicking arrows
    arrows = document.getElementsByClassName('arrow');
    for(i=0; i<arrows.length; i++) {
        // arrows[i].onclick = toggleSubtree;
        arrows[i].addEventListener('click', toggleSubtree, false);
    }

    // document.onclick = hideAllDropdowns;
    document.addEventListener('click', hideAllDropdowns, false);

    // show dropdown menu when clicking dropdown arrows
    arrows = document.getElementsByClassName('dropdown-arrow');
    for(i=0; i<arrows.length; i++) {
        // arrows[i].onclick = showDropdown;
        arrows[i].addEventListener('click', showDropdown, false);
    }

    //load a notebook selected from the dropdown list (or create a new one)
    var notebookSelect = document.getElementById('notebookSelect');
    // notebookSelect.onchange = changeNotebook;
    notebookSelect.addEventListener('change', changeNotebook, false);

    var items = document.getElementsByClassName('item');
    for(i=0; i<items.length; i++) {
        //simulate hover on items where drop is possible
        if(items[i].parentNode.className.lastIndexOf('directory') !== -1 || items[i].hasAttribute('id') && items[i] != 'notebookTitle') {
            // items[i].ondragover = hover;
            // items[i].ondragleave = leave;
            items[i].addEventListener('dragover', hover, false);
            items[i].addEventListener('dragleave', leave, false);
        }

        if(!items[i].hasAttribute('id') || items[i] != 'notebookTitle') {
            // items[i].ondragstart = startDragging;
            // items[i].ondragend = endDragging;
            // items[i].parentNode.ondrop = drop;
            items[i].addEventListener('dragstart', startDragging, false);
            items[i].addEventListener('dragend', endDragging, false);
            items[i].parentNode.addEventListener('drop', drop, false);
        } else {
            // items[i].ondrop = drop;
            items[i].addEventListener('drop', drop, false);
        }
    }
});

/**
 * EVENT FUNCTIONS
 */

function startDragging(ev) {
    document.getElementById('panel').className = 'dragMode';
    item = ev.target.parentNode;
    var path = item.getAttribute('data-path');
    ev.dataTransfer.setData("Text",path);
}

function endDragging(ev) {
    document.getElementById('panel').className = '';
}

function changeNotebook(ev) {
    var home = location.protocol + '//' + location.host + location.pathname;

    //redirect to notebook creation
    if(notebookSelect.value == '!new!') {
        window.location = home+'?action=add';

    //redirect to selected notebook
    } else if(notebookSelect.value.substring(0,1) != '!') {
        window.location = home+'?nb='+notebookSelect.value;
    }
}

function toggleSubtree(ev) {
    var subtree = ev.target.parentNode.querySelector(".subtree");
    var image = ev.target.querySelector('img');
    var srcPath = image.src.substring(0,image.src.lastIndexOf('/') +1 );

    if(ev.target.className == 'arrow open') {
        image.src = srcPath+'arbo-parent-closed.png';
        ev.target.className = 'arrow closed';
        subtree.style.display = 'none';
    } else {
        image.src = srcPath+'arbo-parent-open.png';
        ev.target.className = 'arrow open';
        subtree.style.display = 'block';
    }
    return false;
}

function showDropdown(ev) {
    var dropdown = ev.target.parentNode.querySelector(".dropdown");

    //hide every other dropdown
    hideAllDropdowns();

    //show this one
    dropdown.className = 'dropdown open';
    dropdown.style.display = 'block';

    //avoid click propagation (which would call hideAllDropdowns() again)
    ev.stopPropagation();
    return false;
}

function hideAllDropdowns() {
    var dropdowns = document.getElementsByClassName('dropdown');
    for(var i=0; i<dropdowns.length; i++) {
        dropdowns[i].className = 'dropdown closed';
        dropdowns[i].style.display = 'none';
    }
}

function hover(ev) {
    ev.preventDefault();
    ev.target.className = ev.target.className + ' hover';
}

function leave(ev) {
    ev.target.className = ev.target.className.split(' ').filter(function(v) {
        return v!='hover';
    }).join(' ');
}

/**
 * Perform the update when dropping an item onto another
 */
function drop(ev) {
    ev.preventDefault();
    var i;
    var sourcePath = ev.dataTransfer.getData("Text");
    var sourceDirPath = sourcePath.substring(0, sourcePath.lastIndexOf('/'));
    var source = document.querySelector('[data-path="'+sourcePath+'"]');

    var dest = ev.target.parentNode;
    var destPath = dest.getAttribute('data-path');

    leave(ev);

    //if item was dropped on a directory (not a note) which is not one of its descendant
    //and not its own current directory
    if((dest.className.lastIndexOf('directory') !== -1 && !isAncestor(source, dest) || ev.target.id == 'notebookTitle') && sourceDirPath != destPath) {
        //sync with server
        var request = new XMLHttpRequest();
        var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
        request.open('GET','?action=ajax&option=moveItem&nb='+notebook+'&source='+sourcePath+'&destination='+destPath,false);
        request.send();
        response = JSON.parse(request.responseText);

        // is syncing was successful, display the item at its new position
        if(response === true) {
            //find its subtree list
            var destList;
            if(ev.target.id == 'notebookTitle') {
                destList = document.getElementById('root');
            } else {
                destList = dest.querySelector('li .subtree');
             }

            //remove the source item
            source.parentNode.removeChild(source);

            //insert source item in the right, sorted place
            var sourceItem = sourcePath.substring(sourcePath.lastIndexOf('/')+1);
            var inserted = false;
            for(i=0; i<destList.childNodes.length; i++) {
                var x = destList.childNodes[i];

                if(x.nodeType == Node.ELEMENT_NODE) {
                    var currentItem = x.getAttribute('data-path').substring(x.getAttribute('data-path').lastIndexOf('/')+1);
                    if(currentItem.toLowerCase() > sourceItem.toLowerCase()) {
                        destList.insertBefore(source, x);
                        inserted = true;
                    }
                }
            }

            //add item in the end if not yet inserted
            if(!inserted) {
                destList.appendChild(source);
            }

            // update source item and its children paths
            var items = source.getElementsByClassName('item');
            for(i=0; i<items.length; i++) {
                source = sourceDirPath+(sourceDirPath!==''?'/':'');
                dest = destPath+(destPath!==''?'/':'');
                var pathBefore = items[i].parentNode.getAttribute('data-path');
                var pathAfter = pathBefore.replace(source, dest);

                items[i].parentNode.setAttribute('data-path', pathAfter);

                links = items[i].parentNode.getElementsByTagName('a');
                for(var j=0; j<links.length; j++) {
                    links[j].href = links[j].href.replace('item='+pathBefore, 'item='+pathAfter);
                    links[j].title = links[j].title.replace('"'+pathBefore+'"', '"'+pathAfter+'"');
                }
            }
        }
    }
}

function isDescendant(ancestor,descendant){
    return ancestor.compareDocumentPosition(descendant) & Node.DOCUMENT_POSITION_CONTAINS;
}

function isAncestor(descendant,ancestor){
    return descendant.compareDocumentPosition(ancestor) & Node.DOCUMENT_POSITION_CONTAINED_BY;
}