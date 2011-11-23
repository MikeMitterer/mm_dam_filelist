function getviewport(){
	var viwport;
	var viewportwidth;
	var viewportheight;
	 
	 // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
	 if (typeof window.innerWidth != 'undefined'){
	    viewportwidth = window.innerWidth,
	    viewportheight = window.innerHeight
	 	}
	 
	// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
	 else if (typeof document.documentElement != 'undefined'
	     && typeof document.documentElement.clientWidth !=
	     'undefined' && document.documentElement.clientWidth != 0){
	    viewportwidth = document.documentElement.clientWidth,
	    viewportheight = document.documentElement.clientHeight
	 	}
	 
	 // older versions of IE
	 else {
		viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	    viewportheight = document.getElementsByTagName('body')[0].clientHeight
	 	}

	viewport = {width: viewportwidth, height: viewportheight}
	return viewport;
	}
	

function showPage(baseurl,caption,width,height) {
	var url;
	var viewport;
	if(caption == undefined) 
		caption = ''
	viewport = getviewport();
	
	if(width == undefined) width = viewport.width - 60;
	if(height == undefined) height = viewport.height - 60;
	
	GB_show(caption, baseurl,width,height);	
	return false;
	}
	

