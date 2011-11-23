/**
*	Test
*/

function myJSFunction(firstArg, numberArg, myArrayArg) {
    var newString = firstArg + " and " + (+numberArg + 100) + "\n";
    newString += myArrayArg["myKey"] + " | " + myArrayArg.key2;
    alert(newString);
    
    xajax.$('myDiv').innerHTML = newString;
}

function changePreviewImage(tagID,imgTag) {
	//alert(imgTag);
	xajax.$(tagID).innerHTML = imgTag;
}