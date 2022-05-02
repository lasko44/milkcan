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

