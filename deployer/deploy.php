<?php

namespace Deployer;


use Deployer\Exception\Exception;

require 'recipe/common.php';

// Project name
set('application', 'webonary.com');


// Project repository
set('repository', 'https://github.com/sillsdev/webonary.git');
set('keep_releases', 5);


// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);


// Shared files/dirs between deploys
set('shared_files', []);
set('shared_dirs', []);
set('shared_files_ex', [
	['.htaccess', 'wordpress/.htaccess'],
	['wp-config.php', 'wordpress/wp-config.php'],
	['wp-cache-config.php', 'wordpress/wp-content/wp-cache-config.php']
]);
set('shared_dirs_ex', [
	['uploads', 'wordpress/wp-content/uploads'],
	['blogs.dir', 'wordpress/wp-content/blogs.dir']
]);


set('composer_options', 'install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');


// Hosts
set('default_stage', 'test');
inventory('hosts.yml');


task('deploy:wp_internal_symlink', function () {

	// create link to files installed by composer
	run("ln -sf {{release_path}}/wordpress/wp-content/plugins/wp-super-cache/advanced-cache.php {{release_path}}/wordpress/wp-content/advanced-cache.php");

})->desc('Creating symlink to wordpress internal file.');


task('deploy:clean_files', function () {

	run("rm -f {{release_path}}/deploy");
	run("rm -f {{release_path}}/makefile");
	run("rm -f {{release_path}}/*.md");
	run("rm -f {{release_path}}/.gitignore");
	run("rm -f {{release_path}}/composer*.*");

	run("rm -rf {{release_path}}/deployer");
	run("rm -rf {{release_path}}/shared");
	run("rm -rf {{release_path}}/webonary-cloud-api");
	run("rm -rf {{release_path}}/.git");

})->desc('Removing file not needed.');


desc('Creating symlinks for shared files and dirs using array instead of string');
task('deploy:shared_ex', function () {

	// NB: $dir is an array
	// $dir[0] is the name in $sharedPath
	// $dir[1] is the name in {{deploy_path}}

	$sharedPath = "{{deploy_path}}/shared";

	// Validate shared_dir, find duplicates
	foreach (get('shared_dirs_ex') as $a) {
		foreach (get('shared_dirs_ex') as $b) {
			if ($a[0] !== $b[0] && strpos(rtrim($a[0], '/') . '/', rtrim($b[0], '/') . '/') === 0) {
				if ($a[1] == $b[1]) {
					throw new Exception("Can not share same dirs `$a[1]` and `$b[1]`.");
				}
			}
		}
	}

	foreach (get('shared_dirs_ex') as $dir) {

		// Check if shared dir does not exist.
		if (!test("[ -d {$sharedPath}/{$dir[0]} ]")) {

			// Create shared dir if it does not exist.
			run("mkdir -p {$sharedPath}/{$dir[0]}");

			// If release contains shared dir, copy that dir from release to shared.
			if (test("[ -d $(echo {{release_path}}/{$dir[1]}) ]")) {
				run("cp -rv {{release_path}}/{$dir[1]} $sharedPath/" . dirname(parse($dir[0])));
			}
		}

		// Remove from source.
		run("rm -rf {{release_path}}/{$dir[1]}");

		// Create path to shared dir in release dir if it does not exist.
		// Symlink will not create the path and will fail otherwise.
		run("mkdir -p `dirname {{release_path}}/{$dir[1]}`");

		// Symlink shared dir to release dir
		run("{{bin/symlink}} {$sharedPath}/{$dir[0]} {{release_path}}/{$dir[1]}");
	}

	foreach (get('shared_files_ex') as $file) {
		$dirname = dirname(parse($file[0]));
		$dirname1 = dirname(parse($file[1]));

		// Create dir of shared file if not existing
		if (!test("[ -d {$sharedPath}/{$dirname} ]")) {
			run("mkdir -p {$sharedPath}/{$dirname}");
		}

		// Check if shared file does not exist in shared.
		// and file exist in release
		if (!test("[ -f {$sharedPath}/{$file[0]} ]") && test("[ -f {{release_path}}/{$file[1]} ]")) {
			// Copy file in shared dir if not present
			run("cp -rv {{release_path}}/{$file[1]} {$sharedPath}/{$file[0]}");
		}

		// Remove from source.
		run("if [ -f $(echo {{release_path}}/{$file[1]}) ]; then rm -rf {{release_path}}/{$file[1]}; fi");

		// Ensure dir is available in release
		run("if [ ! -d $(echo {{release_path}}/{$dirname1}) ]; then mkdir -p {{release_path}}/{$dirname1};fi");

		// Touch shared
		run("touch {$sharedPath}/{$file[0]}");

		// Symlink shared dir to release dir
		run("{{bin/symlink}} {$sharedPath}/{$file[0]} {{release_path}}/{$file[1]}");
	}
});


desc('Deploy your project');
task('deploy', [
	'deploy:info',
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:vendors',
	'deploy:shared',
	'deploy:shared_ex',
	'deploy:wp_internal_symlink',
	'deploy:clean_files',
	'deploy:symlink',
	'deploy:unlock',
	'cleanup',
	'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
