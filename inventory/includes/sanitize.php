<?php

/*##########################################################################################################################################

CONTAINS CODE THAT WILL SANITIZE DATA THAT SOMEONE ENTERS INTO A WEB FORM.

Updated December 2007

Copyright © 2009  Tribal Paradigm, LLC – All Rights Reserved
Scott Morris - smmorris@gmail.com

##########################################################################################################################################*/

// DETERMINE WHETHER A VARIABLE MAY CONTAIN A USABLE VALUE
function isblank( $var ){
    if ( empty( $var ) || !isset( $var ) ){
        return true;
    }else{
        return false;
    }
}


//TAKES OUT A SPECIFIED CHUNK OF A STRING
function rmvbetween($removefrom,$start,$end){
  $endloc=strpos($removefrom,$start);
  $startloc=0;
  $newstr="";
  while($endloc>0){
    $newstr.=substr($removefrom,$startloc,($endloc-$startloc));
    $startloc=strpos($removefrom,$end,$endloc)+1;
    $endloc=strpos($removefrom,$start,$startloc);
    return $newstr;
  }
  return $removefrom;
}

//REMOVES ANYTHING NOT AN ALPHA CHARACTER
function alpha_only($string) {
    // REMOVE ANYTHING NOT a-z OR A-Z
    $string=preg_replace("/[^a-zA-Z ]+/"," ",$string);
    return $string;
}

//SANITIZES A USERNAME
function cleanUsername($uname) {
    $uname=strip_tags($uname);
    // REMOVE ANYTHING NOT a-z, A-Z, OR 0-9
    $uname=ereg_replace("[^a-z^0-9^A-Z]","",$uname);
    return $uname;
}

//SANITIZES A PERSON'S NAME
function cleanName($name) {
    $name=trim($name);
    $name=strip_tags($name);
    // Remove anything not a to z, A to Z, ', or a space
    $name=preg_replace("/[^a-zA-Z'- ]+/","",$name);
    $name=ucwords(strtolower($name));
    return $name;
}

//SANITIZES AN EMAIL ADDRESS
function cleanEmail($email){
	$email=strtolower(strip_tags(trim($email)));
	$arr=explode("@",$email);
	$arr[0]=preg_replace("/[\s\"\#\%\(\)\,\;\<\>\[\]]+/","",$arr[0]);
	$arr[1]=preg_replace("/[^a-z0-9-.]+/","",$arr[1]);
	$email=implode("@",$arr);
	return $email;
}

//ENSURES THAT ONLY NUMERIC CHARACTERS ARE IN A STRING
function cleanNum($data) {
    $data=trim($data);
    $data=strip_tags($data);
    // REMOVE ANYTHING THAT'S NOT A NUMBER
    $data=ereg_replace("[^0-9]","",$data);
    return $data;
}

function cleanPhone($data) { // SAME AS CLEANNUM
    $data=trim($data);
    $data=strip_tags($data);
    // REMOVE ANYTHING THAT'S NOT A NUMBER OR A DASH
    $data=ereg_replace("[^0-9]","",$data);
    return $data;
}

//NEW AND IMPROVED EMAIL VALIDATION FUNCTION
function check_email_address($email) {
	return (bool) preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', $email);
}

//THESE TWO FUNCTIONS VALIDATE A CREDIT CARD NUMBER
function mod10cc( $ccnum ) {
    $double = array( 0,2,4,6,8,1,3,5,7,9 );
    $ccnum = strrev( $ccnum );
    for( $i=0; $i<strlen( $ccnum ); $i++ )
        $values[] = ( $i&1 ) ? $double[$ccnum[$i]] : $ccnum[$i];
    return ( array_sum( $values ) % 10 ) ? 0 : 1;
}

function validate_credit_card($number) {
	$return = (mod10cc($number)) ? TRUE : FALSE;
	if($return && ctype_digit($number)) {
		$return = TRUE;
	} else {
		$return = FALSE;
	}
	return $return;
}

// SCRUBS AGAINST A LIST OF KNOWN BAD PHONE NUMBERS OR PIECES OF BAD PHONE NUMBERS
function is_valid_phone($check){

	$check=(string)$check;
	
	$check=cleanNum($check);
	
	$bad=array(
		"000000",
		"111111",
		"222222",
		"333333",
		"444444",
		"555555",
		"666666",
		"777777",
		"888888",
		"999999",
		"123456"
	);
	
	//IS ANY STRING IN THIS ARRAY FOUND IN THE CHECK PHONE NUMBER? IF SO, RETURN FALSE
	$num=count($bad);
	for($x=0;$x<$num;$x++){ if(strpos($check,$bad[$x])!==false){ return false; } }
	
	// IF THE INTEGER EQUIVALENT OF THE PHONE NUMBER IS LESS THAN 1,000,000, RETURN FALSE
	$num=(int)$check;
	if($num<1000000){ return false; }
	
	//IF WE GOT HERE, WE MAY JUST HAVE A VALID PHONE NUMBER
	return true;

}

?>
