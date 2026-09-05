<?php

namespace SIL\WebonaryCreateSite2\Models;

use wpdb;

class Application
{
	public string $FirstName;
	public string $LastName;
	public string $UserName;
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
	public string $Status = 'Unknown';

	private static array $field_map = [
		'FirstName' => 'FirstName',
		'LastName' => 'LastName',
		'username' => 'UserName',
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
		'i-have-read-the-terms-of-service' => 'HasReadTOS',
	];

	public function __construct(array $fields = null, string $submit_time = null)
	{
		if (!empty($fields)) {
			$this->Timestamp = floor($submit_time);
			$this->ID = $submit_time;
			$status = null;

			foreach ($fields as $field) {

				$field_name = self::$field_map[$field->field_name] ?? false;

				if ($field_name === false) {

					if ($field->field_name == 'newapplication')
						$status = 'New Application';
					elseif ($field->field_name == 'removed')
						$status = 'Removed';
					elseif ($field->field_name == 'created')
						$status = 'Created';

					continue;
				}

				$this->$field_name = $field->field_value;
			}

			if (isset($status))
				$this->Status = $status;
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

		if ($property_name == 'UserName')
			return $this->GuessUsername();

		return $this->$property_name;
	}

	private function GuessUsername(): string
	{
		/** @var $wpdb wpdb */
		global $wpdb;

		// check for user
		$admin_email = $this->GetFieldValue('from_email');
		if (mb_strlen($admin_email) > 5) {
			$user_name = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM $wpdb->users WHERE user_email = %s", $admin_email));
			if (!empty($user_name))
				return $user_name;
		}

		$first_name = $this->GetFieldValue('FirstName') ?? '';
		$last_name = $this->GetFieldValue('LastName') ?? '';
		$user_name = str_replace(' ', '', strtolower($first_name . $last_name));

		if (empty($user_name))
			$user_name = str_replace(' ', '', strtolower(explode('@', $admin_email)[0]));

		if (!empty($user_name))
			$other_email = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM $wpdb->users WHERE user_login = %s", $user_name));

		// if $other_email is not empty, this user name already exists for someone else
		return empty($other_email) ? $user_name : '';
	}

	private function MarkApplication(string $mark_as): void
	{
		global $wpdb;

		$sql = <<<SQL
UPDATE {$wpdb->base_prefix}cf7dbplugin_submits
SET field_name = '$mark_as'
WHERE field_name = 'newapplication' AND submit_time = %f
SQL;
		$sql = $wpdb->prepare($sql, $this->ID);
		$wpdb->query($sql);
	}

	public function MarkRemoved(): void
	{
		$this->MarkApplication('removed');
	}

	public function MarkCreated(): void
	{
		$this->MarkApplication('created');
	}
}
