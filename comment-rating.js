	/*
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	*/

function crowdioRatingcreateXMLHttpRequest() {
    var xmlhttp = null;
    try {
        // Moz supports XMLHttpRequest. IE uses ActiveX.
        // browser detction is bad. object detection works for any browser
        xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {
        // browser doesn’t support ajax. handle however you want
        // document.getElementById("errormsg").innerHTML = "Your browser doesnt support XMLHttpRequest.";
        // This won't help ordinary users.  Turned off
        // alert("Your browser does not support the XMLHttpRequest Object!");
    }
    return xmlhttp;
}

var crowdioRatingXhr = crowdioRatingcreateXMLHttpRequest();

function crowdioRatingKarma(id, action, path, imgIndex){
    crowdioRatingXhr.open('get', 'http\://'+ path +'process-karma.php?id='+ id +'&action='+ action +'&path='+ path +'&imgIndex='+imgIndex);
    crowdioRatingXhr.onreadystatechange = crowdioRatingHandleResponse;
    crowdioRatingXhr.send(null);
}

function crowdioRatingHandleResponse() {
    if(crowdioRatingXhr.readyState == 4){
        var response = crowdioRatingXhr.responseText.split('|');
        
        if(response[0] == 'done'){
            if(response[1]){
                //Changes the thumbs to dull gray and disable the action
                if (response[4] == 'down') {
                  if ( document.getElementById("down-"+response[1]) != null ) { 
                      document.getElementById("down-"+response[1]).src = "http://"+response[3]+'images/'+response[6]+'checkmark.png';
                  }
                }
                else {
                  if ( document.getElementById("down-"+response[1]) != null ) {
                      document.getElementById("down-"+response[1]).src = "http://"+response[3]+'images/'+response[6]+'gray_down.png';
                  }
                }
                if ( document.getElementById("down-"+response[1]) != null ) {
                   document.getElementById("down-"+response[1]).onclick    = '';
                }
                if (response[4] == 'up') {
                   if ( document.getElementById("up-"+response[1]) != null ) {
                      document.getElementById("up-"+response[1]).src   = "http://"+response[3]+'images/'+response[6]+'checkmark.png';
                   }
                }
                else {
                   if ( document.getElementById("up-"+response[1]) != null ) {
                      document.getElementById("up-"+response[1]).src   = "http://"+response[3]+'images/'+response[6]+'gray_up.png';
                   }
                }
                if ( document.getElementById("up-"+response[1]) != null ) {
                   document.getElementById("up-"+response[1]).onclick      = '';
                }
                //Update the karma number display
                if(!response[2]){
                	alert("Response has no value");
                }
                var karmanumber = response[2];
                //The below line is commented out because there is no karma number atm.
                if (document.getElementById("karma-"+response[1]+"-"+response[4]) != null) {
                   document.getElementById("karma-"+response[1]+"-"+response[4]).innerHTML = karmanumber;
                }
                // deal with the single value total
                if (document.getElementById("karma-"+response[1]+"-total") != null) {
                   document.getElementById("karma-"+response[1]+"-total").innerHTML = response[5];
                }
            } else {
                alert("WTF ?");
            }
        }
        else if(response[0] == 'error')
        {
            var error = 'Error: '+response[1];
            alert(error);
        } else {
           /*  This causes unnecessary error messages when the icon
            *  is double clicked.
        	   alert("Reponse: "+response[0]);
            alert("Karma not changed, please try again later.");
            */
        }
    }
}

var crToggleComment = 0;

function crSwitchDisplay(id) {
   if (crToggleComment % 2 == 0) { crShowdiv(id); }
   else { crHidediv(id); }
   crToggleComment++;
}

// hide <div id='a2' style="display:none;"> tagged div ID blocks
function crHidediv(id) {
	//safe function to hide an element with a specified id
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'none';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'none';
		}
		else { // IE 4
			document.all.id.style.display = 'none';
		}
	}
}

// show <div id='a2' style="display:none;"> tagged div ID blocks
// <a href="javascript:crShowdiv('a2');">show a2</a>

function crShowdiv(id) {
	//safe function to show an element with a specified id
		  
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'block';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'block';
		}
		else { // IE 4
			document.all.id.style.display = 'block';
		}
	}
}

