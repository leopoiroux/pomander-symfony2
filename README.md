Symfony2 tasks for use with Pomander
=================================================

This is a plugin to help fully manage your Symfony2 projects
with the help of Pomander.

Install
-------

Requirements:

- [pomander](https://github.com/tamagokun/pomander)

Usage
-----

* `pom init` if no configuration found.
* Include plugin in environment config `$env->load('Symfony2');`
* `pom -T` to see the stuff.

Getting Started
---------------

```bash
$ vendor/bin/pom init
```

##### Modify your development.yml or development.php

```
<?php

$env->load('Symfony2');

$env->symfony2(array(
    'version' => '2.4.0',
));

$env->repository('set your repository location here')
    ->deploy_to('set your application location on server')
;
```

```bash
$ vendor/bin/pom symfony2:setup
```

Done!