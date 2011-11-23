/*
Scritp direkt von http://www.just2b.com/fileadmin/ringergeorg/div/listmenu1.js
Beschreibung auf Website (http://www.just2b.com/mein-typo3/css-ts-menues/vertikales-dropdown-menue.html)
stimmt nicht.

Basisinfo:
	http://www.just2b.com/mein-typo3/css-ts-menues/vertikales-dropdown-menue.html
*/

function IEHoverPseudo() {
	doitForElement("nav-hor");
	doitForElement("nav-vert");
}

function doitForElement(elementID) {
	var element = getElement(elementID);
	if(element != null) {
		setItems(element.getElementsByTagName("li"));
		}
	}
	
function setItems(navItems) {
	for (var i=0; i<navItems.length; i++) {
		if(navItems[i].className == "menuparent") {
			navItems[i].onmouseover=function() { this.className += " over"; }
			navItems[i].onmouseout=function() { this.className = "menuparent"; }
		} else if(navItems[i].className == "menuparent-active") {
			navItems[i].onmouseover=function() { this.className += " over"; }
			navItems[i].onmouseout=function() { this.className = "menuparent-active"; }
		}
	}
}

function getElement(aID){ 
     return (document.getElementById) ? document.getElementById(aID) : document.all[aID];
   }
   
//window.onload = IEHoverPseudo;
