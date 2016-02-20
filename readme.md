# AutoMap for Laravel

If you've ever found yourself with a ```routes.php``` file crammed full of routes and wondered, "Hey, can I have a second routes file"? Well, sure, you can include it. You can even modify ```RoutesServiceProvider``` to iterate over an array of namespaces and routes files.

Or you can use ```AutoMapServiceProvider``` and have all the routes files you like.

### How Does It Work?

```AutoMapServiceProvider``` looks in your Controllers directory, finds any subdirectories, and checks to see if there's a Routes subdirectory with the same name. Then it maps the controllers to the routes, letting Laravel know they exist and where they are.

This means that if you have this folder structure:

```
app/Controllers/Foo/FooController.php

app/Routes/Foo/routes.php
```
these will be automatically mapped to each other and available to Laravel.

### Installation

Place ```AutoMapServiceProvider.php``` in your App\Providers directory.

Modify ```config/app.php``` to include ```App\Providers\AutoMapServiceProvider::class``` in the ```'providers'``` array. It may be useful to run ```composer dump-autoload``` after.

If you've changed your app's name via ```php artisan app:name```, you'll have to change the namespacing above from ```App\...``` to ```YourAppName\...```. You'll aso have to change the namespacing in the actual ```AutoMapServiceProvider.php``` file.

### Adding controllers and routes

Take whatever portion of your routes you want to abstract away and move them to App/Routes/{controller}/routes.php. You will have to create the Routes directory, as it does not exist by default.

For example, your controller ```app/Http/Controllers/SuperSecret/SuperSecretController.php``` might look something like this:

```php
namespace App\Http\Controllers\SuperSecret;
...
class SuperSecretController extends Controller
{
    public function hidden () {
        return "Security through obscurity"
    }
}
```

and your routes file ```app/Http/Routes/SuperSecret/routes.php``` might have this:

```php
Route::get('super/secret/hidden', 'SuperSecretController@hidden')->name('some-name');
```

And that's it! You don't have to register the directory anywhere, you don't have to make any assocation betwen the two. It just works, and things like route names are available throughout your application.

### Notes

You can use this alongside Laravel's ```RoutesServiceProvider```. However if you want to completely replace it, just add your default controller namespacing and routes file location to the ```automaps``` array. The autoMap() method appends to this array so you'll be fine.

### Contributing

Yes please. Go ahead. Tell me if I'm doing something silly, or if there if I'm duplicating secret functionality that Laravel already has.