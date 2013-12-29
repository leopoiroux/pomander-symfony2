<?php

group('symfony2',function () {

    desc("Setup Symfony2 in environment.");
    task('setup', 'deploy:setup', function ($app) {

        // This task should only be played in development
        if ($app->env->name != "development") {
            return abort("symfony2:setup","This task should only be played in development");
        }

        // Download Symfony2 Standard Edition
        if (empty($app->env->symfony2["version"])) {
            $version = 'master';
        } elseif (substr($app->env->symfony2["version"], 0, 1) != 'v') {
            $version = 'v' . $app->env->symfony2["version"];
        } else {
            $version = $app->env->symfony2["version"];
        }

        info("symfony2:setup","Download Symfony2 Standard Edition : {$version}");
        $cmd[] = "curl -sL https://github.com/symfony/symfony-standard/archive/{$version}.tar.gz > {$app->env->release_dir}/symfony2.tar";
        $cmd[] = "tar --strip-components=1 -xzf {$app->env->release_dir}/symfony2.tar -C {$app->env->release_dir}";
        $cmd[] = "rm -f {$app->env->release_dir}/symfony2.tar";

        run($cmd);
    });
});
