<?php

/*##########################################################################################################################################

ALLOWS US TO SEPARATE THE LOGIC FROM THE LAYOUT

Updated December 2007

SAMPLE USAGE:

index_page.html contents:
---------------------------------
<html>
<head>
<title></title>
</head>
<body>

<p>Some text is [sumpm].</p>

</body>
</html>
---------------------------------

index.php contents:
---------------------------------
<?php

require_once("template.php");

$vars['sumpm']="something";

$HTML = replace($vars,rf("index_page.html"));
//
?>
---------------------------------

Then, you browse to index.php.  The
terms enclosed in square brackets are
taken from the $vars variable and
placed into the HTML file where they
appear with the same name.  The HTML
file is then sent as the PHP output.

The output of the page will be:

"Some text is something."

How do we get this system into objects?

##########################################################################################################################################*/

function rf($file){
	if(is_file($file)){
		return implode("",file($file));
	}
}

function replace($array,$str){
	if(is_array($array)){
		foreach($array as $n=>$v){
			$search[]="[$n]";
			$replace[]="$v";
		}
		if(!empty($search)&&!empty($replace)){
			return str_replace($search,$replace,$str);
		}else{
			return $str;
		}
	}else{
		return $str;
	}
}
?>