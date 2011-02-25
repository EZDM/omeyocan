/* jseyes.js


The classic Xeyes in JavaScript
(c) PROPIX Ltd,  Written by Pintér Gábor
Székesfehérvár, Kriványi u. 15.
H-8000, HUNGARY
Tel: +36 30 3489752
Fax: +36 22 304326
Email: propix@freemail.hu
Web: http://www.propix.hu

Revisions:
  V1.0  10/14/2001  Original release
		V1.1  02/20/2008: Updated by JavaScriptKit.com to work in the latest browsers (IE7, FF etc)

Usage:
  1. Include this file from the head of your page
  2. Define parameters or accept the defaults
  3. Insert the image

This script requires Internet Explorer 5+ or Nescape Navigator 6+! In other browsers it does nothing.



1. Include jseyes.js from the head of your page
Insert the following line into the head of your page:
  <script src="jseyes.js"></script>


2. Define parameters
You can accept the defaults or assign new values to these variables:

jseyesimg="jseyes.gif"
  The main image. Please do not edit.

jseyeimg="jseyeblue.gif"
  The image of the eye. Must be a 21x29 solid ellipse with transparent background.


4. Insert the image
Call jseyes() where you want to see the image:
  <script>
    jseyes();
  </script>

Or call jseyes(x, y) to show the image at absolute position:
  <script>
    jseyes(100,100);
  </script>



Example: http://www.propix.hu/www/jseyes/jseyes.html

*/




// Defaults
var jseyesimg="jseyes.gif";
var jseyeimg="jseyeblue.gif";
var jseyeslink="http://www.javascriptkit.com";


// Internal
var jseyeso=null, jseye1=null, jseye2=null, jseye3=null, jseye4=null;
var standardbody=(document.compatMode=="CSS1Compat")? document.documentElement : document.body //create reference to common "body" across doctypes

// General utils
var x1 = 36;
var y1 = 44;
var x2 = 68;
var y2 = 44;
var x3 = 167;
var y3 = 47;
var x4 = 199;
var y4 = 47;


// Find object by name or id
function jseyesobj(id) {
  var i, x;
  x= document[id];
  if (!x && document.getElementById) x= document.getElementById(id);
  for (i=0; !x && i<document.forms.length; i++) x= document.forms[i][id];
  return(x);
}


// Move eyes
function jseyesmove(x, y) {
  var ex, ey, dx, dy;
	var a = 1;
	var b= 3;
  if (jseyeso && jseye1 && jseye2 && jseye3 && jseye4 && jseyeso.style) {
    ex=jseyeso.offsetLeft+40; ey=jseyeso.offsetTop+45;
    dx=x-ex; dy=y-ey;
    r=(dx*dx/a+dy*dy/a<1) ? 1 : Math.sqrt(a*b/(dx*dx*a+dy*dy*b));
    jseye1.style.left= r*dx+x1+'px'; jseye1.style.top= r*dy+y1+'px';
    ex+=56; dx-=56;
    r=(dx*dx/a+dy*dy/b<1) ? 1 : Math.sqrt(a*b/(dx*dx*a+dy*dy*b));
    jseye2.style.left= r*dx+x2+'px'; jseye2.style.top= r*dy+y2+'px';
		ex+=56; dx-=56;
    r=(dx*dx/a+dy*dy/b<1) ? 1 : Math.sqrt(a*b/(dx*dx*a+dy*dy*b));
    jseye3.style.left= r*dx+x3+'px'; jseye3.style.top= r*dy+y3+'px';
		ex+=56; dx-=56;
    r=(dx*dx/a+dy*dy/b<1) ? 1 : Math.sqrt(a*b/(dx*dx*a+dy*dy*b));
    jseye4.style.left= r*dx+x4+'px'; jseye4.style.top= r*dy+y4+'px';
  }
}



// Main
function jseyes() {
  var img;
  var x, y, a=false;

  if (arguments.length==2) {
    x= arguments[0];
    y= arguments[1];
    a= true;
  }

    img= "<div id='jseyeslayer' style='position:"+
           (a ? "absolute; left:"+x+"; top:"+y : "relative")+
           "; z-index:5; width:150; height:150 overflow:hidden'>"+
	     "<div id='jseye1' style='position:absolute; left:"+x1+"; top:"+y1+"; z-index:6;'>"+
	       "<img src='"+jseyeimg+"'>"+
	     "</div>"+
	     "<div id='jseye2' style='position:absolute; left:"+x2+"; top:"+y2+"; z-index:6;'>"+
	       "<img src='"+jseyeimg+"'>"+
	     "</div>"+
	      "<div id='jseye3' style='position:absolute; left:"+x3+"; top:"+y3+"; z-index:6;'>"+
	       "<img src='"+jseyeimg+"'>"+
	     "</div>"+
	      "<div id='jseye4' style='position:absolute; left:"+x4+"; top:"+y4+"; z-index:6;'>"+
	       "<img src='"+jseyeimg+"'>"+
	     "</div>"+
	 "</div>";
    document.write(img);
    jseyeso=jseyesobj('jseyeslayer');
    jseye1=jseyesobj('jseye1');
    jseye2=jseyesobj('jseye2');
		jseye3=jseyesobj('jseye3');
		jseye4=jseyesobj('jseye4');

    document.onmousemove=jseyesmousemove;
}


// Mouse move events

function jseyesmousemove(e) {
		var mousex=(e)? e.pageX : event.clientX+standardbody.scrollLeft
		var mousey=(e)? e.pageY : event.clientY+standardbody.scrollTop
  jseyesmove(mousex, mousey);
  //return(false);
}
