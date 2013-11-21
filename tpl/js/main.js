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

    // show dropdown menu when clicking dropdown arrows
    var arrows = document.getElementsByClassName('dropdown-arrow');
    for(var i=0; i<arrows.length; i++) {
        arrows[i].onclick = function() {
            //TODO : make sure every other drodpown is closed before opening one?

            var dropdown = this.parentNode.querySelector(".dropdown");

            if(dropdown.className == 'dropdown closed') {
                dropdown.className = 'dropdown open';
                dropdown.style.display = 'block';
            } else {
                dropdown.className = 'dropdown closed';
                dropdown.style.display = 'none';
            }
            return false;
        }
    }
}