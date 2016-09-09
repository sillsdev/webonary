<?php
//This class has functions for reading out the font family names from a css file and writing the font faces into
//a custom css
class fontMonagment
{
	public function getFontsAvailable(&$arrFontName, &$arrFontStorage, &$arrHasSubFonts)
	{
		$arrFontName[0] = null;
		$arrFontStorage[0] = null;
		$arrHasSubFonts[0] = null;
		
		$arrFontName[1] = "Charis SIL";
		$arrFontStorage[1] = "CharisSIL";
		$arrHasSubFonts[1] = true;
		
		$arrFontName[2] = "Charis SIL Compact";
		$arrFontStorage[2] = "CharisSIL";
		$arrHasSubFonts[2] = true;
		
		$arrFontName[3] = "Andika";
		$arrFontStorage[3] = "Andika";
		$arrHasSubFonts[3] = true;
		
		$arrFontName[4] = "Andika Compact";
		$arrFontStorage[4] = "Andika";
		$arrHasSubFonts[4] = true;
		
		$arrFontName[5] = "Ezra SIL";
		$arrFontStorage[5] = "EzraSIL";
		$arrHasSubFonts[5] = false;

		$arrFontName[6] = "Galatia SIL";
		$arrFontStorage[6] = "GalatiaSIL";
		$arrHasSubFonts[6] = false;
		
		$arrFontName[7] = "Charis SIL Afr";
		$arrFontStorage[7] = "CharisSILAfr";
		$arrHasSubFonts[7] = false;
		
		$arrFontName[8] = "Charis SIL Am";
		$arrFontStorage[8] = "CharisSILAm";
		$arrHasSubFonts[8] = false;

		$arrFontName[9] = "Charis SIL APac";
		$arrFontStorage[9] = "CharisSILAPac";
		$arrHasSubFonts[9] = false;

		$arrFontName[10] = "Charis SIL Cyr";
		$arrFontStorage[10] = "CharisSILCyr";
		$arrHasSubFonts[10] = false;

		$arrFontName[11] = "Charis SIL CyrE";
		$arrFontStorage[11] = "CharisSILCyrE";
		$arrHasSubFonts[11] = false;

		$arrFontName[12] = "Charis SIL Eur";
		$arrFontStorage[12] = "CharisSILEur";
		$arrHasSubFonts[12] = false;

		$arrFontName[12] = "Charis SIL Eur";
		$arrFontStorage[12] = "CharisSILEur";
		$arrHasSubFonts[12] = false;

		$arrFontName[13] = "Charis SIL Phon";
		$arrFontStorage[13] = "CharisSILPhon";
		$arrHasSubFonts[13] = false;
		
		$arrFontName[14] = "Charis SIL Viet";
		$arrFontStorage[14] = "CharisSILViet";
		$arrHasSubFonts[14] = false;
		
		$arrFontName[15] = "Andika Afr";
		$arrFontStorage[15] = "AndikaAfr";
		$arrHasSubFonts[15] = false;
		
		$arrFontName[16] = "Andika Am";
		$arrFontStorage[16] = "AndikaAm";
		$arrHasSubFonts[16] = false;
		
		$arrFontName[17] = "Andika APac";
		$arrFontStorage[17] = "AndikaAPac";
		$arrHasSubFonts[17] = false;

		$arrFontName[18] = "Andika Cyr";
		$arrFontStorage[18] = "AndikaCyr";
		$arrHasSubFonts[18] = false;

		$arrFontName[19] = "Andika CyrE";
		$arrFontStorage[19] = "AndikaCyrE";
		$arrHasSubFonts[19] = false;
		
		$arrFontName[20] = "Andika Eur";
		$arrFontStorage[20] = "AndikaEur";
		$arrHasSubFonts[20] = false;
		
		$arrFontName[21] = "Andika Phon";
		$arrFontStorage[21] = "AndikaPhon";
		$arrHasSubFonts[21] = false;

		$arrFontName[22] = "Andika Viet";
		$arrFontStorage[22] = "AndikaViet";
		$arrHasSubFonts[22] = false;
		
		$arrFontName[23] = "Doulos SIL";
		$arrFontStorage[23] = "DoulosSIL";
		$arrHasSubFonts[23] = true;

		$arrFontName[24] = "Doulos SIL Afr";
		$arrFontStorage[24] = "DoulosSILAfr";
		$arrHasSubFonts[24] = false;

		$arrFontName[25] = "Doulos SIL Am";
		$arrFontStorage[25] = "DoulosSILAm";
		$arrHasSubFonts[25] = false;

		$arrFontName[26] = "Doulos SIL APac";
		$arrFontStorage[26] = "DoulosSILAPac";
		$arrHasSubFonts[26] = false;

		$arrFontName[27] = "Doulos SIL Cyr";
		$arrFontStorage[27] = "DoulosSILCyr";
		$arrHasSubFonts[27] = false;

		$arrFontName[28] = "Doulos SIL CyrE";
		$arrFontStorage[28] = "DoulosSILCyrE";
		$arrHasSubFonts[28] = false;

		$arrFontName[29] = "Doulos SIL Eur";
		$arrFontStorage[29] = "DoulosSILEur";
		$arrHasSubFonts[29] = false;
		
		$arrFontName[30] = "Doulos SIL Phon";
		$arrFontStorage[30] = "DoulosSILPhon";
		$arrHasSubFonts[30] = false;

		$arrFontName[31] = "Doulos SIL Viet";
		$arrFontStorage[31] = "DoulosSILViet";
		$arrHasSubFonts[31] = false;

		$arrFontName[32] = "Annapurna SIL";
		$arrFontStorage[32] = "AnnapurnaSIL";
		$arrHasSubFonts[32] = false;
		
		$arrFontName[33] = "Annapurna SIL Nepal";
		$arrFontStorage[33] = "AnnapurnaSILNepal";
		$arrHasSubFonts[33] = false;

		$arrFontName[34] = "Abyssinica SIL";
		$arrFontStorage[34] = "AbyssinicaSIL-R";
		$arrHasSubFonts[34] = false;
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
		$arrSystemFonts = array("Arial", "Arial Black", "Helvetica", "Times New Roman");
		
		return  $arrSystemFonts;
	}

	//////////////////////
	// creates custom.css
	//////////////////////
	public function set_fontFaces($file = "configured.css", $uploadPath)
	{
		$css_string = file_get_contents($file);
		$arrUniqueCSSFonts = $this->get_fonts_fromCssText($css_string);
		
		$this->getFontsAvailable($arrName, $arrStorage, $arrHasSubFonts);
		$arrFontName = $arrName;
		$arrFontStorage = $arrStorage;
		 
		$fontFace = "";
		$arrFontStyles = array("R", "B", "I", "BI");
		if(count($arrUniqueCSSFonts) > 0)
		{
			foreach($arrUniqueCSSFonts as $userFont)
			{
				$fontKey = array_search($userFont, $arrFontName);
					
				if($fontKey > 0)
				{
					foreach($arrFontStyles as $fontStyle)
					{
						//echo WP_CONTENT_DIR . "/uploads/font/" . $arrFontStorage[$fontKey] . "-" . $fontStyle . ".woff\n";
						if(file_exists(WP_CONTENT_DIR . "/uploads/fonts/" . $arrFontStorage[$fontKey] . "-" . $fontStyle . ".woff"))
						{
							$fontFace .= "@font-face {\n";
							$fontFace .= "font-family: " . $userFont . ";\n";
							$fontFace .= "src: url(/wp-content/uploads/fonts/" . $arrFontStorage[$fontKey] . "-" . $fontStyle . ".woff);\n";
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