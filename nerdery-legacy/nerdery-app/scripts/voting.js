var ns=(document.layers);
var ie=(document.all);
var w3=(document.getElementById && !ie);
var calunit = ns ? "" : "px"

function truebody(){
	return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}


