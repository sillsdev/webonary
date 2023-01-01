<?php

class Webonary_Dashboard_Widget extends WP_Widget
{
	private static array $publication_status = [
		'No status set',
		'Rough draft',
		'Self-reviewed draft',
		'Community-reviewed draft',
		'Consultant approved',
		'Finished (no formal publication)',
		'Formally published'
	];

	public static function OutputDashboardWidget()
	{
		$upload_status = self::GetImportStatus();
		$publication_status = self::$publication_status[intval(get_option('publicationStatus', 0))];
		$backend = IS_CLOUD_BACKEND ? 'Cloud backend' : 'WordPress backend';

		echo <<<HTML
<div style="padding:0 3px">
	<p>
		Webonary provides the administration tools and framework for using WordPress for dictionaries. 
		See <a href="https://www.webonary.org/help" target="_blank">Webonary Support</a> for help.
	</p>
	<div style="max-width: 600px; border-style:solid; border-width: 1px; border-color: red; padding: 5px;">
		<p style="font-weight: 700; margin: 0.5rem 0">Upload Status:</p>
		$upload_status
	</div>
	<p>Publication status:&ensp;$publication_status</p>
	<p>$backend</p>
</div>
HTML;
	}

	private static function GetImportStatus(): string
	{
		if (IS_CLOUD_BACKEND) {

			$dictionary = Webonary_Cloud::getDictionary();

			if (is_null($dictionary))
				return '<p style="font-weight:700">No dictionary data found.</p>';

			$import_status = [
				'Last Upload:&ensp;<em>' . $dictionary->updatedAt . '</em>',
				'Main Language (' . $dictionary->mainLanguage->lang . ') entries:&ensp;<em>' . number_format($dictionary->mainLanguage->entriesCount) . '</em>',
			];

			foreach ($dictionary->reversalLanguages as $reversal) {

				$reversal_lang = 'Reversal Language (' . $reversal->lang . ')';
				if (isset($reversal->entriesCount) && $reversal->entriesCount)
					$reversal_lang .= ' entries:&ensp;<em>'. number_format($reversal->entriesCount) . '</em>';

				$import_status[] = $reversal_lang;
			}


			return '<p>' . implode('<br>' . PHP_EOL, $import_status) . '</p>';
		}
		else {

			return Webonary_Info::import_status();
		}
	}
}