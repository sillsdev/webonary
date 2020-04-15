<?php
/**
 * @package Mapsvg WordPress Plugin
 * @version 1.6.4
 *
 * You must purchase Regular or Extended license to use mapSvg plugin.
 * Visit plugin's page @ CodeCanyon: http://codecanyon.net/item/jquery-interactive-svg-map-plugin/1694201
 * Licenses: http://codecanyon.net/licenses/regular_extended
 */
/*
Plugin Name: MapSVG
Plugin URI: http://codecanyon.net/item/mapsvg-interactive-vector-maps/2547255
Description: Add interactive map to any page of your WordPress site.
Author: Roman S. Stepanov
Author URI: http://codecanyon.net/user/Yatek
Version: 1.6.4
*/ 

//error_reporting(E_ALL);
define('MAPSVG_DEBUG', false);

define('MAPSVG_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('MAPSVG_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('MAPSVG_MAPS_DIR', MAPSVG_PLUGIN_DIR . 'maps');
define('MAPSVG_MAPS_URL', MAPSVG_PLUGIN_URL . 'maps/');
define('MAPSVG_PINS_DIR', MAPSVG_PLUGIN_DIR . 'markers/');
define('MAPSVG_PINS_URL', MAPSVG_PLUGIN_URL . 'markers/');
define('MAPSVG_VERSION', '1.6.4');
define('MAPSVG_JQUERY_VERSION', '5.6.3');

$mapsvg_inline_script = '';


/**
 * including jQuery
 * normally you don't need this because jQuery is included by WP
 */
function add_jquery() {

        global $wp_scripts;

        $version = '1.7.2';
        
        if ( !is_admin() && ( version_compare($version, $wp_scripts -> registered['jquery'] -> ver) == 1 )) {
                wp_deregister_script('jquery');
                wp_register_script('jquery', MAPSVG_PLUGIN_URL . 'js/jquery-1.7.2.js', false, $version);
        }

        wp_enqueue_script('jquery');
}
add_action( 'wp_enqueue_scripts', 'add_jquery' );



/**
 * Add common JS & CSS
 */
function mapsvg_add_jscss_common(){

    wp_register_script('raphael', MAPSVG_PLUGIN_URL . 'js/raphael.js',null,'2.1.0');
    wp_enqueue_script('raphael',null,'2.1.0');
    wp_register_script('jquery.mousewheel', MAPSVG_PLUGIN_URL . 'js/jquery.mousewheel.min.js',array('jquery'), '3.0.6');
    wp_enqueue_script('jquery.mousewheel', null, '3.0.6');

    if(MAPSVG_DEBUG)
        wp_register_script('mapsvg', MAPSVG_PLUGIN_URL . 'js/mapsvg.js', array('jquery'), rand());
    else
        wp_register_script('mapsvg', MAPSVG_PLUGIN_URL . 'js/mapsvg.min.js', array('jquery'), MAPSVG_JQUERY_VERSION);
    
    wp_enqueue_script('mapsvg');

}
add_action('wp_enqueue_scripts', 'mapsvg_add_jscss_common');
//add_action('admin_enqueue_scripts', 'mapsvg_add_jscss_common');


/**
 * Add admin's JS & CSS
 */
function mapsvg_add_jscss_admin($hook_suffix){

    global $mapsvg_settings_page, $wp_version;


    

    // Load scripts only if we on mapSVG admin page
    if ( $mapsvg_settings_page != $hook_suffix )
        return;

    mapsvg_add_jscss_common();

    
    if(isset($_GET['page']) && $_GET['page']=='mapsvg-config'){

        wp_register_script('mapsvg.admin', MAPSVG_PLUGIN_URL . 'js/admin.js', array('jquery'), '2.3');
        wp_enqueue_script('mapsvg.admin');
        wp_register_script('colorpicker.js', MAPSVG_PLUGIN_URL . 'js/colorpicker/js/colorpicker.js', array('jquery'));
        wp_enqueue_script('colorpicker.js');
        wp_register_script('bootstrap.min.js', MAPSVG_PLUGIN_URL . 'js/bootstrap.min.js', array('jquery'), 'custom.2.0');
        wp_enqueue_script('bootstrap.min.js',false);
        wp_register_script('jquery.message', MAPSVG_PLUGIN_URL . 'js/jquery.message.js', array('jquery'));
        wp_enqueue_script('jquery.message');
    	wp_register_style('colorpicker.css', MAPSVG_PLUGIN_URL . 'js/colorpicker/css/colorpicker.css');
    	wp_enqueue_style('colorpicker.css');
    	wp_register_style('bootstrap.min.css', MAPSVG_PLUGIN_URL . 'css/bootstrap.min.css', null, '2.0');
    	wp_enqueue_style('bootstrap.min.css');
    	wp_register_style('jquery.message.css', MAPSVG_PLUGIN_URL . 'css/jquery.message.css');
    	wp_enqueue_style('jquery.message.css');
    	wp_register_style('main.css', MAPSVG_PLUGIN_URL . 'css/main.css');
    	wp_enqueue_style('main.css');

        if(version_compare($wp_version, "3.8", '>=')){
            wp_register_style('mapsvg-grey', MAPSVG_PLUGIN_URL . 'css/grey.css');
            wp_enqueue_style('mapsvg-grey');
        }
    }
     
}


/**
 * Add submenu element to Plugins
 */
$mapsvg_settings_page = '';

function mapsvg_config_page() {
    global $mapsvg_settings_page;

	if ( function_exists('add_menu_page') )
		$mapsvg_settings_page = add_menu_page('MapSVG', 'MapSVG', 'manage_options', 'mapsvg-config', 'mapsvg_conf', '', 66);


    add_action('admin_enqueue_scripts', 'mapsvg_add_jscss_admin');
}

add_action( 'admin_menu', 'mapsvg_config_page' );


/**
 * Register [mapsvg] shortcode
 */
function mapsvg_print( $atts ){
  global $mapsvg_inline_script;

  $post = get_post($atts['id']);

  if (empty($post->ID))
    return 'Map not found, please check "id" parameter in your shortcode.';

  $data  = '<div id="mapsvg-'.$post->ID.'" class="mapsvg"></div>';
  $script = '<script type="text/javascript">';

  if(!empty($atts['selected'])){
      $country = str_replace(' ','_', $atts['selected']);
      $script .= '
      var mapsvg_options = '.$post->post_content.';
      jQuery.extend( true, mapsvg_options, {regions: {"'.$country.'": {selected: true}}} );
      jQuery("#mapsvg-'.$post->ID.'").mapSvg(mapsvg_options);</script>';
  }else{
      $script .= 'jQuery("#mapsvg-'.$post->ID.'").mapSvg('.$post->post_content.');</script>';
  }
  $mapsvg_inline_script[] = $script;
  
  //wp_footer('script');
  add_action('wp_footer', 'script', 9999);

  //return //wp_specialchars_decode($data);
  return $data;
}
add_shortcode( 'mapsvg', 'mapsvg_print' );


function script(){
    global $mapsvg_inline_script;
    foreach($mapsvg_inline_script as $m){
        echo $m;
    }
}

function so_handle_038($content) {
    $content = str_replace(array("&#038;","&amp;"), "&", $content); // or $url = $original_url
    return $content;
}
add_filter('the_content', 'so_handle_038', 199, 1);

/**
 * Save map settings as custom type post (post_type = mapsvg)
 */
function mapsvg_save( $data ){
    global $wpdb, $user_ID;

    // Map path
    $data['m']['source'] = MAPSVG_MAPS_URL.$data['mapfile'];

    // JavaScript data
    $data_js   = str_replace('\/', '/', str_replace('\\','\\\\',json_encode(stripslashes_deep($data['m']))));

    if(!isset($data['title']) || empty($data['title']))
        $data['title'] = 'No title';

    // We should add events to options separately as they
    // shouldn't be enclosed with quotes by json_encode
    if(isset($data['events']) && !empty($data['events'])){
        foreach($data['events'] as $e=>$func)
            $str[] = $e.':'.stripslashes_deep($func);
        $events = implode(',',$str);

        $data_js = rtrim($data_js,'}').','.$events.'}';
    }

    $postarr = array(
    	'post_type'    => 'mapsvg',
    	'post_status'  => 'publish',
    	'post_title'   => $data['title'],
    );


    if(isset($data['map_id']) && $data['map_id']!='new'){

        $postarr['ID'] = $data['map_id'];

        // Escape quotes before adding to MySQL
        $postarr['post_content'] = str_replace(array("\n","\r", "\r\n"),'', $data_js);
        $postarr['post_content'] = str_replace("'","\'",$postarr['post_content']);

        // WordPress cuts tags from post_content sometimes so
        // it's more safe to update database record directly:
        
//        mysql_query("update $wpdb->posts set post_title='".$postarr['post_title']."', post_content = '".$postarr['post_content']."' WHERE ID = ".$postarr['ID'], $wpdb->dbh) || die(mysql_error($wpdb->dbh));
        $wpdb->query("update $wpdb->posts set post_title='".$postarr['post_title']."', post_content = '".$postarr['post_content']."' WHERE ID = ".$postarr['ID']);

        update_post_meta($postarr['ID'], 'mapsvg_options', $data);
        $post_id = $postarr['ID'];

    }else{

        $post_id = wp_insert_post( $postarr );

        // Escape quotes before adding to MySQL
        $postarr['post_content'] = str_replace(array("\n","\r", "\r\n"),'', $data_js);
        $postarr['post_content'] = str_replace("'","\'",$postarr['post_content']);

        $wpdb->query("update $wpdb->posts set post_title='".$postarr['post_title']."',
                     post_content = '".$postarr['post_content']."'
                     WHERE ID = ".$post_id);
        
        add_post_meta($post_id, 'mapsvg_options', $data);
    }

    return $post_id;
}


function mapsvg_export(){
    $generated_maps = get_posts(array('numberposts'=>9999, 'post_type'=>'mapsvg'));
    $data = array();
    foreach($generated_map as $m){
        $data[] = array(
                         'post'=> $m['post_content'],
                         'meta'=> get_post_meta($m['ID'], 'mapsvg', true)
                   );
        $file = $data['post'].'#!#'.$data['meta']."\n";
    }

    header ("Content-Type: application/octet-stream");
    header ("Content-disposition: attachment; filename=mapsvg.txt");

    print $file;
}

function mapsvg_import(){

        $postarr = array(
        	'post_type'    => 'mapsvg',
        	'post_status'  => 'publish',
        	'post_title'   => $data['title'],
        );

        $post_id = wp_insert_post( $postarr );

        // Escape quotes before adding to MySQL
        $postarr['post_content'] = str_replace(array("\n","\r", "\r\n"),'', $data_js);
        $postarr['post_content'] = str_replace("'","\'",$postarr['post_content']);

        $wpdb->query("update $wpdb->posts set post_title='".$postarr['post_title']."',
                     post_content = '".$postarr['post_content']."'
                     WHERE ID = ".$post_id);
        add_post_meta($post_id, 'mapsvg_options', $data);

}

function mapsvg_delete($id, $ajax){
    wp_delete_post($id);
    delete_post_meta($id, 'mapsvg_options');
    if(!$ajax)
        wp_redirect(admin_url('plugins.php?page=mapsvg-config'));
}

function mapsvg_copy($id, $new_name){
    global $wpdb;

    $post = &get_post($id);

    $copy_post = array(
    	'post_type'    => 'mapsvg',
    	'post_status'  => 'publish',
    	'post_title'   => $new_name,
        'post_content' => $post->post_content
    );

    $new_id = wp_insert_post($copy_post);

    $copy_post['post_content'] = str_replace("'", "\'", $copy_post['post_content']);

    $wpdb->query("update $wpdb->posts set post_title='".$new_name."',
                 post_content = '".$copy_post['post_content']."'
                 WHERE ID = ".$new_id);


    $d = get_post_meta($id, 'mapsvg_options');
    $data = $d[0];
    $data['title'] = $new_name;
    add_post_meta($new_id, 'mapsvg_options', $data);
    return $new_id;
}


/**
 * Remove empty elements from an array
 */
function mapsvg_remove_empty($arr){
    foreach ($arr as $id=>$a){
        if(is_array($a)){
            $arr[$id] = mapsvg_remove_empty($a);
            if(count($arr[$id])==0) unset($arr[$id]);
        }else{
            if($arr[$id] == '') unset($arr[$id]);
        }
    }
    return $arr;
}

/**
 * Import data from excel
 */
function mapsvg_import_csv($uploaded_file){
    require(MAPSVG_PLUGIN_DIR . 'csvparser.php');
    $csv  = new MapsvgCsvParser($uploaded_file);
    return $csv->getData(); 
}


/**
 * Settings page in Admin's Control Panel
 */
function mapsvg_conf(){

    $file       = null;
    $map_chosen = false;

    if(isset($_GET['delete_map']))
        mapsvg_delete($_GET['delete_map']);
        
        
    if(isset($_POST['import']) && $_FILES['csv']['tmp_name']){
        
        $file_parts = pathinfo($_FILES['csv']['name']);
                
        if(strtolower($file_parts['extension'])!='csv'){
            $mapsvg_error['file'] = 'Wrong file format ('.$file_parts['extension'].'). Please upload a file in CSV format.';
        }else{
            $import = array();
            $import['data'] = mapsvg_import_csv($_FILES['csv']['tmp_name']);
            $import['type'] = $_POST['import_objects'];

            if(!empty($import['data'])){
                foreach($import['data'] as &$mark){
                    for($i=0;$i<7;$i++){
                        if(empty($mark[$i]))
                            $mark[$i] = '';
                         }                  
                }
                          
                     }
                     

                }
            }
        

    // array for default map setting (from .svg file)
    $default = array('m'=>array('colors'=>array('base'=>null,
                                                'background'=>null,
                                                'hover'=>null,
                                                'selected'=> null,
                                                'disabled'=>null,
                                                'stroke'=> null
                                                ),
                                'width'=>null,
                                'height'=>null,
                                'regions'=>null,
                                'pan'=>false,
                                'zoom'=>false,
                                'zoomButtons'=>array('show'=>true, 'location'=>'right'),
                                'responsive'=>false,
                                'tooltipsMode'=>false,
                                'multiSelect'=>false,
                                'cursor'=>'default',
                                'viewBox' => array()
                                )
                    );
    // array for user-defined map setting
    $data    = array();
    // array for merged setting
    $fields  = array();

    // If $_GET['map_id'] is set then we should get map's settings and from DB
    $map_id = isset($_GET['map_id']) ? $_GET['map_id'] : null;

    if($map_id && $map_id!='new'){
        $d = get_post_meta($map_id, 'mapsvg_options');
        $data = $d[0];
    }

    // Load list of available maps from MAPSVG_MAPS_DIR
    $maps = @scandir(MAPSVG_MAPS_DIR);
    if($maps){
        array_shift($maps); // remove .
        array_shift($maps); // remove ..
    }

    // Load pin images
    $pin_files = @scandir(MAPSVG_PINS_DIR);
    if($pin_files){
        array_shift($pin_files);
        array_shift($pin_files);
    }

    // Load all previously created maps from DB
    $generated_maps = get_posts(array('numberposts'=>999, 'post_type'=>'mapsvg'));

    // Get .svg filename from $_GET query or from loaded settings
    if(isset($_GET['map'])){
        $file = $_GET['map'];
        $data['mapfile'] = $file;
    }elseif(isset($data['mapfile'])){
        $file = $data['mapfile'];
    }


    // If we have a filename then we should parse SVG to get width/height and all region #IDs.
    if($file){

        $map_chosen = true;
        $map_svg    = simplexml_load_file(MAPSVG_MAPS_DIR.'/'.$file);

        $default['m']['width']   = str_replace("px", "",(string)$map_svg['width']);
        $default['m']['height']  = str_replace("px", "",(string)$map_svg['height']);

        if($map_svg['viewBox']){
            $default['m']['viewBox'] = explode(' ',(string)$map_svg['viewBox']);
        }else{
            $default['m']['viewBox'] = array(0, 0, $default['m']['width'], $default['m']['height']);
        }

        $def_region_data = array( 'disabled'=>false,
                                  'selected'=>false,
                                  'tooltip'=>'',
                                  'popover'=>'',
                                  'attr'=>array(
                                                  'fill'=>'',
                                                  'href'=>'',
                                                  'target'=>''
                                               )
                                  );

        $allowed_objects = array(null,'path','ellipse','rect','circle','polygon','polyline');
        $namespaces = $map_svg->getDocNamespaces();        
        $map_svg->registerXPathNamespace('_ns', $namespaces['']);

        while($obj = next($allowed_objects)){
            $nodes = $map_svg->xpath('//_ns:'.$obj);
            if($nodes)
                foreach($nodes as $o)
                    $default['m']['regions'][(string)$o['id']] = $def_region_data;
        }

        if(!empty($default['m']['regions']))
            ksort($default['m']['regions']);

        // Merge default and loaded settings
        if(empty($data['m']['width']))
            $data['m']['width'] = $default['m']['width'];
        if(empty($data['m']['height']))
            $data['m']['height'] = $default['m']['height'];
        if(empty($data['m']['viewBox']))
            $data['m']['viewBox'] = $default['m']['viewBox'];
        if(empty($data['m']['pan']))
            $data['m']['pan'] = false;
        if(empty($data['m']['zoom']))
            $data['m']['zoom'] = false;
        if(empty($data['m']['responsive']))
            $data['m']['responsive'] = false;
        if(empty($data['m']['multiSelect']))
            $data['m']['multiSelect'] = false;
        if(empty($data['m']['disableAll']))
            $data['m']['disableAll'] = false;            
        if(empty($data['m']['tooltipsMode']))
            $data['m']['tooltipsMode'] = false;            

                    
        if(empty($data['m']['colors']))
            $data['m']['colors'] = $default['m']['colors'];
        else
            $data['m']['colors'] = array_merge($default['m']['colors'], $data['m']['colors']);

        /*
        if(empty($data['m']['regions']))
            $data['m']['regions'] = $default['m']['regions'];
        else
            $data['m']['regions'] = array_merge($default['m']['regions'], $data['m']['regions']);
        */


        $events = array('onClick'=>null,'mouseOver'=>null, 'mouseOut'=>null, 'beforeLoad'=>null, 'afterLoad'=>null);

        if(!isset($data['events']) || empty($data['events']))
            $data['events'] = $events;
        else
            $data['events'] = array_merge($events, $data['events']);


        $final_regions = array();

        if(!empty($default['m']['regions'])){
            foreach($default['m']['regions'] as $id=>$def_region){
                if(isset($data['m']['regions'][$id])){
                    $r = array_merge($def_region_data, $data['m']['regions'][$id]);
                    if(isset($data['m']['regions'][$id]['attr']))
                        $r['attr'] = array_merge($def_region_data['attr'], $data['m']['regions'][$id]['attr']);
                    else
                        $r['attr'] = $def_region_data['attr'];

                }else{
                    $r = $def_region_data;
                }
                $final_regions[$id] = $r;
            }
        }

        $data['m']['regions'] = $final_regions;


        $fields = $data;

        $fields['m']['zoomDelta'] = isset($fields['m']['zoomDelta']) && !empty($fields['m']['zoomDelta']) ? (float)$fields['m']['zoomDelta'] : 1.2;
        $fields['m']['zoomLimit'] = isset($fields['m']['zoomLimit']) && !empty($fields['m']['zoomLimit']) ? $fields['m']['zoomLimit'] : array(0,5);
        $fields['m']['zoomButtons'] = isset($fields['m']['zoomButtons']) && !empty($fields['m']['zoomButtons']['location']) ? $fields['m']['zoomButtons'] : array('show'=> true, 'location'=> 'right');
        $fields['m']['loadingText'] = !empty($fields['m']['loadingText']) ? $fields['m']['loadingText'] : 'Loading map...';
        $fields['m']['popover'] = isset($fields['m']['popover']) && !empty($fields['m']['popover']) ? $fields['m']['popover'] : array('width'=>'auto','height'=>'auto');

        $r1 =  number_format( (float) ((int)$default['m']['width'] / (int)$default['m']['height']), 3,'.','');
        $r2 =  number_format( (float) ((int)$fields['m']['width'] / (int)$fields['m']['height']), 3,'.','');

        $fields['ratio_def'] = (int)($r1 == $r2);

        foreach($fields['m']['viewBox'] as $v)
            $vb[] = number_format($v, 0, 0, '');

        $fields['viewBox_i'] = implode(' ',$vb);

    }

    if(empty($fields['title']))
       $fields['title'] = ucfirst(current(explode(".", $file)));

    $fields['mapfile']      = $file;
    $fields['map_id']       = $map_id;

    $jsmapfile = $file ? MAPSVG_MAPS_URL.$fields['mapfile'] : '';
    // Load admin's page tempalte

    $marks = isset($fields['m']) && isset($fields['m']['marks']) ? $fields['m']['marks'] : null;

    ?>
    
    <script type="text/javascript">
        mapsvg_import_marks = <?php if(!empty($import['data'])) echo json_encode($import['data']); else echo '[]';?>;
    </script>
    
    <?php
    include(MAPSVG_PLUGIN_DIR.'template_admin.inc');
    ?>
    
    <script type="text/javascript">
         
         
        (function($) {            
            
             
            $().mapsvgadmin('init', {
                mapfile      : '<?php echo $jsmapfile?>',
                marks        : <?php echo json_encode($marks)?>
            });
        })(jQuery);
    </script>
    <?php

    return true;
}


function ajax_mapsvg_save() {
    if(isset($_POST['data']))
        echo $post_id = mapsvg_save($_POST['data']);
	die();
}
add_action('wp_ajax_mapsvg_save', 'ajax_mapsvg_save');

function ajax_mapsvg_delete() {
    if(isset($_POST['id']))
        mapsvg_delete($_POST['id'], true);
	die();
}
add_action('wp_ajax_mapsvg_delete', 'ajax_mapsvg_delete');

function ajax_mapsvg_copy() {
    if(!empty($_POST['id']) && !empty($_POST['new_name']))
        echo mapsvg_copy($_POST['id'], $_POST['new_name']);
	die();
}
add_action('wp_ajax_mapsvg_copy', 'ajax_mapsvg_copy');

$mapsvg_try = 0;

function ajax_mapsvg_get_coords($addr = false){
    global $mapsvg_try;
    
    $addrs = $_POST['data'] ? $_POST['data'] : array($addr);
    
    if(!empty($addrs)){
        $res = array();
        foreach($addrs as $id=>$a){            
            $data = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($a).'&sensor=false'),true);
            if($data['status']=='OK'){
                $mapsvg_try = 0;
                $res[$id] = $data['results'][0]['geometry']['location'];
            }elseif($mapsvg_try < 3){
                $mapsvg_try++;
                sleep(1);
                $res[$id] = ajax_mapsvg_get_coords($a);
            }else{
                $mapsvg_try = 0;
                $res[$id] = array('lat'=>0,'lon'=>0);
            }
        }
    }
    
    if($addr)
        return $res[0];
    else    
        echo json_encode($res);
        
    die();
}
add_action('wp_ajax_mapsvg_get_coords', 'ajax_mapsvg_get_coords');


add_action('wp_ajax_mapsvg_import', 'ajax_mapsvg_import');

/**
 *  Register mapSVG post type
 */
function reg_mapsvg_post_type(){
    $post_args = array(
        'labels' => array(
            'name' => 'mapSVG',
            'singular_name' => 'mapSVG map'),
        'description' => 'Allows you to insert a map to any page of your website',
        'public' => false,
        'show_ui' => false,
        'exclude_from_search' => true,
        'can_export' => true
    );

    register_post_type('mapsvg', $post_args);
}
add_action('init','reg_mapsvg_post_type');

function cleanArray($arr){
    foreach($arr as $k=>$v) {
        if(is_array($v))
            $arr[$k] = cleanArray($v);
        else
            $arr[$k] = trim(htmlspecialchars(strip_tags($v)));
    }
    return $arr;
}


/*
function mapsvg_check_version(){

    echo 0; die();

    $cc = @file_get_contents('http://codecanyon.net/user/Yatek');

    if(!empty($cc)){

        $pos = strpos($cc, 'mapsvg.wordpress.md5.') + 21;
        $md5 = substr($cc, $pos, 32);
        if ($md5 && $md5!= md5(MAPSVG_VERSION)){
            echo $md5;
        }else{
            echo 0;
        }

    }
    die();
}
add_action('wp_ajax_mapsvg_check_version', 'mapsvg_check_version');
*/


?>