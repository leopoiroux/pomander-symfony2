<?php

group('symfony2',function () {

    task('download', 'app', function ($app) {

        // This task should only be played in development
        if ($app->env->name != "development") {
            abort("symfony2:download","This task should only be played in development");
        }

        // Download Symfony2 Standard Edition
        if (empty($app->env->symfony2["version"])) {
            $version = 'master';
        } elseif (substr($app->env->symfony2["version"], 0, 1) != 'v') {
            $version = 'v' . $app->env->symfony2["version"];
        } else {
            $version = $app->env->symfony2["version"];
        }

        // Task
        info("symfony2:download","Download Symfony2 Standard Edition : {$version}");
        $cmd = array(
            "curl -sL https://github.com/symfony/symfony-standard/archive/{$version}.tar.gz > {$app->env->deploy_to}/symfony2.tar",
            "tar --strip-components=1 -xzf {$app->env->deploy_to}/symfony2.tar -C {$app->env->deploy_to}",
            "rm -f {$app->env->deploy_to}/symfony2.tar"
        );

        run($cmd);
    });

    task('parameters', 'app', function ($app) {

        if ($app->env->symfony2['task']['parameters'] === false) {

            // Task disable
            warn("symfony2:parameters", "Task disable for this environment");

        } else {

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:composer", "Symfony2 not exists on application");

            // Task
            if (!empty($app->env->symfony2["parameters"])) {
                info("symfony2:parameters", "Write /app/config/parameters.yml.dist");
                $parameters = \Spyc::YAMLDump(array('parameters' => $app->env->symfony2["parameters"]),4,60);
                $cmd = array(
                    "rm -f {$app->env->release_dir}/app/config/parameters.yml",
                    "rm -f {$app->env->release_dir}/app/config/parameters.yml.dist",
                    "echo \"# This file is auto-generated by Pomander-Symfony2\" >> {$app->env->release_dir}/app/config/parameters.yml.dist",
                    "echo \"{$parameters}\" >> {$app->env->release_dir}/app/config/parameters.yml.dist"
                );
                run($cmd);
            } else {
                warn("symfony2:parameters", "Configuration not defined for this environment (/app/config/parameters.yml.dist used)");
            }
        }

    });

    desc("Setting up Permissions");
    task('permissions', 'app', function ($app) {

        if ($app->env->symfony2['task']['permissions'] === false) {

            // Task disable
            warn("symfony2:permissions", "Task disable for this environment");

        } else {

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:composer", "Symfony2 not exists on application");

            // Test if setfacl exists
            $setfacl_exists = run("if which setfacl; then echo \"ok\"; fi", true);
            if(empty($setfacl_exists)) abort("symfony2:permissions", "Enable ACL support and install \"setfacl\"");

            // User
            if(empty($app->env->user)) abort("symfony2:permissions", 'You must specify $env->user');

            // Task
            info("symfony2:permissions","app/cache app/logs");
            $cmd = array(
                "cd {$app->env->release_dir}",
                "rm -rf app/cache/*",
                "rm -rf app/logs/*",
                "sudo setfacl -R -m u:www-data:rwX -m u:{$app->env->user}:rwX app/cache app/logs",
                "sudo setfacl -dR -m u:www-data:rwX -m u:{$app->env->user}:rwX app/cache app/logs"
            );

            run($cmd);
        }
    });

    desc("Run \"composer install\"");
    task('composer', 'symfony2:parameters', 'app', function ($app) {

        if ($app->env->symfony2['task']['composer'] === false) {

            // Task disable
            warn("symfony2:composer", "Task disable for this environment");

        } else {

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:composer", "Symfony2 not exists on application");

            // Test if composer exists
            $composer_exists = run("if which composer; then echo \"ok\"; fi", true);
            if(empty($composer_exists)) abort("symfony2:install", "Install \"Composer\" globally");

            // Task
            info("symfony2:composer","composer install --prefer-dist --optimize-autoloader -n");
            $cmd = array(
                "cd {$app->env->release_dir}",
                "composer install --prefer-dist --optimize-autoloader -n"
            );

            run($cmd);
        }
    });

    desc("Clear and Warmup cache");
    task('clear', 'app', function ($app) {

        if ($app->env->symfony2['task']['clear'] === false) {

            // Task disable
            warn("symfony2:clear", "Task disable for this environment");

        } else {

            if(empty($app->env->symfony2["env"])) abort("symfony2:clear", "Symfony2 \"env\" not defined");

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:clear", "Symfony2 not exists on application");

            info("symfony2:clear","Clear and Warmup cache");
            $cmd = array(
                "cd {$app->env->release_dir}",
                "php app/console cache:clear --no-warmup --no-debug --env={$app->env->symfony2["env"]}",
                "php app/console cache:warmup --no-debug --env={$app->env->symfony2["env"]}"
            );

            run($cmd);
        }
    });

    desc("Assets install");
    task('assets', 'app', function ($app) {

        if ($app->env->symfony2['task']['assets'] === false) {

            // Task disable
            warn("symfony2:assets", "Task disable for this environment");

        } else {

            if(empty($app->env->symfony2["env"])) abort("symfony2:assets", "Symfony2 \"env\" not defined");

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:assets", "Symfony2 not exists on application");

            info("symfony2:assets","Assets install");
            $cmd = array(
                "cd {$app->env->release_dir}",
                "php app/console assets:install web --no-debug --env={$app->env->symfony2["env"]}"
            );

            run($cmd);
        }
    });

    desc("Assetic dump");
    task('assetic', 'app', function ($app) {

        if ($app->env->symfony2['task']['assetic'] === false) {

            // Task disable
            warn("symfony2:assetic", "Task disable for this environment");

        } else {

            if(empty($app->env->symfony2["env"])) abort("symfony2:assetic", "Symfony2 \"env\" not defined");

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:assetic", "Symfony2 not exists on application");

            info("symfony2:assetic","Assetic dump");
            $cmd = array(
                "cd {$app->env->release_dir}",
                "php app/console assetic:dump --no-debug --env={$app->env->symfony2["env"]}"
            );

            run($cmd);
        }
    });

    desc("Doctrine migrate");
    task('migrate', 'app', function ($app) {

        if ($app->env->symfony2['task']['migrate'] === false) {

            // Task disable
            warn("symfony2:migrate", "Task disable for this environment");

        } else {

            if(empty($app->env->symfony2["env"])) abort("symfony2:migrate", "Symfony2 \"env\" not defined");

            // Define release_dir
            if ($app->env->releases === false) $app->env->release_dir = $app->env->deploy_to;
            else $app->env->release_dir = $app->env->current_dir;

            // Test if SF2 exists
            $sf2_exists = run("if test -f {$app->env->release_dir}/web/app.php; then echo \"ok\"; fi", true);
            if(empty($sf2_exists)) abort("symfony2:migrate", "Symfony2 not exists on application");

            info("symfony2:migrate","Doctrine migrate");
            $cmd = array(
                "cd {$app->env->release_dir}",
                "php app/console doctrine:migrations:migrate -n --env={$app->env->symfony2["env"]}"
            );

            run($cmd);
        }
    });

    desc("Installation of Symfony2 in environment.");
    task('setup',
            'deploy:setup',
            'symfony2:download'
    );

    desc("Deploy Symfony2 in environment.");
    task('deploy',
            'deploy:update',
            'deploy:finalize',
            'symfony2:permissions',
            'symfony2:parameters',
            'symfony2:composer',
            'symfony2:clear',
            'symfony2:assets',
            'symfony2:assetic',
            'symfony2:migrate'
    );

});
