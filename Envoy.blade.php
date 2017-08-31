@servers(['test_web' => 'root@nanzhu_test','production_web' => 'root@nanzhu_seed'])

#测试环境部署
@task('test_deploy', ['on' => 'test_web'])
cd /usr/share/nginx/html/pc

git pull

php artisan migrate
@endtask

#正式环境部署
@task('production_deploy', ['on' => 'production_web','confirm' => true])
cd /usr/share/nginx/html/pc

git pull

php artisan migrate
@endtask
