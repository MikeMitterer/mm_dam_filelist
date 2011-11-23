/*
Scritp direkt von http://www.just2b.com/fileadmin/ringergeorg/div/listmenu1.js
Beschreibung auf Website (http://www.just2b.com/mein-typo3/css-ts-menues/vertikales-dropdown-menue.html)
stimmt nicht.

Basisinfo:
	http://www.just2b.com/mein-typo3/css-ts-menues/vertikales-dropdown-menue.html
*/
function IEHoverPseudo() {

	var navItems = document.getElementById("nav").getElementsByTagName("li");
	
	for (var i=0; i<navItems.length; i++) {
		if(navItems[i].className == "menuparent") {
			navItems[i].onmouseover=function() { this.className += " over"; }
			navItems[i].onmouseout=function() { this.className = "menuparent"; }
		}
	}

}
window.onload = IEHoverPseudo;