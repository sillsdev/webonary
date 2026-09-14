<?php

namespace SIL\WebonaryCreateSite2\Abstracts;

class AppFields
{
	public static array $Fields = [
		'template-to-use' => [
			'tag' => 'select',
			'src' => 'site_list',
			'label' => 'Choose Source Site to Copy'
		],
		'desired-url' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'New Site Address',
		],
		'language-name' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'New Site Title'
		],
		'language-iso-code' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'Ethnologue Code'
		],
		'country-name' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'Country'
		],
		'region' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'Region'
		],
		'copyright-holder' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'Copyright Holder Text',
			'class' => 'w-100'
		],
		'the-publication-status-of-the-dictionary' => [
			'tag' => 'select',
			'src' => [
				0 => '',
				1 => 'Rough draft',
				2 => 'Self-reviewed draft',
				3 => 'Community-reviewed draft',
				4 => 'Consultant approved',
				5 => 'Finished (no formal publication)',
				6 => 'Formally published',
			],
			'label' => 'Publication Status'
		],
		'allow-comments' => [
			'tag' => 'input',
			'type' => 'checkbox',
			'checked' => true,
			'label' => 'Allow Comments'
		],
		'from_email' => [
			'tag' => 'input',
			'type' => 'email',
			'label' => 'Admin Email'
		],
		'username' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'Username'
		],
		'FirstName' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'First Name'
		],
		'LastName' => [
			'tag' => 'input',
			'type' => 'text',
			'label' => 'Last Name'
		],
		'message' => [
			'tag' => 'label',
			'label' => 'Comments'
		],
		'ui-languages' => [
			'tag' => 'label',
			'label' => 'Display Languages',
			'info' => 'Need to be enabled manually'
		],
		'reversal-languages' => [],
	];
}
