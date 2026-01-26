<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Deployer;

require 'recipe/common.php';

import('hosts.yml');

set('application', 'webonary');
set('repository', 'https://github.com/sillsdev/webonary.git');
set('cleanup_use_sudo', false);
set('keep_releases', 5);

// Shared files/dirs between deploys
set('writable_dirs', ['cache', 'blogs.dir', 'uploads']);
set('shared_files', ['.htaccess', 'wp-config.php', 'wp-cache-config.php']);
set('shared_dirs', ['cache', 'blogs.dir', 'uploads']);
set('clear_paths', ['cache/*']);

desc('Creating shared directories and files.');
task('sil:shared', function () {

	$release_path = "{{release_path}}";
	$shared_path = "{{deploy_path}}/shared";
	$shared_dirs = get('shared_dirs');

	foreach ($shared_dirs as $dir) {

		$shared_dir_path = "$shared_path/$dir";

		if (test("[ -d $shared_dir_path ]"))
			continue;

		// Create shared dir if it does not exist.
		run("mkdir -p $shared_dir_path");

		// If release contains shared dir, copy that dir from release to shared.
		if (test("[ -d $release_path/$dir ]"))
			run("cp -rv $release_path/$dir $shared_dir_path");
	}

	$shared_files = get('shared_files');

	foreach ($shared_files as $file) {

		$shared_file_path = "$shared_path/$file";

		if (test("[ -f $shared_file_path ]"))
			continue;

		run("touch $shared_file_path");
	}
});

desc('Creating symlinks.');
task('sil:symlink_shared', function () {

	// files
	run("ln -sf {{release_path}}/wordpress/wp-content/plugins/wp-super-cache/advanced-cache.php {{release_path}}/wordpress/advanced-cache.php");
	run("ln -sf {{deploy_path}}/shared/.htaccess {{release_path}}/wordpress/.htaccess");
	run("ln -sf {{deploy_path}}/shared/wp-config.php {{release_path}}/wordpress/wp-config.php");
	run("ln -sf {{deploy_path}}/shared/wp-cache-config.php {{release_path}}/wordpress/wp-content/wp-cache-config.php");

	// directories
	run("ln -sfn {{deploy_path}}/shared/uploads/ {{release_path}}/wordpress/wp-content/uploads");
	run("ln -sfn {{deploy_path}}/shared/blogs.dir/ {{release_path}}/wordpress/wp-content/blogs.dir");
	run("ln -sfn {{deploy_path}}/shared/cache/ {{release_path}}/cache");
});

desc('Installs vendors');
task('sil:vendors', function () {
	run('cd {{release_or_current_path}} && COMPOSER=composer-deploy.json {{bin/composer}} {{composer_action}} {{composer_options}} 2>&1');
});

desc('Creating symlink to wordpress internal file.');
task('sil:wp_internal_symlink', function () {

	// create link to files installed by composer
	run("ln -sf {{release_path}}/wordpress/wp-content/plugins/wp-super-cache/advanced-cache.php {{release_path}}/wordpress/wp-content/advanced-cache.php");

});

desc('Unlock deploy and let the CI agent know it failed');
task('sil:unlock_and_fail', function () {
	run("rm -f {{deploy_path}}/.dep/deploy.lock");
	print "\n\n=============\n";
	print "DEPLOY FAILED\n";
	print "=============\n\n";
	exit(1);
});

desc('Removing file not needed.');
task('sil:clean_files', function () {

	run("rm -f {{release_path}}/deploy");
	run("rm -f {{release_path}}/makefile");
	run("rm -f {{release_path}}/*.md");
	run("rm -f {{release_path}}/.gitignore");
	run("rm -f {{release_path}}/composer*.*");

	run("rm -rf {{release_path}}/deployer");
	run("rm -rf {{release_path}}/shared");
	run("rm -rf {{release_path}}/webonary-cloud-api");
	run("rm -rf {{release_path}}/.git");
});


desc('Deploy your project');
task('deploy', [
	'deploy:info',
	'deploy:setup',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'sil:vendors',
	'sil:shared',
	'sil:symlink_shared',
	'deploy:writable',
	'sil:wp_internal_symlink',
	'sil:clean_files',
	'deploy:symlink',
	'deploy:unlock',
	'cleanup',
	'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'sil:unlock_and_fail');
