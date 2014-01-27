Symfony2 tasks for use with Pomander
=================================================

[![Latest Stable Version](https://poser.pugx.org/pomander/pomander/v/stable.png)](https://packagist.org/packages/pomander/symfony2)

This is a plugin to help fully manage your Symfony2 projects
with the help of Pomander.

[Pomander](https://github.com/tamagokun/pomander) is a light-weight flexible deployment tool for deploying web applications. This project was inspired by [Capistrano](https://github.com/capistrano/capistrano) and [Vlad the Deployer](http://rubyhitsquad.com/Vlad_the_Deployer.html), as well as being built on top of [Phake](https://github.com/jaz303/phake), a [Rake](http://rake.rubyforge.org/) clone.

Install
-------

``` bash
$ composer require pomander/symfony2:@dev
```

Usage
-----

* `vendor/bin/pom init` if no configuration found.
* Include plugin in environment config `$env->load('Symfony2');`
* `vendor/bin/pom -T` to see the stuff.

Getting Started
---------------

* `vendor/bin/pom init`

##### Modify your development.yml or development.php

```
<?php

$env->load('Symfony2');

$env->symfony2(array(
	'env' => 'dev',
    'version' => '2.4.0',
    'task' => array(
        'permissions' => true,
        'parameters' => true,
        'composer' => true,
        'clear' => true,
        'assets' => true,
        'migrate' => false,
        'assetic' => false
    ),
    'parameters' => array(
        'database_driver' => 'pdo_mysql',
        'database_host' => '127.0.0.1',
        'database_port' => '~',
        'database_name' => 'symfony',
        'database_user' => 'root',
        'database_password' => '~',
        'mailer_transport' => 'smtp',
        'mailer_host' => '127.0.0.1',
        'mailer_user' => '~',
        'mailer_password' => '~',
        'locale' => 'en',
        'secret' => 'ThisTokenIsNotSoSecretChangeIt'
    )
));

$env->repository('set your repository location here')
    ->deploy_to('set your application location on server')
;
```

* `vendor/bin/pom symfony2:setup`

Commit and push the Symfony Standard Edition downloaded

* `vendor/bin/pom deploy:setup`  
* `vendor/bin/pom symfony2:deploy`

Done!

Tasks
---------------

* `vendor/bin/pom -T`

```
config                  Create development environment configuration
db:backup               Perform a backup suited for merging.
db:create               Create database.
db:destroy              Wipe database.
db:full                 Perform a full database backup.
db:merge                Merge a backup into environment.
deploy:cold             First time deployment.
deploy:setup            Setup application in environment.
deploy:update           Update code to latest changes.
init                    Set it up
rollback                Rollback to the previous release
symfony2:assetic        Assetic dump
symfony2:assets         Assets install
symfony2:clear          Clear and Warmup cache
symfony2:composer       Run "composer install"
symfony2:deploy         Deploy Symfony2 in environment.
symfony2:migrate        Doctrine migrate
symfony2:permissions    Setting up Permissions
symfony2:setup          Installation of Symfony2 in environment.
```
