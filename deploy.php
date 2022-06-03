<?php

/**
 * @file
 */

namespace Deployer;

use Dotenv\Dotenv;

require_once realpath(__DIR__ . '/vendor/autoload.php');

// Looing for .env at the root directory.
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'recipe/drupal8.php';
require 'recipe/composer.php';

$remoteHostname = $_ENV['DEPLOYER_HOSTNAME'];
$remoteUser = $_ENV['DEPLOYER_USER'];
$deployPath = $_ENV['DEPLOYER_DEPLOY_PATH'];

// Config.
set('repository', 'https://github.com/rjcorflo/imecorpa-website.git');

set('keep_releases', 3);

// Drupal 8 shared dirs.
set('shared_dirs', [
  'web/sites/{{drupal_site}}/files',
  'backups',
]);

// Drupal 8 shared files.
set('shared_files', [
  'web/sites/{{drupal_site}}/settings.php',
  'web/sites/{{drupal_site}}/services.yml',
]);

// Drupal 8 Writable dirs.
set('writable_dirs', [
  'web/sites/{{drupal_site}}/files',
]);

// Hosts.
host("$remoteHostname")
  ->set('deploy_path', $deployPath)
  ->set('config_file', '/opt/.ssh/config');

set('http_user', $remoteUser);
set('writable_mode', 'chown');

// Tasks.
task('deploy:theme:build', function () {
    cd('{{release_path}}/web/themes/custom/imecorpa');
    run('npm install');
    run('npm run build');
    run('rm -rf ./node_modules');
});

task('deploy:cache:rebuild', function () {
    cd('{{release_path}}');
    run('./vendor/bin/drush cr');
});

task('config:upload', function () {
  runLocally('drush cex -y');
  upload('./config/', '{{current_path}}/config/');
  cd('{{current_path}}');
  // run('./vendor/bin/drush cim -y');.
});

task('config:download', function () {
  cd('{{current_path}}');
  run('./vendor/bin/drush cex -y');
  download('{{current_path}}/config/', './config/');
  // runLocally('drush cim -y');.
});

after('deploy:vendors', function () {
  invoke('deploy:theme:build');
  // invoke('config:upload');.
  invoke('deploy:cache:rebuild');
});

after('deploy:failed', 'deploy:unlock');
