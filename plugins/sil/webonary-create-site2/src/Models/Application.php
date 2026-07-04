<?php

namespace SIL\WebonaryCreateSite2\Models;

class Application
{
	public string $FirstName;
	public string $LastName;
	public string $FromEmail;
	public string $LanguageName;
	public string $IsoCode;
	public string $CountryName;
	public string $RegionName;
	public string $DesiredUrl;
	public string $TemplateToUse;
    public string $UiLanguages;
	public string $ReversalLanguages;
	public string $PublicationStatus;
	public string $Message;
	public string $CopyrightHolder;
	public string $HasImagePermission;
	public string $HasReadTOS;
	public int $Timestamp;
	public ?string $ID;

	private static array $field_map = [
		'FirstName' => 'FirstName',
		'LastName' => 'LastName',
		'from_email' => 'FromEmail',
		'language-name' => 'LanguageName',
		'language-iso-code' => 'IsoCode',
		'country-name' => 'CountryName',
		'region' => 'RegionName',
		'desired-url' => 'DesiredUrl',
		'template-to-use' => 'TemplateToUse',
		'ui-languages' => 'UiLanguages',
		'reversal-languages' => 'ReversalLanguages',
		'the-publication-status-of-the-dictionary' => 'PublicationStatus',
		'message' => 'Message',
		'copyright-holder' => 'CopyrightHolder',
		'This-dictionary-has-pictures-and-I-have' => 'HasImagePermission',
		'i-have-read-the-terms-of-service' => 'HasReadTOS'
	];

	public function __construct(array $fields = null, string $submit_time = null)
	{
		if (!empty($fields)) {
			$this->Timestamp = floor($submit_time);
			$this->ID = $submit_time;

			foreach ($fields as $field) {

				$field_name = self::$field_map[$field->field_name] ?? false;

				if ($field_name === false)
					continue;

				$this->$field_name = $field->field_value;
			}
		}
		else {
			$this->Timestamp = 0;
			$this->ID = null;

			foreach(self::$field_map as $field_name) {
				$this->$field_name = '';
			}
		}
	}

	/**
	 * Gets the value from the field name on the form.
	 * __The form contains field names that aren't valid property names in PHP.__
	 *
	 * @param string $field_name The name of the field on the form
	 * @return string|null
	 */
	public function GetFieldValue(string $field_name): ?string
	{
		if (!array_key_exists($field_name, self::$field_map))
			return null;

		$property_name = self::$field_map[$field_name];

		return $this->$property_name;
	}

	public function MarkRemoved(): void
	{
		global $wpdb;

		$sql = <<<SQL
UPDATE {$wpdb->prefix}cf7dbplugin_submits
SET field_name = 'removed'
WHERE field_name LIKE 'newapplication' AND submit_time = %f
SQL;
		$sql = $wpdb->prepare($sql, $this->ID);
		$wpdb->query($sql);
	}
}
