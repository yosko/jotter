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

/**
 * When dragging an item, remember its path
 */
function drag(e) {
    item = e.target.parentNode;
    var path = item.getAttribute('data-path');
    e.dataTransfer.setData("Text",path);
}

/**
 * Avoid page reload on dropping an item onto another
 */
function allowDrop(e) {
    e.preventDefault();
}

/**
 * Perform the update when dropping an item onto another
 */
function drop(e) {
    e.preventDefault();
    var sourcePath = e.dataTransfer.getData("Text");
    var source = document.querySelector('[data-path="'+sourcePath+'"]');

    var dest = e.target.parentNode;
    var destPath = dest.getAttribute('data-path');

    //if item was dropped on a directory (not a note) which is not one of its descendant
    if(dest.className == 'directory' && !isAncestor(source, dest)) {
        //sync with server
        var request = new XMLHttpRequest();
        var notebook = document.getElementById('notebookTitle').getAttribute('data-name');
        request.open('GET','?action=ajax&option=moveItem&nb='+notebook+'&source='+sourcePath+'&destination='+destPath,false);
        request.send();
        response = JSON.parse(request.responseText);

        // is syncing was successful, display the item at its new position
        if(response == true) {
            //find its subtree list
            var destList = dest.querySelector('li .subtree');

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

            //TODO: change source item its children paths
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