<?php

define('INSTALLER_VERSION', '0.6');
define('JCORE_URL', 'http://jcore.net/');
define('JCORE_VERSION', '0.7');

/***************************************************************************
 *            install.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 * 
 * 
 * This is the installer system for the jCore - the Webmasters Multisite CMS
 * 
 * To install jCore server and or client just load this file in your browser
 * and follow the required steps.
 * 
 * All jCore (Julius' Core) code is Copyright 2009 by Istvan Petres
 * (aka P.I.Julius) <me@pijulius.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE; if not, please see
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt.
 * 
 */

// This is to check if we want to set cookies and if yes we exit here.
// This is used to set cookies even after content written to browser 
// by using hidden iframes 
if (isset($_GET['cookie']) && $_GET['cookie']) {
	$cookie = $_GET['cookie'];
	$cookievalue = null;
	
	if (isset($_GET['cookievalue']))
		$cookievalue = $_GET['cookievalue'];
	
	setcookie("jCoreInstaller".$cookie, 
		$cookievalue, time()+3600*24);
				
	exit();
}

set_time_limit(0);

// This is a placeholder for future translations of the installer without
// using gettext 
function __($text) {
	return $text;
}
 
/***************************************************************************
 *            url.class.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 ****************************************************************************/
 
if (isset($_GET['path']))
	url::$pagePath = strip_tags($_GET['path']);

class url {
	static $pageTitle = PAGE_TITLE;
	static $pageDescription = META_DESCRIPTION;
	static $pageKeywords = META_KEYWORDS;
	static $pagePath = '';
	
	static function addPageTitle($title) {
		if (!$title)
			return false;
		
		url::$pageTitle =
			strip_tags($title) . 
			(url::$pageTitle?
				' - '.url::$pageTitle:
				null);
		
		return true;
	}
	
	static function addPageDescription($description) {
		if (!$description)
			return false;
		
		url::$pageDescription = 
			strip_tags($description) .
			(url::$pageDescription?
				' '.url::$pageDescription:
				null);
		
		return true;
	}
	
	static function addPageKeywords($keywords) {
		if (!$keywords)
			return false;
		
		url::$pageKeywords = 
			strip_tags($keywords) .
			(url::$pageKeywords?
				', '.url::$pageKeywords:
				null);
		
		return true;
	}
	
	static function setPageTitle($title) {
		url::$pageTitle = strip_tags($title);
		
	}
	
	static function setPageDescription($description) {
		url::$pageDescription = strip_tags($description);
	}
	
	static function setPageKeywords($keywords) {
		url::$pageKeywords = strip_tags($keywords);
	}
	
	static function getPageTitle() {
		return url::$pageTitle;
	}
	
	static function getPageDescription() {
		return url::$pageDescription;
	}
	
	static function getPageKeywords() {
		return url::$pageKeywords;
	}
	
	static function displayPageTitle($level = 0) {
		$title = url::getPageTitle();
		
		if ($level) {
			$titles = explode(' - ', $title);
			
			for($i = 0; $i < $level; $i++) {
				if ($i > 0) echo ' - ';
				echo $titles[$i];
			}
			
			return;
		}
		
		echo $title;
	}
	
	static function displayPageDescription() {
		echo htmlspecialchars(url::getPageDescription(), ENT_QUOTES);
	}
	
	static function displayPageKeywords() {
		echo htmlspecialchars(url::getPageKeywords(), ENT_QUOTES);
	}
	
	static function arg($argument) {
		if (!isset($_GET[$argument]))
			return null;
		
		return $argument.'='.$_GET[$argument];
	}
	
	static function args($notincludeargs = null) {
		$uri = str_replace('//', '/', $_SERVER['REQUEST_URI']);
		$expuri = explode('?', $uri);
		
		if (!isset($expuri[1]))
			return null;
		
		if (!$notincludeargs)
			return str_replace('&', '&amp;', $expuri[1]);
		
		$args = explode('&', $expuri[1]);
		$notincludeargs = explode(",", str_replace(" ", "", $notincludeargs));
		
		$rargs = null;
		foreach($args as $arg) {
			$expargs = explode('=', $arg);
			
			if (in_array($arg, $notincludeargs) ||
				in_array($expargs[0], $notincludeargs))
				continue;
			
			$rargs .= $arg."&amp;";
		}
		
		return substr($rargs, 0, strlen($rargs)-5);
	}
	
	static function delargs($args = null) {
		url::setURI(url::uri($args));
	}
	
	static function referer($striprequests = false) {
		if (!isset($_SERVER['HTTP_REFERER']))
			return null;
		
		if ($striprequests) 
			return 
				preg_replace('/((\?)|&)request=.*/i', '\\1', 
					$_SERVER['HTTP_REFERER']);
		
		return $_SERVER['HTTP_REFERER'];
	}
	
	static function setURI($uri) {
		$_SERVER['REQUEST_URI'] = str_replace('&amp;', '&', $uri);
	}

	static function uri($notincludeargs = null, $inverse = false) {
		$uri = str_replace('//', '/', $_SERVER['REQUEST_URI']);
		$expuri = explode('?', $uri);
		
		if (!$notincludeargs)
			return str_replace('&', '&amp;', $uri).
				(!isset($expuri[1])?
					'?':
					null);
		
		if ($notincludeargs == 'ALL')
			return $expuri[0];
		
		if (!isset($expuri[1]))
			return $expuri[0].'?';
		
		$args = explode('&', $expuri[1]);
		$notincludeargs = explode(",", str_replace(" ", "", $notincludeargs));
		
		$rargs = null;
		foreach($args as $arg) {
			$expargs = explode('=', $arg);
			
			if ((!$inverse && 
				!in_array($arg, $notincludeargs) &&
				!in_array($expargs[0], $notincludeargs)) ||
				($inverse && 
				in_array($expargs[0], $notincludeargs))) 
			{
				$rargs .= $arg."&amp;";
			}
		}
		
		return $expuri[0].'?'.substr($rargs, 0, strlen($rargs)-5);
	}
	
	static function get() {
		$https = false;
		
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
			$https = true;
		
		$url = 'http'.($https?'s':null).'://'.$_SERVER['SERVER_NAME'];
		
		if (($_SERVER['SERVER_PORT'] != '80' && !$https) ||
			($_SERVER['SERVER_PORT'] != '443' && $https))
			$url .= ':'.$_SERVER['SERVER_PORT'];
		
		$url .= $_SERVER['REQUEST_URI'];
		return $url;
	}
	
	static function fix($url, $reverse = false) {
		if (!$url) 
			return null;
			
		$url = strip_tags($url);
		
		if (strstr($url, " "))
			$url = substr($url, 0, strpos($url, " "));
			
		if ($reverse)
			return preg_replace('/^(.*?):\/\//', null, strtolower($url));
		
		if (!preg_match('/^(\/|.*?:\/\/)/', $url) &&
			preg_match('/(www|(.*?\..*))/', $url)) 
				return "http://".$url;
		
		return $url;
	}
	
	static function parseLinks($content) {
		return preg_replace(
			"'(\"|\\'|>)?([[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/])'ie",
				"eval(\"
					if ('\\1') {
						return '\\1\\2';
					} else {
						if (strlen('\\2') > 70) return '<a href=\'\\2\' target=\'_blank\'>'.substr('\\2', 0, 70).'...</a>';
						else return '<a href=\'\\2\' target=\'_blank\'>\\2</a>';
					}
				\"); ", $content);
	}
	
	static function path($level= 0) {
		if (!url::$pagePath)
			return;
			
		if (!$level)
			return url::$pagePath;
			
		$path = null;
		$exppaths = explode('/', url::$pagePath);
		
		foreach($exppaths as $key => $exppath) {
			if ($path) 
				$path .= '/';
				
			$path .= $exppath;
			
			if ($key == count($exppaths)-1-$level)
				break;
		}
		
		return $path;
	}
	
	static function setPath($path) {
		url::$pagePath = $path;
	}
	
	static function rootDomain() {
		return preg_replace('/(\/.*|^www\.)/', '',  
					preg_replace('/.*:\/\//', '', 
						SITE_URL));
	}
	
	static function getPathID($level = 0, $pathvar = 'path') {
		$path = admin::path($level);
		
		if (!$path)
			return 0;
		
		preg_match('/.*\/([0-9]*)(\/|$|&)/', $path, $matches);
		
		if (isset($matches[1]))
			return (int)$matches[1];
		
		return 0;
	}
	
	static function generateLink($link) {
		return
			(!preg_match('/^\/|^javascript:|^(.*?):\/\//', $link)?
				ROOT_DIR:
				null).
			$link;
	}
	
	static function genPathFromString($string) {
		$chars = array(
			'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Auml;|&Aring;/',
			'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;|&ordf;/',
			'C' => '/&Ccedil;/',
			'c' => '/&ccedil;/',
			'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
			'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
			'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
			'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
			'N' => '/&Ntilde;/',
			'n' => '/&ntilde;/',
			'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|&Ouml;/',
			'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;|&ordm;/',
			'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Uuml;/',
			'u' => '/&ugrave;|&uacute;|&ucirc;|&uuml;/',
			'Y' => '/&Yacute;/',
			'y' => '/&yacute;|&yuml;/',
			'-' => '/&nbsp;| - |  | |\/|\\\|\|/'
			);
		
		return strtolower(
			preg_replace('/([^a-z^0-9^_^-]*)/i',
			'', 
			preg_replace($chars, array_keys($chars), 
				htmlentities(strip_tags(trim($string)), 
					ENT_NOQUOTES))));
	}
	
	static function escapeRegexp($string) {
		$patterns = array(
			'/\//', '/\^/', '/\./', '/\$/', '/\|/',
			'/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/', 
			'/\?/', '/\{/', '/\}/', '/\,/');
		
		$replace = array(
			'\/', '\^', '\.', '\$', '\|', '\(', '\)', 
			'\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,');
		
		return preg_replace($patterns,$replace, $string);
	}
	
	static function flushDisplay($delay = false) {
		@ob_flush();
		flush();
		
		if ($delay)
			usleep(50000);
	}
	
	static function searchQuery($search, $fields = array('Title'), $type = 'AND') {
		if (!trim($search) || !is_array($fields) || !count($fields))
			return;
			
		if (strstr($search, ','))
			$separator = ',';
		else
			$separator = ' ';
		
		$query = null;
		$keywords = explode($separator, trim($search));
		
		if (count($keywords) > 21)
			$keywords = array_slice($keywords, 0, 21);
		
		foreach($fields as $field) {
			if ($query)
				$query .= " OR";
			
			$keywordsquery = null;
			
			foreach($keywords as $keyword) {
				if ($keywordsquery)
					$keywordsquery .= " ".$type;
			
				$keywordsquery .= " `".$field."` LIKE '%".
					sql::escape(trim($keyword))."%'";
			}
			
			if ($keywordsquery)
				$query .= " (".$keywordsquery.") ";
		}
		
		if (!$query)
			return;
		
		return " AND (".$query.")";
	}
	
	static function displayCSSLinks() {
		modules::displayCSSLinks();
		
		if ($GLOBALS['ADMIN'])
			admin::displayCSSLinks();
	}
	
	static function displayPath($level = 0, $displaypath = null) {
		if (!$displaypath)
			$displaypath = url::$pagePath;
			
		if (!$displaypath)
			return;
			
		$path = null;
		$exppaths = explode('/', $displaypath);
		
		$i = 0;
		foreach($exppaths as $key => $exppath) {
			if ($path) 
				$path .= '/';
				
			$path .= $exppath;
			
			if ($key < $level)
				continue;
			
			if (!(int)$exppath) {
				if ($i > 0)
					echo "<span class='url-path-separator'> / </span>";
				
				if (SEO_FRIENDLY_LINKS && 
					(!isset($GLOBALS['ADMIN']) || !$GLOBALS['ADMIN'])) 
				{
					echo
						"<a href='". SITE_URL .
							$path."'>".__($exppath)."</a>";
					
				} else {
					echo
						"<a href='". url::uri('ALL') .
							"?path=".$path."'>".__($exppath)."</a>";
				}
				
				$i++;
			}
		}
	}
	
	static function displayRootPath() {
		echo ROOT_DIR;
	}
	
	static function displayError() {
		$codes = new contentCodes();
		$codes->display(PAGE_404_ERROR_TEXT);
		unset($codes);
	}
	
	static function displayValidXHTML() {
		echo 
			"<p class='validXHTML'>" .
				"<a href='http://validator.w3.org/check?uri=referer' " .
					"target='_blank'>" .
					"<img style='border:0;width:88px;height:31px' " .
						"src='http://www.w3.org/Icons/valid-xhtml10' " .
						"alt='Valid XHTML 1.0 Transitional' />" .
				"</a>" .
			"</p>";
	}
	
	static function displayValidCSS() {
		echo
			"<p class='validCSS'>" .
				"<a href='http://jigsaw.w3.org/css-validator/check/referer?profile=css3' " .
					"target='_blank'>" .
					"<img style='border:0;width:88px;height:31px' " .
						"src='http://jigsaw.w3.org/css-validator/images/vcss' " .
						"alt='Valid CSS!' />" .
				"</a>" .
			"</p>";
	}
	
	static function displaySearch($search, $results = null) {
		if (!$search)
			return;
		
		$searches = array();
		
		$tooltipcontent = 
			__("Searching for").": ";
		
		if (strstr($search, ','))
			$separator = ',';
		else
			$separator = ' ';
		
		foreach(explode($separator, $search) as $key => $searchtag) {
			if (!trim($searchtag))
				continue;
			
			if (in_array(trim($searchtag), $searches))
				continue;
			
			$searches[] = trim($searchtag);
			$tooltipcontent .= 
				"<a href='".url::uri('search').
					"&amp;search=" .
					urlencode(
						trim(
							preg_replace(
								'/'.
									($key?'(^|'.$separator.')':'').
									url::escapeRegexp($searchtag).
									(!$key?'('.$separator.'|$)':'').
								'/i', 
								'', 
								$search))) .
					"'>".
				strtoupper(trim($searchtag))."</a>" .
				"<sup class='red'>x</sup> &nbsp;";
		}
		
		$tooltipcontent .= 
			"(<a href='".url::uri('search, searchin')."'>" .
				__("clear") .
			"</a>)";
			
		tooltip::display(
			$tooltipcontent,
			TOOLTIP_NOTIFICATION);
		
		if (isset($results) && !$results)
			tooltip::display(
				__("Your search returned no results. Please make sure all " .
					"words are spelled correctly or try fewer keywords by " .
					"clicking on them to remove."),
				TOOLTIP_NOTIFICATION);
	}
	
	function displayArguments() {
		if (!$this->arguments)
			return false;
		
		$encode = false;
		$decode = false;
			
		if (preg_match('/(^|\/)encode($|\/)/', $this->arguments)) {
			$this->arguments = preg_replace('/(^|\/)encode($|\/)/', '\1', $this->arguments);
			$encode = true;
		}
		
		if (preg_match('/(^|\/)decode($|\/)/', $this->arguments)) {
			$this->arguments = preg_replace('/(^|\/)decode($|\/)/', '\1', $this->arguments);
			$decode = true;
		}
		
		preg_match('/(.*?)(\/|$)(.*)/', $this->arguments, $matches);
		
		if (!isset($matches[1]) || !$matches[1]) {
			if ($encode)
				echo urlencode(url::get());
			elseif ($decode)
				echo urldecode(url::get());
			else
				echo url::get();
			
			return true;
		}
		
		$argument = strtolower($matches[1]);
		$parameters = null;
		$path = null;
		
		if (isset($matches[3]))
			$parameters = $matches[3];
			
		if (isset($_GET['path']))
			$path = strip_tags($_GET['path']);
		
		ob_start();
		
		switch($argument) {
			case 'uri':
				echo url::uri($parameters);
				break;
				
			case 'server':
				echo $_SERVER['SERVER_NAME'];
				break;
				
			case 'sessionid':
				echo session_id();
				break;
				
			case 'root':
				echo ROOT_DIR;
				break;
				
			case 'srcpath':
				echo $path;
				break;
				
			case 'title':
				url::displayPageTitle($parameters);
				break;
				
			case 'description':
				url::displayPageDescription();
				break;
				
			case 'path':
				url::displayPath($parameters, $path);
				break;
				
			case 'validxhtml':
				url::displayValidXHTML();
				break;
				
			case 'validcss':
				url::displayValidCSS();
				break;
			
			default:
				echo $parameters;
				break;
		}
		
		$content = ob_get_contents();
		ob_end_clean();
		
		if ($encode)
			echo urlencode(htmlspecialchars_decode($content, ENT_QUOTES));
		elseif ($decode)
			echo htmlspecialchars(urldecode($content), ENT_QUOTES);
		else
			echo $content;
		
		return true;
	}
	
	function display() {
		if ($this->displayArguments())
			return;
	}
}

/***************************************************************************
 *            files.class.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 ****************************************************************************/

define('FILE_TYPE_UPLOAD', 1); 
define('FILE_TYPE_IMAGE', 2);
define('FILE_TYPE_VIDEO', 3); 
define('FILE_TYPE_BANNER', 4);
define('FILE_TYPE_AUDIO', 5); 

class files {
	static $allowedFileTypes = array(
		FILE_TYPE_UPLOAD => '\.(7z|aiff|asf|avi|bmp|csv|doc|fla|flv|gif|gz|gzip|jpeg|jpg|mid|mov|mp3|mp4|mpc|mpeg|mpg|ods|odt|pdf|png|ppt|eps|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|sdc|sitd|swf|sxc|sxw|tar|tgz|tif|tiff|txt|vsd|wav|wma|wmv|xls|xml|zip|patch|sql|mo|po)$',
		FILE_TYPE_IMAGE => '\.(jpg|gif|jpeg|png|bmp)$',
		FILE_TYPE_VIDEO => '\.(avi|wmv|swf|flv|mov|mp4|webm|ogv|mpeg|mpg|qt|rm)$', 
		FILE_TYPE_BANNER => '\.(jpg|gif|jpeg|png|bmp|swf)$',
		FILE_TYPE_AUDIO => '\.(mid|mp3|rmi|wav|wma)$');
	
	static $mimeTypes = array(
		"323" => "text/h323",
		"acx" => "application/internet-property-stream",
		"ai" => "application/postscript",
		"aif" => "audio/x-aiff",
		"aifc" => "audio/x-aiff",
		"aiff" => "audio/x-aiff",
		"asf" => "video/x-ms-asf",
		"asr" => "video/x-ms-asf",
		"asx" => "video/x-ms-asf",
		"au" => "audio/basic",
		"avi" => "video/x-msvideo",
		"axs" => "application/olescript",
		"bas" => "text/plain",
		"bcpio" => "application/x-bcpio",
		"bin" => "application/octet-stream",
		"bmp" => "image/bmp",
		"c" => "text/plain",
		"cat" => "application/vnd.ms-pkiseccat",
		"cdf" => "application/x-cdf",
		"cer" => "application/x-x509-ca-cert",
		"class" => "application/octet-stream",
		"clp" => "application/x-msclip",
		"cmx" => "image/x-cmx",
		"cod" => "image/cis-cod",
		"cpio" => "application/x-cpio",
		"crd" => "application/x-mscardfile",
		"crl" => "application/pkix-crl",
		"crt" => "application/x-x509-ca-cert",
		"csh" => "application/x-csh",
		"css" => "text/css",
		"dcr" => "application/x-director",
		"der" => "application/x-x509-ca-cert",
		"dir" => "application/x-director",
		"dll" => "application/x-msdownload",
		"dms" => "application/octet-stream",
		"doc" => "application/msword",
		"dot" => "application/msword",
		"dvi" => "application/x-dvi",
		"dxr" => "application/x-director",
		"eps" => "application/postscript",
		"etx" => "text/x-setext",
		"evy" => "application/envoy",
		"exe" => "application/octet-stream",
		"fif" => "application/fractals",
		"flr" => "x-world/x-vrml",
		"gif" => "image/gif",
		"gtar" => "application/x-gtar",
		"gz" => "application/x-gzip",
		"h" => "text/plain",
		"hdf" => "application/x-hdf",
		"hlp" => "application/winhlp",
		"hqx" => "application/mac-binhex40",
		"hta" => "application/hta",
		"htc" => "text/x-component",
		"htm" => "text/html",
		"html" => "text/html",
		"htt" => "text/webviewhtml",
		"ico" => "image/x-icon",
		"ief" => "image/ief",
		"iii" => "application/x-iphone",
		"ins" => "application/x-internet-signup",
		"isp" => "application/x-internet-signup",
		"jfif" => "image/pipeg",
		"jpe" => "image/jpeg",
		"jpeg" => "image/jpeg",
		"jpg" => "image/jpeg",
		"js" => "application/x-javascript",
		"latex" => "application/x-latex",
		"lha" => "application/octet-stream",
		"lsf" => "video/x-la-asf",
		"lsx" => "video/x-la-asf",
		"lzh" => "application/octet-stream",
		"m13" => "application/x-msmediaview",
		"m14" => "application/x-msmediaview",
		"m3u" => "audio/x-mpegurl",
		"man" => "application/x-troff-man",
		"mdb" => "application/x-msaccess",
		"me" => "application/x-troff-me",
		"mht" => "message/rfc822",
		"mhtml" => "message/rfc822",
		"mid" => "audio/mid",
		"mny" => "application/x-msmoney",
		"mov" => "video/quicktime",
		"movie" => "video/x-sgi-movie",
		"mp2" => "video/mpeg",
		"mp3" => "audio/mpeg",
		"mpa" => "video/mpeg",
		"mpe" => "video/mpeg",
		"mpeg" => "video/mpeg",
		"mpg" => "video/mpeg",
		"mpp" => "application/vnd.ms-project",
		"mpv2" => "video/mpeg",
		"ms" => "application/x-troff-ms",
		"mvb" => "application/x-msmediaview",
		"nws" => "message/rfc822",
		"oda" => "application/oda",
		"p10" => "application/pkcs10",
		"p12" => "application/x-pkcs12",
		"p7b" => "application/x-pkcs7-certificates",
		"p7c" => "application/x-pkcs7-mime",
		"p7m" => "application/x-pkcs7-mime",
		"p7r" => "application/x-pkcs7-certreqresp",
		"p7s" => "application/x-pkcs7-signature",
		"pbm" => "image/x-portable-bitmap",
		"pdf" => "application/pdf",
		"pfx" => "application/x-pkcs12",
		"pgm" => "image/x-portable-graymap",
		"pko" => "application/ynd.ms-pkipko",
		"pma" => "application/x-perfmon",
		"pmc" => "application/x-perfmon",
		"pml" => "application/x-perfmon",
		"pmr" => "application/x-perfmon",
		"pmw" => "application/x-perfmon",
		"pnm" => "image/x-portable-anymap",
		"pot" => "application/vnd.ms-powerpoint",
		"ppm" => "image/x-portable-pixmap",
		"pps" => "application/vnd.ms-powerpoint",
		"ppt" => "application/vnd.ms-powerpoint",
		"prf" => "application/pics-rules",
		"ps" => "application/postscript",
		"pub" => "application/x-mspublisher",
		"qt" => "video/quicktime",
		"ra" => "audio/x-pn-realaudio",
		"ram" => "audio/x-pn-realaudio",
		"ras" => "image/x-cmu-raster",
		"rgb" => "image/x-rgb",
		"rmi" => "audio/mid",
		"roff" => "application/x-troff",
		"rtf" => "application/rtf",
		"rtx" => "text/richtext",
		"scd" => "application/x-msschedule",
		"sct" => "text/scriptlet",
		"setpay" => "application/set-payment-initiation",
		"setreg" => "application/set-registration-initiation",
		"sh" => "application/x-sh",
		"shar" => "application/x-shar",
		"sit" => "application/x-stuffit",
		"snd" => "audio/basic",
		"spc" => "application/x-pkcs7-certificates",
		"spl" => "application/futuresplash",
		"src" => "application/x-wais-source",
		"sst" => "application/vnd.ms-pkicertstore",
		"stl" => "application/vnd.ms-pkistl",
		"stm" => "text/html",
		"svg" => "image/svg+xml",
		"sv4cpio" => "application/x-sv4cpio",
		"sv4crc" => "application/x-sv4crc",
		"t" => "application/x-troff",
		"tar" => "application/x-tar",
		"tcl" => "application/x-tcl",
		"tex" => "application/x-tex",
		"texi" => "application/x-texinfo",
		"texinfo" => "application/x-texinfo",
		"tgz" => "application/x-compressed",
		"tif" => "image/tiff",
		"tiff" => "image/tiff",
		"tr" => "application/x-troff",
		"trm" => "application/x-msterminal",
		"tsv" => "text/tab-separated-values",
		"txt" => "text/plain",
		"uls" => "text/iuls",
		"ustar" => "application/x-ustar",
		"vcf" => "text/x-vcard",
		"vrml" => "x-world/x-vrml",
		"wav" => "audio/x-wav",
		"wcm" => "application/vnd.ms-works",
		"wdb" => "application/vnd.ms-works",
		"wks" => "application/vnd.ms-works",
		"wmf" => "application/x-msmetafile",
		"wps" => "application/vnd.ms-works",
		"wri" => "application/x-mswrite",
		"wrl" => "x-world/x-vrml",
		"wrz" => "x-world/x-vrml",
		"xaf" => "x-world/x-vrml",
		"xbm" => "image/x-xbitmap",
		"xla" => "application/vnd.ms-excel",
		"xlc" => "application/vnd.ms-excel",
		"xlm" => "application/vnd.ms-excel",
		"xls" => "application/vnd.ms-excel",
		"xlt" => "application/vnd.ms-excel",
		"xlw" => "application/vnd.ms-excel",
		"xof" => "x-world/x-vrml",
		"xpm" => "image/x-xpixmap",
		"xwd" => "image/x-xwindowdump",
		"z" => "application/x-compress",
		"zip" => "application/zip");
		
	static function getUploadMaxFilesize() {
		return settings::iniGet('upload_max_filesize', true);
	}
	
 	static function upload($file, $to, $filetype = FILE_TYPE_UPLOAD) {
		$topath = preg_replace('/(.*(\/|\\\)).*/', '\1', $to);
		$tofilename = preg_replace('/.*(\/|\\\)/', '', $to);
		
 		if (strstr($file, '://')) {
			$filename = preg_replace('/.*(\/|\\\)/', '', $file);
			
			if (!$tofilename)
				$tofilename = preg_replace("/[^A-Za-z0-9._-]/", "", $filename);
 			
 		} elseif (strstr($file, '/') || strstr($file, '\\')) {
			$filename = preg_replace('/.*(\/|\\\)/', '', $file);
			
			if (!$tofilename) {
				foreach($_FILES as $f) {
					if (is_array($f['tmp_name'])) {
						foreach($f['tmp_name'] as $key => $fi) {
							if ($fi == $file) {
								$tofilename = preg_replace("/[^A-Za-z0-9._-]/", "", 
									$f['name'][$key]);
								break;
							}
						}
						
					} elseif ($f['tmp_name'] == $file) {
						$tofilename = preg_replace("/[^A-Za-z0-9._-]/", "", 
							$f['name']);
						break;
					}
				}
			}
 			
			if (!$tofilename)
				$tofilename = preg_replace("/[^A-Za-z0-9._-]/", "", $filename);
 			
 		} else {
	 		$fileid = preg_replace('/\[.*?\]/', '', $file);
	 		$filearrayid = null;
	 		
 			preg_match('/\[(.*?)\]/', $file, $matches);
 			
 			if (isset($matches[1]))
	 			$filearrayid = $matches[1];
	 		
 			if (!isset($_FILES[$fileid]))
 				return false;
 			
	 		if (isset($filearrayid)) {
	 			$file = $_FILES[$fileid]['tmp_name'][$filearrayid];
				$filename = $_FILES[$fileid]['name'][$filearrayid];
	 		} else {
	 			$file = $_FILES[$fileid]['tmp_name'];
				$filename = $_FILES[$fileid]['name'];
	 		}
	 		
			if (!$tofilename)
				$tofilename = preg_replace("/[^A-Za-z0-9._-]/", "", $filename);
 		}
		
		//if uploader is not admin we won't allow files to be overwritten
		if (!$GLOBALS['USER']->data['Admin'] && @file_exists($topath.$tofilename)) {
			tooltip::display(
				sprintf(__("The file you are trying to upload \"%s\" already exists " .
					"on our site. Please rename and reselect the file you " .
					"would like to upload and try again."), $tofilename),
				TOOLTIP_ERROR);
				
			return false;
		}
		
		if (!preg_match("/".files::$allowedFileTypes[$filetype]."/i", $tofilename)) {
			tooltip::display(
				__("Unsuported file format! Supported formats are").
					" ".str_replace('|', ', ', files::$allowedFileTypes[$filetype]).". " .
				__("Please reselect the file you would like to upload."),
				TOOLTIP_ERROR);
				
			return false;
		}
		
		if (!is_dir($topath) && !@mkdir($topath, 0777, true)) {
			tooltip::display(
				__("Setting up your file's storage failed!"). " " . 
				sprintf(__("Please make sure that %s is writable by me " .
					"or contact webmaster."), $topath),
				TOOLTIP_ERROR);
				
			return false;
		}
		
 		if (strstr($file, '://')) {
			$uploaded = @copy($file, $topath.$tofilename);
 		} else {
			$uploaded = @move_uploaded_file($file, $topath.$tofilename);
 		}
		
		if (!$uploaded) {
			tooltip::display(
				__("Couldn't move the file to the storage!"). " " .
				sprintf(__("This usually means that your file is larger than the " .
					"allowed upload limit (%s) but please also make sure that %s " .
					"is writable by me or contact webmaster."),
						files::humanSize(files::getUploadMaxFilesize()),
						$topath), 
				TOOLTIP_ERROR);
				
			return false;
		}

		return $tofilename;
 	}
 	
 	static function display($file, $forcedownload = false, $resumable = true) {
 		if (!@is_file($file))
 			return;
 		
		$size = @filesize($file);
		$fileinfo = @pathinfo($file);
		$filemtime = @filemtime($file);
		
		$filename = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ?
			preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'], '.') - 1) :
			$fileinfo['basename'];
		
		$ctype='application/force-download';
		
		if (!$forcedownload && isset(files::$mimeTypes[strtolower($fileinfo['extension'])]))
			$ctype = files::$mimeTypes[strtolower($fileinfo['extension'])];
		
		if($resumable && isset($_SERVER['HTTP_RANGE'])) {
			list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

			if ($size_unit == 'bytes')
				list($range, $extra_ranges) = explode(',', $range_orig, 2);
			else
				$range = '';
		} else {
			$range = '';
		}
		
		$seek_end = null;
		$seek_start = null;
		
		$exprange = explode('-', $range, 2);
		
		if (isset($exprange[0]))
			$seek_start = $exprange[0];
		
		if (isset($exprange[1]))
			$seek_end = $exprange[1];
		
		$seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
		$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
		
		if ($forcedownload && $resumable) {
			if ($seek_start > 0 || $seek_end < ($size - 1)) {
				header('HTTP/1.1 206 Partial Content');
			}
			
			header('Accept-Ranges: bytes');
			header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
		}

		header('Cache-Control: public');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $filemtime).' GMT');
		
		header('Content-Type: ' . $ctype);
		header('Content-Length: '.($seek_end - $seek_start + 1));
		
		if ($forcedownload)
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
			(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $filemtime)) 
		{
			header('HTTP/1.0 304 Not Modified');
			return;
		}
		
		$fp = fopen($file, 'rb');
		fseek($fp, $seek_start);
		
		while(!feof($fp)) {
			if (!ini_get('safe_mode'))
	        	set_time_limit(0);
	        
    	    print(fread($fp, 1024*8));
        	flush();
        	ob_flush();
		}
		
		fclose($fp);
 	}
 	
 	static function humanSize($size) {
		$sizetext = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		
		$i = 0;
		for ($i = 0; $size >= 1024; $i++)
			$size /= 1024;
		
		return round($size).' '.$sizetext[$i];
 	}
 	
 	static function mimeType($file) {
        $type = @exec("file -bi ".escapeshellarg($file));
        if($type)
        	return $type;
        
        $type = files::$mimeTypes[preg_replace('/.*\./', '', $file)];
 		if ($type)
        	return $type;
        
        return __("uknown/file");
 	}
 	
 	static function humanMimeType($file) {
 		$type = @exec("file -b ".escapeshellarg($file));
 		if ($type)
        	return $type;
        
        $type = files::$mimeTypes[preg_replace('/.*\./', '', $file)];
 		if ($type)
        	return $type;
        
        return __("Uknown File Type");
 	}
 	
 	static function ext2MimeClass($file) {
		if (preg_match('/\.(7z|rar|gz|gzip|tar|tgz|zip)$/i', $file))
			return "mime-type-package";
		
		if (preg_match('/\.(gif|bmp|jpeg|jpg|png|tif|tiff)$/i', $file))
			return "mime-type-photo";
		
		if (preg_match('/\.(asf|avi|mov|fla|flv|mid|mp3|mp4|mpc|mpeg|mpg|rm|qt|ram|swf|wav|wma|wmv)$/i', $file))
			return "mime-type-multimedia";
		
		if (preg_match('/\.(csv|doc|pdf|ppt|rtf|xls)$/i', $file))
			return "mime-type-office";
		
		if (preg_match('/\.(txt|xml)$/i', $file))
			return "mime-type-text";
		
		if (preg_match('/\.(patch)$/i', $file))
			return "mime-type-patch";
		
		if (preg_match('/\.(sql)$/i', $file))
			return "mime-type-db";
 	}
 	
 	static function exists($file) {
 		return file_exists($file);
 	}
 	
 	static function delete($file) {
 		if (strstr($file, '://'))
 			return false;
 		
 		if (@is_dir($file)) {
			$d = dir($file);
			
			while (false !== ($entry = $d->read()))
				if ($entry != '.' && $entry != '..')
					files::delete($file.'/'.$entry);
			
			$d->close();
			return @rmdir($file);
 		}
 		
 		return @unlink($file);
 	}
 	
 	static function rename($file, $to) {
 		$dir = preg_replace('/((.*(\/|\\\))|^).*$/', '\2', $to);
 		
		if ($dir && !is_dir($dir) && !@mkdir($dir, 0777, true))
			return false;
 		
 		return @rename($file, $to);
 	}
 	
 	static function copy($file, $to) {
 		$dir = preg_replace('/((.*(\/|\\\))|^).*$/', '\2', $to);
 		
		if ($dir && !is_dir($dir) && !@mkdir($dir, 0777, true))
			return false;
 		
		return @copy($file, $to);
 	}
 	
 	static function get($file, $httpheader = null) {
 		if (strstr($file, '://')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $file);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			if ($httpheader && is_array($httpheader))
				curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
			
			$data = curl_exec($ch);
			curl_close($ch);
			
			return $data;
 		}
 		
 		return @file_get_contents($file);
 	}
 	
 	static function create($file, $data) {
 		$dir = preg_replace('/((.*(\/|\\\))|^).*$/', '\2', $file);
 		
		if ($dir && !is_dir($dir) && !@mkdir($dir, 0777, true))
			return false;
 		
 		return @file_put_contents($file, $data);
 	}
 	
 	static function save($file, $data = null, $debug = false) {
 		if ($debug)
 			echo "<p>".__("Writing file:")." ".$file." ";
 		
 		$result = @files::create($file, $data);
 		
 		if (!$result) {
 			if ($debug)
 				echo __("[ERROR]")."</p>";
 			
 			return false;
 		}
 		
 		if ($debug)
			echo __("[SUCCESS]")."</p>";
		
 		return @$result;
 	}
}

/***************************************************************************
 *            form.class.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 ****************************************************************************/
 
define('FORM_INPUT_TYPE_TEXT', 1);
define('FORM_INPUT_TYPE_EMAIL', 2);
define('FORM_INPUT_TYPE_CHECKBOX', 3);
define('FORM_INPUT_TYPE_RADIO', 4);
define('FORM_INPUT_TYPE_SELECT', 5);
define('FORM_INPUT_TYPE_TEXTAREA', 6);
define('FORM_INPUT_TYPE_HIDDEN', 7);
define('FORM_INPUT_TYPE_SUBMIT', 8);
define('FORM_INPUT_TYPE_RESET', 9);
define('FORM_INPUT_TYPE_BUTTON', 10);
define('FORM_INPUT_TYPE_VERIFICATION_CODE', 11);
define('FORM_INPUT_TYPE_FILE', 12);
define('FORM_OPEN_FRAME_CONTAINER', 13);
define('FORM_CLOSE_FRAME_CONTAINER', 14);
define('FORM_INPUT_TYPE_MULTISELECT', 15);
define('FORM_INPUT_TYPE_TIMESTAMP', 16);
define('FORM_INPUT_TYPE_DATE', 17);
define('FORM_STATIC_TEXT', 18);
define('FORM_INPUT_TYPE_EDITOR', 19);
define('FORM_INPUT_TYPE_PASSWORD', 20);
define('FORM_INPUT_TYPE_CONFIRM', 21);
define('FORM_INPUT_TYPE_REVIEW', 22);
define('FORM_INPUT_TYPE_CODE_EDITOR', 23);
define('FORM_INPUT_TYPE_COLOR', 24);
define('FORM_INPUT_TYPE_SEARCH', 25);
define('FORM_INPUT_TYPE_TEL', 26);
define('FORM_INPUT_TYPE_URL', 27);
define('FORM_INPUT_TYPE_RANGE', 28);
define('FORM_INPUT_TYPE_NUMBER', 29);
define('FORM_INPUT_TYPE_TIME', 30);

define('FORM_VALUE_TYPE_STRING', 1);
define('FORM_VALUE_TYPE_INT', 2);
define('FORM_VALUE_TYPE_ARRAY', 3);
define('FORM_VALUE_TYPE_TIMESTAMP', 4);
define('FORM_VALUE_TYPE_DATE', 5);
define('FORM_VALUE_TYPE_HTML', 6);
define('FORM_VALUE_TYPE_URL', 7);
define('FORM_VALUE_TYPE_LIMITED_STRING', 8);
define('FORM_VALUE_TYPE_TEXT', 9);
define('FORM_VALUE_TYPE_BOOL', 10);
define('FORM_VALUE_TYPE_FILE', 11);
define('FORM_VALUE_TYPE_FLOAT', 12);

define('FORM_INSERT_AFTER', 1);
define('FORM_INSERT_BEFORE', 2);

define('FORM_ELEMENT_SET', 1);
define('FORM_ELEMENT_ADD', 2);
define('FORM_ELEMENT_ARRAY', 3);

class form {
	var $title;
	var $id;
	var $action;
	var $method;
	var $attributes = '';
	var $elements = array();
	var $footer = null;
	var $preview = false;
	var $verifyPassword = true;
	var $textsDomain = 'messages';
	
	var $emptyElement = array(
		'Title' => '',
		'Name' => 'PlaceholderElement',
		'EntryID' => '',
		'Type' => FORM_INPUT_TYPE_HIDDEN,
		'Required' => false,
		'OriginalValue' => null,
		'Attributes' => '',
		'ValueType' => FORM_VALUE_TYPE_TEXT,
		'Value' => null);
	
	function __construct($title = null, $id = null, $method = 'post') {
		$this->title = $title;
		$this->id = ($id?$id:preg_replace('/ /', '', strtolower($title)));
		$this->action = url::uri();
		$this->method = $method;
		$this->textsDomain = 'messages';
	}
	
	function submitted() {
		if ($this->get($this->id."submit"))
			return $this->get($this->id."submit");
		
		foreach($this->elements as $element) {
			if ($element['Type'] == FORM_INPUT_TYPE_SUBMIT && 
				$this->get($element['Name']))
			{
				return $this->get($element['Name']);
			}
		}
		
		return false;
	}
	
	function reset($elementname = null) {
		foreach($this->elements as $elementnum => $element) {
			if (!$elementname || $elementname == $element['Name'])
				unset($GLOBALS['_'.strtoupper($this->method)][$element['Name']]);
				unset($this->elements[$elementnum]['VerifyResult']);
				
				if (in_array($element['Type'], array(
					FORM_INPUT_TYPE_CHECKBOX,
					FORM_INPUT_TYPE_RADIO,
					FORM_INPUT_TYPE_SELECT,
					FORM_INPUT_TYPE_MULTISELECT)))
				{
					$this->elements[$elementnum]['Value'] = null;
					continue;
				}
				
				$originalvalue = null;
				
				if (isset($this->elements[$elementnum]['OriginalValue']))
					$originalvalue = $this->elements[$elementnum]['OriginalValue'];
				
				$this->elements[$elementnum]['Value'] = $originalvalue;
		}
	}
	
	function clear() {
		$this->elements = array();
	}
	
	function getPostArray() {
		$post = array();
		
		foreach($this->elements as $element) {
			$elementid = preg_replace('/\[.*\]/i', '', $element['Name']);
			
			if (isset($post[$elementid]))
				continue;
			
			$post[$elementid] = $this->get($element['Name']);
		}
			
		return $post;
	}
	
	function getElementID($elementname = null) {
		if (!$elementname)
			return count($this->elements)-1;
		
		$elementid = null;
		
		foreach($this->elements as $elementnum => $element) {
			if (!isset($element['Name']))
				continue;
			
			if ($element['Name'] == $elementname || 
				preg_replace('/\[.*\]/', '', $element['Name']) == $elementname) 
			{
				$elementid = $elementnum;
				break;
			}
		}
		
		return $elementid;
	}
			
	function updateElement($title, $name, $type = FORM_INPUT_TYPE_TEXT, 
				$required = false, $value = null, $elementid = null) 
	{
		if (isset($elementid) && !isset($this->elements[$elementid]))
			return false;
		
		if (!isset($elementid)) {
			if (isset($this->elements) && is_array($this->elements))
				$elementid = count($this->elements);
			else
				$elementid = 0;
		}
		
		if ($type == FORM_INPUT_TYPE_VERIFICATION_CODE) {
			if ($GLOBALS['USER']->loginok && !$this->preview)
				return false;
			
			$this->elements[$elementid]['Title'] = 
				"<b>".__($title, $this->textsDomain)."</b>";
					
			$this->elements[$elementid]['AdditionalTitle'] =
				"<div class='comment'>" .
					__("Enter the code shown on the right") .
				"<p>&nbsp;</p></div>";
				
			$this->elements[$elementid]['Name'] = "scimagecode";
			$this->elements[$elementid]['EntryID'] = "scimagecode";				
			$this->elements[$elementid]['Type'] = $type;
			$this->elements[$elementid]['Required'] = true;
			$this->elements[$elementid]['ValueType'] = FORM_VALUE_TYPE_TEXT;
			$this->elements[$elementid]['Attributes'] = '';
			$this->elements[$elementid]['Value'] = $this->get($elementid);
					
			$this->elements[$elementid]['AdditionalPreText'] = 
				"<div class='security-image ".$this->id."-scimage'>" .
					"<img src='".url::uri().
						"&amp;request=security&amp;scimage=1&amp;ajax=1' " .
						(JCORE_VERSION < '0.6'?
							"border='2' ":
							null) .
						"alt='Security Image' />" .
					"<a class='reload-link' href='javascript://' " .
						"onclick=\"jQuery('.".$this->id."-scimage img').attr('src', '".
							url::uri().
							"&amp;request=security&amp;scimage=1&amp;ajax=1'+Math.random());\">".
						__("Reload").
					"</a>" .
				"</div>";
			
			return $elementid;
		}
		
		$this->elements[$elementid]['Title'] = $title;
		$this->elements[$elementid]['Name'] = $name;
		$this->elements[$elementid]['EntryID'] = 
			preg_replace('/([^a-z^0-9^_^-]*)/i', '', $name);
		$this->elements[$elementid]['Type'] = $type;
		$this->elements[$elementid]['Required'] = $required;
		$this->elements[$elementid]['OriginalValue'] = $value;
		$this->elements[$elementid]['Attributes'] = '';
		$this->elements[$elementid]['ValueType'] = FORM_VALUE_TYPE_TEXT;
		
		if (JCORE_VERSION >= '0.6') {
			if ($type == FORM_INPUT_TYPE_EMAIL)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. user@domain.com");
			elseif ($type == FORM_INPUT_TYPE_TIMESTAMP)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. 2010-07-21 21:00:00");
			elseif ($type == FORM_INPUT_TYPE_DATE)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. 2010-07-21");
			elseif ($type == FORM_INPUT_TYPE_TIME)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. 21:00:00");
			elseif ($type == FORM_INPUT_TYPE_COLOR)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. #ff9933");
			elseif ($type == FORM_INPUT_TYPE_URL)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. http://domain.com");
			elseif ($type == FORM_INPUT_TYPE_TEL)
				$this->elements[$elementid]['TooltipText'] = 
					__("e.g. +1 (202) 555-1234");
		}
		
		if ($type == FORM_INPUT_TYPE_FILE)
			$this->elements[$elementid]['ValueType'] = FORM_VALUE_TYPE_FILE;
		
		$submittedvalue = null;
		
		if (isset($GLOBALS['_'.strtoupper($this->method)][$name]))
			$submittedvalue = $GLOBALS['_'.strtoupper($this->method)][$name];
		
		if (preg_match('/\[(.*)\]/', $name, $matches) &&
			isset($GLOBALS['_'.strtoupper($this->method)][preg_replace('/\[.*\]/', '', $name)]) && 
			is_array($GLOBALS['_'.strtoupper($this->method)][preg_replace('/\[.*\]/', '', $name)]))
		{
			$submittedvalue = 
				$GLOBALS['_'.strtoupper($this->method)]
					[preg_replace('/\[.*\]/', '', $name)];
			
			if (isset($matches[1])) {
				$arraykeys = explode('][', $matches[1]);
				foreach($arraykeys as $arraykey) {
					if (isset($submittedvalue[$arraykey]))
						$submittedvalue = $submittedvalue[$arraykey];
					else
						$submittedvalue = null;
				}
			}
		}
		
		if ($type == FORM_INPUT_TYPE_CHECKBOX || 
			$type == FORM_INPUT_TYPE_RADIO) 
		{
			$this->elements[$elementid]['Value'] = $submittedvalue;
			return $elementid;
		}
		
		$this->elements[$elementid]['Value'] = 
			(isset($submittedvalue)?
				$submittedvalue:
				$value);
		
		return $elementid;
	}
	
	function getFile($elementname) {
		if (!$elementname)
			return false;
		
		$elementid = $this->getElementID($elementname);
		
		if (!isset($elementid))
			return false;
		
 		$fileid = preg_replace('/\[.*?\]/', '', $elementname);
 		$filearrayid = null;
 		
 		preg_match('/\[(.*?)\]/', $elementname, $matches);
 		
 		if (isset($matches[1]))
	 		$filearrayid = $matches[1];
 		
 		if (isset($filearrayid))
			$file = $_FILES[$fileid]['tmp_name'][$filearrayid];
 		else
			$file = $_FILES[$fileid]['tmp_name'];
		
		if ($file)
			return $file;
		
		return $this->get($elementname);
	}
	
	function get($elementname) {
		if (!isset($elementname))
			return false;
		
		if (is_numeric($elementname)) {
			$elementid = $elementname;
			$elementname = $this->elements[$elementid]['Name'];
		} else {
			$elementid = $this->getElementID($elementname);
		}
		
		if (!isset($elementid))
			return false;
		
		if (strstr($elementname, '['))	
			$elementname = preg_replace('/\[.*\]/', '', $elementname);
		
		$value = null;
		$file = null;
		
		if (isset($GLOBALS['_'.strtoupper($this->method)][$elementname]))
			$value = $GLOBALS['_'.strtoupper($this->method)][$elementname];
			
		if (isset($GLOBALS['_FILES'][$elementname]['name']))
			$file = $GLOBALS['_FILES'][$elementname]['name'];
			
		if (!isset($value) && !isset($file))
			return null;
		
		switch ($this->elements[$elementid]['ValueType']) {
			case FORM_VALUE_TYPE_FILE:
				return trim(strip_tags($value?
							$value:
							$file));
		
			case FORM_VALUE_TYPE_INT:
			case FORM_VALUE_TYPE_BOOL:
				return (strlen($value)?
							(int)$value:
							null);
		
			case FORM_VALUE_TYPE_FLOAT:
				return form::parseFloat($value);
		
			case FORM_VALUE_TYPE_ARRAY:
				return form::parseArray($value);
			
			case FORM_VALUE_TYPE_TIMESTAMP:
				return 
					($value?
						date('Y-m-d H:i:s', 
							strtotime($value)):
						null);
			
			case FORM_VALUE_TYPE_DATE:
				return 
					($value?
						date('Y-m-d', 
							strtotime($value)):
						null);
			
			case FORM_VALUE_TYPE_HTML:
				return $value;
		
			case FORM_VALUE_TYPE_URL:
				return url::fix($value);
		
			case FORM_VALUE_TYPE_LIMITED_STRING:
				return trim(preg_replace('/[^a-zA-Z0-9\@\.\_\-]/', '',
							strip_tags($value)));
		
			case FORM_VALUE_TYPE_STRING:
			case FORM_VALUE_TYPE_TEXT:
			
			default:
				return form::parseString($value);
		}
	}
	
	function set($element, $value) {
		$GLOBALS['_'.strtoupper($this->method)][$element] = $value;
	}
	
	function insert($insertto, $title, $name, $type = FORM_INPUT_TYPE_TEXT, 
				$required = false, $value = null, $inserttype = FORM_INSERT_AFTER) 
	{
		$inserttoid = $this->getElementID($insertto);
		
		if (!$insertto)
			return false;
		
		if ($inserttype == FORM_INSERT_AFTER)
			$inserttoid++;
		
		array_splice($this->elements, $inserttoid, count($this->elements), 
			array_merge(array($this->emptyElement), array_slice($this->elements, $inserttoid)));
		
		return $this->updateElement(
			$title, $name, $type, $required, $value, $inserttoid); 
	}
	
	function add($title, $name, $type = FORM_INPUT_TYPE_TEXT, 
				$required = false, $value = null) 
	{
		return $this->updateElement(
			$title, $name, $type, $required, $value); 
	}
	
	function edit($elementname, $title, $name, $type = FORM_INPUT_TYPE_TEXT, 
				$required = false, $value = null) 
	{
		$elementid = $this->getElementID($elementname);
		
		if (!isset($elementid))
			return false;
		
		return $this->updateElement(
			$title, $name, $type, $required, $value, $elementid); 
	}
	
	function delete($elementname) 
	{
		$elementid = $this->getElementID($elementname);
		
		if (!isset($elementid))
			return false;
		
		array_splice($this->elements, $elementid, 1);
		return true;
	}
	
	static function parseFloat($floatString){
		return preg_replace('/[^0-9\.]/', '', $floatString);
	}
	
	static function parseArray($array) {
		if (!is_array($array))
			return array();
		
		$strippedarray = array();
		
		foreach($array as $key => $value)
			$strippedarray[$key] = trim(strip_tags($value));
			
		return $strippedarray;
	}
	
	static function parseString($content) {
		if (!$content)
			return null;
		
		$content = (string)$content;
		
		$content = strip_tags($content, 
			'<a><b><i><u><span><br><hr><em><blockquote><code>');
		
		$content = preg_replace(
			'/<(\/?blockquote|code|span|br|hr|em|b|i|u).*?( ?\/?)>/i', 
			'<\1\2>', $content);
		
		$content = preg_replace(
			'/<(\/?a).*?(( href=(\'|")(ht|f)tps?:\/\/.*?(\'|"))| ?\/?)>/i', 
			'<\1\3>', $content);
		
		return trim($content);
	}

	static function type2Text($type) {
		if (!$type)
			return;
		
		switch($type) {
			case FORM_INPUT_TYPE_TEXT:
				return 'Text';
			case FORM_INPUT_TYPE_EMAIL:
				return 'Email';
			case FORM_INPUT_TYPE_CHECKBOX:
				return 'Checkbox';
			case FORM_INPUT_TYPE_RADIO:
				return 'Radio';
			case FORM_INPUT_TYPE_SELECT:
				return 'Select';
			case FORM_INPUT_TYPE_MULTISELECT:
				return 'Multi Select';
			case FORM_INPUT_TYPE_TEXTAREA:
				return 'Textarea';
			case FORM_INPUT_TYPE_EDITOR:
				return 'Text Editor';
			case FORM_INPUT_TYPE_CODE_EDITOR:
				return 'Code Editor';
			case FORM_INPUT_TYPE_HIDDEN:
				return 'Hidden';
			case FORM_INPUT_TYPE_REVIEW:
				return 'Review';
			case FORM_INPUT_TYPE_PASSWORD:
				return 'Password';
			case FORM_INPUT_TYPE_CONFIRM:
				return 'Confirm Prev Field';
			case FORM_INPUT_TYPE_TIMESTAMP:
				return 'Date Time';
			case FORM_INPUT_TYPE_DATE:
				return 'Date';
			case FORM_INPUT_TYPE_TIME:
				return 'Time';
			case FORM_STATIC_TEXT:
				return 'Static Text';
			case FORM_INPUT_TYPE_SUBMIT:
				return 'Button Submit';
			case FORM_INPUT_TYPE_RESET:
				return 'Button Reset';
			case FORM_INPUT_TYPE_BUTTON:
				return 'Button';
			case FORM_INPUT_TYPE_VERIFICATION_CODE:
				return 'Verification code';
			case FORM_INPUT_TYPE_FILE:
				return 'File';
			case FORM_OPEN_FRAME_CONTAINER:
				return 'Open Form Area';
			case FORM_CLOSE_FRAME_CONTAINER:
				return 'Close Form Area';
			case FORM_INPUT_TYPE_COLOR:
				return 'Color';
			case FORM_INPUT_TYPE_SEARCH:
				return 'Search';
			case FORM_INPUT_TYPE_TEL:
				return 'Telephone';
			case FORM_INPUT_TYPE_URL:
				return 'URL';
			case FORM_INPUT_TYPE_RANGE:
				return 'Range';
			case FORM_INPUT_TYPE_NUMBER:
				return 'Number';
			default:
				return 'Undefined!';
		}
	}
	
	static function valueType2Text($type) {
		if (!$type)
			return;
		
		switch($type) {
			case FORM_VALUE_TYPE_STRING:
				return 'String';
			case FORM_VALUE_TYPE_INT:
				return 'Int';
			case FORM_VALUE_TYPE_FLOAT:
				return 'Float';
			case FORM_VALUE_TYPE_ARRAY:
				return 'Array';
			case FORM_VALUE_TYPE_TIMESTAMP:
				return 'TimeStamp';
			case FORM_VALUE_TYPE_DATE:
				return 'Date';
			case FORM_VALUE_TYPE_HTML:
				return 'HTML';
			case FORM_VALUE_TYPE_URL:
				return 'URL';
			case FORM_VALUE_TYPE_LIMITED_STRING:
				return 'LimitedString';
			case FORM_VALUE_TYPE_TEXT:
				return 'Text';
			case FORM_VALUE_TYPE_BOOL:
				return 'Boolean';
			default:
				return 'Undefined!';
		}
	}
	
	static function fcState($name, $state = false) {
		$name = preg_replace('/^fc/', '', $name);
		
		if (!$name)
			if (!$state)
				return null;
			else
				return ' expanded';
		
		if ($state && (!isset($_COOKIE['fcstates']) || 
			!in_array($name, explode('|', $_COOKIE['fcstates']))) ||
			(!$state && isset($_COOKIE['fcstates']) && 
			in_array($name, explode('|', $_COOKIE['fcstates']))))
			return ' expanded';
		
		return null;
	}
	
	function addSubmitButtons() {
		form::add(
			__('Submit'),
			$this->id.'submit',
			FORM_INPUT_TYPE_SUBMIT);
		
		form::add(
			__('Reset'),
			$this->id.'reset',
			FORM_INPUT_TYPE_RESET);
	}
	
	function setAttributes($elementname, $value = null) {
		return $this->setElementKey('Attributes', $elementname, $value);
	}
	
	function setPlaceholderText($elementname, $value = null) {
		return $this->setElementKey('PlaceholderText', $elementname, $value);
	}
	
	function setTooltipText($elementname, $value = null) {
		return $this->setElementKey('TooltipText', $elementname, $value);
	}
	
	function setAdditionalTitle($elementname, $value = null) {
		return $this->setElementKey('AdditionalTitle', $elementname, $value);
	}
	
	function setAdditionalText($elementname, $value = null) {
		return $this->setElementKey('AdditionalText', $elementname, $value);
	}
	
	function setAdditionalPreText($elementname, $value = null) {
		return $this->setElementKey('AdditionalPreText', $elementname, $value);
	}
	
	function setAutoFocus($elementname, $value = null) {
		return $this->setElementKey('AutoFocus', $elementname, $value);
	}
	
	function addAttributes($elementname, $value = null) {
		return $this->setElementKey('Attributes', $elementname, $value, FORM_ELEMENT_ADD);
	}
	
	function addAdditionalTitle($elementname, $value = null) {
		return $this->setElementKey('AdditionalTitle', $elementname, $value, FORM_ELEMENT_ADD);
	}
	
	function addAdditionalText($elementname, $value = null) {
		return $this->setElementKey('AdditionalText', $elementname, $value, FORM_ELEMENT_ADD);
	}
	
	function addAdditionalPreText($elementname, $value = null) {
		return $this->setElementKey('AdditionalPreText', $elementname, $value, FORM_ELEMENT_ADD);
	}
	
	function addValue($elementname, $value = null, $valuetext = null) {
		if (isset($valuetext)) {
			$value = array(
				'Value' => $value,
				'ValueText' => $valuetext);
		} else {
			$elementname = array(
				'Value' => $elementname,
				'ValueText' => $value);
			$value = null;
		}
		
		return $this->setElementKey('Values', $elementname, $value, FORM_ELEMENT_ARRAY);
	}
	
	function disableValues($elementname, $values = null) {
		return $this->setElementKey('DisabledValues', $elementname, $values);
	}
	
	function setStyle($elementname, $value = null) {
		if (isset($value))
			$value = " style='".$value."'";
		else
			$elementname = " style='".$elementname."'";
		
		return $this->setElementKey('Attributes', $elementname, $value, FORM_ELEMENT_ADD);
	}
	
	function setValue($elementname, $value = null) {
		if (isset($value))
			$this->set($elementname, $value);
		
		return $this->setElementKey('Value', $elementname, $value);
	}
	
	function setValues($values = array()) {
		if (!$values || !is_array($values) || !count($values))
			return false;
		
		foreach($this->elements as $key => $element) {
			if (!isset($values[$element['Name']]))
				continue;
			
			if ($element['ValueType'] == FORM_VALUE_TYPE_ARRAY)
				$value = explode('|', $values[$element['Name']]);
			else
				$value = $values[$element['Name']];
		
			$this->setValue($element['Name'], $value);
		}
		
		return true;
	}
	
	function setValueType($elementname, $type = null) {
		return $this->setElementKey('ValueType', $elementname, $type);
	}
	
	function setElementKey($key, $elementname, $value = null, $method = null) {
		if (!count($this->elements))
			return false;
			
		if (!$key)
			return false;
			
		if (!isset($elementname))
			return false;
		
		if (isset($value)) {	
			$elementid = $this->getElementID($elementname);
			
			if (!isset($elementid))
				return false;
			
			$elementname = $value;
		} else {
			$elementid = $this->getElementID();
		}
		
		if (!isset($this->elements[$elementid][$key]))
			$this->elements[$elementid][$key] = null;
		
		if ($method == FORM_ELEMENT_ARRAY) {
			$this->elements[$elementid][$key][] = $elementname;
			return true;
		}
		
		if ($method == FORM_ELEMENT_ADD) {
			$this->elements[$elementid][$key] .= $elementname;
			return true;
		}
		
		$this->elements[$elementid][$key] = $elementname;
		return true;
	}
	
	static function isInput($element) {
		if (!isset($element))
			return false;
		
		if (!isset($element['Type']))
			return false;
		
		if (in_array($element['Type'], array( 
				FORM_INPUT_TYPE_TEXT,
				FORM_INPUT_TYPE_EMAIL,
				FORM_INPUT_TYPE_CHECKBOX,
				FORM_INPUT_TYPE_RADIO,
				FORM_INPUT_TYPE_SELECT,
				FORM_INPUT_TYPE_TEXTAREA,
				FORM_INPUT_TYPE_HIDDEN,
				FORM_INPUT_TYPE_FILE,
				FORM_INPUT_TYPE_MULTISELECT,
				FORM_INPUT_TYPE_TIMESTAMP,
				FORM_INPUT_TYPE_DATE,
				FORM_INPUT_TYPE_TIME,
				FORM_INPUT_TYPE_EDITOR,
				FORM_INPUT_TYPE_COLOR)))
			return true;
		
		return false;
	}
	
	function verify() {
		if (!$this->submitted())
			return false;
		
		$errors = array();
		
		foreach($this->elements as $elementnum => $element) {
			$value = $this->get($elementnum);
			$this->elements[$elementnum]['VerifyResult'] = 0;
			
			if (!$element['Required'] && !$value &&
				$element['Type'] != FORM_INPUT_TYPE_CONFIRM)
				continue;
			
			if ($element['Type'] == FORM_INPUT_TYPE_EMAIL &&
				!email::verify($value))
			{
				$this->elements[$elementnum]['VerifyResult'] = 1;
				$errors[] = 5;
				
			} elseif ($element['Type'] == FORM_INPUT_TYPE_PASSWORD &&
				$this->verifyPassword &&
				strlen($value) < MINIMUM_PASSWORD_LENGTH)
			{
				$this->elements[$elementnum]['VerifyResult'] = 1;
				$errors[] = 6;
				
			} elseif ($element['Type'] == FORM_INPUT_TYPE_VERIFICATION_CODE &&
				!security::verifyImageCode($value))
			{
				$this->elements[$elementnum]['VerifyResult'] = 1;
				$errors[] = 4;
				
			} elseif ($element['Type'] == FORM_INPUT_TYPE_CONFIRM &&
				isset($this->elements[($elementnum-1)]) && 
				$this->get($elementnum-1) != $value)
			{
				$this->elements[$elementnum]['VerifyResult'] = 1;
				$errors[] = 3;
				
			} elseif ($element['Type'] == FORM_INPUT_TYPE_FILE &&
				!$value) 
			{
				$this->elements[$elementnum]['VerifyResult'] = 1;
				$errors[] = 2;
				
			} elseif (!in_array($element['Type'], array(
				FORM_INPUT_TYPE_CONFIRM,
				FORM_OPEN_FRAME_CONTAINER,
				FORM_CLOSE_FRAME_CONTAINER,
				FORM_STATIC_TEXT)) && 
				!$value) 
			{
				$this->elements[$elementnum]['VerifyResult'] = 1;
				$errors[] = 1;
			}
		}
		
		$error = null;
		
		if (in_array(1, $errors))
			$error .=
				(JCORE_VERSION >= '0.6'?
					__("Fields marked with an asterisk (*) are required."): 
					__("Field(s) marked with an asterisk (*) is/are required.")) .
				" ";
		
		if (in_array(2, $errors))
			$error .= 
				__("No file selected to upload.")." ";
		
		if (in_array(3, $errors))
			$error .= 
				__("Some fields do not match! Please make sure to enter " .
					"the same value when asked to confirm a prev field.")." ";
		
		if (in_array(4, $errors))
			$error .= 
				__("Incorrect verification code. " .
					"Please enter the code shown on the image.")." ";
		
		if (in_array(5, $errors))
			$error .= 
				__("Invalid email address. Please make sure you enter " .
					"a valid email address.")." ";
		
		if (in_array(6, $errors))
			$error .= 
				sprintf(__("The password you entered is too short. Your " .
					"password must be at least %s characters long."), 
					MINIMUM_PASSWORD_LENGTH)." ";
		
		if ($error) {
			$error .= 
				__("Please review/correct the marked fields in the form below " .
					"and try again.");
			
			tooltip::display($error, TOOLTIP_ERROR);
			return false;
		}
		
		return true;
	}
	
	function displayElements($elements) {
		if (!is_array($elements))
			return false;
		
		$requiredelements = 0;
		$totalelements = count($elements)-1;
		$submitbuttonid = 0;		
		
		foreach($elements as $elementnum => $element) {
			if (!isset($element['Type']))
				continue;
			
			if ($element['Required'])
				$requiredelements++;
			
			if ($element['Type'] == FORM_INPUT_TYPE_VERIFICATION_CODE && 
				isset($element['VerifyResult']) && !$element['VerifyResult']) 
			{
				echo 
					"<input type='hidden' name='".$element['Name']."' " .
						"value='".htmlspecialchars($element['Value'], ENT_QUOTES)."' />";
				
				continue;
			}
			 
			if (in_array($element['Type'], array(
				FORM_INPUT_TYPE_HIDDEN, 
				FORM_INPUT_TYPE_REVIEW))) 
			{
				if ($element['ValueType'] == FORM_VALUE_TYPE_ARRAY) {
					foreach($element['Value'] as $value)
						echo 
							"<input type='hidden' name='".$element['Name']."[]' " .
								"value='".htmlspecialchars($value, ENT_QUOTES)."' />";
				} else {
					echo 
						"<input type='hidden' name='".$element['Name']."' " .
							"value='".htmlspecialchars($element['Value'], ENT_QUOTES)."' />";
				}
				
				if ($element['Type'] == FORM_INPUT_TYPE_HIDDEN)
					continue;
			}
			 
			if (in_array($element['Type'], array(
				FORM_INPUT_TYPE_SUBMIT,
				FORM_INPUT_TYPE_RESET, 
				FORM_INPUT_TYPE_BUTTON)))
			{
				if (isset($elements[($elementnum-1)]) && !in_array(
					$elements[($elementnum-1)]['Type'], array(
						FORM_INPUT_TYPE_SUBMIT,
						FORM_INPUT_TYPE_RESET,
						FORM_INPUT_TYPE_BUTTON)))
					echo
						"<div class='clear-both'></div>";
				
				if (isset($element['AdditionalPreText']) && $element['AdditionalPreText'])
					echo $element['AdditionalPreText'];
				
				if ($element['Type'] == FORM_INPUT_TYPE_SUBMIT) {
					echo 
						"<input type='submit' " .
							"name='".$element['Name']."' " .
							"id='button".$element['EntryID']."' " .
							"class='button " .
							($submitbuttonid?
								"additional-":
								null) .
								"submit button-".$element['Name']."' " .
							"value='".__($element['Title'], $this->textsDomain)."' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							" /> ";
					
					if (!$submitbuttonid)
						$submitbuttonid = key($element);
				}
					
				if ($element['Type'] == FORM_INPUT_TYPE_RESET) {
					echo 
						"<input type='reset' " .
							"name='".$element['Name']."' " .
							"id='button".$element['EntryID']."' " .
							"class='button reset button-".$element['Name']."' " .
							"value='".__($element['Title'], $this->textsDomain)."' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							" /> ";
				}
					
				if ($element['Type'] == FORM_INPUT_TYPE_BUTTON) {
					echo 
						"<input type='button' " .
							"name='".$element['Name']."' " .
							"id='button".$element['EntryID']."' " .
							"class='button button-".$element['Name']."' " .
							"value='".__($element['Title'], $this->textsDomain)."' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							" /> ";
				}
				
				if (isset($element['AdditionalText']) && $element['AdditionalText'])
					echo $element['AdditionalText'];
					
				continue;
			}
			
			if ($element['Type'] == FORM_OPEN_FRAME_CONTAINER) {
				echo
				"<div class='fc" .
					($element['Name']?
						" fc-".url::genPathFromString($element['Name']):
						null) .
					form::fcState(
						'fc'.url::genPathFromString($element['Name']),
						$element['Required']) .
					"' ".$element['Attributes'].">" .
					"<a class='fc-title' ".
						($element['Name']?
							"name='fc".url::genPathFromString($element['Name'])."'":
							null) .
						">" .
						__($element['Title'], $this->textsDomain) .
					"</a>" .
					"<div class='fc-content'>";
				
				continue;
			} 
			
			if ($element['Type'] == FORM_CLOSE_FRAME_CONTAINER) {
				echo
					"</div>" .
				"</div>";
				
				continue;
			}
			
			if ($element['Type'] == FORM_INPUT_TYPE_CONFIRM) {
				if (!$elements[($elementnum-1)])
					continue;
				
				$element['Type'] = $elements[($elementnum-1)]['Type'];
			}
			
			echo
				"<div class='form-entry" .
					($element['Name'] || $element['Title']?
						" form-entry-".
						($element['Name']?
							url::genPathFromString($element['Name']):
							url::genPathFromString($element['Title'])):
						null) .
					($element['Required']?
						" form-entry-required":
						null) .
					(isset($element['VerifyResult']) && $element['VerifyResult']?
						" form-entry-error":
						null) .
					($elementnum == 0?
						" first":
						null) .
					($elementnum == $totalelements?
						" last":
						null) .
					"'>";
			
			if (in_array($element['Type'], array(
				FORM_INPUT_TYPE_TEXT,
				FORM_INPUT_TYPE_EMAIL,
				FORM_INPUT_TYPE_CHECKBOX, 
				FORM_INPUT_TYPE_RADIO,
				FORM_INPUT_TYPE_SELECT,
				FORM_INPUT_TYPE_MULTISELECT,
				FORM_INPUT_TYPE_TEXTAREA,
				FORM_INPUT_TYPE_VERIFICATION_CODE,
				FORM_INPUT_TYPE_FILE,
				FORM_INPUT_TYPE_TIMESTAMP,
				FORM_INPUT_TYPE_DATE,
				FORM_INPUT_TYPE_TIME,
				FORM_INPUT_TYPE_PASSWORD,
				FORM_INPUT_TYPE_REVIEW,
				FORM_INPUT_TYPE_COLOR,
				FORM_INPUT_TYPE_SEARCH,
				FORM_INPUT_TYPE_TEL,
				FORM_INPUT_TYPE_URL,
				FORM_INPUT_TYPE_RANGE,
				FORM_INPUT_TYPE_NUMBER)))
			{
				echo
						"<div class='form-entry-title" .
							(isset($element['VerifyResult']) && $element['VerifyResult']?
								" red":
								null) .
							"'>".
							($element['Title']?
								__($element['Title'], $this->textsDomain).
								($element['Required']?
									'*':
									null) .
								":" .
								($element['Type'] == FORM_INPUT_TYPE_FILE?
									"<br /><span class='comment'>(" .
										__("max")." ".files::humanSize(files::getUploadMaxFilesize()) .
									")</span>":
									null):
								null) .
							(isset($element['AdditionalTitle']) && $element['AdditionalTitle']?
								$element['AdditionalTitle']:
								null).
						"</div>" .
						"<div class='form-entry-content'>";
					
				if (isset($element['AdditionalPreText']) && $element['AdditionalPreText'])
					echo $element['AdditionalPreText'];
					
				if (in_array($element['Type'], array(
					FORM_INPUT_TYPE_TEXT,
					FORM_INPUT_TYPE_EMAIL,
					FORM_INPUT_TYPE_VERIFICATION_CODE,
					FORM_INPUT_TYPE_TIMESTAMP,
					FORM_INPUT_TYPE_DATE,
					FORM_INPUT_TYPE_TIME,
					FORM_INPUT_TYPE_COLOR,
					FORM_INPUT_TYPE_SEARCH,
					FORM_INPUT_TYPE_TEL,
					FORM_INPUT_TYPE_URL,
					FORM_INPUT_TYPE_RANGE,
					FORM_INPUT_TYPE_NUMBER))) 
				{
					echo 
						"<input type='";
					
					if (JCORE_VERSION >= '0.6') {
						if ($element['Type'] == FORM_INPUT_TYPE_EMAIL)
							echo "email";
						// Not using for now as date definition is a mess:
						// http://dev.w3.org/html5/markup/input.datetime.html#input.datetime
						/*elseif ($element['Type'] == FORM_INPUT_TYPE_TIMESTAMP)
							echo "datetime";*/
						elseif ($element['Type'] == FORM_INPUT_TYPE_DATE)
							echo "date";
						elseif ($element['Type'] == FORM_INPUT_TYPE_TIME)
							echo "time";
						elseif ($element['Type'] == FORM_INPUT_TYPE_COLOR)
							echo "color";
						elseif ($element['Type'] == FORM_INPUT_TYPE_SEARCH)
							echo "search";
						elseif ($element['Type'] == FORM_INPUT_TYPE_TEL)
							echo "tel";
						elseif ($element['Type'] == FORM_INPUT_TYPE_URL)
							echo "url";
						elseif ($element['Type'] == FORM_INPUT_TYPE_RANGE)
							echo "range";
						elseif ($element['Type'] == FORM_INPUT_TYPE_NUMBER)
							echo "number";
						else
							echo "text";
						
					} else {
						echo "text";
					}
					
					echo
							"' " .
							"name='".$element['Name']."' " .
							(isset($element['PlaceholderText']) &&
							 $element['PlaceholderText']?
							 	"placeholder='".htmlspecialchars($element['PlaceholderText'], ENT_QUOTES)."' ":
							 	null) .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							"id='entry".$element['EntryID']."' " .
							"class='text-entry" .
								($element['Type'] == FORM_INPUT_TYPE_TIMESTAMP ||
								 $element['Type'] == FORM_INPUT_TYPE_DATE?
								 	" calendar-input":
								 	null).
								($element['Type'] == FORM_INPUT_TYPE_TIMESTAMP?
									" timestamp":
									null).
								($element['Type'] == FORM_INPUT_TYPE_COLOR?
									" color-input":
									null).
								"' " .
							"value='".htmlspecialchars($element['Value'], ENT_QUOTES)."' " .
							(isset($element['AutoFocus']) &&
							 $element['AutoFocus']?
							 	"autofocus='autofocus' ":
							 	null) .
							$element['Attributes'] .
							" /> ";
				}
				
				if ($element['Type'] == FORM_INPUT_TYPE_PASSWORD) {
					echo 
						"<input type='password' " .
							"name='".$element['Name']."' " .
							"id='entry".$element['EntryID']."' " .
							"class='text-entry' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							// We do not want password fields to have predefined values
							//"value='".htmlspecialchars($element['Value'], ENT_QUOTES)."' " .
							$element['Attributes'] .
							" /> ";
				}
				
				if ($element['Type'] == FORM_INPUT_TYPE_CHECKBOX) {
					if (isset($element['Values']) && is_array($element['Values'])) {
						foreach($element['Values'] as $key => $value) {
							echo
								"<label>" .
								"<input type='checkbox' " .
									"name='".$element['Name']."[]' " .
									"id='entry".$element['EntryID'].$key."' " .
									"class='checkbox-entry' " .
									"value='".htmlspecialchars($value['Value'], ENT_QUOTES)."' " .
									(isset($element['TooltipText']) &&
									 $element['TooltipText']?
									 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
									 	null) .
									$element['Attributes'] .
									(is_array($element['Value']) && 
									 in_array($value['Value'], $element['Value'])?
										"checked='checked' ":
										null) .
									(isset($element['DisabledValues']) && 
									 is_array($element['DisabledValues']) && 
									 in_array($value['Value'], $element['DisabledValues'])?
										"disabled='disabled' ":
										null) .
									" /> " .
									($value['ValueText']?
											__($value['ValueText'], $this->textsDomain):
											$value['Value']).
								"</label> ";
						}
						
					} else {
						echo 
							"<input type='checkbox' " .
								"name='".$element['Name']."' " .
								"id='entry".$element['EntryID']."' " .
								"class='checkbox-entry' " .
								"value='".htmlspecialchars($element['OriginalValue'], ENT_QUOTES)."' " .
								(isset($element['TooltipText']) &&
								 $element['TooltipText']?
								 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
								 	null) .
								$element['Attributes'] .
								($element['OriginalValue'] == $element['Value']?
									"checked='checked'":
									null) .
								" /> ";
					}
				}
						
				if ($element['Type'] == FORM_INPUT_TYPE_RADIO) {
					if (isset($element['Values']) && is_array($element['Values'])) {
						foreach($element['Values'] as $key => $value) {
							echo
								"<label>" .
								"<input type='radio' " .
									"name='".$element['Name']."' " .
									"id='entry".$element['EntryID'].$key."' " .
									"class='radio-entry' " .
									"value='".htmlspecialchars($value['Value'], ENT_QUOTES)."' " .
									(isset($element['TooltipText']) &&
									 $element['TooltipText']?
									 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
									 	null) .
									$element['Attributes'] .
									($value['Value'] == $element['Value']?
										"checked='checked' ":
										null) .
									(isset($element['DisabledValues']) &&
									 is_array($element['DisabledValues']) && 
									 in_array($value['Value'], $element['DisabledValues'])?
										"disabled='disabled' ":
										null) .
									" /> " .
									(isset($value['ValueText']) && $value['ValueText']?
											__($value['ValueText'], $this->textsDomain):
											$value['Value']).
								"</label> ";
						}
						
					} else {
						echo 
							"<input type='radio' " .
								"name='".$element['Name']."' " .
								"id='entry".$element['EntryID']."' " .
								"class='radio-entry' " .
								"value='".htmlspecialchars($element['OriginalValue'], ENT_QUOTES)."' " .
								(isset($element['TooltipText']) &&
								 $element['TooltipText']?
								 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
								 	null) .
								$element['Attributes'] .
								($element['OriginalValue'] == $element['Value']?
									"checked='checked'":
									null) .
								" /> ";
					}
				}
						
				if ($element['Type'] == FORM_INPUT_TYPE_SELECT) {
					echo 
						"<select " .
							"name='".$element['Name']."' " .
							"id='entry".$element['EntryID']."' " .
							"class='select-entry' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							">";
					
					if (is_array($element['Values'])) {
						foreach($element['Values'] as $value) {
							echo
								"<option value='".$value['Value']."' " .
									($value['Value'] == $element['Value']?
										"selected='selected' ":
										null) .
									(isset($element['DisabledValues']) && 
									 is_array($element['DisabledValues']) && 
									 in_array($value['Value'], $element['DisabledValues'])?
										"disabled='disabled' ":
										null) .
									">" .
									(isset($value['ValueText']) && $value['ValueText']?
											__($value['ValueText'], $this->textsDomain):
											$value['Value']).
								"</option>";
						}
					}
					
					echo
						"</select>";
				}
					
				if ($element['Type'] == FORM_INPUT_TYPE_MULTISELECT) {
					echo 
						"<select multiple='multiple' " .
							"name='".$element['Name']."[]' " .
							"id='entry".$element['EntryID']."' " .
							"class='select-entry' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							">";
					
					if (is_array($element['Values'])) {
						foreach($element['Values'] as $value) {
							echo
								"<option value='".$value['Value']."' " .
									(is_array($element['Value']) && 
									 in_array($value['Value'], $element['Value'])?
										"selected='selected'":
										null) .
									">" .
									(isset($value['ValueText']) && $value['ValueText']?
											__($value['ValueText'], $this->textsDomain):
											$value['Value']).
								"</option>";
						}
					}
					
					echo
						"</select>";
				}
				
				if ($element['Type'] == FORM_INPUT_TYPE_TEXTAREA) {
					echo 
						"<textarea " .
							"rows='5' cols='10' " .
							"name='".$element['Name']."' " .
							"id='entry".$element['EntryID']."' " .
							"class='text-entry' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							">" .
							htmlspecialchars($element['Value']) .
						"</textarea>";
				}
				
				if ($element['Type'] == FORM_INPUT_TYPE_FILE) {
					echo 
						($element['Value']?
							"<b>".$element['Value']."</b><br />":
							null).
						"<input type='file' " .
							"name='".$element['Name']."' " .
							"id='entry".$element['EntryID']."' " .
							"class='file-entry' " .
							"value='".htmlspecialchars($element['Value'], ENT_QUOTES)."' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] . " /> ";
				}
				
				if ($element['Type'] == FORM_INPUT_TYPE_REVIEW) {
					echo "<span class='bold'>";
					
					if ($element['ValueType'] == FORM_VALUE_TYPE_ARRAY)
						echo nl2br(implode('; ', $element['Value']));
					elseif ($element['ValueType'] == FORM_VALUE_TYPE_BOOL)
						echo ($element['Value']?__("Yes"):__("No"));
					else
						echo nl2br($element['Value']);
					
					echo
						"</span>";
				}
					
				if (isset($element['AdditionalText']) && $element['AdditionalText'])
					echo $element['AdditionalText'];
				
				echo
						"</div>";
			}
					
			if ($element['Type'] == FORM_INPUT_TYPE_EDITOR) {
				echo
						"<textarea " .
							"style='width: 98%;' spellcheck='false' " .
							"rows='15' cols='10' " .
							"name='".$element['Name']."' " .
							"id='entry".$element['EntryID']."' " .
							"class='text-entry ck-editor' " .
							$element['Attributes'] .
							">" .
							htmlspecialchars($element['Value']) .
						"</textarea>";
				
				ckEditor::display("entry".$element['EntryID']);
			}
			
			if ($element['Type'] == FORM_INPUT_TYPE_CODE_EDITOR) {
					echo 
						"<textarea " .
							"style='width: 98%;'  spellcheck='false' " .
							"rows='15' cols='10' " .
							"name='".$element['Name']."' " .
							"id='entry".$element['EntryID']."' " .
							"class='text-entry code-editor' " .
							(isset($element['TooltipText']) &&
							 $element['TooltipText']?
							 	"title='".htmlspecialchars($element['TooltipText'], ENT_QUOTES)."' ":
							 	null) .
							$element['Attributes'] .
							">" .
							htmlspecialchars($element['Value']) .
						"</textarea>";
			}
			
			if ($element['Type'] == FORM_STATIC_TEXT) {
				echo $element['Title'];
			}
						
			echo
				"</div>";
		}
		
		if (!isset($this->footer) || $this->footer)
			echo
				"<div class='form-footer comment'>";
				
		$this->displayFooter($requiredelements);
		
		if (!isset($this->footer))
			$this->displayDefaultFooter($requiredelements);
		
		if (!isset($this->footer) || $this->footer)
			echo
				"</div>";
	}
	
	function displayFooter($requiredelements = 0) {
		echo
			$this->footer;
	}
	
	function displayDefaultFooter($requiredelements = 0) {
		if (!$requiredelements)
			return;
		
		if (JCORE_VERSION >= '0.6') {
			echo
				"<p>";
			
			if ($requiredelements > 1)
				echo
					__("Fields marked with an asterisk (*) are required.");
			else
				echo
					__("Field marked with an asterisk (*) is required.");
			
			echo
				"</p>";
			
			return;
		}
		
		echo
			"<br />" .
			__("Field(s) marked with an asterisk (*) is/are required.");
	}
	
	function display($formdesign = true) {
		if (!is_array($this->elements))
			return false;
		
		echo 
			"<div id='".$this->id."form'" .
				" class='" .
				(JCORE_VERSION >= '0.6'?
					"form ":
					null) .
				"rounded-corners'>";
			
		if ($formdesign)
			echo
				"<div class='form-title rounded-corners-top'>" .
					__($this->title, $this->textsDomain).
				"</div>" .
				"<div class='" .
					(JCORE_VERSION >= '0.6'?
						"form-content":
						"form") .
					" rounded-corners-bottom'>";
				
		echo
				"<form action='".$this->action."' method='".$this->method."' " .
					"enctype='multipart/form-data' ".$this->attributes.">";
		
		$this->displayElements($this->elements);
		
		echo
				"</form>";
				
		if ($formdesign)
			echo
				"</div>"; //#form
		
		echo
			"</div>"; //#formid
	}
}

/***************************************************************************
 *            tooltip.class.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 ****************************************************************************/
 
define('TOOLTIP_DEFAULT', '');
define('TOOLTIP_SUCCESS', 'success');
define('TOOLTIP_ERROR', 'error');
define('TOOLTIP_NOTIFICATION', 'notification');
 
class tooltip {
	static $cache = "";
	static $caching = false;
	
	static function caching($onoff) {
		tooltip::$caching = $onoff;
	}
	
	static function construct($message, $type = null) {
		if (defined($type))
			$type = constant($type);
		
		return 
			"<div class='tooltip ".$type." rounded-corners'>" .
				"<span>" .
				$message .
				"</span>" .
			"</div>";
	}
	
	static function display($message = null, $type = null) {
		if (!$message) {
			echo tooltip::$cache;
			tooltip::$cache = '';
			
			return;
		}
		
		if (tooltip::$caching)
			tooltip::$cache .= tooltip::construct($message, $type);
		else 
			echo tooltip::construct($message, $type);
	}
}

/***************************************************************************
 *            sql.class.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 ****************************************************************************/

class sql {
	static $link = null;
	static $lastQuery = null;
	
	static function setTimeZone() {
		sql::run("SET `time_zone` = '".
			(phpversion() < '5.1.3'?
				preg_replace('/(..)$/', ':\1', date('O')):
				date('P')).
			"'");
	}
	
	static function mtimetosec($current, $start) {
		$exp_current = explode(" ", $current);
		$exp_start = explode(" ", $start);
		
		if (!isset($exp_current[1]))
			$exp_current[1] = 0;
		
		if (!isset($exp_start[1]))
			$exp_start[1] = 0;
		
		$msec = $exp_current[0] - $exp_start[0];
		$sec = $exp_current[1] - $exp_start[1];
		
		return number_format($sec+$msec, 5);
	}

	static function connect($host, $user, $pass) {
		sql::$link = @mysql_connect($host, $user, $pass);
		return sql::$link;
	}

	static function selectDB($db) {
		if (!sql::$link)
			return false;
		
		return @mysql_select_db($db, sql::$link); 
	}

	static function login() {
		sql::$link = sql::connect(SQL_HOST, SQL_USER, SQL_PASS);
		
		if (!sql::$link || !sql::selectDB(SQL_DATABASE))
			exit(
				"<html>" .
				"<head>" .
				"<title>Site Under Maintenance</title>" .
				"</head>" .
				"<body>" .
				"<div style='margin: 100px auto; border: solid 1px #CCCCCC; " .
					"width: 500px; padding: 10px; text-align: center; " .
					"font-family: Arial, Helvetica, Sans-serif;'>" .
					"<h1>" .
						__("Site Temporary Unavailable") .
					"</h1>" .
					"<p>" .
						sprintf(
							__("Could not establish a connection to the database.<br />" .
							"We are sorry for the inconvenience and " .
							"appreciate your patience during this time. " .
							"Please wait for a few minutes and <a href='%s'>" .
							"try again</a>."), 
							$_SERVER['REQUEST_URI']) .
					"</p>" .
				"</div>" .
				"</body>" .
				"</html>");
	
    	// I have no idea why this is needed but unless I set the character set
    	// manually all my Hungarian/Romanian characters are messed up.
    	
		$character_set = sql::fetch(sql::run(
			" SHOW VARIABLES LIKE 'character_set_database'"));
		
		if ($character_set)
	  		sql::run("SET CHARACTER SET '".$character_set['Value']."'");
	}
	
	static function prefixTable($query) {
		if (!defined('SQL_PREFIX') || !SQL_PREFIX)
			return preg_replace(
						'/`{([a-zA-Z0-9\_\-]*?)}`/', 
						'`\1`', 
						$query);
			
		return preg_replace(
					'/`{([a-zA-Z0-9\_\-]*?)}`/', 
					'`'.SQL_PREFIX.'_\1`', 
					$query);
	}
	
	static function regexp2txt($string) {
		$string = preg_replace('/^\^|\$$/', '', $string);
		$string = str_replace('.*', '*', $string);
		$string = str_replace('$|^', ', ', $string);
		return $string;
	}
	
	static function txt2regexp($string) {
		$string = '^'.$string.'$';
		$string = preg_replace('/, ?/', ', ', $string);
		$string = str_replace('*', '.*', $string);
		$string = str_replace(', ', '$|^', $string);
		return $string;
	}
	
	static function run($query, $debug = false) {
		if (!trim($query))
			return false;
		
		sql::$lastQuery = $query;
		
		if ($debug)
			$time_start = microtime(true);
	
		if (!sql::$link) 
			sql::login();
			
		$query = sql::prefixTable($query);
	    $result = @mysql_query($query, sql::$link);
	    
	    if (!$result) {
			sql::displayError();
	    	return false;
	    }
		
		if (preg_match('/^ *?INSERT/i', $query)) 
			$result = mysql_insert_id(sql::$link);
		
		if ($debug) {
			$time = sql::mtimetosec(microtime(true), $time_start);
			
			tooltip::display(
				"Query took: $time seconds<br />" .
				"MySQL error: ". mysql_error(sql::$link) . "<br /><br />" .
				$query,
				TOOLTIP_NOTIFICATION);
		}
		
		return $result;
	}
	
	static function fetch($result) {
	    if (!$result)
	    	return false;
		
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}
	
	static function seek(&$rows, $to = 0) {
	    if (!$rows)
	    	return false;
		
		return mysql_data_seek($rows, $to);
	}
	
	static function rows($result) {
	    if (!$result)
	    	return false;
		
		return mysql_num_rows($result);
	}
	
	static function affected() {
		return mysql_affected_rows(sql::$link);
	}

	static function escape($string) {
		return mysql_real_escape_string($string, sql::$link);
	}
	
	static function count($tblkey = '`ID`', $debug = false) {
		if ($debug) {
			$time_start = microtime(true);
		}
		
		if (sql::$lastQuery) {
			$query = sql::$lastQuery;
			preg_match("/FROM (.*?) (GROUP|ORDER|LIMIT)/is", $query, $found);
			
			if (stristr($tblkey, 'SELECT')) {
				$query = 
					$tblkey .
					" LIMIT 1";
			} else {
				$query = 
					" SELECT COUNT(".$tblkey.") AS `Rows` FROM " .
					$found[1] .
					" LIMIT 1";
			}
			
			$query = sql::prefixTable($query);
			$row = sql::fetch(sql::run($query));
			
		} else {
			$query = "SELECT FOUND_ROWS() AS `Rows`";
			
			$row = sql::fetch(sql::run($query));
		}
		
		if ($debug) {
			$time = sql::mtimetosec(microtime(true), $time_start);
		}
 		
		if ($debug) {
			tooltip::display(
				"Query took: $time seconds<br />" .
				"MySQL error: ". mysql_error(sql::$link) . "<br /><br />" .
				$query,
				TOOLTIP_NOTIFICATION);
		}
	
		return $row['Rows'];	
	}
	
	static function search($search, $fields = array('Title'), $type = 'AND') {
		if (!trim($search) || !is_array($fields) || !count($fields))
			return;
			
		if (strstr($search, ','))
			$separator = ',';
		else
			$separator = ' ';
		
		$query = null;
		$keywords = explode($separator, trim($search));
		
		if (count($keywords) > 21)
			$keywords = array_slice($keywords, 0, 21);
		
		foreach($fields as $field) {
			if ($query)
				$query .= " OR";
			
			$keywordsquery = null;
			
			foreach($keywords as $keyword) {
				if ($keywordsquery)
					$keywordsquery .= " ".$type;
			
				$keywordsquery .= " `".$field."` LIKE '%".
					sql::escape(trim($keyword))."%'";
			}
			
			if ($keywordsquery)
				$query .= " (".$keywordsquery.") ";
		}
		
		if (!$query)
			return;
		
		return " AND (".$query.")";
	}
	
	static function lastQuery() {
		return sql::$lastQuery;		
	}
	
	static function error() {
		return mysql_error(sql::$link);
	}

	static function logout() {
    	return mysql_close(sql::$link);
	}

	static function link() {
		return sql::$link;
	}
	
	static function displayError() {
		$error = sql::error();
		
		if (!$error)
			return false;
		
		tooltip::display(
			__("SQL Error:"). " " .
			$error."<br />" .
			sql::$lastQuery,
			TOOLTIP_ERROR);
		
		return $error;
	}
	
	static function display($quiet = false) {
		if (!$quiet)
			echo 
				"<p>".
					sql::lastQuery()." " .
					sprintf(__("(affected rows: %s)"), sql::affected()).
				"</p>";
		
		return sql::displayError();
	}
} 
 
// $Id: class.tar.php 2 2005-11-02 18:23:29Z skalpa $
/*
    package::i.tools
 
    php-downloader    v1.0    -    www.ipunkt.biz
 
    (c)    2002 - www.ipunkt.biz (rok)
*/
 
/*
=======================================================================
Name:
    tar Class
 
Author:
    Josh Barger <joshb@npt.com>
 
Description:
    This class reads and writes Tape-Archive (TAR) Files and Gzip
    compressed TAR files, which are mainly used on UNIX systems.
    This class works on both windows AND unix systems, and does
    NOT rely on external applications!! Woohoo!
 
Usage:
    Copyright (C) 2002  Josh Barger
 
    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.
 
    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details at:
        http://www.gnu.org/copyleft/lesser.html
 
    If you use this script in your application/website, please
    send me an e-mail letting me know about it :)
 
Bugs:
    Please report any bugs you might find to my e-mail address
    at joshb@npt.com.  If you have already created a fix/patch
    for the bug, please do send it to me so I can incorporate it into my release.
 
Version History:
    1.0    04/10/2002    - InitialRelease
 
    2.0    04/11/2002    - Merged both tarReader and tarWriter
                  classes into one
                - Added support for gzipped tar files
                  Remember to name for .tar.gz or .tgz
                  if you use gzip compression!
                  :: THIS REQUIRES ZLIB EXTENSION ::
                - Added additional comments to
                  functions to help users
                - Added ability to remove files and
                  directories from archive
    2.1    04/12/2002    - Fixed serious bug in generating tar
                - Created another example file
                - Added check to make sure ZLIB is
                  installed before running GZIP
                  compression on TAR
    2.2    05/07/2002    - Added automatic detection of Gzipped
                  tar files (Thanks go to Jidgen Falch
                  for the idea)
                - Changed "private" functions to have
                  special function names beginning with
                  two underscores
=======================================================================
XOOPS changes onokazu <webmaster@xoops.org>
 
    12/25/2002 - Added flag to addFile() function for binary files
 
=======================================================================
*/
 
/**
 * tar Class
 * 
 * This class reads and writes Tape-Archive (TAR) Files and Gzip
 * compressed TAR files, which are mainly used on UNIX systems.
 * This class works on both windows AND unix systems, and does
 * NOT rely on external applications!! Woohoo!
 * 
 * @author    Josh Barger <joshb@npt.com>
 * @copyright    Copyright (C) 2002  Josh Barger
 * 
 * @package     kernel
 * @subpackage  core
 */
class tar
{
    /**#@+
     * Unprocessed Archive Information
     */
    var $filename;
    var $isGzipped;
    var $tar_file;
    /**#@-*/
 
    /**#@+
     * Processed Archive Information
     */
    var $files;
    var $directories;
    var $numFiles;
    var $numDirectories;
    /**#@-*/
 
 
    /**
     * Class Constructor -- Does nothing...
     */
    function tar()
    {
        return true;
    }
 
    /**
     * Computes the unsigned Checksum of a file's header
     * to try to ensure valid file
     * 
     * @param    string  $bytestring 
     * 
     * @access    private
     */
    function __computeUnsignedChecksum($bytestring)
    {
        $unsigned_chksum = '';
        for($i=0; $i<512; $i++)
            $unsigned_chksum += ord($bytestring[$i]);
        for($i=0; $i<8; $i++)
            $unsigned_chksum -= ord($bytestring[148 + $i]);
        $unsigned_chksum += ord(" ") * 8;
 
        return $unsigned_chksum;
    }
 
 
    /**
     * Converts a NULL padded string to a non-NULL padded string
     * 
     * @param   string  $string 
     * 
     * @return  string 
     * 
     * @access    private
     ***/
    function __parseNullPaddedString($string)
    {
        $position = strpos($string,chr(0));
        return substr($string,0,$position);
    }
 
    /**
     * This function parses the current TAR file
     * 
     * @return  bool    always TRUE
     * 
     * @access    private
     ***/
    function __parseTar()
    {
        // Read Files from archive
        $tar_length = strlen($this->tar_file);
        $main_offset = 0;
        $this->numFiles = 0;
        while ( $main_offset < $tar_length ) {
            // If we read a block of 512 nulls, we are at the end of the archive
            if(substr($this->tar_file,$main_offset,512) == str_repeat(chr(0),512))
                break;
 
            // Parse file name
            $file_name        = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset,100));
 
            // Parse the file mode
            $file_mode        = substr($this->tar_file,$main_offset + 100,8);
 
            // Parse the file user ID
            $file_uid        = octdec(substr($this->tar_file,$main_offset + 108,8));
 
            // Parse the file group ID
            $file_gid        = octdec(substr($this->tar_file,$main_offset + 116,8));
 
            // Parse the file size
            $file_size        = octdec(substr($this->tar_file,$main_offset + 124,12));
 
            // Parse the file update time - unix timestamp format
            $file_time        = octdec(substr($this->tar_file,$main_offset + 136,12));
 
            // Parse Checksum
            $file_chksum        = octdec(substr($this->tar_file,$main_offset + 148,6));
 
            // Parse user name
            $file_uname        = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset + 265,32));
 
            // Parse Group name
            $file_gname        = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset + 297,32));
 
            // Make sure our file is valid
            if($this->__computeUnsignedChecksum(substr($this->tar_file,$main_offset,512)) != $file_chksum)
                return false;
 
            // Parse File Contents
            $file_contents        = substr($this->tar_file,$main_offset + 512,$file_size);
 
            /*    ### Unused Header Information ###
                $activeFile["typeflag"]        = substr($this->tar_file,$main_offset + 156,1);
                $activeFile["linkname"]        = substr($this->tar_file,$main_offset + 157,100);
                $activeFile["magic"]        = substr($this->tar_file,$main_offset + 257,6);
                $activeFile["version"]        = substr($this->tar_file,$main_offset + 263,2);
                $activeFile["devmajor"]        = substr($this->tar_file,$main_offset + 329,8);
                $activeFile["devminor"]        = substr($this->tar_file,$main_offset + 337,8);
                $activeFile["prefix"]        = substr($this->tar_file,$main_offset + 345,155);
                $activeFile["endheader"]    = substr($this->tar_file,$main_offset + 500,12);
            */
            
            $file_type = substr($this->tar_file,$main_offset + 156,1);
            
            if ($file_type == 5) {
                // Increment number of directories
                $this->numDirectories++;
 
                // Create a new directory in our array
                $activeDir = &$this->directories[];
 
                // Assign values
                $activeDir["name"]        = $file_name;
                $activeDir["mode"]        = $file_mode;
                $activeDir["time"]        = $file_time;
                $activeDir["user_id"]        = $file_uid;
                $activeDir["group_id"]        = $file_gid;
                $activeDir["user_name"]        = $file_uname;
                $activeDir["group_name"]    = $file_gname;
                $activeDir["checksum"]        = $file_chksum;
            	
            } else {
                // Increment number of files
                $this->numFiles++;
 
                // Create us a new file in our array
                $activeFile = &$this->files[];
 
                // Asign Values
                $activeFile["name"]        = $file_name;
                $activeFile["mode"]        = $file_mode;
                $activeFile["size"]        = $file_size;
                $activeFile["time"]        = $file_time;
                $activeFile["user_id"]        = $file_uid;
                $activeFile["group_id"]        = $file_gid;
                $activeFile["user_name"]    = $file_uname;
                $activeFile["group_name"]    = $file_gname;
                $activeFile["checksum"]        = $file_chksum;
                $activeFile["file"]        = $file_contents;
            }
 
            // Move our offset the number of blocks we have processed
            $main_offset += 512 + (ceil($file_size / 512) * 512);
        }
 
        return true;
    }
 
    /**
     * Read a non gzipped tar file in for processing.
     * 
     * @param   string  $filename   full filename
     * @return  bool    always TRUE
     * 
     * @access    private
     ***/
    function __readTar($filename='')
    {
        // Set the filename to load
        if(!$filename)
            $filename = $this->filename;
 
        // Read in the TAR file
        $fp = fopen($filename,"rb");
        $this->tar_file = fread($fp,filesize($filename));
        fclose($fp);
 
        if($this->tar_file[0] == chr(31) && $this->tar_file[1] == chr(139) && $this->tar_file[2] == chr(8)) {
            if(!function_exists("gzinflate"))
                return false;
 
            $this->isGzipped = true;
 
            $this->tar_file = gzinflate(substr($this->tar_file,10,-4));
        }
 
        // Parse the TAR file
        $this->__parseTar();
 
        return true;
    }
 
    /**
     * Generates a TAR file from the processed data
     * 
     * @return  bool    always TRUE
     * 
     * @access    private
     ***/
    function __generateTAR()
    {
        // Clear any data currently in $this->tar_file
        unset($this->tar_file);
 
        // Generate Records for each directory, if we have directories
        if($this->numDirectories > 0) {
            foreach($this->directories as $key => $information) {
                $header = null;
 
                // Generate tar header for this directory
                // Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
                $header .= str_pad($information["name"],100,chr(0));
                $header .= str_pad(decoct($information["mode"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["user_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["group_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct(0),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["time"]),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_repeat(" ",8);
                $header .= "5";
                $header .= str_repeat(chr(0),100);
                $header .= str_pad("ustar",6,chr(32));
                $header .= chr(32) . chr(0);
                $header .= str_pad("",32,chr(0));
                $header .= str_pad("",32,chr(0));
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),155);
                $header .= str_repeat(chr(0),12);
 
                // Compute header checksum
                $checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)),6,"0",STR_PAD_LEFT);
                for($i=0; $i<6; $i++) {
                    $header[(148 + $i)] = substr($checksum,$i,1);
                }
                $header[154] = chr(0);
                $header[155] = chr(32);
 
                // Add new tar formatted data to tar file contents
                $this->tar_file .= $header;
            }
        }
 
        // Generate Records for each file, if we have files (We should...)
        if($this->numFiles > 0) {
            $this->tar_file = '';
            foreach($this->files as $key => $information) {
                $header = null;
 
                // Generate the TAR header for this file
                // Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
                $header = str_pad($information["name"],100,chr(0));
                $header .= str_pad(decoct($information["mode"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["user_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["group_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["size"]),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["time"]),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_repeat(" ",8);
                $header .= "0";
                $header .= str_repeat(chr(0),100);
                $header .= str_pad("ustar",6,chr(32));
                $header .= chr(32) . chr(0);
                $header .= str_pad($information["user_name"],32,chr(0));    // How do I get a file's user name from PHP?
                $header .= str_pad($information["group_name"],32,chr(0));    // How do I get a file's group name from PHP?
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),155);
                $header .= str_repeat(chr(0),12);
 
                // Compute header checksum
                $checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)),6,"0",STR_PAD_LEFT);
                for($i=0; $i<6; $i++) {
                    $header[(148 + $i)] = substr($checksum,$i,1);
                }
                $header[154] = chr(0);
                $header[155] = chr(32);
 
                // Pad file contents to byte count divisible by 512
                $file_contents = str_pad($information["file"],(ceil($information["size"] / 512) * 512),chr(0));
 
                // Add new tar formatted data to tar file contents
                $this->tar_file .= $header . $file_contents;
            }
        }
 
        // Add 512 bytes of NULLs to designate EOF
        $this->tar_file .= str_repeat(chr(0),512);
 
        return true;
    }
 
 
    /**
     * Open a TAR file
     * 
     * @param   string  $filename 
     * @return  bool 
     ***/
    function openTAR($filename)
    {
        // Clear any values from previous tar archives
        unset($this->filename);
        unset($this->isGzipped);
        unset($this->tar_file);
        unset($this->files);
        unset($this->directories);
        unset($this->numFiles);
        unset($this->numDirectories);
 
        // If the tar file doesn't exist...
        if(!file_exists($filename))
            return false;
 
        $this->filename = $filename;
 
        // Parse this file
        $this->__readTar();
 
        return true;
    }
 
    /**
     * Appends a tar file to the end of the currently opened tar file.
     * 
     * @param   string  $filename 
     * @return  bool 
     ***/
    function appendTar($filename)
    {
        // If the tar file doesn't exist...
        if(!file_exists($filename))
            return false;
 
        $this->__readTar($filename);
 
        return true;
    }
 
    /**
     * Retrieves information about a file in the current tar archive
     * 
     * @param   string  $filename 
     * @return  string  FALSE on fail
     ***/
    function getFile($filename)
    {
        if ( $this->numFiles > 0 ) {
            foreach($this->files as $key => $information) {
                if($information["name"] == $filename)
                    return $information;
            }
        }
 
        return false;
    }
 
    /**
     * Retrieves information about a directory in the current tar archive
     * 
     * @param   string  $dirname 
     * @return  string  FALSE on fail
     ***/
    function getDirectory($dirname)
    {
        if($this->numDirectories > 0) {
            foreach($this->directories as $key => $information) {
                if($information["name"] == $dirname)
                    return $information;
            }
        }
 
        return false;
    }
 
    /**
     * Check if this tar archive contains a specific file
     * 
     * @param   string  $filename 
     * @return  bool 
     ***/
    function containsFile($filename)
    {
        if ( $this->numFiles > 0 ) {
            foreach($this->files as $key => $information) {
                if($information["name"] == $filename)
                    return true;
            }
        }
        return false;
    }
 
    /**
     * Check if this tar archive contains a specific directory
     * 
     * @param   string  $dirname 
     * @return  bool 
     ***/
    function containsDirectory($dirname)
    {
        if ( $this->numDirectories > 0 ) {
            foreach ( $this->directories as $key => $information ) {
                if ( $information["name"] == $dirname ) {
                    return true;
                }
            }
        }
        return false;
    }
 
    /**
     * Add a directory to this tar archive
     * 
     * @param   string  $dirname 
     * @return  bool 
     ***/
    function addDirectory($dirname)
    {
        if ( !file_exists($dirname) ) {
            return false;
        }
 
        // Get directory information
        $file_information = stat($dirname);
 
        // Add directory to processed data
        $this->numDirectories++;
        $activeDir        = &$this->directories[];
        $activeDir["name"]    = $dirname;
        $activeDir["mode"]    = $file_information["mode"];
        $activeDir["time"]    = $file_information["time"];
        $activeDir["user_id"]    = $file_information["uid"];
        $activeDir["group_id"]    = $file_information["gid"];
        $activeDir["checksum"]    = null;
 
        return true;
    }
 
    /**
     * Add a file to the tar archive
     * 
     * @param   string  $filename 
     * @param   boolean $binary     Binary file?
     * @return  bool 
     ***/
    function addFile($filename, $binary = false)
    {
        // Make sure the file we are adding exists!
        if ( !file_exists($filename) ) {
            return false;
        }
 
        // Make sure there are no other files in the archive that have this same filename
        if ( $this->containsFile($filename) ) {
            return false;
        }
 
        // Get file information
        $file_information = stat($filename);
 
        // Read in the file's contents
        if (!$binary) {
            $fp = fopen($filename, "r");
        } else {
            $fp = fopen($filename, "rb");
        }
        $file_contents = fread($fp,filesize($filename));
        fclose($fp);
 
        // Add file to processed data
        $this->numFiles++;
        $activeFile            = &$this->files[];
        $activeFile["name"]        = $filename;
        $activeFile["mode"]        = $file_information["mode"];
        $activeFile["user_id"]        = $file_information["uid"];
        $activeFile["group_id"]        = $file_information["gid"];
        $activeFile["size"]        = $file_information["size"];
        $activeFile["time"]        = $file_information["mtime"];
        $activeFile["checksum"]        = isset($checksum) ? $checksum : '';
        $activeFile["user_name"]    = "";
        $activeFile["group_name"]    = "";
        $activeFile["file"]        = trim($file_contents);
 
        return true;
    }
 
    /**
     * Remove a file from the tar archive
     * 
     * @param   string  $filename 
     * @return  bool 
     ***/
    function removeFile($filename)
    {
        if ( $this->numFiles > 0 ) {
            foreach ( $this->files as $key => $information ) {
                if ( $information["name"] == $filename ) {
                    $this->numFiles--;
                    unset($this->files[$key]);
                    return true;
                }
            }
        }
        return false;
    }
 
    /**
     * Remove a directory from the tar archive
     * 
     * @param   string  $dirname 
     * @return  bool 
     ***/
    function removeDirectory($dirname)
    {
        if ( $this->numDirectories > 0 ) {
            foreach ( $this->directories as $key => $information ) {
                if ( $information["name"] == $dirname ) {
                    $this->numDirectories--;
                    unset($this->directories[$key]);
                    return true;
                }
            }
        }
        return false;
    }
 
    /**
     * Write the currently loaded tar archive to disk
     * 
     * @return  bool 
     ***/
    function saveTar()
    {
        if ( !$this->filename ) {
            return false;
        }
 
        // Write tar to current file using specified gzip compression
        $this->toTar($this->filename,$this->isGzipped);
 
        return true;
    }
 
    /**
     * Saves tar archive to a different file than the current file
     * 
     * @param   string  $filename 
     * @param   bool    $useGzip    Use GZ compression?
     * @return  bool 
     ***/
    function toTar($filename,$useGzip)
    {
        if ( !$filename ) {
            return false;
        }
 
        // Encode processed files into TAR file format
        $this->__generateTar();
 
        // GZ Compress the data if we need to
        if ( $useGzip ) {
            // Make sure we have gzip support
            if ( !function_exists("gzencode") ) {
                return false;
            }
 
            $file = gzencode($this->tar_file);
        } else {
            $file = $this->tar_file;
        }
 
        // Write the TAR file
        $fp = fopen($filename,"wb");
        fwrite($fp,$file);
        fclose($fp);
 
        return true;
    }
 
    /**
     * Sends tar archive to stdout
     * 
     * @param   string  $filename 
     * @param   bool    $useGzip    Use GZ compression?
     * @return  string 
     ***/
    function toTarOutput($filename,$useGzip)
    {
        if ( !$filename ) {
            return false;
        }
 
        // Encode processed files into TAR file format
        $this->__generateTar();
 
        // GZ Compress the data if we need to
        if ( $useGzip ) {
            // Make sure we have gzip support
            if ( !function_exists("gzencode") ) {
                return false;
            }
 
            $file = gzencode($this->tar_file);
        } else {
            $file = $this->tar_file;
        }
 
        return $file;
    }
}
 
/***************************************************************************
 *            installer.class.php
 *
 *  Jul 05, 07:00:00 2009
 *  Copyright  2009  Istvan Petres (aka P.I.Julius)
 *  me@pijulius.com
 ****************************************************************************/

class installer {
	var $error = 0;
	var $publicFiles;
	var $modules = array();
	var $install;
	var $installPath;
	var $installOverwrite = false;
	var $installOverwriteSQL = false;
	var $installURL;
	var $serverPath;
	var $serverURL;
	var $clientModules = array();
	var $sqlHost;
	var $sqlDB;
	var $sqlUser;
	var $sqlPassword;
	var $sqlPrefix;
	var $downloadServerID;
	var $downloadClientID;
	var $downloadInstallerID;
	var $downloadSQLID;
	var $downloadSQLArchiveID;
	var $keepPackages = false;
	var $cleanupFiles = null;
	
	function __construct() {
		$this->publicFiles = '/' .
			'^\/sitemap\.xml|' .
			'^\/rss|' .
			'^\/sitefiles|' .
			'^\/template|' .
			'^\/template\/images|' .
			'^\/template\/modules|' .
			'^\/template\/modules\/css|' .
			'^\/template\/modules\/js|' .
			'^\/template\/template\.css|' .
			'^\/template\/template\.js' .
			'/i';
		
		$this->installPath = preg_replace('/[^\/]*?$/', '', $_SERVER['SCRIPT_FILENAME']);
		$this->installURL = preg_replace('/[^\/]*?$/', '', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$this->sqlHost = 'localhost';
		$this->sqlDB = preg_replace('/\..*?$/', '', $_SERVER['HTTP_HOST']).'_DB';
		$this->sqlUser = 'root';
		
		$this->sqlTables = array(
			'ads',
			'bfprotection',
			'bfprotectionbans',
			'blocks',
			'dynamicformfields',
			'dynamicformfieldvalues',
			'dynamicforms',
			'languages',
			'menuitemmodules',
			'menuitems',
			'menus',
			'modules',
			'postattachments',
			'postcomments',
			'postcommentsratings',
			'postpictures',
			'posts',
			'ptprotectionbans',
			'rssfeeds',
			'settings',
			'userlogins',
			'userpermissions',
			'userrequests',
			'users',
			'massemails');
		
		$this->serverPath = '/var/www/html/jcore/';
		$this->serverURL = JCORE_URL;
		
		if (isset($_COOKIE['jCoreInstaller']['Modules']))
			$this->modules = $_COOKIE['jCoreInstaller']['Modules'];
		
		if (isset($_COOKIE['jCoreInstaller']['Downloads']['Server']))
			$this->downloadServerID = $_COOKIE['jCoreInstaller']['Downloads']['Server'];
		
		if (isset($_COOKIE['jCoreInstaller']['Downloads']['Client']))
			$this->downloadClientID = $_COOKIE['jCoreInstaller']['Downloads']['Client'];
		
		if (isset($_COOKIE['jCoreInstaller']['Downloads']['Installer']))
			$this->downloadInstallerID = $_COOKIE['jCoreInstaller']['Downloads']['Installer'];
			
		if (isset($_COOKIE['jCoreInstaller']['Downloads']['SQL']))
			$this->downloadSQLID = $_COOKIE['jCoreInstaller']['Downloads']['SQL'];
		
		if (isset($_COOKIE['jCoreInstaller']['Downloads']['SQLArchive']))
			$this->downloadSQLArchiveID = $_COOKIE['jCoreInstaller']['Downloads']['SQLArchive'];
	}
	
	function downloadCheckTimeOut($fp, $title) {
		$status = socket_get_status($fp);
		
		if ($status["timed_out"]) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (".__("connection timed out").")<br />');" .
				"</script>";
			
			url::flushDisplay();
			
			tooltip::display(
				__("The download for ".$title." timed out. Please " .
					"make sure you are still connected to the internet and " .
					"<a href='http://jcore.net' target='_blank'>http://jcore.net</a> " .
					"can be loaded from your browser. Please " .
					"<a href='javascript://' onclick=\"jQuery('#installerform #buttoninstall').click();\">try again</a> " .
					"and if this error keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
		
			return true;
		}
		
		return false;
	}
	
	function download($downloadid, $title, $savefile = true) {
		$downloadstatusclass = "download-".strtolower(str_replace(' ', '-', $title));
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__($title."..." .
						"<span class=\"".$downloadstatusclass."\" style=\"font-weight: bold;\">" .
							__("connecting") .
						"</span> ") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		$fp = @fsockopen(
				'jcore.net',
				80, $errno, $errstr);
				
		if (!$fp) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (".$errno.": ".$errstr.")<br />');" .
				"</script>";
			
			url::flushDisplay();
			
			tooltip::display(
				__("Couldn't start the downloading proccess. Please " .
					"make sure you are connected to the internet and " .
					"<a href='http://jcore.net' target='_blank'>http://jcore.net</a> " .
					"can be loaded from your browser. Please " .
					"<a href='javascript://' onclick=\"jQuery('#installerform #buttoninstall').click();\">try again</a> " .
					"and if this error keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
			
			return false;
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess .".$downloadstatusclass."').html('" .
						__("sending request")."');" .
			"</script>";
		
		url::flushDisplay();
		
		stream_set_timeout($fp, 10);
		
		$filename = null;
		$filesize = null;
		$header = null;
		$content = null;
		
		if ((int)$downloadid)
			$geturl = 
				"?request=modules/filesharing/filesharingattachments" .
				"&download=".$downloadid .
				"&downloading=1&ajax=1";
		else
			$geturl = $downloadid;
		
		@fwrite($fp, 
			"GET /".$geturl." HTTP/1.1\r\n" .
			"Host: jcore.net\r\n" .
			"Content-type: text/html\r\n" .
			"Connection: close\r\n\r\n");
			
		while($data = @fgets($fp)) {
			if ($this->downloadCheckTimeOut($fp, $title))
				return false;
			
			if($data == "\r\n")
				break;
				
			$header .= $data;
		}
		
		preg_match('/filename="(.*?)"/i', 
			$header, $matches);
			
		if (isset($matches[1]))
			$filename = $matches[1];
		
		preg_match('/Content-Length:(.*)/i', 
			$header, $matches);
			
		if (isset($matches[1]))
			$filesize = (int)$matches[1];
		
		$fl = null;
		
		if ($filename && @file_exists($this->installPath.$filename)) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess .".$downloadstatusclass."').html('');" .
				"</script>";
			
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b>" .
							__("OK") .
						"</b> (".files::humanSize(@filesize($this->installPath.$filename))." cached)<br />');" .
				"</script>";
			
			url::flushDisplay();
			
			@fclose($fp);
			$this->cleanupFiles[$filename] = $filename;
			
			if ($savefile)
				return $filename;
			
			return files::get($this->installPath.$filename);
		}
		
		if ($savefile && $filename) {
			$fl = @fopen($this->installPath.$filename, 'w');
			
			if (!$fl) {
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
								__("FAILED") .
							"</b><br />');" .
					"</script>";
				
				url::flushDisplay();
				
				tooltip::display(
					sprintf(
					__("Couldn't create local file at \"<b>%s</b>\". Please make sure " .
						"the install path is writable by me. Please " .
						"<a href='javascript://' onclick=\"jQuery('#installerform #buttoninstall').click();\">try again</a> " .
						"and if this error keeps showing up please " .
						"<a href='".JCORE_URL."contact' target='_blank'>" .
						"contact jCore</a> with this error and your system setup."),
						$this->installPath),
					'error');
			
				return false;
			}
		}
		
    	if (!$savefile || !$fl)
			@fgets($fp);
		
		$time = null;
		$percentage = 0;
		$downloadsize = 0;
		
		while (true) {
			if ($filesize)
				$percentage = round($downloadsize * 100 / $filesize);
			
			if ($this->downloadCheckTimeOut($fp, $title))
				return false;
				
			if (!$time || time() - $time > 1) {
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess .".$downloadstatusclass."').html('" .
								$percentage."%');" .
					"</script>";
				
				$time = time();
				url::flushDisplay();
			}
			
   			$data = @fread($fp, 8192);
   			$downloadsize += strlen($data);
   			
    		if (strlen($data) == 0) {
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess .".$downloadstatusclass."').html('" .
								$percentage."%');" .
					"</script>";
				
				url::flushDisplay();
   	    		break;
    		}
   	    	
   	    	if ($fl)
   	    		@fwrite($fl, $data, 8192);
   	    	else	
	   	    	$content .= $data;
		}
		
		fclose($fp);
		
		if ($fl)
			fclose($fl);
		
		if ($savefile && !$filename) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b><br />');" .
				"</script>";
			
			url::flushDisplay();
			
			tooltip::display(
				sprintf(
				__($title." couldn't be completed. The returned response by jCore.net " .
					"is not a file. Please see returned content below. Please " .
					"<a href='javascript://' onclick=\"jQuery('#installerform #buttoninstall').click();\">try again</a> " .
					"and if this error keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
					$this->installPath),
				'error');
			
			echo substr(preg_replace('/\r\n0\r\n/', '', $content),
					0, 1024);
			
			return false;
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b> (".files::humanSize($downloadsize).")<br />');" .
			"</script>";
		
		url::flushDisplay();
		
		if ($savefile) {
			$this->cleanupFiles[$filename] = $filename;
			return $filename;
		}
		
		return $content;
	}
	
	function decompress($file, $title) {
		if (!@file_exists($this->installPath.$file)) {
			tooltip::display(
				__("Couldn't find downloaded ".$title." package file. " .
					"This is a strange error and shouldn't happen so please " .
					"<a href='javascript://' onclick=\"jQuery('#installerform #buttoninstall').click();\">try again</a> " .
					"and if this error keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
			
			return false;
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Uncompressing ".$title."...") .
					"');" .
			"</script>";
		
		if ($this->checkOutOfMemory($this->installPath.$file)) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (" .__("Out of Memory! Please extract jCore " .
							"manually or increment the PHP memory limit.") .
						")<br />');" .
				"</script>";
			
			return false;
		}
		
		url::flushDisplay();
		
		$tar = new tar();
		$tar->openTar($this->installPath.$file);
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b><br />');" .
			"</script>";
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Creating directories...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		foreach($tar->directories as $directory) {
			$directory['name'] = str_replace(
				substr($file, 0, -7), 
				'', $directory['name']);
			
			if (@is_dir($this->installPath.$directory['name']) && 
				!@is_writable($this->installPath.$directory['name']))
			{
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
								__("FAILED") .
							"</b> (".$directory['name']." ".__("not writable").")<br />');" .
					"</script>";
		
				url::flushDisplay();
				return false;
		
			} else {
				@mkdir($this->installPath.$directory['name']);
				@chmod($this->installPath.$directory['name'], 0755);
			}
			
			if (preg_match($this->publicFiles, $directory['name']))
				@chmod($this->installPath.$directory['name'], 0757);
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b><br />');" .
			"</script>";
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Writing files...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		foreach($tar->files as $tarfile) {
			$tarfile['name'] = str_replace(
				substr($file, 0, -7), 
				'', $tarfile['name']);
			
			if (@is_file($this->installPath.$tarfile['name']) && 
				!@is_writable($this->installPath.$tarfile['name']))
			{
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
								__("FAILED") .
							"</b> (".$tarfile['name']." ".__("not writable").")<br />');" .
					"</script>";
		
				url::flushDisplay();
				return false;
		
			} else {
				if ($fp = @fopen($this->installPath.$tarfile['name'], 'w')) {
					@fwrite($fp, $tarfile['file']);
					fclose($fp);
				
					@chmod($this->installPath.$tarfile['name'], 0644);
					
					if (preg_match($this->publicFiles, $tarfile['name']))
						@chmod($this->installPath.$tarfile['name'], 0646);
				}
			}
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b><br />');" .
			"</script>";
		
		url::flushDisplay();
		
		unset($tar);
		
		return true;
	}
	
	function checkOutOfMemory($file) {
		$memoryneeded = round(@filesize($file)*6);
		
		$availablememory = $this->iniGet('memory_limit', true);
		
		if (!$availablememory)
			return false;
			
		if ($memoryneeded+memory_get_usage() < $availablememory)
			return false;
			
		return true;
	}
	
	function checkExtractedSystem() {
		if (!@is_file($this->installPath.'config.inc.php') &&
			!@is_file($this->installPath.'jcore.inc.php'))
			return false;
		
		if (@is_file($this->installPath.'config.inc.php')) {
			$config = files::get($this->installPath.'config.inc.php');
			
			if (!preg_match('/localhost.*?yourdomain_DB.*?yourdomain_mysqluser.*?mysqlpass/s', $config))
				return false;
			
			if (!preg_match('/JCORE_VERSION.*?([0-9\.]+)(\'|"| )/i', $config, $matches))
				return false;
			
			if (!isset($matches[1]) || !$matches[1])
				return false;
			
			$sqlfile = '';
			if (@is_file($this->installPath.'jCore-'.$matches[1].'.sql')) {
				$sqlfile = 'jCore-'.$matches[1].'.sql';
				$this->cleanupFiles[$sqlfile] = $sqlfile;
			}
			
			return array(
				'Type' => 'Server',
				'Name' => 'jCore Server',
				'Version' => $matches[1],
				'ConfigFile' => 'config.inc.php',
				'SQLFile' => $sqlfile);
		}
		
		$config = files::get($this->installPath.'jcore.inc.php');
		
		if (!preg_match('/localhost.*?yourclient_DB.*?yourclient_mysqlusername.*?mysqlpassword/s', $config))
			return false;
		
		if (!preg_match('/JCORE_VERSION.*?([0-9\.]+)(\'|"| )/i', $config, $matches))
			return false;
		
		if (!isset($matches[1]) || !$matches[1])
			return false;
		
		$sqlfile = '';
		if (@is_file($this->installPath.'jCore-'.$matches[1].'.sql')) {
			$sqlfile = 'jCore-'.$matches[1].'.sql';
			$this->cleanupFiles[$sqlfile] = $sqlfile;
		}
		
		return array(
			'Type' => 'Client',
			'Name' => 'jCore Client',
			'Version' => $matches[1],
			'ConfigFile' => 'jcore.inc.php', 
			'SQLFile' => $sqlfile);
	}
	
	function iniGet($var, $parse = false) {
		if (!$var)
			return null;
		
		$value = ini_get($var);
		
		if (!$parse)
			return $value;
		
		if (!is_numeric($value)) {
    		if (strpos($value, 'M') !== false)
        		$value = intval($value)*1024*1024;
    		elseif (strpos($value, 'K') !== false)
        		$value = intval($value)*1024;
    		elseif (strpos($value, 'G') !== false)
        		$value = intval($value)*1024*1024*1024;
		}
		
		return $value;
	}
	
	function runSQL($sqlqueries, $title) {
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Running SQL queries...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		define('SQL_PREFIX', $this->sqlPrefix);
		$queries = preg_split('/;(\r\n|\n)/', $sqlqueries);
		
		foreach($queries as $query) {
			$query = preg_replace(
				'/(((DROP|CREATE|ALTER) TABLE|INSERT INTO|UPDATE|DELETE) [a-zA-Z0-9\_\- ]*?)`([a-zA-Z0-9\_\-]*?)`/',
				'\1`{\4}`',
				$query);
			
			sql::run($query);
			
			if (sql::error()) {
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
								__("FAILED") .
							"</b> (please see error) ');" .
					"</script>";
				
				url::flushDisplay();
				return false;
			}
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b><br />');" .
			"</script>";
		
		return true;
	}
	
	function cleanup($files) {
		if (!$files || !is_array($files))
			return true;
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Deleting packages (cleanup)...") .
					"');" .
			"</script>";
					
		url::flushDisplay();
		$errors = false;
		
		foreach($files as $file) {
			if (!@unlink($this->installPath.$file)) {
				echo
					"<script type='text/javascript'>" .
						"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
								__("FAILED") .
							"</b> (".$file.") ');" .
					"</script>";
				
				url::flushDisplay();
				$errors[] = $file;
			}
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
					($errors?
						"Please go ahead and delete them manually.":
						"<b>".__("OK")."</b>") .
					"<br />');" .
			"</script>";
		
		url::flushDisplay();
		return true;
	}
	
	function check() {
		tooltip::display(
			"<span id='jcoreinstallerprocess'></span>",
			'notification');
			
		// Checking PHP Version	
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Checking PHP version...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		if (phpversion() < '5.1') {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (".phpversion().") (" .
						__("you may continue but jCore was only tested with PHP >= 5.1") .
						")<br />');" .
				"</script>";
			
			url::flushDisplay();
			
		} else {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b>" .
							__("OK") .
						"</b> (".phpversion().")<br />');" .
				"</script>";
			
			url::flushDisplay();
		}
		
		// Checking MySQL Version
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Checking MySQL version...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		if (mysql_get_client_info() < '4.1') {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (".mysql_get_client_info().") (" .
						__("you may continue but jCore was only tested with MySQL >= 4.1").
						")<br />');" .
				"</script>";
			
			url::flushDisplay();
			
		} else {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b>" .
							__("OK") .
						"</b> (".mysql_get_client_info().")<br />');" .
				"</script>";
			
			url::flushDisplay();
		}
		
		// Checking for GetText
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Checking for PHP gettext...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		if (!extension_loaded('gettext')) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (".__("not implemented").")<br />');" .
				"</script>";
			
			url::flushDisplay();
			
			tooltip::display(
				__("No build in gettext support found for PHP. Please " .
					"<a href='http://php.net/manual/en/book.gettext.php' target='_blank'>" .
					"re-build PHP with gettext support</a> on LAMP enviroments or " .
					"activate the extension in WAMP enviroments using " .
					"PHP -> PHP Extensions -> php_gettext."),
				'error');
		
			return false;
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b> (".__("implemented").")<br />');" .
			"</script>";
		
		url::flushDisplay();
		
		// Checking for GD Extension
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Checking for PHP GD...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		if (!extension_loaded('gd')) {
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b class=\"red\">" .
							__("FAILED") .
						"</b> (".__("not implemented").")<br />');" .
				"</script>";
			
			url::flushDisplay();
			
			tooltip::display(
				__("No build in GD support found for PHP. Please " .
					"<a href='http://php.net/manual/en/book.image.php' target='_blank'>" .
					"re-build PHP with GD support</a> on LAMP enviroments or " .
					"activate the extension in WAMP enviroments using " .
					"PHP -> PHP Extensions -> php_gd."),
				'error');
		
			return false;
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b> (".__("implemented").")<br />');" .
			"</script>";
		
		url::flushDisplay();
		
		// Check for jCore versions
		$padcontent = $this->download(
			'pad.xml', 'Checking for latest jCore versions', false);
			
		preg_match('/<Program_Versions>(.*?)<\/Program_Versions>/is', $padcontent, $matches);
		
		if (!isset($matches[1])) {
			tooltip::display(
				__("Something went wrong while downloading the PAD file from jCore.net " .
					"which is required to check for latest versions. Please " .
					"<a href='install.php'>try again</a> and if this error " .
					"keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
		
			return false;
		}
		
		preg_match_all('/<(.*?)>(.*?)<\/\1>/is', $matches[1], $matches);
		
		foreach($matches[1] as $key => $value) {
			echo	
				"<iframe src='install.php?cookie=".
					urlencode("[Versions][".$value."]")."&amp;cookievalue=".
					urlencode($matches[2][$key])."' style='display: none;'>" .
				"</iframe>" .
				"<script type='text/javascript'>" .
					"jQuery('.jcore-versions .jcore-version-".strtolower($value)."').html('" .
						$value." ".$matches[2][$key]."');" .
				"</script>";
		}
		
		preg_match('/<Program_Downloads>(.*?)<\/Program_Downloads>/is', $padcontent, $matches);
		
		if (!isset($matches[1])) {
			tooltip::display(
				__("Something went wrong while downloading the PAD file from jCore.net " .
					"which is required to check for latest download urls. Please " .
					"<a href='install.php'>try again</a> and if this error " .
					"keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
		
			return false;
		}
		
		preg_match_all('/<(.*?)>(.*?)<\/\1>/is', $matches[1], $matches);
		
		foreach($matches[1] as $key => $value) {
			if (preg_match('/SQLArchive/', $value)) {
				
				preg_match_all('/<(.*?)>(.*?)<\/\1>/is', $matches[2][$key], $archivematches);
				
				foreach($archivematches[1] as $key => $value) {
					echo	
						"<iframe src='install.php?cookie=".
							urlencode("[Downloads][SQLArchive][".$value."]")."&amp;cookievalue=".
							urlencode($archivematches[2][$key])."' style='display: none;'>" .
						"</iframe>";
					
					$this->downloadSQLArchiveID[$archivematches[1][$key]] = $archivematches[2][$key];
				}
				
				continue;
			}
			
			echo	
				"<iframe src='install.php?cookie=".
					urlencode("[Downloads][".$value."]")."&amp;cookievalue=".
					urlencode($matches[2][$key])."' style='display: none;'>" .
				"</iframe>";
				
			if (preg_match('/Server/', $value))
				$this->downloadServerID = $matches[2][$key];
				
			if (preg_match('/Client/', $value))
				$this->downloadClientID = $matches[2][$key];
				
			if (preg_match('/Installer/', $value))
				$this->downloadInstallerID = $matches[2][$key];
				
			if (preg_match('/SQL/', $value))
				$this->downloadSQLID = $matches[2][$key];
		}
		
		preg_match('/<Program_Modules>(.*?)<\/Program_Modules>/is', $padcontent, $matches);
		
		if (!isset($matches[1])) {
			tooltip::display(
				__("Something went wrong while downloading the PAD file from jCore.net " .
					"which is required to check for latest modules. Please " .
					"<a href='install.php'>try again</a> and if this error " .
					"keeps showing up please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
		
			return false;
		}
		
		preg_match_all('/<ID>(.*?)<\/ID>.*?<Title>(.*?)<\/Title>/is', 
			$matches[1], $matches);
		
		$this->modules = array();
		
		foreach($matches[1] as $key => $value) {
			echo	
				"<iframe src='install.php?cookie=".
					urlencode("[Modules][".$value."]")."&amp;cookievalue=".
					urlencode($matches[2][$key])."' style='display: none;'>" .
				"</iframe>";
				
			$this->modules[$value] = $matches[2][$key];
		}
	}
	
	function install() {
		$sqlqueries = null;
		$extractedsystem = $this->checkExtractedSystem();
		
		if ($extractedsystem) {
			$this->install ==  strtolower($extractedsystem['Type']);
			
			if (isset($this->downloadSQLArchiveID['v'.str_replace('.','', $extractedsystem['Version'])]) &&
				$this->downloadSQLArchiveID['v'.str_replace('.','', $extractedsystem['Version'])])
			{
				$this->downloadSQLID = 
					$this->downloadSQLArchiveID['v'.str_replace('.','', $extractedsystem['Version'])];
			}
		}
		
		if (!$extractedsystem && !$this->downloadServerID) {
			tooltip::display(
				__("jCore Server download ID couldn't be found. This usually means " .
					"that something whent wrong while the last check for latest " .
					"versions. Please try to do a <a href='install.php?check=1'>" .
					"recheck</a> and if you keep seeing this message please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
			
			$this->error = 1;
			return false;
		}
				
		if (!$extractedsystem && !$this->downloadClientID) {
			tooltip::display(
				__("jCore Client download ID couldn't be found. This usually means " .
					"that something whent wrong while the last check for latest " .
					"versions. Please try to do a <a href='install.php?check=1'>" .
					"recheck</a> and if you keep seeing this message please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
			
			$this->error = 1;
			return false;
		}
		
		if (!$this->downloadSQLID) {
			tooltip::display(
				__("jCore SQL download ID couldn't be found. This usually means " .
					"that something whent wrong while the last check for latest " .
					"versions. Please try to do a <a href='install.php?check=1'>" .
					"recheck</a> and if you keep seeing this message please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
			
			$this->error = 1;
			return false;
		}
				
		if (!count($this->modules)) {
			tooltip::display(
				__("Available jCore modules couldn't be found. This usually means " .
					"that something whent wrong while the last check for latest " .
					"versions. Please try to do a <a href='install.php?check=1'>" .
					"recheck</a> and if you keep seeing this message please " .
					"<a href='".JCORE_URL."contact' target='_blank'>" .
					"contact jCore</a> with this error and your system setup."),
				'error');
			
			$this->error = 1;
			return false;
		}
		
		if (preg_match('/[^a-zA-Z0-9\.\_\-]/', $this->sqlDB)) {
			tooltip::display(
				__("Invalid SQL database specified! SQL database " .
					"may consist of a-z, 0-9 and underscores only."),
				'error');
			
			$this->error = 1;
			return false;
		}
				
		if (preg_match('/[^a-zA-Z0-9\.\_\-]/', $this->sqlPrefix)) {
			tooltip::display(
				__("Invalid SQL table prefix specified! SQL table prefix " .
					"may consist of a-z, 0-9 and underscores only."),
				'error');
			
			$this->error = 1;
			return false;
		}
				
		if (!$extractedsystem && !@is_writable($this->installPath)) {
			tooltip::display(
				__("Please give write access to the install " .
					"path you would like to install jCore to!<br />" .
					"Install path currently set to: ").
					"<b>".$this->installPath."</b>",
				'error');
			
			$this->error = 2;
			return false;
		}
		
		if (!$extractedsystem && !$this->installOverwrite && 
			(@is_file($this->installPath.'config.inc.php') ||
			 @is_file($this->installPath.'jcore.inc.php'))) 
		{
			tooltip::display(
				sprintf(
					__("An existing installation has been noticed at the " .
						"defined \"<b>%s</b>\" install path. To make sure you won't " .
						"overwrite an existing jCore system unless you want to please " .
						"check the Overwrite Existing system in the form " .
						"below and try again.<br /><br />" .
						"Please note that all the modifications you have " .
						"made to the existing site will also be overwritten."),
					$this->installPath),
				'error');
			
			$this->error = 3;
			return false;
		}
		
		if ($extractedsystem && !@is_writable($this->installPath.$extractedsystem['ConfigFile'])) {
			tooltip::display(
				__("Please give write access to the <b>".$extractedsystem['ConfigFile']."</b> " .
					"file for the installer to configure your website! You can remove " .
					"the write access once the installation process has been completed."),
				'error');
			
			$this->error = 2;
			return false;
		}
		
		if (!sql::connect($this->sqlHost, $this->sqlUser, $this->sqlPassword)) {
			tooltip::display(
				sprintf(
					__("Couldn't connect to the SQL Database on \"<b>%s</b>\". " .
						"Please make sure you can connect to your database " .
						"on \"<b>%s</b>\" and the defined user \"<b>%s</b>\" " .
						"has the required privileges to create new tables/db."),
					$this->sqlHost,
					$this->sqlHost,
					$this->sqlUser),
				'error');
			
			$this->error = 4;
			return false;
		}
			
		if (!sql::selectDB($this->sqlDB)) {
			sql::run(
				" CREATE DATABASE `".$this->sqlDB."` " .
				" DEFAULT CHARACTER SET utf8 " .
				" COLLATE utf8_general_ci");
			
			$sqlerror = sql::error();
			
			if ($sqlerror) {
				tooltip::display(
					sprintf(
						__("Couldn't create \"<b>%s</b>\" database. " .
							"Please make sure \"<b>%s</b>\" has the required privileges " .
							"to create new databases or please create the database " .
							"manually.<br /><br /> SQL Error: %s"),
						$this->sqlDB,
						$this->sqlUser,
						$sqlerror),
					'error');
			
				$this->error = 4;
				return false;
			}
			
			sql::selectDB($this->sqlDB);
		}
		
		if ($this->sqlPrefix) {
			$prefixedtables = array();
			
			foreach($this->sqlTables as $table)
				$prefixedtables[] = $this->sqlPrefix.'_'.$table;
				
			$this->sqlTables = $prefixedtables;
		}
		
		$tablesexist = sql::fetch(sql::run(
			" SHOW TABLES " .
			" WHERE `Tables_in_".$this->sqlDB."` IN " .
			" ('".implode("', '", $this->sqlTables)."')"));
		
		if (!$this->installOverwriteSQL && $tablesexist) {
			tooltip::display(
				sprintf(
					__("An existing SQL installation has been noticed in the " .
						"defined \"<b>%s</b>\" database. To make sure you won't " .
						"overwrite an existing jCore database unless you want to please " .
						"check the Overwrite Existing database in the form " .
						"below and try again.<br /><br />" .
						"Please note that all the modifications you have " .
						"made to the existing database will also be overwritten."),
					$this->sqlDB),
				'error');
			
			$this->error = 7;
			return false;
		}
				
		if ($this->install == 'client') {
			if (!is_dir($this->serverPath) || !is_file($this->serverPath.'config.inc.php')) {
				tooltip::display(
					sprintf(
						__("The defined jCore Server Path \"<b>%s</b>\" cannot be found " .
							"and/or is not a valid jCore Server directory! " .
							"Please make sure you have extracted jCore Server package to " .
							"this directory and is readable by everyone. NOTE: You " .
							"don't have to install it, just copy the content of jCore Server " .
							"package to this directory."),
						$this->serverPath),
					'error');
				
				$this->error = 5;
				return false;
			}
			
			tooltip::display(
				"<span id='jcoreinstallerprocess'></span>",
				'notification');
			
			if (!$extractedsystem) {
				$packagefile = $this->download(
					$this->downloadClientID, 'Downloading jCore Client');
					
				if (!$packagefile)
					return false;
				
				if (!$this->decompress($packagefile, 'jCore Client')) {
					tooltip::display(
						__("Something went wrong while decompressing the package file. " .
							"This error should only happen when you want to overwrite an " .
							"existing system and the already existing directories/files " .
							"are not writable by me or if you run out of memory. " .
							"Please see the location where the script stoped and after " .
							"fixing the permissions or incrementing the PHP memory limit " .
							"try again.<br /><br />" .
							"Also you could just simply extract jCore manually and run " .
							"the installer again."),
						'error');
					
					$this->error = 6;
					return false;
				}
			}
			
			if ($extractedsystem['SQLFile'])
				$sqlqueries = files::get($this->installPath.$extractedsystem['SQLFile']);
			
			if (!$sqlqueries)
				$sqlqueries = $this->download(
					$this->downloadSQLID, 'Downloading jCore Client SQL', false);
			
			if (!$sqlqueries)
				return false;
				
			if (!$this->runSQL($sqlqueries, 'jCore Client'))
				return false;
			
			if (count($this->clientModules)) {	
				foreach($this->clientModules as $module)
					sql::run(
						" INSERT INTO `" .
							($this->sqlPrefix?
								$this->sqlPrefix.'_modules':
								'modules') .
							"` SET" .
						" Name = '".sql::escape($module)."'");
			}
			
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('" .
							__("Updating config/template files...") .
						"');" .
				"</script>";
						
			url::flushDisplay();
			
			if ($fp = @fopen($this->installPath.'jcore.inc.php', 'r+')) {
				$config = @fread($fp, @filesize($this->installPath.'jcore.inc.php'));
				@fclose($fp);
				
				$config = str_replace(
					'localhost', $this->sqlHost, $config);
				$config = str_replace(
					'yourclient_DB', $this->sqlDB, $config);
				$config = str_replace(
					'yourclient_mysqlusername', $this->sqlUser, $config);
				$config = str_replace(
					'mysqlpassword', $this->sqlPassword, $config);
				$config = str_replace(
					'http://yourclient.com/', $this->installURL, $config);
				$config = str_replace(
					'/home/yourclient/public_html/', $this->installPath, $config);
				$config = str_replace(
					'http://jcore.yourdomain.com/', $this->serverURL, $config);
				$config = str_replace(
					'/var/www/jcore/', $this->serverPath, $config);
				
				$config = preg_replace(
					'/(SQL_PREFIX.*?)\'\'/', '\1\''.$this->sqlPrefix.'\'', $config);
				
				if ($fp = @fopen($this->installPath.'jcore.inc.php', 'w')) {
					@fwrite($fp, $config);
					@fclose($fp);
				}
			}
		
			if ($fp = @fopen($this->installPath.'template/template.css', 'r+')) {
				$css = @fread($fp, @filesize($this->installPath.'template/template.css'));
				@fclose($fp);
				
				$css = str_replace(
					'http://icons.jcore.net/', $this->serverURL.'lib/icons/', $css);
				
				if ($fp = @fopen($this->installPath.'template/template.css', 'w')) {
					@fwrite($fp, $css);
					@fclose($fp);
				}
			}
		
			echo
				"<script type='text/javascript'>" .
					"jQuery('#jcoreinstallerprocess').append('<b>" .
							__("OK") .
						"</b><br />');" .
				"</script>";
		
			url::flushDisplay();
			
			if (!$this->keepPackages && $this->cleanupFiles)
				$this->cleanup($this->cleanupFiles);
			
			tooltip::display(
				sprintf(
					__("<b>jCore Client successfully installed!</b><br /> " .
						"You can now access your new site at <a href='%s' target='_blank'>%s</a><br /><br /> " .
						"Please remember to delete \"install.php\" and remove write access where not needed."),
					$this->installURL,
					$this->installURL),
				'success');
		
			return true;
		}
		
		tooltip::display(
			"<span id='jcoreinstallerprocess'></span>",
			'notification');
		
		if (!$extractedsystem) {
			$packagefile = $this->download(
				$this->downloadServerID, 'Downloading jCore Server');
				
			if (!$packagefile)
				return false;
			
			if (!$this->decompress($packagefile, 'jCore Server')) {
				tooltip::display(
					__("Something went wrong while decompressing the package file. " .
						"This error should only happen when you want to overwrite an " .
						"existing system and the already existing directories/files " .
						"are not writable by me or if you run out of memory. " .
						"Please see the location where the script stoped and after " .
						"fixing the permissions or incrementing the PHP memory limit " .
						"try again.<br /><br />" .
						"Also you could just simply extract jCore manually and run " .
						"the installer again."),
					'error');
				
				$this->error = 6;
				return false;
			}
		}
		
		if ($extractedsystem['SQLFile'])
			$sqlqueries = files::get($this->installPath.$extractedsystem['SQLFile']);
		
		if (!$sqlqueries)
			$sqlqueries = $this->download(
				$this->downloadSQLID, 'Downloading jCore Server SQL', false);
		
		if (!$sqlqueries)
			return false;
		
		if (!$this->runSQL($sqlqueries, 'jCore Server'))
			return false;
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('" .
						__("Updating config/template files...") .
					"');" .
			"</script>";
						
		url::flushDisplay();
		
		if ($fp = @fopen($this->installPath.'config.inc.php', 'r+')) {
			$config = @fread($fp, @filesize($this->installPath.'config.inc.php'));
			@fclose($fp);
			
			$config = str_replace(
				'localhost', $this->sqlHost, $config);
			$config = str_replace(
				'yourdomain_DB', $this->sqlDB, $config);
			$config = str_replace(
				'yourdomain_mysqluser', $this->sqlUser, $config);
			$config = str_replace(
				'mysqlpass', $this->sqlPassword, $config);
			$config = str_replace(
				'http://yourdomain.com/', $this->installURL, $config);
			$config = str_replace(
				'/home/yourdomain/public_html/', $this->installPath, $config);
			
			$config = preg_replace(
				'/(SQL_PREFIX.*?)\'\'/', '\1\''.$this->sqlPrefix.'\'', $config);
				
			if ($fp = @fopen($this->installPath.'config.inc.php', 'w')) {
				@fwrite($fp, $config);
				@fclose($fp);
			}
		}
		
		if ($fp = @fopen($this->installPath.'template/template.css', 'r+')) {
			$css = @fread($fp, @filesize($this->installPath.'template/template.css'));
			@fclose($fp);
			
			$css = str_replace(
				'http://icons.jcore.net/', $this->installURL.'lib/icons/', $css);
			
			if ($fp = @fopen($this->installPath.'template/template.css', 'w')) {
				@fwrite($fp, $css);
				@fclose($fp);
			}
		}
		
		if ($fp = @fopen($this->installPath.'template/admin.css', 'r+')) {
			$css = @fread($fp, @filesize($this->installPath.'template/admin.css'));
			@fclose($fp);
			
			$css = str_replace(
				'http://icons.jcore.net/', $this->installURL.'lib/icons/', $css);
			
			if ($fp = @fopen($this->installPath.'template/admin.css', 'w')) {
				@fwrite($fp, $css);
				@fclose($fp);
			}
		}
		
		echo
			"<script type='text/javascript'>" .
				"jQuery('#jcoreinstallerprocess').append('<b>" .
						__("OK") .
					"</b><br />');" .
			"</script>";
		
		url::flushDisplay();
		
		if (!$this->keepPackages && $this->cleanupFiles)
			$this->cleanup($this->cleanupFiles);
		
		tooltip::display(
			sprintf(
				__("<b>jCore Server successfully installed!</b><br /> " .
					"You can now access your new site at <a href='%s' target='_blank'>%s</a><br /><br /> " .
					"If you would like to use jCore Server for your other client sites " .
					"please use the following values:<br /> " .
					"<li><span style='font-size: 90%%;'>jCore Server Path</span>: %s</li> " .
					"<li><span style='font-size: 90%%;'>jCore Server URL</span>: %s</li><br /> " .
					"Please remember to delete \"install.php\" and remove write access where not needed."),
				$this->installURL,
				$this->installURL,
				$this->installPath,
				$this->installURL),
			'success');
		
		return true;
	}
	
	function verify(&$form) {
		if (!isset($_COOKIE['jCoreInstaller']) || isset($_GET['check']))
			$this->check();
		
		if (!$form->verify())
			return false;
		
		$this->install = $form->get('Install');
		$this->installOverwrite = $form->get('InstallOverwrite');
		$this->installOverwriteSQL = $form->get('InstallOverwriteSQL');
		$this->installPath = rtrim($form->get('InstallPath'), '/').'/';
		$this->installURL = rtrim($form->get('InstallURL'), '/').'/';
		$this->clientModules = $form->get('ClientModules');
		$this->sqlHost = $form->get('SQLHost');
		$this->sqlDB = $form->get('SQLDB');
		$this->sqlUser = $form->get('SQLUser');
		$this->sqlPassword = $form->get('SQLPassword');
		$this->sqlPrefix = $form->get('SQLPrefix');
		$this->serverPath = rtrim($form->get('ServerPath'), '/').'/';
		$this->serverURL = rtrim($form->get('ServerURL'), '/').'/';
		$this->keepPackages = $form->get('KeepPackages');
		
		if (!$this->install())
			return false;
		
		$form->setValue('InstallOverwrite', '');
		$form->setValue('InstallOverwriteSQL', '');
		
		return true;
	}
	
	function display($formdesign = true) {
		echo
			"<div class='installer'>";
		
		$extractedsystem = $this->checkExtractedSystem();
		
		$form = new form("jCore Installer");
		$form->action = url::uri('ALL');
		
		$form->add(
			'Install',
			'Install',
			FORM_INPUT_TYPE_RADIO,
			true);
		
		if ($extractedsystem) {
			$form->addValue(
				strtolower($extractedsystem['Type']),
				"<b>".$extractedsystem['Name']." ver-".$extractedsystem['Version']."</b> " .
					"(found extracted at the Install Path)");
			
			$form->setValue(strtolower($extractedsystem['Type']));
			$form->setStyle('display: none;');
			
		} else {
			$form->addValue(
				'server',
				"<b>jCore Server</b> " .
					"(install this if you're not sure what to choose)");
			
			$form->addValue(
				'client',
				"<b>jCore Client</b> " .
					"(you will have to define jCore server's path)");
		}
		
		$form->addAttributes(
			"onclick=\"toggleClientSettings(this)\"");
		
		$form->add(
			'Overwrite Existing',
			'InstallOverwrite',
			FORM_INPUT_TYPE_HIDDEN);
		$form->setValueType(FORM_VALUE_TYPE_BOOL);
		$form->setValue(0);
		
		$form->addAdditionalText(
			__("(confirm to overwrite exising jCore system at the install path)"));
		
		$form->add(
			'Install Path',
			'InstallPath',
			FORM_INPUT_TYPE_TEXT,
			true,
			$this->installPath);
		$form->setStyle('width: 500px;');
		
		$form->addAdditionalText(
			__("(install jCore to)"));
		
		$form->add(
			'Website URL',
			'InstallURL',
			FORM_INPUT_TYPE_TEXT,
			true,
			$this->installURL);
		$form->setStyle('width: 350px;');
		
		$form->addAdditionalText(
			__("(the URL to access your new website)"));
		
		$form->add(
			'Database / MySQL Settings',
			null,
			FORM_OPEN_FRAME_CONTAINER,
			true);
		
		$form->add(
			'Overwrite Existing',
			'InstallOverwriteSQL',
			FORM_INPUT_TYPE_HIDDEN);
		$form->setValueType(FORM_VALUE_TYPE_BOOL);
		$form->setValue(0);
		
		$form->addAdditionalText(
			__("(confirm to overwrite exising jCore tables in the defined database)"));
		
		$form->add(
			'MySQL Host',
			'SQLHost',
			FORM_INPUT_TYPE_TEXT,
			true,
			$this->sqlHost);
		$form->setStyle('width: 200px;');
		
		$form->addAdditionalText(
			__("(in most situation localhost is just perfect)"));
		
		$form->add(
			'Database',
			'SQLDB',
			FORM_INPUT_TYPE_TEXT,
			true,
			$this->sqlDB);
		$form->setStyle('width: 250px;');
		
		$form->addAdditionalText(
			__("(installer will try to create this database automatically)"));
		
		$form->add(
			'Table Prefix',
			'SQLPrefix',
			FORM_INPUT_TYPE_TEXT);
		$form->setStyle('width: 100px;');
		
		$form->addAdditionalText(
			__("(only if you want to use one DB for multiple sites, for e.g. client1, client2, ...)"));
		
		$form->add(
			'MySQL User',
			'SQLUser',
			FORM_INPUT_TYPE_TEXT,
			true,
			'root');
		$form->setStyle('width: 150px;');
		
		$form->addAdditionalText(
			__("(username to access MySQL on localhost)"));
		
		$form->add(
			'Password',
			'SQLPassword',
			FORM_INPUT_TYPE_TEXT);
		$form->setStyle('width: 150px;');
		
		$form->addAdditionalText(
			__("(can be left empty but recommended to set)"));
		
		$form->add(
			null,
			null,
			FORM_CLOSE_FRAME_CONTAINER);
		
		if (!$extractedsystem || $extractedsystem['Type'] == 'Client') {
			$form->add(
				'jCore Client Settings',
				'client-settings',
				FORM_OPEN_FRAME_CONTAINER,
				($extractedsystem?
					true:
					false));
			
			$form->add(
				'jCore Server Path',
				'ServerPath',
				FORM_INPUT_TYPE_TEXT,
				false,
				$this->serverPath);
			$form->setStyle('width: 350px;');
			
			$form->addAdditionalText(
				__("(this should point to the jCore Server)"));
			
			$form->add(
				'jCore Server URL',
				'ServerURL',
				FORM_INPUT_TYPE_TEXT,
				false,
				$this->serverURL);
			$form->setStyle('width: 250px;');
			
			$form->addAdditionalText(
				__("(url to access jCore Server)"));
			
			$form->add(
				'Modules to use',
				'ClientModules',
				FORM_INPUT_TYPE_CHECKBOX,
				false);
			$form->setValueType(FORM_VALUE_TYPE_ARRAY);
			
			$form->add(
				null,
				null,
				FORM_CLOSE_FRAME_CONTAINER);
		}
		
		$form->add(
			'Additional Settings / Information',
			null,
			FORM_OPEN_FRAME_CONTAINER);
		
		$form->add(
			'Keep Packages',
			'KeepPackages',
			($extractedsystem?
				FORM_INPUT_TYPE_HIDDEN:
				FORM_INPUT_TYPE_CHECKBOX),
			false,
			($extractedsystem?
				null:
				true));
		$form->setValueType(FORM_VALUE_TYPE_BOOL);
		
		$form->addAdditionalText(
			__("(do not delete downloaded jCore packages after installation)"));
		
		$form->add(
			"<ul>" .
				"<li>" .
					"<b>".
						__("Admin Login Information").
					"</b>" .
					"<br /><br />" .
					__("Once jCore installed a default admin user will be set up " .
						"with administration privileges, to login to this account " .
						"please use:" .
						"<br /><br />" .
						"Username: <b>admin</b><br />" .
						"Password: <b>jcore</b><br /><br />").
				"</li>" .
				"<li>" .
					"<b>".
						__("SEO Friendly Links").
					"</b>" .
					"<br /><br />" .
					__("jCore uses the " .
						"<a href='http://httpd.apache.org/docs/1.3/mod/mod_rewrite.html' target='_blank'>" .
						"mod_rewrite Apache module</a> wich enables the system " .
						"to use SEO friendly links istead of the old index.php?id=xx " .
						"method. If you don't have mod_rewrite activated for your " .
						"site you can still use jCore just go to the \"jcore.inc.php\" " .
						"and/or \"config.inc.php\" and change SEO_FRIENDLY_LINKS to " .
						"\"false\". By default this option is turned on and set to \"true\".<br /><br />").
				"</li>" .
				"<li>" .
					"<b>".
						__("Website User Ownership (Apache, suPHP)").
					"</b>" .
					"<br /><br />" .
					__("On systems without <a href='http://www.suphp.org' target='_blank'>" .
						"suPHP</a> installed the whole directory tree and files " .
						"will be owned by apache:apache user/group. To change the ownership " .
						"of the system in these cases please run (as root) the following " .
						"command in your install path:<br /><br />" .
						"<code>[root@localhost]# chown user:group -R ./</code>").
				"</li>" .
			"</ul>",
			null,
			FORM_STATIC_TEXT);
		
		$form->add(
			null,
			null,
			FORM_CLOSE_FRAME_CONTAINER);
		
		$form->add(
			'Install jCore',
			'install',
			FORM_INPUT_TYPE_SUBMIT);
		
		$form->add(
			'Reset',
			'reset',
			FORM_INPUT_TYPE_RESET);
		
		$this->verify($form);
		
		foreach($this->modules as $moduleid => $modulename)
			$form->addValue(
				'ClientModules',
				$moduleid,
				$modulename);
		
		if ($this->error == 3 || $form->get('InstallOverwrite')) {
			$form->edit(
				'InstallOverwrite',
				'Overwrite Existing',
				'InstallOverwrite',
				FORM_INPUT_TYPE_CHECKBOX,
				true,
				1);
			
			if ($this->error == 3)
				$form->setElementKey('VerifyResult', 'InstallOverwrite', 1);
		}
		
		if ($this->error == 7 || $form->get('InstallOverwriteSQL')) {
			$form->edit(
				'InstallOverwriteSQL',
				'Overwrite Existing',
				'InstallOverwriteSQL',
				FORM_INPUT_TYPE_CHECKBOX,
				true,
				1);
			
			if ($this->error == 7)
				$form->setElementKey('VerifyResult', 'InstallOverwriteSQL', 1);
		}
		
		$form->display();
		
		echo 
			"</div>";	//installer
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Installer - jCore - the Webmasters Multisite CMS</title>
<meta charset="utf8" />
<meta name="title" content="Installer - jCore - the Webmasters Multisite CMS" />
<meta name="keywords" content="jcore, cms, content management system, php, mysql, lamp, web cms, multi site cms, php multisite cms, open source cms" />
<meta name="description" content="jCore is the Multisite web Content Management System build especially for webmasters to easily maintenance multiple websites." />
<link rel="icon" type="image/png" href="<?php echo JCORE_URL; ?>template/images/favicon.png" />
<script src='<?php echo JCORE_URL; ?>static.php?request=jquery&amp;installer-v<?php echo INSTALLER_VERSION; ?>' type='text/javascript'></script> 
<script type="text/javascript">
<!--
	function toggleClientSettings(install) {
		if (install.value == 'server' && jQuery('.fc-client-settings, .fc-client-settings a').is('.expanded')) {
			jQuery('.fc-client-settings a').click();
			return;
		}
		
		if (install.value == 'client' && !jQuery('.fc-client-settings, .fc-client-settings a').is('.expanded')) { 
			jQuery('.fc-client-settings a').click();
			return;
		}
	}
-->
</script>
<style type='text/css'>
/*
 * 
 * HTML Elements
 * 
 */



body, td {
	font-family: Arial, Helvetica, Sans-serif;
	font-size: 13px;
	color: #777;
}

body {
	background: #ddd;
	margin: 0;
}

a {
	color: #50b1d4;
}

a:hover {
	color: #000;
}

a.comment {
	color: #a9a9a9;
	text-decoration: none;
}

a.comment:hover {
	color: #50b1d4;
	text-decoration: underline;
}

form {
	padding: 0;
	margin: 0;
}

label {
	white-space: nowrap;
}

hr {
	height: 1px;
	overflow: hidden;
	border: 0;
	border-bottom: 1px dotted #e0e0e0;
	color: #e0e0e0;
	clear: both;
}

h1, h2, h3 {
	color: #000000;
	font-weight: normal;
	font-family: 'Nobile', arial, serif;
}

input, select, textarea {
	border: 1px solid #d4d4d4;
	background: url("http://jcore.net/template/images/inputbg.jpg") repeat-x #fff;
	padding: 5px 7px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
}

input:focus, select:focus, textarea:focus {
	outline: none;
	border: 1px solid #338ead;
	-webkit-box-shadow: 0px 0px 5px #338ead;
	-moz-box-shadow: 0px 0px 5px #338ead;
	box-shadow: 0px 0px 5px #338ead;
}


/*
 * 
 * Color classes
 * 
 */



.hilight {
	color: #50b1d4;
}

.hilight-bg {
	background-color: #50b1d4;
	color: #fff;
}

.site-color {
	color: #50b1d4;
}

.comment {
	font-size: 10px;
	color: #a9a9a9;
}

.red {
	color: #ff0000;
}

.green {
	color: #00FF00;
}

.blue {
	color: #0000FF;
}

.black {
	color: #000;
}

.white {
	color: #fff;
}

.bold {
	font-weight: bold;
}

.absolute {
	position: absolute;
	display: none;
}

.nowrap {
	white-space: nowrap;
}



/*
 * 
 * Globally used elements
 * 
 */



.clear-both {
	clear: both;
	height: 1px;
	overflow: hidden;
}

.spacer {
	clear: both;
	height: 5px;
	overflow: hidden;
	display: block;
}

.separator {
	height: 1px;
	overflow: hidden;
	border: 0;
	border-bottom: 1px dotted #e0e0e0;
	clear: both;
}

.align-right {
	float: right;
}

.rounded-corners {
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
}

.rounded-corners-top {
	-webkit-border-radius: 3px 3px 0 0;
	-moz-border-radius: 3px 3px 0 0;
	border-radius: 3px 3px 0 0;
}

.rounded-corners-bottom {
	-webkit-border-radius: 0 0 3px 3px;
	-moz-border-radius: 0 0 3px 3px;
	border-radius: 0 0 3px 3px;
}



/*
 * 
 * Ajax Loading status
 * 
 */


 
.loading {
    background: #fbf7aa;
    color: black;
    font-weight: bold;
    padding: 5px 10px;
    border: 2px solid #f9e98e;
    border-top: 0;
    z-index: 101;
	overflow: hidden;
}

.loading.rounded-corners {
	-webkit-border-radius: 0 0 3px 3px;
	-moz-border-radius: 0 0 3px 3px;
	border-radius: 0 0 3px 3px;
}



/*
 * 
 * Tooltip / notification element
 * 
 */



.tooltip {
	background: #50b1d4;
	padding: 2px;
	margin-bottom: 15px;
	border: 1px solid #338ead;
	border-bottom-width: 3px;
	color: #fff;
	text-align: left;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
}

.tooltip > span {
	display: block;
	padding: 9px 5px 9px 35px;
	background: url("http://icons.jcore.net/32/Orange Ball.png") no-repeat 0px 60%;
}

.tooltip a {
	color: #fff;
}

.tooltip.success {
	background: #5ec53f;
	border-color: #4eb52f;
	color: #fff;
}

.tooltip.success > span {
	background: url("http://icons.jcore.net/32/emblem-default.png") no-repeat 0px 60%;
}

.tooltip.error {
	background: #f2432e;
	border-color: #d91b0b;
	color: #ffffff;
}

.tooltip.error > span {
	background-image: url("http://icons.jcore.net/32/dialog-error.png");
}

.tooltip.notification {
	background: url("http://jcore.net/template/images/buttonbg.jpg") repeat-x 0 100% #fff;
	border-color: #d4d4d4;
	color: #000;
}

.tooltip.notification > span {
	background-image: url("http://icons.jcore.net/32/Get Info.png");
}

.tooltip.notification a {
	color: #50b1d4;
}



/*
 * 
 * Buttons
 * 
 */



.button {
	float: left;
	margin-right: 5px;
	overflow: hidden;
	background: url("http://jcore.net/template/images/buttonbg.jpg") repeat-x 0 100% #fff;
	color: #919191;
	border: 1px solid #d4d4d4;
	font-weight: bold;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	text-shadow: 1px 1px 1px #fff;
}

.button:hover {
	color: #000;
	background: url("http://jcore.net/template/images/inputbg.jpg") repeat-x 0 0 #fff;
}

.button.submit {
	background: url("http://jcore.net/template/images/buttonbluebg.jpg") repeat-x #50b1d4;
	border: 1px solid #338ead;
	color: #fff;
	text-shadow: 1px 1px 1px #338ead;
}

.button.submit:hover {
	color: #fff;
	background: url("http://jcore.net/template/images/buttonbluefocusedbg.jpg") repeat-x #50b1d4;
}

.button a {
	display: block;
	padding: 7px 15px;
	color: #919191;
	text-decoration: none;
}

.button a:hover {
	color: #000;
}

.button.submit a,
.button.submit a:hover
{
	color: #fff;
}

input.button {
	float: none;
	padding: 7px 15px;
}

input.button:hover {
	cursor: pointer;
}



/*
 * 
 * Frame containers
 * 
 */
 


.fc {
	clear: both;
	background: url("http://jcore.net/template/images/fcbg.jpg") repeat-x 0 100% #fff;
	border: 1px solid #eee;
	margin-bottom: 10px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
}

.fc.hidden {
	display: none;
}

.fc-title {
	background: url("http://icons.jcore.net/16/colapsed-black.png") 7px 6px no-repeat;
	padding: 7px 15px 7px 30px;
	display: block;
	color: #919191;
	font-weight: bold;
	text-decoration: none;
	cursor: pointer;
	text-shadow: 1px 1px 1px #fff;
	zoom: 1;
}

.fc.expanded > .fc-title, 
.fc-title.expanded
{
	background-image: url("http://icons.jcore.net/16/expanded-black.png");
}

.fc.colapsed > .fc-title, 
.fc-title.colapsed
{
	background-image: url("http://icons.jcore.net/16/colapsed-black.png");
}

.fc-title:hover {
	color: #000;
	background-image: url("http://icons.jcore.net/16/colapsed-focused-black.png");
}

.fc.expanded > a:hover,
.fc-title.expanded:hover
{
	background-image: url("http://icons.jcore.net/16/expanded-focused-black.png");
}

.fc-content {
	padding: 10px;
	display: none;
}

.fc.expanded > .fc-content {
	display: block;
}

.fc-content .form-entry .form-entry-title {
	width: 110px;
}



/*
 * 
 * Website forms to submit 
 * 
 */



.form {
	margin-top: 20px;
}

.form, .form td {
	color: #555;
}

.form-title {
	padding: 7px 15px;
	font-weight: bold;
	background: url("http://jcore.net/template/images/buttonbluebg.jpg") repeat-x #50b1d4;
	border: 1px solid #50b1d4;
	border-bottom: 0;
	color: #fff;
	text-shadow: 1px 1px 1px #338ead;
}

.form-content {
	background: url("http://jcore.net/template/images/inputbg.jpg") repeat-x 0 0 #fff;
	border: 1px solid #eee;
	border-top: 1px solid #fff;
	padding: 10px 15px;
}

.form-entry {
	clear: both;
	padding-bottom: 10px;
}

.form-entry.last {
	padding-bottom: 0;
}

.form-entry .form-entry-title {
	float: left;
	text-align: right;
	width: 120px;
	padding: 5px 10px 10px 0;
}

.security-image {
	margin-bottom: 5px;
}

.security-image img {
	border: 2px solid #F9E98E;
}

.security-image .reload-link {
	position: relative;
	top: -10px;
	left: 5px;
}

.add-link {
	padding: 0 0 5px 20px;
	background: url("http://icons.jcore.net/16/add.png") no-repeat;
}

.remove-link {
	padding: 0 0 5px 20px;
	background: url("http://icons.jcore.net/16/cross.png") no-repeat;
}

.reload-link {
	padding: 0 0 5px 20px;
	background: url("http://icons.jcore.net/16/arrow_refresh.png") no-repeat;
}

.show-calendar-input,
.show-color-input
{
	text-decoration: none;
	cursor: pointer;
	font-size: 16px;
	padding: 0px 0px 0px 16px;
	margin: 0px 0px 0px 10px;
	background: url("http://icons.jcore.net/16/calendar_2.png") no-repeat;
}

.show-color-input {
	background-image: url("http://icons.jcore.net/16/control_wheel.png");
}

.clear-calendar-input,
.clear-color-input
{
	text-decoration: none;
	cursor: pointer;
	font-size: 16px;
	padding: 0px 0px 0px 16px;
	background: url("http://icons.jcore.net/16/cross.png") no-repeat;
}

.form-footer p {
	margin-bottom: 0;
}



/*
 * 
 * Tables with list elements 
 * 
 */



.list {
	margin: 0;
	border-spacing: 0;
	width: 100%;
	border: 1px solid #eee;
}

.list .list {
	background: transparent;
}

.list .order-id-entry {
	width: 30px;
}

.list tbody tr {
	background: url("http://jcore.net/template/images/fcbg.jpg") repeat-x 0 100% #fff;
}

.list tbody tr:hover,
.list tbody tr.hilight 
{
	background: #fff;
}

.list tbody td {
	border-top: 1px solid #eee;
	padding: 3px 5px 3px 5px;
	width: 1px;
}

.list tbody td.auto-width {
	width: 100%;
}

*+html .list tbody td.auto-width {
	width: auto;
}

.list thead tr {
	padding: 7px 15px;
	font-weight: bold;
	background: url("http://jcore.net/template/images/buttonbluebg.jpg") repeat-x #50b1d4;
}

.list thead th {
	padding: 7px;
	font-size: 80%;
	font-weight: normal;
	text-align: left;
	width: 1px;
	white-space: nowrap;
	color: #fff;
}



/*
 * 
 * Progress bar 
 * 
 */



.progressbar {
	background: url("http://jcore.net/template/images/fcbg.jpg") repeat-x 0 100% #fff;
	border: 1px solid #eee;
}

.progressbar-value {
	background: url("http://jcore.net/template/images/buttonbluebg.jpg") repeat-x 0 100% #50b1d4;
	border: 1px solid #338ead;
	color: #fff;
	text-shadow: 1px 1px 1px #338ead;
	overflow: hidden;
	font-size: 90%;
}

.progressbar-value span {
	text-align: right;
	display: block;
	padding: 2px 3px;
}



/*
 * 
 * Javascript calendar
 * 
 */



.ui-datepicker {
	background: #fff;
	border: 1px solid #5179bc;
	display: none;
	width: 200px;
	text-align: center;
}

.ui-datepicker-header {
	color: #fff;
	background: #5179bc;
	padding: 5px;
	text-align: center;
}

.ui-datepicker-prev {
	cursor: pointer;
	float: left;
	background: url("http://icons.jcore.net/16/arrow_left.png") no-repeat;
	width: 16px;
	height: 16px;
}

.ui-datepicker-prev span {
	display: none;
}

.ui-datepicker-next {
	cursor: pointer;
	float: right;
	background: url("http://icons.jcore.net/16/arrow_right.png") no-repeat;
	width: 16px;
	height: 16px;
}

.ui-datepicker-next span {
	display: none;
}

.ui-datepicker-title {
	display: inline;
}

.ui-datepicker-calendar {
	padding: 5px;
}

.ui-datepicker-calendar th {
	font-weight: normal;
	font-size: 12px;
	color: #999;
}

.ui-datepicker-calendar td {
	border: 1px solid #ccc;
	text-align: center;
}

.ui-datepicker-calendar td a {
	text-decoration: none;
	padding: 1px 3px;
	color: #3159ac;
}

.ui-datepicker-calendar td a:hover {
	color: #000;
}

.ui-datepicker-calendar td.ui-datepicker-today {
	border: 1px solid #dd7a64;
	background: #ffeeee;
}

.ui-datepicker-calendar td.ui-datepicker-today a {
	color: #000;
}

.ui-datepicker-calendar td.ui-datepicker-current-day {
	background: #5179bc;
	border-color: #5179bc;
}

.ui-datepicker-calendar td.ui-datepicker-current-day a {
	color: #fff;
}

.ui-datepicker-calendar td.ui-datepicker-other-month {
	border: 0px;
}



/*
 * 
 * Javascript Color Picker 
 * 
 */



.colorpicker {
	width: 210px;
	height: 205px;
	overflow: hidden;
	position: absolute;
	display: none;
	background: #fff;
	border: 1px solid #5179bc;
}

.colorpicker_color {
	width: 150px;
	height: 150px;
	left: 14px;
	top: 40px;
	position: absolute;
	background: #f00;
	overflow: hidden;
	cursor: crosshair;
	border: 1px solid #cccccc;
}

.colorpicker_color div {
	position: absolute;
	top: 0;
	left: 0;
	width: 150px;
	height: 150px;
	background: url("http://icons.jcore.net/custom/colorpicker-overlay.png");
}

.colorpicker_color div div {
	position: absolute;
	top: 0;
	left: 0;
	width: 11px;
	height: 11px;
	overflow: hidden;
	background: url("http://icons.jcore.net/custom/colorpicker-select.gif");
	margin: -5px 0 0 -5px;
}

.colorpicker_hue {
	position: absolute;
	top: 40px;
	left: 171px;
	width: 20px;
	height: 150px;
	cursor: n-resize;
	background: url("http://icons.jcore.net/custom/colorpicker-slider.png");
	border: 1px solid #ccc;
}

.colorpicker_hue div {
	position: absolute;
	width: 20px;
	height: 9px;
	overflow: hidden;
	background: url("http://icons.jcore.net/custom/colorpicker-indic.gif") left top;
	margin: -4px 0 0 0;
	left: 0px;
}

.colorpicker_new_color {
	display: block;
	height: 23px;
	border-bottom: 1px solid #5179bc;
}

.colorpicker_hex {
	text-align: center;
}

.colorpicker_hex input {
	position: relative;
	top: -19px;
	color: #fff;
	background: transparent;
	border: 0;
	margin: 0;
	padding: 0;
	text-shadow: 1px 1px 1px #000;
	filter: Shadow(Color=#000000, Direction=135, Strength=1);
}

.colorpicker_current_color,
.colorpicker_field
{
	display: none;
}



/*
 *
 * Counter icon
 *
 */



.counter {
	background: url("http://icons.jcore.net/16/counter_bgr.png") 100% 0 no-repeat;
	display: block;
	height: 16px;
	font-size: 10px;
	color: #333;
	position: absolute;
	z-index: 0;
	overflow: hidden;
}

.counter > span {
	height: 16px;
	padding: 0 0 0 4px;
	margin-right: 8px;
	display: block;
	background: url("http://icons.jcore.net/16/counter_bgl.png") no-repeat;
}

.counter > span > span {
	position: relative;
	left: 2px;
	top: 2px;
}



/*
 * 
 * Tipsy, a small tooltip 
 * 
 */



.tipsy {
	padding: 5px;
	font-size: 10px; 
	position: absolute;
	z-index: 100000;
}

.tipsy-inner { 
	padding: 5px 8px 4px 8px; 
	background-color: black; 
	color: white; 
	max-width: 200px; 
	text-align: center;
}

.tipsy-inner {
	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
}

.tipsy-arrow {
	position: absolute;
	background: url('http://icons.jcore.net/custom/tipsy.gif') no-repeat top left;
	width: 9px;
	height: 5px;
}

.tipsy-n .tipsy-arrow {
	top: 0; 
	left: 50%; 
	margin-left: -4px;
}

.tipsy-nw .tipsy-arrow {
	top: 0; 
	left: 10px;
}

.tipsy-ne .tipsy-arrow {
	top: 0; right: 10px;
}

.tipsy-s .tipsy-arrow {
	bottom: 0;
	left: 50%;
	margin-left: -4px;
	background-position: bottom left;
}

.tipsy-sw .tipsy-arrow {
	bottom: 0;
	left: 10px;
	background-position: bottom left;
}

.tipsy-se .tipsy-arrow {
	bottom: 0;
	right: 10px;
	background-position: bottom left;
}

.tipsy-e .tipsy-arrow {
	top: 50%; 
	margin-top: -4px; 
	right: 0; 
	width: 5px; 
	height: 9px; 
	background-position: top right;
}

.tipsy-w .tipsy-arrow {
	top: 50%;
	margin-top: -4px;
	left: 0;
	width: 5px;
	height: 9px;
}



/*
 * 
 * qTip Modifications so our content looks good here too 
 * 
 */



.qtip-wrapper {
	z-index: 1;
}

.qtip-tip {
	z-index: 2;
}

.qtip-button {
	display: block;
	width: 16px;
	height: 16px;
	background: url("http://icons.jcore.net/16/cross.png") no-repeat;
}

.qtip-button span {
	display: none;
}

.qtip .form {
	margin: 0;
}

.qtip .form-title {
	margin: 0;
	border: 0;
	padding: 5px;
	background: transparent;
	color: #000;
	text-shadow: none;
}

.qtip .form-content {
	border: 0;
	padding: 5px;
	background: transparent;
	color: #000;
}

.qtip .form-entry .form-entry-title {
	width: auto;
	float: none;
	text-align: left;
	padding-bottom: 3px;
}

.qtip .form-entry-scimagecode .comment {
	display: none;
}

.qtip .tooltip {
	margin: 0;
}

.qtip .paging-text { 
	color: #A27D35;
}

.qtip .pagenumber a, 
.qtip .pagenumber.pagenumber-selected a:hover 
{
	background: transparent;
	border: 0;
	color: #A27D35;
}

.qtip .pagenumber a:hover, 
.qtip .pagenumber.pagenumber-selected a 
{
	background: #e7cE6D;
	border: 0;
	color: #000000;
}



/*
 * 
 * Website related elements 
 * 
 */



#background {
	background: url("http://jcore.net/template/images/topmenublackbg.jpg") repeat-x #f3f3f3;
}

#website {
	width: 965px;
	margin: 0 auto;
}

.bookmarking {
	float: right;
	margin: 18px 15px 0 0;
}

#header {
	height: 252px;
	overflow: hidden;
	background: url("http://jcore.net/template/images/contenttop.jpg") 50% 233px no-repeat;
}

#header-menu {
	height: 54px;
}

.header-image {
	clear: both;
	height: 100px;
	overflow: hidden;
	background: url("http://jcore.net/template/images/header.jpg") 49% 0 no-repeat;
}

.header-image a {
	position: absolute;
	display: block;
	color: #fff;
	text-decoration: none;
	z-index: 10;
	margin: 70px 0 0 12px;
	font-weight: bold;
	font-size: 18px;
}

.logo {
	float: left;
	margin: 50px 0 40px 10px;
}

.logo a {
	display: block;
	width: 145px;
	height: 43px;
	background: url("http://jcore.net/template/images/logo.png") -8px -8px no-repeat;
	float: left;
}

.slogan {
	width: 318px;
	height: 43px;
	background: url("http://jcore.net/template/images/slogan.png") 20px 15px no-repeat;
	float: left;
	border-left: 1px solid #ddd;
	margin-left: 20px;
}

#content {
	background: url("http://jcore.net/template/images/contentbg.jpg") 50% 0 repeat-y;
	padding-bottom: 1px;
}

#content-bar {
	padding: 20px 0px 1px 0px;
	margin: 0 40px;
	overflow: hidden;
}

#footer {
	background: url("http://jcore.net/template/images/contentbottom.jpg") 50% 0 no-repeat;
	color: #919191;
	width: 965px;
	height: 130px;
	margin: 0 auto;
	overflow: hidden;
}

.footercontent {
	padding: 70px 30px 30px 30px;
	font-size: 90%;
}



/*
 * 
 * Main Menu and Side Menu 
 * 
 */



#main-menu,
#main-menu ul
{
	list-style-type: none;
	margin: 0;
	padding: 0;
}

#main-menu .menu {
	float: left;
	font-size: 12px;
}

#main-menu .menu a {
	display: block;
	color: #fff;
	text-decoration: none;
	white-space: nowrap;
	text-transform: uppercase;
	height: 54px;
	overflow: hidden;
}

#main-menu .selected > a,
#main-menu a:hover
{
	background: url("http://jcore.net/template/images/topmenubg.jpg") repeat-x #46a8cd;
	text-shadow: 1px 1px 1px #187faf;
}

#main-menu .menu span {
	padding: 20px 25px;
	display: block;
}

#main-menu .sub-menu {
	display: none;
	position: absolute;
	background: url("http://jcore.net/template/images/submenubg.png");
	z-index: 11;
}

#main-menu .sub-menu .last {
	border-bottom: 3px solid #46a8cd;
}

#main-menu .sub-menu .menu {
	float: none;
	background: none;
}

#main-menu .sub-menu .menu a {
	background: none;
	text-shadow: 0px 0px 0px;
	height: auto;
	text-transform: none;
	overflow: visible;
}

#main-menu .sub-menu .menu a:hover {
	background: url("http://jcore.net/template/images/topmenubg.jpg") 0% 100% repeat-x #46a8cd;
	text-shadow: 1px 1px 1px #187faf;
	text-decoration: none;
}

#main-menu .sub-menu .menu span {
	padding: 15px 25px;
}

.jcore-versions {
	float: right;
	width: 400px;
	overflow: hidden;
	text-align: right;
	margin: 50px 10px 0 0;
}

.jcore-versions .installer {
	font-weight: bold;
	font-size: 150%;
}

.form {
	margin-top: 0;
}

.form-title {
	background: none;
	border: 0;
	color: #000;
	text-shadow: 0px 0px 0px;
	font-size: 200%;
	font-weight: normal;
	margin: 0;
	padding: 0;
}

.form-content {
	padding: 10px 0 0 0;
	background: none;
	border: 0;
}

.form-entry-content label {
	display: block;
}

.form-entry-clientmodules .form-entry-content {
	margin-left: 120px;
}
</style>
</head>
<body>
<div id='background'>
	<div id='website'>
		<div id='header-menu'>
			<ul id='main-menu'>
				<li class='menu'>
					<a href='install.php?check=1'><span>Check for Updates</span></a> 
				</li>
				<li class='menu'>
					<a href='http://jcore.net/help/installer' target='_blank'><span>Get Help</span></a> 
				</li>
				<li class='menu'>
					<a href='http://jcore.net/contact' target='_blank'><span>Contact</span></a> 
				</li>
				<li class='menu'>
					<a href='http://jcore.net' target='_blank'><span>jCore.net</span></a> 
				</li>
			</ul>
		</div>
		<div id='header'>
			<div class='jcore-versions'>
				<span class='installer'>Installer ver. <?php echo INSTALLER_VERSION; ?></span><br />
				<span class='jcore-version-server'>Server 
				<?php 
					if (isset($_COOKIE['jCoreInstaller']['Versions']['Server'])) 
						echo $_COOKIE['jCoreInstaller']['Versions']['Server']; 
					else 
						echo "-.-"; 
				?>
				</span> -
				<span class='jcore-version-client'>Client 
				<?php 
					if (isset($_COOKIE['jCoreInstaller']['Versions']['Client'])) 
						echo $_COOKIE['jCoreInstaller']['Versions']['Client']; 
					else 
						echo "-.-"; 
				?>
				</span> -
				<span class='jcore-version-installer'>Installer 
				<?php 
					if (isset($_COOKIE['jCoreInstaller']['Versions']['Installer'])) 
						echo $_COOKIE['jCoreInstaller']['Versions']['Installer']; 
					else 
						echo "-.-"; 
				?>
				</span>
			</div>
			<div class='logo'> 
				<a href='<?php echo url::uri('ALL'); ?>'></a> 
				<div class='slogan'></div> 
			</div> 
			<div class='header-image'> 
				<a class='tipsy' href='http://sea-weed.deviantart.com/' target='_blank' title='Picture Copyright &copy; by Iliana (~sea-weed)'>&copy;</a> 
			</div>
		</div>
		<div id='content'>
			<div id='content-bar'>
				<div id='pagecontent'>
				<?php
					$installer = new installer();
					$installer->display();
					unset($installer);
				?>
				</div>
			</div>
		</div>
	</div>
</div>
<div id='footer'>
	<div class='footercontent'> 
		<div class='copyright'>
			Copyright &copy; 2009 by Istvan Petres<br /> 
			All Rights Reserved
		</div>
	</div>
</div>
<script src='<?php echo JCORE_URL; ?>static.php?request=js&amp;installer-v<?php echo INSTALLER_VERSION; ?>' type='text/javascript'></script>
</body>
</html>