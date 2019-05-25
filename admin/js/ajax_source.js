//Ajax functions

var httpHandle = new Array(); //Http object

//Structure to link object and the function that formats the http response back in the page
function struct(){
	var handle = null;
	var procedure = null;
	var errorProc = null;
}

//Generic function that's called to process http response from server
//It receives a param number that identifies the call along the array of http handles
function response( x )
{
	if ( httpHandle[x].handle.readyState == 4 ) {
    	if( httpHandle[x].handle.status == 200 ) {
			//Passing the response to the function that works on it in the web page
			httpHandle[x].procedure( httpHandle[x].handle.responseText ); 
			httpHandle[x].handle = null; //Making a handle free for next assignment.
    	}
		else
		{
			if(httpHandle[x].errorProc == null)
				alert('error ' + httpHandle[x].handle.status);
			else
				httpHandle[x].errorProc(httpHandle[x].handle.status);
		}
  	} else {
  		
  	}
}

//Class to construct the http object
function createAJAX()
{
	this.n = 0; //Array counter of http handles
}

//This function constructs the http object
//It receives the handle of the function that formats the content in the web page
createAJAX.prototype.getXMLHTTPRequest = function( fun, errorProc )
{ 
	var req = null;
	try 
	{
		req = new XMLHttpRequest();
	} 
	catch(err1) 
	{	
  		try 
  		{
  			req = new ActiveXObject("Msxml2.XMLHTTP");
  		} 
  		catch (err2) 
  		{
    		try 
			{
    			req = new ActiveXObject("Microsoft.XMLHTTP");
    		} 
			catch (err3) 
			{
      			req = false;
    		}
  		}
	}
	//Auxiliary structure is created.
	//This structure receives a handle of the http object 
	//and the function that works on content in the browser
	this.n = this.itemAvailable();
	httpHandle[this.n] = new struct();
	httpHandle[this.n].handle = req;
	httpHandle[this.n].procedure = fun;	
	httpHandle[this.n].errorProc = errorProc;	
}

//Function that requests data to the server.
//It receives the location of the file in the server, some params, 
//and a handle of the function in the page that formats the response
createAJAX.prototype.async_call = function(url, params, doResponse, errorProc) 
{
	this.getXMLHTTPRequest(doResponse, errorProc);
	
	var myurl = url + '?';
  	if(params != '')
  		myurl += params + '&';
  	myRand = parseInt(Math.random()*999999999999999);
  	var modurl = myurl+ 'rand='+myRand;
	httpHandle[this.n].handle.open("GET", modurl, true);
 	httpHandle[this.n].handle.onreadystatechange = new Function ( "x, fun", "response(" + this.n  + ")" );
  	httpHandle[this.n].handle.send(null);
  	//++this.n;
}

//Esta funcion busca un elemento null en la lista de handles HttpRequest
//Si no existe ninguno envia la longitud del arreglo para un nuevo item (ultimo de la lista)
createAJAX.prototype.itemAvailable = function(){
	for(var i = 0; i < httpHandle.length; i++ ){
		if( httpHandle[i].handle == null ){
			return i;
		}
	}
	return httpHandle.length;
}
