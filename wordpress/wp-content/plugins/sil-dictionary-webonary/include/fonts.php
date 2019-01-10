<?php
//This class has functions for reading out the font family names from a css file and writing the font faces into
//a custom css
class fontMonagment
{
	public function getFontsAvailable()
	{
		$string = file_get_contents(ABSPATH . FONTFOLDER . "fonts.json");
		$arrFont = json_decode($string, true);

		return $arrFont;
	}

	public function get_fonts_fromCssText($css_string)
	{
		// Get the CSS that contains a font-family rule.
		$length = strlen($css_string);
		$porperty = 'font-family';
		$last_position = 0;
		$arrCSSFonts = null;
		$x = 0;
		while (($last_position = strpos($css_string, $porperty, $last_position)) !== FALSE) {
			// Get closing bracket.
			$end = strpos($css_string, '}', $last_position);
			if ($end === FALSE) {
				$end = $length;
			}
			$end++;

			// Get position of the last closing bracket (start of this section).
			$start = strrpos($css_string, '}', - ($length - $last_position));
			if ($start === FALSE) {
				$start = 0;
			}
			else {
				$start++;
			}

			// Get closing ; in order to get the end of the declaration.
			$declaration_end = strpos($css_string, ';', $last_position);

			// Get values.
			$start_of_values = strpos($css_string, ':', $last_position);
			$values_string = substr($css_string, $start_of_values + 1, $declaration_end - ($start_of_values + 1));
			// Parse values string into an array of values.
			$values_array = explode(',', $values_string);

			$fontName = trim(str_replace("'", "", $values_array[0]));
			$arrCSSFonts[$x] = str_replace('"', '', $fontName);

			// Values array has more than 1 value and first element is a quoted string.

			// Advance position.
			$x++;
			$last_position = $end;
		}
		$arrUniqueCSSFonts = null;
		if(count($arrCSSFonts) > 0)
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
	public function set_fontFaces($file = "configured.css", $uploadPath)
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
}