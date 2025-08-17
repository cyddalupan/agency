<?php

function checkViaAjax() 
{
	$isAjax= isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlttprequest';
	
	if (!$isAjax) { show_404(); }
}

function controllerActive($controller, $hasSubMenus = false) {
	$class = '';
	
	//Remove trailing slash
	$controller = strtolower( rtrim( $controller, '/' ) );
	
	$segments = explode( '/', uri_string() );
	
	array_walk($segments, function(&$value, $key) {
		$value = strtolower( $value );
	});
	
	$system 		= $segments[0]; //cpanel
	$uriController 	= $segments[1];

	if ( $controller == $uriController ) {
		$class = '';

		if ( $hasSubMenus == true && isset( $segments[2] ) ) {
			$class .= ' open';
		}
	}	
	
	return $class;
}

function subControllerActive($controllerMethod, $hasSubMenus = false) {
	$class = '';
	
	$controller = strtolower( explode( '/', $controllerMethod )[0] );
	$method 	= strtolower( explode( '/', $controllerMethod )[1] );
	
	$segments = explode( '/', uri_string() );
	
	array_walk( $segments, function(&$value, $key) {
		$value = strtolower( $value );
	});
	
	$system 		= $segments[0]; //cpanel
	$uriController 	= $segments[1];
	$uriMethod 		= isset( $segments[2] ) ? $segments[2] : '';
	
	if ( $controller == $uriController && $method == $uriMethod ) {
		$class = 'active';
		
		if ( $hasSubMenus == true && isset( $segments[3] ) ) {
			$class .= ' open';
		}
	}	
	
	return $class;
}

function getClassActiveController($Uri) 
{
	$currentUri = uri_string();
	
	$CI =& get_instance();
	
	if (in_array($Uri, array($currentUri, $CI->router->class))) { 
		return true; 
	}
	
	$cArr = explode('/', $currentUri);
	$tArr = explode('/', $Uri);

	$cSegments = $tSegments = array();
	
	$i = 0;
	foreach($cArr as $key => $segment) {
		if(!empty($segment)) { $cSegments[$i++] = $segment; }
	}
	
	$i = 0;
	foreach ($tArr as $key => $segment) {
		if (!empty($segment)) { $tSegments[$i++] = $segment; }
	} 
	
	for ($i=0; $i<count($tSegments); $i++) {
		if (!isset($cSegments[$i])) { return false; }
		if ($tSegments[$i] != $cSegments[$i]) { return false; }
	}
	
	return true; 
}

function href($uri, $defaultString = '#') 
{
	if (current_url() == site_url($uri)) {
		echo $defaultString;
		return;
	}
	
	echo site_url($uri);
} 

/*
* --------------------------- Development ----------------------- */

function dd($var, $toArray = false) 
{
	echo '<pre>';
	
	if (!$toArray) {
		print_r($var);
	} else {
		var_dump($var);
	}
		
	echo '</pre>';
	die();
}

function d($var) 
{
	var_dump($var);
}

function dpg() 
{
	var_dump($_GET);
	if (isset($_POST)) var_dump($_POST);
}  

function logAction($action, $category = "", $item = "") 
{
	if (!isset($_SESSION['user']['user_id'])) {
		return false;
	}
	
	$userId = $_SESSION['user']['user_id'];
	
	$ci =& get_instance();
	
	$data = array(
		'log_user'		=> $userId,
		'log_category'	=> $category,
		'log_item'		=> $item,
		'log_action'	=> $action,
		'log_created'	=> date('Y-m-d H:i:s', time())
	);
	
	return $ci->db->insert('user_log', $data);
}

/*
* --------------------------- String ----------------------- */

function ads($str) 
{
	return addslashes($str);
}

function strip($str) 
{
	return stripslashes($str);
}

function t($str) 
{
	return trim($str);
}

function str_short($str, $len, $dots = '...') 
{
	$str = htmlentities($str);
	return substr($str,0,$len).($len<strlen($str)?$dots:'');
}

function strSlug($string, $maxlen = 0) 
{
	$string = trim(preg_replace('/[^a-zA-Z0-9]+/', '+', ucwords($string)), '-');
	
    if ($maxlen && strlen($string) > $maxlen) {
        $string = substr($string, 0, $maxlen);
        $pos = strrpos($string, '-');
		
        if ($pos > 0) {
            $string = substr($string, 0, $pos);
        }
    }
	
    return $string;
}

function matchStr($match, $string) 
{
	return str_ireplace($match, '<span class="match-text">'.$match.'</span>', $string);
}

function mask( $str, $start = 0, $length = null ) {
    $mask = preg_replace ( "/\S/", "*", $str );
    if( is_null ( $length )) {
        $mask = substr ( $mask, $start );
        $str = substr_replace ( $str, $mask, $start );
    }else{
        $mask = substr ( $mask, $start, $length );
        $str = substr_replace ( $str, $mask, $start, $length );
    }
    return $str;
}

function strSlugSEO($string, $ext='.html')
{
	$replace = '-';
	
	$string = strtolower($string);
	
	//Remove query string     
	if (preg_match("#^http(s)?://[a-z0-9-_.]+\.[a-z]{2,4}#i",$string)) {
		$parsedUrl = parse_url($string);         
		$string 	= $parsedUrl['host'].' '.$parsedUrl['path'];        
		
		//if want to add scheme eg. http, https than uncomment next line        
		//$string = $parsed_url['scheme'].' '.$string;    
	}
	
	//replace / and . with white space
	$string = preg_replace("/[\/\.]/", " ", $string);    
	
	$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);     
	
	//remove multiple dashes or whitespaces     
	$string = preg_replace("/[\s-]+/", " ", $string);     
	
	//convert whitespaces and underscore to $replace    
	$string = preg_replace("/[\s_]/", $replace, $string);     
	
	//limit the slug size     
	$string = substr($string, 0, 100);     
	   
	//slug is generated     
	return ($ext) ? $string.$ext : $string; 
}

function camel2dashed($string) {
	return strtolower( preg_replace('/([^A-Z-])([A-Z])/', '$1-$2', $string) );
}

function fdate( $format, $date, $default = null )
{ 
    $dateFormatted       = date( $format, strtotime( $date ) );
    $emptyDateFormatted  = date( $format, strtotime( null ) );
    
    if ( $dateFormatted == $emptyDateFormatted ) {
        return is_null( $default ) ? date( $format, time() ) : $default;
    }
    
    return $dateFormatted;
}
