<?php

class Webonary_Parts_Of_Speech
{
	/** @var ICloudPartOfSpeech[] */
	private static ?array $parts = null;

	private static ?array $parts_of_speech_selected = null;


	/** @noinspection JSJQueryEfficiency */
	public static function GetDropdown(): string
	{
		$counter = 1;
		$all_parts = __('All Parts of Speech', 'sil_dictionary');
		/** @noinspection HtmlUnknownAttribute */
		$template = '<div class="pos-entry"><input type="checkbox" name="pos" id="pos-%1$s" class="form-check form-check-input me-2 pos-check" value="%2$s" %3$s>&nbsp;<label for="pos-%1$s">%4$s</label></div>';
		$options = [];

		$value = '';
		$selected_values = self::GetPartsOfSpeechSelected();
		$selected_text = [];

		$checked = (empty($selected_values) || in_array($value, $selected_values)) ? 'checked="checked"' : '';
		$options[] = sprintf($template, $counter, $value, $checked, $all_parts);
		if ($checked)
			$selected_text[] = $all_parts;

		self::GetPartsOfSpeechForLanguage($counter, $options, self::GetCurrentLanguageCode(), $template);

		// if the list for the current language is empty, choose the first language
		$parts = self::GetPartsOfSpeech();
		if (count($options) == 1 && !empty($parts)) {

			$language = $parts[0]->lang ?? '';

			if ($language != '')
				self::GetPartsOfSpeechForLanguage($counter, $options, $language, $template);
		}

		foreach ($parts as $part) {

			if (is_null($part->abbreviation))
				continue;

			if (in_array($part->abbreviation, $selected_values))
				$selected_text[] = $part->name;
		}

		// if no parts of speech found, return nothing
		if (count($options) == 1)
			return '';

		$option_str = implode(PHP_EOL, $options);

		$button_text = implode(' | ', $selected_text);

		/** @noinspection JSPotentiallyInvalidUsageOfThis */
		return <<<HTML
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {

        jQuery('.pos-check').on('click', function() {

            // un-check options that are excluded by the selected option
            if (this.checked) {
                if (this.id === 'pos-1') {
                    // un-check everything else if 'All parts of speech' was selected
                    jQuery('.pos-check:checked').not(this).prop('checked', false);
                }
                else {
                    // un-check 'All parts of speech' if something else was selected
                    jQuery('#pos-1:checked').prop('checked', false);
                }
            }

            if (!jQuery('.pos-check:checked').length)
                jQuery('#pos-1').prop('checked', true);

            // show the selected options
            let names = [];
            jQuery('.pos-check:checked').each(function() {
                names.push(jQuery('label[for="' + this.id + '"]').text());
            })

			document.getElementById('pos-option').innerHTML = names.join(' | ');

        });
	});

    function toggleDropdown() {

        let list = jQuery('.pos-list');
        let is_visible = list.is(':visible');

        if (!is_visible) {
        	list.css('top', 'unset');
            list.css('left', 'unset');
            list.css('max-height', '500px');
        }

        list.toggle();

        if (is_visible)
            return;

        if (window.innerWidth > 699) {

            // layout for desktop and tablet
            list.css('position', 'absolute');

			let rect = document.getElementById('pos-select').getBoundingClientRect();
            let admin_bar = document.getElementById('wpadminbar');
            let admin_height = admin_bar ? admin_bar.offsetHeight : 0;
			let win_height = window.innerHeight;
			let space_below = win_height - rect.bottom;
			let space_above = rect.top - admin_height;

            let height = 500;
			let top = rect.height;

            // if the space below is greater than 500, all is good
			if (space_below > 500) {
                list.css('top', top.toString() + '.px');
                return;
			}

			if (space_above > space_below) {

                // put the list above the select element, there is more space above
				height = space_above - 6;
				top = (space_above - 6) * (-1);
			}
			else {

                // put the list below the select element, there is more space below
				height = space_below - 6;
			}

			if (height > 500)
					height = 500;

			list.css('max-height', height.toString() + 'px');
			list.css('top', top.toString() + 'px');
        }
        else {

            // layout for mobile
            list.css('position', 'fixed');

			let win_height = window.innerHeight;

            list.css('max-height', (win_height - 30).toString() + 'px');

            let rect = document.getElementById('pos-list').getBoundingClientRect();
            let top = (win_height - rect.height) / 2;
            let left = (window.innerWidth - rect.width) / 2;
            list.css('top', top.toString() + 'px');
            list.css('left', left.toString() + 'px');
        }
    }

    // this event listener hides the list if the user clicks on something else
    window.addEventListener('click', function(evt) {

        if (evt.target['id'] === 'pos-cover') {
            toggleDropdown();
            return;
        }

        if (!document.getElementById('pos-list').contains(evt.target))
            jQuery('.pos-list').hide();
    });

    // this event listener hides the list if the user scrolls the document
    document.addEventListener('scroll', function(evt) {

        if (!document.getElementById('pos-list').contains(evt.target))
            jQuery('.pos-list').hide();
    });
</script>
<div class="pos-container">
	<div class="pos-select" id="pos-select">
		<select class="form-select webonary_searchform_pos_select"><option id="pos-option">$button_text</option></select>
		<div class="pos-cover" id="pos-cover"></div>
	</div>
	<div class="pos-list" id="pos-list" style="display: none">
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
				function($val) { return str_starts_with($val, 'pos='); }
			)
		);

		// remove 'tax=' from the part of speech values
		if (!empty($tax))
			$tax = array_map(function($val) { return urldecode(substr($val, 4)); }, $tax);

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
