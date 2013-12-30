<?php

group('symfony2',function () {

    task('download', function ($app) {

        // This task should only be played in development
        if ($app->env->name != "development") {
            return abort("symfony2:download","This task should only be played in development");
        }

        // Download Symfony2 Standard Edition
        if (empty($app->env->symfony2["version"])) {
            $version = 'master';
        } elseif (substr($app->env->symfony2["version"], 0, 1) != 'v') {
            $version = 'v' . $app->env->symfony2["version"];
        } else {
            $version = $app->env->symfony2["version"];
        }

        info("symfony2:download","Download Symfony2 Standard Edition : {$version}");
        $cmd = array(
            "curl -sL https://github.com/symfony/symfony-standard/archive/{$version}.tar.gz > {$app->env->releases_dir}/symfony2.tar",
            "tar --strip-components=1 -xzf {$app->env->releases_dir}/symfony2.tar -C {$app->env->releases_dir}",
            "rm -f {$app->env->releases_dir}/symfony2.tar"
        );

        run($cmd);
    });

    task('parameters', function ($app) {

        if (!file_exists($app->env->releases_dir . '/web/app.php')) return abort("symfony2:parameters", "Symfony2 not exists on application");
        if (!empty($app->env->symfony2["parameters"])) {
            info("symfony2:parameters", "Write /app/config/parameters.yml");
            $parameters = \Spyc::YAMLDump(array('parameters' => $app->env->symfony2["parameters"]),4,60);
            file_put_contents($app->env->releases_dir . '/app/config/parameters.yml', $parameters);
        } else {
            warn("symfony2:parameters", "Configuration not defined for environment (/app/config/parameters.yml.dist used)");
        }

    });

    desc("Run Composer: install");
    task('composer', 'parameters', function ($app) {

        // Test if composer exists
        $composer_exists = run("if which composer; then echo \"ok\"; fi", true);
        if(!empty($composer_exists)) $composer = 'composer';
        else if (file_exists($app->env->releases_dir . '/composer.phar')) $composer = 'php composer.phar';
        else return abort("symfony2:install", "Install \"Composer\" the Dependency Manager for PHP");

        info("symfony2:composer","composer install");
        $cmd = array(
            "cd {$app->env->releases_dir}",
            "{$composer} install -n"
        );

        run($cmd);
    });

    desc("Installation of Symfony2 in environment.");
    task('setup', 'deploy:setup', 'symfony2:download', 'symfony2:composer');
});
