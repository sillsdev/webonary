<?php

namespace Deployer;


require 'recipe/common.php';

// Project name
set('application', 'webonary.com');


// Project repository
set('repository', 'https://github.com/sillsdev/webonary.git');


// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);


// Shared files/dirs between deploys
set('shared_files', []);
set('shared_dirs', ['uploads', 'blogs.dir']);


set('composer_options', 'install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');


// Hosts
set('default_stage', 'test');
inventory('hosts.yml');


task('deploy:wp_config_symlink', function () {

	// create link to the shared wp_config.php file
	run("ln -sf {{deploy_path}}/shared/wp-config.php {{release_path}}/wordpress/wp-config.php");
	run("ln -sf {{deploy_path}}/shared/.htaccess {{release_path}}/wordpress/.htaccess");

})->desc('Creating symlink to wordpress config file.');

task('deploy:uploads_symlink', function () {

	// create link to the uploads directory
	run("ln -sfn {{deploy_path}}/shared/uploads/ {{release_path}}/wordpress/wp-content/uploads");
	run("ln -sfn {{deploy_path}}/shared/blogs.dir/ {{release_path}}/wordpress/wp-content/blogs.dir");

})->desc('Creating symlink to the uploads shared directory.');


desc('Deploy your project');
task('deploy', [
	'deploy:info',
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:vendors',
	'deploy:shared',
	'deploy:wp_config_symlink',
	'deploy:uploads_symlink',
	'deploy:symlink',
	'deploy:unlock',
	'cleanup',
	'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
