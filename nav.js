
function MM_preloadImages() { //v2.0
  if (document.images) {
    var imgFiles = MM_preloadImages.arguments;
    if (document.preloadArray==null) document.preloadArray = new Array();
    var i = document.preloadArray.length;
    with (document) for (var j=0; j<imgFiles.length; j++) if (imgFiles[j].charAt(0)!="#"){
      preloadArray[i] = new Image;
      preloadArray[i++].src = imgFiles[j];
  } }
}

function MM_swapImgRestore() { //v2.0
  if (document.MM_swapImgData != null)
    for (var i=0; i<(document.MM_swapImgData.length-1); i+=2)
      document.MM_swapImgData[i].src = document.MM_swapImgData[i+1];
}

function MM_swapImage() { //v2.0
  var i,j=0,objStr,obj,swapArray=new Array,oldArray=document.MM_swapImgData;
  for (i=0; i < (MM_swapImage.arguments.length-2); i+=3) {
    objStr = MM_swapImage.arguments[(navigator.appName == 'Netscape')?i:i+1];
    if ((objStr.indexOf('document.layers[')==0 && document.layers==null) ||
        (objStr.indexOf('document.all[')   ==0 && document.all   ==null))
      objStr = 'document'+objStr.substring(objStr.lastIndexOf('.'),objStr.length);
    obj = eval(objStr);
    if (obj != null) {
      swapArray[j++] = obj;
      swapArray[j++] = (oldArray==null || oldArray[j-1]!=obj)?obj.src:oldArray[j];
      obj.src = MM_swapImage.arguments[i+2];
  } }
  document.MM_swapImgData = swapArray; //used for restore
}


    IE4 = (document.all) ? 1 : 0;
    NS4 = (document.layers) ? 1 : 0;
    ver4 = (IE4 || NS4) ? 1 : 0;

    if (ver4) {
        secondIm = "<IMG SRC='nav_over.gif' USEMAP='#mpMenu' WIDTH=100 HEIGHT=300 BORDER=0>";
        arPopups = new Array()
    }
    else { secondIm = "" }

    function setBeginEnd(which,from,to) {
        arPopups[which] = new Array();
        arPopups[which][0] = from;
        arPopups[which][1] = to;
    }

    if (ver4) {
        setBeginEnd(1,27,55);
        setBeginEnd(2,56,83);
        setBeginEnd(3,84,111);
        setBeginEnd(4,112,139);
        setBeginEnd(5,139,167);
        setBeginEnd(6,169,195);
        setBeginEnd(7,196,223);
        setBeginEnd(8,224,251);
    }

    clLeft = 0;
    clRight = 100;

    function mapOver(which,on) {
        if (!ver4) { return }
        if (IE4) { whichEl = document.all.elMenuOver.style }
            else { whichEl = document.elMenu.document.elMenuOver };

        if (!on) { whichEl.visibility = "hidden"; return }

        clTop = arPopups[which][0];
        clBot = arPopups[which][1];

	if (NS4) {
            whichEl.clip.top = clTop;
            whichEl.clip.bottom = clBot;
	}
	else {
            whichEl.clip = "rect(" + clTop + " " + clRight + " " + clBot + " " + clLeft + ")";
	}

        whichEl.visibility = "visible" 
    }
