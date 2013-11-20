window.onload=function() {
    // show/hide subtree when clicking arrows
    var arrows = document.getElementsByClassName('arrow');
    for(var i=0; i<arrows.length; i++) {
        arrows[i].onclick = function() {
            var subtree = this.parentNode.querySelector("ul");
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
}