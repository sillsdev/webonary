<?php
/**
 * Contains functions for various needs throughout the plugin
 *
 * @since 1.0
 */

/**
 * @param $path
 *
 * @since 1.0
 *
 * Load all classes in a specified directory
 */
function phpinfo_custom_autoloader( $path ) {
	/** @var $classes array - grab all php files from directory and include */
	$classes = glob( PHPINFO_DIR . $path . '/*.php' );
	foreach ( $classes as $class ) {
		if ( is_file( $class ) ) {
			include $class;
		}
	}
}

/**
 * @param $option - optional parameter for phpinfo()
 *
 * @return string
 *
 * @since 1.0
 *
 * Display phpinfo() function content
 */
function phpinfo_content( $option = 11 ) {
	ob_start();
	phpinfo( $option );
    $phpinfo = ob_get_clean();

    $html = new DomDocument;
	$html->preserveWhiteSpace = false;
	$html->loadHTML( $phpinfo );
	$body = $html->getElementsByTagName( 'body' );
	$tables = $html->getElementsByTagName( 'table' );

	foreach ( $tables as $table ) {
	    $table->setAttribute( 'class', 'wp-list-table widefat fixed striped php-info-table' );
    }

	foreach ( $body as $info ) :
        echo $info->ownerDocument->saveXML( $info );
    endforeach;
}

/**
 * @param int $option
 *
 * @return string
 *
 * Return phpinfo() instead of echoing it
 */
function get_phpinfo_content( $option = - 1 ) {
	ob_start();
	phpinfo_content( $option );

	return ob_get_clean();
}

function email_phpinfo_content( $emails, $redirect = false ) {
	$emails  = explode( ',', $emails );
	$info    = get_phpinfo_content();
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . get_bloginfo( 'name', 'display' ) . '<no-reply@' . $_SERVER['HTTP_HOST'] . '>'
	);

	foreach ( $emails as $email ) {
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			wp_mail(
				$email,
				'PHP Info for ' . get_bloginfo( 'name' ),
				$info,
				$headers
			);
		}
	}

	if ( $redirect ) {
		$redirect .= strpos( $redirect, '?' ) !== false ? '&phpinfo=emailsent' : '?phpinfo=emailsent';
		wp_safe_redirect( $redirect );
	}
}

/**
 * Check for email form submission nonce, verify and run email sending function
 */
function email_phpinfo_form_handler() {
	if ( isset( $_POST[ PHPINFO_PREFIX . '_nonce' ] )
	     && wp_verify_nonce( $_POST[ PHPINFO_PREFIX . '_nonce' ], PHPINFO_PREFIX . '_email_phpinfo' )
	) {
		email_phpinfo_content( $_POST['emails'], $_POST['_wp_http_referer'] );
	}
}