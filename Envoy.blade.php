@servers(['localhost' => '127.0.0.1'])

@story('deploy')
    git
    composer
    migrations
    npm
    cache
    supervisor
@endstory

@story('reset-cache')
    cache
@endstory


@task('git')
    git pull
@endtask

@task('composer')
    composer install --no-dev
@endtask

@task('migrations')
    php artisan migrate --force
@endtask

@task('npm')
    npm install
    npm run build
@endtask

@task('cache')
    php artisan route:clear
    php artisan view:clear
    php artisan event:clear
    sudo php artisan cache:clear

    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
@endtask

@task('supervisor')
    sudo systemctl restart analytics-queue@{1..5}
@endtask

@error
    echo "Error occurred on task {$task}!".PHP_EOL;
@enderror
