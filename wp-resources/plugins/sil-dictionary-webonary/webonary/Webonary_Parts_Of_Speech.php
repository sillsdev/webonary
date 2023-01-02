<?php

class Webonary_Parts_Of_Speech
{
	/** @var ICloudPartOfSpeech[] */
	private static ?array $parts = null;

	private static ?array $parts_of_speech_selected = null;


	public static function GetDropdown(): string
	{
		$counter = 1;
		$all_parts = __('All Parts of Speech', 'sil_dictionary');
		/** @noinspection HtmlUnknownAttribute */
		$template = '<div class="pos-entry"><input type="checkbox" name="tax" id="pos-%1$s" class="form-check form-check-input me-2 pos-check" value="%2$s" %3$s>&nbsp;<label for="pos-%1$s">%4$s</label></div>';
		$options = [];

		$value = '';
		$selected_values = self::GetPartsOfSpeechSelected();

		$checked = (empty($selected_values) || in_array($value, $selected_values)) ? 'checked="checked"' : '';
		$options[] = sprintf($template, $counter, $value, $checked, $all_parts);

		self::GetPartsOfSpeechForLanguage($counter, $options, self::GetCurrentLanguageCode(), $template);

		// if the list for the current language is empty, choose the first language
		$parts = self::GetPartsOfSpeech();
		if (count($options) == 1 && !empty($parts)) {

			$language = $parts[0]->lang ?? '';

			if ($language != '')
				self::GetPartsOfSpeechForLanguage($counter, $options, $language, $template);
		}

		// if no parts of speech found, return nothing
		if (count($options) == 1)
			return '';

		$option_str = implode(PHP_EOL, $options);

		$button_text = __('Parts of Speech', 'sil_dictionary');

		return <<<HTML
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
        
        // un-check options that are excluded by the selected option
        jQuery('.pos-check').on('click', function() {
            if (!this.checked)
                return;

            if (this.id === 'pos-1') {
                // un-check everything else if 'All parts of speech' was selected
                jQuery('.pos-check:checked').not(this).prop('checked', false);
            }
            else {
                // un-check 'All parts of speech' if something else was selected
                jQuery('#pos-1:checked').prop('checked', false);
            }
        });
	});
    
    function toggleDropdown(btn) {
        
        let list = jQuery('.pos-list');
        list.toggle();
        
        let is_visible = list.is(':visible');
        
        if (window.innerWidth > 699) {
            if (is_visible) {

                // we have to set the left position after the element is no longer hidden
                let rect = btn.getBoundingClientRect();
        		let list_left = rect.x - list[0].offsetWidth;
                list.css('left', list_left + 'px');
            }
        }
        else {
            list.css('left', 'unset')
        }
    }
</script>
<div class="pos-container">
	<div class="pos-select" onclick="toggleDropdown(this);">
		<select class="form-select"><option>$button_text</option></select>
		<div class="pos-cover"></div>
	</div>
	<div class="pos-list" style="display: none">
	    $option_str
    </div>
</div>
HTML;
	}

	/**
	 * Returns the selected parts of speech as an array of strings.
	 *
	 * NOTE: An empty array is 'All parts of speech'
	 *
	 * @return array
	 */
	public static function GetPartsOfSpeechSelected(): array
	{
		if (!is_null(self::$parts_of_speech_selected))
			return self::$parts_of_speech_selected;

		// NOTE: There may be multiple 'tax' values in the query string,
		//       but the $_GET[] array will only contain one.
		$tax = array_values(
			array_filter(
				explode('&', $_SERVER['QUERY_STRING']),
				function($val) { return str_starts_with($val, 'tax='); }
			)
		);

		// remove 'tax=' from the part of speech values
		if (!empty($tax))
			$tax = array_map(function($val) { return substr($val, 4); }, $tax);

		// with multi-select, it is possible there are no part of speech options selected
		if (empty($tax)) {
			self::$parts_of_speech_selected = [];
		}
		else {

			// with the MySQL backend, 'All parts of speech' has the value '-1'
			if (count($tax) == 1 && $tax[0] == '-1')
				self::$parts_of_speech_selected = [];
			else
				self::$parts_of_speech_selected = Webonary_Utility::RemoveEmptyStrings($tax);
		}

		return self::$parts_of_speech_selected;
	}

	private static function GetPartsOfSpeech(): array
	{
		if (!is_null(self::$parts))
			return self::$parts;

		if (IS_CLOUD_BACKEND) {
			self::$parts = Webonary_Cloud::getPartsOfSpeech();
		}
		else {
			self::$parts = self::GetPartsOfSpeechFromWordpress();
		}

		usort(self::$parts, function ($a, $b) {
			return strcasecmp($a->name, $b->name);
		});

		return self::$parts;
	}

	/**
	 * @return ICloudPartOfSpeech[]
	 */
	private static function GetPartsOfSpeechFromWordpress(): array
	{
		$list = get_terms('sil_parts_of_speech');

		if (is_a($list, WP_Error::class))
			return [];

		$return_val = [];

		/** @var WP_Term $item */
		foreach ($list as $item) {

			if (empty($item->term_id))
				continue;

			$part = [
				'abbreviation' => $item->term_id,
				'lang' => 'en',
				'name' => $item->name . '&ensp;(' . $item->count . ')',
				'guid' => $item->term_id
			];

			$return_val[] = (object)$part;
		}

		return $return_val;
	}

	private static function GetPartsOfSpeechForLanguage(int &$counter, array &$options, string $language, string $template): void
	{
		$parts = self::GetPartsOfSpeech();
		$selected_values = self::GetPartsOfSpeechSelected();

		foreach ($parts as $part) {

			if (is_null($part->abbreviation) || $part->lang != $language)
				continue;

			$counter++;
			$value = $part->abbreviation;
			$checked = in_array($value, $selected_values) ? 'checked="checked"' : '';
			$options[] = sprintf($template, $counter, $value, $checked, $part->name);
		}
	}

	private static function GetCurrentLanguageCode(): string
	{
		if (IS_CLOUD_BACKEND)
			return Webonary_Cloud::getCurrentLanguage();

		return 'en';
	}

	public function HasPartsOfSpeech(): bool
	{
		return !empty($this->parts);
	}
}