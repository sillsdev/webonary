# Notes On Unit Testing Webonary

### Requirements

The `intl` extension for PHP is required:

```bash
sudo apt install php7.3-intl
```


### Initializing

To get the script for installing the unit test framework and other files, run this command:

```bash
wp scaffold plugin-tests sil-dictionary-webonary
```

Add these environment variables, changing the directory location as needed. On Linux add them to `.bash_profile`

```
export WP_CORE_DIR="/var/www/projects/wordpress-develop"
export WP_TESTS_DIR="/var/www/projects/wordpress-tests-lib"
```

Make sure the `WP_CORE_DIR` and `WP_TESTS_DIR` directories, and the test database do NOT exist before running the next command.

Run this command to download and install the WordPress unit testing framework:

```bash
./bin/install-wp-tests.sh <test_database_name> <db_user_name> <db_password> localhost latest
```

Edit `./tests/bootstrap.php`, changing the `_manually_load_plugin` function to the following:

```php
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/sil-dictionary.php';

    global $wpdb;

    remove_entries();

    $sql = "ALTER DATABASE " . $wpdb->dbname .
           " CHARACTER SET utf8mb4 " .
           " COLLATE utf8mb4_general_ci";

    $wpdb->query($sql);

    create_custom_relevance();
    create_search_tables();
    create_reversal_tables();
}
```

Edit `wp-tests-config.php`, changing the `ABSPATH` value to the Webonary wordpress directory:

```php
define( 'ABSPATH', '/var/www/projects/webonary2/wordpress/' );
````

Edit `phpunit.xml.dist`, adding multi-site support:

```xml
<?xml version="1.0"?>
<phpunit
	bootstrap="tests/bootstrap.php"
	backupGlobals="false"
	colors="true"
	convertErrorsToExceptions="true"
	convertNoticesToExceptions="true"
	convertWarningsToExceptions="true"
	>
	<php>
		<const name="WP_TESTS_MULTISITE" value="1" />
	</php>
	<testsuites>
		<testsuite name="webonary">
			<directory prefix="test-" suffix=".php">./tests/</directory>
		</testsuite>
	</testsuites>
</phpunit>
```
