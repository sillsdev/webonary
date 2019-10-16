<?php


class Webonary_Font_Management
{
	public function getFontsAvailable()
	{
		$string = file_get_contents(ABSPATH . FONTFOLDER . 'fonts.json');
		$arrFont = json_decode($string, true);

		return $arrFont;
	}

	public function get_fonts_fromCssText($css_string)
	{
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

		$arrUniqueCSSFonts = array();

		if(!empty($arrCSSFonts))
		{
			$arrUniqueCSSFonts = array_unique($arrCSSFonts);
			sort($arrUniqueCSSFonts);
		}

		return $arrUniqueCSSFonts;
	}

	public function get_system_fonts()
	{
		$arrSystemFonts = array("Arial", "Arial Black", "Helvetica", "Times New Roman", "SimSun", "Tahoma", "Calibri", "Comic Sans MS", "Verdana");

		return  $arrSystemFonts;
	}

	//////////////////////
	// creates custom.css
	//////////////////////
	public function set_fontFaces($file, $uploadPath)
	{
		$css_string = file_get_contents($file);
		$arrUniqueCSSFonts = $this->get_fonts_fromCssText($css_string);

		$arrFont = $this->getFontsAvailable();

		$fontFace = "";
		$arrFonttyles = array("R", "B", "I", "BI");
		if(count($arrUniqueCSSFonts) > 0)
		{
			foreach($arrUniqueCSSFonts as $userFont)
			{
				$fontKey = array_search($userFont, array_column($arrFont, 'name'));

				if($fontKey !== false)
				{
					foreach($arrFonttyles as $fontStyle)
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
		return;
	}

	public function uploadFont()
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
					array_push($tempArray, $arrFont);
					$jsonData = json_encode($tempArray);
					file_put_contents(ABSPATH . FONTFOLDER . 'fonts.json', $jsonData);
				}

				$upload_dir = wp_upload_dir();
				$target_path = $upload_dir['path'] . "/imported-with-xhtml.css";
				$this->set_fontFaces($target_path, $upload_dir['path']);

			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
		echo "</h3>";
		echo "<hr>";
	}

	public function uploadFontForm()
	{
		$submitNumber = array_pop(array_keys($_REQUEST['uploadButton']));
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
