<p align="center"><a href="http://finemenu.net" target="_blank">
    <img src="./resources/images/logo-light.png" width="400" alt="Laravel Logo"></a>
</p>

## FineMenu system 
### Dr.Healthy

It’s the FineMenu version for the Diet & healthy restaurants, It is a SAAS project B2B to support Diet & Healthy restaurant owners to manage their business.

- More about [FineMenu system](https://finemenu.atlassian.net/wiki/spaces/~5570588686200f90b9447190d8bd2141fe9d01/pages/131197/FineMenu+Project+Planning).
- More about [Dr Healthy](https://finemenu.atlassian.net/wiki/spaces/~5570588686200f90b9447190d8bd2141fe9d01/pages/1048577/Dr+Healthy).
- Backend [GitHub](https://github.com/engmg2011/finemenu-2024)

## Dependencies
- PHP , Laravel sail
- Docker , Docker compose cli


## Start your project
- Clone the projects
- In mysql container
    - mysql -u sail -ppassword
    - create database finemenu;
- alias sail='/vendor/bin/sail';
- In backend directory
  - composer install
  - cp Dockerfile vendor/laravel/sail/runtimes/8.2/Dockerfile
  - sail build
  - sail up -d
  - sail artisan migrate
  - sail artisan db:seed --class PermissionsSeeder
  - sail artisan storage:link
  - sail artisan passport:install

