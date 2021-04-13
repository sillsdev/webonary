<?php
$absolute_path = __FILE__;
$path_to_file = explode( 'wp-content', $absolute_path );
$path_to_wp = $path_to_file[0];
// Access WordPress
require_once( $path_to_wp . '/wp-load.php' );

$widget_data = get_option('widget_chart-popups');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="content-type" />
<title>Character Chart</title>
<link href="chart.css" rel="stylesheet" type="text/css" />
<base target="-s" />
<script type="text/javascript"><!--
    txtfield = window.opener.document.searchform.s.value;
    function sendValue(character){
            window.opener.document.searchform.s.value = txtfield + character;
            window.opener.focus();
            window.close();
    }
    //
    --></script>
    <style>
    <?php
    $options = get_option('themezee_options');
    if ( $options['themeZee_custom_css'] <> "" ) { echo esc_attr($options['themeZee_custom_css']); }
    
    if(isset($widget_data[2]['fontfamily']))
    {
    ?>
    td {
    	font-family: <?php echo $widget_data[2]['fontfamily']; ?>;
    }
    <?php
    }
    
	if(isset($widget_data[2]['fontsize']))
    {
    ?>
    td {
    	font-size: <?php echo $widget_data[2]['fontsize']; ?>;
    }
    <?php
    }
    ?>
    </style>
</head>
<body>
<?php
		
		$numberOfCols = 10;
		if(isset($widget_data[2]['numberOfCols']))
		{
			$numberOfCols = $widget_data[2]['numberOfCols'];
		}
		
		//$characters = apply_filters('widget_characters', $instance['characters']);
		$arrChar = explode(",", $widget_data[2]['characters']);
		
		echo "<table>";
		echo "<tr>";
		
		$i = 0;
		foreach($arrChar as $char)
		{
			if (($i % $numberOfCols) == 0)
			{
				echo "</tr>";
				echo "<tr>";
			}
				echo "<td><a class=chartLink href=\"#\" onclick=\"sendValue('" . $char . "'); return false;\">" . $char . "</a></td>";
			$i++;
		}

		echo "</tr>";
		echo "</table>";
?>
<div class="YiChart_TableContainer" id="YiChart_TableContainer">

</div>
</body>
</html>
