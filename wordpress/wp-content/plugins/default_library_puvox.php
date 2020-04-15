<?php
/**
 *		 #################################################################
 *		 ############### Puvox.software [ Coder: T.Todua ] ############### 
 *		 ########## Base Library & Classes for all our plugins. ##########
 *		 #################################################################
 *
 *
 *  PLEASE NOTE: 
 *     Due to the increased numbers of our clients, for whom we have built custom plugins, themes or websites, we have had a necessity to make a central library for our developments. So, we decided to put all our shared functions and plugins bases in this file (even though made on-the-fly and is not organized), this library includes the every-day used functions for our developers. We strive to release secure and easily maintenable plugins. However, some users might find this library file a bit frustrating, because of the functions this library includes. However, we say again and clarify, that part of functions in this library are just for a reference (not actually used in any our production plugin), so only during development or on private custom plugins, some of the functions can be used by developers during temporary testings. That's why we needed that.
 *	
 *				There are three classes:
 *		1- Library of useful PHP functions
 *		2- Library of useful WP-specific functions
 *		3- Main base for our plugins (neccessary initializations,hooks & etc)
 *
 *  P.S: We even advise every plugin developer to have their own re-usable library(class) file. You can even copy this library to build yours.  If you need more details, ask us a question on WP forums.
*/



/**
 *
 * @package   Puvox.software - reusable PHP class
 * @author    T.Todua <contact@puvox.software>
 * @license   GPL-3.0+
 * @link      https://puvox.software
 * @copyright Puvox.software
 *
*/





//==========================================================================================================
//==========================================================================================================
//======================================== 1) Library of PHP functions  ====================================
//==========================================================================================================
//==========================================================================================================

#region 1
if(!class_exists('standard_php_library__PuvoxSoftware')) {
class standard_php_library__PuvoxSoftware 
{
	
	public function property($propertyName)
	{
		return property_exists($this, $propertyName) ? $this->{$propertyName} : null;
	}
	
	public function __construct($args=[])
	{
		$this->ip				= $this->get_visitor_ip();
		$this->isMobile			= false;
		$this->isWP				= defined("ABSPATH");
		// Because this is a trait, we don't use "__FILE__" & "__DIR__" here, but "Reflection" to refer to caller file ####
		$reflection				= (new \ReflectionClass( $args['class'] ));
		$this->plugin_NAMESPACE	= $reflection->getNamespaceName(); 				// get parent's namespace name
		$this->moduleFILE		= $reflection->getFileName(); 					// set plugin's main file path
		$this->moduleDIR		= dirname($this->moduleFILE);					// set plugin's dir	path
		$this->prefix			= strtolower( preg_replace('![^A-Z]+!', '', $this->plugin_NAMESPACE) );// get prefix from current namespace initials of UpperCase characters (i.e. MyPluginNamespace-->MPN)
		$this->prefix_			= $this->prefix .'_';

		// if this class is used just as a helper php library
		if (!$this->isWP)
		{
			//public function initialize_construct()
			{
				$this->wpURL 			= "";
				$this->wpFOLDER 		= "";
				$this->homeURL 			= $this->wpURL;
				$this->homeFOLDER 		= $this->wpFOLDER;
				$this->pluginURL		= $this->wpURL;
			}
			$this->initialize_construct();
		}
		// else, if this class is used as plugin trait (used mostly by Puvox.Software)
		else
		{ 
			$this->wpURL 			= network_home_url('/');						// WP installation home 
			$this->wpFOLDER 		= network_home_url('/', 'relative');			// WP folder 
			$this->homeURL			= home_url('/');								// current sub/site home url
			$this->homeFOLDER		= home_url('/', 'relative');					// current sub/site home folder
			//
			//$trace=debug_backtrace();  define('plugin_main_indexfile', $trace[0]['file']); 
			$this->pluginURL		= plugin_dir_url($this->moduleFILE);			//	
			$this->themeURL			= get_template_directory_uri().'/';				// 
			$this->themeDIR			= get_template_directory();						//
			$this->adminURL			= $this->homeURL.'/wp-admin';					//
		}
		$this->baseDIR			= $this->moduleDIR;									//
		$this->baseURL			= property_exists($this, 'base_path') ? $this->base_path : $this->pluginURL; //( stripos(__FILE__, 'wp-content'.DIRECTORY_SEPARATOR.'themes') !== false ? themeURL ... 

		$this->is_https			= ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || $_SERVER['SERVER_PORT']==443);
		$this->httpsCurrent		= $this->is_https ? 'https://' : 'http://';
		$this->httpsReal		= preg_replace('/(http(s|):\/\/)(.*)/i', '$1', $this->homeURL);  
		$this->domainCurrent	= $_SERVER['HTTP_HOST'];
		$this->domainReal		= $this->getDomain($this->homeURL);
		$this->domain			= $this->httpsReal.$this->domainReal;
		$this->domain_schemeless= '//'.$this->domainReal;
		$this->siteslug			= str_ireplace('.','_',   $this->domainReal);
		$this->requestURI		= $_SERVER["REQUEST_URI"];
		$this->currentURL		= $this->domain.$this->requestURI;
		$this->urlAfterHome		= substr($this->requestURI, strlen($this->homeFOLDER) );
		$this->pathAfterHome	= parse_url($this->urlAfterHome, PHP_URL_PATH);
		$this->homeUrlStripped	= $this->stripUrlPrefixes($this->homeURL);
		$this->is_localhost 	= (stripos($this->homeURL,'://127.0.0.1')!==false || stripos($this->homeURL,'://localhost')!==false );
		$this->Default_Post_Thumb_Imagee= 'https://i.imgur.com/WMDqOvL.png'; // default post image thumbnail (for 1px: https://i.imgur.com/faqQ49G.png )
		$this->site_favicon 	= 'https://i.imgur.com/WMDqOvL.png'; 			 // favicon (for 1px: https://i.imgur.com/faqQ49G.png )
		
		// others
		$this->is_development 	= defined("_puvox_machine_") ;			// set only in devmachine (in "my_superglobals.php" and in "EnvVariables")
		if($this->is_development)	$this->display_errors();
		$this->test_environment = in_array( $this->domainReal, ['localhost','l','127.0.0.1'] ); 

		$backtrace = debug_backtrace(); 
		$this->_index_file_		= $backtrace[0]['file'];
		$this->_index_dir_		= dirname($this->_index_file_);
		$this->changeable_JS_CSS_version =  (file_exists($file = $this->moduleDIR.'/style-public.css') ? 'date_'.filemtime($file) : sanitize_key($this->domainReal).date('m') );

		$this->check_analytics();
		
		if ($this->is_development)
		{
			register_shutdown_function( function(){ die('<div data-debug-memory-limit="'. ini_get('memory_limit').'" data-debug-WP_MEMORY_LIMIT="'. WP_MEMORY_LIMIT.'"></div>');} );
			$this->js_debugmode("debugmode");
			$this->START_TIME1 = microtime(true);
			register_shutdown_function( function(){ die('<div data-debug-time-load="'. (microtime(true)-$this->START_TIME1).'"></div>');} );
		}
		
		// This is not enabled, unless user explicitly enables it during tests!!! IT IS NOWHERE ENABLED, UNLESS YOU INSERT IN CODE YOURSELF. so, don't fear.
		if ($this->property("enable_write_logs")) { SaveLogs( dirname(dirname(__DIR__)) .'/___logs_' ); }
	}

	public function definedTRUE($var)				{return (defined($var) && constant($var));}	
	public function definedVALUE($var,$value=NULL)	{return (defined($var) ? constant($var) : (!is_null($value) ? $value : false  )  );}
	public function globalTRUE($var,$value=NULL)	{return (array_key_exists($var,$GLOBALS) && $GLOBALS[$var]);}
	public function globalVALUE($var,$value=NULL)	{return (array_key_exists($var,$GLOBALS) ? $GLOBALS[$var] : (!is_null($value) ? $value : false) );}
	
	//WP immitations
	public function add_filterX($a=null,$b=null,$c=null,$d=null)	{if(function_exists('add_filter')) 		return add_filter($a,$b,$c,$d);  	}
	public function add_actionX($a=null,$b=null,$c=null,$d=null)	{if(function_exists('add_action')) 		return add_action($a,$b,$c,$d);  	}
	public function add_shortcodeX($a=null,$b=null,$c=null,$d=null)	{if(function_exists('add_shortcode'))	return add_shortcode($a,$b,$c,$d);  }
	public function constantX($var){return $this->definedVALUE($var);}

	public function headers_()
	{
		// 	ini_set('session.cookie_httponly', 1);		
			//always display as new
		//	header("Cache-Control: no-cache, must-revalidate, max-age=0");
			//expired in past
		//	header("Expires: ".			date	('D, d M Y H:i:s', time() - 86400 *2) . " GMT");
		//	header("Vary: Accept-Encoding");
		//	header("Last-Modified: ".	gmdate	("D, d M Y H:i:s", time() - 86400 *2) . " GMT"); 

		//ob_start('ob_gzhandler');} } }
		//similar as: ini_set('zlib.output_compression', '1');
		
	}

	public function max_upload()
	{
		//$this->upload_max_limit , $this->definedVALUE('MAX_UPLOAD_SIZEE',5) 
		//ini_set('post_max_size', $this->upload_max_limit.'M'); ini_set('upload_max_filesize', upload_max_limit.'M');   ini_set('upload_max_size', upload_max_limit.'M'); 
	}

	public function timezones()
	{
		//set timezone for  //date_default_timezone_set('Etc/GMT+4');	
		// our default thumbnail size  :  set_post_thumbnail_size( 211, 138 );	
	}
	

	//timers
	public function timerstart()	{ echo '<pre>'.$this->decimal_outputer( $GLOBALS['timer_started']	=microtime(true) ).'</pre>';	}
	public function timermiddle()	{ echo '<pre>'.$this->decimal_outputer( $GLOBALS['timer_middle']	=microtime(true) ).'</pre>';	}
	public function timerend()		{ 
							echo '<pre>'.$this->decimal_outputer( $GLOBALS['timer_ended']	=microtime(true) ).'</pre>'; 
		if(!empty($GLOBALS['timer_middle'])) {
			echo $this->decimal_outputer($first=$GLOBALS['timer_middle']-$GLOBALS['timer_started'] );	 echo ' ------(middle-start)<br/> '; 
			echo $this->decimal_outputer($second=$GLOBALS['timer_ended']-$GLOBALS['timer_middle'] ) ;	 echo ' ------(end-middle)<b style="color:red;">' . round( max($first,$second)/min($first,$second), 2)  . '</b>x slower<br/>';  
		}
		echo $this->decimal_outputer($GLOBALS['timer_ended'] - $GLOBALS['timer_started'] ) . ' (end-start)';
		exit;	
	}
	public function timernow($name){
		echo $name . ": "; 
		$now =  floatval(microtime(true));
		if(empty($GLOBALS['lastime'])) echo "start" ; else  $this->decimal_outputer($now   - $GLOBALS['lastime'] ) ;
		$GLOBALS['lastime']=  floatval(microtime(true));
		echo "\r\n";
	}	


	//$time_start = microtime_float();
	//usleep(100);
	//$time_end = microtime_float();
	public function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	/*
	example: 
	public function My_Test1(){
		add_actionX('wp',function(){  
			var_dump(microtime(true));
			for ($i=1; $i<100; $i++) { get_option('blogdescription'); }
			var_dump(microtime(true));
			for ($i=1; $i<100; $i++) { get_theme_mod('blogdescription'); }
			var_dump(microtime(true));
			exit;
		});
	}


	public function My_Test2() {
		add_actionX('wp',function(){  

			timerstart();
			for($i=0; $i<100; $i++){
				get_post_meta($GLOBALS['post']->ID, 'smth'.$i, true);
				//$x= $GLOBALS['post']->post_content;
			} 
			timermiddle();
			for($i=0; $i<100; $i++){
				$x= $GLOBALS['post']->post_content;
			} 
			timerend();

			//global $wpdb;
			// $res= $wpdb->query('ALTER TABLE `wp__mlss_translatedwords` ENGINE=MyISAM');
			// $res= $wpdb->query('SHOW CREATE TABLE wp__mlss_translatedwords');
		});
	}

	*/

		
	public function input_fields_from_array_RECURSIVE($value, $keyname='', $replace_spaces=false){		
		if (!is_array($value)){
			$height=30; $lines=explode("\r\n",$value); 
				foreach($lines as $eachLINE){
					$height= $height+ceil(mb_strlen($eachLINE)/100) * 30; 
				}
				// replace multiple whitespaces with single
				$value =   !$replace_spaces ? $value : preg_replace('!\s+!', ' ', str_replace("\t",' ', $value));
			echo 
			'<div class="each_ln">
				<div class="keyname">'.$keyname.'</div>
				<div class="txtar"><textarea class="" style="height:'. $height.'px;" name="'.$keyname.'">'.$value.'</textarea></div>
			</div>';
		}
		else{
			echo '<div class="new_array_title">'.$keyname.'</div>';
			foreach ($value  as $keyname1=>$value1){
				echo '<div class="new_block">';
				$this->input_fields_from_array_RECURSIVE($value1, $keyname.'['.$keyname1.']',  $replace_spaces);
				echo '</div>';
			}
		}
	}




	public function dropdown_for_categories($ul___id_class, $ShowPlusMinusDropdown = false){	
		if (!defined('drp_already_out')) {   define('drp_already_out', true);  ?>
	<style>
	.ChildHidden {} 
	.OPCL_containtr{float:right; display: inline-block; text-align:right; height:30px;width:30px; }
	.drop_CLOSE{background:transparent url("<?php echo $this->baseURL.'library/media/other/sign-minus.png';?>") no-repeat scroll 0% 0%; }
	.drop_OPEN{background:transparent url("<?php echo $this->baseURL.'library/media/other/sign-plus.png';?>") no-repeat scroll 0% 0%;}
	.ChildHidden ul.sub-menu{display:none;}	
	.OpenCloseSp {display: inline-block;height: 30px;width: 30px;}
	zzzz body li.ChildHidden > a {display: inline-block;} 
	</style>
	<script type="text/javascript">
	public function make_element_children_dropdowned(element, ShowPlusMinusSign){
		if (element) {
			element.each(function( index,key ) { 
			  if (key.className.indexOf("menu-item-has-children") >= 0) {
				$( this ).addClass("ChildHidden");
				if (ShowPlusMinusSign) { $(this).children('a').append('<span class="OPCL_containtr"> <span class="OpenCloseSp drop_OPEN"> </span> </span>'); }
				
				$( this ).children('a').click(function() {
					if (ShowPlusMinusSign) { $(this).children('.OPCL_containtr').find('span.OpenCloseSp').toggleClass('drop_OPEN drop_CLOSE'); }
					$(this).siblings('ul.sub-menu').toggle();
					return false;
				});
			  }
			});
		}
	}
	</script>	
	<?php 
		} ?>
		<script type="text/javascript">
		var Containr = $("<?php echo $ul___id_class;?>");
		var ShowPlusMinusSign = false;  <?php if ($ShowPlusMinusDropdown) { ?> ShowPlusMinusSign = true;  <?php } ?>
		make_element_children_dropdowned(Containr,ShowPlusMinusSign);
		</script>
		<?php	
	}

	 
	public function expand_CHILD_menu_by_a_name($ul___a_class, $A_href_NAMEs=array() ){	?>
		<script type="text/javascript">
		var A_names = [<?php foreach ($A_href_NAMEs as $key=>$each) {echo '"'.$each.'"'; if($key != count($A_href_NAMEs)-1) echo ',';  } ?>];
		var Containr2 = $("<?php echo $ul___a_class;?>");
		if (Containr2) { 
			Containr2.each(function( index,key ) { 
			  if (A_names.indexOf(key.innerHTML) > -1) {
				var ff= $(this).siblings('ul.sub-menu').addClass("displayblock");
			  }
			});
		}
		</script>	
		<?php	
	}

	public function force_https(){
		if(!$this->is_https) {  header("Location: https://" . $this->domainReal . $_SERVER["REQUEST_URI"], true, 301); exit;  }
	}
	public function string_to_truefalse($string) { return ( $string ==='true' ? true : ($string ==='false' ? false : $string)); }
	public function truefalse_to_string($string) { return ( $string === true ? 'true' : ($string ===false ? 'false' : $string)); }

	public function string_to_array($string){ return array_map('trim', array_filter( explode(',', $string) ) ); }
	public function array_to_string($array)	{ return implode(",",  array_map('trim',array_filter($array)) ); }

	//convert unsorted array (i.e. [ 'first'=>["a","b","c"], 'second'=>[1,2,3] , ] ) to associative   [ "a"=>1, "b"=2 ]
	public function array_to_associative($array) {
		
	}


	//add_action('wp_footer','a_hashtag_click_change'); 
	public function a_hashtag_click_change(){	
		if (!defined('enable_hashtag_href_changer') || !enable_hashtag_href_changer){ return; }
		?>
		<script type="text/javascript">
		public function MyCallback5(e) { var e = window.e || e; var t=e.target; 
					
			if (t.tagName !== 'A') return;
			else{
				var link=t.href;
				if( link.indexOf('#') >-1) {  //found hashtag
					var hashtag= link.split('#')[1];  //var match = url.match(/#.*[?&]locale=([^&]+)(&|$)/);   return(match ? match[1] : "");(^|\s)(#[a-z\d-]+)
					var sanitized_link= link.replace( location.href.split('#')[0] ,"");
					if(link.indexOf(location.href) >-1 || sanitized_link.charAt(0)=='#') { //if conains current link, or starts with #
						location.hash=hashtag;
					}
				}
			}
		}

		if (document.addEventListener) document.addEventListener('click', MyCallback5, false);
		else document.attachEvent('onclick', MyCallback5);	
		</script>
		<?php 
	}


	public function add_prefix_to_array_keys($array, $prefix){
		$new_array =[];
		foreach ($array as $k => $v) {
			$new_array[$prefix.$k] = $v;
		}
		return $new_array;
	}

	public function get_visitor_ip() {
		$proxy_headers = array("CLIENT_IP", "FORWARDED", "FORWARDED_FOR", "FORWARDED_FOR_IP", "HTTP_CLIENT_IP", "HTTP_FORWARDED", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED_FOR_IP", "HTTP_PC_REMOTE_ADDR", "HTTP_PROXY_CONNECTION", "HTTP_VIA", "HTTP_X_FORWARDED", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED_FOR_IP", "HTTP_X_IMFORWARDS", "HTTP_XROXY_CONNECTION", "VIA", "X_FORWARDED", "X_FORWARDED_FOR");
		foreach($proxy_headers as $proxy_header) {
			if (isset($_SERVER[$proxy_header])) {
				if(preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $_SERVER[$proxy_header])) {
					return $_SERVER[$proxy_header];
				}
				else if (stristr(",", $_SERVER[$proxy_header]) !== FALSE) {
					$proxy_header_temp = trim(array_shift(explode(",", $_SERVER[$proxy_header])));
					if (($pos_temp = stripos($proxy_header_temp, ":")) !== FALSE) {$proxy_header_temp = substr($proxy_header_temp, 0, $pos_temp); }
					if (preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $proxy_header_temp)) { return $proxy_header_temp; }
				}
			}
		}
		return $_SERVER["REMOTE_ADDR"];
	}

	public function arrayToObject($array) { return json_decode(json_encode($array)); }
	public function objectToArray($object){ return json_decode(json_encode($object), true); }

	public function mail_scrambler($email) {  return str_replace('@', '&#64;', $email);}


	public function convert_urls_in_text($text) {
		return preg_replace('@([^\"\']https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#][^<]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $text);
	}

	public function randomString($length = 11) {
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);    //random_stringg($length= 15){ return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);}
	}

	public function PlainString(&$text1=false,&$text2=false,&$text3=false,&$text4=false,&$text5=false,&$text6=false,&$text7=false,&$text8=false){
		for($i=1; $i<=8; $i++){    if(${'text'.$i}) {${'text'.$i} = preg_replace('/\W/si','',${'text'.$i});} 	}
		return $text1;
	}

	public function getDomain($url){
		return preg_replace('/http(s|):\/\/(www.|)(.*?)(\/.*|$)/i', '$3', $url);
	}

	public function adjustedUrlPrefixes($url){
		if(strpos($url, '://') !== false){
			return preg_replace('/^(http(s|)|):\/\/(www.|)/i', 'https://www.', $url);
		}
		else{
			return 'https://www.'.$url;
		}
	}

	public function remove_www($url) 	{ 
		return str_replace( ['://www.'], '://', $url ); 
	}

	public function remove_https_www($url){
		return str_replace( ['https://www.','http://www.','http://','https://'], '', $url ); 
	}

	public function normalize_with_slashes($url, $add_trailing_slash=true){ 
		return rtrim( $this->OneSlash($url), '/')  . ($add_trailing_slash ? '/' : '') ; 
	}

	public function OneSlash($url){
		$prefix='';
		if(substr($url,0,2)=='//'){
			$prefix = '//';
			$url=substr($url,2);
		}
		return $prefix.preg_replace( '/([^:])\/\//',  '$1/', $url);
	}
	
	public function stripUrlPrefixes($url){
		return preg_replace('/http(s|):\/\/(www.|)/i', '',  $url);
	}

	public function stripDomain($url){
		return str_replace( $this->adjustedUrlPrefixes($this->domainReal), '', $this->adjustedUrlPrefixes($url) );
	}

	// i.e. 5m, 1H, 2H, 1D, 240M, etc...
	public function stockTF_to_seconds($string, $minuteIs="m", $monthIs="M"){
		$res=$string;
		$arr=[$minuteIs=>1, 'h'=>60, 'H'=>60, 'd'=>24*60, 'D'=>24*60, 'w'=>7*24*60, 'W'=>7*24*60, $monthIs=>31*24*60, 'y'=>365*24*60, 'Y'=>365*24*60];
		foreach ($arr as $key=>$val) { if (empty($key)) continue; if (strpos($string, $key)!==false) { $res = str_ireplace($key, '', $string) * $val; break; }  }
		$res = $res *60; //into seconds
		return $res;
	}
		
	public function toString($inp){
		return $inp."";
	}
	public function contains_numeric($str){
		$str=$this->toString($str);
		for($i=0; $i<=9; $i++) {
			if (strpos($str, $this->toString($i) )!==false){
				return true;
			}
		}
		return false;
	}
	
	public function dayForTime($time){
		return strtotime(date('Y-m-d', $time));
	}
	
	public function isWeekend($time){
		return date('N',$time) > 5;
	}

	public function safemode_basedir_set(){
		return ( ini_get('open_basedir') || ini_get('safe_mode') ) ;
	}
	public function header($type){
		switch ($type){
			case "json" : header('Content-Type: application/json'); break;
			case "text" : header('Content-Type: text/plain;  charset=utf-8'); break;
			case "js" : header('Content-Type: application/javascript;  charset=utf-8'); break;
		}
	}
	
	public function exitPlain($content, $encode=false){
		self::headerPlain();
		if ($encode) $content = json_encode($content);
		print($content); exit;
	}

	public function exitJson($content, $encode=false){
		self::headerJson();
		if ($encode) $content = json_encode($content);
		exit($content);
	}
	public function try_increase_exec_time($seconds){
		if( ! $this-> safemode_basedir_set() ) {
			@set_time_limit($seconds);
			@ini_set('max_execution_time', $seconds);
			$this->try_increase_memory(512);
			return true;
		}
		return false;
	}
	public function try_increase_memory($limit=512){
		if( ! $this-> safemode_basedir_set() ) {
			$limitBytes = $limit * 1048576;
			$currentLimit = trim(ini_get('memory_limit'));
			$lastChar = strtolower($currentLimit[strlen((int) $currentLimit)-1]);
			switch($lastChar) {
				case 'g': $currentLimit *= 1024;
				case 'm': $currentLimit *= 1024;
				case 'k': $currentLimit *= 1024;
			}
			if ($currentLimit < $limitBytes)
				return ini_set('memory_limit', $limit . 'M');
		}
		return false;
	}


	public function MessageAgainstMaliciousAttempt(){
		return 'Not allowed. Try again.';//'Well... I know that these words won\'t change you, but I\'ll do it again: Developers try to create a balance & harmony in internet, and some people like you try to steal things from other people. Even if you can it, please don\'t do that.';
	}




	public function tmp_name($name){
		return __DIR__.'/_temp/' . $name.'.html';  //$_SERVER['DOCUMENT_ROOT']
	}
	
	public function tempfile($name, $value=false){
		if(!$value){
			$cont = $this->file_get_contents($this->tmp_name($name));
			if( $this->is_JSON_string($cont) ){
				return json_decode($this->file_get_contents($this->tmp_name($name)), true);
			}
			else{
				$this->send_error( "not json" );
			}
		}
		else{
			$this->file_put_contents( $this->tmp_name($name), json_encode($value) );
		}
	}
	

	public function mkdir($dest, $permissions=0755, $create=true){ return $this->mkdir_recursive($dest, $permissions, $create); }
	public function mkdir_recursive($dest, $permissions=0755, $create=true){
		if(!is_dir($dest)){
			//at first, recursively create directory if doesn't exist
			if(!is_dir(dirname($dest))){ $this->mkdir_recursive(dirname($dest), $permissions, $create); }
			mkdir($dest, $permissions, $create); 
		}
		else{return true;}
	}

	public function rmdir($path){ return $this->rmdir_recursive($path); }
	public function rmdir_recursive($path){
		if(!empty($path) && is_dir($path) ){
			$dir  = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS); //upper dirs not included,otherwise DISASTER HAPPENS :)
			$files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $f) {if (is_file($f)) {unlink($f);} else {$empty_dirs[] = $f;} } if (!empty($empty_dirs)) {foreach ($empty_dirs as $eachDir) {rmdir($eachDir);}} rmdir($path);
			return true;
		}
		return true;
		//include_once(ABSPATH.'/wp-admin/includes/class-wp-filesystem-base.php');
		//\WP_Filesystem_Base::rmdir($fullPath, true);
	}
	
	public $cacheDir = false;		//set in parent app, i.e. $class->cacheDir = __DIR__.'/_cache/';
	public $forceNewCache=false;	//set in parent app, i.e. $class->forceNewCache = isset($_GET['_flush_cache']);
	public function cacheDir_(){ return ($this->cacheDir ?: sys_get_temp_dir().'/_cache/'); }
	
	public function cachedFile($callbackFunc, $params=[], $seconds, $force_on_empty=true){
		if(array_key_exists('_uniqueKey', $params)) { $uniqKey=$params['_uniqueKey']; unset($params['_uniqueKey']); }
		$key= md5(json_encode($callbackFunc)) ."_". $this->sanitize_alhpanum(json_encode($params)) . (isset($uniqKey) ? $uniqKey : ''). "_". $seconds;
		$cache_dir = $this->cacheDir_();
		if(!is_dir($cache_dir)){ mkdir($cache_dir, 0755, true); }
		$t=time(); 
		$cache_file = $cache_dir .'_'. $key ;
		$call = false;
		if ($this->forceNewCache || !file_exists($cache_file) ||  time() - filemtime($cache_file) > $seconds )  
		{
			$call=true;
		}
		else{
			$cont = file_get_contents($cache_file);
			if ($cont=="" && $force_on_empty){
				$call=true;
			}
			else{
				$response = unserialize($cont);
			}
		}
		if($call){
			$response= call_user_func_array($callbackFunc, $params);
			$this->file_put_contents($cache_file, serialize($response) );
		}
		return $response;
	}
	
	public function clearCacheDir($seconds=86400){
		$timerFile= $this->cacheDir_().'/_cleanTime.blobz';
		if (file_exists($timerFile) && filemtime($timerFile)<time()-$seconds){
			array_map( 'unlink', array_filter((array) glob( $this->cacheDir_()."*") ) );
			$this->file_put_contents($timerFile, time());
		}
		else{
			$this->file_put_contents($timerFile, time());
		}
	}

	public function cachedMemoryFile($path, $key){
		if (array_key_exists($key, $GLOBALS) ) {
			$content = $GLOBALS[$key];
		}
		else{
			$content = $this->file_get_contents($path);
			$GLOBALS[$key]= $content;
		}
		return $content;	
	}

	public function sanitize_alhpanum($str){
		//Try this to remove everything except a-z, A-Z and 0-9, -, _, .
		return preg_replace("/[^a-zA-Z0-9\-\_\.]+/", "", $str);
	}

	public function copy_recursive($source, $dest, $permissions = 0755){
		if (is_link($source))	{ return symlink(readlink($source), $dest); }
		elseif (is_file($source))	{ 
			if(!file_exists(dirname($dest))){$this->mkdir_recursive(dirname($dest), $permissions, true); }
			if(!copy($source, $dest)) {echo "not copied ($source ---> $dest )";} return true; 
		}
		elseif (is_dir($source))	{ 
			$this->mkdir_recursive($dest, $permissions, true); 
			foreach (glob($source.'/*') as $each){	$basen= basename($each);
				if ($basen != '.' && $basen != '..') { $this->copy_recursive("$each", "$dest/$basen", $permissions);	}
			}
		}
	}

	public function file_get_contents($path, $waitIfLocked=true)
	{
		if (!file_exists($path)) 
			return "";
		else {
			$fo = fopen($path, 'r');
			$locked = flock($fo, LOCK_SH, $waitIfLocked);
			
			if(!$locked) {
				return false; //throw new Exception('File "'.$path.'" does not exists');
			}
			else {
				$txt = file_get_contents($path);
				flock($fo, LOCK_UN);
				fclose($fo);
				return $txt;
			}
		}
	}

	public function file_put_contents($path, $content, $third = null)
	{
		$dir = dirname($path);
		if(!is_dir($dir))  $this->mkdir_recursive($dir); 
		file_put_contents($path, $content, ($third==null ? LOCK_EX : $third + LOCK_EX) );
		return;
		//$f = fopen($path, 'c');  //https://www.php.net/manual/en/function.fopen.php
		//flock($f, LOCK_EX);
		//fwrite($f, $content);
		//flock($f, LOCK_UN);
		//fclose($f);
	}
	
	public function fileNotContainsWrite($path, $text)
	{
		$contains=false;
		if(!file_exists($path))
		{
			$contains=false;
		}
		else{
			$content = $this->file_get_contents($path);
			if ( strpos($content, $txt)===false )
			{
				$contains=false;
				$this->file_put_contents($path, $text);
			}
			else
			{
				$contains=true;
			}
		}
		return $contains;
	}

	
	public function get_option_json($name, $default_value=null){
		$json = file_exists($path = __DIR__.'/site_options.ini') ? $this->file_get_contents($path) : '{}';
		$array= json_decode($json, true);
		return ( array_key_exists($name, $array) ? $array[$name] : $default_value ); 
	}
	
	public function update_option_json($name, $value, $autoload=null){
		return (array_key_exists($name, $array) ? $array[$name] : $default_value ); 
		$json = file_exists($path = __DIR__.'/site_options.ini') ? $this->file_get_contents($path) : '{}';
		$array= json_decode($json, true);
		$array[$name]=$value;
		try { file_write($path, json_encode($array)); } catch (Exception $e){ return false; }
		return true;
	}
	
	
	public function get_option($name, $defaultValue=null){ 
		return function_exists('get_option') ? get_option($name) : get_option_json($name,$defaultValue); 
	}
	public function update_option($name, $value, $autoload=null){  
		return function_exists('get_option') ? update_option($name, $value, $autoload) : update_option_json($name,$value,$autoload); 
	}
	
	public function add_my_site_options($array)
	{ 
		$this->extra_options_enabled=true;
		//Initiate as options
		$trigger_update=false;
		$this->_my_site_options=get_site_option('_my_site_options',[]);
		foreach($array as $key=>$value){
			if (!array_key_exists($key, $this->_my_site_options)) { $this->_my_site_options[$key]=$value;  $trigger_update=1; } 
		}
		if(!empty($trigger_update)) { $this->update_my_site_options(); }
	 
	}
	public function get_my_site_option($name=null, $default=null, $force_update=false)
	{
		$this->_my_site_options=get_site_option('_my_site_options',[]);
		if ($name!=null)
		{
			if (! array_key_exists($name, $this->_my_site_options) || $force_update){
				$this->_my_site_options[$name]=$default;
				$this->update_my_site_options();
			}
			return $this->_my_site_options[$name];
		}
		return $this->_my_site_options;
	}
	public function update_my_site_options($array=false)
	{
		update_option('_my_site_options',  ( $array ? $array : $this->_my_site_options) );
	}
	
	public function fileUrl($file){
		return $this->pluginURL."/$file?vers_=".$this->filedate($this->moduleDIR. "/$file");
	}


	public function FullIframeScript(){ ?>
		<script>
		function MakeIframeFullHeight_tt(iframeElement, cycling, overwrite_margin){
			cycling= cycling || false;
			overwrite_margin= overwrite_margin || false;
			iframeElement.style.width	= "100%";
			var ifrD = iframeElement.contentDocument || iframeElement.contentWindow.document;
			var mHeight = parseInt( window.getComputedStyle( ifrD.documentElement).height );  // Math.max( ifrD.body.scrollHeight, .. offsetHeight, ....clientHeight,
			var margins = ifrD.body.style.margin + ifrD.body.style.padding + ifrD.documentElement.style.margin + ifrD.documentElement.style.padding;
			if(margins=="") { margins=0; if(overwrite_margin) {  ifrD.body.style.margin="0px"; } }
			(function(){
				var interval = setInterval(function(){
				if(ifrD.readyState  == 'complete' ){
					setTimeout( function(){
						if(!cycling) { setTimeout( function(){ clearInterval(interval);}, 500); }
						iframeElement.style.height	= (parseInt(window.getComputedStyle( ifrD.documentElement).height) + parseInt(margins)+1) +"px";
					}, 200 );
				}
				},200)
			})();
				//var funcname= arguments.callee.name;
				//window.setTimeout( function(){ console.log(funcname); console.log(cycling); window[funcname](iframeElement, cycling); }, 500 );
		}
		</script>
		<?php
	}




	// ======== manual stripslashes_deep ========
	public function array_map_recursive(callable $func, $value) {
		return filter_var($value, \FILTER_CALLBACK, ['options' => $func]);
	}
	
	public function array_map_deep( $callback , $value) 
	{
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
					$value[ $index ] = $this->array_map_deep($callback,  $item );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
					$value->$property_name = $this->array_map_deep( $callback, $property_value );
			}
		} else {
			$value = call_user_func( $callback, $value );
		}
		return $value;
	}
	public function stripslashes_from_strings_only( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;	
	}
	public function stripslashes_deep($value){ return $this->array_map_deep([$this,'stripslashes_from_strings_only'] , $value ); }
	// ================================================




	public function cookieFuncs(){
	?>
	<script>
	// ================= create, read,delete cookies  =================
	function Is_Cookie_Set_tt(cookiename) { return document.cookie.indexOf('; '+cookiename+'=');}

	function createCookie_tt(name,value,days) {
		var expires = "";
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days*24*60*60*1000));
			expires = "; expires=" + date.toUTCString();
		}
		document.cookie = name + "=" + (value || "")  + expires + "; path=/";
	}
	function readCookie_tt(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}
	function eraseCookie_tt(name) {
		document.cookie = name+'=; Max-Age=-99999999;';
	}
			function setCookie(name,value,days) { createCookie(name,value,days); }
			function getCookie(name) { return readCookie(name); }
			function setCookieOnce(name) { createCookie(name, "okk" , 1000); }
	// ===========================================================================================
	</script>
	<?php
	}


	//only open and close the same-origin creator of session  (argument can be TRUE/FALSE or STRING too
	public function session_state ($arg) { 
		if($arg===true)	{	if(session_status() == PHP_SESSION_NONE)	{ $GLOBALS['my_session_pp']='sess'.rand(1,99999999); session_start();  return $GLOBALS['my_session_pp']; }   	}     
		else			{	if(session_status() == PHP_SESSION_ACTIVE)	{ if(!$arg || $arg==$GLOBALS['my_session_pp']) session_write_close();  }   	}  
	}
	public function set_session_var ($name,$value) {
		$id= $this->session_state(true);
		$_SESSION[$name] = $value;
		$this->session_state($id);
	}
	
	public function startSessionIfNotStarted(){
		if(session_status() == PHP_SESSION_NONE)  { $this->session_being_opened = true; session_start();  }
	}
	public function endSessionIfWasStarted( $method=2){
		if(session_status() != PHP_SESSION_NONE && property_exists($this,"session_being_opened") )  {
			unset($this->session_being_opened);
			if($method==1) session_destroy();
			elseif($method==2) session_write_close();
			elseif($method==3) session_abort();
		}
	}


	
	public function array_value($array, $key){
		return (array_key_exists($key, $array) ? $array[$key] : '');
	}

	public function nextKeyInArray($target_keyname, $array){
		$keys = array_keys($array);
		$index_of_target_keyname = array_search($target_keyname,  $keys , true);
		return (count($array) > $index_of_target_keyname+1 ) ? $keys[$index_of_target_keyname+1]  :  $keys[0];
	}

	public function nextValueInArray($target_value, $array, $by_key=false){
		$keys = array_keys($array);
		$target_keyname = $by_key ? $target_value : array_search($target_value,  $array, true );
		$index_of_target_keyname = array_search($target_keyname,  $keys, true );
		return (count($array) > $index_of_target_keyname+1 ) ? $array[ $keys[$index_of_target_keyname+1] ]  :  $array[  $keys[0]  ];
	}

	public function getIndexOfKey($array, $key){
		return array_search($key, array_keys($array) );
	}
	public function getIndexOfValue($array, $key){
		return array_search($key, $array );
	}

	public function getMemberByIndex($array, $idx){
		$keys= array_keys($array);
		return (!empty($keys) && !empty($array[$keys[$idx]])) ? $array[$keys[$idx]] : null ;
	}

	public function resortArrayByKey($array, $key, $remove_current= false){
		$remaining =  array_splice ($array, $this->getIndexOfKey($array, $key)   );
		if($remove_current){
			$array[$key]= $remaining[$key];
			unset($remaining[$key] );
		}
		return array_merge($remaining, $array);
	}
	
	//in multi dimensional array
	public function findArrayByKeyValue($array, $key, $value){
		foreach($array as $subArray){
			if (array_key_exists($key, $subArray) && $subArray[$key]==$value){
				return $subArray;
			}
		}
		return [];
	}

	public function insertValueAtPosition($arr, $insertedArray, $position) {
		$i = 0;
		$new_array=[];
		foreach ($arr as $key => $value) {
			if ($i == $position) {
				foreach ($insertedArray as $ikey => $ivalue) {
					$new_array[$ikey] = $ivalue;
				}
			}
			$new_array[$key] = $value;
			$i++;
		}
		return $new_array;
	}


	// Output decimals better, i.e.  $x= 0.000021;  or  $x= 123424235.325434645
	// method 1
	public function trim_zero_dot($input){
		$sanitized=rtrim( $input, "0");
		if(substr($sanitized, -1) =="."){
			$sanitized=substr($sanitized,0, -1);
		}
		return $sanitized;
	}
	//
	public function doubleNormal($input, $round_to=15, $use_sprintf=true){ 
		return (!is_float($input) && !is_numeric($input) ? $input : $this->trim_zero_dot( $use_sprintf ? sprintf("%.{$round_to}f", $input) : number_format($input, $round_to) ) );	
	}
	
	// method 2
	public function decimal_outputer($input, $length=5, $only_dot=false){  
		$timeParts = explode('.', $input);
		if(count($timeParts)<=1) return $input;
		return ($only_dot ? '' : $timeParts[0] . '.') . substr($timeParts[1], 0, $length); //sprintf('%.10F',$input); 
	}
	//
	public function doubleNormalArray($array){
		return $this->array_map_deep([$this,'doubleNormal'], $array);
	}


	public function arrayPhpToJs($array){
		return '["'. implode('","', $array) .'"]';
	}

	public function ListAllInDir($path, $only_files = false) {
		$all_list = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
				( $only_files ? \RecursiveIteratorIterator::LEAVES_ONLY : \RecursiveIteratorIterator::SELF_FIRST )
		);
		$files = [];
		foreach ($all_list as $file)
			$files[] = $file->getPathname();

		return $files;
	}


	public function replace_occurences_in_dir($dir_base, $from, $to, $exts=array('php','shtml') ){
		$dirIterator = $this->ListAllInDir($dir_base, true);
		foreach($dirIterator as $idx => $value) {
			$filext = pathinfo($value, PATHINFO_EXTENSION);
			if( in_array($filext,  $exts ) ){
				$cont = $this->file_get_contents($value);
				if(stripos($cont, $from) !== false){
					$new_cont = str_replace($from, $to, $this->file_get_contents($value) );
					$this->file_put_contents($value, $new_cont);
				}
			}
		}
	}

	public function replace_in_file($file, $from_pattern, $to){
		if(file_exists($file))
		{
			$cont= $this->file_get_contents($file);
			$new_cont= preg_replace($from_pattern, $to, $cont);
			$this->file_put_contents($file, $new_cont);
		}
	}
	
	public function is_localhost() 		{ 
		return in_array($_SERVER['HTTP_HOST'],['localhost','127.0.0.1','::1']); 
	}
	

	public function is_JSON_string($string){
		return (is_string($string) && is_array(json_decode($string, true)));
	}

	public function arrayed_json($answer){
		$result = [];
		if(!$this->is_JSON_string($answer)){
			$result['error'] = $answer;
		}
		else{
			$result = json_decode($answer, true);
		}
		return $result;
	}

	public function arrayed_answer($answer){
		$result = [];
		if(!$this->is_JSON_string($answer)){
			$result['error'] = $answer;
		}
		else{
			$result = json_decode($answer, true);
		}
		return $result;
	}



	// TODO - handle $_POST
	public function disable_cache($hard=false, $file=false){
		header("Expires: Mon, 4 Jan 1999 12:00:00 GMT");        // Expired already 
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");     
		header("Cache-Control: no-cache, must-revalidate");      // good for HTTP/1.1 
		header("Pragma: no-cache"); 
		if($hard){
			if(!isset($_GET['rand']))
				$this->php_redirect( $this->AddStringToUrl($_SERVER['REQUEST_URI'], 'rand='.rand(1,9999999) )   );
		}
		ini_set("opcache.enable", 0); 
		if($file){
			opcache_invalidate($file);
		}
	}


		
	public function my_mail($a=null,$b=null,$c=null,$d=null,$e=null){ return (!$this->definedTRUE("MAILS_DISABLED") ? mail($a,$b,$c,$d,$e) : "MAILS_NOT_ENABLED__error99234"); }

	public function get_yout_Vid_Aud_array($ID,$TITL)	{return yout_DownUrls($ID, $TITL);}

	public function default_mail_headers($from=false){ return $headers='MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n" . 'From: mesg@' .$_SERVER['HTTP_HOST'] ."\r\n".'Reply-To: mesg@'.$_SERVER['HTTP_HOST'] . "\r\n" . "X-Mailer: PHP/" . phpversion(); }	
		


	//use whenever you want to show something on the first happening
	// first_cookie_message('ini_get_noexits','<script>alert("ini_get doesnt work on server. i will hide forever now");</script>')
	public function first_cookie_message($identifier, $message){
		$cName=filter_var($identifier, FILTER_SANITIZE_STRING);
		if (!isset($_COOKIE[$cName])){
			setcookie($cName,'okk',time()+99999999, $this->definedVALUE('homeFOLD','/'));
			die($message);
		}
	}

	public function CookieSet($name){ if (empty($_COOKIE[$name])) { return false;} else { return true;} }
	public function CookieSetOnceExecution($name){ if (empty($_COOKIE[$name])) { setcookie($name, time(), time()+ 999999,  $this->definedVALUE('homeFOLD','/') ); return true; } return false; }
	public function CookieNotSet($name){ CookieSetOnceExecution($name); }

	public function set_cookie($name, $val, $time_length = 86400, $path=false, $domain=false, $httponly=true){
		$site_urls = parse_url( (function_exists('home_url') ? home_url() : $_SERVER['SERVER_NAME']) );
		$real_domain = $site_urls["host"];
		$path = $path ? $path : ( (!empty($this) && property_exists($this,'homeFOLDER') ) ?  $this->homeFOLDER : '/');
		$domain = $domain ? $domain : ((substr($real_domain, 0, 4) == "www.") ? substr($real_domain, 4) : $real_domain);
		setcookie ( $name , $val , time()+$time_length, $path = $path, $domain = $domain,  $only_on_secure_https = FALSE,  $httponly  );
	}
	public function setcookie_secure($name, $val, $time_length = 86400, $httponly=true, $homeurl=false){
		$real_domain = $homeur ?: $_SERVER['HTTP_HOST'];
		$domain = (substr($real_domain, 0, 4) == "www.") ? substr($real_domain, 4) : $real_domain;
		$path = $path ?: ( (!empty($this) && property_exists('pathAfterDomain', $this) ) ?  $this->pathAfterDomain : '/');
		setcookie ( $name , $val , time()+$time_length, $path, $domain = $domain ,  $only_on_https = FALSE,  $httponly  );
	}

	public function page_load_limited_for_seconds($seconds = 3, $cookiename = 'pageloader_limiter'){
		if (isset($_COOKIE[$cookiename])) {
			
		}
	}
	public function siteSlug() { return str_replace(array('.','/',':'),'_', $this->domain  ); }

	public function site_visitor_default_cookiee() {return 'default_visitr_'.siteSlug(); }

	public function SetCookieForVisitors(){ setcookie(site_visitor_default_cookiee(), time()+1000, time()+1000, $this->definedVALUE('homeFOLD','/'));  }
	//      SetCookieForVisitors();

	public function die_if_not_this_site_youtube(){if (!isset($_COOKIE[site_visitor_default_cookiee()])) {  die('noauth_6453'); } }

		
	// other information arrayyyyyy
	public function SetOddss($arr=array())
	{
		$baselink = $this->definedVALUE("MyBaseLink_schemeless",'/');

		
		$ar_CURRENT = !empty($GLOBALS['odd']) ? $GLOBALS['odd'] : array();
		$ar_CURRENT = array_merge($ar_CURRENT, $arr);
		$ar_NEW = array
	(
				
		'shares'=> array(
			'facebook'	=>'https://www.facebook.com/sharer/sharer.php?u=', 
			'googleplus'=>'https://plus.google.com/share?url=', 
			'twitter'	=>'https://twitter.com/share?url='
		),
		'contct_lnk'		=> '?contactMAILpage&lang=',

		//wp-codes
		'downloaderis_url'	=> $this->definedVALUE("PHP_customCALL_1",678)."FILEdownload&ver=".$this->definedVALUE("$this->changeable_JS_CSS_version",678),
		'post_Typess' =>  array() , //dont change this 
		'post_Types2' =>  (isset($SITE_LANGUAGES)?  $SITE_LANGUAGES : array()), //dont change this 
		'WPDB_Table__mycalendar'=>(!empty($GLOBALS['wpdb']) ? $GLOBALS['wpdb']->prefix.'kalendari_my' : ''),
		
		
		'myMONTHs' => array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'),
		'months_arr' => array('Jan'=>'January',  'Feb'=>'February',  'Mar'=>'March', 'Apr'=>'April',  'May'=>'May', 'Jun'=>'June',  'Jul'=>'July',  'Aug'=>'August','Sep'=>'September', 'Oct'=>'October', 'Nov'=>'November', 'Dec'=>'December'),
		'months_langs' => [
			"geo"=> [
				'January'=>'იანვარი',  'February'=>'თებერვალი',  'March'=>'მარტი', 'April'=>'აპრილი',  'May'=>'მაისი', 'June'=>'ივნისი',  'July'=>'ივლისი',  'August'=>'აგვისტო','September'=>'სექტემბერი', 'October'=>'ოქტომბერი', 'November'=>'ნოემბერი', 'December'=>'დეკემბერი'
			],
			"rus"=> [
				'January'=>'Январь',  'February'=>'Февраль',  'March'=>'Март', 'April'=>'Апрель',  'May'=>'Май', 'June'=>'Июнь',  'July'=>'Июль',  'August'=>'Август','September'=>'Сентябрь', 'October'=>'Октябрь', 'November'=>'Ноябрь', 'December'=>'Декабрь'
			],
		],

		
		'transl_linkebi' => ' - <a href="http://translate.google.com/"> google translate</a><br/>' .
							' - <a href="http://winrus.com/keyboard.htm"> Russian keyboard</a><br/>',
		'countries_en' => array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe"),

		
		//patterns(detectors) for different preg_match functions, we use later
		'book_sub_categ_symbol'		=> '-wig-', //dont change this
		'calendr_ddays_divider'		=> '_!Y!_',
			'calendr_monthss_divider'=> '_!X!_',
			'calnd_dividerr_saintDay'=> ' !X!',
			//'calnd_dividerr_readBe'=> ' !W!',
		'MULTIPAGED_book_dividr'	=> '_xTx_',
		'pages_dividerrr_phraze'	=>         'XXXXXX_PAGES_DIVIDER_XXXXXX',	
		'pages_dividerrr_used'		=> '\r\n\r\nXXXXXX_PAGES_DIVIDER_XXXXXX\r\n\r\n',
	);

		
		
									// support for deprecated commands..
									$ar_NEW = array_merge($ar_NEW, array(  
										'fb_share_url'			=> $ar_NEW['shares']['facebook'], 
										'twit_share_url'		=> $ar_NEW['shares']['twitter']
									));
		
		return $GLOBALS['odd']=array_merge($ar_CURRENT , $ar_NEW);
	}
	//$odd=SetOddss();
		
	public function get_current_buffer_clean($func_name=false){  ob_start(); if ($func_name) {$args=func_get_args(); call_user_func_array($func_name, $args);}  $cont= ob_get_clean(); ob_flush(); return $cont; }	
	//ob_get_contents() 

	public function validate_mail( $mail ){  //$_POST['email']
		return !filter_var( $mail, FILTER_VALIDATE_EMAIL );
	}

	// only for explicit temp use
	public function password_site($password)
	{
		$rnd_ext = str_replace('.','_', $this->domain);
		if ( isset($_POST['passwk']) && $password == $_POST['passwk'] ) {setcookie('pss_'.$rnd_ext, 'okk',  time()+1111111,  $this->definedVALUE('homeFOLD','/')); header("location:".$_SERVER['REQUEST_URI']);exit;} 
		elseif (!isset($_COOKIE['pss_'.$rnd_ext])){ echo '<form action="" method="post">  <b>'.$password.'</b>:<input name="passwk" value="">  <input type="submit" value="Enter"></form>';exit;}
	}	


	public function get_filename_($url){ return basename(parse_url($url)['path']); }

	// Obfuscate js/css : FsFkUweM

	public function scriptt($name, $with_css=false)	{ 
		return  ( (!empty($GLOBALS['already_loaded_'.$name])) ? '<!-- already outputed "'.$name.'" -->' :  $GLOBALS['already_loaded_'.$name]='<script type="text/javascript" src="'. $GLOBALS['odd']['scripts'][$name]['js'].'"></script>')  
			.  
		( !$with_css ? '' : '<link rel="stylesheet" href="'.  $GLOBALS['odd']['scripts'][$name]['css'].'"> '   );
	}

	public function scriptss(){
		foreach(func_get_args() as $key=>$value){ echo (!is_array($value) ? scriptt($value) : scriptt($value[0], $value[1]) ); }
	}


	public function translate__MONTH($text,$target_lang=''){   global $odd;	//switch ($text) { case 'January':	return TRANSLL('monthh1',$target_lang);
		if( !empty($odd['months_langs'][$target_lang]) && array_key_exists($text, $odd['months_langs'][$target_lang]))	{  
			$text = $odd['months_langs'][$target_lang][$text];
			if (mb_detect_encoding($text) =='UTF-8') {$text= mb_substr ($text,0,3,'utf-8') ; }  
		} 
		else{
			$text = TRANSLL($text,$target_lang);
		}
		return $text;
	}


	public function translate__DAY($text,$target_lang='') {		//switch ($text) { case 'January':	return TRANSLL('monthh1',$target_lang);
		if (in_array($text, array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')) ) {
			return TRANSLL($text,$target_lang);  //switch ($text) { case 'January':	return TRANSLL('monthh1',$target_lang);
		} return $text;
	}

	public function my_mailToUsername($mail){	return strtr($mail, array(  "."=>"_", "-"=>"__",	"@"=>"AT"    )); }


	// language specifics
	public function GEO_to_ENG($input){  return strtr($input, array(
		"ა"=>"a",	"ბ"=>"b",	"გ"=>"g",	"დ"=>"d",	"ე"=>"e",	"ვ"=>"v",	"ზ"=>"z",	"თ"=>"T",	"ი"=>"i",
		"კ"=>"k",	"ლ"=>"l",	"მ"=>"m",	"ნ"=>"n",	"ო"=>"o",	"პ"=>"p",	"ჟ"=>"J",	"რ"=>"r",	"ს"=>"s",
		"ტ"=>"t",	"უ"=>"u",	"ფ"=>"f",	"ქ"=>"q",	"ღ"=>"R",	"ყ"=>"y",	"შ"=>"S",	"ჩ"=>"C",	"ც"=>"c",
		"ძ"=>"Z",	"წ"=>"w",	"ჭ"=>"W",	"ხ"=>"x",	"ჯ"=>"j",	"ჰ"=>"h"    	));
	}
	public function ENG_to_GEO($input) { return strtr($input, array(
		'a'=>'ა',	'b'=>'ბ',	'g'=>'გ',	'd'=>'დ',	'e'=>'ე',	'v'=>'ვ',	'z'=>'ზ',	'T'=>'თ',	'i'=>'ი',
		'k'=>'კ',	'l'=>'ლ',	'm'=>'მ',	'n'=>'ნ',	'o'=>'ო',	'p'=>'პ',	'J'=>'ჟ',	'r'=>'რ',	's'=>'ს',
		't'=>'ტ',	'u'=>'უ',	'f'=>'ფ',	'q'=>'ქ',	'R'=>'ღ',	'y'=>'ყ',	'S'=>'შ',	'C'=>'ჩ',	'c'=>'ც',
		'Z'=>'ძ',	'w'=>'წ',	'W'=>'ჭ',	'x'=>'ხ',	'j'=>'ჯ',	'h'=>'ჰ'		));
	}

	//UPPERCASE CHARS sometimes MESS-UP several FUNCTION's USAGE. So, sometimes we need lowercased words
	public function GEO_to_ENG__LowerCased($m) { return strtolower(strtr($m, array(
		//$m=str_replace('თ','T',$m); $m=str_replace('ჟ','J',$m); $m=str_replace('ტ','t',$m); $m=str_replace('ღ','R',$m);
		//$m=str_replace('შ','S',$m); $m=str_replace('ჩ','C',$m); $m=str_replace('ძ','Z',$m); $m=str_replace('ჭ','W',$m); 
		"ა"=>"a",	"ბ"=>"b",	"გ"=>"g",	"დ"=>"d",	"ე"=>"e",	"ვ"=>"v",	"ზ"=>"z",	"თ"=>"t",	"ი"=>"i",
		"კ"=>"k",	"ლ"=>"l",	"მ"=>"m",	"ნ"=>"n",	"ო"=>"o",	"პ"=>"p",	"ჟ"=>"dj",	"რ"=>"r",	"ს"=>"s",
		"ტ"=>"t",	"უ"=>"u",	"ფ"=>"f",	"ქ"=>"q",	"ღ"=>"gh",	"ყ"=>"y",	"შ"=>"sh",	"ჩ"=>"ch",	"ც"=>"c",
		"ძ"=>"dz",	"წ"=>"w",	"ჭ"=>"tch",	"ხ"=>"x",	"ჯ"=>"j",	"ჰ"=>"h"    	)));
	}

	public function Rus_To_Eng__LowerCased($input){  return strtr($input, array(
		"а"=>"a","А"=>"a",		"б"=>"b","Б"=>"b",		"в"=>"v","В"=>"v",		"г"=>"g","Г"=>"g",		"д"=>"d","Д"=>"d",
		"е"=>"e","Е"=>"e",		"ё"=>"yo","Ё"=>"yo",	"ж"=>"zh","Ж"=>"zh",	"з"=>"z","З"=>"z",		"и"=>"i","И"=>"i",
		"й"=>"j","Й"=>"j",		"к"=>"k","К"=>"k",		"л"=>"l","Л"=>"l",		"м"=>"m","М"=>"m",		"н"=>"n","Н"=>"n",
		"о"=>"o","О"=>"o",		"п"=>"p","П"=>"p",		"р"=>"r","Р"=>"r",		"с"=>"s","С"=>"s",		"т"=>"t","Т"=>"t",
		"у"=>"u","У"=>"u",		"ф"=>"f","Ф"=>"f",		"х"=>"kh","Х"=>"kh",	"ц"=>"ts","Ц"=>"ts",	"ч"=>"ch","Ч"=>"ch",
		"ш"=>"sh","Ш"=>"sh",	"щ"=>"sch","Щ"=>"sch",	"ъ"=>"","Ъ"=>"",		"ы"=>"y","Ы"=>"y", 		"ь"=>"","Ь"=>"",
		"э"=>"e","Э"=>"e",		"ю"=>"yu","Ю"=>"yu",	"я"=>"ya","Я"=>"ya",    ));
	}
	public function ic1251_to_utf8($s){
		$s= str_replace('С?',$a1='fgr43443443',$s);
		$s= str_replace('Р?',$a2='tg5gh45h3hg3',$s);
		$s= str_replace('пїЅпїЅ?',$a3='fgr35gh35hg3gdfw',$s);
		$s= str_replace('СЊС?',$a4='XXX83rhf423888df8d23d1',$s);
		$s= str_replace('бѓ?',$a5='XXX83rhf423888df8d23d2',$s);
		$s= mb_convert_encoding($s, "windows-1251", "utf-8");
		$s= str_replace($a5,'ი',$s);
		$s= str_replace($a3,'ი',$s);
		$s= str_replace($a1,'ш',$s);
		$s= str_replace($a2,'И',$s);
		$s= str_replace($a4,'шь',$s);
		return $s;
	}

	// when something error happens...
	public function INCORRECT_GEO_to_ENG($input){  return strtr($input, array(
		"áƒ"=>"a", "áƒ‘"=>"b", "áƒ’"=>"g",  "áƒ“"=>"d",  "áƒ”"=>"e",  "áƒ•"=>"v",  "áƒ–"=>"z",  "áƒ—"=>"T",  "áƒ˜"=>"i",  "áƒ™"=>"k", "áƒš"=>"l",  "áƒ›"=>"m",  "áƒœ"=>"n",  "áƒ"=>"o", "áƒž"=>"p",  "áƒŸ"=>"J",  "áƒ "=>"r",  "áƒ¡"=>"s",    "áƒ¢"=>"t",  "áƒ£"=>"u",  "áƒ¤"=>"f",  "áƒ¥"=>"q",  "áƒ¦"=>"R",  "áƒ§"=>"y",  "áƒ¨"=>"S",  "áƒ©"=>"C",  "áƒª"=>"c",  "áƒ«"=>"Z",  "áƒ¬"=>"w",  "áƒ­"=>"W",  "áƒ®"=>"x",  "áƒ¯"=>"j",  "áƒ°"=>"h"   ));
	}
	
	public function Javascript_ArabicToRoman_covnerter(){
		return "
		function ConvertNumbToRoman(num){
			num= num.replace('40','XXXX');	num= num.replace('39','XXXIX');	num= num.replace('38','XXXVIII');	num= num.replace('37','XXXVII');
			num= num.replace('36','XXXVI');	num= num.replace('35','XXXV');	num= num.replace('34','XXXIV');		num= num.replace('33','XXXII');
			num= num.replace('32','XXXII');	num= num.replace('31','XXXI');  num= num.replace('30','XXX');		num= num.replace('29','XXIX');
			num= num.replace('28','XXVIII');num= num.replace('27','XXVII');	num= num.replace('26','XXVI');		num= num.replace('25','XXV');
			num= num.replace('24','XXIV');	num= num.replace('23','XXIII');	num= num.replace('22','XXII');		num= num.replace('21','XXI');
			num= num.replace('20','XX');	num= num.replace('19','XIX');	num= num.replace('18','XVIII');		num= num.replace('17','XVII');
			num= num.replace('16','XVI');	num= num.replace('15','XV');	num= num.replace('14','XIV');		num= num.replace('13','XIII');
			num= num.replace('12','XII');	num= num.replace('11','XI');	num= num.replace('10','X');			num= num.replace('9','IX');
			num= num.replace('8','VIII');	num= num.replace('7','VII');	num= num.replace('6','VI');			num= num.replace('5','V');
			num= num.replace('4','IV');		num= num.replace('3','III');	num= num.replace('2','II');			num= num.replace('1','I'); 		return num;
		}";
	}
	// # language specifics
	
	
	

	public function sanitize_nonalpha($input){  return strtr($input, array(
		" "=>"-",	"."=>"--",	":"=>"--",	","=>"-",	"/"=>"-",	";"=>"--",	"—"=>"",	"–"=>"-"
		));
	}

	public function sanitize_utf8_filenamee($input){
		$filename_sanitized = GEO_to_ENG__LowerCased($input);
		$filename_sanitized = Rus_To_Eng__LowerCased($filename_sanitized);
		$filename_sanitized = str_replace(' ','-',$filename_sanitized);
		$filename_sanitized = utf8_encode($filename_sanitized);
		return $filename_sanitized;
	}

	//AS OFFICIAL STANDARDS - https://stackoverflow.com/a/996161/2377343 , there exist 'reserved' characetrs.. ';', '/', '?', ':', '@','&', '=', '+', ',','$'
	//to send those characters to FLASH/SWF PLAYERs, they need to be encoded twice.
	public function my_Filename_Encoder_For_Flash_player($sentence){	
		$chars_array0=array(';', '/', '?', ':', '@','&', '=', '+', ',','$');
		//if the sentence contains encoded directories, then get to normal...
		$sentence= str_replace("%2F","/",  $sentence );
		//lets get already executed values (so, we save HOSTING memory :))
		if (!isset($GLOBALS[__FUNCTION__ .'_EXECUTED_ARRAYYYYY'])) { 
			foreach ($chars_array0 as $name=>$value){$chars_array1[$value] = urlencode($value);}
			foreach ($chars_array1 as $name=>$value){$chars_array2[$value] = urlencode($value);}
			$GLOBALS[__FUNCTION__ .'_EXECUTED_ARRAYYYYY'] = $chars_array2;
		}
		return strtr($sentence, $GLOBALS[__FUNCTION__ .'_EXECUTED_ARRAYYYYY']);
	}

	public function cut__my($text, $chars, $points = "...") {  $text = strip_tags($text);	if( strlen($text) <= $chars) { return $text;} else { return mb_strimwidth($text,0,$chars, $points,'utf-8'); } }


	public function myUTF8truncate($string, $width){
		if (mb_str_word_count($string) > $width) {
			$string= preg_replace('/((\w+\W*|| [\p{L}]+\W*){'.($width-1).'}(\w+))(.*)/', '${1}', $string);
		}
		return $string;
	}


	public function php_to_js_array($array){
		return '["'. implode('","', $array ) .'"]';
	}

	public function trim_to_charlength($text, $charlength) {
		$charlength++;

		if ( mb_strlen( $text ) > $charlength ) {
			$subex = mb_substr( $text, 0, $charlength - 5 );
			$exwords = explode( ' ', $subex );
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
			if ( $excut < 0 ) {
				echo mb_substr( $subex, 0, $excut );
			} else {
				echo $subex;
			}
			echo '...';
		} else {
			echo $text;
		}
	}


	// only for explicit use/tests
	public function my_wpdb($user, $pass, $db, $host, $path_to_root= '/../../../../../../', $run_wp_config=true){
		$path_to_wp=  dirname(__DIR__) .$path_to_root;
	//execute wp-config, if not run already
		if($exec_config && !defined('DB_HOST')){	preg_match('/\<\?php(.*?)\/\* That\'s all, stop editing/si',  $this->file_get_contents($path_to_root."wp-config.php"), $found); eval($found[1]); 	}
		require_once( $path_to_wp.'wp-includes/wp-db.php' );   if ( file_exists($path_to_wp.'wp-content/db.php' ) ) require_once($path_to_wp.'wp-content/db.php' );
		return $GLOBALS['wpdb'] = new wpdb( $user, $pass, $db, $host );
	}

	//$this->set_cookies_from_url($chosen_server_url.'?username=user&auth=key');
	public function  set_cookies_from_url($url) 
	{
		$d=$this->get_remote_data($url, false, ["curl_opts"=>["CURLOPT_HEADERFUNCTION"=> 
			( function ($ch, $headerLine) {
				if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookieArr) == 1)
				{
					$cookie = $cookieArr[1];
					$cookie_vars = explode('=', $cookie, 2);
					$this->example_cookies[$cookie_vars[0]] = $cookie_vars[1];
				}
				return strlen($headerLine); // Needed by curl
				} 
			) 
			]] 
		);
		foreach($this->example_cookies as $key=>$name)
		{
			$this->set_cookie($key,$name, 86000, '/target_dir/');
		}
		$this->set_cookie("sample_confirm","1");
	}

	// makes a string from an assiciative array
	public function implodeAssoc($glue,$arr) 
	{ 
		$keys=array_keys($arr); 
		$values=array_values($arr);
		return(implode($glue,$keys).$glue.implode($glue,$values)); 
	}

	public function get_youtube_id_from_url($url) {
	preg_match('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $results);    return $results[6];
	}
	public function get_youtube_id_from_contents($url){ 
		if (stripos($url,'youtu.be/')!==false)			{preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/si', $url, $final_ID); $x= !empty($final_ID[4]) ? $final_ID[4] : '';}
		elseif  (stripos($url,'youtube.com/')!==false)	{preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/si', $url, $IDD);$x= !empty($IDD[5]) ? $IDD[5] : ''; }
		return (!empty($x) ? $x : '');
	}

	public function get_youtube_id_from_contents_JAVASCRIPT(){ return '<script type="text/javascript">
		function getYtIdFromURL(URLL){var r=URLL.match(/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/); return r[1];}
		</script>';
	}
	public function Youtube_Img_Url_using_ID($id){return 'https://img.youtube.com/vi/'.$id.'/mqdefault.jpg'; }


	//to check if variable are normal
	public function validate_youtube_id($id){ if (strlen($id)!=11 || preg_match('/[\<\>\'\=\$\"\?\(\{]/si',$text)) {die("incorrrrrect_ID_ error79");	 }}
	public function validate_post_id($id)	 { if (!is_numeric($id) || strlen($id)>7) 								{die("incorrrrrect_postid error81"); }}
	public function validate_simple_word_of_s_GET($text){if (preg_match('/[\<\>\'\=\$\"\?\(\{]/si',$text))			{die("incorrrrrect error86");}}
	public function sanitizer($text)	{ return preg_replace('/\W/si','',$text); }
	public function validate_url($url)	{ return filter_var($url, FILTER_VALIDATE_URL) && (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url)); }
	public function startsWith($haystack, $needle) {   return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false; }
	public function endsWith($haystack, $needle) { $length = strlen($needle);  return $length === 0 ||  (substr($haystack, -$length) === $needle); }
	public function contains($content, $needle, $case_sens= true){ return ($case_sens ? strpos($content, $needle) : stripos($content, $needle)) !== false;  }


	public function remove_query_from_url($url, $which_argument=false){ 
		return preg_replace( '/'.  (  $which_argument ? '(\&|)'.$which_argument.'(\=(.*?)((?=&(?!amp\;))|$)|(.*?)\b)' : '(\?.*)').'/i' , '', $url);  
	} 

	public function die_if_not_this_site_visitor(){ //if half day passed
		if (empty($_COOKIE['ytdow___']) || $_COOKIE['ytdow___'] > time()*3 + 43200 ) {die('incorrect_download_<b>123</b>.<script type="text/javascript">top.window.location = "http://'.$_SERVER['HTTP_HOST'].'";</script>');}
	}

	public function js_redirect($url=false, $echo=true){
		$str = '<script>window.location = "'. ( $url ?: $_SERVER['REQUEST_URI'] ) .'"; document.body.style.opacity=0; </script>';
		if($echo) { exit($str); }  else { return $str; }
	}

	public function php_redirect($url=false, $code=302){
		//avoid redirection from customizer: if (!empty($_COOKIE['MLSS_cstRedirect']) || defined('MLSS_cstRedirect')) {return;}
		header("Cache-Control: no-store, no-cache, must-revalidate"); header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");   
		header("location: ". ( $url ?: $_SERVER['REQUEST_URI'] ), true, $code); exit;
	}

	public function js_redirect_message($message,$url=false){
		echo '<script>alert(\''.$message.'\');</script>';
		$this->js_redirect($url);
	}

	public function include_file_get($file){
		ob_start();
		include_once($file);
		$cont= ob_get_contents();
		ob_get_clean();
		return $cont;
	}


	// telegramMessage( ['chat_id'=>'-1001234567890', 'text'=>'hello world', ],   $bot_key );
	public static function telegramMessage($array, $botid){
		$phrase = http_build_query($array, '');
		return $this->get_remote_data('https://api.telegram.org/bot'.$botid.'/sendMessage?'.$phrase);
	}
	
	//===================  ( https://github.com/tazotodua/useful-php-scripts/ ) ==========================

	public function get_remote_data($url, $post_paramtrs=false,            $extra=array('schemeless'=>true, 'replace_src'=>true, 'return_array'=>false, "curl_opts"=>[]))	
	{ 
		$c = curl_init(); 
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		//if parameters were passed to this function, then transform into POST method.. (if you need GET request, then simply change the passed URL)
		if($post_paramtrs){ curl_setopt($c, CURLOPT_POST,TRUE);  curl_setopt($c, CURLOPT_POSTFIELDS, (is_array($post_paramtrs)? http_build_query($post_paramtrs) : $post_paramtrs) ); }
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false); 
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;'); 
			$headers[]= "User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:76.0) Gecko/20100101 Firefox/76.0";	 $headers[]= "Pragma: ";  $headers[]= "Cache-Control: max-age=0";
			if (!empty($post_paramtrs) && !is_array($post_paramtrs) && is_object(json_decode($post_paramtrs))){ $headers[]= 'Content-Type: application/json'; $headers[]= 'Content-Length: '.strlen($post_paramtrs); }
		curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($c, CURLOPT_MAXREDIRS, 10); 
		//if SAFE_MODE or OPEN_BASEDIR is set,then FollowLocation cant be used.. so...
		$follow_allowed= ( ini_get('open_basedir') || ini_get('safe_mode')) ? false:true;  if ($follow_allowed){curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);}
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 15); 
		curl_setopt($c, CURLOPT_TIMEOUT, 25);
		curl_setopt($c, CURLOPT_REFERER, $url);   
		curl_setopt($c, CURLOPT_AUTOREFERER, true);
		curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($c, CURLOPT_HEADER, !empty($extra['return_array']));
		//set extra options if passed
		if(!empty($extra['curl_opts'])) foreach($extra['curl_opts'] as $key=>$value) curl_setopt($c, constant($key), $value);
		$data = curl_exec($c);
		if(!empty($extra['return_array'])) { 
			preg_match("/(.*?)\r\n\r\n((?!HTTP\/\d\.\d).*)/si",$data, $x); preg_match_all('/(.*?): (.*?)\r\n/i', trim('head_line: '.$x[1]), $headers_, PREG_SET_ORDER); foreach($headers_ as $each){ $header[$each[1]] = $each[2]; }   $data=trim($x[2]); 
		}
		$status=curl_getinfo($c); curl_close($c);
		// if redirected, then get that redirected page
		if($status['http_code']==301 || $status['http_code']==302) { 
			//if we FOLLOWLOCATION was not allowed, then re-get REDIRECTED URL
			//p.s. WE dont need "else", because if FOLLOWLOCATION was allowed, then we wouldnt have come to this place, because 301 could already auto-followed by curl  :)
			if (!$follow_allowed){
				//if REDIRECT URL is found in HEADER
				if(empty($redirURL)){if(!empty($status['redirect_url'])){$redirURL=$status['redirect_url'];}}
				//if REDIRECT URL is found in RESPONSE
				if(empty($redirURL)){preg_match('/(Location:|URI:)(.*?)(\r|\n)/si', $data, $m);	                if (!empty($m[2])){ $redirURL=$m[2]; } }
				//if REDIRECT URL is found in OUTPUT
				if(empty($redirURL)){preg_match('/moved\s\<a(.*?)href\=\"(.*?)\"(.*?)here\<\/a\>/si',$data,$m); if (!empty($m[1])){ $redirURL=$m[1]; } }
				//if URL found, then re-use this function again, for the found url
				if(!empty($redirURL)){$t=debug_backtrace(); return call_user_func( $t[0]["function"], trim($redirURL), $post_paramtrs);}
			}
		}
		// if not redirected,and nor "status 200" page, then error..
		elseif ( $status['http_code'] != 200 ) { $data =  "ERRORCODE22 with $url<br/><br/>Last status codes:".json_encode($status)."<br/><br/>Last data got:$data";}
		//URLS correction
		$answer = ( !empty($extra['return_array']) ? array('data'=>$data, 'header'=>$header, 'info'=>$status) : $data);
		return $answer;      
	} 


	//usage: pastebin_com/36QUw5vp
	public function multi_curl($urls)
	{			
		$curl_responses	=[];
		$curl_errors	=[];
		$mch = curl_multi_init();
		$handlesArray=[];
		$curl_max_timeout = 60*60; //max 1 hr to run
		foreach ($urls as $key=> $url) { 
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, false);
			// timeouts: https://thisinterestsme.com/php-setting-curl-timeout/   and https://stackoverflow.com/a/15982505/2377343
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $curl_max_timeout);
			curl_setopt($ch, CURLOPT_TIMEOUT, $curl_max_timeout);
			if (defined('CURLOPT_TCP_FASTOPEN')) curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1); 
			curl_setopt($ch, CURLOPT_ENCODING, ""); // empty to autodetect | gzip,deflate
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_URL, $url);
			$handlesArray[$key] = $ch; 
			curl_multi_add_handle($mch, $handlesArray[$key]);
		}
		
		// other approaches are deprecated ! https://stackoverflow.com/questions/58971677/
		do {
			$execReturnValue = curl_multi_exec($mch, $runningHandles);
		} while ($runningHandles>0);
		
		//exec now
		foreach($urls as $key => $url)
		{
			$handle = $handlesArray[$key];
			// Check for errors
			$curlError = curl_error($handle);
			if($curlError != "") {
				$curl_responses[$key]=false;
				$curl_errors[$key] = $curlError;
			}
			else{
				$curl_responses[$key] = curl_multi_getcontent($handle);
			}
			curl_multi_remove_handle($mch, $handle); curl_close($handle);
		}
		curl_multi_close($mch);
		return [$curl_responses, $curl_errors];
	}
	
	// for fast requests on same server
	public function curlFast($url, $params){
		if (!property_exists($this,'curlFastInited')) 
		{
			$c = curl_init();	
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_TIMEOUT, 9);
			curl_setopt($c, CURLOPT_HTTPGET, true);
			curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false); 
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($c, CURLOPT_MAXREDIRS, 1); 
			curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
			curl_setopt($c, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
			curl_setopt($c, CURLOPT_ENCODING,  '');
			curl_setopt($c, CURLOPT_TCP_FASTOPEN,  1);
			$this->$curlFastInited =$c;
			register_shutdown_function( function(){ curl_close($this->$curlFastInited); } );
		}
		curl_setopt($this->$curlFastInited, CURLOPT_URL, $url);
		$data = curl_exec($this->$curlFastInited);
		return $data;
	}
	
	// ----
	public function get_client_ip() {
		$proxy_headers = array("CLIENT_IP", "FORWARDED", "FORWARDED_FOR", "FORWARDED_FOR_IP", "HTTP_CLIENT_IP", "HTTP_FORWARDED", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED_FOR_IP", "HTTP_PC_REMOTE_ADDR", "HTTP_PROXY_CONNECTION", "HTTP_VIA", "HTTP_X_FORWARDED", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED_FOR_IP", "HTTP_X_IMFORWARDS", "HTTP_XROXY_CONNECTION", "VIA", "X_FORWARDED", "X_FORWARDED_FOR");
		foreach($proxy_headers as $proxy_header) {
			if (isset($_SERVER[$proxy_header])) {
				if(preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $_SERVER[$proxy_header])) {
					return $_SERVER[$proxy_header];
				}
				else if (stristr(",", $_SERVER[$proxy_header]) !== FALSE) {
					$proxy_header_temp = trim(array_shift(explode(",", $_SERVER[$proxy_header])));
					if (($pos_temp = stripos($proxy_header_temp, ":")) !== FALSE) {$proxy_header_temp = substr($proxy_header_temp, 0, $pos_temp); }
					if (preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $proxy_header_temp)) { return $proxy_header_temp; }
				}
			}
		}
		return $_SERVER["REMOTE_ADDR"];
	}

	
	// example:
	//$ipinfo = json_decode(getIpInfo($_SERVER['REMOTE_ADDR']), true);
	//if($ipinfo['country_name'] != 'Georgia'){
	//    header("Location: https://www.cnn.com", true, 302); exit;
	//}

	public function getIpInfo($ip, $type=1, $api=""){
		$info="";
		if($type==1){
			$info = get_remote_data('https://geoip-db.com/json/'.$ip);	
			//"country_code":"GE", "country_name":"Georgia", "city":"null", "postal":null, "latitude":42, "longitude":43.5, "IPv4":"xxx.xxx.xxx.xxx", "state":"null"
			
		}
		elseif($type==2){
			// PLEASE DONT USE THIS API
			$info_initial = get_remote_data('https://geoipify.whoisxmlapi.com/api/v1?apiKey='.$api.'&ipAddress='.$ip);	
			// {"ip":"xxx.xxx.xxx.xxx","location":{"country":"AU","region":"Victoria","city":"Research","lat":-37.7,"lng":145.1833,"postalCode":"3095","timezone":"Australia\/Melbourne"}}
			$decoded = json_decode($info_initial, true);
			$loc =$decoded['location'] ;
			unset($decoded['location']) ;
			$ipinfo_new = array_merge( $decoded,$loc );
			return  $ipinfo_new;
		}
		return $info;
	}


	// Dont' be afraid - this function is not used anywhere in any of plugins (it is only usable & available if called directly in custom tests by developers, to temporarily monitor any $_POST attacks)
	public function SaveLogs($dirname= false) 
	{
		$currUrl = $_SERVER['REQUEST_URI'];
		$dir = $dirname ? $dirname : __DIR__ .'/___l';	if (!is_dir($dir)) {  mkdir($dir, 0755, true); }
		
		//create index to hide directory listing (both for Apache/Nginx)
		$index			=$dir.'/index.html';
		if (!file_exists($index)) { $this->file_put_contents($index, "");	}
		
		//create htaccess to disallow users access to folder (only for Apache)
		$htacc			=$dir.'/.htaccess';
		$htacc_content	= "RewriteEngine On"."\r\n"."redirect 302 / https://example.com"."\r\n"."order deny,allow"."\r\n"."deny from all"."\r\n"."	#allow from 111.111.111.111";
		if (!file_exists($htacc)) { $this->file_put_contents($htacc, $htacc_content);	}
		
		//create hidden prefix, to avoid recognition of files
		$prefixFile = $dir .'/prefix.php';
		$pre ='<?php //';
		$prefix_new ="prefix_".rand(1,9999999).rand(1,9999999).rand(1,9999999);
		if(!file_exists($prefixFile)) { $this->file_put_contents($prefixFile, $pre.$prefix_new); }
		$prefix = str_replace($pre,'', file_get_contents($prefixFile) );
		
		// message
		$message =  "\r\n\r\n".date('Y-m-d H-i-s') .'----- '. $this->get_client_ip()." [ ". (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "") ." ] " . $currUrl;
		// file
		$fileStart= $dir.'/__'.date('y').'-'.date('m').'_'.$prefix.'_';
		
		$actions_ignore = !empty($GLOBALS['post_override_logs_actions']) ? $GLOBALS['post_override_logs_actions'] : array('heartbeat');
		
		//start
		$types = ['_POST', '_GET'];
		foreach($types as $type)
		{
			if ( !empty($$type)  &&  (!isset($$type['action']) || !in_array($$type['action'],$actions_ignore )  ) ) { 
				if($type=='_POST') $message .= "  /////////////POST:".print_r($_POST,true);
				if(stripos($currUrl, 'wp-json/oembed/') !== false ) {	$fileStart .= '_JSON_OEMBED_';	}
				$this->file_put_contents( $fileStart.'__'.$type,   $message,  FILE_APPEND );
				break;
			}
		}
	}

	
	public function CurrentSiteIs($site){ return $site == $_SERVER['HTTP_HOST']; }
	public function CurrentHomeIs($path){ return trailingslashit($path)==trailingslashit(str_replace( trailingslashit(network_site_url()), '', trailingslashit(home_url())) ); } //return in_array(home_url(), ["http://$site","https://$site"]) || home_url('', 'relative')==$site; } 


	public function output_js_headers()
	{
		session_cache_limiter('none');
		// https://stackoverflow.com/a/1385982/2377343
											$year=60*60*24*365;//year
		//Caching with "CACHE CONTROL"
			header('Cache-control: max-age='.$year .', public');
		//Caching with "EXPIRES"  (no need of EXPIRES when CACHE-CONTROL enabled)
			//header('Expires: '.gmdate(DATE_RFC1123,time()+$year));
		//To get best cacheability, send Last-Modified header and ...
			header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime(__file__)));  //i.e.  1467220550 [it's 30 june,2016]
		//reply using: status 304 (with empty body) if browser sends If-Modified-Since header.... This is cheating a bit (doesn't verify the date), but remove if you dont want to be cached forever:
			// if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {  header('HTTP/1.1 304 Not Modified');   die();	}
		header("Content-type: application/javascript;  charset=utf-8");
	}


	// this is not called anywhere in plugins (only for developer exlicit usage)
	public function get_phpmailer()
	{
		$x=  __DIR__ .'/___SMTP.php'; 		if( ! file_exists($x) ){ $this->file_put_contents( $x, get_remote_data('https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php') );   }
		$x=  __DIR__ .'/___PHPMailer.php';	if( ! file_exists($x) ){ $this->file_put_contents( $x, get_remote_data('https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php') );   }
		include_once($x);
	}


	public function get_user_browser(){ 
		if (empty($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT']="unknown";
		$b = $_SERVER['HTTP_USER_AGENT']; $final =array();

		//(START FROM MOBILE check!!!!)
		if(
			preg_match('/android.+mobile|Windows Mobile|Nokia|avantgo|Mozilla(.*?)(Android|Mobile|Blackberry|Symbian)|OperaMini|Opera Mini|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|ap|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$b)
			||
			preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($b,0,4))
			)									{	$final['brwsr'] = "Mobilee";	}
		//if typical browsers
		elseif(preg_match('/Firefox/i',$b))		{	$final['brwsr'] = "Firefox";	}
		elseif(preg_match('/Safari/i',$b))		{	$final['brwsr'] = "Safari";	}
		elseif(preg_match('/Chrome/i',$b))		{	$final['brwsr'] = "Chrome";	}
		elseif(preg_match('/Flock/i',$b))		{	$final['brwsr'] = "Flock";		}
		elseif(preg_match('/Opera/i',$b))		{	$final['brwsr'] = "Opera";		}
				elseif(preg_match('/MSIE 6/i',$b))				{$final['brwsr'] = "MSIE 6";	}
				elseif(preg_match('/MSIE 7/i',$b))				{$final['brwsr'] = "MSIE 7";	}
				elseif(preg_match('/MSIE 8/i',$b))				{$final['brwsr'] = "MSIE 8";	}
				elseif(preg_match('/MSIE 9/i',$b))				{$final['brwsr'] = "MSIE 9";	}
				elseif(preg_match('/MSIE 10/i',$b))				{$final['brwsr'] = "MSIE 10";	}
				elseif(preg_match('/Trident\/7.0; rv:11.0/',$b)){$final['brwsr'] = "MSIE 11";	}
				else											{$final['brwsr'] = "UNKNOWNNN";	}
		//===========================================================================================================
		$final['full_brwsr_namee']	 = $b;
		//other parameters
		return $final;
	}


	public function get_user_OperatingSystem() { 
		if (empty($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT']="unknown";
		$user_agent=$_SERVER['HTTP_USER_AGENT']; $final =array(); $final['os_namee']="_Unknown_OS_";  $final['os_typee']="_Unknown_OS_";
		$os_array=array(
			'MOUSED'	=> array(
				'/windows nt 10.0/i'=>'Windows 10', '/windows nt 6.3/i'=>'Windows 8.1', '/windows nt 6.2/i'=>'Windows 8', '/windows nt 6.1/i'=>'Windows 7',	'/windows nt 6.0/i'=>'Windows Vista','/windows nt 5.2/i'=>'Windows Server 2003/XP x64', '/windows nt 5.1/i'=>'Windows XP', '/windows xp/i'=>'Windows XP','/windows nt 5.0/i'=>'Windows 2000','/windows me/i'=>'Windows ME','/win98/i'=>'Windows 98','/win95/i'=>'Windows 95','/win16/i'=>'Windows 3.11',
				'/macintosh|mac os x/i' =>'Mac OS X','/mac_powerpc/i'=>'Mac OS 9', '/linux/i'=>'Linux','/ubuntu/i'=>'Ubuntu',
								),
			'NOMOUSED'	=> array(
				'/iphone/i'=>'iPhone','/ipod/i'=>'iPod','/ipad/i'=>'iPad','/android/i'=>'Android','/blackberry/i'=>'BlackBerry', '/webos/i'=>'Mobile'
								)
		); 
		foreach($os_array as $namee=>$valuee) { foreach ($valuee as $regex => $value1) {	if(preg_match($regex, $user_agent)){$final['os_namee']=$value1;  $final['os_typee'] = $namee;}		} }
		return $final;
	}



	public function mobile_detect()
	{
		$x=  __DIR__ .'/___Mobile_Detect.php'; 	if( ! file_exists($x) ){  $this->file_put_contents( $x, get_remote_data('https://raw.githubusercontent.com/serbanghita/Mobile-Detect/master/Mobile_Detect.php') );  }
		include_once($x);
		$detect = new Mobile_Detect;

		$odd['is_portable_platform']= ($odd['brwsr'] == "Mobilee" || $odd['os_typee']=="NOMOUSED" || (isset($detect) && $detect->isMobile()) );
		$odd['is_mobilee']			= ($odd['is_portable_platform'])			;
		$odd['is_pc']=$odd['is_pc_platform']= (!$odd['is_portable_platform'])	;
		$odd['is_new_browser']		= (in_array($odd['brwsr'],array('Opera','Chrome','Firefox','Safari','Flock')));
	}

	public function platforms()
	{
		if (property_exists($this, 'platforms_cached')) return $this->platforms_cached;
		$this->platforms_cached = array_merge( $this->get_user_browser(), $this->mobile_detect(), $this->get_user_OperatingSystem() );
		return $this->platforms_cached;
	}
	




	public function get_url_parts($url,$part){	 $x='';
		$pURL = parse_url($url);	$pthURL = pathinfo($url);		// https://stackoverflow.com/a/31476046/2377343 

		//for example: https://example.com/myfolder/sympony.mp3?aa=1&bb=2?cc=#gggg
		if		($part=='schemee'){ 	$x = !empty($pURL['scheme'])	?	$pURL['scheme']				:'';}	//  http
		elseif	($part=='hostnamee'){ 	$x = !empty($pURL['host'])		?	$pURL['host']				:'';}   //  example.com
		elseif	($part=='queryy'){ 		$x = !empty($pURL['query'])		?	$pURL['query']				:'';}   //  aa=1&bb=2?cc=
		elseif	($part=='hashh'){ 		$x = !empty($pURL['fragment'])	?	$pURL['fragment']			:'';}   //  gggg
		elseif	($part=='filee'){ 		$x = !empty($pURL['path'])		?	$pURL['path']				:'';}   //  /myfolder/sympony.mp3
		elseif	($part=='filenamee'){ 	$x = !empty($pURL['path'])		?	basename($pURL['path'])		:'';}   //  sympony.mp3
		elseif	($part=='extensionn'){	$x = !empty($pURL['path'])		?	pathinfo($pURL['path'], PATHINFO_EXTENSION) :'';}   //  mp3
		elseif	($part=='folderr'){ 	$x = !empty($pURL['path'])		?	dirname($pURL['path'])		:'';}   //  /myfolder
		elseif	($part=='dirnamee'){ 	$x = !empty($pthURL['dirname'])	?	$pthURL['dirname']			:'';}   //  https://example.com/myfolder
		elseif	($part=='afterfolderr'){$x = !empty($pthURL['basename'])?	$pthURL['basename']			:'';}   //  sympony.mp3?aa=1&bb=2?cc=#ggg
		
		return $x;
	}

	public function urlencodeall($x) {
		$out = '';
		for ($i = 0; isset($x[$i]); $i++) {
			$c = $x[$i];
			if (!ctype_alnum($c)) $c = '%' . sprintf('%02X', ord($c));
			$out .= $c;
		}
		return $out;
	}

	public function customm_word_length_sentence($got_content,$words_length,$StripOrNot=true, $preserved=''){
		$got_content = trim($got_content); 			//https://php.net/manual/en/function.trim.php
		//$got_content = strip_shortcodes($got_content); //https://stackoverflow.com/a/20403438/2165415
		$got_content = str_replace(']]>', ']]>', $got_content);
		$got_content= str_replace("\n",' ',$got_content);
		$got_content= str_replace("\r",' ',$got_content);
		$got_content = !$StripOrNot ? $got_content : strip_tags($got_content,$preserved) ;
		$words = explode(' ', $got_content, $words_length + 1);
		if(count($words) > $words_length) :
			array_pop($words);
			array_push($words, '…');
			$got_content = implode(' ', $words);
		endif;
		return $got_content;	
	}

	public function unicode_words_count($string) {	preg_match_all('/[\pL\pN\pPd]+/u', $string, $matches);	return count($matches[0]);}
	public function text_splitt($msg, $word_numbs) {
		$msg = preg_replace('/[\r\n]+/', ' ', $msg);
		$chunks = wordwrap($msg, $word_numbs*20 , '\n', true);
		return explode('\n', $chunks);
	}

	public function FilterUrlFromLang($url){	return preg_replace('/(\&|\?)lg\=((.*?)&|(.*))/si','',$url); }


	public function utf8_declarationn() { return '<meta http-equiv="content-type" content="text/html; charset=UTF-8">'; }
	public function utf8_declarationn_auto() { return '<meta http-equiv="content-type" content="'.get_bloginfo('html_type').'; charset='.get_bloginfo('charset').'">'; }


	public function HTML_DOCTYPE_DECLARATIONsss(){  $lng = (defined('LNG') ? LNG : '') ;
		return 
	'<!DOCTYPE html>
	<html id="pagehtml" class="LN_'.$lng.'" xmlns:fb="https://www.facebook.com/2008/fbml" xmlns:og="https://opengraphprotocol.org/schema/" xmlns="https://www.w3.org/1999/xhtml" lang="'.$lng.'" xml:lang="'.$lng.'" >';
	}


	public function default_rss_head_tags(){ 
	?> 	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
		<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
		<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" /> <?php	
	}

	//add_actionX('wp_head','check_if_js_cookies_enabled');
	public function check_if_JS_enabled(){	$out = 
		'<noscript>
			<div style="text-align:center; position:absolute;background-color:red;">Enable Javascript in your Browser to avoid BROWSER problems!</div>
		</noscript>';
		return	$out;
	}				
	public function check_if_COOKIES_enabled(){ $out1 = 
			'<script>
			function check_if_cookies_are_enabled(){ 
				var temp_cooK_name="__verify=1"; var dattee = new Date();dattee.setTime(dattee.getTime()+(30*1000));
				document.cookie = temp_cooK_name + ";expires=" + dattee.toUTCString();
				var supportsCOOCKIES = document.cookie.length >= 1 && document.cookie.indexOf(temp_cooK_name) > -1;
				if (supportsCOOCKIES) {document.write(\'<div style="text-align:center; position:absolute;background-color:red;">Enable cookies in your<br/> browser to avoid <br/>browser problems!</div>\');}
			}
			check_if_cookies_are_enabled();
			</script>';
		return $out1;
	}			
		
	
	public function old_browser_message($first=null, $incompatible_browsers=array('MSIE') ){
		global $odd;
		if (in_array($this->platforms()['brwsr'], $incompatible_browsers) ) { echo '<div style="padding:20px;text-align:center;position:fixed; top:0px;left:0px; z-idnex:99; background:red;color:black; ">Your have an INCOMPATIBLE BROWSER! Please, use any modern browser (<b><a href="https://www.firefox.com">Firefox</a>, <a href="https://www.opera.com">Opera</a>, <a href="https://www.apple.com/safari/‎">Safari</a> , <a href="https://www.chrome.com">Chrome</a></b>..) to view site normally. </div>'; }
	}	


	public function facebook_rescarpe_url($url){  $x=get_remote_data('https://graph.facebook.com/','id='.urlencode($url).'&scrape=true'); }


	// https://goo.gl/gB7j9Q
	public function send_data_to_pastebin( $array= array())
	{
		$api_dev_key 			= $array['api_key'];
		$api_paste_code 		= $array['content']; 
		$api_paste_private 		= $array['public'];
		$api_paste_name			= $array['title'];
		$api_paste_expire_date 	= $array['expiration'];
		$api_paste_format 		= 'text';
		$api_user_key 			= $array['user_key']; // if an invalid or expired api_user_key is used, an error will spawn. If no api_user_key is used, a guest paste will be created
		$url 					= str_replace('com','.com','https://pastebincom/api/api_post.php'); //funny, but pastebin links are detected as malware on hostings (they fight malware that way :)) ( https://goo.gl/bRV6dE )
		$ch 					= curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'api_option=paste&api_user_key='.$api_user_key.'&api_paste_private='.$api_paste_private.'&api_paste_name='.urlencode($api_paste_name).'&api_paste_expire_date='.$api_paste_expire_date.'&api_paste_format='.$api_paste_format.'&api_dev_key='.$api_dev_key.'&api_paste_code='.urlencode($api_paste_code).'');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);	
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
		curl_setopt($ch, CURLOPT_REFERER, $url);    
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);  
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		$response  			= curl_exec($ch);
		return $response;
	}
	
	public function create_pastebin($content,$title ='untitled'){
		return send_data_to_pastebin(    array( 'api_key'=>$GLOBALS['pastebin_api_key'], 'user_key'=>$GLOBALS['pastebin_user_key'], 'public'=>'1',  'expiration'=>'1M', 'content'=>$content,   'title'=>'untitled'  ) ); 
	}


	// github api for GIST create
	public function create_gist($content, $token=""){
		$url = 'https://api.github.com/gists';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		$header[]= "User-Agent: My App Name,Website or Email (for identification)";
		$header[]= "Authorization: token $token"; //basic base64_encode("username:password");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		$data = array(
		"description"=> "descr",
		"public"=> true,
		"files" => array(
			"file1.txt"=> array(
			"content" => $content
			)
		)
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($data) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
		// $response['html_url'] ----->   https://gist.github.com/0f62b7edeb2f03af1ec8d10558b7d67a 
		// $response['raw_url']  -----> https://gist.github usercontent.com/anonymous/0f62b7edebd1057d67a/raw/b004869d73c82d0d1/file1.txt
		// $response['url'] ----->  .... just contains some info about the api call
	}



		
	public function value_or_input_field($namee){
		if (!empty($GLOBALS['editing_inputs'])){
			
		}
		else{
			
		}
	}
	public function recursive_for_array_value($array,$function_name=false){ 
	//on first run, we define the desired function name to be executed on values
		if ($function_name) { $GLOBALS['current_func_name']= $function_name; } else {$function_name=$GLOBALS['current_func_name'];}
	//now, if it's array, then recurse, otherwise execute function
		return is_array($array) ? array_map('recursive_for_array_value', $array) : $function_name($array); 
	}

	public function  header_mail($from=false, $host= false){ 
		$from = $from ? $from : "contact"; 
		$host = $host ? $host : $_SERVER['HTTP_HOST'];//$_SERVER['SERVER_ADDR']; 
		return array('From: '.$from.'@'.$host . "\r\n" .  'Reply-To: '.$from.'@'.$host . "\r\n" .  'X-Mailer: PHP/' . phpversion());
	}

	// replace only first occurence:     
	public function str_replace_first_occurence($from, $to, $content, $type="plain"){
		if($type=="plain"){
			$pos = strpos($content, $needle);
			if ($pos !== false) {
				$content = substr_replace($content, $replace, $pos, strlen($needle));
			}
			return $content;
		}
		elseif($type=="regex"){
			$from = '/'.preg_quote($from, '/').'/';
			return preg_replace($from, $to, $content, 1);
		}
	}

	public function keyAtIndex($index, $array){
		$keys = array_keys($arr);
		return $keys[$index];
	}

	public function keyAfterKey($keyname, $array, $increment){
		$keys = array_keys($arr);
		$current_key_index = array_search($keyname, $keys);
		return $keys[array_search($keyname,$keys)+$increment];
	}


	public function preg_quote_fast($text){
		$specs =array('/', '.','\\','+','*','?','[','^',']','$','(',')','{','}','=','!','<','>','|',':','-');
		$new_array_for_strtr = array();
		foreach($specs as $each){
			$new_array_for_strtr[$each] = '\\'.$each;
		}
		$text = strtr( $text, $new_array_for_strtr);
		return $text;
	}

	public function Convert_Empty_to_Zero ($var){ if (empty($var)) return 0; else return $var; }



	public function chars_array_($alhpanumeric=true){  return  ( $alhpanumeric ?
			array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z')
			:
			array('!','$','+','<','[',']','%',',','.','=','&','-','<','>','|', '"', '\'', '\\', '~','(','/',')','!',' ',"\r","\n", '*', '{','}','?','`','@',':',';','^')
		);
	}


	public function arraykey_equals($array, $key_name, $key_value){
		return (!empty($array) && array_key_exists($key_name,$array) && $array[$key_name] == $key_value );
	}



	public function checkboxes($checkbox_name,$current_value, $unchecked_value,$checked_value){
		$out = '<input type="hidden" name="'.$checkbox_name.'" value="'.$unchecked_value.'" /><input class="chbkx" type="checkbox"  name="'.$checkbox_name.'" value="'.$checked_value.'" '. ($current_value==$checked_value ? 'checked="checked"': '') .' />'; return $out;
	}

	public function DownloadFile($filepath=true, $value, $output_file_name=''){
		$content= $filepath ? $this->file_get_contents($value) :  $value;
		ob_get_clean();
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$output_file_name);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 5');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		ob_clean();	flush(); echo $content;
		exit;
	}

	
	public function move_folder_contents($from, $to)
	{
		foreach( glob($from ."/*") as $each)
		{
			$target=$to."/".basename($each);
			if(is_dir($target)) {
				//$this->rmdir_recursive($target);
			}
			elseif(is_file($target)) 
			{
				@unlink($target);
				//rename($each, $target);
			}
		}
	}




	// i.e.    insert_code_in_file(WP_CONTENT_DIR .'/themes/freshwp/header.php', '</nav>', '<somethinggggg my></nav>' );
	public function insert_code_in_file($filepath, $replace_what, $replace_with, $admin=true)
	{
		if(!$admin || is_admin())
		{
			if(file_exists($filepath))
			{
				$content= $this->file_get_contents($filepath);
				if(stripos($content, $replace_with) === false)	//if it doesnt contain
				//if(stripos($content, $replace_what) !== false &&  stripos($content, $replace_with) === false)	//if target exists, but desired not
				{
					$this->file_put_contents( $filepath, str_replace($replace_what, $replace_with, $content) );
				}
			} 
		}
	}


	public function OutputIfNotPC($var){ if($GLOBALS['odd']['is_portable_platform']){echo $var;} }


	
	
	// common funcs
	public function  str_replace_first($from, $to, $content, $type="plain"){
		if($type=="plain"){
			$pos = strpos($content, $from);
			if ($pos !== false) {
				$content = substr_replace($content, $to, $pos, strlen($from));
			}
			return $content;
		}
		elseif($type=="regex"){
			$from = '/'.preg_quote($from, '/').'/';
			return preg_replace($from, $to, $content, 1);
		}
	}
			

	public function my_translate_month_inside($string = '27/January/2015'){
		foreach($GLOBALS['odd']['months_arr'] as $each){
			if(strpos($string,$each)!==false) { 
				$string = str_replace($each,translate__MONTH($each), $string);
			}
		}
		return $string;
	}

	public function get_First_words($sentence , $desired_words_amount=5){
		$all_words = explode(' ', $sentence);  $words_amount = count($all_words);  $words_index_amount=$words_amount-1;
		$out = '';
		if ($words_amount > $desired_words_amount) {
			for($i = 0; $i< $desired_words_amount; $i++) {
				if(array_key_exists( $i,$all_words)){
					$out = $out.' '.$all_words[$i];
				}
			}
		}
		else {$out = $sentence;  }
		return strip_tags($out);
	}

	public function get_Last_words($sentence , $desired_words_amount=5){
		$all_words = explode(' ', $sentence);  $words_amount = count($all_words);  $words_index_amount=$words_amount-1;
		$out = '';
		if ($words_amount > $desired_words_amount) {
			for($i = 0; $i< $desired_words_amount; $i++) {
				if(array_key_exists( ($words_index_amount-$i),$all_words)){
					$out = $all_words[($words_index_amount-$i)].' '.$out;
				}
			}
		}
		else {$out = $sentence;  }
		return strip_tags($out);
	}

	public function my_utf8_decode($textt){
		$var = $textt;	$var = iconv("UTF-8","ISO-8859-1//IGNORE",$var);	$var = iconv("ISO-8859-1","UTF-8",$var); $var = str_replace(' ','',$var);
		return $var;
	}



	// ============================================= YOUTUBE DOWNLOAD FUNCTIONS ====================================================
	/*
	public function yout_downl2($yout_video_id, $type =false, $vid_title=false){
		$outp = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><style>a {display:block;text-align:center; background-color:#e7e7e7;border-radius:5px; }</style></head><body>
			<div class="fanj" align="center" style="width:600px;margin:0 auto;">
			<br/>https://www.youtube.com/watch?v='.$yout_video_id.' ( Alternative Download ):';
				if ($type=='video'){
					$outp .= '<br/>
					<br/><a target="_blank" href="http://www.clipconverter.cc/?ref=bookmarklet&url=http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D'.$yout_video_id.'">2) clipconverter.com</a>
					<br/><a target="_blank"  href="http://savefrom.net/?url=http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D'.$yout_video_id.'">3) saveFrom.net</a>
					<br/><a target="_blank"  href="http://www.fullrip.net/video-m/'.$yout_video_id.'">4) fullrip.net</a>
					';
					//http://www.convertfiles.com/ 
					//freefileconvert.com
				}
				elseif 	($type=='audio'){
					$outp .= '<br/>
					<br/><a target="_blank"  href="http://www.youtube-mp3.org/?e=s_exp&r=true#v='.$yout_video_id.'">1) youtube-mp3.org</a>
					<br/><form style="display:none;" action="http://convert2mp3.net/en/index.php?p=convert" name="frm1" method="post"><input name="url" value="https://www.youtube.com/watch?v='.$yout_video_id.'" type="text" />	<input name="format" value="mp3" type="text" /><input name="85tvb5" value="242433" type="text" /><input type="submit"></form> <a  href="javascript:document.forms[\'frm1\'].submit();">2) convert2mp3.net</a>
					<br/><a target="_blank" href="http://www.clipconverter.cc/?ref=bookmarklet&url=http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D'.$yout_video_id.'">3) clipconverter.com(MP3)</a>
					';
					//$result = get_remote_data('http://www.force-download.net/getDlLink.php', 'video_url=https://www.youtube.com/watch?v='.$yt_ID); preg_match('/%3Cbr%2F%3E(.*?)\<\/link\>/si',$result,$n);  $AUDIO_FILE = urldecode($n[1]).'&title='.$yt_titl.'.mp3';
				}
			$outp .= 
			'</div>
			<div style="margin:50px 0 0 0; font-style:italic; font-size:13px;">Other download sites: ytconv.net, online-convert.com, fullrip.net, 2conv.com </div>
			</body></html>';
		return $outp;
	}
	
	public function yout_DownUrls($viid_id, $titlee='')	{ 
		$full_info = get_youtube_data($viid_id);
		if (!empty($full_info['url_encoded_fmt_stream_map'])) 	{
			$streams = explode(',',$full_info['url_encoded_fmt_stream_map']);			//echo '<pre>';print_r($streams);echo '</pre>';exit;
			foreach($streams as $stream){
				parse_str($stream, $data); 
				if(stristr($data['type'], "video/mp4") && $data['quality']=="medium"){
					if(empty($titlee)) {$titlee= !empty($data['title']) ? urlencode($data['title']) : (!empty($full_info['title']) ?  urldecode($full_info['title']) :'youtube_file');}
					$vidLink= urldecode($data['url']).'&title='.$titlee.'_'; break;
						//$linkk		= $data['url'].'&signature='.$data['sig']; //$linkk="http://127.0.0.1:8182/MVI_1356_1.mp4";
						//$json_output	= file_get_contents(trim("http://gdata.youtube.com/feeds/api/videos/".$viid_id."?v=2&alt=json"));
						//$json 		= json_decode($json_output, true);
						//$video_description = $json['entry']['media$group']['media$description']['$t'];
						//$video_counts	= $json['entry']['yt$statistics']['viewCount'];
						//$video_title3	= urlencode($data['title']);// urlencode($json['entry']['title']['$t']);
						//$final_url 	= urldecode($linkk). '&title='.$video_title3.'';
						//$file = fopen('video.'.rand (100, 1000000).str_replace($format,'video/','').".mp4",'w');
						//stream_copy_to_stream($video, $file); //fclose($video); readfile($file);	//break;
				}
			}
		} else {$vidLink= false;}
	
		if (!empty($full_info['adaptive_fmts'])) 	{
			$streams = explode(',',$full_info['adaptive_fmts']);	//echo '<pre>';print_r($streams);echo '</pre>';exit;
			foreach($streams as $stream){
				parse_str($stream, $data); if(stristr($data['type'], "audio/mp4")){ $audLink= urldecode($data['url']).'&title='.$titlee.'_'; break;}
			}
		} else {$audLink= false;}
		return array('vid'=>$vidLink,'aud'=>$audLink,'title'=>$titlee);
	} 
	*/
	
	public function get_youtube_thumbnail($id,$quality='maxres'){return 'https://i.ytimg.com/vi/'.$id.'/'.$quality.'default.jpg';}
		
	public function get_youtube_data($viid_id, $part='')	{ 
		$ResponserUrl = 'https://youtube.com/get_video_info?video_id=';
		// when Youtube Blocks this Server's IP, use 3rd party:
		// $ResponserUrl = 'http://my_another_clean_host_site.com/youtube_info.php?yt_id=';		and this code: pastebin_com/dxigxnNH
		// better to use curl 
		$data = get_remote_data($ResponserUrl.$viid_id);	// file_get_contents($ResponserUrl.$viid_id);
		parse_str($data , $full_info); 
		if(!empty($part)){
			if($part=='title') {
				if(!empty($full_info['title'])) return $full_info['title'];
				else{
					if (!empty($full_info['url_encoded_fmt_stream_map'])) 	{
						$streams = explode(',',$full_info['url_encoded_fmt_stream_map']);	//echo '<pre>';print_r($streams);echo '</pre>';exit;
						foreach($streams as $stream){
							parse_str($stream, $data); 
							if($part=='title'){
								return !empty($data['title']) ? urlencode($data['title'])  :'youtube_video';
							}
						}
					}	
				}
			}
		}
		return $full_info;
	} 
		
	// ============================================= YOUTUBE DOWNLOAD FUNCTIONS ====================================================


		
	// force ssl	
	public function redirect_to_https(){
		if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
			$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $redirect);
			exit();
		}
	}

	public function redirect_to_nonwww($https=true){
		if( stripos($_SERVER['HTTP_HOST'],'www.') !== false ) {
			$redirect =  ($https ? 'https' : 'http') . '://' . str_replace('www.','', $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $redirect);
			exit();
		}
	}


	/*
	// only for explicit use
	public function Download_Filee( $x= array('forbidden_directories'=>'/wp-content/uploads' , 'file_locationn'=>'')    ){
		$filee=$x['file_locationn'];
		//check if not allowed directory requested
			IsRestirctedDirecotryRequested($filee);
		//check if it is not limited to WP_CONTENT folder
			if ($x['forbidden_directories'])	{  $filee= $x['forbidden_directories'].$filee; }
		//set path correctly from this file:
			$fileLocation	= (defined('ABSPATH')		?	ABSPATH :  '../../..'  .$filee   ); 
		//when file doesnt exist
			if (!file_exists($fileLocation)){ echo '<b style="background:red;">error_750 [contact administrator !!]</b><pre>'; var_dump($fileLocation);echo '</pre>';exit;}
		
		// Download
		ob_get_clean();		ini_set('auto_detect_line_endings', true);
		header("Pragma: public");header("Expires: 0");
		header('Content-Type: application/force-download'); //application/octet-stream
		header("Content-Description: File Transfer");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header('Content-Length: '.filesize($fileLocation));
		header("Content-Disposition: attachment; filename=\"".basename($fileLocation)."\"");
		readfile($fileLocation);
		exit;
	}
	*/

		
	//enable HTTP compression
	//	if( $this->'ENABLE_gZIP') { add_action('wp', (function (){ if (!is_admin()) ob_start('ob_gzhandler'); } ) ,1);  }
		//similar as: ini_set('zlib.output_compression', '1');  remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );


	public function Serialized_Fixer($serialized_string){
		// securities
		if (empty($serialized_string)) 						return '';
		if ( !preg_match('/^[aOs]:/', $serialized_string) )	return $serialized_string;
		if ( @unserialize($serialized_string) !== false ) 	return $serialized_string;
		
		return
		preg_replace_callback(
			'/s\:(\d+)\:\"(.*?)\";/s', 
			function ($matches){	return 's:'.strlen($matches[2]).':"'.$matches[2].'";';	},
			$serialized_string )
		;
	}

	public function myIMGurlencode2($imgUrl){
		preg_match('/(.*)\/(.*)/si',$imgUrl, $n);	$x = (!empty($n[1]) && !empty($n[2])) ? $n[1].'/'.str_replace('+','%20',urlencode($n[2])) : "error_29858";  return $x;
	}

	public function myIMGurlencode($imgUrl){
		return str_replace('/'.basename($imgUrl) ,  '/'.str_replace('+','%20',basename($imgUrl)),       $imgUrl);
	}


	public function AddStringToUrl($url, $string){
		return $url .( stripos($url,'?')===false ?  '?'.$string :  '&'.$string);
	}

	//add only in case the array didnt containted it already
	public function Add_in_array_if_not_already_added($my_arrayy,$target_value){
		if (array_search($target_value, $my_arrayy) !== true) {	$my_arrayy[] = $target_value;}			return $my_arrayy;
	}

	//remove item from array by value
	public function remove_value_from_arrayyy($my_arrayy, $target_value){  
		if (!empty($my_arrayy) && is_array($my_arrayy) ) {
			foreach ($my_arrayy as $key => $value){  if ($value == $target_value) { unset($my_arrayy[$key]); }   }
		}
		return $my_arrayy;
	}


	function RemoveParameterFromUrl($full_url, $param_name){
		return $final = preg_replace('/(\&|\?)'.$param_name.'(\=(.*?(&|#)|.*)|)/i', (!empty('$4') ? '$4' : ''), $full_url);
	}
	
	//sample function, to show black background
	public function my_black_backgorund_output(){	$scrpt=
		'<script type="text/javascript">
		var innerDiv = document.createElement("div"); innerDiv.id = "my_black_backgr";
		innerDiv.setAttribute("style", "background:black; height:4000px; left:0px; opacity:0.9; position:fixed; top:0px; width:100%; z-index:9990;");
		var BODYYY = document.body;	BODYYY.insertBefore(innerDiv, BODYYY.childNodes[0]);
		</script>'; return $scrpt;
	}

	public function Sanitize44($string=''){	return str_replace('"',"'",$string);}
	
	public function isAssociative(array $arr){
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	public function removWhitespaces($input){ 
		$input= str_replace("   ",		" ",$input );
		$input= str_replace("  ",		" ",$input );
		$input= str_replace("\t\t",		" ",$input );
		$input= str_replace("\t",		" ",$input );
		$input= str_replace("\r\n\r\n",	" ",$input );
		$input= str_replace("\r\n ",	" ",$input );
		return $input;
	}
	public function stripCOODs($input){ return strip_shortcodes(strip_tags($input, '<h1></h1><br><br/><br /><br/ ><br / >< br>< br/>'));}


	//check, if AJAX has requested error send
	public function check_error_AJAX_request(){	if (isset($_REQUEST['ErrorAjax'])){  	
	//	error_notify_admin__MYDDD(  rawurldecode($_REQUEST['ErrorAjax']) ,  urlencode($_REQUEST['p'])  );  exit("sent");
		// else { myyAjaxRequest("ErrorAjax="+encodeURIComponent(location.href)+"&p=". (!empty($GLOBALS['post']) ? $GLOBALS['post']->ID : '') ."&bla=b","","POST"); alert("ERROR_185_from_header_php. Please, tell this error code to the administrator"); return false; }
	}}

	public function error_notify_admin($error_msg=false,$postidd=false){ return error_notify_admin__MYDDD($error_msg,$postidd); }
	public function error_notify_admin__MYDDD($error_msg=false,$postidd=false){ 	if (test_environment) return;
		// usage https://github.com/tazotodua/useful-javascript/blob/master/AJAX-examples
		//'<script type="text/javascript">myyAjaxRequest('error_ajaxx=' + encodeURIcomponent(document.URL) + '&p= [[[[$GLOBALS['post']->ID]]]] &bla=blabla');</script>';
			$message	="\r\n\r\n\r\n\r\n\r\n\r\n===============================================================".date("Y-m-d H:i:s")."\r\n" . $error_msg. ' ||| URL:'.  ($postidd ?  get_permalink($postidd) : "") . " | " . $_SERVER['REQUEST_URI']. ' | REFERER:'. $_SERVER['HTTP_REFERER']."\r\n\r\nbacktrace:\r\n".print_r(debug_backtrace(), true); 
		
		//write into file
			$file=moduleDIR.'/zzz___ERRORnotifications_FROM_site_acts_'.my_site_variables__secret('rand_name', RandomString(11)).'.txt';
			$this->file_put_contents($file,$message, FILE_APPEND);
		// send to mail
			$subjectt	='error_'.$_SERVER['HTTP_HOST'];
			$message=str_replace(array("\r\n","\n"),"<br/>",$message);  $message=str_replace(array("\s"," ","\t"),"&nbsp;",$message);
			return my_mail($this->error_to_mailaddress, $subjectt, $message, default_mail_headers() );
			return "mail was not sent... check functionality";
	}


	// get_remote_data('https://tinyurl.com/api-create.php?url='.$url); 
	public function get_short_link($url) { return $url; }



	// C# to PHP encryption/description :
	// https://github.com/skotz/csharp-to-php-encryption
	// https://www.codeproject.com/Articles/223081/Encrypting-Communication-between-Csharp-and-PHP
	// https://gist.github.com/odan/138dbd41a0c5ef43cbf529b03d814d7c

	public function encrypt_c($plaintext, $password, $method= 'aes-256-cbc'){
		// Must be exact 32 chars (256 bit)
		$password = substr(hash('sha256', $password, true), 0, 32);		
		// IV must be exact 16 chars (128 bit)
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
		return base64_encode(openssl_encrypt($plaintext, $method, $password, OPENSSL_RAW_DATA, $iv));
	}

	public function decrypt_c($plaintext, $password, $method= 'aes-256-cbc'){
		// Must be exact 32 chars (256 bit)
		$password = substr(hash('sha256', $password, true), 0, 32);		
		// IV must be exact 16 chars (128 bit)
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
		return openssl_decrypt(base64_decode($encrypted), $method, $password, OPENSSL_RAW_DATA, $iv);
	}
	
	public function allowed_extensions_of_url( $url ) {
		$ext = array( 'jpeg', 'jpg', 'gif', 'png' );
		$info = (array) pathinfo( parse_url( $url, PHP_URL_PATH ) );
		return isset( $info['extension'] ) && in_array( strtolower( $info['extension'] ), $ext, TRUE );
	}

	public function momery_usage(){ return memory_get_usage()/pow(1024,2); }


	public function check_analytics()
	{
		if ( property_exists($this,'google_analytics_ID') && !empty($this->google_analytics_ID) ) $this->google_analytics_script($this->google_analytics_ID);
		if ( property_exists($this,'google_tag_manager_ID') && !empty($this->google_tag_manager_ID) ) $this->google_tag_manager_script($this->google_analytics_ID);
		if ( property_exists($this,'top_ge_ID') && !empty($this->top_ge_ID) ) $this->top_ge_script($this->top_ge_ID);
	}
	
	public function google_analytics_script($id){ if (empty($id)) return '';
		$out = 
		'<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id='.$id.'"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag("js", new Date());

		gtag("config", "'.$id.'");
		</script>';
		
		if ($this->Track_404_with_GoogleAnalytics && function_exists('is_404') && is_404() )
		{
			$out .= "<script>  ga('send', 'event', 'error', '404', 'page: ' + document.location.pathname + document.location.search + ' ref: ' + document.referrer, {nonInteraction: true});  </script>";
		}
		return $out;
	}

	public function google_tag_manager_script( $id, $show_noscript=true )
	{
		$out = 
		"<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','".$id."');</script>
	<!-- End Google Tag Manager -->";
		
		if($show_noscript) {
			$out .= 
			'<!-- Google Tag Manager (noscript) -->   <noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$id.'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>   <!-- End Google Tag Manager (noscript) -->';
		}

		if (is_404()) {
			$out .= 
			"<script>
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
				'event' : '404'
			});
			</script>";
		}	
		return $out;
	}
	
	public function top_ge_script( $id )
	{
		$out = 
		'<!-- TOP.GE ASYNC COUNTER CODE --><div id="top-ge-counter-container" data-site-id="'.$id.'"></div><script async src="//counter.top.ge/counter.js"></script><!-- / END OF TOP.GE COUNTER CODE -->';
		return $out;
	}


	public function non_empty_arrayyyy($x=array()){ if (!is_array($x) || empty($x) || (is_array($x) && count($x)==1 && $x[0]==null)  ){ return array('');} else return $x; }
		
	public function validate_email($email)	{
		$regex = '/([a-z0-9_.-]+)'. # name
		'@'. # at
		'([a-z0-9.-]+){2,255}'. # domain & possibly subdomains
		'.'. # period
		'([a-z]+){2,10}/i'; # domain extension 
		if($email == '') { 	return false;	}
		else {$eregi = preg_replace($regex, '', $email);	}
		return empty($eregi) ? true : false;
	}

	// https://php.net/manual/en/filter.filters.sanitize.php
	public function sanitize_digits($string){ return filter_var($string,FILTER_SANITIZE_NUMBER_INT);}
	public function sanitize_text($string) { return filter_var($string,FILTER_SANITIZE_STRING);}
	public function sanitize_url($string)  { return filter_var($string,FILTER_SANITIZE_SPECIAL_CHARS);}


	public function url_correction_for_html_output($content){ 
		return preg_replace_callback( 
			'/\<(img|link|iframe|frame|frameset|script|embed|video|audio)([^>]*)/si', 
			function($matches) { return '<'.$matches[1].preg_replace('/=(\"|\')(http(s|):)/si','=$1', $matches[2]);	}, 
			$content
		);
	}

	public function GetJsonedFileData($path,$AsArray=false)	{ return json_decode( (file_exists($path) ? $this->file_get_contents($path) : '{}'), $AsArray) ;  }
	public function GetSymbolData($name, $AsArray=false)		{ return array('symbol_data'=>GetJsonedFileData(GetSymbolPath($name), $AsArray)    )  ; }
	public function array_to_xml_output($array) {
		$xml_data = new SimpleXMLElement('<?xml version="1.0"?><xml_data></xml_data>');
		array_to_xml($array, $xml_data);
		//$result = $xml_data->asXML('/file/path/name.xml');
		return $xml_data->asXML();
	}

	public function array_to_xml( $data, &$xml_data ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){	$key = 'item'.$key; } //dealing with <0/>..<nuemric/> issues
			if( is_array($value) ) { $subnode = $xml_data->addChild($key);	array_to_xml($value, $subnode);	} 
			else {	$xml_data->addChild("$key",htmlspecialchars("$value"));	}
		}
	}

	public function SanitizeString($str){ return str_replace(array(' ','-',',','.','/','\\','|','!','@','#','$','%','^','&','*','(',')'),'_',   strip_tags( trim($str) )); }
	public function SanitizeSymbol($str){ return str_replace(array('/','\\','|','!','*'), '_',   strip_tags( strtoupper(trim($str) )) ) ; }

	// get-timezones : pastebin_com/4tXjgY7B

	// directory correction
	public function directory_canonicalize3($address)
	{
		$address = explode('/', $address);
		$keys = array_keys($address, '..');

		foreach($keys AS $keypos => $key)
		{
			array_splice($address, $key - ($keypos * 2 + 1), 2);
		}

		$address = implode('/', $address);
		$address = str_replace('./', '', $address);

		return $address;
	}
	public function directory_canonicalize2($address)
	{
		$address =preg_replace_callback(
			'/(.*?|)\/(.*?)(\/..*?)\b/i',  
			function ($matches){
				if(!empty($matches[3])){
					return ($matches[3]);
				}
				return $matches[0];
			},
			$address
		);
		return $address;
	}


	public function add_prefix_to_object_keys($object, $prefix){
		$new_object = new stdClass();
		foreach ($object as $k => $v) { 
			$new_object->{$prefix . $k} = $v;
		}
		return $new_object;
	}


	public function dieMessage($txt){
		echo 
		'<div style="padding: 50px; margin:100px auto; width:50%; text-align:center; line-height: 1.4; display:flex; justify-content:center; flex-direction:column; font-family: cursive; font-size: 1.7em; box-shadow:0px 0px 10px gray; border-radius: 10px;">'.
			'<div><h3>'.$txt.'</h3></div>'.
		'</div>';
		exit;
	}


	//====================================== # MAIL ===============================



	public function Return_If_Isset($var){ if (isset($var)) { return $var; }    else { return false; }  }
	public function Return_If_Not_Empty($var){ if (!empty($var)) { return $var; }    else { return false; }  }
	public function Return_If_Array_Key($array, $keyname){ if (array_key_exists($keyname, $array)) { return $array[$keyname]; }    else { return false; }  }

	
	public function include_wp($shortInit=false, $dir=false){
		if($shortInit)  { define ("SHORTINIT",true); }
		$wpload='/wp_load.php';
		if($dir){
			require_once($dir .$wpload);
		}
		else{
			for($i=0; $i<10; $i++){
				$target = str_repeat ( '../', $i).$wpload;
				if(file_exists($target) && require_once($target) ) 
					break;
			}
		}
	}

	//can only be used explicitly
	/*
	public function my_include_fonts($url_till_here, $fonts_sub_path, $font_family_tag,  $output_text_example=false, $text=""){
		$final_out='';
		if($output_text_example){
			$final_out .=  '<style type="text/css">.mystyllle ,.mystyllle div{background:#c6c6c4;}  .sconttt{background: #fffbe8; margin:2px 0 0 0; border:1px solid; padding:2px;} .sconttt .num{display: inline-block; float: left; margin: 0 10px 0 0; font-size: 0.7em;}</style>';
		}
		$label = substr(basename($fonts_sub_path),0, 3);
		
		//for sub-foldered fonts
		foreach(   $x=glob(__DIR__.'/'.$fonts_sub_path.'/*')      as $eachChild ){ 
			$i= (empty($i) ? 1 : $i+1);
			$f_out="\r\n";
			
			$myf = func($fonts_sub_path, $folderName,$eachfile ){
				$filen=basename($eachfile);
				$ext = pathinfo($eachfile, PATHINFO_EXTENSION);
				if ($ext=='eot'){$f_out .=
				'url("'.$url_till_here.'/'.$fonts_sub_path.'/'.$folderName.'/'.$filen.'?#iefix") format("embedded-opentype") myHint("IE6-9"),'."\r\n"; }
				else if ($ext=='ttf'){$f_out .=
				'url("'.$url_till_here.'/'.$fonts_sub_path.'/'.$folderName.'/'.$filen.'") format("truetype") myHint("Safari, Android, iOS"),'."\r\n"; }
				else if ($ext=='otf'){$f_out .=
				'url("'.$url_till_here.'/'.$fonts_sub_path.'/'.$folderName.'/'.$filen.'") format("opentype") myHint("everyone else"),'."\r\n"; }
				else if ($ext=='woff'){$f_out .=
				'url("'.$url_till_here.'/'.$fonts_sub_path.'/'.$folderName.'/'.$filen.'") format("woff") myHint("Modern Browsers "),'."\r\n"; }
				else if ($ext=='woff'){$f_out .=
				'url("'.$url_till_here.'/'.$fonts_sub_path.'/'.$folderName.'/'.$filen.'") format("woff2") myHint("Modernest Browsers "),'."\r\n"; }
				else if ($ext=='svg'){$f_out .=
				'url("'.$url_till_here.'/'.$fonts_sub_path.'/'.$folderName.'/'.$filen.'#Sylfaen") format("svg") myHint("iOS <4.1 "),'."\r\n"; }
			};
			
			if(is_dir($eachChild)){
				$files = array_filter(glob($eachChild.'/*'), 'is_file');
				foreach ($files as $eachfile){
					$f_out .= $myf($fonts_sub_path, $folderName=basename($eachChild),$eachfile );
				}
			}
			else{
				$f_out .=  $myf($fonts_sub_path, $folderName="", $eachChild);
			}
			$f_out .= 'url("//:") format("blanklinee");'."\r\n";
			$final_out .= '<style type="text/css">@font-face {  font-family: "'.$font_family_tag.$i.'"; 		src: '.$f_out.' }</style>';
			if($output_text_example){ $final_out .= '<div class="sconttt" style="font-family:'.$font_family_tag.$i.',arial,sylfaen,sans-serif;"><span class="num">'.$label.'_'.$i.') </span><span>'.$text .'</span></div>' ; }
		}
		
		$final_out = '<div class="myfontss">'.$final_out.'</div>';
		return $final_out ;
	}
	*/

	// custom always-loaded scripts 
	// script_url("css|js",  "public|admin")
	public function script_url($type="js", $kind="public", $with_tag=false) 
	{
		if ($type=='js'){
			return ($with_tag? '<script type="text/javascript" src="':'') . $this->pluginURL.'/library/default_library_puvox_JS.php?vers='.$this->changeable_JS_CSS_version . ($with_tag? '"></script>':'');
		}
		elseif ($type=='css'){
			return ($with_tag? '<link rel="stylesheet" href="':'') . $this->pluginURL.'style-'.$kind.'.css?vers='.$this->changeable_JS_CSS_version. ($with_tag? '"	type="text/css" media="all" />':'');
		}
	}
	public function my_loader_css_js($css=true, $js=true)
	{  	
		$admin = function_exists('is_admin') ? is_admin() : false;
		if ($css)	echo $this->script_url('css', ( $admin ? 'admin':'public'), true);
		if ($js) 	echo $this->script_url('js',  '', true);
	}
	public function my_loader_css_js_trigger($css=true, $js=true)
	{  	
		$this->my_loader_css_js($css=true, $js=true);
	}
	public function load_css_js($css=true, $js=true)
	{  	
		add_action( (is_admin() ? 'admin' : 'wp'). '_head',  [$this, 'my_loader_css_js_trigger'] );
	}

	// ======================================== STYLES =================================
	/* usage:
		$this->helpers->load_scripts_override = [
			'jquery'			=> ['screen'=>['admin'=>0, 'public'=>1]],
			'jquery-migrate'	=> ['screen'=>['admin'=>0, 'public'=>0]],
			'jquery-ui'			=> ['screen'=>['admin'=>1, 'public'=>1]],
			'bootstrap'			=> ['screen'=>['admin'=>0, 'public'=>1]],
			'less'				=> ['screen'=>['admin'=>0, 'public'=>0]],
			'font-awesome'		=> ['screen'=>['admin'=>1, 'public'=>1]],
			'google-fonts'		=> ['screen'=>['admin'=>0, 'public'=>0]],
			'fancybox'			=> ['screen'=>['admin'=>1, 'public'=>1]],
			'animate'			=> ['screen'=>['admin'=>1, 'public'=>1]],
			'hover'				=> ['screen'=>['admin'=>1, 'public'=>1]],
			'cookies'			=> ['screen'=>['admin'=>1, 'public'=>1]],
			'spin'				=> ['screen'=>['admin'=>0, 'public'=>0]],
		];
	*/
	public $load_scripts_override = [
		
	];
	public $load_scripts = 
	[
		'jquery'		=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'js' => '//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'
		]],
		'jquery-migrate'=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'js' => '//cdnjs.cloudflare.com/ajax/libs/jquery-migrate/3.0.1/jquery-migrate.min.js'
		]],
		'jquery-ui'		=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css' =>'//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', 
			'js' => '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
		]],
		'bootstrap'		=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css'=> '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', 
			'js' => '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js',
		]],
		'less'			=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'js' => '//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js',
		]],
		'font-awesome'	=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css'=> '//use.fontawesome.com/releases/v5.6.3/css/all.css', 
		]],
		'font-awesome-animations1'=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css'=> '//cdnjs.cloudflare.com/ajax/libs/font-awesome-animation/0.2.1/font-awesome-animation.min.css',
		]],  // https://l-lin.github.io/font-awesome-animation/
		'google-fonts'	=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css' => '//fonts.googleapis.com/css?family=PT+Sans+Caption:400,700&subset=latin,latin-ext'  
		]],
		'fancybox'		=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css' => '//cdnjs.cloudflare.com/ajax/libs/fancybox/3.2.5/jquery.fancybox.min.css',  
			'js'  => '//cdnjs.cloudflare.com/ajax/libs/fancybox/3.2.5/jquery.fancybox.min.js', 
		]],  // http://fancyapps.com/fancybox/
		'animate'		=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css' => '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css', 
			'js'  => '//cdnjs.cloudflare.com/ajax/libs/animateCSS/1.2.2/jquery.animatecss.min.js', 
		]],  //https://codepen.io/strapro/pen/dIqAH    https://daneden.github.io/animate.css/ 
			/*
			//  <span class="animated bounceIn">hello</span>
			$('#your-id').animateCSS('fadeIn', {
				delay: 1000,
				callback: function(){
					console.log('Boom! Animation Complete');
				}
			});

			function animationClick(element, animation, removeOrNot){
				$=jQuery;
				element = $(element);
				element.click(
					function() {
					element.addClass('animated ' + animation);
					//wait for animation to finish before removing classes
					if(removeOrNot){
						window.setTimeout( function(){
							element.removeClass('animated ' + animation);
						}, 2000);
					}
					}
				);
			};   
			*/
			
		'cookies'		=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'js' => 'https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.2.0/js.cookie.min.js',
		]],
		'spin'			=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'js'  => '//cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js',
		]],  // http://spin.js.org/
		'hover'			=> ['screen'=>['admin'=>0, 'public'=>0], 'urls'=>[
			'css'  => '//cdnjs.cloudflare.com/ajax/libs/hover.css/2.3.1/css/hover-min.css'
		]], // https://ianlunn.github.io/Hover/
	];
 
	public function my_styles_hook($pure_php=false) {
		$current_screen = !function_exists('is_admin') || !is_admin() ? 'public' : 'admin';
		$all = array_merge_recursive($this->load_scripts, $this->load_scripts_override);
		foreach ($all as $name=>$block)
		{
			if($all[$name]['screen'][$current_screen])
			{
				foreach ($block['urls'] as $type=>$url)
				{ 
					$type_ = ($type=="js") ? 'script' : ($type=="css" ? 'style' : $type);
					if ($pure_php===true) 
					{
						if ($type_=='style')
							echo '<link rel="stylesheet" href="'.$url.'" type="text/css" media="all" />';
						else
							echo '<script src="'.$url.'"></script>';
					}
					else
					{
						$this->register_stylescript($type_, $name, $url);
					}
				}
			}
		}
	}

	//$GLOBALS["Javascript_Image_correction_MyClassnames"] = array (
	//	//array("img_classname"=>"js_sized1",			"desired_widthh"=>'0', "desired_heightt"=> '0',		"parenttClass" =>"ThumbnPlc" ),
	//};


	public function filedate($file){
		return date("Y-M-D--H-i-s", filemtime($file) ); 
	}


	public function TRANSLL($phraze,$LNG=false, $desired=array())	{ return apply_filters('MLSS', $phraze, ($LNG ? $LNG: (defined('LNG') ? LNG : '' )  ),  $desired    );   }

	public function MY_LANGSS(){
		if (!function_exists('LANGS__MLSS')){
			if(!empty($GLOBALS['my_custom_langs'])) return $GLOBALS['my_custom_langs'];
			if(defined('ERROR_SHOWN__MLSS') || DISABLE_MLSS_ERROR ) {return array();}	

			$xx344=debug_backtrace();
				echo '<script>alert(\'plugin "Multi-Language Site (basis)" seems not installed. please install it.\r\n\r\n\ File:'. $xx344[0]['file'] .' \r\n\ line:'.$xx344[0]['line'].'\');</script>';  
				if (!is_admin() && !in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {die('error_45y4e5ge4g'); }	 define('ERROR_SHOWN__MLSS',1);
		}
		else{  return LANGS__MLSS();  }
	}
	
	 
	//if ( !$this->above_version('5.4') ) { echo("php_version is ". PHP_VERSION ." (quite old). HIGHLY recomended to update to higher version, or this program might not funciton normally ". __FILE__ ); }
	public function above_version($version= "5.4"){
		return version_compare(phpversion(), $version, '>=');
	}

	public function noindex_meta_tag() { return '<meta name="robots" content="noindex, nofollow">'; }
	
	//function to replace double-slashes with one slashes
	public function remove_double_slashes($input){
		$input=str_replace('//','/', $input);  $input=str_replace('\\\\','\\', $input);  return str_replace(':/','://', $input);
	}
	
	public function replace_slashes($path){
		return 	str_replace( ['/','\\',DIRECTORY_SEPARATOR], '/', $path); 
	}
	public function remove_extra_slashes($path){
		return 	str_replace( '//', '/', $path); 
	}
	
	public function urlify($path){
		return str_replace( '\\', "/", $path); 
	}
	public function IsRestirctedDirecotryRequested($url=false, $dieORreturn=true ){ if (!$url) {$url=$_SERVER['REQUEST_URI'];}
		$url =stripslashes($url);
		if (  stristr($url,'\\')  ||   substr($url, 0, 2)=='..' || stristr($url,'../')  ||  stristr($url,'/..')  ||  stristr($url,'?')  ||  stristr($url,'*')  ||  stristr($url,'.php')	){
			if ($dieORreturn) {die("incorrect path requested.. error4292");} 	else{ return true;}
		}
	}

	public function directory_separatored($path){
		return str_replace(array('/','\\'),DIRECTORY_SEPARATOR, $path); 
	}

	public function valueToString( $value ){
		return is_bool($value) ? ($value ? 'true' : 'false' ) : strip_tags(  $value ) ;
	}
	public function stringToValue( $value ){
		return is_bool($value) ? $value : ( !is_string($value) ?  $value : ( $value =='true' ? true : (  $value =='false' ? false : $value) ) );
	}

	public function argv_to_array($argv_)
	{
		$array=[];
		if (!empty($argv_[1])) parse_str($argv_[1], $array);	//convert command line to $_GET
		return $array;
	}
	
	public function serialize_argv($argvs)
	{
		if(empty($argvs) || !is_array($argvs)) return $argvs;
	
		$new_ar=[];
		foreach($argvs as $key=>$value)
		{
			if(stripos($value,'=')===false)
			{
				$new_ar[$key] = $value;
			}
			else{
				parse_str($argvs[$key], $params);
				$key1=array_keys($params)[0];
				if(!empty($argvs) && is_array($params))
					$new_ar[$key1] =  $params[$key1];
			}
				
		}
		return $new_ar;
	}
	

	public function include_wp_without_db($wp_path)
	{
		define( 'SHORTINIT', true );
		define( 'SHORTINIT_WITHOUT_DB', true );
		$wp_config_PATH = $wp_path .'/wp-config.php';
	
		// check if needed modification is already done
		$wp_settings_PATH = $wp_path .'/wp-settings.php';
		$addition = ' if (SHORTINIT_WITHOUT_DB) return false;';
		$target_hint = 'require_wp_db();';
		$content = $this->file_get_contents($wp_settings_PATH);
		if (strpos($content, $target_hint.  $addition) ===false )
		{
			$this->file_put_contents($wp_settings_PATH, str_replace($target_hint, $target_hint.$addition, $content));
		}
	
		//check if wp-config exists
		if (!file_exists($wp_config_PATH)){
			copy($wp_path .'/wp-config-sample.php', $wp_config_PATH);
		}
		require_once( $wp_config_PATH );
	}
	
	
	public function array_fields($array, $parent="plugin_slug[sample][sub]", $pairs=false)
	{ 
		echo '<div class="inpHolder">';

		echo '<div class="inputsBlock">';
		if (is_array($array) && !empty($array)) 
		{
			foreach ($array as $optName=>$value)
			{
				echo $this->field_out_helper1($parent, $optName, $value, $pairs) ;
			}
		}

		$sample_field = $this->field_out_helper1($parent, "", "", $pairs);
		//echo $sample_field;
		echo '</div>';
		?>
				<?php $unique = $this->sanitizer($parent); ?>
		<a class="button" href="#" onclick="return <?php echo $unique;?>_addNewArrayField_k(this);" class="addNewArrayInput"><?php _e('Add New');?></a>
		<script>
		function <?php echo $unique;?>_addNewArrayField_k(el)
		{ 
			var targetEl = el.parentNode.parentNode.parentNode.getElementsByClassName("eachInputBlock")[0];
			var rand=  Math.random().toString(36).substring(2); 
			var newElString = targetEl.outerHTML.replace( /(inputKey_[\w]*)/g, "inputKey_"+rand).replace(/value="(.*?)"/g, 'value=""');
			targetEl.parentNode.insertAdjacentHTML("beforeend", newElString);
			return false;
		}
		</script>
		<?php
		echo '</div>';
	}

	public function field_out_helper1($parent, $optName, $value, $pairs)
	{
		$output='<div class="eachInputBlock">';
		$rand= "inputKey_".rand(1,999999)."_".rand(1,999999)."_".rand(1,999999);  
		if (!$pairs) { 
			$key = (!empty($optName) ? $optName : $rand);
			$output .= '<input name="'.$parent.'['.$key.']"  class="eachInput each_'.$key.' regular-text" type="text" value="'.$value.'"  placeholder="" />';
		} else {
			$output .= '<input name="'.$parent.'['.$rand.'][name]"  class="eachInput each_'.$rand.' medium-text _key" type="text" value="'. (!empty($optName) ? $optName : "").'"  placeholder="name" />';
			$output .= '<input name="'.$parent.'['.$rand.'][value]"  class="eachInput each_'.$rand.' medium-text _value" type="text" value="'.$value.'"  placeholder="value" />';
		}
		$output .='</div>';
		return $output;
	}



	public function sanitize_text_field($text)
	{
		if(function_exists('sanitize_text_field'))
			return sanitize_text_field($text);
		else
			return $text;
	}
	
	
	// 	<!-- GEORGIAN automatic keyboard while typing in SEARCH --> <script type="text/javascript" src="'. $this->baseURL .'/library/js/geokbd.js"></script>

	public function createHtaccessDirDisableBrowsing($dir)
	{
		$htaccess = $dir .'/.htaccess';
		$myCont = 
		'# Disable Browsing for this directory '."\r\n".
		'<IfModule mod_rewrite.c>'."\r\n".
			'Options -Indexes'."\r\n".
		'</IfModule>';
			//'RewriteEngine on'."\r\n".
			//'RewriteRule !^'.$this->pma_name_randomed.'($|/) http://example.com/good_bye [L,R=301]'."\r\n".
		
		$cont = $myCont . (!file_exists($htaccess) ? "" :  "\r\n\r\n". $this->file_get_contents($htaccess) );
		$this->file_put_contents($htaccess, $cont );

		if (!file_exists($x=$dir .'/index.html')) 
			$this->file_put_contents($x, " ");
	}

	public function arrayFieldsResort($ar)
	{
		$new=[];
		foreach($ar as $key=>$val)
		{
			$new[ $this->sanitize_text_field($val["name"]) ] = $this->sanitize_text_field($val["value"]);
		}
		return $new;
	}


	public function get_fb_name_regex($fb_url){
		preg_match('/'.preg_quote('^(?:https?://)?(?:www.|m.|touch.)?(?:facebook.com|fb(?:.me|.com))/(?!$)(?:(?:\w)#!/)?(?:pages/)?(?:[\w-]/)?(?:/)?(?:profile.php?id=)?([^/?\s])(?:/|&|?)?.*$/'), $fb_url, $n);
		return $n[1];
	}
	
	public function display_errors()
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting( E_ALL );
		
		
		// ini_set("display_errors", 1);
		//htaccess:
		//php_flag log_errors on
		//php_value error_log /home/FTP_username/public_html/error_log.txt
		
			//ini_set("log_errors", 1);
			//error log locations
			//ini_set("error_log", dirname(plugin_main_indexfile)."/zzz___php-my-errors_".my_site_variables__secret('rand_name', RandomString(11)).".log");
		  //error_log( "Hello, errors!" );	
	}
	
	//example testmode : pastebin_com/bUncPcFD



	// mb_str_word_count: pastebin_com/745RQRbY
	
}} // class



	if (!class_exists('Cryptor_Puvox'))
	{
		// minified two-way encryption ( see: https://goo.gl/hZJaEB )
		class Cryptor_Puvox { const METHOD = 'aes-256-ctr'; const HASH_ALGO = 'sha256'; public static function unsafe_encrypt($message, $key, $encode = false) { $nonceSize = openssl_cipher_iv_length(self::METHOD); $nonce = openssl_random_pseudo_bytes($nonceSize); $ciphertext = openssl_encrypt( $message, self::METHOD, $key, OPENSSL_RAW_DATA, $nonce ); if ($encode) { return base64_encode($nonce.$ciphertext); } return $nonce.$ciphertext; } public static function unsafe_decrypt($message, $key, $encoded = false) { if ($encoded) { $message = base64_decode($message, true); if ($message === false) { throw new Exception('Encryption failure'); } } $nonceSize = openssl_cipher_iv_length(self::METHOD); $nonce = mb_substr($message, 0, $nonceSize, '8bit'); $ciphertext = mb_substr($message, $nonceSize, null, '8bit'); $plaintext = openssl_decrypt( $ciphertext, self::METHOD, $key, OPENSSL_RAW_DATA, $nonce ); return $plaintext; } public static function encrypt($message, $key, $encode = false) { $key = hex2bin(implode(unpack("H*", $key))); list($encKey, $authKey) = self::splitKeys($key); $ciphertext = self::unsafe_encrypt($message, $encKey); $mac = hash_hmac(self::HASH_ALGO, $ciphertext, $authKey, true); if ($encode) { return base64_encode($mac.$ciphertext); } return base64_encode($mac.$ciphertext); } public static function decrypt($message, $key, $encoded = false) { $message= base64_decode($message); $key = hex2bin(implode(unpack("H*", $key))); list($encKey, $authKey) = self::splitKeys($key); if ($encoded) { $message = base64_decode($message, true); if ($message === false) { throw new Exception('Encryption failure'); } } $hs = mb_strlen(hash(self::HASH_ALGO, '', true), '8bit'); $mac = mb_substr($message, 0, $hs, '8bit'); $ciphertext = mb_substr($message, $hs, null, '8bit'); $calculated = hash_hmac( self::HASH_ALGO, $ciphertext, $authKey, true ); if (!self::hashEquals($mac, $calculated)) { throw new Exception('Encryption failure'); } $plaintext = self::unsafe_decrypt($ciphertext, $encKey); return $plaintext; } protected static function splitKeys($masterKey) { return [ hash_hmac(self::HASH_ALGO, 'ENCRYPTION', $masterKey, true), hash_hmac(self::HASH_ALGO, 'AUTHENTICATION', $masterKey, true) ]; } protected static function hashEquals($a, $b) { if (function_exists('hash_equals')) { return hash_equals($a, $b); } $nonce = openssl_random_pseudo_bytes(32); return hash_hmac(self::HASH_ALGO, $a, $nonce) === hash_hmac(self::HASH_ALGO, $b, $nonce); } }


		// just extend
		class Cryptor123 extends Cryptor_Puvox{
			
			public static function encrypt($content, $randomVarName=false, $false=false){
				if( ! $randomVarName){
					$randomVarName= 'cryptor_var';
				}
				$key= random_val($randomVarName);
				return parent::encrypt($content,$key);
			}
			
			public static function decrypt($content, $randomVarName=false, $false=false){
				if( ! $randomVarName){
					$randomVarName= 'cryptor_var';
				}
				$key= random_val($randomVarName);
				return parent::decrypt($content,$key);
			}
		}
	}



#endregion
//==========================================================================================================
//==========================================     ### PHP codes     =========================================
//==========================================================================================================
 



 

































































 
//==========================================================================================================
//==========================================================================================================
//======================================== 2) Library of WP functions  =====================================
//==========================================================================================================
//==========================================================================================================
#region 2
if (!class_exists('standard_wp_library__PuvoxSoftware')){
class standard_wp_library__PuvoxSoftware extends standard_php_library__PuvoxSoftware  
{
	public function __construct($args=[])
	{
		parent::__construct($args);
		// others
		$this->this_file_link= '';//$this->baseURL . $this->urlify( explode( basename($this->baseURL), __FILE__  )[1] );
		$this->PHP_customCALL= '';//$this->this_file_link .'?custom_php_load=scripts_load&actionn=';
		
		//get blog-slug
		if(is_multisite()){
			global $blog_id;  if(empty($blog_id)) $blog_id = get_current_blog_id();  
			$current_blog_details = function_exists('get_sites') ? get_site($blog_id) : get_blog_details( array( 'blog_id' => $blog_id ) );
			$b_slug = basename($current_blog_details->path);
		} 
		$this->BLOGSLUG = (!empty($b_slug)? $b_slug : basename($this->homeFOLDER) );
		

		//extend log-in expiration
		if ($this->property('auth_expiration_hours'))	 	$this->init__cookieexpiration();
		if ($this->property('search_items_amount_in_menu')) $this->init__quicksearch();
		if ($this->property('extend_shortcodes')) 			$this->extendShortcodes();
		if ($this->property('disable_update')) 				$this->init__disableupdate();
		add_action( 'admin_head', [$this, 'admin_menuu_style1'] );
		
		//load desired scripts
		add_action( 'wp_enqueue_scripts', 		[$this, 'my_styles_hook'], 9); 
		add_action( 'admin_enqueue_scripts',	[$this, 'my_styles_hook'], 9);
	}
	
 
	//when is_admin or when page is unknown (for example, custom page or "wp-login.php" or etc... )
	public function Is_Backend(){
		$includes=get_included_files();
		$path	= str_replace( ['\\','/'], DIRECTORY_SEPARATOR, ABSPATH);
		return (is_admin() || in_array($path.'wp-login.php', $includes) || in_array($path.'wp-register.php', $includes) );
		//return (!!array_intersect([$ABSPATH_MY.'wp-login.php',$ABSPATH_MY.'wp-register.php'] , get_included_files())) ;
	}
		
	public function is_gutenberg($active=true){
		return ( function_exists( 'is_gutenberg_page' ) && (!$active || $this->is_gutenberg_page() ) );
	}
		
	public function is_gutenberg_page($active=true){
		if (is_admin()) {
			global $current_screen;
			if (!isset($current_screen)) {$current_screen = get_current_screen();}
			if ( method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() ||  $this->is_gutenberg(true) ) {
				return true;
			}
		}
		return false;
	}

	//Get Blog slug, i.e. "subdir"  from "http://example.com/subdir/"
	public function get_blog_name(){
		if(is_multisite()){
			global $blog_id;
			$current_blog_details = !function_exists('get_blog_details') ? get_site($blog_id) : get_blog_details( ['blog_id' => $blog_id] );
			$b_slug = basename($current_blog_details->path);
			return $b_slug;
		}
		return false;
	}
	
	
		

	public function sqlResultsToArray($tableName, $first_key, $second_key=false, $data_key=false)
	{ 
		$array=$this->objectToArray($GLOBALS['wpdb']->get_results("SELECT * FROM ". $tableName));

		$new_array=[];
		foreach($array as $id=>$block)
		{
			if(array_key_exists($first_key, $block))
			{
				if ($second_key)
				{
					if(array_key_exists($second_key, $block))
					{
						$new_array[$block[$first_key]][$block[$second_key]] = $data_key ? json_decode($block[$data_key]) : $block;
					}
				}
				else
				{
					$new_array[$block[$first_key]] = $data_key ? json_decode($block[$data_key]) : $block;
				}
			}
		}
		return $new_array;
	}


	public function get_locale__SANITIZED(){
		return ( get_locale() ? "en" : preg_replace('/_(.*)/','',get_locale()) ); //i.e. 'en'
		//$x=$GLOBALS['wpdb']->get_var("SELECT lng FROM ".$this->options." WHERE `lang` = '".$lang."'"); return !empty($x);
		// preg_replace('/[^\w\d_\-]/', '',  filter_var($input,	FILTER_SANITIZE_STRING)	);
	}

	public function blog_prefix()
	{
		$blog_prefix = '';
		if ( is_multisite() && ! is_subdomain_install() && is_main_site() && 0 === strpos( get_option( 'permalink_structure' ), '/blog/' ) ) {
			$blog_prefix = '/blog';
		}
		$this->blog_prefix = $blog_prefix;
		return $blog_prefix;
	}

	public function path_after_blog()
	{
		$prf = $this->blog_prefix();
		$path = $this->pathAfterHome; 
		return ( ($prf=="/blog") ? str_replace('/blog/', '', '/'.$path) : $path );
	}

	public function readUrl( $url){
		return wp_remote_retrieve_body(  wp_remote_get( $url )  );
	}
		

	public function checkMyselfAgainstModification()
	{
		//if ($this->is_development) return;
		$name = '_puvox_default_lib_last_revision';
		$opt= $this->get_option_CHOSEN($name, 0 );
		$days=7;
		if( time() - $opt > $days* 86400 )
		{
			//$wpURL  =readUrl   
			//https://plugins.trac.wordpress.org/browser/simple-post-views-count/trunk/default_library_puvox.php
			update_option_CHOSEN($name, time() );
		}
		if(time() - $opt < 0 ){
			update_option_CHOSEN($name, 0 );
		}
	}

	public function NonceCheck($value, $action_name){if ( !isset($value) || !wp_verify_nonce($value, $action_name) ) { die("error_5151, Refresh the page");}}	

	// ====================== tinymce buttons ==================== //
	public function tinymce_funcs()
	{
		// Add button in TinyMCE 
		add_action( 'admin_init', 			function(){
				add_filter( 'mce_external_plugins',	[$this, 'tinymce_js'] );
				add_filter( 'mce_buttons_2',		[$this, 'register_buttons'] );
				//add_filter( 'tiny_mce_version',  function ( $ver ) { return $ver + 3;}  );
		} );
		//tinymce buttons if needed
		$this->tinymce_buttons_body();
		foreach($this->tinymce_buttons as $each_button){
			if( !empty($each_button["shortcode"]) ){
				add_shortcode($each_button["shortcode"], [$this, $each_button["shortcode"]] );
			}
		} 
	}
	
	/*
	$this->my_default_buttons= array('superscript', 'subscript') +  array( "|", "youtube_video","audioo", "add_spacee_button", "removeline_button", "abzac_button","videomovie", "lists", "script");

	// ========================== ADD BUTTON =============================== //
		//Add Tinymce Several Buttons
		add_action('admin_init', function () { 
			if ( get_user_option('rich_editing') == 'true') {
				add_filter('mce_external_plugins', function($plugin_array) {
					return  array_merge($plugin_array,   array('MyButtonss1_pipp'=> PHP_customCALL_1.'myButons88')  ); 
				});		
				add_filter('mce_buttons_2',  function($buttons){
					return  array_merge($buttons,   array_merge($this->my_default_buttons, (!empty($GLOBALS['mybutons_Array_2']) ? $GLOBALS['mybutons_Array_2'] : array()) ) );  
				});
			} 
		});
		//this is must for REFRESHING!
		add_filter( 'tiny_mce_version',  function ($ver) {  $ver += 3;  return $ver;} );
	// ========================== ADD BUTTON =============================== //
	
	
	
	
	*/

	/*
	add personal notes page:
	
	
	add_action('admin_menu', 'mynots222'); 
	function mynots222() 	{add_menu_page('myNOTES', 'myNOTES', 'read','mynotes-urllll', 'ntsFNC222');}
	function ntsFNC222() {
		if (isset($_POST['nmtIDv'])) {update_option('myfuture_notes_contentt',$_POST['nmtIDv']);}
		$contn = get_option('myfuture_notes_contentt');
					echo 
		'<form style="margin:50px 0 0 0;" action="" method="POST">ამ გვერდზე დაიმახსოვრეთ რაიმე პირადი ან სხვადასხვა ჩანაწერები (მაგ: სამომავლო ცვლილებების სია ან ა.შ.)';
			if (current_user_can('create_users')) { echo '<div style="color:red;font-style:italic;">(იმისათვის რომ ადამიანი აქ შემოვიდეს, საჭიროა შექმანთ რაიმე საცდელი "იუზერი"(subscriber ტიპის) და მაგ იუზერის პაროლი შეგიძლიათ გაუგზავნოთ ვისაც გინდათ, და მხოლოდ ამ გვერდზე ექნებათ წვდომა)</div>';}
					echo
			'<div class="mpmybook_textareaDIV"> 
				<style>	#nmtIDv_div{width:100% !important; height:1000px !important;}</style>';
				wp_editor($contn, 'nmtIDv', $settings = array(
				'editor_class'=>'notesmyyCLASS',    'textarea_name'=>'mynots123', 'editor_height'=>'1000px', 'textarea_rows'=>'1000',
				'tinymce'=>true ,'wpautop'=>false,	'media_buttons'=>true,	'teeny'=>false,	'quicktags'=>false,		'drag_drop_upload'=>true )); echo
			'</div>
			<br/><input style="position:fixed;left:45%;bottom:10px;background-color:#1FC81F;" type="submit" value="SAVE" />
		</form>';
	}
	*/
	
	
	/*
	

	// ====  Make pretty, categorized permalinks ( https://wordpress.stackexchange.com/a/167992/33667 )  =====
	if($this->definedTRUE('force_categorized_permalinks')){
		if (defined('my_Additional_post_typesss')) { 
			foreach (json_decode(my_Additional_post_typesss) as $each) {		$GLOBALS['my_Permalinked_post_typesss'][] = $each['name']; 	}
		}
		add_filter('post_type_link',	'my_func88888', 6, 4 );
		add_action('pre_get_posts',		'my_func4444',	12); 
		//===STEP 2  (create desired PERMALINKS)
		public function my_func88888( $post_link, $post, $sdsd){
			if (!empty($post->post_type) && in_array($post->post_type, $GLOBALS['my_Permalinked_post_typesss']) ) {
				$SLUGG = $post->post_name;
				$post_cats = get_the_category($post->ID);		
				if (!empty($post_cats[0])){	$target_CAT= $post_cats[0];
					while(!empty($target_CAT->slug)){
						$SLUGG =  $target_CAT->slug .'/'.$SLUGG; 
						if	(!empty($target_CAT->parent)) {$target_CAT = get_term( $target_CAT->parent, 'category');} 	else {break;}
					}
					$post_link= get_option('home').'/'. urldecode($SLUGG);
				}
			}
			return  $post_link;
		}


		// STEP 3  (by default, while accessing:  "EXAMPLE.COM/category/postname"     WP thinks, that a standard post is requested. So, we are adding CUSTOM POST TYPE into that query.
		public function my_func4444($q){    
			if ($q->is_main_query() && !is_admin() && $q->is_single){
				$final_types=array();
				$query_p_type=$q->query_vars['post_type'];
				if (is_array($query_p_type))	{$final_types=array_merge($query_p_type,$GLOBALS['my_Permalinked_post_typesss']);}
				elseif(!empty($query_p_type))	{$final_types[]=$query_p_type;  $final_types=array_merge($final_types,$GLOBALS['my_Permalinked_post_typesss']);}
				else							{$final_types[]='post';			$final_types=array_merge($final_types,$GLOBALS['my_Permalinked_post_typesss']);}
				$final_types=array_filter($final_types);
				$q->set( 'post_type', $final_types );
			}
			return $q;
		}
	}
	
	*/
	
	
	public function shapeSpace_allowed_html() {

		$allowed_tags = array(
			'a' => array(
				'class' => array(),
				'href'  => array(),
				'rel'   => array(),
				'title' => array(),
			),
			'abbr' => array(
				'title' => array(),
			),
			'b' => array(),
			'blockquote' => array(
				'cite'  => array(),
			),
			'cite' => array(
				'title' => array(),
			),
			'code' => array(),
			'del' => array(
				'datetime' => array(),
				'title' => array(),
			),
			'dd' => array(),
			'div' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl' => array(),
			'dt' => array(),
			'em' => array(),
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array(),
			'i' => array(),
			'img' => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'li' => array(
				'class' => array(),
			),
			'ol' => array(
				'class' => array(),
			),
			'p' => array(
				'class' => array(),
			),
			'q' => array(
				'cite' => array(),
				'title' => array(),
			),
			'span' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'strike' => array(),
			'strong' => array(),
			'ul' => array(
				'class' => array(),
			),
		);
		
		return $allowed_tags;
	}	
	
	
	public function my_site_variables__secret($var_name=false, $value=false){
		$final= $this->SITE_VARIABLES = get_site_option('site_variables_my_secret',array());
		if ($var_name) {
			if(array_key_exists($var_name, $this->SITE_VARIABLES)){
				$final = $this->SITE_VARIABLES[$var_name];
			}
			elseif($value) {
				$final = $this->SITE_VARIABLES[$var_name]=$value;
				update_site_option('site_variables_my_secret', $this->SITE_VARIABLES);
			}
			else{
				$final = '';
			}
			return $final;
		}
		else{ return $this->SITE_VARIABLES; }
	}

	public function tinymce_js( $plugin_array )	 {
		$plugin_array[ "button_handle_" . $this->slug ] = $this->homeURL  . '?tinymce_buttons_'.$this->slug;
		return $plugin_array;
	}
	public function register_buttons( $buttons ) {
		$button_names = array_map(  function($ar){ return $ar['button_name']; }, $this->tinymce_buttons );
		return array_merge( $buttons,   $button_names );
	}
	public function tinymce_buttons_body( )
	{
		if( ! isset($_GET['tinymce_buttons_'. $this->slug] ) ) return;

			session_cache_limiter('none');
		// http://stackoverflow.com/a/1385982/2377343
		//Caching with "CACHE CONTROL"
			header('Cache-control: max-age='. ($year=60*60*24*365) .', public');
		//Caching with "EXPIRES"  (no need of EXPIRES when CACHE-CONTROL enabled)
			//header('Expires: '.gmdate(DATE_RFC1123,time()+$year));
		//To get best cacheability, send Last-Modified header and ...
			header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime(__file__)));  //i.e.  1467220550 [it's 30 june,2016]
		//reply using: status 304 (with empty body) if browser sends If-Modified-Since header.... This is cheating a bit (doesn't verify the date), but remove if you dont want to be cached forever:
			// if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {  header('HTTP/1.1 304 Not Modified');   die();	}
			header("Content-type: application/javascript;  charset=utf-8");
		?>
		// ************ these useful scripts got from: https://github.com/tazotodua/useful-javascript/   **********
		// "<script>"  dont remove this line,,, because, now JAVSCRIPT highlighting started in text-editor

		<?php
		$random_name = "button_".rand(1,999999999).rand(1,999999999);
		?>
		"use strict";

		(function ()
		{
			// Name the plugin anything we want
			tinymce.create( 'tinymce.plugins.<?php echo $random_name;?>',
			{
				init: function (ed, url)
				{

				<?php foreach ($this->tinymce_buttons as $each_button ) { ?>
					// The button name should be the same as used in PHP function of WP
					ed.addButton( '<?php echo $each_button["button_name"];?>',
					{
						// Title of button
						title: '<?php echo $each_button["shortcode"];?>',
						// icon url of button
						image: '<?php echo $each_button["icon"];?>', //url +
						// Onclick action onto button
						onclick: function ()
						{
							// Create shortcode string, with default values
							var val = '<?php echo $this->shortcode_example($each_button["shortcode"], $each_button["default_atts"]);?>';
							// Insert shortcode in text-editor
							ed.execCommand( 'mceInsertContent', false, val );
						}
					});
				<?php } ?>


				},
				createControl: function (n, cm) {
					return null;
				}

			});

			// first parameter	- the same name as defined in PHP function of WP
			// second parameter	- the module name (as defined a bit above)
			tinymce.PluginManager.add( '<?php echo "button_handle_" . $this->slug;?>', tinymce.plugins.<?php echo $random_name;?> );

		})();
		//</script>
		<?php
		exit;
	}
	// =========================================================== //



	public function add_extra_options_page()
	{
		add_action('admin_menu',  function () {
			//add_menu_page('sample_page', 'sample_page', 'administrator','smpl_pggg', 'fnc34252');
			add_submenu_page('options-general.php' , 'MY_EXTRA Options', 'MY_EXTRA Options', 'edit_others_posts', 'mysubpage-slug8452', 'fnc345732523' );
		} );
		function fnc345732523() {
			$all_opts=$this->get_my_site_option();
			
			//if updated
			if (isset($_POST['securit_noncee23'])){    
				$this->NonceCheckk('securit_noncee23','myopts_exs2');
				
				$all_opts=$_POST['my']; 
				$this->update_my_site_options($all_opts);
			}
				
			?>
			<form action="" method="POST" class="additionals">
				<?php 	//wp_editor( htmlspecialchars_decode(get_option('nwsMTNG_notes_'.$laang)), 'mtng_notes_styl_ID'. $laang, $settings = array('textarea_name'=>'nwsMTNG_notes_'. $laang,  'editor_class' => "editoor_nws_note")); ?>
				<h2>Extra options   (0= OFF,  1= ON)</h2>
				<?php
				input_fields_from_array($all_opts,'my');
				?>
				<div class="my_save_divv" style="text-align:center; position:fixed; bottom:20px; left:40%; padding:10px; background-color: red;"><input type="submit" class="my_SUBMITT" value="SAVE" /></div> <?php echo NonceFieldd('securit_noncee23','myopts_exs2'); ?> 
			</form>
			<?php 
		}
	}



	//add_action( 'pre_get_posts', 'querymodify_2322',77); 
	public function querymodify_2322($query) { $q=$query;
		if( $q->is_main_query() && !is_admin() ) {
			if($q->is_home){
				//$q->init();			$q->set('post_type',LNG);     $q->set('category__not_in', 64);
				//var_dump($q);
				//$q->set_query_vars('category__not_in',array(64)  );
			}
		}
		return $q;
	}

	//add_action('init', 'my_custom_init');  function my_custom_init() {	add_post_type_support( 'page', 'excerpt' );}

	
	// Register Custom Post 	
	//if (!empty($GLOBALS['my_Additional_post_typesss'])) { 	add_action('init', 	'my_reg_postype32323'); 	}
	public function my_reg_postype32323() {
		foreach ($GLOBALS['my_Additional_post_typesss'] as $each) {
			$title = isset( $each['title'] ) ?  $each['title']  : $each['name'];
			// https://codex.wordpress.org/Function_Reference/register_post_type 
			register_post_type( $each['name'], array(
				'label'	  => __( $title ),	'description'  => __( $each['name'].'s'),
				'labels'	 =>  array('name' => $each['name'], 'singular_name' => $each['name'].' '.'page'),
				'supports'		=> array('title','editor', 'thumbnail', 'excerpt', 'page-attributes', 'post_tag', 'revisions','comments','post-formats'  ),
				'taxonomies'	=> array('category', 'post_tag'),  
				'public'			=> true,	'query_var'=> true,				'publicly_queryable'=>true,	'show_ui'=> true,	'show_in_menu'	=> true,
				'show_in_nav_menus'	=> true,	'show_in_admin_bar'	=> true,	'menu_position'	=> 18,
				'menu_icon'			=> '',		'can_export'		=> true,	'hierarchical' => true, 'has_archive'	=> true, 'menu_icon' => 'dashicons-editor-spellcheck', // https://developer.wordpress.org/resource/dashicons/#editor-spellcheck
				'exclude_from_search' => false,	'capability_type'=> 'page',
				'rewrite' => array('with_front'=>true,   ), 
			) );
		}
	}
	





	// increase filtering quick-menu-search results (this seems better than other a bit harder methods, like: https://goo.gl/BWMmDp )
	public function init__quicksearch() { add_action( 'pre_get_posts', [$this, 'myFilter_quicksearch'], 10, 2 );  }
	public function myFilter_quicksearch( $q ) {
		// example of $q properties: https://goo.gl/SNeDwX
		if(isset($_POST['action']) && $_POST['action']=="menu-quick-search" && isset($_POST['menu-settings-column-nonce'])){	
			// other parameters for more refinement: https://goo.gl/m2NFCr
			if( is_a($q->query_vars['walker'], 'Walker_Nav_Menu_Checklist') ){
				$q->query_vars['posts_per_page'] = property_exists($this,'search_items_amount_in_menu') ? $this->search_items_amount_in_menu :  20;
			}
		}
		return $q;
	}


	// remove URL field from comments: if($this->definedTRUE('REMOVE_URL_FROM_COMMENTS')) add_filter('comment_form_default_fields',public function ($fields) {    unset($fields['url']);    return $fields;    }); 

	/*
	if ($this->definedTRUE('editor_increase_functions')) 	{ add_action( 'admin_init', 'allow_editor_increased_access');  }
	// https://codex.wordpress.org/Roles_and_Capabilities#edit_theme_options
	public function allow_editor_increased_access(){
		$role_object = get_role( 'editor' );
		if(empty($role_object )) return;
		$role_object->add_cap( 'edit_theme_options' );
		$role_object->add_cap( 'update_core' );
		$role_object->add_cap( 'update_themes' );
		$role_object->add_cap( 'switch_themes' );
		$role_object->add_cap( 'delete_themes' );
		$role_object->add_cap( 'delete_plugins' );
		$role_object->add_cap( 'update_plugins' );
		//$role_object->add_cap( 'create_users' );  // will access ADMIN!
		//$role_object->add_cap( 'edit_users' );  // will access ADMIN!
		$role_object->add_cap( 'delete_users' );
		$role_object->add_cap( 'remove_users' );
		$role_object->add_cap( 'edit_files' );
		$role_object->add_cap( 'list_users' );
		$role_object->add_cap( 'edit_dashboard' );
		// CAREFULL !
		//$role_object->add_cap( 'manage_options' );
	}


	//add_action('admin_head', 'hide_menu'); 
	public function hide_menu() {
		if (iss_editorrr()){
			//remove_submenu_page( 'themes.php', 'themes.php' ); // hide the theme selection submenu
			//remove_submenu_page( 'themes.php', 'widgets.php' ); // hide the widgets submenu
			//remove_submenu_page( 'themes.php', 'custom-header' );
			//remove_submenu_page( 'themes.php', 'custom-background' );
		}
	}
	*/
	 


	// =====================#defaults=====================================================
	//add_filter('excerpt_more', 'barista_new_custom_exerpt2');   public function barista_new_custom_exerpt2($more) {	return ' <a class="read-more" href="'. get_permalink(get_the_ID()) . '"> (Continue Reading)</a>';}
	//add_filter('excerpt_length', 'barista_custom_excerpt_length2');   public function barista_custom_excerpt_length2() {    return 25;} 

	public function NonceCheckk($name='nonce_input_name', $action_name='blabla')  {
		return ( wp_verify_nonce($_POST[$name], $action_name)  ?  true : die("not allowed, refresh page!") );
	}
	public function NonceFieldd($name='nonce_input_name', $action_name='blabla')  { return '<input type="hidden" name="'.$name.'" value="'.wp_create_nonce($action_name).'" />';}

	public function noindex_pagesss() {
		if ( !is_404() && !is_page() && !is_single() && !is_search() && !is_archive() && !is_admin() && !is_attachment() && !is_author() && !is_category() && !is_front_page() && !is_home() && !is_preview() && !is_tag())  { echo '<meta name="robots" content="noindex, nofollow"><!-- by MLSS -->'; }
	}



	// remove category base: pastebin_com/raw/YpV0wp27


	//add_action( 'after_setup_theme', 'theme_supportss' );  
	public function theme_supportss(){
		// https://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
			//remove_theme_support( 'custom-header' ); 
		// Add support for:		menus
			add_theme_support('menus');		
		// Add support for:		titles
			add_theme_support('title-tag');   
		// Editor Styles
			add_theme_support('editor-style');
			add_editor_style();
		// Enable Thumbnails for Feature Images 
		add_theme_support( 'post-thumbnails');
			set_post_thumbnail_size( 200, 150 );
			add_image_size('my-small-thumbnail', 150, 150, true);
			add_image_size('my-medium-thumbnail', 650, 150, true);
		// Translation Ready
				//load_theme_textdomain( 'my', get_template_directory() . '/languages' );
		// Add default posts and comments RRS feeds links to the head.
			add_theme_support('automatic-feed-links');
		//Suppot HTML5 Search Form
			add_theme_support( 'html5', array( 'search-form' ) );
		
				//load_theme_textdomain( 'my', get_template_directory() . '/languages' );
			
		//Custom Header
		if ($this->definedTRUE('ENABLE_CUSTOM_HEADER')){
				$defaults = array(
				// Text color and image (empty to use none).
					'random-default' => true,
					'default-text-color'=>'FFFFFF',
					'default-image'=> '',
					'uploads'=>true,
				// Set height and width, with a maximum value for the width.
					'height'=>200, 'width'=>900, 'max-width'=>2000,
				// Support flexible height and width.
					'flex-height'=>true,		'flex-width'=>true,
				// Random image rotation off by default.
					'random-default'         => false,
					'header-text'            => true,
				// Callbacks for styling the header and the admin preview.
					//'wp-head-callback'       => 'barista_header_style', 
					//'admin-head-callback' => '',
					//'admin-preview-callback' => ''
				);
			add_theme_support( 'custom-header',  $defaults );
		}
		  
		//Custom Background
		if ($this->definedTRUE('ENABLE_CUSTOM_BACKGROUND')) {
				$defaults = array(
					'default-color'          => '#e7e7e7',
					'default-image'          => '', 
					'default-repeat' =>'',  'default-position-x'=> '',
					//'wp-head-callback'       => '_custom_background_cb',
					//'admin-head-callback'    => '',
					//'admin-preview-callback' => ''
				);
			add_theme_support( 'custom-background', $defaults );
		}

	}





	// add_filter('upload_mimes', 'custom_upload_mimes');
	public function custom_upload_mimes ( $existing_mimes=array() ) {
		// add your extension to the mimes array as below
		$existing_mimes['zip']	= 'application/zip';
		$existing_mimes['gz']	= 'application/x-gzip';
		$existing_mimes['txt'] = 'text/plain'; 
		return $existing_mimes;
	}







	//LOAD_JQUERY and etc...

	//add_action( 'wp_enqueue_scripts','xxxx1332434',44);
	//add_action( 'admin_enqueue_scripts','xxxx1332434',44);
	public function xxxx1332434(){

		//		wp_register_script( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), '1.11.0' );
		//		wp_register_script( 'jquery-core', '/wp-includes/js/jquery/jquery.js', false, '1.11.0' );

		foreach($GLOBALS['odd']['scripts'] as $name=>$value){
			$each_UPPERCASE=strtoupper(str_replace('-','_',$name));
				
			if($this->definedTRUE('LOAD_'.$each_UPPERCASE)){
				//force to load my JQUERY
				if(!$this->definedTRUE('DISABLE_MY_'.$each_UPPERCASE.'_FORCE')){ wp_deregister_script($name);	}
				$registered	= wp_script_is( $name, 'registered' );
				$enqueued	= wp_script_is( $name, 'enqueued' );
				if (!$registered)	{ 
					if(!empty($GLOBALS['odd']['scripts'][$name]['js'])) {
						wp_register_script($name, $GLOBALS['odd']['scripts'][$name]['js'], 	array(), $this->changeable_JS_CSS_version, false );	
					}
					if(!empty($GLOBALS['odd']['scripts'][$name]['css'])) {
						wp_register_style( $name, $GLOBALS['odd']['scripts'][$name]['css'],	array(), $this->changeable_JS_CSS_version, false );	
					}
				}
				if (!$enqueued)		{
					if(!empty($GLOBALS['odd']['scripts'][$name]['js'])) {
						wp_enqueue_script( $name );
					}
					if(!empty($GLOBALS['odd']['scripts'][$name]['css'])) {
						wp_enqueue_style	( $name );
					}
				}
			}
		}
	}


	
	public function register_stylescript($admin_or_wp, $type, $handle=false, $url=false, $dependant=null, $version=false, $target=false)
	{
		$this->register_stylescript_old($admin_or_wp, $type, $handle, $url, $dependant, $version, $target);
	}
	
	public function register_stylescript_new($type, $handle=false, $url=false, $dependant=null, $version=false, $target=false)
	{
		call_user_func("wp_deregister_".$type,	$handle);
		if ( ! call_user_func("wp_".$type."_is",	$handle, "registered" ) ){
			call_user_func("wp_register_".$type,	$handle, $url,  $dependant,  $version, $target );   //,'jquery-migrate'
		}
		if ( ! call_user_func("wp_".$type."_is",	$handle, "enqueued" ) ){
			call_user_func("wp_enqueue_".$type,	$handle);
		}
	}

	public function register_stylescript_old($admin_or_wp, $type, $handle=false, $url=false, $dependant=null, $version=false, $target=false)
	{
		add_action( $admin_or_wp.'_enqueue_scripts',	function() use($type, $handle, $url, $dependant, $version, $target) {
			$this->enqueue($type, $handle, $url, $dependant, $version, $target);
		}); 
	}

	public function enqueue($type, $handle=false, $url=false, $dependant=null, $version=false, $target=false)
	{
		//lets allow shorthanded start
		$localstart = 'assets';
		if( substr($url,0, strlen($localstart) ) == $localstart ) 
			$url = $this->pluginURL. $url;
		if ( ! call_user_func("wp_".$type."_is",	$handle, "registered" ) ){
			call_user_func("wp_register_".$type,	$handle, $url,  $dependant,  $version, $target );   //,'jquery-migrate'
		}
		if ( ! call_user_func("wp_".$type."_is",	$handle, "enqueued" ) ){
			call_user_func("wp_enqueue_".$type,	$handle);
		}
	}

	public function add_localscript($handle, $string){
		$is_js = stripos($string,'<script>')!==false;
		$js_or_css = $is_js ? 'script' : 'style';
		//$url = $this->pluginURL . '?
		//register_stylescript($js_or_css, $handle, $url)
	}

	//public function sjquery() {
	//	wp_deregister_script('jquery');
	//	wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.3.1.min.js', array(), null, true);
	//	wp_enqueue_script('jquery');
	//}
	//add_action('wp_enqueue_scripts', 'sjquery');



	public function get_permalink_ADOPTED($post=false){
		global $wp_rewrite;	
		//trick if used earlier than INIT 
		//$GLOBALS['wp_rewrite']=(object) array('use_trailing_slashes'=>false);
		if (empty($wp_rewrite)){
			if(is_object($post))	 {$link=$post->guid; }
			elseif(is_numeric($post)){ $post_obj=get_post($post, OBJECT); $link=get_permalink($post_obj->ID); }
			
		}
		else{
			if(is_object($post))	{ $link=get_permalink($post->ID);}
			else					{ $link=get_permalink($post); }
		}
		return  $link;
	}

	//add_filter('the_content', 'home_url_repalceerr');
	public function home_url_repalceerr($content) {
		if (defined('IS_SINGLEE') && IS_SINGLEE){ $content = str_replace('_THE_HOME_URL_', $this->homeURL, $content) ; } 
		return $content;
	}

	// add a empty div before and after content
	//add_filter('the_content', 'empty_div_add');
	public function empty_div_add($content) {
		if (defined('IS_SINGLEE') && IS_SINGLEE){ $content = '<div class="bef_cont_div"></div><div class="cont_div">'.$content.'</div><div class="aft_cont_div"></div>'; } 
		return $content;
	}


	public function titles_convertacia($inputed_title) {
		$search_fraze=get_search_query();
		//if the page seems to be "SEARCH results" page
		if ($search_fraze)	{$final= str_replace( $search_fraze, '<span class="fouund_phraze">'.$search_fraze.'</span>', $inputed_title);	}
		else				{$final= $inputed_title;}
		$final = str_replace('  ',' ',$final);
		return $final;
	}



	public function redirect_to_geo($siteSlug= "geo")
	{
		// redirect to /GEO
		$redirect_lang = 1;
		if ($redirect_lang)
			if (!is_admin())
				if( stripos($this->currentURL,"/$siteSlug/")===false && stripos($this->currentURL,'/wp-login')===false  && stripos($this->currentURL,'/wp-admin')===false   )
					$this->php_redirect( str_replace($this->domain, $this->domain."/$siteSlug/",  $this->currentURL));
	}

	//not_founded_images_redirections (when on FTP, the file is not found, then automatically, the site is loaded.. so, in this case, use our function.
	//add_action( 'wp', 'not_found_images_redirect' ,59);
	//if (!$this->definedTRUE('avoid_image_notfound')){ not_found_images_redirect(); }
	public function not_found_images_redirect() {
		if (in_array( get_url_parts($this->currentURL,'extensionn')  ,array('png','jpg','jpeg','gif','bmp','svg')))		{  
			//var_dump(ob_get_contents());exit;
			
			// ======= 1 ========  output custom image FORMAT
			OutputImageFile($this->moduleDIR.'/library/media/image-not-found.png');
			 
			// ======= 2 ========  output custom image CREATED
				// https://stackoverflow.com/a/31474885/2377343
		}
	}
	

	public function OutputImageFile($file=''){
		header("Content-type: image/png");  die(  $this->file_get_contents($file)  ); 
	}


	public function get_parent_slugs($post){
		$final_SLUGG = '';
		if (!empty($post->post_parent)){
			$parent_post= get_post($post->post_parent);
			while(!empty($parent_post)){
				$final_SLUGG =  $parent_post->post_name .'/'.$final_SLUGG; 
				if (!empty($parent_post->post_parent) ) { $parent_post = get_post( $parent_post->post_parent); } else{ break ;} 
			}
		}
		return $final_SLUGG;
	}
	
	
	// navigation page numbers
	public function wpbeginner_numeric_posts_nav( $array = array()) {	
		$wp_query= (!empty($array['wp_query']) ? $array['wp_query'] : $GLOBALS['wp_query'] );
		$show_all_pages = !empty($array['show_all_pages']);
		if( is_singular() )	return "";						// Stop if single content page
		if( $wp_query->max_num_pages <= 1 ) {return "";}	// Stop execution if there's only 1 page	
		$paged = !empty($wp_query->query['paged']) ? absint($wp_query->query['paged']) : 1;		//$pNUMB = get_query_var('paged')
		$temp_global_paged=$GLOBALS['paged'];    $GLOBALS['paged']=$paged;
		$max   = intval( $wp_query->max_num_pages );
		if ( $paged >= 1 )	{$links[] = $paged;} //	Add current page to the array 
		if ( $paged >= 3 ) 	{$links[] = $paged - 1;	$links[] = $paged - 2;} //	Add the pages around the current page to the array 
		if ( ( $paged + 2 ) <= $max ){	$links[] = $paged + 2;	$links[] = $paged + 1; }
		if($show_all_pages){  $links = range(1,  $max); }
		
		$out = "\n";
			if(!$show_all_pages) {
				if ( get_previous_posts_link() ){	//Previous Post Link
					$out .= '<li class="previous_pagee nextprev_pg">'.get_previous_posts_link('<span style="position:relative;top:-3px;">&larr;</span>').'</li>' . "\n";	
				}
			}
			if ( ! in_array( 1, $links ) ) {	//Link to first page (plus ellipses if necessary)
				$class = 1 == $paged ? ' class="active"' : '';
				$out .=  '<li'.$class.'><a href="'.esc_url( get_pagenum_link( 1 ) ).'">1</a></li>' . "\n";
				if ( ! in_array( 2, $links ) )	$out .= '<li>…</li>';
			}
			sort( $links );	foreach ( (array) $links as $link )	{ //Link to current page, plus 2 pages in either direction if necessary
				$class = $paged == $link ? ' class="active"' : '';
				$out .= '<li'.$class.'><a href="'.esc_url(get_pagenum_link($link)).'">'.$link .'</a></li>' . "\n";
			}
			if ( ! in_array( $max, $links ) ) { //Link to last page (plus ellipses if necessary)
				if ( ! in_array( $max - 1, $links ) )	
					$out .= '<li class="three_dots">…</li>' . "\n";
				$class = $paged == $max ? ' class="active"' : '';
				$out .=  '<li'.$class.'><a href="'.esc_url(get_pagenum_link($max)).'">'.$max.'</a></li>'."\n";
			}
			if(!$show_all_pages) {
				if ( get_next_posts_link() ) { //Next Post Link 
					$out .='<li class="next_pagee nextprev_pg">'.get_next_posts_link('<span style="position:relative;top:-3px;">&rarr;</span>').'</li>'."\n";
				}
			}
		$out .= "\n";
				//restore variable
				$GLOBALS['paged']=$temp_global_paged;
		if(!empty($array['use_goto'])) {
			$out .= '<div class="goto_nav"><input type="text" id="goto_nav" value="" placeholder="123" /><button onclick="goto_page();">'.( !empty($array['goto_string']) ? $array['goto_string'] : 'go to').'</button> <script> public function goto_page() { var el= document.getElementById("goto_nav"); var base_link=\''.get_pagenum_link(19999991).'\'; window.location= base_link.replace("19999991",el.value ) ; } </script> </div>'; 
		}
		return  $out;
	}
		
	// https://stackoverflow.com/questions/18401236/custom-category-tree-in-wordpress
	public function my_Categ_tree($TermName='', $termID, $separator='', $parent_shown=true ){
		$args = 'hierarchical=1&taxonomy='.$TermName.'&hide_empty=0&orderby=id&parent=';
				if ($parent_shown) {$term=get_term($termID , $TermName); $output=$separator.$term->name.'('.$term->term_id.')<br/>'; $parent_shown=false;}
		$separator .= '-';	
		$terms = get_terms($TermName, $args . $termID);
		if(count($terms)>0){
			foreach ($terms as $term) {
				//$selected = ($cat->term_id=="22") ? " selected": "";
				//$output .=  '<option value="'.$category->term_id.'" '.$selected .'>'.$separator.$category->cat_name.'</option>';
				$output .=  $separator.$term->name.'('.$term->term_id.')<br/>';
				$output .=  my_Categ_tree($TermName, $term->term_id, $separator, $parent_shown);
			}
		}
		return $output;
	}
		//foreach (get_terms($allTermSlugs, array('hide_empty'=>0, 'orderby'=>'id', 'parent'=>0)  ) as $category) {
			//	echo my_Categ_tree($category->taxonomy,$category->term_id);
			//}


	//    add_filter( 'wp_mail_from',			function( $email ) { return 'contact@'.$_SERVER['HTTP_HOST']; } );
	//    add_filter( 'wp_mail_from_name',	function( $name ) { return 'WordPress Email System'; } );
	//    add_filter( 'wp_mail_content_type', function($cotnent_type=false){ return "text/html"; } ) ;
	// $headers = array('Content-Type: text/html; charset=UTF-8')


	public function random_val($name){
		$randoms = get_site_option('randoms_for_main_site', array());
		if(empty($randoms) || empty($randoms[$name])){
			$randoms[$name]= random_stringg(16);
			update_site_option('randoms_for_main_site', $randoms);
		}
		return $randoms[$name];
	}



	// for SEARCH RESULTS, lets make the "search query" in CSS CLASS
	//add_filter('the_content',	'searchWord_blacking',14);
	//add_filter('the_excerpt',	'searchWord_blacking',14);
	//add_filter('the_title',		'searchWord_blacking',14);  // in this case, $content will mean title
	public function searchWord_blacking($content) {
		if(empty($GLOBALS['wp_query'])) return $content;
		if ($GLOBALS['wp_query']->is_main_query() && defined('IS_SEARCHH') && IS_SEARCHH){ 
			$search_query=get_search_query();
			//avoid replacing in "title" attributes 
			//$content = preg_replace('((?!style)|(?!class))=[\'"](.*?)[\'"]/si','',$content);
			//$content = preg_replace('/(?<!(style|class|href))\=[\'"](.*?)[\'"]/si','',$content);
			//$content = preg_replace('/\<(.*?)'..'(.*?)\>/si/si','$1 $2',$content);
			//$content=str_replace($search_query,'<span class="searchquery_blacken">'.$search_query.'</span>',$content);
			$content=preg_replace('/(\<.*?\>)(.*?)'.$search_query.'(.*?)(\<.*?\>)/si', '$1$2<span class="searchquery_blacken">'.$search_query.'</span>$3$4',$content);
		}
		return $content;
	}


	// removing WP version meta-tags ( https://stackoverflow.com/q/16335347/2377343 ) 
	// $this->remove_version_hints();
	public function remove_version_hints()
	{	
		// Hide "?vers=XXXXX" strings from scripts and styles  ( https://premium.wpmudev.org/blog/how-to-hide-your-wordpress-version-number/ )
		add_action( 'after_setup_theme', function(){
			remove_action('wp_head', 'wp_generator');	//remove inbuilt version
			remove_action('wp_head', 'woo_version');	//remove Woo-version (in case someone uses that)
		} ); 
		
		foreach(['style_loader_src','script_loader_src'] as $e) 
		add_filter( $e, function ( $src ) { $vers= get_bloginfo( 'version' );	return ( strpos( $src, 'ver=' . $vers ) ) ? str_replace( 'ver='. $vers, 'ver='. substr($vers, -6) , $src ) : $src;	}, 9999 ); 
		
		foreach(['script_loader_src','style_loader_src'] as $e) 
		add_filter($e, function ( $src ) {global $wp_version;
			parse_str(parse_url($src, PHP_URL_QUERY), $query);
			if ( !empty($query['ver']) && ($query['ver'] === $wp_version || $query['ver'] == $wp_version) ) { $src = remove_query_arg('ver', $src); }   return $src;
		} );
		
		// hide VERSION GENERATOR
		foreach(['the_generator','get_the_generator_html','get_the_generator_xhtml','get_the_generator_atom','get_the_generator_rss2','get_the_generator_comment','get_the_generator_export','wf_disable_generator_tags'] as $e) 
		add_filter($e,	(function () {return '';}) ); 
	}
	
	/*
	//add_action( 'init', 'my_menu_registerss3' ); 
	public function my_menu_registerss3() {
		$menu_name='aamy-vustom-menu';
		$menu_exists = wp_get_nav_menu_object('aamy-vustom-menu');
		if( !$menu_exists){
			$menu_id = wp_create_nav_menu($menu_name);
			// Set up default menu items
			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' =>  __('Home'),
				'menu-item-classes' => 'home',
				'menu-item-url' => home_url( '/' ), 
				'menu-item-status' => 'publish'));

			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' =>  __('Custom Page'),
				'menu-item-url' => home_url( '/custom/' ), 
				'menu-item-status' => 'publish'));
		}
		// maybe it should be in: after_theme_setup   
		register_nav_menus(array(
			'menu_left_sidebarr'   =>'zzleft Main Menu',
			'menu_right_sidebarr'   =>'zzright Main Menu'
		));
	}


	define('my_sample_array_widgets', 'my_top_widgett,');
	add_action('admin_init', function(){ 
		if($GLOBALS['pagenow']== 'widgets.php'){
			if(isset($_GET['widget_add'])){
				
				update_option('optname_widgets', explode(',',  filter_var($_GET['widgets_list'], FILTER_SANITIZE_STRING ) ) ) ;
			}
	
			add_action('admin_notices', function() {
			?>
			<div><form action="" method="POST">enter widgets list (comma separated): <input type="text" value="<?php echo get_option('optname_widgets',my_sample_array_widgets);?>" name="widget_add" /> <input type="submit" /></form></div>
			<?php 
			});
		}
	});
			
	add_action( 'widgets_init',	 public function () {	
	
		$optval=get_option('optname_widgets', my_sample_array_widgets);
		$additional_array = !empty($GLOBALS['MyWidgetss']) ? $GLOBALS['MyWidgetss'] : array();
		$widgets= array_merge( explode(',',$optval),  $additional_array );
		if (!empty($widgets) ) {
			foreach ($widgets as $value){
				register_sidebar( array('name' => $value ,'id' => strtolower($value),	'before_widget'=>'<div class="sideb_clas '.$value.'">','after_widget'=>'</div>','before_title'=>'<h2 class="sideb_around">','after_title'=>'</h2>') );
			}
		}
	});
	*/
	
	public function delete_transients_by_prefix($myPrefix, $table_name, $column_name, $prefix=false){
		global $wpdb;
		$myPrefix 		= sanitize_key($myPrefix);
		$sql = "delete from $table_name where $column_name like '%_transient_$myPrefix%' or $column_name like '%_transient_timeout_$myPrefix%'";
		return $wpdb->query($sql);
	}

	//add_filter( 'tiny_mce_before_init', 'wptrac_36636_editor_inline_style22' );
	public function wptrac_36636_editor_inline_style22( $settings ) {$settings['content_style'] = (!empty($settings['content_style']) ? $settings['content_style'] : '') . (!empty($GLOBALS['my_tinymce_styles']) ? addslashes($GLOBALS['my_tinymce_styles']) : ''); return $settings;}

	
	
	public function input_fields_from_array($value, $keyname='', $replace_spaces=false){	//$keyname= (strpos($keyname,'[') === false) ? '['.$keyname.']' : $keyname;
		echo '<div class="array_fields1"><style>.array_fields1 textarea{max-height:200px!important;  border-radius: 5px; width:100%; color:#53ae14; border: 2px solid black; margin:0 0 0 0px; height:50px; }  .def_textareaa{height:70px;} .high_textarea{height:130px;} .new_block{MARGIN:0 0 0 50px; border:2px solid; border-width:0 0 0 2px;} .txtar{padding:0 0 0 25px;}  .new_block .keyname{color:rgb(248, 48, 83);} </style>';
		input_fields_from_array_RECURSIVE($value, $keyname, $replace_spaces);
		echo '</div>';
	}
	
	
	// Adding .zip extension
	public function upload_mimes_filter( $mime_types ) {
		if (!array_key_exists('zip', $mime_types)) $mime_types['zip'] = 'application/zip';  
		if (!array_key_exists('gz|gzip|zip', $mime_types)) $mime_types['gz|gzip|zip'] = 'application/x-zip'; 
		//	['gz|gzip'] => application/x-gzip
		//	[rar] => application/rar
		//	[7z] => application/x-7z-compressed
		return $mime_types;
	}

	public function unzip_url($url, $where)
	{
		$zipLoc = $where.'/'.(basename($url)).'.zip';
		wp_remote_get
		(
			$url,
			[
				'timeout'  => 300,
				'stream'   => true,
				'filename' => $zipLoc
			]
		);
		$this->unzip($zipLoc, $where);
		@unlink($zipLoc);
	}

	public function unzip($path, $where)
	{ 
		$this->mkdir_recursive($where);
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		\WP_Filesystem();
		\unzip_file($path, $where);
		usleep(300000);
	}
	
	public function disable_php_in_wpcontent()
	{
		if (last_checkpoint('uploads_htaccess', 500000))
		add_action('init', function() {
			$uploads_dir = defined('UPLOADS') ? UPLOAD : get_option('upload_path');
			$uploads_dir = !empty($uploads_dir) ?  $uploads_dir : WP_CONTENT_DIR.'/uploads';
			$file=$uploads_dir.'/.htaccess';
			if(!file_exists($file)) {
				$this->helpers->file_put_contents($file, '<Files ~ "\.php">'."\r\n".
					'Order allow,deny'."\r\n".
					'Deny from all'."\r\n".
					'</Files>'
				);
			}
		});
	}
	
	public function unzip_in_dir($dir, $rewrite=true)
	{
		$this->temp_unziped_folders = [];
		foreach( array_filter(glob($dir.'/*.zip'), 'is_file')  as $each_zip)
		{
			$uniqueTag	= md5($each_zip);
			$each_dir	= substr($each_zip, 0, -4); //trim .zip
			if (empty($each_dir)) return; // ! must have, to avoid empty directory threat

			// remove if previous unpack was partial.
			if( is_dir($each_dir) && $rewrite )
			{
				if( !array_key_exists($uniqueTag, $this->temp_unziped_folders) || $this->temp_unziped_folders[$uniqueTag]==false )
				{
					$this->rmdir_recursive($each_dir);
					usleep(500000);
					//$this->mkdir_recursive($pathh);
				}
			}
			elseif( !is_dir($each_dir) )
			{
				$this->temp_unziped_folders[$uniqueTag] = false;
				$this->unzip($each_zip, dirname($each_zip));
				$this->temp_unziped_folders[$uniqueTag] = true;
			}
		}
	}


	public function is_activation(){
		return (isset($_GET['isactivation']));
	}

	public function reload_without_query($params=array(), $js_redir=true){
		$url = remove_query_arg( array_merge($params, ['isactivation'] ) );
		if ($js_redir=="js"){ $this->js_redirect($url); }
		else { $this->php_redirect($url); }
	}

	public function if_activation_reload_with_message($message){
		if($this->is_activation()){
			echo '<script>alert(\''.$message.'\');</script>';
			$this->reload_without_query();
		}
	}

	public function add_default_uninstall(){
		if( is_admin() && !$this->is_development)
		{
			$wp_uninstall_file = $this->moduleDIR.'/uninstall.php';
			if( !file_exists($wp_uninstall_file) )
			{
				$content=
				'<'.'?php
				// If uninstall not called from WordPress, then exit
				if ( ! defined( "WP_UNINSTALL_PLUGIN" ) ) {
					exit;
				}

				$lib = dirname(__DIR__)."/'.basename(__FILE__).'";
				if(file_exists($lib)){
					//@unlink($lib);
				}';

				$this->file_put_contents($wp_uninstall_file, $content);
			}
		}
	}

	//disable emojis
	public function disable_emojicons()
	{
		add_action( 'init', function () {
		  // all actions related to emojis
		  remove_action( 'admin_print_styles', 'print_emoji_styles' );
		  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		  remove_action( 'wp_print_styles', 'print_emoji_styles' );
		  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		} );

		//to remove emojis from TinyMCE
		add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		  if ( is_array( $plugins ) ) {  return array_diff( $plugins, array( 'wpemoji' ) );} 
		  else { return array(); }
		} );
	}



	/*
	//add_action( 'admin_init', 'theme_options_init' );
	function theme_options_init(){	 
		// https://codex.wordpress.org/Function_Reference/add_settings_field#Examples
		add_settings_field( 'myprefix_setting-id', 'This is the setting title', 'myprefix_setting_callback_function', 'general', 'myprefix_settings-section-name', array( 'label_for' => 'myprefix_setting-id' ) );
		
		// https://codex.wordpress.org/Function_Reference/add_settings_section#Notes
		add_settings_section('eg_setting_section',	'Example settings in reading',		'funcXXXX',		'reading');
		function funcXXXX( $arg ) {
			// echo section intro text here
			echo '<p>id: ' . $arg['id'] . '</p>';             // id: eg_setting_section
			echo '<p>title: ' . $arg['title'] . '</p>';       // title: Example settings section in reading
			echo '<p>callback: ' . $arg['callback'] . '</p>'; // callback: eg_setting_section_callback_function
		}
		//register_setting( 'bbbbbla', 'sample_theme_options');
	}
	
	
	
	*/
	
	
	/*
	public function shortcode_handler($atts, $content=false){
		$d=debug_backtrace()[0];
		if(!empty($d['args']))
		{
			if(!empty($d['args'][2]))
			{
				$name = $d['args'][2];
				$args = $this->shortcode_atts($name, $atts);
				return call_user_func( [$this, $name], $args, $content);
			}
		}
	}
	
	////////////////////////////
	
		

	//		//register desired widget names
	//			$GLOBALS['MyWidgetss'] = array (
	//				'Myy-area-1___LEFT','Slideshow-area-4', 'area-Custom4',
	//			);
	//		// registered menus
	//			$GLOBALS['REGISTERED_MENUS']					=array('main-top', 'main-nav','footer-nav' );
	//			$GLOBALS['REGISTERED_MENU_CLASSES_ul']			=array('main-top'=>'nav navbar-nav sf-menu clearfix' );
	//			$GLOBALS['REGISTERED_MENU_CLASSES_li']			=array();
	//			$GLOBALS['REGISTERED_MENU_CLASSES_li_CHILDED']	=array('main-top'=>'sub-menu sub-menu-1' );
	//			$GLOBALS['REGISTERED_MENU_CLASSES_a']			=array('main-top'=>'nav navbar-nav sf-menu clearfix' );
	//			//$GLOBALS['REGISTERED_MENUS_ADD_EXTRA_li']		='<li class="emptyItem><a class=""></a></li>';	
					
	//			session_cache_limiter('none'); 	$length=60*60*24*14; //14 day
	//			header('Cache-control: max-age='.$length .', public');
	//			header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime(__file__))); 
	//			header("Content-type: application/javascript;  charset=utf-8");			
	//		add_action( 'widgets_init', 'myyy_widgets_ingit' );	function myyy_widgets_ingit() {
	//			register_sidebar( array('name' => 'MyHorizontalmenuuInHeader','id' => 'my-sidebar-id',
	//			'before_widget'=>'<div class="myTopSlidebarr1">','after_widget'=>'</div>','before_title'=>'<h2 class="roundedd">','after_title'=>'</h2>',) );
	//		}	
	// -----------> 	dynamic_sidebar('my-sidebar-id');

		
	//	//included in header.php
	//	function output_HorizontalMenuu(){
	//		dynamic_sidebar( 'MyHorizontalmenuuInHeader' ); 
	//		$menuu=wp_nav_menu( array('theme_location'=>'',   'menu'=> 'main-horizontal',
	//								'container'       => 'div',			'container_class' => 'horiz_id',    'container_id'=> 'my_horiz_id',
	//								'menu_class'      => 'menu',		'menu_id'         => '',
	//								'echo'            => 0,				'fallback_cb'     => 'wp_page_menu',
	//									'before'=>'', 'after'=>'', 'link_before'=>'', 'link_after'=>'',
	//									'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
	//								'depth'           => 0,	'walker'          => ''
	//		));
	//		//echo $menuu;
	//	}	



	*/


	public function Is_Home_lang($lang=false){
		if ((defined('isLangHomeURI__MLSS') && isLangHomeURI__MLSS) && (is_multisite() && IS_HOMEE && (!$lang || $lang==LNG ))){
			return true;
		}
		return false;
	}	

	//Advanced custom fields alternative
	//add_action('init', 'acf_getfield_detect', 1);
	public function acf_getfield_detect(){
		if (!function_exists('get_field')){
			function get_field(){
				return 'Advanced Custom Fields plugin is not installed';
			}
		}
	}



	// ================ flash rules ================= // 
	public function flush_rules_double(){ add_action('wp', [$this, 'MyFlush__rewrite'] ); }
	public function MyFlush__rewrite($RedirectFlushToo=false){	
		$GLOBALS['wp_rewrite']->flush_rules(); 
		flush_rewrite_rules();
		//DUE TO WORDPRESS BUG ( https://core.trac.wordpress.org/ticket/32023 ) , i use this: (//USE ECHO ONLY! because code maybe executed before other PHP functions.. so, we shouldnt stop&redirect, but  we should redirect from already executed PHP output )
		if($RedirectFlushToo) {echo '<form name="mlss_frForm" method="POST" action="" style="display:none;"> <input type="text" name="mlss_FRRULES_AGAIN" value="ok" /> <input type="submit"> </form> <script type="text/javascript"> document.forms["mlss_frForm"].submit(); </script>';}
	}

 
	public function flush_rules($redirect=false){
		flush_rewrite_rules();
		if($redirect) {
			if ($redirect=="js"){ $this->js_redirect(); }   else { $this->php_redirect(); }
		}
	}
	
	
	// ==================== shortcodes =======================
	public function shortcode_atts($shortcode, $predefined_atts, $passed_atts){
		$new_arr=[]; 
		foreach($predefined_atts as $x){
			$new_arr[ $x[0] ] =  $this->stringToValue($x[1]) ;
		}
		if (!empty($passed_atts)) {
			$filtered_atts=[];
			foreach($passed_atts as $key=>$value){
				$filtered_atts[$key] =  $this->stringToValue($value) ;
			}
			$new_arr = array_merge($new_arr, $filtered_atts);
		}
		$new_arr = $this->sanitize_shortcode_empty_defaults_pre($new_arr);
		$new_atts = shortcode_atts($new_arr, [] );
		return $new_atts;
	}
	
	public function sanitize_shortcode_empty_defaults_pre($atts){
		$ar= ["...","___", 0];
		foreach($ar as $e) { if (array_key_exists($e, $atts)) unset($atts[$e]); }
		return $atts;
	}
	
	public function sanitize_shortcode_empty_defaults($attsArray){
		$new_arr = [];
		foreach($attsArray as $eachAttArr)
		{ 
			if ( in_array($eachAttArr[0], ["...","___", 0] ) ) continue;
			$new_arr[] = $eachAttArr;
		}
		return $new_arr;
	}
	public function shortcode_alternative_message($name, $params_name=false)
	{
		?>
		<div class="alertnative_to_shortcodes">
			<h2><?php _e('(Alternatives to shortcode)'); ?></h2>
			<?php _e('Note, you can always use programatical approach using:'); ?> 
			<br/> <code>&lt;?php echo do_shortcode('[.....]'); ?&gt;</code>
			<br/> or 
			<br/> <code>&lt;?php if (function_exists('<?php echo $name;?>'))		{ echo <?php echo $name;?>(["arg1"=>"value1", ...]); } ?&gt;</code>
		</div>
		<?php
	}
	
	public function shortcode_example_string($array, $strip_tags=false, $htmlentities=false, $ended=false){
		$out = '<code>';
		$out .= '['. $array['name'].'<span class="shortcode_atts">';  $atts = $this->sanitize_shortcode_empty_defaults($array['atts']);  foreach( $atts  as $key=>$value){ $out .= " ".$value[0].'="'. htmlentities($this->truefalse_to_string($value[1])).'"';} $out .='</span>]'; 
		$out = ( $strip_tags	? strip_tags($out) : $out);
		$out = ( $htmlentities	? htmlentities($out) : $out);		
		if( $ended ) 
			$out .= "...[/".$array['name']."]";
		$out .= '</code>';
		return $out;
	}

	public function shortcode_example($shortcode, $array, $ended=false){
		$out="[$shortcode ";   foreach($array as $key=>$value){   $out .= $key.'="'.$this->valueToString($value) .'" ';  }   $out = trim($out). "]";
		if( $ended ) 
			$out .= "...[/$shortcode]";
		return $out;
	}
	
	public function shortcodes_table($name, $array)
	{ 
		/*======= example ========
		
		$this->shortcodes_table( "breadcrumbs", [
			[ 'id', 				'',			__('Post ID (you can ignore that parameter if you want to get for current post)', 'breadcrumbs-shortcode') ],
			[ 'delimiter',			'hello', 	__('Your desired delimiter', 'breadcrumbs-shortcode') ],
		] );
		*/
	?>
	<div class="shortcodes_block">
		<h3><?php echo $array['description'];?></h3>
		<table class="form-table shortcodes">
		<tr>
			<td><?php _e('Example:');?></td>
			<td>
				<?php echo $this->shortcode_example_string($array, false,false, array_key_exists('ended', $array) );?>
			</td>
		</tr>
		<tr>
			<td><?php _e('Parameters:');?></td>
			<td>
				<table>
				<tr class="shortcode_tr_descr">
					<td><?php _e('name');?></td><td><?php _e('default value');?></td><td><?php _e('description');?></td>
				<tr>
				<?php 
				foreach($array['atts'] as $key=>$value)
				{ ?>
				<tr>
					<td><code><?php echo htmlentities($value[0]);?></code></td><td><code><?php echo htmlentities($this->truefalse_to_string($value[1]));?></code></td><td><?php echo $value[2];?></td>
				</tr>
				<?php 
				}
				?>
				</table>
			</td>
		</tr>
		</table>
	</div>
		<?php
	}
	
	public function extendShortcodes(){
		add_shortcode('image', function ($atts){ 	$GLOBALS['CategImgggg'] = $atts['url'];
			return '<div class="ImgShortcodeHolder"><img src="'.$atts['url'].'" alt="'.(!empty($atts['title']) ? $atts['title'] : basename($atts['url']) ).'" class="ShortcImageee" /></div>';
		}); 
		add_shortcode('link', function ($atts){
			return '<a href="'.basename($atts['url']).'" class="ShortcLinkk" target="_blank" />'.(!empty($atts['title']) ? $atts['title'] : basename($atts['url']) ).'</a>';
		} ); 

		add_shortcode('iframe', function ($atts){ 
			return '<div class="IframeHolder ifr_'.(!empty($atts['class']) ? $atts['class'] : 'defclass' ).'"><iframe src="'.$atts['url'].'"></iframe></div>';
		} ); 
		add_shortcode('@', function ($atts){ 
			return '&#64;';
		} );
		
		add_shortcode('script', function ($atts, $content=false){
			$cont= urldecode(  $content ? $content : $atts['content'] ); 
			return '<span class="cont_script '.(strpos($cont,'<iframe ')!== false  ?  'contains_frame':'') .'">'.$cont.'</span>';
		} ); 

		add_shortcode('list_subpages', function ($atts){ $out = ''; 
			if (IS_SINGULARR){
				$id= $GLOBALS['post']->ID;
				$args = array(
					'authors'=>'',  'child_of'=>$id,   'date_format'=>get_option('date_format'), 'depth'=> 0, 'echo'=>0,'exclude'=>'','include'=>'',
					'link_after'=>'',   'link_before'=>'',  'post_type'=>'page',  'post_status'=>'publish',  'show_date'=>'',  
					'sort_column'=> 'post_date', //'menu_order, post_title',
					'sort_order'=> '',  'title_li'=> __(''),   //'walker'       => new Walker_Page
				);
				$out = wp_list_pages( $args );
			}
			return '<div class="my_subpagelistt">'.$out.'</div>';
		} ); 

		add_shortcode('video', function ($atts){ 
			$url	= $atts['url'];
			$player = !empty($atts['player']) ? $atts['player'] : 1;
			
			if ($player==1) { $out = 
				'<link href="https://vjs.zencdn.net/5.4.4/video-js.css" rel="stylesheet"><style type="text/css">body .video-js .vjs-tech {position:relative;} body #my-video{width: 80%; margin: 0 0 0 10%;} body .video-js .vjs-big-play-button{left: 45%; top:45%;}</style>
				<script src="https://vjs.zencdn.net/ie8/1.1.1/videojs-ie8.min.js"></script>
				<video id="my-video" class="video-js" controls preload="auto" width="640" height="264"
				poster="" data-setup="{}">
				<source src="'.$url.'" type="video/mp4">
				<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
				</video> <script src="https://vjs.zencdn.net/5.4.4/video.js"></script>';
			}
			elseif ($player==2) { $out = '<video width="'. (!empty($atts['width']) ? $atts['width'] : 480) . '" height="'. (!empty($atts['width']) ? $atts['width'] : 320) . '" controls="controls" preload="auto" poster="#"> <source src="'.$url.'" type="video/mp4" /> </video>';}
			else {$out='';}
			
			return '<div class="VidShortcodeHolder">'.$out.'</div>';
		} ); 
		
		
		// i.e. [list type="categories"	id="32" depth=0 exclude="4,28"] 
		// i.e. [list type="pages"		id="32" depth=0 exclude="4,28"]    (or id="this")
		// i.e. [list type="menu" id="32"]
		add_shortcode( 'list',  function ($atts){
			$TYPEE	= !empty($atts['type'])	? $atts['type']	: '';  if(empty($TYPEE)) { return 'error2229.  please, set "type" parameter' ;  }
			$args	= $atts;

			if ( 'pages' == $TYPEE){
				// https://codex.wordpress.org/Function_Reference/wp_list_pages   //authors,child_of,date_format,depth,echo,exclude,include,link_after,link_before,post_type,post_status,show_date,sort_column,sort_order,title_li,
						if (empty($args['sort_column'])){$args['sort_column']= 'post_date';}
						//when ESSENTIAL parameters are not set
						if (empty($args['child_of']))	{ return 'error494__set child_of parameter for listed ' .$TYPEE;}
							elseif ($args['child_of']=='this') { $args['child_of']= $GLOBALS['post']->ID;}
						if (empty($args['depth']) )		{ $args['depth']= 1;}
						if (empty($args['echo']) )		{ $args['echo']	= 0;}
						if (empty($args['title_li']) )	{ $args['title_li']	= "";}
						if (empty($args['post_type']))	{$args['post_type']=$GLOBALS['post']->post_type;} 
				$X= wp_list_pages($args);
			}
			
			elseif ( 'categories' == $TYPEE){
				// https://codex.wordpress.org/Function_Reference/wp_list_categories //show_option_all,orderby,order,style,show_count,hide_empty,use_desc_for_title,child_of,feed,feed_type,feed_image,exclude,exclude_tree,include,hierarchical,show_option_none,number,echo,current_category,pad_counts,taxonomy
				
						//when ESSENTIAL parameters are not set
						if (empty($args['child_of']))		{ return 'error494__set child_of parameter for listed ' .$TYPEE;}
						if (empty($args['depth']) )			{ $args['depth']= 0;}
						if (empty($args['echo']) )			{ $args['echo']	= 0;}
						if (empty($args['hide_empty']) )	{ $args['hide_empty']= 0;}
						if (empty($args['title_li']) )		{ $args['title_li']	= "";}
						
					//this doesnt work when used inside LOOP
						//$X =  get_categories('echo=1&child_of=30') );
				$X = wp_list_categories($args);
			}
			elseif ( 'posts' == $TYPEE){
				// https://codex.wordpress.org/Function_Reference/wp_list_categories //show_option_all,orderby,order,style,show_count,hide_empty,use_desc_for_title,child_of,feed,feed_type,feed_image,exclude,exclude_tree,include,hierarchical,show_option_none,number,echo,current_category,pad_counts,taxonomy
				
						//when ESSENTIAL parameters are not set
						if (empty($args['child_of']))		{ return 'error494__set child_of parameter for listed ' .$TYPEE;}
						if (empty($args['depth']) )			{ $args['depth']= 0;}
						if (empty($args['echo']) )			{ $args['echo']	= 0;}
						if (empty($args['posts_per_page']) ){ $args['posts_per_page']	= -1;}
						if (empty($args['hide_empty']) )	{ $args['hide_empty']	= 0;}
						if (empty($args['post_type']) )		{ $args['post_type']	= get_post_types();}
						if (empty($args['category']) )		{ $args['category']	= $args['child_of'];}
						
				$out = '';
				$array =  get_posts($args); 
				foreach ($array as $key=> $value) {
					$out .= '<li class="manual_posts"><a href="'.get_permalink($value->ID).'">'.$value->post_title.'</a></li>';
				}

					//this doesnt work when used inside LOOP
						//$X =  get_categories('echo=1&child_of=30') );
						//$X = wp_list_categories($args);
				$X =$out;
			}
			elseif ( 'menu' == $TYPEE){
				// https://codex.wordpress.org/Function_Reference/wp_nav_menu  //theme_location,menu,container,container_class,container_id,menu_class,menu_id,echo,fallback_cb,before,before,after,link_before,link_after,items_wrap,depth,
					
						//when ESSENTIAL parameters are not set
						if (empty($args['menu']))	{ return 'error494__set "menu" parameter for listed ' .$TYPEE;}
				$X= wp_nav_menu($args);
					//https://codex.wordpress.org/Function_Reference/wp_nav_menu
					//https://codex.wordpress.org/Function_Reference/wp_get_nav_menu_items
					$sample_arr= array(
						'theme_location'  => '',
						'menu'            => '_main_menu',
						'container'       => 'div',			'container_class' => 'sideMyBox',			'container_id'    => 'my_SideTreeee',
						'menu_class'      => 'menu',		'menu_id'         => '',
						'echo'            => 0,				'fallback_cb'     => 'wp_page_menu',
							'before'          => '',		'after'           => '',
							'link_before'     => '',		'link_after'      => '',
							'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
						'depth'           => 0,
						'walker'          => ''
					);
			}
			return '<div class="listed_shortcode listed_'.$TYPEE.'"><ul>'.$X.'</ul></div>';
		} );

	}
	// ==================== END | Shortcodes =======================
	
	
	
	
	
	
	
	
	public function init__cookieexpiration(){   add_filter('auth_cookie_expiration', [$this,'my_auth_cookie_expiration'], 99, 3);   }
	public function my_auth_cookie_expiration($seconds, $user_id, $remember){
		$expiration = $remember ? $this->auth_expiration_hours*60*60 : 2*24*60*60;

		// https://en.wikipedia.org/wiki/Year_2038_problem
		if ( PHP_INT_MAX - time() < $expiration ) {
			//Fix to a little bit earlier!
			$expiration =  PHP_INT_MAX - time() - 5;
		}

		return $expiration;
	}


										
	//add_action('wp_footer',  'Image_correction_javascript',1);
	public function Image_correction_javascript(){  
		if (!empty($GLOBALS["Javascript_Image_correction_MyClassnames"])){ ?>
			<script type="text/javascript"> 
			<?php $i=0; foreach ($GLOBALS["Javascript_Image_correction_MyClassnames"] as $each){ $i++;  ?>
			
			// ------------   EXECUTE Proportion functions --------//
				function fnc44488829_<?php echo $i;?>(){
					//Balanced_Image_proportions("<?php echo $each["img_classname"];?>",<?php echo $each["desired_widthh"];?>, <?php echo $each["desired_heightt"];?>, "<?php echo (!empty($each['parenttClass']) ? $each['parenttClass'] : '' ) ;?>");
				}
				
			// execute now 
				fnc44488829_<?php echo $i;?>();
			// execute aftert Page load too, because of previous possible problems... ( // https://stackoverflow.com/a/3144510/2377343 )
				function MyInitt() {
					if (arguments.callee.done) return;  arguments.callee.done = true;  if (_timer) clearInterval(_timer);
					// do stuff !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
					fnc44488829_<?php echo $i;?>();
				};

				//firefox/opera
			//		if (document.addEventListener) { document.addEventListener("DOMContentLoaded", MyInitt, false);}
				//Internet Explorer: ..
				//safari 
			//		if (/WebKit/i.test(navigator.userAgent)) { var _timer = setInterval(function() {    if (/loaded|complete/.test(document.readyState)) {   MyInitt();  } }, 10);}
				/* other browsers */
			//		window.onload = MyInitt;
			<?php } ?>
			</script>
		<?php 
		}
	}	
	// Use shortcodes in text widgets. //i.e. [my_shortcodeee]
	//add_filter( 'widget_text', 'do_shortcode' );

	
	//breadcrumbs: pastebin_com/CzNyaEKE
	
	
	//add_action ( 'edit_category_form_fields', 'addTitleFieldToCat');
	public function addTitleFieldToCat(){
		$cat_title = get_term_meta( (int) $_POST['tag_ID'], '_pagetitle', true);
		?> 
		<tr class="form-field">
			<th scope="row" valign="top"><label for="cat_page_title"><?php _e('Category Page Title'); ?></label></th>
			<td>
			<input type="text" name="cat_title" id="cat_title" value="<?php echo $cat_title ?>"><br />
				<span class="description"><?php _e('Title for the Category '); ?></span>
			</td>
		</tr>
		<?php

	}
	//add_action ( 'edited_category', 'saveCategoryFields');
	public function saveCategoryFields() {
		if ( isset( $_POST['cat_title'] ) ) {
			update_term_meta( (int) $_POST['tag_ID'], '_pagetitle', sanitize_text_field($_POST['cat_title']) );
		}
	}


	public function get_template_filename($post_id=false){
		if(is_page()){
			$name=	get_post_meta( $post_id ?: $GLOBALS['post']->ID, '_wp_page_template', true);   // page-templates/my_homepage_1.php
			return basename($name);
		}
		return false;
	}




	//==================DISABLE GEORGIAN + Russian SLUGS for POSTS================
	public function disable_geo_rus_slugs()
	{
		add_action( 'wp_ajax_sample-permalink', function ($data) {
			// check that we're dealing with a product, and editing the slug
			$post_id = isset($_POST['post_id']) ? (int) ($_POST['post_id']) : 0;
			$new_title = isset($_POST['new_title'])? sanitize_text_field($_POST['new_title']) : null;
			$post_name = isset($_POST['new_slug'])? sanitize_text_field($_POST['new_slug']) : $new_title;
			//on first fire, there is not set the "new_slug"
			$_POST['new_slug'] = ISSET($_POST['new_slug']) ? sanitize_text_field($_POST['new_slug']) : $this->slug_modify($post_name); 
		} ,1);
		 
		//disable slug beforehand Post Update  action (also, in navigation menus and etc...)
		add_filter('name_save_pre', function ($post_name) { 
			if (!empty($_POST['post_ID']) || !empty($_POST['post_name']) || !empty($_POST['post_title']) ){
				// check that we're dealing with a product, and editing the slug
				$post_id = !empty($_POST['post_ID']) ? (int) ($_POST['post_ID']) : 0;
				$new_slug = !empty($_POST['post_name']) ? sanitize_text_field($_POST['post_name']) :  sanitize_text_field($_POST['post_title']);
				//if got from new post
				if ($post_id && !empty($_POST['_wp_http_referer']) ) {	if (stripos($_POST['_wp_http_referer'],'wp-admin/post-new.php')!==false) { $post_name = $this->slug_modify($new_slug); $_POST['post_name']=$post_name;} } 
			}
			return $post_name;
		});
		 
		
		//disable slug on any update
		add_filter('wp_insert_post_data', function($dataaaaaa) {	
			if (!empty($_POST['_wp_http_referer'])) {
				if (stripos($_POST['_wp_http_referer'],'wp-admin/post-new.php')!==false) {
					$dataaaaaa['post_name']=$this->slug_modify(     (!empty($_POST['post_name']) ? sanitize_text_field($_POST['post_name']) : $dataaaaaa['post_title'])      );
				} 
			}
			return $dataaaaaa; 
		}, 3); 
	}
	
	public function slug_modify($slg) {return $this->myUTF8truncate(sanitize_title($this->GEO_to_ENG__LowerCased($this->Rus_To_Eng__LowerCased(urldecode($slg)))),  5);}
	//=============================================================================

	//lets load after init of LANGUAGE phrazes
	public function mailsent_page($to,$subject,$message,$from, $title=false){
		echo '<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>'.(isset($title) ? $title : 'Mail Sending'). '('.$_SERVER['http_host'].')'.'</title></head><body>';      
		echo send_maill($to,$subject,$message,$from);
		echo '</body></html>';
		exit;
	}	

	//execute explicitly when testing
	public function save_post_debug(){
		add_action('save_post',  function () { var_dump($_POST); exit; }, 99, 11); 
	}
	

	//if ($this->definedTRUE('REMOVE_CANONICAL_FROM_WP_HEAD')) { add_action('wp','remove_relative_links'); }
	public function remove_relative_links(){
		// Remove original REL=CANONICAL, and CREATE NEW 
		remove_action('wp_head', 'rel_canonical'); 	add_action('wp_head', function(){ echo '<link rel="canonical" href="'. $this->currentURL.'" />'; } );
		//
		remove_action('wp_head', 'start_post_rel_link', 10, 0 );
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	}


	public function create_table_my($table_name='tablename', $array){
		global $wpdb;
	   //1 (for phrazes)
		$sql  ="CREATE TABLE IF NOT EXISTS `$table_name` ( ";
		$sql .="`ID` mediumint(11) NOT NULL AUTO_INCREMENT,";
		foreach( $array as $key=>$val){
		   $i= isset($i) ? $i + 1 : 0;
		   $sql .= $val . ' NOT NULL,';
		}
		$sql .=
			"PRIMARY KEY (`ID`),
			UNIQUE KEY ID (ID)
			) ".$wpdb->get_charset_collate()." AUTO_INCREMENT=1;";
			// DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;"
			// text text NOT NULL, name tinytext NOT NULL, time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,  id mediumint(9) NOT NULL AUTO_INCREMENT,
		$x= $wpdb->query($sql);
		//require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		//dbDelta( $sql );
		return $x;
	}


	public function debug_actions(){	add_action( 'wp_footer', function (){ var_dump( $GLOBALS['wp_filter']); } );   }

	public function calledScript()	{ return $_SERVER["SCRIPT_FILENAME"];}
	public function is_subscriber()	{ return (function_exists('current_user_can') || require_once(ABSPATH.'wp-includes/pluggable.php')) ? current_user_can('read') : false;}
	public function is_contributor(){ return (function_exists('current_user_can') || require_once(ABSPATH.'wp-includes/pluggable.php')) ? current_user_can('edit_posts') : false;}
	public function is_author()		{ return (function_exists('current_user_can') || require_once(ABSPATH.'wp-includes/pluggable.php')) ? current_user_can('upload_files') : false;}
	public function is_editor()		{ return (function_exists('current_user_can') || require_once(ABSPATH.'wp-includes/pluggable.php')) ? current_user_can('edit_others_posts') : false; }
	public function is_administrator(){return (function_exists('current_user_can') || require_once(ABSPATH.'wp-includes/pluggable.php')) ? current_user_can('install_plugins') : false; }
	
	public function die_if_not_subscriberr(){	if(!is_subscriberr())	{die('not subscriberr  (error_852) '.$this->calledScript());}	}
	public function die_if_not_contributor(){	if(!is_contributorr())	{die('not contributorr  (error_853) '.$this->calledScript());} }
	public function die_if_not_author()	 {	if(!is_authorr())		{die('not authorr  (error_854) '.$this->calledScript());}	}
	public function die_if_not_editor()	 {	if(!is_editorr())		{die('not editorr  (error_855) '.$this->calledScript());}	}
	public function die_if_not_admin()		 {	if(!is_administrator())		{die('not adminn  (error_856) '	.$this->calledScript());} }

	public function get_user_roleX( $user_id = 0 ) {
		$user = ( $user_id ) ? get_userdata( $user_id ) : wp_get_current_user();
		return current( $user->roles );
	}

	public function post_is_in_descendant_category($cat, $postttId) {
		$descendants = array_merge( array($cat,''),     get_term_children((int) $cat, 'category')   ); 
		return (in_category($descendants, $postttId))  ? true : false;
	}

	//if post is ansector of category
	public function post_or_cat_is_in_ansector($upper_category_id){
		$truee_fals =false;	global $post;
		//for categories
		if (is_archive())	{ $cur_cat_id = get_query_var('cat');
			if ($cur_cat_id == $upper_category_id || cat_is_ancestor_of($upper_category_id, $cur_cat_id)) {return true;} 
		}
		else	{
			if (in_category( $upper_category_id, $post->ID )) { return true;} 
		}
		//$curr_post = get_post($post->ID); $truee_fals=cat_is_ancestor_of($upper_category_id, $curr_post->$post_category) ? true : $truee_fals;
		//is_category(4)		
	}


	//add_action( 'comment_form_after_fields', 'wporg_more_comments' );
	//Check_commentt();
	public function wporg_more_comments( $post_id ) {
		echo '<p class="comment-form-more-comments"><label for="more-comments"><span class="required">*</span>' . __( 'code:  ', 'your-theme-text-domain' ) . ' <b>'.date('d').'+'.date('d').'=</b></label> <input type="text" name="my_captchaa" value="" /></p>';
	}
	public function Check_commentt(){
		if (isset($_POST['author']) && isset($_POST['email'])){
			if ($_POST['my_captchaa'] != date('d') + date('d')) {
				die("incorrect captcha. try again or notify administrator about problem");
			}
		}
	}
	
	//add_filter( 'wp_mail_content_type', function($cotnent_type=false){ return "text/html"; } ) ;
	// add_filter( 'wp_revisions_to_keep',	function($num,$post){return (defined("POSTS_REVISION_NUMBERS") ? POSTS_REVISION_NUMBERS : 3) ; }  , 10, 2 );



	// example JS script	
	//if( ! ($out = get_transient('termids_for_js'))) {
	//	$terms= get_terms();
	//	foreach($terms as $term){
	//		$cats[$term->term_id] = urldecode($term->slug);
	//	}
	//	$out = json_encode($cats, JSON_UNESCAPED_UNICODE);
	//}
	//echo "<script>cat_term_ids = $out;</script>";		
		
			
	public function get_metas_by_metakv($key, $value=null, $what=false) {
		global $wpdb;
		$results =  $wpdb->get_results( 
			$wpdb->prepare( "SELECT ". ($what ?: "*") ." FROM ".$wpdb->postmeta." WHERE meta_key=%s". ($value ? " AND meta_value=%s" : ""), $key, $value ) 
		);

		if (!empty($results)) {
			if ($what){
				$array= array();
				foreach($results as $index => $result) {  $array[$index] = $result->{$what};  }
				return $array;	
			}	
			return $results;
		}
		return false;
	}
		

			
	// adminis style 
	public function admin_menuu_style1(){ 
		//echo '<link rel="stylesheet" href="'.$this->baseURL.'style_admin_css_dashboard33.css?'.time().'" type="text/css" media="all" />';
		if ($this->property('admin_styles'))  echo  $this->admin_styles;	
	}
	
	/*
	public function Media_Uploader_code($value, $each_title){
		?>
		<div class="<?php echo $each_title;?>" style="border:1px gray black; border-width:0 0 2px 0;">
			<h1><?php echo $each_title;?></h1>
			<input id="my_upl_button_<?php echo $each_title;?>" type="button" value="Upload Image" /> <input id="my_image_URL_<?php echo $each_title;?>" name="my_metabxs[myX_<?php echo $each_title;?>]" type="text" value="<?php echo $value;?>" style="width:400px;" />
			<br/><img src="<?php echo $value;?>" style="width:160px;<?php if (empty($value)) {echo "display:none;";} ?>" id="picsrc_<?php echo $each_title;?>" />
			<script>
			jQuery(document).ready( function( $ ) {
				
				jQuery('#my_upl_button_<?php echo $each_title;?>').click(function() {
					//use here, because you may have multiple buttons, so `send_to_editor` needs fresh
					window.send_to_editor = function(html) {
						imgurl = jQuery(html).attr('src')
						jQuery('#my_image_URL_<?php echo $each_title;?>').val(imgurl);
						jQuery('#picsrc_<?php echo $each_title;?>').attr("src", imgurl).css("display","inline-block");
						tb_remove();
					}
				
					formfield = jQuery('#my_image_URL_<?php echo $each_title;?>').attr('name');
					tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
					return false;
				});

			});
			</script>
		</div>
	<?php
	}
	

	public function my_metabox( $type='text', $title){
		$GLOBALS['fields_for___'.$type][] = $title;	
		
		// avoid the below block to execute multiple times
		if (!defined('triggered_999999_'.$type)){	define('triggered_999999_'.$type,true);
			Register_functionality_for($type) ;
		}
	}

	public function Register_functionality_for($type){
		
		if ($type=='media_uploader'){ 
		
			add_action('plugins_loaded', function(){ 
				if($GLOBALS['pagenow']=='post.php'){
					add_action('admin_print_scripts',	function() {wp_enqueue_script('jquery');	wp_enqueue_script('media-upload');	wp_enqueue_script('thickbox'); }  );
					add_action('admin_print_styles',	function() {wp_enqueue_style('thickbox');	});
				}
			});
			
			
			
			add_action('add_meta_boxes', function(){  add_meta_box('my-Images-Upload', 'my-Images-Upload-box','myfunc8888', get_post_types(),'normal'); }, 9);
			public function myfunc8888($post){ 
				foreach ( $GLOBALS['fields_for___media_uploader'] as $each) { 
					$value = get_post_meta($post->ID, $each, true);
					Media_Uploader_code($value, $each);
				}
			}
			
		}
		
		elseif($type=='text'){ 
			add_action('add_meta_boxes', function(){  add_meta_box('my-fieldss', 'my-text-boxes','myfunc33322', get_post_types(),'normal'); }, 9);
			public function myfunc33322($post){ 
				foreach ( $GLOBALS['fields_for___text'] as $each) { 
					$value = get_post_meta($post->ID, $each, true);
					?>
					<div class="<?php echo $each;?>" style="border:1px solid black; border-width:0 0 2px 0;">
						<h1><?php echo $each;?></h1>
						<input id="my_field_<?php echo $each;?>" name="my_metabxs[myX_<?php echo $each;?>]" type="text" value="<?php echo $value;?>" style="width:400px;" />
					</div>
				<?php
				
				}
			}
		}
		
		elseif($type=='checkbox'){ 
			add_action('add_meta_boxes', function(){  add_meta_box('my-checkboxs', 'my-checkboxes','myfunc6555', get_post_types(),'normal'); }, 9);
			public function myfunc6555($post){ 
				foreach ( $GLOBALS['fields_for___checkbox'] as $each) { 
					$value = get_post_meta($post->ID, $each, true);
					?>
					<div class="<?php echo $each;?>" style="border:1px solid black; border-width:0 0 2px 0;">
						<h1><?php echo $each;?></h1>
						<input type="hidden" name="my_metabxs[myX_<?php echo $each;?>]" value="0" />
						<input type="checkbox" name="my_metabxs[myX_<?php echo $each;?>]" value="1" <?php echo ($value? 'checked="checked"': '');?> />
					</div>
				<?php
				
				}
			}
		}		
		
		elseif($type=='textarea_FULL'){ 
			add_action('add_meta_boxes', function(){  add_meta_box('my-textarea-fieldss', 'my-textarea1-boxes','myfunc6444', get_post_types(),'normal'); }, 9);
			public function myfunc6444($post){
				foreach ( $GLOBALS['fields_for___textarea_FULL'] as $each) { 
					$value = get_post_meta($post->ID, $each, true);
					?>
					<div class="<?php echo $each;?>" style="border:1px solid black; border-width:0 0 2px 0;">
						<h1><?php echo $each;?></h1>
						<?php wp_editor( htmlspecialchars_decode($value), 'styl_ID_'. $each, $settings = array('textarea_name'=>'my_metabxs[myX_'.$each.']',  'editor_class' => "txtaream editoor_ful"));?>
					</div>
				<?php
				
				}
			}
		}		
		elseif($type=='textarea_MINIMAL'){ 
			add_action('add_meta_boxes', function(){  add_meta_box('my-textarea-fieldss2', 'my-textarea2-boxes','myfunc88884', get_post_types(),'normal'); }, 9);
			public function myfunc88884($post){
				foreach ( $GLOBALS['fields_for___textarea_MINIMAL'] as $each) { 
					$value = get_post_meta($post->ID, $each, true);
					?>
					<div class="<?php echo $each;?>" style="border:1px solid black; border-width:0 0 2px 0;">
						<h1><?php echo $each;?></h1>
						<?php wp_editor( htmlspecialchars_decode($value), 'styl_ID_'. $each, $settings = array('textarea_name'=>'my_metabxs[myX_'.$each.']',  'teeny'=>true, 'tinymce'=>false, 'editor_class' => "txtaream editoor_min",  'media_buttons'=>false ));?>
						<style>.editoor_min{height:160px;}</style>
					</div>
				<?php
				
				}
			}
		}	


		
		// Save Action
		add_action( 'save_post', public function ($post_id) {
			if (!empty($_POST['my_metabxs'])){
				foreach ($_POST['my_metabxs'] as $key=>$value) { 
					update_post_meta($post_id, str_replace('myX_','', $key), $value);
				}
			}
		});	
	}
	
	*/
	
	public function error_mail($subject, $text){
		return wp_mail(get_option('admin_email'),  $subject,  $text );
	}
		
		


	public function myframe_center($content){
		if(is_singular() && stripos($content,'<iframe') !==false){
			$content= preg_replace('/\<iframe (.*?)\>/si','<div class="frame_parentt" style="text-align:center;">$0</div>', $content);
		}
		return $content;
	}



	//add_shortcode( 'YoutubeVideo', 'output_mymn24' );
	//add_shortcode( 'Youtube', 'output_mymn24' );
	public function output_mymn24($atts){
		$idd = get_youtube_id_from_contents($atts['url']);
		return '<div class="ytframe_parent"><iframe class="ytb_framee"  src="https://www.youtube.com/embed/'.$idd.'?rel=0" frameborder="0" allowfullscreen></iframe><div style="clear:both;"></div></div>';
	}
	
	public function change_output()
	{
		add_action('wp_loaded', $buffer_start = function() { ob_start($func = function ($buffer) {
			// modify buffer here, and then return the updated code
			$buffer = str_replace('MERCEDES','FERRARI',$buffer);
			return $buffer;
		}); } ); 
		add_action('shutdown',  $buffer_end   = function() { ob_end_flush(); } );     
	}

	public function update_or_insert($tablename, $NewArray, $WhereArray=[]){	global $wpdb; $arrayNames= array_keys($WhereArray);
		//convert array to STRING
		$o=''; $i=1; foreach ($WhereArray as $key=>$value){ $o .= $key . ' = \''. $value .'\''; if ($i != count($WhereArray)) { $o .=' AND '; $i++;}  }
		//check if already exist
		if(!empty($o)){
			$CheckIfExists = $wpdb->get_var("SELECT ".$arrayNames[0]." FROM ".$tablename." WHERE ".$o);
			if ( $wpdb->update($tablename,	$NewArray,	$WhereArray	) ) return true;
		}
		if ( $wpdb->insert($tablename, 	array_merge($NewArray, $WhereArray)	) ) return true;
		return false;
	}

	public function add_column_my($table_name='tablename', $column_line= '`smth` varchar(100)', $after=''){
		global $wpdb;
		preg_match('/`(.*?)`/i',$column_line, $n);  $column_name=  $n[1];
		$all_columns = $wpdb->get_col( "DESC " . $table_name , 0 );
		$result= 'already exists';
		if (!in_array($column_name, $all_columns )){  
			$result= $wpdb->query( $sql = ("ALTER     TABLE ".$table_name."   ADD ".$column_line."  NOT NULL ". ($after? "AFTER ".$after : "") ) );  // CHARACTER SET utf8 
		}
		return $result;
	}

	public function get_row_my($table_name='tablename', $line = '`ID` = 42'){
		global $wpdb;
		$res = $wpdb->get_row( $sql = "SELECT * FROM ". $table_name . " WHERE ".$line  );
		return $res;
	}

	public function get_results_my($table_name='tablename', $line = '`ID` = 42'){
		global $wpdb;
		$res = $wpdb->get_results( $sql = "SELECT * FROM ". $table_name . " WHERE ".$line  );
		return $res;
	}


	public function get_table_my($table_name='tablename', $line = '`ID` = 42'){
		global $wpdb;
		$res = $wpdb->get_results( $sql = "SELECT * FROM ". $table_name);
		return $res;
	}

	public function show_post_categories($vars=array('POST_ID'=>false, 'excluded_categories'=>array(-1) ) )  {   $x=''; 
		if (!$vars['POST_ID']) { $vars['POST_ID']= $GLOBALS['post']->ID; }
		$post_categories = wp_get_post_categories( $vars['POST_ID'] ); 
		$cats=array();
		foreach($post_categories as $c){   $cats[] = get_category( $c );   }
		foreach($cats as $c){ if (!in_array($c->term_id,   ($vars['excluded_categories'] ?: array())      ) ) {$x .= '<a href="'.get_term_link( $c->term_id, 'category' ).'" target="_blank">'.$c->name.'</a>, '; }  }  return $x;
	}
	
		
	//add_action( 'after_setup_theme', 'my_theme_add_editor_styles' );
	public function my_theme_add_editor_styles() {
		add_editor_style( PHP_customCALL_1.'tinymce_styles&ver='.$this->changeable_JS_CSS_version );
	}

	//change slug,if already exists slug for posts/or/pages
	//add_action('save_post', 'efrg324f3f32f4',3);	
	public function efrg324f3f32f4($post) 	{
		if (isset($_POST['post_name'])) { 
			global $wpdb;
			$slug = sanitize_text_field($_POST['post_name']); 
			
			$Post_id_1		= $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '%s' AND ( post_type = 'page' OR post_type = 'post') ", $slug) );
			$post_counts_1	= $wpdb->get_var($wpdb->prepare("SELECT count(post_name) FROM ".$wpdb->posts ." WHERE post_name like '%s'", $slug) );
			
			if (!empty($Post_Object_1) || $post_counts_1 < 1) {
				$_POST['post_name'] = $slug. '-'.rand(11,9999999);
			}
		} 
	}	

	//lets load after init of LANGUAGE phrazes
	public function mailform_page($title=false){
		echo '<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>'.(isset($title) ? $title : 'Mail Sending'). '('.$_SERVER['http_host'].')'.'</title></head><body>';      
		echo contacttt_form() ;
		echo '</body></html>';
		exit;
	}

	//add_shortcodeX('MyCONTACT_FORM', 'contacttt_form' );
	public function contacttt_form($extra_block=''){ 	global $lang;  $rand= rand(1,111); $multiplier=date('j');  
		if ( defined('LNG'))					{$lngg = LNG; } 
		elseif ( defined('LnG'))				{$lngg = LnG; }
		elseif ( isset($_GET['lang']))			{$lngg = sanitize_key($_GET['lang']); }
		elseif ( !empty($_COOKIE[siteslug()]))	{$lngg = sanitize_key($_COOKIE[siteslug()]); }
		else 									{$lngg = 'geo'; }
		
		$extra_bl =(!is_array($extra_block) ? $extra_block : '');
		$your_name =(is_array($extra_block) && !empty($extra_block['eng'])) ? "Your Name" : TRANSLL('mailform_YOUR_NAME');
		$your_mail =(is_array($extra_block) && !empty($extra_block['eng'])) ? "Your Email" : TRANSLL('mailform_YOUR_MAIL');
		$antispam_1=(is_array($extra_block) && !empty($extra_block['eng'])) ? "Please insert AntiSpam" : TRANSLL('mailform_ANTISPAM');
		$antispam_2=(is_array($extra_block) && !empty($extra_block['eng'])) ? "Please check AntiSpam" : TRANSLL('mailform_ANTISPAM2');
		$x = utf8_declarationn() . '
		<div id="contac_DIV">
			<style type="text/css">
			#submitContatForm{cursor:pointer;}
			.cf_formm{ display:block;	margin:0 auto;}       .cf_formm table{width:100%; max-width:400px; margin:0 auto;}
			.brdr{ border-radius:5px; border:1px solid; padding:3px; margin:5px; background-color:#E6E6E6; }
			.cfinputt{display:block; width:92%; height:30px; min-width:140px; }      .cftxtarea{display:block; width:96%; height:200px;}
			.submt{ cursor:pointer; }
			td{vertical-align: middle;}         td.leftflot{float:left; padding:0 0 0 10px;}     span.antWORD{font-weight:bold;}
			</style> 
			<form class="cf_formm" action="" method="POST" id="contactFormID" target="_blank"> 	<input type="hidden" name="contactIsSubmited" value="y" />
			<table><tbody>
			'.$extra_bl.'
			<tr><td>'. $your_name .'</td><td><input class="cfinputt brdr" name="namee" value="" placeholder="" type="text" /></td></tr>
			<tr><td>'. $your_mail .'</td><td><input class="cfinputt brdr" name="emailii" value="" placeholder="" type="text" /></td></tr>
			<tr><td colspan="2"><textarea class="cftxtarea brdr" name="teext"/></textarea></td></tr>
			<tr><td>'. $antispam_1 .' : <span class="antWORD">'. ($rand*$multiplier) .'<input type="hidden" name="initiall" value="'. $rand .'" /></span></td><td class="leftflot"><input class="cfinputt brdr" type="text" value="" name="antiSpamm"  /></td></tr>
			<!-- <tr><td>'. $antispam_2  .'</td><td class="leftflot"><input type="hidden" value="noo" name="antisp_conf"  /><input class="cfinputt confrm" type="checkbox" value="yess" name="antisp_conf"  /></td></tr> -->
			
			<tr><td><input class="cfinputt brdr submt" type="submit" value="SEND" id="submitContatForm"  /></td><td>&nbsp;</td></tr>
			</tbody></table>
			</form>
		</div>
		
		<script type="text/javascript">
		if (false && window.jQuery ) {
			if (window.jQuery) {
				$("#contactFormID").on("submit", function(evt){
					evt.preventDefault();
					SHOW_waiting();
					jQuery.ajax({
					method: "POST",  url: "'. homeURL.'/index.php",  data: $("#contactFormID").serialize()		 //{ name: "John", location: "Boston" }
					})
					.done(function( msg ) {
					HIDE_waiting();  document.getElementsByClassName("fancybox-close")[0].click(); show_my_popup(msg);
					});
				});
			}
		}
		function popupContactFromm_standard(){	show_my_popup( document.getElementById("contac_DIV").innerHTML);}
		function popupContactFromm_fancy(){
			$.fancybox({
				"href"			: "#contac_DIV",
				"width"			: 125,
				"titleShow" 	: false,
				"showCloseButton": false,
				"centerOnScroll": true,	
				"scrolling"		: "no",
				"helpers"		: {     "overlay":{ "closeClick": false, "locked": false, }     }
			});
		}  
		
		</script>';
		return $x;
	} 

	//if (isset($_GET['contactMAILpage'])) {
	//	mailform_page();
	//}


	//if(!$this->definedTRUE('avoid_mailcheck')) { add_actionX('init','check_mailsentt'); }
	public function check_mailsentt(){
		if (!empty($_POST['contactIsSubmited'])){
			header('Content-Type: text/html; charset=utf-8');  
			global $odd,$lang;
			$messiji	= isset($_POST['teext'])	? sanitize_text_field($_POST['teext'])	: '';
			$maill		= isset($_POST['emailii'])	? sanitize_text_field($_POST['emailii'])	: ''; 
				$from_mail =(!stristr($maill,'@yahoo.com')) ?		$maill : 'X'.rand(1,1111111).rand(1,1111111).'@no-reply.com';
			$nameei		= isset($_POST['namee'])	? sanitize_text_field($_POST['namee'])	: '';
			
			$admin_mail	= get_option('admin_email');
			$subjectt	= $_SERVER['HTTP_HOST'] . '-dan gamogzavnilia shetyobineba';
			$full_messag="FROM: $nameei ($maill) \r\n\r\n Message:\r\n" . $messiji;
			
			
			//if (($_POST['antisp_conf']=='yess') && (!empty($_POST['namee']) && !empty($_POST['emailii']) && !empty($_POST['teext'])))
			if(empty($_POST['namee']) || empty($_POST['emailii']) || empty($_POST['teext']))					{die(!defined('LNG') ? "please, fill form" : $lang['fill_form_'.LNG] ); }	
			elseif ( ! ( in_array($_POST['antiSpamm']/$_POST['initiall'],  array(date('j'),date('j')-1))) ) 	{die("Error Captcha");	}
			else {  exit(send_maill($admin_mail,$subjectt,$full_messag, $from_mail )); }
		}		
	}

		
	public function check_mailsent2(){
		if(isset($_GET['sendmessage'])){
			$to				= get_option('admin_mail');
			$subject		= !empty($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '' ;
			$from			= validate_mail($_POST['email']) ? sanitize_text_field($_POST['email']) : die("incorrect_mail");
			$name			= isset($_POST['name']) ? sanitize_textt($_POST['name']) : die("incorrect NAME");
			$messg			= isset($_POST['content']) ? sanitize_textt($_POST['name']) : die("incorrect NAME");
			$phone			= !empty($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '' ;
			$message	= "Name: ".$name."\r\nE-mail: ".$from."\r\nPhone: ".$phone."\r\nMessage:\r\n\r\n".$messg;
			if($_POST['captxt'] != $_POST['Captcha']){ die("incorrect captcha"); }
			// mailsent_page($to,$subject,$message, $from);
			send_maill($to,$subject,$message,$from);
		}
	}


	public function send_maill($to, $subject, $message, $from=false, $reply_to=false){
		$result = my_mail($to ,$subject, $message ,  default_mail_headers($from));

		if ($result) { $success_messg='<span class="seent" style="color:green; display:block; font-size:25px;">SENT!</span>';}
		else		 { $success_messg='<span class="cant_send" style="color:red;">ERRORR..</span>';	}
		return $success_messg;
	}

	public function simple_captcha_field($question='',$captcha_text=''){
		$GLOBALS['captcha_already_outputed']=true;
		$captcha_text= !empty($captcha_text) ? $captcha_text : rand(1,11111);
		echo '<style>input.captxt{color:black; width:60px; font-style:italic; font-size:15px;}</style>
			<div class="captcha_area">'.
				(!empty($question) ? "<div class=\"question\">$question</div>":""). 
				'<div class="cap_word"><input type=text" class="captxt" name="captxt" value="'.$captcha_text.'" /></div>'.
			'</div>';
			
		?>
		<script>
		window.onload=function(){
				var target='c'+'a'+'p'+'t'+'x'+'t';
				if(document.getElementsByClassName(target)[0]){
				var cptcha_input = document.getElementsByClassName(target)[0];
				//cptcha_input.setAttribute("name",target);
				cptcha_input.value=shuffle_Word(cptcha_input.value);
			}
		};
		</script>
		<?php
	}
	
	// only for temporary use
	public function js_debugmode($name='debugmode')
	{
		//if (isset($_GET[$name]))
		{
			$this->is_debug_mode= 'true';//$_GET[$name]=="true" ? "true" : "false";
			$this->debugmode_script = '<script>debug_mode_ ="'. $this->is_debug_mode .'";</script>';
			add_action('wp_head',	function(){ echo $this->debugmode_script; }, 1); 
			add_action('admin_head',function(){ echo $this->debugmode_script; }, 1);
		}
	}
	
	public function last_checkpoint($var_name, $seconds_to_check=86400){
		$opt= "last_checkpoints_rand_24df3023yfdh3qfhs";
		$this->$opt= !empty($this->$opt) ? $this->$opt : get_option($opt, []);
		if(empty($this->$opt) || empty($this->$opt[$var_name]) || !is_numeric($this->$opt[$var_name]) || $this->$opt[$var_name]< time()-$seconds_to_check ){
			$this->$opt[$var_name]	= time();
			update_option($opt, $this->$opt);
			return true;
		}
		return false;
	}

	//  add_action('wp_head', (function (){ if(is_home()) { echo '<script>IS_HOMEE=true;</script>'; }   }) ,2);

	//remove admin-generated css: add_action('get_header',  function () {	remove_action('wp_head', '_admin_bar_bump_cb'); } );




	/*
	if ($this->definedTRUE('force_wpconfig_loader_inject') && function_exists('sanitize_text_field') && empty($GLOBALS['goto_my_wpConfig']) && !defined("include_my_custom_wpConfig")){
		$filter = function ($path){return 	str_replace(array('/','\\'),DIRECTORY_SEPARATOR, $path); };
				
		//add in wp-config
		$str='define("include_my_custom_wpConfig",true); if(file_exists($a=__DIR__."/'.str_replace( $filter(ABSPATH),'', $filter($loader_file)).'")){ include_once($a); }'; 
		$wp_config=ABSPATH.'wp-config.php'; 
		//$new_content= preg_replace('/define\(\'DB_COLLATE(.*?)\);/', '$0'."\r\n\r\n".$str, $this->file_get_contents($wp_config), 1); 
		$new_content= str_replace('/* That\'s all, stop editing', $str."\r\n\r\n".'/* That\'s all, stop editing', $this->file_get_contents($wp_config)); 
		$this->file_put_contents($wp_config,$new_content);
		
		//create file
		$loader_file=$filter(dirname(dirname(__DIR__)).'/custom_files/__loader_wp_config.php');
		if(!file_exists($loader_file)) {
			$this->file_put_contents($loader_file, '<?php
				// check if this file is included IN WP-CONFIG
				if (defined("include_my_custom_wpConfig")) {
					if (!defined("my_wp_cofnig_called")) { define("my_wp_cofnig_called", 1);
						define("WP_DEBUG", 	0); 
						define("WP_DEBUG_DISPLAY", 0);
						return;
					}
				} 
				?>'
			);
		}
		
		$GLOBALS['goto_my_wpConfig']=1;
	}
	*/



	// https://wordpress.stackexchange.com/questions/16382/showing-errors-with-wpdb-update
	public function show_wp_error(){
		global $wpdb; 
		$wpdb->show_errors = TRUE;
		$wpdb->suppress_errors = FALSE;

		$wpdb->show_errors(); $wpdb->print_error();  
		if ($wpdb->last_error) {
		  die('error=' . var_dump($wpdb->last_query) . ',' . var_dump($wpdb->error));
		}
	}

	public function add_favicons()
	{
		$func = function($first=false){ 
			$x='';
			if ($this->test_environment)	{$x="_localhost"; }  
			if (IS_ADMINN)		{$x .="_admin"; }
			$final_url =  $this->baseURL.'customs/media/favicons/favicon'.$x .'.png';
			if (!$this->test_environment && !IS_ADMINN) { $final_url = defined("site_favicon") ?   ( stripos(site_favicon,'//') !==false ? "" : $this->baseURL).site_favicon   : $final_url; }
			return '<link rel="icon" type="image/png"  href="'. $final_url.'" />';
		};

		add_action('admin_head', $func);
		add_action('wp_head', $func);
		 
	}

	/*
		add_action( 'widgets_init',	 function () {	
			$optval=get_option('optname_widgets', my_sample_array_widgets);
			$additional_array = !empty($GLOBALS['MyWidgetss']) ? $GLOBALS['MyWidgetss'] : array();
			$widgets= array_merge( explode(',',$optval),  $additional_array );
			if (!empty($widgets) ) {
				foreach ($widgets as $value){
					register_sidebar( array('name' => $value ,'id' => strtolower($value),	'before_widget'=>'<div class="sideb_clas '.$value.'">','after_widget'=>'</div>','before_title'=>'<h2 class="sideb_around">','after_title'=>'</h2>') );
				}
			}
		});
	*/

	// Default class 
	public function add_content_classes()
	{
		$div_default_Excerpt_class = function ($cont){ return !isset($GLOBALS['post']) ? $cont : '<div class="default-excerpt-clss excp_'. ( !empty($GLOBALS['post']->ID) ?  $GLOBALS['post']->ID : 'undefineddd') .'">'.$cont.'</div>'; };
		add_action('the_excerpt',		$div_default_Excerpt_class);  
		add_action('the_excerpt_rss',	$div_default_Excerpt_class); //<-deprecated or not?  
		add_action('the_excerpt_feed',	$div_default_Excerpt_class);
		
		$div_default_Content_class = function ($cont){ return !isset($GLOBALS['post']) ? $cont : '<div class="default-content-clss type_'.$GLOBALS['post']->post_name.' cnt_'.  $GLOBALS['post']->ID .'">'.$cont.'</div>';  };
		add_action('the_content',		$div_default_Content_class);
		add_action('the_content_rss',	$div_default_Content_class); //<-deprecated
		add_action('the_content_feed',	$div_default_Content_class);
	}
	
	
		
	//CSS CLASSES for BODY
	public function add_my_body_classes()
	{
		$bClass1 = function ( $classes ) {
			$this->add_body_class_($classes, $this->domain);
			$this->add_body_class_($classes, (is_admin() ? "admin":"public") );
			//$this->add_body_class_($classes, 'myLNG_'. (defined('LNG') ? LNG : '') ); 
			//$this->add_body_class_($classes, $this->['brwsr']);
			//$this->add_body_class_($classes, $GLOBALS['odd']['is_pc_platform'] ? "pcOS" : "mobileOS");
			//$this->add_body_class_($classes, 'new_browser_'.$GLOBALS['odd']['is_new_browser'] );
			
			//add role
			$user = wp_get_current_user();
			$role = ( array ) $user->roles;
			$chosen = 'role-'.$role[0]; // //array_shift($GLOBALS['current_user']->roles);
			$this->add_body_class_($classes, $chosen);
			//
			return $classes;
		};
		
		add_filter( 'body_class', 		$bClass1 );
		add_filter( 'admin_body_class', $bClass1 );
	}

	public function add_body_class_(&$classes, $value){ if (is_array($classes)) $classes[] = $value;  else $classes .= $value; return $classes;  }
	 
	
	//allsite_options
	public function get_option_my($keyNAME, $re_call = false, $defaultvalue=false){
		if (!isset($this->my_custom_optioned_array) || !array_key_exists($keyNAME, $this->my_custom_optioned_array) || $re_call) {
			$x = get_option('my_optioned_arrayyy',array());
			if (!array_key_exists($keyNAME,$x))  { $x[$keyNAME]=false;}
			if ($x[$keyNAME]==false || !empty($defaultvalue) ) { $x[$keyNAME]=$defaultvalue;   update_option('my_optioned_arrayyy',$x); }
			$this->my_custom_optioned_array = $x;
		}
		return $this->my_custom_optioned_array[$keyNAME];
	}

	public function update_option_my($keyNAME, $value){
		$x= get_optionX($keyNAME, true);
		$x[$keyNAME] = $value;
		update_option('my_optioned_arrayyy',$x);
	}



	public function is_login_page(){ return did_action('login_init'); }
	public function is_posteditor_page(){ global $pagenow;
		if 	( is_admin()
			&& 
			(
				(in_array( $pagenow, array('post.php'))  && 'edit' ==$_GET['action'])	//if Edit page 
				|| (in_array( $pagenow, array('post-new.php'))) 						//if NEW page
			)
		){	return true;	}
		else{ return false;}
		
	} 

 
	//header_parts(false,false,true,false,false,false,false,false,false,false,false)
	public function header_parts(
		$auto_title=true,					//1
		$DEFINED_site_title=false,  		//2
		$DEFINED_site_description=false, 	//3 
		$DEFINED_title=false,  				//4
		$DEFINED_description=false,  		//5
		$DEFINED_url=false,  				//6
		$DEFINED_fb_og_title=false,  		//7
		$DEFINED_fb_og_description=false,  	//8
		$DEFINED_fb_og_image=false,		  	//9
		$DEFINED_default_image=false		//10
	 ){ 
		global $post,$odd;  $out = '';
	 
							$title = IS_SINGULARR ? get_the_title() :  wp_title('',false);
		$default_titlee	= customm_word_length_sentence($title , 9); 					// (wp_title('',false), 9); 
		$default_desc= $post ? customm_word_length_sentence($post->post_content ,35) : $default_titlee  ;
			 
		$default_imag	= ($DEFINED_default_image ? $DEFINED_default_image : (defined('Default_Post_Thumb_Imagee') ? Default_Post_Thumb_Imagee : $this->baseURL.'library/media/other/default_img.png') );	 		$final_imag='';

		$MAIN_TITLL		= htmlentities((isset($GLOBALS['MAIN_TITLL'])	 ? $GLOBALS['MAIN_TITLL'] : get_bloginfo('name')),				ENT_QUOTES); 
		$MAIN_TITLL_OFC	= htmlentities((isset($GLOBALS['MAIN_TITLL_OFC'])? $GLOBALS['MAIN_TITLL_OFC'] : get_bloginfo('description')),	ENT_QUOTES); 
			 
			$final_title=$default_titlee;
			$final_desc	=$default_desc;
		
		
		
		if ($auto_title) {
			$whichPaged	= ($x = get_query_var('paged')) ? " - $x " : "";

			// Front page
			if ( IS_HOMEE )	{
				$final_title= $MAIN_TITLL .$whichPaged;		$final_desc = $MAIN_TITLL_OFC; 	$out .=  '';
			}
			elseif (IS_SINGLEE)
			{
				//==================================== detect according to post type==============================
				if ('video' == POST_FORMATT) {
					//others
					$y_ID = get_youtube_id_from_VIDEOPOST($post); 			
					
					if (!defined('VIDEO_TYPE_OG_DISABLED')) {
						$out .=  
						'<meta property="og:type" content="movie" />'.
						'<meta property="og:video" content="'.$this->currentURL.'" />'.
						'<meta property="og:video:secure_url" content="'.$this->currentURL.'" />'.
						//'<meta property="og:video" content="https://www.youtube.com/v/'.$y_ID.'&fs=1&version=3&showinfo=0&autohide=0&autoplay=1&rel=0&modestbranding=1" />'.
						//'<meta property="og:video:secure_url" content="https://www.youtube.com/v/'.$y_ID.'&fs=1&version=3&showinfo=0&autohide=0&autoplay=1&rel=0&modestbranding=1" />'.
						'<meta property="og:video:type" content="application/x-shockwave-flash" />'. 
						'<meta property="og:video:width" content="560" />  <meta property="og:video:height" content="340" />'
						;
					}
					//re-set image for this post_type
					$final_imag = videos_thumb_FINDD($post); 
					//$final_imag = str_replace('mqdefault','hqdefault',$default_imag);
				}
	 
				// 2) ---------- AUDIO ---------------
				elseif ('audio' == POST_FORMATT) { 
					$aud_file_linki=homeURL.'/wp-content/uploads/'.get_MyAttachment_path($post); 

					$out .= 
					'<meta name="medium" content="audio" />
					<meta property="og:type" content="music.song"/><META PROPERTY="og:audio:title" CONTENT="'. $default_titlee .'"><meta property="og:audio" content="'.$aud_file_linki.'"/>
					<!--   <meta PROPERTY="sssssssssssssssssssog:audio:artist" CONTENT="NO NAME">
					<META PROPERTY="sssssssssssssssssssog:audio:album"  CONTENT="NO NAME">
					<meta property="sssssssssssssssssssaudio:secure_url" content="'.$aud_file_linki.'" /> -->
					<meta property="og:audio:type" content="application/mp3"/> ';

					//re-set image for this post_type
					$default_imag =  $this->baseURL.'custom_files/media/default_audio_page_image1.jpg'; 
				}
				
				else{}  // no extra FB needed 
				
								$x= Get_ImageUrl_FromPost($GLOBALS['post'],'full');
				$default_imag	= $x['imgURL'];	$default_imag=str_replace('/mqdefault.jpg','/hqdefault.jpg',$default_imag);
				$final_og_title	=	!empty( $GLOBALS['post_og_titlee']) ?  $GLOBALS['post_og_titlee'] : '';
				$final_og_desc	=	!empty( $GLOBALS['post_og_descrr']) ?  $GLOBALS['post_og_descrr'] : '';
			}	

			//if any kind of "PAGE"  type
			elseif (IS_PAGEE)	{				
								$x= Get_ImageUrl_FromPost($GLOBALS['post'],'full');
				$default_imag	= $x['imgURL'];	$default_imag=str_replace('/mqdefault.jpg','/hqdefault.jpg',$default_imag);
				$final_og_title	=	!empty( $GLOBALS['post_og_titlee']) ?  $GLOBALS['post_og_titlee'] : '';
				$final_og_desc	=	!empty( $GLOBALS['post_og_descrr']) ?  $GLOBALS['post_og_descrr'] : '';
				$final_title .= $whichPaged;		
			}
		 
			//if any kind of "PAGE"  type
			elseif (IS_SEARCHH)	{	$final_title = TRANSLL('search_results').' - '.get_search_query();	}

			//if CATEGORY
			elseif (IS_CATEGORYY)	{
				$final_title = single_cat_title( $prefix = '', $display = false ) .$whichPaged;			$out .='';
					if (!empty($GLOBALS['wp_query']->queried_object->description) )  { 
						do_shortcode($GLOBALS['wp_query']->queried_object->description); 
						if (!empty($GLOBALS['CategImgggg'])) { $default_imag = $GLOBALS['CategImgggg'];}
					}
			}
			//if CATEGORY
			elseif (IS_ARCHIVEE)	{
				$final_title = wp_title('',false);		$out .='';
			}
			// ======== ###END### Meta Titles
			
			$final_og_title	= !empty($final_og_title)	? $final_og_title	: $final_title;
			$final_og_desc	= !empty($final_og_desc)	? $final_og_desc	: $final_desc;
			
			if (IS_SINGULARR)	{
				$fbTITLE		= get_post_meta($post->ID, 'fb_titlee',		true);
				$fbDESCR		= get_post_meta($post->ID, 'fb_contentt',	true);
				if (!empty($fbTITLE))	{$final_og_title= $fbTITLE;}	
				if (!empty($fbDESCR))	{$final_og_desc	= $fbDESCR;} else { $authorr = get_post_meta($post->ID,'author_title', true);  if (!empty($authorr)) {$final_og_desc= $authorr; }  }	
			}
			
		}	
			
			$final_og_title	= !empty($final_og_title)	? $final_og_title	: $final_title;
			$final_og_desc	= !empty($final_og_desc)	? $final_og_desc	: $final_desc;
			
		 
		$final_out = '
		<title>'.  ($DEFINED_title ? $DEFINED_title : $final_title ) .'</title>
		<meta name="title" content="'. ($DEFINED_title ? $DEFINED_title : $final_title ) .'" >
		<meta name="description" content="'. ( $DEFINED_description ? $DEFINED_description : $final_desc  ).'" >'
		//Opengraph (facebook) tags
		. $out;
		return $final_out;
	}

		  


	//$this->check_actions();
	public function check_actions($act = "")
	{
		if ($act=="") $act=sanitize_key($_GET['actionn']);
		
		if ($act == 'myButons88')
		{
			header("Content-type: application/javascript");
			// this stops wp-settings from load everything
			//define ('SHORTINIT',true);
			//require('../../../../wp-load.php');
			//define( 'ABSPATH', __DIR__ . '/' );
			//require ('wp-config.php'); ?>
			
			//
			(function() {
			tinymce.create('tinymce.plugins.Nlineeplg', {
				init : function(ed, url) {


						ed.addButton('youtube_video', {
							title : 'youtub_vid',
							image : url+'/library/media/tinymce__editor_buttons/youtubee.png',
							onclick : function() {
								var newtex= prompt("Youtube Link", ""); if (null == newtex) {return;}
									var finall='[YoutubeVideo url="'+newtex+'"]';
								ed.execCommand('mceInsertContent', false, finall);
									
							}
						});
							
						
						ed.addButton('audioo', {
							title : 'audioo',
							image : url+'/library/media/tinymce__editor_buttons/audio.png',
							onclick : function() {
								var newtex= prompt("Audio link", ""); if (null == newtex) {return;}
									var finall='[audio mp3="'+newtex+'"]';
								ed.execCommand('mceInsertContent', false, finall);
									
							}
						});
							
						
						
						ed.addButton('add_spacee_button', {
							title : 'AddNewLin',
							image : url+'/library/media/tinymce__editor_buttons/adl_img.png',
							onclick : function() {
								ed.execCommand('mceInsertContent', false, '<span style="margin:8px 0 0 0; display:block;" class="myNewLine_buton">&nbsp;</span>');
								//ed.execCommand('mceInsertContent', false, "\r\n<br/>"+"&nbsp;"+"\r\n<br/>");
							}
						});


						ed.addButton('removeline_button', {
							title : 'RemoveLines',
							image : url+'/library/media/tinymce__editor_buttons/rml_img.png',
							onclick : function() {
								var gotted= tinyMCE.activeEditor.selection.getContent({format : 'raw'})
								var newtex= gotted.replace(/<br \/>/g,'');
									newtex= newtex.replace(/\r\n/g,'');
									newtex= newtex.replace(/\n/g,'');
								ed.execCommand('mceInsertContent', false, newtex);	
							}
						});


						ed.addButton('abzac_button', {
							title : 'Abzaci',
							image : url+'/library/media/tinymce__editor_buttons/paragraphh.png',
							onclick : function() {
								ed.execCommand('mceInsertContent', false, '<span style="margin:0 0 0 10px;" class="myspace_buton">&nbsp;</span>');
							}
						});
						
						
						
						ed.addButton('lists', {
							title : 'List Pages,Categories,Menus',
							image : url+'/library/media/tinymce__editor_buttons/lists.png',
							onclick : function() {
								var newtex= prompt('Insert AUTO-LISTING (Pages,Categories or Menus..)\r\ni.e.\r\n[list type="categories" child_of="34"]\r\n[list type="pages"         child_of="34"] (or child_of="this")\r\n[list type="menu" menu="My_Custom_Menu_Name"] (or menu="menu_ID")\r\n\r\n p.s.you can use all other parameters, described in online references ("wp_list_pages" or "wp_list_categoris" or "wp_nav_menu")', '[list type="pages" child_of="this"]'); if (null == newtex) {return;}
								ed.execCommand('mceInsertContent', false, newtex);
							}
						});


						ed.addButton('videomovie', {
							title : 'VideoMovie',
							image : url+'/library/media/tinymce__editor_buttons/video.png',
							onclick : function() {
								var newtex= prompt('mp4 link'); if (null == newtex) {return;}
									newtex= '[video url="'+ newtex +'"]';
								ed.execCommand('mceInsertContent', false, newtex);
							}
						});

						ed.addButton('script', {
							title : 'Scriptt',
							image : url+'/library/media/tinymce__editor_buttons/script.png',
							onclick : function() {
								var newtex= prompt('insert code'); if (null == newtex) {return;}
									newtex= '[script content="'+ encodeURIComponent(newtex)  +'"]';
								ed.execCommand('mceInsertContent', false, newtex);
							}
						});




					},
					createControl : function(n, cm) {
						return null;
					}
					
					
				});
				tinymce.PluginManager.add('MyButtonss1', tinymce.plugins.Nlineeplg);
			})();
				
			<?php	
			exit;
		}
		
		elseif ( $act=='tinymce_styles'){ header('Content-Type: text/css');  ?>
			.anons_of{background: gray;} 
			.gray_backgroundd{}
			html .mceContentBody { max-width:100%;}
			<?php	exit;
		}
	}

 
}} // class
#endregion
//==========================================================================================================
//==========================================      ### WP codes     =========================================
//==========================================================================================================
































//==========================================================================================================
//==========================================================================================================
//======================================== 3) Main base for plugins  =======================================
//==========================================================================================================
//==========================================================================================================
#region 3
goto label_pro_plugin__PuvoxSoftware;
label_default_plugin__PuvoxSoftware:

if (!class_exists('default_plugin__PuvoxSoftware')) {
class default_plugin__PuvoxSoftware 
{
	use pro_plugin__PuvoxSoftware;
	
	public $helper;
	public $helpers;
	public function __construct($arg1=[])
	{
		$this->helpers = new standard_wp_library__PuvoxSoftware(['class'=>get_called_class()]);
		
		$this->plugin_inits(); 
	}

    public function __call($method, $arguments)
    {
        try {
            return call_user_func_array([$this->helpers, $method], $arguments);
        } catch (Exception $e) {
            throw $e;
        }
    }
	
	public function plugin_inits()
	{			
		if ($this->helpers->isWP)
		{
			$this->is_settings_page = false;
			$this->wpdb 			= $GLOBALS['wpdb'];
			$this->options_tabs 	= [];
			
			if (!$this->helpers->above_version("5.4")){
				register_activation_hook( $this->helpers->moduleFILE,	function(){ exit( __("Sorry, your PHP version ". phpversion() ." is very old. We suggest changing your hosting's PHP version.") ); }	);
				return;
			}
			// initial variables
			$this->my_plugin_vars();
			$this->network_managed	= is_multisite() && $this->IsNetworkManaged();
			$this->opts				= $this->refresh_options();							// Setup final variables
			$this->refresh_options_TimeGone();
			$this->logs_table_name	= $this->get_prefix_CHOSEN() . $this->plugin_slug_u.'__errors_log';	// error logs table name
			$this->logs_table_maxnum= 100;	// maximum rows in errors logs table
			$this->check_if_pro_plugin();
			$this->__construct_my();													// All other custom construction hooks
			$this->optionsPageSlug = property_exists($this,'customOptsPageUrl') ? $this->customOptsPageUrl : $this->slug;
			$this->settingsPHP_page	= $this->static_settings['menu_button_level']=="mainmenu" ? 'admin.php' : (is_network_admin() || $this->network_managed ? 'settings.php' : 'options-general.php');
			$this->plugin_page_url	= ( !is_network_admin() && (!is_multisite() || !$this->network_managed || !$this->IsNetworkwidePlugin() || !is_plugin_active_for_network($this->static_settings['plugin_basename']) ) ? admin_url() : network_admin_url() ) .$this->settingsPHP_page.'?page='.$this->optionsPageSlug; 
			$this->plugin_files		= array_merge( (property_exists($this, 'plugin_files') ? $this->plugin_files : [] ),   ['index.php'] );
			$this->translation_phrases= $this->get_phrases();
			$this->is_in_customizer	= (stripos($this->helpers->currentURL, admin_url('customize.php')) !== false);
			$this->myplugin_class	= 'myplugin postbox version_'. (!$this->static_settings['has_pro_version']  ? "free" : ($this->is_pro_legal ? "pro" : "not_pro") );
			$this->addon_namepart	= 'puvox.software';
			
			$this->define_option_tabs();

			//activation & deactivation (empty hooks by default. all important things migrated into `refresh_options`)
			register_activation_hook( $this->helpers->moduleFILE,	[$this, 'activate']		);
			register_deactivation_hook( $this->helpers->moduleFILE, [$this, 'deactivate']	);

			//translation hook
			add_action('init', [$this, 'load_textdomain'] );

			//==== my other default hooks ===//
			$this->plugin__setupLinksAndMenus();

			//shortcodes
			$this->shortcodes_initialize();

			// if buttons needed
			//if( property_exists($this, 'tinymce_buttons') ) $this->tinymce_funcs();

			// for backend ajax
			add_action( 'wp_ajax_'.$this->plugin_slug_u.'_all',	[$this, 'ajax_backend_call'] );

			add_action( 'admin_head', [$this,'admin_head_func']);
			add_action( 'current_screen', function(){ $this->admin_scripts(null); } );

			//add uninstaller file
			if(is_admin()) $this->helpers->add_default_uninstall();	//add_action( 'shutdown', [$this, 'my_shutdown_for_versioning']);

			add_action('wp',		[$this, 'flush_checkpoint'], 999);

			// functions for PRO-ADDON upload
			// add_filter( 'pre_move_uploaded_file', function( $null, $file, $new_file, $type ){ return $path; }, 10, 4);
			$this->pro_file_part = 'puvox-software';
			if($this->static_settings['has_pro_version']) 	{
				add_filter( 'upload_mimes', [$this->helper,'upload_mimes_filter'], 1); 
				add_filter( 'wp_handle_upload', [$this->helper,'wp_handle_upload_filter'], 10, 2);
			}
			
			if ($this->helpers->property('extra_options_enabled')) $this->add_extra_options_page(); 
		}
	}
	
	//example construct
	/*
    public function __construct_my()
    {
        //
        $this->helpers->error_to_mailaddress 	= 'your_mail@gmail.com';				// send errors to email
        $this->helpers->enable_write_logs		= false;								// enable logs writing
        $this->helpers->PASSWORD_FOR_SITE		= '';									//
        $this->helpers->google_analytics_ID		= '';									// 
        $this->helpers->google_tag_manager_ID	= '';									// 
        $this->helpers->top_ge_ID				= '';									// 
        $this->helpers->google_firebase_ID		= '';									// https://console.firebase.google.com/project/my-test-proj-fc1cb/overview 
        $this->helpers->auth_expiration_hours	= 444;									//
        $this->helpers->search_items_amount_in_menu	= 20;								//
        $this->helpers->shortcodes				= [];									//
        $this->helpers->customOptsPageUrl		= admin_url().'/.../';					//
        $this->helpers->extend_shortcodes		= true;									// activates shortcodes: image, video, link, iframe, @, script, video, list_subpages , 		
																										// i.e. [list type="categories"	id="32" depth=0 exclude="4,28"] 
																										// i.e. [list type="pages"		id="32" depth=0 exclude="4,28"]    (or id="this")
																										// i.e. [list type="menu" id="32"]
        $this->helpers->disable_update			= true;									// disable update for current plugin
		$this->add_my_site_options(['smth'=>'value']);  								// activates extra options page
		$this->admin_styles						= '<style>.xxx{}</style>';
		
        $this->helpers->load_scripts_override	= 
		[
            'jquery'			=> ['screen'=>['admin'=>0, 'public'=>1]],
            'jquery-migrate'	=> ['screen'=>['admin'=>0, 'public'=>0]],
            'jquery-ui'			=> ['screen'=>['admin'=>1, 'public'=>1]],
            'bootstrap'			=> ['screen'=>['admin'=>0, 'public'=>1]],
            'less'				=> ['screen'=>['admin'=>0, 'public'=>0]],
            'font-awesome'		=> ['screen'=>['admin'=>1, 'public'=>1]],
            //'font-awesome-anim1'=> ['screen'=>['admin'=>1, 'public'=>1]],
            'google-fonts'		=> ['screen'=>['admin'=>0, 'public'=>0]],
            'fancybox'			=> ['screen'=>['admin'=>1, 'public'=>1]],
            'animate'			=> ['screen'=>['admin'=>1, 'public'=>1]],
            'hover'				=> ['screen'=>['admin'=>1, 'public'=>1]],
            'cookies'			=> ['screen'=>['admin'=>1, 'public'=>1]],
            'spin'				=> ['screen'=>['admin'=>0, 'public'=>0]],
        ];
		 
		//if ($this->helpers->property('add_social_links_options')) add_action('admin_menu', [$this,'funccc413']);
        $this->helpers->plugin_reset_callback	= function(){};							//
        $this->helpers->hooks_examples			= function(){};							//
    }
	*/
	
	//add my default values
	public function my_plugin_vars($step=0)
	{
		//get default plugin data: https://goo.gl/Z3z8FW
		include_once(ABSPATH . "wp-admin/includes/plugin.php");
		$plugin_vars = $this->pluginvars();
		$this->slug			= sanitize_key($plugin_vars['TextDomain']);	//same as foldername
		$this->plugin_slug	= $this->slug;								//same as foldername
		$this->plugin_slug_u= str_replace('-','_', $this->slug);

		$AuthorDomain = !property_exists($this, 'PuvoxDomain') ? 'https://puvox.software/' : 'https://127.0.0.1/wp/puvox.software/';
		$this->static_settings	= $plugin_vars   +  
		[
			'plugin_basename'	=> plugin_basename($this->helpers->moduleFILE),
			'menu_text'			=> array(
				'donate'				=>__('Donate'),
				'settings'				=>__('Settings'),
				'open_settings'			=>__('You can access settings from dashboard of:'),
				'activated_only_from'	=>__('Plugin activable only from'),
				'deactivated_only_from'	=>__('Plugin deactivable only from'),
			),
			'lang'				=> $this->helpers->get_locale__SANITIZED(),
			'wp_rate_url'		=> 'https://wordpress.org/support/plugin/'.$this->slug.'/reviews/#new-post',
			'public_assets_url'	=> 'https://ps.w.org/internal-functions-for-protectpages-com-users/trunk/',
			'question_mark_icon'=> 'https://ps.w.org/internal-functions-for-protectpages-com-users/trunk/assets/question-mark-2.png',
			'donate_url'		=> 'https://paypal.me/Puvox', // business: http://paypal.me/Puvox   ||  personal : http://paypal.me/tazotodua
			'mail_errors'		=> 'wp_plugin_errors@puvox.software',
			'licenser_domain'	=> $AuthorDomain,
			'musthave_plugins'	=> $AuthorDomain.'/blog/must-have-wordpress-plugins/',
			'purchase_url'		=> $AuthorDomain.'/?purchase_wp_plugin='.$this->slug,
			'purchase_check'	=> $AuthorDomain.'/?purchase_wp_act=',
			'wp_tt_freelancers'	=> 'https://goo.gl/wZKANN',
			'wp_fl_freelancers'	=> 'https://goo.gl/JSVy37',
			'wp_pph_freelancers'=> 'https://goo.gl/vhrqiM',
			// virtual/overload
			'has_pro_version'	=> null,
			'menu_button_level'	=> null,
		];
		
		$this->declare_settings_initial(); 
		//enrich from main class
		if(is_admin()) $this->declare_settings(); 
		$this->static_settings	= $this->static_settings + $this->initial_static_options;
	}

	// will be overriden
    public function declare_settings_initial()
    {
        $this->initial_static_options	= array
        (
            'has_pro_version'	=>0, 
            'show_opts'			=>false, 
            'show_rating_message'=>false, 
            'display_tabs'		=>false,
            'required_role'		=>'install_plugins', 
            'default_managed'	=>'network'			//network, subsite, both
        );

        $this->initial_user_options	=
        [ 
            //'counter_limit'=>'&raquo;';
        ];
    }

	public function plugin__setupLinksAndMenus()
	{
		// If plugin has options, show button (in admin menu sidebar)
		if($this->static_settings['show_opts'])
		{
			$register_handle = function()
			{
				$menu_button_name = (array_key_exists('menu_button_name', $this->static_settings) ? $this->static_settings['menu_button_name'] : $this->opts['name'] );
				if( array_key_exists('menu_button_level', $this->static_settings) && $this->static_settings['menu_button_level']=="mainmenu" )
																																							// icons: https://goo.gl/WXAYCi 
					add_menu_page($menu_button_name, $menu_button_name, $this->static_settings['required_role'] , $this->slug, [$this, 'opts_page_output_parent'], $this->static_settings['menu_icon'] );
				else 
					add_submenu_page($this->settingsPHP_page, $menu_button_name, $menu_button_name, $this->static_settings['required_role'] , $this->slug,  [$this, 'opts_page_output_parent'] );

				// if target is custom link (not options page)
				if(array_key_exists('menu_button_link', $this->opts)){
					add_action( 'admin_footer', function (){
							?>
							<script type="text/javascript">
								jQuery('a.toplevel_page_<?php echo $this->slug;?>').attr('href','<?php echo $this->opts['menu_button_link'];?>').attr('target','_blank');
							</script>
							<?php
						}
					);
				}
			};

			if (is_multisite()){
				add_action('network_admin_menu', $register_handle );
				if ( !$this->network_managed ){
					add_action('admin_menu', $register_handle );
				}
			}
			else {
				add_action('admin_menu', $register_handle );
			}		
			//redirect to settings page after activation (if not bulk activation)
			add_action('activated_plugin', function($plugin) { if ($this->is_not_bulk_activation($plugin))  { exit( wp_redirect($this->plugin_page_url.'&isactivation') ); } } );
		}


		// show author & donate urls (unless hidden)
		if ( !array_key_exists('hide_plugin_links', $this->static_settings))
		{
			// add Settings & Donate buttons in plugins list
			add_filter( (is_network_admin() ? 'network_admin_' : ''). 'plugin_action_links_'. $this->static_settings['plugin_basename'],  function($links){
				if(!$this->static_settings['has_pro_version'])	{ $links[] = '<a href="'.$this->static_settings['donate_url'].'">'.$this->static_settings['menu_text']['donate'].'</a>'; }
				if($this->static_settings['show_opts']){ $links[] = '<a href="'.$this->plugin_page_url.'">'.$this->static_settings['menu_text']['settings'].'</a>';  }
				if(isset($this->opts['custom_opts_page'])){ $links[] = '<a href="'.$this->opts['custom_opts_page'].'">'.$this->static_settings['menu_text']['settings'].'</a>';  }
				//if(is_network_admin() && $this->initial_static_options['allowed_on'] =='subsite'){ unset($links['activate']); $links[] = '<b style="color:red;">'.$this->static_settings['menu_text']['deactivated_only_from'].' SUB-SITES</b>';  }
				return $links;
			});
		}
	}
	

	// ================  dont use activation/deactivation hooks =====================//
	public function activate($network_wide)
	{
		// Differentiation only applies when/if MultiSite enabled. Otherwise, always master site
		if ( is_multisite() )
		{
			if(
				(  $this->is_network_admin_referrer() && (!$this->IsNetworkwidePlugin()) )
					||
				( !$this->is_network_admin_referrer() && ($this->IsNetworkwidePlugin() || $network_wide ) )
			)
			{
				$text= '<h2><code>'.$this->opts['name'].'</code>: '. $this->static_settings['menu_text']['activated_only_from']. ' <b style="color:red;">'.($this->static_settings['default_managed']).'</b></h2>';
				//$text .=  '<script>alert("'.strip_tags($text).'");</script>';
				die($text);
			}
		}
		//$this->plugin_updated_hook();
		if(method_exists($this, 'activation_funcs') ) {   $this->activation_funcs($network_wide);  }
	}
	// commented part:  pastebin_com/KNM3iMEs

	public function deactivate($network_wide){
		if(method_exists($this, 'deactivation_funcs') ) {   $this->deactivation_funcs($network_wide);  }
	}

	//load translation
	public function load_textdomain(){
		load_plugin_textdomain( $this->slug, false, basename($this->helpers->moduleDIR). '/languages/' );
	}

	public function is_not_bulk_activation($plugin)
	{
		return ( $plugin == $this->static_settings['plugin_basename'] && !((new WP_Plugins_List_Table())->current_action()=='activate-selected'));
	}
	
	// for some reasons, native "is_network_admin()" doesn't work during ACTIVATION hook, and we need to manually use this
	public function is_network_admin_referrer()
	{
		return (array_key_exists("HTTP_REFERER", $_SERVER) && stripos($_SERVER["HTTP_REFERER"],'/wp-admin/network/') !==false);
	}

	public function pluginvars(){
		/*
		[Name] => My Plugin Name
		[PluginURI] => https://example.com
		[Version] => 1.23
		[Description] => Plugin Description. By [Author].
		[Author] => myAuthorTitle
		[AuthorURI] => https://example.com/xyz
		[TextDomain] => my-plugin-name
		[DomainPath] => /languages
		[Network] => 
		[Title] => My Plugin Name
		[AuthorName] => Author Name
		*/
		return get_plugin_data( $this->helpers->moduleFILE, $markup = true, $translate = false);    //dont $translate, otherwise you will get error of: https://core.trac.wordpress.org/ticket/43869
	}

	//get latest options (in case there were updated,refresh them)
	public function refresh_options(){
		$this->opts	= $this->get_option_CHOSEN($this->slug, []);
		if(!is_array($this->opts)) $this->opts = $this->initial_user_options;
		foreach($this->initial_user_options as $name=>$value){ if (!array_key_exists($name, $this->opts)) { $this->opts[$name]=$value;  $should_update=true; }  }
		$this->opts = array_merge($this->opts, $this->initial_static_options);
		$this->opts['name']		=$this->static_settings['Name'];
		$this->opts['title']	=$this->static_settings['Title'];
		$this->opts['version']	=$this->static_settings['Version'];
		if(isset($should_update)) {	$this->update_opts(); }
		return $this->opts;
	}

	public function refresh_options_TimeGone(){
		//if never updated
		if(empty($this->opts['last_update_time'])) {
			$this->opts['last_update_time'] = time();   $should_update=true;
		}
		if(empty($this->opts['last_updates'])) {
			$this->opts['last_updates'] = [];   $should_update=true;
		}
		if(empty($this->opts['fist_install_date'])) {
			$this->opts['fist_install_date'] = time();  $should_update=true;
		}
		//if plugin updated through hook or manually... to avoid complete break..
		if( empty($this->opts['last_version']) || $this->opts['last_version'] != $this->opts['version'] ){
			$this->opts['last_version'] = $this->opts['version'];
			$should_update=true;
			$reload_needed=true;
		}
		if(isset($should_update)) {	$this->update_opts(); }
		if(isset($reload_needed)) { $this->plugin_updated_hook(true); }
	}

	
	public function ajax_backend_call()
	{
		if(isset($_POST['action']) && $_POST['action']==$this->plugin_slug_u .'_all')
		{
			if( empty( $_POST["_wpnonce"] ) || !wp_verify_nonce( $_POST["_wpnonce"], "Puvox_BackendCallJS") ) 
			{
				exit( __('Incorrect nonce. Refresh page and try again.') );
			}

			if(isset($_POST['PRO_check_key'])){
				echo $this->license_status( sanitize_text_field($_POST['PRO_check_key']), "activate");
			}

			elseif(isset($_POST['PRO_save_results'])){

			}

			elseif(method_exists($this, 'backend_call')){
				$this->backend_call( sanitize_key($_POST['act']) );
			}

			wp_die();
		}
		exit( __('Unknown-action') );
	}
	
	
	public function reset_plugin_to_defaults()
	{
		$this->update_opts([]) ;
		$this->update_phrases([]) ;
		if(property_exists($this, 'plugin_reset_callback'))   $this->plugin_reset_callback();
	}

	//update library file on activation/update
	public function plugin_updated_hook($redirect=false)
	{
		return;	
	}
	
	// quick method to update this plugin's opts
	public function optName($optname, $prefix=false){
		if( substr($optname,  0, 1) == '`'  ) {  $prefix=true;  $optname= substr($optname,1); }
		return ( !$prefix || stripos($optname, $this->slug) !== false )  ? $optname :  $this->slug . '_' . $optname;
	}

	public function update_opts($opts=false){
		return $this->update_option_CHOSEN($this->slug, ( $opts!==false ? $opts : $this->opts) );
	}

	public function get_option_CHOSEN($optname, $default=false        				, $prefix=false){
		return call_user_func("get_".		( $this->network_managed ? "site_" : "" ). "option",  $this->optName($optname, $prefix), $default );
	}
	public function update_option_CHOSEN($optname, $optvalue, $autoload=null		, $prefix=false){
		return call_user_func("update_".	( $this->network_managed ? "site_" : "" ). "option",  $this->optName($optname, $prefix), $optvalue, $autoload );
	}
	public function delete_option_CHOSEN($optname									, $prefix=false){
		return call_user_func("delete_".	( $this->network_managed ? "site_" : "" ). "option",  $this->optName($optname, $prefix) );
	}

	public function get_transient_CHOSEN($optname, $default=false        			, $prefix=false){
		return call_user_func("get_".		( $this->network_managed ? "site_" : "" ). "transient",  $this->optName($optname, $prefix), $default );
	}
	public function update_transient_CHOSEN($optname, $optvalue, $autoload=null		, $prefix=false){
		return call_user_func("set_".		( $this->network_managed ? "site_" : "" ). "transient",  $this->optName($optname, $prefix), $optvalue, $autoload );
	}
	public function delete_transient_CHOSEN($optname								, $prefix=false){
		return call_user_func("delete_".	( $this->network_managed ? "site_" : "" ). "transient",  $this->optName($optname, $prefix) );
	}
	
	public function delete_transients_by_prefix_CHOSEN($myPrefix					, $prefix=false){
		global $wpdb;
		$table_name		= $this->network_managed ? $wpdb->base_prefix.'sitemeta' : $wpdb->prefix .'options' ;
		$column_name	= $this->network_managed ? 'meta_key' : 'option_name';
		return $this->helpers->delete_transients_by_prefix($myPrefix, $table_name, $column_name);
	}
	
	public function get_prefix_CHOSEN(){
		return ($this->network_managed ? $GLOBALS['wpdb']->base_prefix : $GLOBALS['wpdb']->prefix);
	}

	public function IsNetworkwidePlugin() { return $this->static_settings['default_managed']=='network'; }
	
	public function IsNetworkManaged(){
		return get_site_option( $this->slug . '_network_managed', $this->IsNetworkwidePlugin() );
	}

	public function updateNetworkedState($value){
		return update_site_option( $this->slug . '_network_managed', $value );
	}
	
	public function phrase($key, $is_variable=false) {
		if($is_variable){
			if (!isset($this->translation_phrases[$key])){
				$this->translation_phrases[sanitize_title($key)] = sanitize_title($key);
				$this->update_phrases();
			}
		}
		return ( isset($this->translation_phrases[$key]) ? $this->translation_phrases[$key] : $key ); 
	}
	

	public function is_this_settings_page(){
	  return 
	  (
		is_admin() && 
		(
			( stripos(get_current_screen()->base, $this->slug) !== false)  &&  (isset($_GET['page']) && $_GET['page']==$this->slug ) 
			//	||
			//( stripos($this->helpers->currentURL, $this->optionsPageSlug) !==false )
		)
	  );
	}
		
	// navigation menu nav menu hooks: pastebin_com/BcGsVpe9

	// if post_exists query: https://goo.gl/aHZzv9


	public function create_log_table()
	{
		return $GLOBALS['wpdb']->query("CREATE TABLE IF NOT EXISTS `". $this->logs_table_name ."` (
				`id` int(50) NOT NULL AUTO_INCREMENT,
				`gmdate` datetime, 
				`function_args` longtext NOT NULL,
				`message` longtext NOT NULL, 
				`backtrace` longtext NOT NULL, 
				PRIMARY KEY (`id`),
				UNIQUE KEY `id` (`id`)
			)  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci AUTO_INCREMENT=1" 
			//	)  " . $wpdb->get_charset_collate()   || DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci AUTO_INCREMENT=1
		);  // or die("error_2345_". $wpdb->print_error())	//CHARACTER SET utf8mb4
	}


	//i.e. $this->log("couldnt get results", '<code>'.print_r($response, true).'</code>' );
	public function log( $message ="", $exception="", $retrying=false)
	{	global $wpdb; 
		$this->trim_errorslog();
		$trace=debug_backtrace(); array_shift($trace); $last_func = $trace[0];//$trace=array_splice($trace, 0, 6); //get only first 6 functions 
		$chain=""; foreach($trace as $e) {$chain=$e['function']."->".$chain;} 
		$res = $wpdb->insert( $this->logs_table_name, $arr=[ 
			'gmdate'=> gmdate("Y-m-d H:i:s"), 
			'function_args'=>  $chain ." :: ". print_r($last_func['args'],true), 
			'message'=>print_r($message, true). "\r\n".print_r($exception, true)] 
		);
		if(!$res && !$retrying){
			$this->create_log_table();
			$this->log( $message, $exception, true); 
		}
		return $res;
	}

	public function clear_errorslog(){ return $GLOBALS['wpdb']->query("TRUNCATE TABLE ".$this->logs_table_name );	} 
	public function get_errorslog()	{  return $GLOBALS['wpdb']->get_results("SELECT * from ".$this->logs_table_name."");	}

	// Removes  oldest rows if rows count exceeds the limit
	public function trim_errorslog()
	{ 
		$rows_amount = $GLOBALS['wpdb']->query("SELECT COUNT(*) FROM ".$this->logs_table_name ." GROUP BY `id`");
		if( $rows_amount > $this->logs_table_maxnum )	
		{
			$amount_to_delete=$rows_amount - $this->logs_table_maxnum;
			return $GLOBALS['wpdb']->query("DELETE FROM ". $this->logs_table_name. " WHERE 1=1 LIMIT " . $amount_to_delete ); 
		}
		return null;
	}  

	public function send_error_mail($error){
		return wp_mail($this->static_settings['mail_errors'], 'wp plugin error at '. home_url(),  (is_array($error) ? print_r($error, true) : $error)  );
	}
	
		// unique func to flush rewrite rules when needed. if not hooked into wp_footer, hangs plugin options resaving 
	public function flush_rules_if_needed($temp_key=false){
		// lets check if refresh needed
		$key="b".get_current_blog_id()."_". md5(    (empty($temp_key) ?  "sample" : ( stripos($temp_key, basename($this->helpers->moduleDIR)) !== false ? md5(filemtime($temp_key)) : $temp_key ))    );
		if( !array_key_exists($key, $this->opts['last_updates']) || $this->opts['last_updates'][$key] < $this->opts['last_update_time']){
			$this->opts['last_updates'][$key] = $this->opts['last_update_time'];
			$this->update_opts();
			add_action('wp_footer', function(){ $this->helpers->flush_rules("js"); } );
		}
	}
	
	public function flush_rules_checkmark($redirect=false){
		flush_rewrite_rules();
		$this->opts['needs_flushing'] = true; $this->update_opts();
		if($redirect) {
			if ($redirect=="js"){ $this->helpers->js_redirect(); }   else { $this->helpers->php_redirect(); }
		}
	}
	public function flush_checkpoint(){
		if(isset($this->opts['needs_flushing']))
		{
			unset($this->opts['needs_flushing']);
			$this->update_opts();
			$this->helpers->flush_rules(true);
		}
	}
	
	
	public function shortcodes_initialize(){
		if(property_exists($this,'shortcodes'))
		{
			//enable shortcodes (if it's disabled)
			add_filter( 'widget_text', 'do_shortcode' );

			foreach($this->shortcodes as $name=>$val)
			{
				//add "name" manually as name
				$this->shortcodes[$name]['name']=$name;
				add_shortcode($name, [$this, $name]);
			}
		}
	}
	
	public function init__disableupdate( $value ) { add_filter( 'site_transient_update_plugins', [$this, 'disable_plugin_update'] ); }
	public function disable_plugin_update( $value ) {

		$pluginsToDisable = plugin_basename($this->moduleFILE); //  [ 'plugin-folder/plugin.php' ];
		if ( isset($value) && is_object($value) ) {
			foreach ($pluginsToDisable as $plugin) {
				if ( isset( $value->response[$plugin] ) ) {
					unset( $value->response[$plugin] );
				}
			}
		}
		return $value;
	}
			


		

	// =================================================================================================== //
	// ============================================ SOCIAL LINKS ========================================= //
	// =================================================================================================== //

	public function socialss_Dataa(){
		$x = get_option('my_socials_optsszz');
		if (empty($x['sitesList']))	{ $x['sitesList']	= array('facebook','youtube','googleplus','twitter','pinterest','deviantart','mailsubscribe'); }
		if (empty($x['langzz']))	{ $x['langzz']		= array('geo','eng','rus'); }
		if (empty($x['Datas']))		{ $x['Datas']		= array('facebook_ENABLED__geo'=>1,'facebook_URL__geo'=>'https://facebook.com','facebook_TITLE__geo'=>'Like Our Facebook');}
		return $x;
	} 

	public function funccc413() {
		//add_menu_page('sample_page', 'sample_page', 'administrator','smpl_pggg', 'fnc34252');
		add_submenu_page('options-general.php' , 'My SOCIAL LINKS', 'SOCIAL LINKS', 'manage_options', 'mysubpage-slug133', 'fnc34252' ); } public function fnc34252() 	{
			
		//if updated
		if (isset($_POST['securit_noncee'])){    $this->NonceCheckk(sanitize_text_field($_POST['securit_noncee']),'fupd_mlss');
			$ARRAY=socialss_Dataa();
			$ARRAY['langzz']	= explode(",",  sanitize_text_field($_POST['langzz']));
			foreach ($_POST as $name=>$value) {$ARRAY['Datas'][$name]=sanitize_text_field($value);}
			update_option('my_socials_optsszz', $ARRAY );
		}
		//get the latest
		$ARRAY=socialss_Dataa();

		?>
		<form action="" method="POST" class="news_meetinggs">
			<?php 	//wp_editor( htmlspecialchars_decode(get_option('nwsMTNG_notes_'.$laang)), 'mtng_notes_styl_ID'. $laang, $settings = array('textarea_name'=>'nwsMTNG_notes_'. $laang,  'editor_class' => "editoor_nws_note")); ?>
			<h2>Social Links</h2>
			Your site Languages(initials): <input type="text" value="<?php echo implode(",",  $ARRAY['langzz']);?>" name="langzz" placeholder="geo,eng,rus.." />
			</br><br/>
			<table border=1><tbody>
				<tr><?php foreach($ARRAY['sitesList'] as $e) {echo '<td style="background-color:#FFEAEF; text-align:center; font-weight:bold;"><h2>'.$e.'</h2></td>';} ?></tr>
				<?php foreach($ARRAY['langzz'] as $each_lang) { ?>
				<tr><td colspan="4" style="border:none;"><h3 style="margin: 2em 0px 0;"><?php echo $each_lang;?> </h3></td> </tr>
				<tr>
					<?php foreach ($ARRAY['sitesList'] as $each_social) { 
					$enabl	= $each_social.'_ENABLED__'.$each_lang;
					$url	= $each_social.'_URL__'.$each_lang;
					$title	= $each_social.'_TITLE__'.$each_lang;
					?>
					<td>
						<span class="description">(Enabled:
						<input name="<?php echo $enabl;?>"	type="checkbox" value="1" <?php if (Return_If_Array_Key($ARRAY['Datas'],$enabl)) {echo 'checked="checked"';} ;?> />)
						</span>
						<input name="<?php echo $url;?>"	type="text"		value="<?php echo Return_If_Array_Key($ARRAY['Datas'],$url); ?>"	placeholder="https://..." class="regular-text" />
						<input name="<?php echo $title;?>"	type="text"		value="<?php echo Return_If_Array_Key($ARRAY['Datas'],$title); ?>"	placeholder="Title here..." class="regular-text" />
					</td>
					<?php } ?>
				</tr>
				<?php } ?>
			</tbody></table>
			
			<div class="my_save_divv" style="text-align:center; position:fixed; bottom:20px; left:40%; padding:10px; background-color: red;"><input type="submit" class="my_SUBMITT" value="SAVE" /></div> <input type="hidden" name="securit_noncee" value="<?php echo wp_create_nonce('fupd_mlss'); ?>" />
		</form>
		<?php 
	}

	public function Output_socials($lang=''){ $opts = socialss_Dataa();
		if (empty($lang)) {$lngs = array_filter($opts['langzz']); $lang= $lngs[0]; }
		$output	='<div class="socialss">';
			foreach ($opts['sitesList'] as $key=>$each_social){
				$enbled = 	(Return_If_Array_Key($opts['Datas'],$each_social.'_ENABLED__'.$lang)) ? 	true: false;
				if ($enbled){
					$output .= '<a class="sc_a" href="'.Return_If_Array_Key($opts['Datas'],$each_social.'_URL__'.$lang).'" target="_blank"><div class="eachd '.$each_social.'"><div class="sLogo"></div><div class="sPhraze">'.Return_If_Array_Key($opts['Datas'],$each_social.'_TITLE__'.$lang).'</div></div></a>';
				}
			}
		$output .='</div>';
		return $output;
	}
	// ============================================ ## SOCIAL LINKS ========================================= //






	public function get_phrases()
	{
		return $this->get_option_CHOSEN('`translated_phrases', []);
	}

	public function update_phrases($array=null)
	{
		if(!isset($array)) $array=$this->translation_phrases;
		return $this->update_option_CHOSEN('`translated_phrases', $array);
	}
	
	public function phrases_array()
	{ 
		$cont='';
		foreach( $this->plugin_files as $each)
		{
			$cont .= $this->file_get_contents($this->helpers->moduleDIR.'/'.$each);
		}
		preg_match_all( '/\$this\-\>phrase\((.*?)\)/si', $cont, $matches );
		$phrases_array = $this->get_phrases();
		foreach($matches[1] as $value) {
			$value=trim($value);
			//if not variable
			if(substr($value, 0, 1) != '$')
			{
				$sanitized_value = preg_replace("/[\"\']/", "", $value);
				$phrases_array[$sanitized_value] = $sanitized_value;
			}
		}
		return $phrases_array;
	}

	
	public function question_mark($text, $dialog=0, $question_mark="") { 
		$mouseover='';
		$content = '';
		if($dialog==0){
			$content = $text;
		}
		else if($dialog==1){
			$content = '';
			$mouseover = ' onmouseover="jQuery(\'#\'+this.parentNode.id).tooltip({ items:this,   content:\''.$text.'\', show: { effect: \'blind\', duration: 800 } 	}).tooltip(\'open\');"'; 	
		}
		else if($dialog==2){
			$content = '';
			$mouseover = ' onmouseover="jQuery(\'<div>'.$text.'</div>\').dialog({   modal:true,   width:600 });"';
		}
		if (empty($question_mark)) $question_mark=$this->static_settings['question_mark_icon'];
		return '<span id="xx"><img src="'. $question_mark .'" class="question_mark" style="cursor:none; width:20px;" alt="'.$content.'" title="'.$content.'" '.$mouseover.' /></span>';
	}

	public function define_translations_exist(){
		//check if translations exist
		$last_vers  = get_site_option($this->slug . '_transl_lastvers');
		if( ! $last_vers || $last_vers != $this->static_settings["Version"] ){
			update_site_option('transl_lastvers', $this->static_settings["Version"]);
			$res = !empty($this->phrases_array());
			update_site_option($this->slug . '_transl_exists', $res);
			return $res;
		}
		return get_site_option($this->slug . '_transl_exists');
	}	


	// settings page
	public function define_option_tabs(){
		if(!is_admin()) return;
		
		$this->show_tabs	= $this->static_settings['display_tabs'];
		$this->options_tabs	= array_merge( 
			["Options"],
			$this->options_tabs, 
			property_exists($this,'shortcodes') ? ['Shortcodes'] : [],
			( $this->define_translations_exist() ? ["Translations & Phrases"] : [] ),
			["Errors-Log & Reset"]  //( ! property_exists($this, 'errors_tab') || $this->errors_tab ? ['Errors log'] :  []  )
		); 

	}

	public function options_tab($tabs_array =false){
		if(!$tabs_array)  $tabs_array = $this->options_tabs;
		$this->active_tab = $tabs_array[0];
		foreach($tabs_array as $each_tab){
			if (isset($_GET['tab']) && sanitize_key($each_tab)==$_GET['tab'])  
				$this->active_tab=$each_tab;
		}
		echo '<div class="nav-tab-wrapper customNav '. (!$this->show_tabs ? "displaynone" : "") .'">';
		foreach($tabs_array as $each_tab){
			$tab_TITLE = $each_tab=="Shortcodes" ? "Shortcodes & Api" : $each_tab;
			echo '<a  href="'.add_query_arg('tab', sanitize_key($each_tab) ).'" class="nav-tab '. sanitize_key($each_tab).' '. ($this->active_tab == $each_tab ? 'nav-tab-active  whiteback' : ''). '">'. __( $tab_TITLE).'</a>';
		}
		echo '</div>';
	}


	public function opts_page_output_parent($args=false)
	{
		if(is_network_admin())
		{
			if( !empty( $_POST["mng_nonce"] ) && check_admin_referer( "nonce_mng_" . $this->slug, "mng_nonce" ) ) 
			{
				if(isset( $_POST[$this->slug]['managed_from_changer'] ) ){
					$val = in_array($_POST[$this->slug]['managed_from_site'], ['Network-Wide','network']) ;
					$this->network_managed = $val;
					$this->updateNetworkedState($val);
					$this->helpers->js_redirect();
				}
			}
			?>
			<style>
			.networked_switcher_itsf_parent {position:relative;}
			.networked_switcher_itsf { box-shadow: 0px 0px 15px #329c1e; z-index: 3; min-width:210px; background:#ffa637; border-radius: 0 10px 0 190px;  border: 2px solid #80808094; padding:2px 10px 4px 30px; position: fixed; top: 32px; right: 0px;  line-height:1.2em; text-align: center; z-index: 77; }
			.networked_switcher_itsf .modeChooser1 { display: flex; flex-direction: row; justify-content: center; align-items: center; }
			@media all and (max-width: 850px) { .networked_switcher_itsf .modeChooser1 { flex-wrap:wrap;}  }
			.networked_switcher_itsf label { padding-right: 20px!important;  border-radius: 0px!important; margin: 2px !important;}
			.networked_switcher_itsf label.networkwide { border-radius: 100px 0px 0px 100px!important;}
			.networked_switcher_itsf label.subsite { border-radius: 0px 100px 100px 0!important;}
			.networked_switcher_itsf input[type="radio"] { margin:2px 0 0 -20px ;}
			</style>
			<div class="networked_switcher_itsf_parent">
				<div class="networked_switcher_itsf">
					<form method="POST" action="" name="networked_switcher_form">
					<?php _e("You can change from where you'd like to manage this plugin's settings page"); ?></b>
					<div class="modeChooser1">
				
						<label for="networkwide" class="networkwide button<?php echo $this->network_managed ? "-primary":"";?> "><?php _e("Network dashboard (controls all subsites)");?></label>
						<input id="networkwide" onchange="managed_from_onchanger(this)" type="radio" name="<?php echo $this->slug;?>[managed_from_site]" value="network" <?php checked($this->network_managed);?> />
						<label for="subsite" class="subsite button<?php echo !$this->network_managed ? "-primary":"";?>"><?php _e("Each Sub-Site with own settings page");?></label>
						<input id="subsite" onchange="managed_from_onchanger(this)"  type="radio" name="<?php echo $this->slug;?>[managed_from_site]" value="subsite" <?php checked(!$this->network_managed);?>  />
						<!--					
						<input type="submit" name="<?php echo $this->slug;?>[managed_from_site]" value="<?php _e("Network-Wide");?>" class="button<?php echo $this->network_managed ? "-primary":"";?>" />
						<input type="submit" name="<?php echo $this->slug;?>[managed_from_site]" value="<?php _e("Per sub-site, individually");?>" class="button<?php echo !$this->network_managed? "-primary":"";?>" />
						-->
						<input type="hidden" name="<?php echo $this->slug;?>[managed_from_changer]" value="ok" />  
						<?php wp_nonce_field( "nonce_mng_".$this->slug, "mng_nonce" ); ?>
					</div>
					</form>
					<script>
					//jQuery(".modeChooser1").controlgroup();
					function managed_from_onchanger(e)
					{
						document.forms["networked_switcher_form"].submit();
						document.getElementById("wpbody").style.opacity = 0.1;
					}
					</script>
				</div>
			</div>
		<?php 
		}

		if ( (is_network_admin() && $this->network_managed) || (!is_network_admin() && !$this->network_managed) ){
			$this->opts_page_output();
		}
		else{
			echo '<div style="display: flex; background: white; flex-direction: column; max-width: 600px; margin: 100px auto; border-radius: 10px; padding: 30px;"><h1>'.__('Plugin is set to be managed per: <span class="perChosen">'. ($this->network_managed ? "Network": "Sub-sites") ).'</span></h1></div>';
		}
	}
	

	public function settings_page_part($type)
	{
		$this->is_settings_page = true;

		if($type=="start")
		{
			if( !empty( $_POST["_wpnonce"] ) && check_admin_referer( "nonce_" . $this->slug, "_wpnonce" ) ) 
			{
				if(!empty($_POST[$this->slug]) ) {
					$this->opts['last_update_time'] = time();
					$this->update_opts();
				}
				
				if(isset( $_POST[$this->slug]['clear_error_logs'] ) ){
					$this->clear_errorslog();
				}

				if(isset( $_POST[$this->slug]['reset_plugin_defaults'] ) ){
					$this->reset_plugin_to_defaults(); $this->helpers->js_redirect();
				}

				if(isset( $_POST[$this->slug]['update_transl_phrases'] ) ){
					$this->translation_phrases =  array_map('sanitize_text_field', $_POST[$this->slug]['translation_phrases']);
					$this->update_phrases( $this->translation_phrases ) ;
				}
			} 
			
				
			if( !empty( $_GET["_wpnonce"] ) && check_admin_referer( "nonce_" . $this->slug, "_wpnonce" ) ) 
			{
				if(isset($_GET[$this->slug.'-remove-pro']) ) {
					delete_site_option($this->license_keyname());
					$this->helpers->js_redirect(remove_query_arg($this->slug.'-remove-pro'));
				}
			}
			
			?>
			<div class="clear"></div>
			<div class="<?php echo $this->myplugin_class;?>">

				<h1 class="plugin-title"><?php echo $this->opts['name'];?></h1> 
				<?php $this->options_tab();  ?>
				<!-- <h2 class="settingsTitle"><?php _e('Plugin Settings Page!');?></h2> -->
				
					<div class="optwindow">
				<?php



				if ($this->active_tab == "Shortcodes")
				{ 
					echo '<h1 class="shortcodes_maintitle">'. __('Shortcodes Usage').'</h1>';
					
					foreach($this->shortcodes as $key=>$value)
					{
						$this->helpers->shortcodes_table($key, $value);
						$this->helpers->shortcode_alternative_message($key);
					}


					echo '<div class="hooks_examples">';
					echo '<h1 class="shortcodes_maintitle">'. __('Available hooks (to modify from external functions)') .'</h1>';
					if ( property_exists($this, "hooks_examples") ) {
						foreach ($this->hooks_examples as $key=>$block){
							echo '<div class="hook_example_block '.$key.'">';
							if ($block['type']=='filter'){
								echo '<div class="description">'. __($block['description']) .':</div>';
								echo '<code>add_filter("'.$key.'", "yourFunc", 10, '. count($block['parameters'] ) .' );     function yourFunc($'. implode(', $', $block['parameters'] ).') { ... return $'.$block['parameters'][0].';} </code>'; 
							}
							echo '</div>';
						}
					}
					echo '</div>';
				}



				if ($this->active_tab == "Translations & Phrases")
				{ ?>
					<div class="translations_page">
						<form method="post" action="">
							<?php _e("Here will show up all phrases that are outputed on your site fron-end by this plugin, so you can translate/customize them."); ?>
							<table class="translations_table">
								<tbody>
									<?php 
									$phrases_arr = $this->phrases_array();
									$phrases = $this->translation_phrases;
									if(is_array($phrases_arr)){
										foreach ($phrases_arr as $key=>$value){
											$value = array_key_exists($key, $phrases) ? $phrases[$key] : $key;
											echo '<tr>';
											echo '<td>'. $key.'</td><td><input type="text" name="'.$this->slug.'[translation_phrases]['.$key.']" value="'. $value .'" /></td>';
											echo '</tr>';
										}
									}
									?>
								</tbody>
							</table>
							<input type="hidden" name="<?php echo $this->slug;?>[update_transl_phrases]" value="ok" />
							<?php
							wp_nonce_field( "nonce_".$this->slug);
							submit_button(  __( 'Save' ), 'button-secondary', '', true  );
							?>
						</form>
					</div>
				<?php
				}



				if ($this->active_tab == "Errors-Log & Reset")
				{ ?>
					<div class="errors_page">
						<div class="errors_table_container">
							<table class="errors_log">
								<style>
								.myplugin .errors_page .errors_table_container { max-height: 400px;  overflow-y: scroll;  border: 1px solid #b5b5b5;}
								.myplugin .errors_page table {border-collapse: collapse;}
								.myplugin .errors_page table tr > * { border: 1px solid #c7c7c7; padding: 3px 5px; }
								.myplugin .errors_page .errors_log tr{transition:0.2s all;}
								.myplugin .errors_page .errors_log tr.headerRow{color:orange;}
								.myplugin .errors_page .errors_log tr:hover{background:#fdf7f7;}
								.myplugin .errors_page .errors_log td{min-width: 10px;} 
								.myplugin .errors_page .errors_log td:nth-child(1){max-width:80px;}
								.myplugin .errors_page .errors_log td:nth-child(2){min-width:80px; max-width:120px;}
								.myplugin .errors_page .errors_log td:nth-child(3){max-width:150px;}
								.myplugin .errors_page .errors_log td pre { white-space: pre-wrap; word-wrap: break-word; }
								</style>
								<tbody>
									<?php
									//$this->log("asdddd", "");  
									$errors = $this->get_errorslog();
									if(!empty($errors))
									{
										rsort($errors);  //reverse order, last added to top
										$column_count =  count( $keys = array_keys( ((array)$errors[0]) )); 
										echo '<tr class="headerRow">'; for($i=0; $i<$column_count; $i++) echo "<td>$keys[$i]</td>";echo '</tr>'; 

										foreach ($errors as $each_err ) {
											$each_err= (array) $each_err;
											echo '<tr>';
											for($i=0; $i<$column_count; $i++){
												$out='';
												$current = $each_err[ array_keys($each_err)[$i]];
												if (!empty($current) )
												{
													$out = $current;
												}
												echo '<td><pre>'. htmlentities($out).'</pre></td>';
											}
											echo '</tr>';
										}

									}
									?>
								</tbody>
							</table>
						</div>
						

						<div class="clear-errors-log">
						<form method="post" action="">
							<input type="hidden" name="<?php echo $this->slug;?>[clear_error_logs]" value="ok" />
							<?php
							wp_nonce_field( "nonce_".$this->slug);
							submit_button(  __( 'Clear Errors Log' ), 'button-secondary red-button', '', true  );
							?>
						</form>
						</div>


						<div class="plugin-reset-defaults">
						<form method="post" action="">
							<input type="hidden" name="<?php echo $this->slug;?>[reset_plugin_defaults]" value="ok" />
							<?php
							wp_nonce_field( "nonce_".$this->slug);
							submit_button(  __( 'Reset plugin options to defaults' ), 'button-secondary red-button', '', true  );
							?>
						</form>
						</div>
					</div>
				<?php
				}
		}

		
		elseif ($type=="end")
		{ ?>
				</div><!-- optwindow -->
				<?php $this->endStyles();?>
			</div><!-- myplugin -->
		<?php
		}
	}





	
	public function endStyles($external=false)
	{ ?>
		<?php 
		if ($external===false) {
			//
		}
		elseif ($external===true) {
			echo '<div class="'.$this->myplugin_class.'">'; 
		}
		?>

		<style>
		.myplugin { margin: 20px 20px 0 0; line-height:1.2;  max-width:100%; display:flex; flex-wrap:rap; justify-content:center; flex-direction:column; padding: 20px; border-radius: 100px; }
		.myplugin * { position:relative;}
		.myplugin code {font-weight:bold; padding: 3px 5px;  display: inline-block;}
		.myplugin >h2 {text-align:center;}
		.myplugin h1,
		.myplugin h2,
		.myplugin h3 {text-align:center; margin: 0.5em 1em 1em;}
		.myplugin table tr { border-bottom: 1px solid #cacaca; }
		.myplugin table td {min-width:50px;}
		.myplugin .form-table  { border: 1px solid #cacaca; padding:2px;  }
		.myplugin .form-table td { padding: 15px 5px;  }
		.myplugin .form-table th { padding: 20px 10px 20px 10px; } 
		.myplugin p.submit {text-align: center;}
		.myplugin .optwindow { border: 1px solid #b5b5b56e;  padding: 10px; border-width: 0px 1px 1px 1px; border-radius: 0px 0px 30px 30px; }
		zz.myplugin input[type="text"]{width:100%;}
		.myplugin .additionals{ display:flex;  font-family: initial; font-size: 1.5em;   text-align:center; margin: 25px 5px 5px; padding: 5px; background: #efeab7;  padding: 5px 0 0 20px;  border-radius: 0% 20px 140px 90%; }
		z.myplugin .additionals:before { content: ""; position: absolute; top: 5%; left: 5%; height: 90%; width: 90%; background: #a222ff61; border-radius: 60% 60% 770% 110px;opacity: 0.6; transform: rotatez(-2deg); }
		.myplugin .additionals:after { content: ""; position: absolute; top: 5%; left: 5%; width: 90%; background: #6bd5ff45; border-radius: 10% 40% 20% 110px; opacity: 0.6; transform: rotatez(3deg); z-index: 0; height: 100px; }
		.myplugin .additionals a{font-weight:bold;font-size:1.1em; color:blue;}
		.myplugin .in_additional { z-index:3; width: 700px; background: #ffffff00; box-shadow: 0px 0px 20px #7d7474; border-radius: 30px; padding: 11px; margin: 0 auto; margin: 20px auto; }
		z.myplugin .additionals li { list-style-type: circle; list-style-type: circle; float: left; margin: 5px 0 5px 40px;}
		.myplugin .whiteback { background:white; border-bottom:1px solid white; }
		.myplugin.version_pro .donations_block, .myplugin.version_not_pro .donations_block { display:none; }
		.myplugin .donation_li a{  color: #d47b09; }
		.myplugin .customNav {margin: 0 0 0 0;}
		.myplugin .customNav .errors-logreset{ color: #903e4c; font-size: 0.7em; font-style: italic; opacity:  0.6;  float:right;}
		.myplugin .customNav .nav-tab{ border-radius: 60% 30% 5% 0px; }
		.myplugin .customNav .nav-tab-active{ color: #43ceb5; pointer-events: none; }
		.myplugin .freelancers {font-style: italic; font-family: arial; font-size: 0.9em; margin: 15px; padding: 10px; border-radius: 5px; opacity: 0.7; }
		.myplugin .freelancers a{}
		.myplugin .button { top: -4px; }
		.myplugin .red-button { background: #ec5d5d;   zbackground:  #ffdfdf;}
		.myplugin .pink-button { background: pink; }
		.myplugin .green-button { background: #44d090; }
		.myplugin .float-left { float:left; }
		.myplugin .float-right { float:right; }
		.myplugin .displaynone { display:none; }
		.myplugin .clearboth { clear:both;  height: 20px;  }
		.myplugin .noinput { border: none!important; box-shadow:none!important; width:auto!important; display:inline-block!important; font-weight:bold; }
		.myplugin .translations_table { margin: 20px 0 0 30px; border-collapse: collapse;}
		xxx.ui-widget-overlay { background: #000000; opacity: 0.8; filter: Alpha(Opacity=80); }
		xxx.ui-dialog {z-index: 9222!important; }
		.myplugin .alertnative_to_shortcodes {margin:50px 10px; box-shadow: 0px 0px 20px grey; padding: 40px; }

		.myplugin .hook_example_block { margin: 10px 0; line-height: 1.4em; }
		.myplugin a,.myplugin a.button { display: inline; }
		.myplugin .disabled { pointer-events: none; }
		.myplugin .nonclickable { pointer-events: none; }

		.myplugin .review_block{ float:right; }
		.myplugin .review_block a{ float:right; font-size:20px; font-weight:bold; }
		.myplugin .review_block .stars{ height:30px; }
		.myplugin .review_block span.leaverating {position:absolute; z-index:4; margin:0 auto; text-align:center; width:auto; white-space:nowrap; top:15px; color:#000000de; font-size:0.8em; left:20px; text-shadow:0px 0px 25px black;}
		.myplugin .review_block img.stars{ height:30px; vertical-align:middle; }

		.myplugin .shortcode_atts{ color:#b900f3; }
		.myplugin .shortcodes_maintitle { font-style:italic; }
		.myplugin table .shortcode_tr_descr { font-weight:bold; color:black; }
		.myplugin .site_author_block{ text-align:center; font-size:0.8em; }
		.myplugin .site_author_block a{ text-decoration:none; color:black;}
		.myplugin .shortcodes_block { box-shadow: 0px 0px 15px #00000066; padding: 10px 0 0 0; margin: 20px 0;}
		.myplugin .shortcodes_block z.h3{ color:#f34500; text-align:center; }

		.myplugin .datachange-save-button{ display:none; }
		.myplugin ._save_button{ display:none; }
		.myplugin .numeric_input{ width:50px; font-weight:bold;}
		.myplugin .form-table td { vertical-align: top; }
		
		/* ---- jquery ui  ( https://github.com/jquery/jquery-ui/tree/master/themes/base )----- */
		.ui-widget.ui-widget-content { border: 1px solid #c5c5c5; }
		.ui-corner-all { border-radius: 3px; }
		.ui-widget-header { border: 1px solid #dddddd; background: #e9e9e9; color: #333333; font-weight: bold; }
		
		.ui-dialog { padding: .2em; }
		
		.ui-tooltip {	padding: 8px;	position: absolute;	z-index: 9999;	max-width: 300px;}
		body .ui-tooltip {	border-width: 2px; border:1px solid #e7e7e7; box-shadow:0px 0px 3px gray; }
		</style>

	<div class="newBlock additionals">
		<div class="in_additional">
			<h4></h4>
			<h3><?php _e('More Actions');?></h3>
			<ul class="donations_block">
				<li class="donation_li">
					<?php _e('If you found this plugin useful, any donation is welcomed');?> : 
					$<input id="donate_pt" type="number" class="numeric_input" value="3" /> <button onclick="tt_donate_trigger();"/><?php _e('Donate');?></button>
					<script>
					function tt_donate_trigger()
					{
						var url= '<?php echo $this->static_settings['donate_url'];?>';
						window.open(url + '/'+ document.getElementById('donate_pt').value,'_blank');
					}
					</script>
					<!-- <a href="%s" class="button" target="_blank">donation</a> -->
				</li>
			</ul>
			<ul>
				<li>
					<?php //printf(__('You can check other useful plugins at: <a href="%s">Must have free plugins for everyone</a>'),  $this->static_settings['musthave_plugins'] ).'.';	?>
				</li>
			</ul>
			<ul class="freelancers">
				<li>
					<?php //printf(__('To hire a qualified WordPress specialist, you can use:<br/><a target="_blank" href="%s">TopTal.com</a>, <a target="_blank" href="%s">FreeLancer.com</a> or <a target="_blank" href="%s">PeoplePerHour.com</a> '),  $this->static_settings['wp_tt_freelancers'],  $this->static_settings['wp_fl_freelancers'], $this->static_settings['wp_pph_freelancers']  );  ?>
				</li>
			</ul>
		</div>



		<?php if($this->static_settings['show_rating_message']) 
		{ ?>
		<div class="review_block">
			<a class="review_leave" href="<?php echo $this->static_settings['wp_rate_url'];?>" target="_blank">
				<span class="leaverating"><?php _e('Rate plugin');?></span>
				<img class="stars" src="<?php echo $this->static_settings['public_assets_url'];?>/assets/rating-transparent-shaded.png" />
			</a>
		</div>
		<?php
		}
		?>

	</div>

	<div class="clear"></div>
	<script> tt_ajax_action = '<?php echo $this->plugin_slug_u;?>_all';</script>
	
	<script>
	function pro_field(targetEl){
		var is_pro = <?php echo $this->unregistered_pro() ? "true" : "false";?>; 
		if(is_pro) {
			targetEl.attr("data-pro-overlay","pro_overlay");
		}
	}
	</script> 

	<?php 
	$this->purchase_pro_block();  
	?>

	
	<!-- Show "SAVE" button after input change,  type="text" id="manual_pma_login_url" data-onchange-save="true"  data-onchange-hide=".type_manual" name=" -->
	<div class="datachange-save-button">
		<?php submit_button( false, 'button-primary _save_button', '', true,  $attrib=  ['id' => '_save_button'] ); ?> 

		<script>
		(function($){ 

			// save button show/hide
			var save_button=$('.myplugin #_save_button');
			$('.myplugin [data-onchange-save]').on("change, input", function(e){
				save_button.insertAfter( $(this) );
				save_button.show();
				save_button.css( { 'margin-left': "-"+(save_button.css("width")), 'position':'relative', 'top':'0px', 'left':save_button.css("width")  });
				var target=$(this).attr("data-onchange-hide"); if(target && target.length) {   $( target ).css("visibility","hidden");   }
			});

			//noinput types
			if($(".noinput").length) $(".noinput").attr('size', $(".noinput").val().length);
		})(jQuery); 
		</script> 
	</div>


	<?php if ($external===true) echo '</div>'; ?>
	<?php
	}
	 

	public function admin_scripts($hook)  //i.e. edit.php
	{
		if($this->is_this_settings_page()){
			$this->admin_scripts_out($hook);
		}
	}

	// https://github.com/WordPress/WordPress/blob/master/wp-includes/script-loader.php
	public function admin_scripts_out($hook)  //i.e. edit.php
	{
		$where='admin';
		$this->helpers->register_stylescript($where, 'script', 'jquery');

		//jquery ui core
		//$this->helpers->register_stylescript($where, 'script',	'jquery-ui-core');
		
		//jquery ui EFFECTS
		$this->helpers->register_stylescript($where, 'script',	'jquery-effects-core');

		//jquery ui DIALOG
		$this->helpers->register_stylescript($where, 'script',	'jquery-ui-dialog');
		$this->helpers->register_stylescript($where, 'style',	'wp-jquery-ui-dialog');	//	'ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',  false,  '1.1');
		
		$this->helpers->register_stylescript($where, 'script',	'jquery-ui-tooltip');
		
		// spin.js
		//$this->register_stylescript($where, 'script',	'spin', 'https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js',  ['jquery'],  '2.3.2', true);

		// touch-punch.js
		//$this->register_stylescript($where, 'script', 'touch-punch', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', ['jquery'],  '0.2.3', true );

		//add_action('admin_footer', function() { <script></script> } );
	}


	/*
	public public function admin_scripts(){
		
		// jquery ui
		$handle= 'jquery-effects-core';
		if(!wp_script_is( $handle, "enqueued" ) ){
			wp_enqueue_script( $handle);
		}
		
		// spin.js
		$handle= 'spin.js';
		if(!wp_script_is( $handle, "registered" ) ){
			wp_register_script( $handle, 'https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js' ,  array('jquery'), '2.3.2', true );
		}
		if(!wp_script_is( $handle, "enqueued" ) ){
			wp_enqueue_script( $handle);
		}

		// touch-punch.js
		$handle= 'touch-punch.js';
		if(!wp_script_is( $handle, "registered" ) ){
			wp_register_script( $handle, 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js' ,  array('jquery'),  '0.2.3', true );
		}
		if(!wp_script_is( $handle, "enqueued" ) ){
			wp_enqueue_script( $handle);
		}
		
		// animate.css
		$handle= 'animate.css';
		if(!wp_style_is( $handle, "registered" ) ){
			   wp_register_style( $handle, 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css', false, '1.0.0' );
		}
		if(!wp_script_is( $handle, "enqueued" ) ){
			wp_enqueue_style( $handle);
		}	
		$handle= 'animate.css.js';
		if(!wp_script_is( $handle, "registered" ) ){
			wp_register_script( $handle, 'https://cdnjs.cloudflare.com/ajax/libs/animateCSS/1.2.2/jquery.animatecss.min.js' ,  array('jquery'), '', true );
		}
		if(!wp_script_is( $handle, "enqueued" ) ){
			wp_enqueue_script( $handle);
		}	
		
	 
		

		// my_scripts.js
		$handle= 'my_scripts_tt.js';
		if(!wp_script_is( $handle, "registered" ) ){
			wp_register_script( $handle, plugin_dir_url(__FILE__) ,  array('jquery'), '1.1', true );
		}
		if(!wp_script_is( $handle, "enqueued" ) ){
			wp_enqueue_script( $handle);
		}
		
		
			
		<script>
		public function InitializeSpinner(){
		var opts = {
		  lines: 13, length: 38, width: 17, radius: 45, scale: 1, corners: 1,  color: '#333333', fadeColor: '#e7e7e7', opacity: 0.25, rotate: 0,  direction: 1, speed: 1,   trail: 60,  fps: 5,  zIndex: 2e9,  className: 'spinner', top: '50%',  left: '50%', shadow: '0px 0px 5px black',	position: 'fixed' 
		};
		my_spinner_sampleTT = new Spinner(opts).spin();
		}

		public function spin(action){
		if(typeof my_spinner_sampleTT == 'undefined'){
			InitializeSpinner();
		}
		if(action=="show"){
			$("body").append('<div id="blackground_spinner" style="top:0px; width:100%;height:100%;position:fixed;background:white; z-index:12; opacity:0.8; z-index: 9990;"></div>');
			$("body").append(my_spinner_sampleTT.el);
		}
		else if(action=="hide"){
			$("#blackground_spinner").remove();
			my_spinner_sampleTT.el.remove();
		}
		} 
		</script>



		<script>
		$('#your-id').animateCSS('fadeIn', {
		delay: 1000,
		callback: function(){
		console.log('Boom! Animation Complete');
		}
		});
		</script>
		}


	*/


	public function admin_head_func()
	{ 
		if( defined("ttLibrary_scripts_loaded") ) return;  define("ttLibrary_scripts_loaded", true); 
		?>
		<script>
		//window.onload REPLACEMENT
		// window.addEventListener ? window.addEventListener("load",yourFunction,false) : window.attachEvent && window.attachEvent("onload",yourFunction);
		ttLibrary =
		{
			// check for Ajax calls from front-end
			backend_call : function (data, callback)
			{
				data["action"] = tt_ajax_action;
				data["_wpnonce"] = '<?php echo wp_create_nonce( "Puvox_BackendCallJS");?>';
				ttLibrary.spinner(true);

				jQuery.post
				(
					ajaxurl,
					data,
					function(response){  ttLibrary.spinner(false);   callback(response); }
				);
			},

			reload_this_page : function(){
				window.location = window.location.href;
			},

			//show Spinner (loader-waiter)
			spinner: function(action)
			{
				var spinner_id = "tt_spinner";
				if(action)
				{
					var div=
					'<div id="'+spinner_id+'" style="background:black; position:fixed; height:100%; width:100%; opacity:0.9; z-index:9990;   display: flex; justify-content: center; align-items: center;">'+
						'<div style="">Please Wait...</div>'+
					'</div>';
					document.body.insertAdjacentHTML("afterbegin", div);
				}
				else
				{
					var el = document.getElementById(spinner_id);
					if (el) {
						el.parentNode.removeChild(el);
					}
				}
			},

			//shorthand for jQuery dialog
			dialog: function(message)
			{
				jQuery('<div class="ttDialog">'+message+'</div>').dialog({
					modal: true,
					width: 500,
					close: function (event, ui) {
						jQuery(this).remove();	// Remove it completely on close
					}
				});
			},
			//shortand for the same, to remember easily
			message: function(message)
			{
				return ttLibrary.dialog(message);
			},



			// make an element field to blink/animate
			blink_field : function (fieldObj)
			{
				fieldObj.animate({backgroundColor: '#00bb00'}, 'slow').animate({backgroundColor: '#FFFFFF'}, 'slow');
			},


			// hide content if chosen radio box not chosen  
			radiobox_onchange_hider : function (selector,desiredvalue, target_hidding_selector, SHOW_or_hide)
			{
				SHOW_or_hide = SHOW_or_hide || false;
				if( typeof dropdown_objs == 'undefined') { dropdown_objs = {}; } 
				if( typeof dropdown_objs[selector] == 'undefined' ){
					dropdown_objs[selector] = true ; var funcname= arguments.callee.name;
					//jQuery(selector).change(function() { window[funcname](selector,desiredvalue, target_hidding_selector, SHOW_or_hide);	});
					jQuery(selector).change(function() { ttLibrary.radiobox_onchange_hider(selector,desiredvalue, target_hidding_selector, SHOW_or_hide);	});
				}
				var x = jQuery(target_hidding_selector);
				if( jQuery(selector+':checked').val() == desiredvalue)	{ if(SHOW_or_hide)	x.show(); else x.hide(); } 
				else 													{ if(SHOW_or_hide)	x.hide(); else x.show(); }
			},

			// hide content if chosen radio box not chosen  
			checkbox_onchange_hider : function (selector, when_checked, destination_hidding_selector)
			{
				var x=function(target, destination){
					if(   (when_checked && jQuery(target).is(':checked'))  || (!when_checked && jQuery(target).not(':checked'))  ) {
						jQuery(destination).show();
					} else {
						jQuery(destination).hide();
					}
				};
				x(selector, destination_hidding_selector);
				jQuery(selector).click( function(e){ x(e.target, destination_hidding_selector); } ); 
			}
		};


		</script>
		<?php
	}
	
	//move uploaded addon to it's folder
	public function wp_handle_upload_filter( $array=['file' => 'path/to/wp-content/uploads/2018/12/example.ext', 'url'  => 'https://.....example.ext', 'type' => 'application/zip'],   $action= 'sideload|upload' )
	{
		$file = $array['file'];
		if($array['type']=="application/zip" || $array['type']=="application/x-zip")
		{
			$filename = basename($file);
			$found = false; 

			$found_files=[];
			if (function_exists('zip_open'))
			{
				$zip = zip_open($file);
				if (is_resource($zip))
				{
					while ($zip_entry = zip_read($zip))
					{
						$found_files[]=zip_entry_name($zip_entry);
						//if (zip_entry_open($zip, $zip_entry))
						//{
							//echo zip_entry_read($zip_entry);
							//zip_entry_close($zip_entry);
						//}
					}
					zip_close($zip);
				}
			}
			elseif (class_exists('\ZipArchive'))
			{
				$za = new ZipArchive();
				$za->open($file);  
				for( $i = 0; $i < $za->numFiles; $i++ ){ 
					$stat = $za->statIndex( $i ); 
					$found_files[] = basename( $stat['name'] ) ;
				}
			}
			//elseif( stripos($filename, $this->pro_file_part) !== false)
			//{
			//	$found = true;
			//}

			//if contains
			if(!empty($found_files))
			{
				foreach(array_filter($found_files) as $each)
				{
					if( stripos($each, $this->addon_namepart.'/'.$this->slug)!==false)
					{
						$found = true;
					}
				}
			} 

			if($found)
			{
				$this->helpers->unzip($file, $this->addons_dir);
				$this->helpers->move_folder_contents($this->addons_dir.'/'. $this->addon_namepart, $this->addons_dir);
				$this->helpers->rmdir_recursive($this->addons_dir.'/'. $this->addon_namepart);
				$need_space = stripos($_SERVER['REQUEST_URI'], 'upload.php') !== false ? '        ' : '';
				return ['error'=> $need_space."Thank You ⭐ Addon has been installed, you can activate it with the key !"];
			}
		}
		return $array;
	}

}} // class 
#endregion


label_pro_plugin__PuvoxSoftware:
#region  Pro-Version functions ( extends above default plugin class)
if (!trait_exists('pro_plugin__PuvoxSoftware')){
trait pro_plugin__PuvoxSoftware
{
	// ========= my functions for PRO plugins ========== //
	public function addon_path()
	{
		return WP_PLUGIN_DIR .'/_addons/'.$this->slug .'-addon/addon.php';
	}
	
	public function addon_exists()
	{
		return (file_exists($this->addon_path()));
	}

	public function load_pro()
	{
		if ($this->is_pro)
		{
			if ($this->is_pro_legal)
			{
				$puvox_last_class = $this;
				if($this->addon_exists())
					include_once($this->addon_path());
			}
		}
	}

	public function check_if_pro_plugin()
	{
		$this->is_pro		= null;
		$this->is_pro_legal	= null;
		if( $this->static_settings['has_pro_version'] ){
			//$this->has_pro_version = true;  // it is price of plugin
			$ar= $this->get_license();
			$this->is_pro		= $ar['status'];
			$this->is_pro_legal	= $ar['legal'];
		}
		if(is_admin())
		{
			if ($this->is_pro)
			{
				if (!$this->is_pro_legal)
				{
					add_action('network_admin_notices', [$this, 'admin_error_notice_pro'] ); 
					add_action('admin_notices', [$this, 'admin_error_notice_pro'] ); 
				}
				else{
					$this->pro_check_once_in_a_while();
				}
			}
		}
		$this->addons_dir = WP_PLUGIN_DIR.'/_addons'; //wp_plugins_dir();
	} 

	public function license_keyname(){
		return $this->plugin_slug_u ."_l_key";
	}

	public function get_license($key=false){
		$def_array = [
			'status' => false,
			'legal' => false,
			'key' => '',
			'last_error'=>''
		];
		$license_arr = get_site_option($this->license_keyname(), $def_array );
		return ($key ? $license_arr[$key] : $license_arr);
	}

	public function update_license($val, $val1=false){
		if(is_array($val)){
			$array = $val;
		}
		else{
			$array= $this->get_license();
			$array[$val]=$val1;
		}
		update_site_option( $this->license_keyname(), $array );
	}



	public function license_answer($key, $type="check/or/activate")
	{
		$this->info_arr	= ['key' => $key] + ['siteurl'=>home_url(), 'plugin_slug'=>$this->slug ] + $this->pluginvars() + $this->opts;
		$answer =
			wp_remote_retrieve_body(
				wp_remote_post($this->static_settings['purchase_check'].$type,
					[
						'method' => 'POST',
						'timeout' => 25,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => [],
						'body' => $this->info_arr,
						'cookies' => []
					]
				)
			);
		return $answer;
	}

	public function license_status($license, $type="check/or/activate")
	{
		$key = sanitize_text_field($license);
		$answer = $this->license_answer($key, $type);

		if(!$this->helpers->is_JSON_string($answer)){
			$result = [];
			$result['error'] = $answer;
		}
		else{
			$result = json_decode($answer, true);
		}
		//
		if(isset($result['valid'])){
			if($result['valid']){
				$ar['status']= true;
				$ar['legal']= true;
				$ar['key']= $key;
				$ar['last_error']= '';
				$this->update_license($ar);
			}
			else { 
				// 
				$this->update_license( 'legal', false );
				$this->update_license( 'last_error', json_encode($result['response']) );
				$result['error'] = json_encode($result['response']);
			} 
		}
		else{
			$result['error'] = $answer;
			$this->log('Error while calling to vendor', $result['error']);
		}
		return json_encode($result);
	}

	public function pro_check_once_in_a_while( $time_length = 864000 )
	{
		$name= '`_last_license_check';
		$value= $this->get_transient_CHOSEN($name);
		if( !$value || time() - $value > $time_length )
		{
			$lic = $this->get_license();
			$res= $this->license_status($lic['key'], 'activate');
			$this->update_transient_CHOSEN($name, time() );
		}
	}

	public function unregistered_pro() { return $this->static_settings['has_pro_version'] && !$this->is_pro_legal; }

	public function admin_error_notice_pro(){ ?>
		<div class="notice notice-error is-dismissible">
			<p><?php  _e( sprintf('Notice: License for plugin <code><b>%s</b></code> is invalidated, so it\'s <b style="color:red;">PRO</b> functionality has been disabled.', $this->static_settings['Name']) );?> <a href="<?php echo $this->plugin_page_url;?>" target="_blank"><?php _e("Re-validate the key");?></a></p> 
		</div>
		<?php
	}

	public function pro_field($echo=true){
		if($this->unregistered_pro()){
			$res= 'data-pro-overlay="pro_overlay"';
			if($echo) echo $res;
			else return $res;
			//echo '<span class="pro_overlay overlay_lines"></span> ';
		}
	}

	public function purchase_pro_block(){
		if ( !$this->static_settings['has_pro_version'])  return;
		if ( $this->is_pro_legal  ) return;

		?>
		<div class="pro_block">
			<style>
			.get_pro_version { line-height: 1.2; z-index: 123; background: #ff1818;  text-align: center; border-radius: 100% 100% 0 0; display: inline-block;  position: fixed; bottom: 0px; right: 0; left: 0; padding: 10px 10px; max-width: 750px; margin: 0 auto; text-shadow: 0px 0px 6px white;  box-shadow: 0px 0px 52px black; }
			.get_pro_version .centered_div > span  { font-size: 1.5em; }
			.get_pro_version .centered_div .or_enter_key_phrase{ font-style: italic; font-size:1em; }
			.get_pro_version .centered_div > span  a { font-size: 1em; color: #7dff83;}
			.init_hidden{ display:none; }
			z#check_results{ display:inline; flex-direction:row; font-style:italic; }
			#check_results .correct{  background: #a8fba8;  }
			#check_results .incorrect{  background: pink;  }
			#check_results span{  padding:3px 5px;  }
			.myplugin .dialog_enter_key{ display:none; }
			.dialog_enter_key_content {  display: flex; flex-direction: column; align-items: center;  }
			.dialog_enter_key_content > *{  margin: 10px ;  }
			.myplugin .illegal_missing {font-size:12px; word-wrap:pre-wrap; }

			[data-pro-overlay=pro_overlay]{  pointer-events: none;  cursor: default;  position:relative;  min-height: 2em;  padding:5px; }
			[data-pro-overlay=pro_overlay]::before{   content:""; width: 100%; height: 100%; position: absolute; background: black; opacity: 0.3; z-index: 1;  top: 0;   left: 0;
				background: url("https://ps.w.org/internal-functions-for-protectpages-com-users/trunk/assets/overlay-1.png");
			}
			[data-pro-overlay=pro_overlay]::after{ 
				white-space: pre; content: "<?php $str=__('Only available in FULL VERSION');  echo str_repeat($str.'\a\a\a\a\a\a\a\a\a\a\a\a\a\a\a\a\a\a\a', 4).$str;?>"; 
				text-shadow: 0px 0px 5px black; padding: 5px;  opacity:1;  text-align: center;  animation-name: blinking;  zzanimation-name: moving;  animation-duration: 6s;  animation-iteration-count: infinite;  overflow:hidden; display: flex; justify-content: center; align-items: center; position: absolute; top: 0; left: 0; bottom: 0; right: 0; z-index: 3; overflow: hidden; font-size: 2em; color: red;
			}
			@keyframes blinking {
				0% {opacity: 0;}
				50% {opacity: 1;}
				100% {opacity: 0;}
			}
			@keyframes moving {
				0% {left: 30%;}
				40% {left: 100%;}
				100% {left: 0%;}
			}
			</style>
			
			<div class="dialog_enter_key">
				<div class="dialog_enter_key_content" title="Enter the purchased license key">
					<input id="key_this" class="regular-text" type="text" value="<?php echo $this->get_license('key');?>"  />
					<button id="check_key" ><?php _e( 'Check key' );?></button>
					<span id="check_results">
						<span class="correct init_hidden"><?php _e( 'correct' );?></span>
						<span class="incorrect init_hidden"><?php _e( 'incorrect' );?></span>
					</span>
				</div>
			</div>

			<div class="get_pro_version">
				<div class="centered_div">
					<?php 
					$need_to_enter_key = false;

					if ( $this->is_pro )
					{ 
						if ( !$this->addon_exists() )
						{ ?>
							<span class="addon_missing">
							( <?php _e('Seems you have bought a PRO version, but the addon is not installed.');?> )
							</span>	
							<?php
						}
						elseif( !$this->is_pro_legal)
						{
							$need_to_enter_key=true;
							?>
							<span class="illegal_missing">
							( <?php _e('Seems you don\'t have a legal key');?>.  <span class="last_err_msg" style="white-space: pre-wrap;">( <?php _e( sprintf('Last error message: <code>%s</code> ',  $this->get_license('last_error') ) ); ?> )</span>  )
							</span>	
							<?php
						}
					}  
					else
					{
						if (!$this->addon_exists()) {  ?>
							<span class="purchase_phrase">
								<a id="purchase_key" href="<?php echo esc_url($this->static_settings['purchase_url']);?>" target="_blank"><?php _e('GET FULL VERSION');?></a> <span class="price_amnt"><?php _e('only');?> <?php echo $this->static_settings['has_pro_version'];?>$</span>
							</span>
						<?php 
						}
						$need_to_enter_key=true;
					} 
					?>
					<?php
					
					if ($need_to_enter_key)
					{
					?>
					<span class="or_enter_key_phrase">
					( <?php _e('After purchase');?> <a id="enter_key"  href=""><?php _e('Enter License Key');?></a> )
					</span>	
					<?php
					}

					?>
				</div>
			</div>
		</div>
		<?php
		$this->plugin_scripts();
	}

	public function plugin_scripts(){
		?>
		<script>
		function main_tt()
		{ 
			var this_action_name = '<?php echo $this->plugin_slug_u;?>';

			(function ( $ ) {
				$(function () {
					//$("#purchase").on("click", function(e){ this_name_tt.open_license_dialog(); } );
					$("#enter_key").on("click", function(e){ return this_name_tt.enter_key_popup(); } );
					$("#check_key").off().on("click", function(e){ return this_name_tt.check_key(); } );
				});
			})( jQuery );

			// Create our namespace
			this_name_tt = {

				/*
				*	Method to check (using AJAX, which calls WP back-end) if inputed username is available
				*/
				enter_key_popup: function(e) {

					// Show jQuery dialog
					jQuery('.dialog_enter_key_content').dialog({
						modal: true,
						width: 500,
						close: function (event, ui) {
							//jQuery(this).remove();	// Remove it completely on close
						}
					});
					return false;
				},

				IsJsonString: function(str) {
					try {
						JSON.parse(str);
					} catch (e) {
						return false;
					}
					return true;
				},

				check_key : function(e) {

					var this1 = this;

					var inp_value = jQuery("#key_this").val();

					if (inp_value == ""){  return;  }

					ttLibrary.backend_call(
						{
							'PRO_check_key': inp_value
						},

						// Function when request complete
						function (answer) {

							if(typeof window.ttdebug != "undefined"){  console.log(answer);  }

							if(this1.IsJsonString(answer)){
								var new_res=  JSON.parse(answer);
								if(new_res.hasOwnProperty('valid')){
									if(new_res.valid){
										this1.show_green();
									}
									else{
										var reponse1 = JSON.parse(new_res.response);
										this1.show_red(reponse1.message);
									}
								}
								else {
									this1.show_red(new_res);
								}
							}
							else{
								this1.show_red(answer);
							}
						}
					);
				},

				show_green : function(){
					jQuery("#check_results .correct").show();
					jQuery("#check_results .incorrect").hide();
					alert("<?php _e("Thanks! License is activated for this domain."); echo '\n\n\n\n'; _e("NOTE: Sharing or unauthorized use of the license will be ended with the suspension of the license code.") ;?>");
					ttLibrary.reload_this_page();
					//this.save_results();
				},

				show_red : function(e){
					jQuery("#check_results .correct").hide();
					jQuery("#check_results .incorrect").show();
					jQuery("#check_results .incorrect").html(e);

					/*
					var message = 'Your inputed username "' + tw_usr + '" is incorrect! \nPlease, change it.';
					// Show jQuery dialog
					jQuery('<div>' + message + '</div>').dialog({
						modal: true,
						width: 500,
						close: function (event, ui) {
							jQuery(this).remove();	// Remove it completely on close
						}
					});
					*/
				}

			};
		}
		main_tt();
		</script>

		<?php
	}
} goto label_default_plugin__PuvoxSoftware;  } // class

#endregion
//==========================================================================================================
//========================================== PLUGIN SPECIFIC PARTS =========================================
//==========================================================================================================
 
 