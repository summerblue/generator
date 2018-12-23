# Laravel 5.x Scaffold Generator

[![Travis](https://img.shields.io/travis/summerblue/generator.svg?style=flat-square)](https://github.com/summerblue/generator)
[![Packagist](https://img.shields.io/packagist/dt/summerblue/generator.svg?style=flat-square)](https://packagist.org/packages/summerblue/generator)
[![Tag](https://img.shields.io/github/tag/summerblue/generator.svg)](https://github.com/summerblue/generator/tags)

Laravel Scaffold Generator, for Laravel 5.3.

## Install

### Step 1: Install Through Composer

5.1 ~ 5.5:

```
composer require "summerblue/generator:~0.5" --dev
```

>= 5.6 (Bootstrap4):


```
composer require "summerblue/generator:~1.0" --dev
```

### Step 2: Add the Service Provider

Open `/app/Providers/AppServiceProvider.php` and, to your **register** function, add:

```
public function register()
{
     if (app()->environment() == 'local' || app()->environment() == 'testing') {

        $this->app->register(\Summerblue\Generator\GeneratorsServiceProvider::class);

    }
}
```

### Step 3: Run Artisan!

You're all set. Run `php artisan` from the console, and you'll see the new commands `make:scaffold`.

## Examples

Use this command to generator scaffolding of **Project** in your project:

> php artisan make:scaffold Projects --schema="name:string:index,description:text:nullable,subscriber_count:integer:unsigned:default(0)"

This command will generate:

```
$ php artisan make:scaffold Projects --schema="name:string:index,description:text:nullable,subscriber_count:integer:unsigned:default(0)"


----------- scaffolding: Project -----------

+ ./database/migrations/2017_04_17_065656_create_projects_table.php
+ ./database/factories/ModelFactory.php
+ ./database/seeds/ProjectsTableSeeder.php
+ ./database/seeds/DatabaseSeeder.php (Updated)
x ./app/Models/Model.php (Skipped)
+ ./app/Models/Project.php
+ ./app/Http/Controllers/ProjectsController.php
x ./app/Http/Requests/Request.php (Skipped)
+ ./app/Http/Requests/ProjectRequest.php
+ ./app/Observers/ProjectObserver.php
+ ./app/Providers/AppServiceProvider.php (Updated)
x ./app/Policies/Policy.php
+ ./app/Policies/ProjectPolicy.php
+ ./app/Providers/AuthServiceProvider.php (Updated)
+ ./routes/web.php (Updated)

--- Views ---
   + create_and_edit.blade.php
   + index.blade.php
   + show.blade.php
x ./resources/views/error.blade.php
Migrated: 2017_04_17_065656_create_projects_table

----------- -------------------- -----------
-----------   >DUMP AUTOLOAD<    -----------
```

## Explain

Generate the following:

- Migration
- Seed, add ModelFactory entry, and DatabaseSeeder entry
- Base Model class, Model and helper trait
- Resource Controller
- Base FormRequest class and StoreRequest, UpdateRequest
- Policy and Policy base class, auto register AuthServiceProvider class
- Update routes file to register resource route
- Add error page view
- Create and Edit action share the same view

## Future Plan

- API
- Admin
- Auto fill FormRequest rule
- Auto fill ModelFactory filed

## Screenshot

![file](https://cloud.githubusercontent.com/assets/324764/22488519/7466a638-e84d-11e6-8201-99ad377d6270.png)

## Thinks to
- [laralib/l5scaffold](https://github.com/laralib/l5scaffold)
