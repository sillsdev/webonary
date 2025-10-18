<?php
$absolute_path = __DIR__;

// check for development environment
if (strpos($absolute_path, 'wp-content') === false) {
	$parts = explode('webonary', $absolute_path);
	$wp_load = $parts[0] . 'webonary/wordpress/wp-load.php';
} else {
	$parts = explode('wp-content', $absolute_path);
	$wp_load = $parts[0] . 'wp-load.php';
}

// Access WordPress
require_once($wp_load);

$widget_data = get_option('widget_chart-popups');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Character Chart</title>
	<link href="chart.css" rel="stylesheet" type="text/css" />
	<base target="-s" />
	<script type="text/javascript">
    txtfield = window.opener.document.searchform.s.value;
    function sendValue(character){
            window.opener.document.searchform.s.value = txtfield + character;
            window.opener.focus();
            window.close();
    }
    </script>
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
		$arrChar = explode(',', $widget_data[2]['characters']);

		$char_html = <<<'HTML'
<td class='spbutton'><a class=chartLink href="#" onclick="sendValue('%1$s'); return false;">%1$s</a></td>
HTML;

		echo '<table>';
		echo '<tr>';

		$i = 0;
		foreach ($arrChar as $char) {

			if (($i % $numberOfCols) == 0) {
				echo '</tr>';
				echo '<tr>';
			}

			printf($char_html, $char);

			$i++;
		}

		echo '</tr>';
		echo '</table>';
?>
<div class="YiChart_TableContainer" id="YiChart_TableContainer">

</div>
</body>
</html>
