<?php

class Webonary_Parts_Of_Speech
{
	private string $current_lang_code;

	/** @var ICloudPartOfSpeech[] */
	private array $parts;

	/** @var string[] */
	private array $selected_values;

	private static ?array $parts_of_speech_selected = null;

	/**
	 * @param string $current_lang_code
	 * @param ICloudPartOfSpeech[]|null $parts
	 * @param array $selected_values
	 */
	public function __construct(string $current_lang_code, ?array $parts, array $selected_values)
	{
		$this->current_lang_code = $current_lang_code;
		$this->selected_values = $selected_values;

		if (is_null($parts)) {
			$this->parts = $this->GetPartsOfSpeechFromWordpress();
		} else {
			$this->parts = $parts;

			// pre-sort the list
			usort($this->parts, function ($a, $b) {
				return strcasecmp($a->name, $b->name);
			});
		}
	}

	public function GetDropdown(): string
	{
		$counter = 1;
		$all_parts = __('All Parts of Speech', 'sil_dictionary');
		$template = '<div class="pos-entry"><input type="checkbox" name="tax" id="pos-%1$s" class="form-control mr-2 pos-check" value="%2$s" %3$s>&nbsp;<label for="pos-%1$s">%4$s</label></div>';
		$options = [];

		$value = '';
		$checked = (empty($this->selected_values) || in_array($value, $this->selected_values)) ? 'checked="checked"' : '';
		$options[] = sprintf($template, $counter, $value, $checked, $all_parts);

		$this->GetPartsOfSpeechForLanguage($counter, $options, $this->current_lang_code, $template);

		// if the list for the current language is empty, choose the first language
		if (count($options) == 1 && !empty($this->parts)) {

			$language = $this->parts[0]->lang ?? '';

			if ($language != '')
				$this->GetPartsOfSpeechForLanguage($counter, $options, $language, $template);
		}

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
</script>
<div class="pos-container">
	<button type="button" class="btn btn-dropdown" onclick="jQuery('.pos-list').toggle();">$button_text<span>&ensp;&#x25BE;</span></button>
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

	/**
	 * @return ICloudPartOfSpeech[]
	 */
	private function GetPartsOfSpeechFromWordpress(): array
	{
		$list = get_terms('sil_parts_of_speech');
		$return_val = [];

		/** @var WP_Term $item */
		foreach ($list as $item) {

			$part = [
				'abbreviation' => $item->term_id,
				'lang' => $this->current_lang_code,
				'name' => $item->name . '&ensp;(' . $item->count . ')',
				'guid' => $item->term_id
			];

			$return_val[] = (object)$part;
		}

		return $return_val;
	}

	private function GetPartsOfSpeechForLanguage(int &$counter, array &$options, string $language, string $template): void
	{
		foreach ($this->parts as $part) {

			if (is_null($part->abbreviation) || $part->lang != $language)
				continue;

			$counter++;
			$value = $part->abbreviation;
			$checked = in_array($value, $this->selected_values) ? 'checked="checked"' : '';
			$options[] = sprintf($template, $counter, $value, $checked, $part->name);
		}
	}

	public function HasPartsOfSpeech(): bool
	{
		return !empty($this->parts);
	}
}