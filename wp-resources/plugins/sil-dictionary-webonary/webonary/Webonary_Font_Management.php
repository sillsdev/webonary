<?php


class Webonary_Font_Management
{
	private static ?array $fonts_available = null;
	private static ?array $fonts_configured = null;

	public static string $font_option_name_prefix = 'mapped_font_';

	public static function GetFontOptionName(string $font_name): string
	{
		return self::$font_option_name_prefix . str_replace(' ', '?', $font_name);
	}

	public static function GetFontNameFromOptionName(string $option_name): string
	{
		return str_replace('?', ' ', substr($option_name, strlen(self::$font_option_name_prefix)));
	}

	public static function getFontsAvailable(): array
	{
		$file_name = wp_normalize_path(ABSPATH . FONTFOLDER . 'fonts.json');

		if (!is_file($file_name))
			return [];

		$string = file_get_contents($file_name);
		if (empty($string))
			return [];

		return json_decode($string, true);
	}

	public static function getFontsAvailableNames(): array
	{
		if (!is_null(self::$fonts_available))
			return self::$fonts_available;

		$fonts = self::getFontsAvailable();

		$return_val = [];
		foreach ($fonts as $font) {
			$return_val[$font['name']] = $font['name'];
		}

		natcasesort($return_val);

		self::$fonts_available = $return_val;

		return self::$fonts_available;
	}

	/**
	 * @param $css_string
	 * @return string[]
	 */
	public static function get_fonts_fromCssText($css_string): array
	{
		if (empty($css_string))
			return [];

		// Get the CSS that contains a font-family rule.
		$length = strlen($css_string);
		$property = 'font-family';
		$last_position = 0;
		$arrCSSFonts = array();

		while (($last_position = strpos($css_string, $property, $last_position)) !== FALSE) {

			// Get closing bracket.
			$end = strpos($css_string, '}', $last_position);
			if ($end === FALSE) {
				$end = $length;
			}
			$end++;

			// Get closing ; in order to get the end of the declaration.
			$declaration_end = strpos($css_string, ';', $last_position);

			// Get values.
			$start_of_values = strpos($css_string, ':', $last_position);
			$values_string = substr($css_string, $start_of_values + 1, $declaration_end - ($start_of_values + 1));
			// Parse values string into an array of values.
			$values_array = explode(',', $values_string);

			// We want the first font if there is more than one, without any quotation marks
			if (!empty($values_array))
			    $arrCSSFonts[] = trim($values_array[0], " \t\n\r\0\x0B\"'");

			// Advance position.
			$last_position = $end;
		}

		$return_val = [];
		foreach ($arrCSSFonts as $font) {
			$return_val[$font] = $font;
		}

		sort($return_val);

		return $return_val;
	}

	public static function GetConfiguredFonts(): array
	{
		if (!is_null(self::$fonts_configured))
			return self::$fonts_configured;

		$upload_dir = wp_upload_dir();
		$css_string = '';
		$configured_css_file = $upload_dir['basedir'] . '/imported-with-xhtml.css';
		if (file_exists($configured_css_file))
			$css_string = file_get_contents($configured_css_file);

		$fonts = Webonary_Font_Management::get_fonts_fromCssText($css_string);

		$return_val = [];
		foreach ($fonts as $font) {
			$return_val[$font] = $font;
		}

		natcasesort($return_val);

		self::$fonts_configured = $return_val;

		return self::$fonts_configured;
	}

	public static function get_system_fonts(): array
	{
		return [
			'Arial',
			'Arial Black',
			'Calibri',
			'Comic Sans MS',
			'DejaVu Sans',
			'Droid Sans',
			'Georgia',
			'Helvetica',
			'Helvetica Neue',
			'Red Hat',
			'Roboto',
			'San Francisco',
			'Segoe UI',
			'SimSun',
			'Tahoma',
			'Times',
			'Times New Roman',
			'Ubuntu',
			'Verdana'
		];
	}

	//////////////////////
	// creates custom.css
	//////////////////////
	public function set_fontFaces($css_string, $uploadPath): void
	{
		$arrUniqueCSSFonts = Webonary_Font_Management::get_fonts_fromCssText($css_string);
		$arrFont = $this->getFontsAvailable();

		$fontFace = '';
		$arrFontStyles = array('R', 'B', 'I', 'BI');
		if(count($arrUniqueCSSFonts) > 0)
		{
			foreach($arrUniqueCSSFonts as $userFont)
			{
				$fontKey = array_search($userFont, array_column($arrFont, 'name'));

				if($fontKey !== false)
				{
					foreach($arrFontStyles as $fontStyle)
					{
						$extension = "." . $arrFont[$fontKey]["type"];

						if(file_exists(ABSPATH . FONTFOLDER . $arrFont[$fontKey]["filename"] . "-" . $fontStyle . $extension))
						{
							$fontFace .= "@font-face {\n";
							$fontFace .= "font-family: " . $userFont . ";\n";
							$fontFace .= "src: url(" . FONTFOLDER . $arrFont[$fontKey]["filename"] . "-" . $fontStyle . $extension . ");\n";
							if($fontStyle == "B" || $fontStyle == "BI")
							{
								$fontFace .= "font-weight: bold;\n";
							}
							if($fontStyle == "I" || $fontStyle == "BI")
							{
								$fontFace .= "font-style: italic;\n";
							}
							$fontFace .= "}\n\n";
						}
					}
				}
			}
			file_put_contents($uploadPath . "/custom.css" , $fontFace);
		}
	}

	/**
	 * @param array $fonts Key = CSS name, Value = system name
	 * @param string $upload_path
	 * @return void
	 */
	public static function SaveSelectedFonts(array $fonts, string $upload_path): void
	{
		$available = self::getFontsAvailable();
		$styles = ['R', 'B', 'I', 'BI'];
		$template = '@font-face { font-family: %s; src: url(/wp-content/uploads/fonts/%s); %s %s }';
		$monospace = '@font-face { font-family: %s; src: local(ui-monospace), local(Menlo), local(Monaco), local("Cascadia Mono"), local("Segoe UI Mono"), local("Roboto Mono"), local("Oxygen Mono"), local("Ubuntu Mono"), local("Ubuntu Monospace"), local("Source Code Pro"), local("Fira Mono"), local("Droid Sans Mono"), local("DejaVu Sans Mono"), local("Courier New"), local(Courier); }';
		$serif = '@font-face { font-family: %s; src: local(Times), local(Georgia), local("Droid Serif"), local("Noto Serif"), local("Times New Roman"), local("Free Serif"), local("Droid Serif"), local("DejaVu Serif"); }';
		$sans_serif = '@font-face { font-family: %s; src: local("San Francisco"), local("Helvetica Neue"), local("Lucida Grande"), local("Segoe UI"), local("Tahoma"), local("Arial"), local("Helvetica"), local(Ubuntu), local("Red Hat"), local("Liberation Sans"), local("DejaVu Sans"), local("Droid Sans"), local("Roboto"); }';
		$local = '@font-face { font-family: %s; src: local("%s"); }';
		$entries = [];

		foreach ($fonts as $css_name => $system_name) {

			// is this an available font?
			$found = array_values(array_filter($available, function ($font) use ($system_name) {
				return $font['name'] == $system_name;
			}));

			if (!empty($found)) {

				// this is one of the Available fonts
				$font = $found[0];

				// add all available font styles to the css
				foreach ($styles as $style) {

					$file_name = $font['filename'] . '-' . $style . '.' . $font['type'];

					if (file_exists(ABSPATH . FONTFOLDER . $file_name)) {

						$bold = ($style == 'B' || $style == 'BI') ? 'font-weight: bold;' : '';
						$italic = ($style == 'I' || $style == 'BI') ? 'font-style: italic;' : '';
						$css = sprintf($template, $css_name, $file_name, $bold, $italic);

						// remove extra spaces before saving
						$entries[] = preg_replace('/\s\s+/', ' ', $css);
					}
				}
			}
			elseif ($css_name != $system_name) {

				// NOTE: if the $css_name and $system_name are the same, the browser will load the font itself.

				// this is a System or Default font
				$entries[] = match ($system_name) {
					'monospace' => sprintf($monospace, $css_name),
					'serif' => sprintf($serif, $css_name),
					'sans-serif' => sprintf($sans_serif, $css_name),
					default => sprintf($local, $css_name, $system_name)
				};
			}
		}

		$css = implode(PHP_EOL, $entries);

		file_put_contents($upload_path . '/custom.css' , $css);
	}

	public function uploadFont(): void
	{
		$filetype = strtolower(pathinfo($_FILES["fileToUpload"]["name"],PATHINFO_EXTENSION));
		$filename = str_replace(" ", "", $_POST["fontname"]);
		$fontType = "R";
		if($_POST['fonttype'] == "bold")
		{
			$fontType = "B";
		}
		if($_POST['fonttype'] == "cursive")
		{
			$fontType = "I";
		}

		$filenameFull = $filename . "-" . $fontType . "." . $filetype;
		$target_file = ABSPATH . FONTFOLDER . $filenameFull;
		$uploadOk = 1;

		echo "<h3>";
		// Allow certain file formats
		if($filetype != "woff" && $filetype != "ttf") {
			echo "Sorry, only woff and ttf files are allowed.";
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 1) {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

				$arrFont["name"] = $_POST["fontname"];
				$arrFont["filename"] = $filename;
				$arrFont["hasSubFonts"] = false;
				$arrFont["type"] = $filetype;

				$inp = file_get_contents(ABSPATH . FONTFOLDER . 'fonts.json');
				$tempArray = json_decode($inp);

				$hasFont = array_search($_POST["fontname"], array_column($tempArray, 'name'));
				if(!$hasFont)
				{
					$tempArray[] = $arrFont;
					$jsonData = json_encode($tempArray);
					file_put_contents(ABSPATH . FONTFOLDER . 'fonts.json', $jsonData);
				}

				$upload_dir = wp_upload_dir();
				$target_path = $upload_dir['path'] . "/imported-with-xhtml.css";
				$css_string = file_get_contents($target_path);
				$this->set_fontFaces($css_string, $upload_dir['path']);

			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
		echo "</h3>";
		echo "<hr>";
	}

	public function uploadFontForm(): void
	{
		$keys = array_keys($_REQUEST['uploadButton']);
		$submitNumber = array_pop($keys);
		?>
		<a id="uploadfont"></a>
		<h1>Upload Font</h1>
		<h3><?php echo $_POST['fontname'][$submitNumber]; ?></h3>
		<p></p>
		<form action="#" method="post" enctype="multipart/form-data">
			<input type="hidden" name="fontname" value="<?php echo $_POST['fontname'][$submitNumber]; ?>">
			Type:
            <select name="fonttype" title="">
				<option value="regular">regular</option>
				<option value="bold">bold</option>
				<option value="cursive">cursive</option>
			</select>
			<p></p>
			Font file: <input type="file" name="fileToUpload">
			<p></p>
			<input type="submit" value="Upload Font" name="uploadFont">
		</form>
		<hr>
		<?php
	}
}
