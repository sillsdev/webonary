<?php
//This class has functions for reading out the font family names from a css file and writing the font faces into
//a custom css
class fontMonagment
{
	public function getFontsAvailable()
	{
		$arrFont["name"][0] = null;
		$arrFont["filename"][0] = null;
		$arrFont["hasSubFonts"][0] = null;

		$arrFont["name"][1] = "Charis SIL";
		$arrFont["filename"][1] = "CharisSIL";
		$arrFont["hasSubFonts"][1] = true;

		$arrFont["name"][2] = "Charis SIL Compact";
		$arrFont["filename"][2] = "CharisSIL";
		$arrFont["hasSubFonts"][2] = true;

		$arrFont["name"][3] = "Andika";
		$arrFont["filename"][3] = "Andika";
		$arrFont["hasSubFonts"][3] = true;

		$arrFont["name"][4] = "Andika Compact";
		$arrFont["filename"][4] = "Andika";
		$arrFont["hasSubFonts"][4] = true;

		$arrFont["name"][5] = "Ezra SIL";
		$arrFont["filename"][5] = "EzraSIL";
		$arrFont["hasSubFonts"][5] = false;

		$arrFont["name"][6] = "Galatia SIL";
		$arrFont["filename"][6] = "GalatiaSIL";
		$arrFont["hasSubFonts"][6] = false;

		$arrFont["name"][7] = "Charis SIL Afr";
		$arrFont["filename"][7] = "CharisSILAfr";
		$arrFont["hasSubFonts"][7] = false;

		$arrFont["name"][8] = "Charis SIL Am";
		$arrFont["filename"][8] = "CharisSILAm";
		$arrFont["hasSubFonts"][8] = false;

		$arrFont["name"][9] = "Charis SIL APac";
		$arrFont["filename"][9] = "CharisSILAPac";
		$arrFont["hasSubFonts"][9] = false;

		$arrFont["name"][10] = "Charis SIL Cyr";
		$arrFont["filename"][10] = "CharisSILCyr";
		$arrFont["hasSubFonts"][10] = false;

		$arrFont["name"][11] = "Charis SIL CyrE";
		$arrFont["filename"][11] = "CharisSILCyrE";
		$arrFont["hasSubFonts"][11] = false;

		$arrFont["name"][12] = "Charis SIL Eur";
		$arrFont["filename"][12] = "CharisSILEur";
		$arrFont["hasSubFonts"][12] = false;

		$arrFont["name"][12] = "Charis SIL Eur";
		$arrFont["filename"][12] = "CharisSILEur";
		$arrFont["hasSubFonts"][12] = false;

		$arrFont["name"][13] = "Charis SIL Phon";
		$arrFont["filename"][13] = "CharisSILPhon";
		$arrFont["hasSubFonts"][13] = false;

		$arrFont["name"][14] = "Charis SIL Viet";
		$arrFont["filename"][14] = "CharisSILViet";
		$arrFont["hasSubFonts"][14] = false;

		$arrFont["name"][15] = "Andika Afr";
		$arrFont["filename"][15] = "AndikaAfr";
		$arrFont["hasSubFonts"][15] = false;

		$arrFont["name"][16] = "Andika Am";
		$arrFont["filename"][16] = "AndikaAm";
		$arrFont["hasSubFonts"][16] = false;

		$arrFont["name"][17] = "Andika APac";
		$arrFont["filename"][17] = "AndikaAPac";
		$arrFont["hasSubFonts"][17] = false;

		$arrFont["name"][18] = "Andika Cyr";
		$arrFont["filename"][18] = "AndikaCyr";
		$arrFont["hasSubFonts"][18] = false;

		$arrFont["name"][19] = "Andika CyrE";
		$arrFont["filename"][19] = "AndikaCyrE";
		$arrFont["hasSubFonts"][19] = false;

		$arrFont["name"][20] = "Andika Eur";
		$arrFont["filename"][20] = "AndikaEur";
		$arrFont["hasSubFonts"][20] = false;

		$arrFont["name"][21] = "Andika Phon";
		$arrFont["filename"][21] = "AndikaPhon";
		$arrFont["hasSubFonts"][21] = false;

		$arrFont["name"][22] = "Andika Viet";
		$arrFont["filename"][22] = "AndikaViet";
		$arrFont["hasSubFonts"][22] = false;

		$arrFont["name"][23] = "Doulos SIL";
		$arrFont["filename"][23] = "DoulosSIL";
		$arrFont["hasSubFonts"][23] = true;

		$arrFont["name"][24] = "Doulos SIL Afr";
		$arrFont["filename"][24] = "DoulosSILAfr";
		$arrFont["hasSubFonts"][24] = false;

		$arrFont["name"][25] = "Doulos SIL Am";
		$arrFont["filename"][25] = "DoulosSILAm";
		$arrFont["hasSubFonts"][25] = false;

		$arrFont["name"][26] = "Doulos SIL APac";
		$arrFont["filename"][26] = "DoulosSILAPac";
		$arrFont["hasSubFonts"][26] = false;

		$arrFont["name"][27] = "Doulos SIL Cyr";
		$arrFont["filename"][27] = "DoulosSILCyr";
		$arrFont["hasSubFonts"][27] = false;

		$arrFont["name"][28] = "Doulos SIL CyrE";
		$arrFont["filename"][28] = "DoulosSILCyrE";
		$arrFont["hasSubFonts"][28] = false;

		$arrFont["name"][29] = "Doulos SIL Eur";
		$arrFont["filename"][29] = "DoulosSILEur";
		$arrFont["hasSubFonts"][29] = false;

		$arrFont["name"][30] = "Doulos SIL Phon";
		$arrFont["filename"][30] = "DoulosSILPhon";
		$arrFont["hasSubFonts"][30] = false;

		$arrFont["name"][31] = "Doulos SIL Viet";
		$arrFont["filename"][31] = "DoulosSILViet";
		$arrFont["hasSubFonts"][31] = false;

		$arrFont["name"][32] = "Annapurna SIL";
		$arrFont["filename"][32] = "AnnapurnaSIL";
		$arrFont["hasSubFonts"][32] = false;

		$arrFont["name"][33] = "Annapurna SIL Nepal";
		$arrFont["filename"][33] = "AnnapurnaSILNepal";
		$arrFont["hasSubFonts"][33] = false;

		$arrFont["name"][34] = "Abyssinica SIL";
		$arrFont["filename"][34] = "AbyssinicaSIL";
		$arrFont["hasSubFonts"][34] = false;

		$arrFont["name"][35] = "Scheherazade";
		$arrFont["filename"][35] = "Scheherazade";
		$arrFont["hasSubFonts"][35] = false;

		$arrFont["name"][36] = "Padauk";
		$arrFont["filename"][36] = "Padauk";
		$arrFont["hasSubFonts"][36] = false;

		$arrFont["name"][37] = "Nokyung";
		$arrFont["filename"][37] = "Nokyung.ttf";
		$arrFont["hasSubFonts"][37] = false;

		$arrFont["name"][38] = "Taogu";
		$arrFont["filename"][38] = "Taogu.ttf";
		$arrFont["hasSubFonts"][38] = false;

		$arrFont["name"][39] = "BJCree UNI";
		$arrFont["filename"][39] = "Bycrus";
		$arrFont["hasSubFonts"][39] = false;

		$arrFont["name"][40] = "Taogu-OT";
		$arrFont["filename"][40] = "Taogu-OT.ttf";
		$arrFont["hasSubFonts"][39] = false;

		$arrFont["name"][41] = "Gentium Plus";
		$arrFont["filename"][41] = "GentiumPlus";
		$arrFont["hasSubFonts"][41] = true;

		$arrFont["name"][42] = "Gentium Plus Afr";
		$arrFont["filename"][42] = "Gentium PlusAfr";
		$arrFont["hasSubFonts"][42] = false;

		$arrFont["name"][43] = "Gentium Plus Am";
		$arrFont["filename"][43] = "Gentium PlusAm";
		$arrFont["hasSubFonts"][43] = false;

		$arrFont["name"][44] = "Gentium Plus APac";
		$arrFont["filename"][44] = "GentiumPlusAPac";
		$arrFont["hasSubFonts"][44] = false;

		$arrFont["name"][45] = "Gentium Plus Cyr";
		$arrFont["filename"][45] = "GentiumPlusCyr";
		$arrFont["hasSubFonts"][45] = false;

		$arrFont["name"][46] = "Gentium Plus CyrE";
		$arrFont["filename"][46] = "GentiumPlusCyrE";
		$arrFont["hasSubFonts"][46] = false;

		$arrFont["name"][47] = "Gentium Plus Eur";
		$arrFont["filename"][47] = "GentiumPlusEur";
		$arrFont["hasSubFonts"][47] = false;

		$arrFont["name"][48] = "Gentium Plus Phon";
		$arrFont["filename"][48] = "GentiumPlusPhon";
		$arrFont["hasSubFonts"][48] = false;

		$arrFont["name"][49] = "Gentium Plus Viet";
		$arrFont["filename"][49] = "GentiumPlusViet";
		$arrFont["hasSubFonts"][49] = false;

		$arrFont["name"][50] = "Sawndip";
		$arrFont["filename"][50] = "Sawndip";
		$arrFont["hasSubFonts"][50] = false;

		$arrFont["name"][51] = "Hispa";
		$arrFont["filename"][51] = "Hispa.ttf";
		$arrFont["hasSubFonts"][51] = false;

		$arrFont["name"][52] = "AndikaNewBasicW";
		$arrFont["filename"][52] = "AndikaNewBasic";
		$arrFont["hasSubFonts"][52] = false;

		$arrFont["name"][53] = "Lateef Lateef KasLow";
		$arrFont["filename"][53] = "LateefRegOT-LateefKasLow.ttf";
		$arrFont["hasSubFonts"][53] = false;

		$arrFont["name"][54] = "aUI";
		$arrFont["filename"][54] = "aUI";
		$arrFont["hasSubFonts"][54] = false;

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
				$fontKey = array_search($userFont, $arrFont["name"]);

				if($fontKey > 0)
				{
					foreach($arrFonttyles as $fontStyle)
					{
						//echo WP_CONTENT_DIR . "/uploads/font/" . $arrFonttorage[$fontKey] . "-" . $fontStyle . ".woff\n";
						$extension = ".woff";
						if(strpos($arrFont["filename"][$fontKey], ".ttf") > 0)
						{
							$arrFont["filename"][$fontKey] = str_replace(".ttf", "", $arrFont["filename"][$fontKey]);
							$extension = ".ttf";
						}

						if(file_exists(WP_CONTENT_DIR . "/uploads/fonts/" . $arrFont["filename"][$fontKey] . "-" . $fontStyle . $extension))
						{
							$fontFace .= "@font-face {\n";
							$fontFace .= "font-family: " . $userFont . ";\n";
							$fontFace .= "src: url(/wp-content/uploads/fonts/" . $arrFont["filename"][$fontKey] . "-" . $fontStyle . $extension . ");\n";
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