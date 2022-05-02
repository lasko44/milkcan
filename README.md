# Laravel Whitelabel

Reduce the amount of code written and time needed to create whitelabeled Laravel applications with the use of flavors.
Run multiple domains while reducing the amount of code needed for creating different styles for different domains.
Reuse blade files with "dynamic-static" content and styles very similarly to Laravel's built-in localization, using 
laravel-whitelabel's `@flavor()` Blade directive. 


## Getting Started

#### 1. Create packages directory in your project's root

``mkdir packages``

#### 2. Cd into packages directory and clone repository into the packages directory
```
cd packages
git clone https://github.com/lasko44/milkcan.git
```

#### 3.  Register the package in your project's composer.json file in the require section and repositories section

Add to the require section
```
 "require": {
        "milkcan/whitelabel": "@dev"
    }
```

If you do not have a repositories section add one like this

```
    "repositories": {
        "whitelabel": {
            "type": "path",
            "url": "packages/milkcan/whitelabel",
            "options": {
                "symlink": true
            }
        }
    },
```

#### 4. Run composer update
``composer update``

#### 5. Publish the whitelabel migrations
``php artisan vendor:publish --provider=“Milkcan\Whitelabel\WhitelabelServiceProvider”``

Run the migrations
``php artisan migrate``


## Usage

### Install

``php artisan whitelabel:install "<yourDomain.test>"``

The installation process requires a domain for the base whitelabel application. The domain cannot contain any spaces. 
The installation process creates two entries in the database along with a flavor directory in the application's config
directory.  The database is used to map domain names to specific flavor directories in ``config/flavor``. 

By default `flavor/base` and `flavor/whiteLabel` are created and mapped. The `flavor/base` is mapped to the domain that
you entered and `flavor/whiteLabel` is mapped to `white-label.test`

In each of the flavor directories there will be four files. `images.php` `misc.php` `styles.php` `text.php` for the sake
of organization. Example key pairs will be generated in both of the  `text.php` files.

config/flavor/base/text.php
```
return[
	'flavor-test'=>'Base Text',
    ];
```

config/flavor/whiteLabel/text.php
```
return[
	'flavor-test'=>'White Label Text',
    ];
```

#### Access in blade files

`@flavor(text.flavor-test)`

#### Base Output
    Base Text

#### Whitelabel Output
    White Label Text

### Making Sure the Flavors are Loaded
In any controller that returns a view that will be whitelabeled you must add a constructor that clears the views, 
because there are behind the scenes tweaks to the `@flavor` Blade directive that need to be registered.

Add the following to each controller that returns a whitelabeled view

```
public function __construct()
    {
        Artisan::call('view:clear');
    }
```

#### Alternatively Place the construct method in the base controller ``Http/Controllers/Controller.php``

This will run the artisan command on every user-created controller with only having to write the code once

```
public function __construct()
    {
        Artisan::call('view:clear');
    }
```

### Domain Grouping

Laravel-Whitelabel uses Laravel's built-in domain grouping to ensure that correct application flavors are being displayed,
for the correct url. To add a white labeled domain to your application you can use (my preferred method) Laravel Valet
to add another url mapped to your Laravel project.

In the root of your Laravel directory run the following `valet link white-label.test --secure`. Then in your `web.php`
you can create a domain group. Any ungrouped routes will be seen as the base application. 

Read more on [Domain Routing](https://laravel.com/docs/9.x/routing#route-group-subdomain-routing)

All routes in the domain group will show the flavors defined in the mapped flavors folder

```
    Route::domain('white-label.test')->group(function(){
        Route::controller(AppController::class)->group(function(){
            Route::get('/','index')->name('index');
        });
    });
```

### Adding Flavors

Although you can manually add flavor key pairs in the flavor files it is recommended that you use the `whitelabel:flavor`
artisan command. Using the artisan command will ensure that each of flavor files will have matching keys so there is key 
integrity. 

There are two required arguments while using the command, `{file}` and `{flavorKey}`, along with one optional argument  
`{flavorValue}`. The `file` argument is the file that you want to add the `flavorKey`. It will make sure that the key will 
be added to each of the defined file in each flavor directory. 

You can define a flavor value that will be applied in all of desired files

Example: 
``php artisan whitelabel:flavor styles "new-flavor""``

#### Output

config/flavor/base/styles.php


```
<?php

return[
	'new-flavor'=>'', //add value for key
    ];

```

config/flavor/base/styles.php

```
<?php

return[
	'new-flavor'=>'', //add value for key
    ];

```

### Using Flavors in Blade Files

The `@flavor()` directive can be used anywhere within your blade files including css classes and vue props

Examples:

Normal Usage

``<h1 class="@flavor(styles.title-class)">@flavor(text.title)</h1>``

Vue - Must wrap in single quotes when passing as a prop

```
   <search-bar
        :bar="'@flavor(styles.search-bar)'"
        :search="'@flavor(styles.search-btn)'"
        :browse="'@flavor(styles.browse-btn)'"
    ></search-bar>
```

### Adding Whitelabeled Domains

Adding more domains and flavor folders is easy with `whitelabel:domain`. This command takes one required argument `{domain}`.
Example `php artisan whitelabel:domain "newDomain.test"`. This will create a database entry with the new domain and folder
name. A new empty folder will be created based on the domain argument in the config/flavor directory. 

A new domain group will need to be added to web.php file to make flavors work correctly. 

There are available options for creating folders when making a new whitelabeled domain. 

`--all` Copies all flavor files and flavors from the base directory

`--all-empty` Copies all flavor files but the files are empty

`--default-empty` Copies default flavor files only but they are empty

`--default-fill` Copies default flavor files only and fills the flavors based on base directory

### Mapping Multiple Domains to One Flavor

If you need to map multiple domains to one flavor directory you can use `whitelabel:map`. This command takes two 
required arguments `{domain}` and `{folder}`. This will simply update the database to make sure the domain is mapped 
correctly. 
