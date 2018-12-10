@servers(['web' => ['forge@kendozone.com']])

@task('deploy', ['on' => 'web'])
    cd api.kendozone.com
    git pull origin master
    composer install
@endtask