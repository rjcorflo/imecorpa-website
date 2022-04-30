<?php

/**
 * @file
 */

namespace Deployer;

/**
 * @file
 */

require_once realpath(__DIR__ . '/vendor/autoload.php');

// Looing for .env at the root directory.
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'recipe/drupal8.php';
require 'recipe/composer.php';

$remoteHostname = $_ENV['DEPLOYER_HOSTNAME'];
$remoteUser = $_ENV['DEPLOYER_USER'];
$deployPath = $_ENV['DEPLOYER_DEPLOY_PATH'];

// Config.
set('repository', 'https://github.com/rjcorflo/imecorpa-website.git');

set('keep_releases', 5);

// Drupal 8 shared dirs.
set('shared_dirs', [
  'web/sites/{{drupal_site}}/files',
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
/* task('build', function () {
    cd('{{release_path}}');
    run('npm run build');
});*/

after('deploy:failed', 'deploy:unlock');
