window.onload=function() {
    // show/hide subtree when clicking arrows
    var arrows = document.getElementsByClassName('arrow');
    for(var i=0; i<arrows.length; i++) {
        arrows[i].onclick = function() {
            var subtree = this.parentNode.querySelector(".subtree");
            var image = this.querySelector('img');
            var srcPath = image.src.substring(0,image.src.lastIndexOf('/') +1 );

            if(this.className == 'arrow open') {
                image.src = srcPath+'arbo-parent-closed.png';
                this.className = 'arrow closed';
                subtree.style.display = 'none';
            } else {
                image.src = srcPath+'arbo-parent-open.png';
                this.className = 'arrow open';
                subtree.style.display = 'block';
            }
            return false;
        }
    }

    document.onclick = hideDropdowns;

    // show dropdown menu when clicking dropdown arrows
    var arrows = document.getElementsByClassName('dropdown-arrow');
    for(var i=0; i<arrows.length; i++) {
        arrows[i].onclick = function(e) {
            var dropdown = this.parentNode.querySelector(".dropdown");

            //hide every other dropdown
            hideDropdowns();

            //show this one
            dropdown.className = 'dropdown open';
            dropdown.style.display = 'block';

            //avoid click propagation (which would call hideDropdowns() again)
            e.stopPropagation();
            return false;
        }
    }

    //load a notebook selected from the dropdown list (or create a new one)
    var notebookSelect = document.getElementById('notebookSelect');
    notebookSelect.onchange = function(e) {
        var home = location.protocol + '//' + location.host + location.pathname;

        //redirect to notebook creation
        if(notebookSelect.value == '!new!') {
            window.location = home+'?action=add';

        //redirect to selected notebook
        } else if(notebookSelect.value.substring(0,1) != '!') {
            window.location = home+'?nb='+notebookSelect.value;
        }
    }

    var items = document.getElementsByClassName('item');
    for(var i=0; i<items.length; i++) {
        //simulate hover on items where drop is possible
        if(items[i].parentNode.className.lastIndexOf('directory') !== -1
            || items[i].hasAttribute('id') && items[i] != 'notebookTitle'
        ) {
            items[i].ondragover = function(e) {
                e.preventDefault();
                hover(this);
            }
            items[i].ondragleave = function() {
                leave(this);
            }
        }

        if(!items[i].hasAttribute('id') || !items[i] != 'notebookTitle') {
            //draggable items (every item except the root)
            items[i].ondragstart = function(e) {
                document.getElementById('panel').className = 'dragMode';
                item = e.target.parentNode;
                var path = item.getAttribute('data-path');
                e.dataTransfer.setData("Text",path);
            }
            items[i].ondragend = function(e) {
                document.getElementById('panel').className = '';
            }
            items[i].parentNode.ondrop = function(e) {
                drop(e);
            }
        } else {
            items[i].ondrop = function(e) {
                drop(e);
            }
        }
    }
}

/**
 * Hide any open dropdown menu
 */
function hideDropdowns() {
    var dropdowns = document.getElementsByClassName('dropdown');
    for(var i=0; i<dropdowns.length; i++) {
        dropdowns[i].className = 'dropdown closed';
        dropdowns[i].style.display = 'none';
    }
}

function hover(node) {
    node.className = node.className + ' hover';
}

function leave(node) {
    node.className = node.className.split(' ').filter(function(v) {
        return v!='hover';
    }).join(' ');
}

/**
 * Perform the update when dropping an item onto another
 */
function drop(e) {
    e.preventDefault();
    var sourcePath = e.dataTransfer.getData("Text");
    var sourceDirPath = sourcePath.substring(0, sourcePath.lastIndexOf('/'));
    var source = document.querySelector('[data-path="'+sourcePath+'"]');

    var dest = e.target.parentNode;
    var destPath = dest.getAttribute('data-path');

    leave(e.target);

    //if item was dropped on a directory (not a note) which is not one of its descendant
    //and not its own current directory
    if((dest.className.lastIndexOf('directory') !== -1
        && !isAncestor(source, dest)
        || e.target.id == 'notebookTitle')
        && sourceDirPath != destPath
    ) {
        //sync with server
        var request = new XMLHttpRequest();
        var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
        request.open('GET','?action=ajax&option=moveItem&nb='+notebook+'&source='+sourcePath+'&destination='+destPath,false);
        request.send();
        response = JSON.parse(request.responseText);

        // is syncing was successful, display the item at its new position
        if(response == true) {
            //find its subtree list
            var destList;
            if(e.target.id == 'notebookTitle') {
                destList = document.getElementById('root');
            } else {
                destList = dest.querySelector('li .subtree');
             }

            //remove the source item
            source.parentNode.removeChild(source);

            //insert source item in the right, sorted place
            var sourceItem = sourcePath.substring(sourcePath.lastIndexOf('/')+1);
            var inserted = false;
            for(var i=0; i<destList.childNodes.length; i++) {
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
            for(var i=0; i<items.length; i++) {
                items[i].parentNode.setAttribute('data-path', items[i].parentNode.getAttribute('data-path').replace(sourceDirPath, destPath));
            }
        }
    }
}

function isDescendant(ancestor,descendant){
    return ancestor.compareDocumentPosition(descendant) & 
        Node.DOCUMENT_POSITION_CONTAINS;
}

function isAncestor(descendant,ancestor){
    return descendant.compareDocumentPosition(ancestor) & 
        Node.DOCUMENT_POSITION_CONTAINED_BY;
}