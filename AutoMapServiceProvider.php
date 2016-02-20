<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AutoMapServiceProvider extends ServiceProvider
{
    use \Illuminate\Console\AppNamespaceDetectorTrait;

    /**
     * This array is automatically appended to by the autoMap() method.
     * However you can manually add controllers and routes here and they
     * will be processed as well.
     *
     * @var string
     */
    protected $automaps = [];

    /**
     * The base namespace. This can change (php artisane name)
     * @var string
     */
    private $base_namespace;

    /**
     * This is where controllers are located relative to /
     * This should change but if it does simply modify this.
     * @var string
     */
    private $controllers_path = '../app/Http/Controllers/';

    /**
     * Routes location relative to /.
     * @var string
     */
    private $routes_path = '../app/Http/Routes/';

    /**
     * An arry of controllers names.
     * @var array
     */
    private $controllers = [];

    /**
     * An arry of route names.
     * @var array
     */
    private $routes = [];

    /**
     * Set base namespace, call autoMap, then boot.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        $this->setBaseNamespace()->autoMap();

        parent::boot($router);
    }

    /**
     * Define the routes for the application. This method takes each key/value pair
     * in the automaps array and iterates over them to map the controller namespace
     * and routes file for each.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        foreach($this->automaps as $namespace => $route) {
            $router->group(['namespace' => $namespace], function ($router) use ($route) {
                require app_path($route);
            });
        }
    }

    /**
     * Sets automaps.
     * @return void
     */
    private function autoMap()
    {
        if ( ! $this->base_namespace ) {
            return;
        }

        // Get an array of controller subdirectory names and validate that
        // the controllers file is there with the right name
        $this->scrape( $this->controllers_path );

        // Get an array of route subdirectory names
        $this->scrape( $this->routes_path );

        // It's possible that the routes[] or controllers[] parameters could be
        // empty. This happens if there's no Routes subdirectory (likely)
        // or no Controllers subdirectory (somewhat less likely)
        if ( ! $this->routes || ! $this->controllers ) {
            return $this;
        }

        // Discard any names that aren't contained in both properties
        $matches = array_intersect($this->routes, $this->controllers);

        // Build the Controller namespaces and Route paths and add them to the
        // automaps property.
        foreach($matches as $match) {
            $this->automaps[$this->buildNamespace($match)] = $this->buildRoute($match);
        }

        return $this;
    }

    /**
     * Check that a directory's subdirectories have the expected files.
     * app/Http/Controllers/Foo/ should have a FooController.php.
     * app/Http/Routes/Foo/ should have a routes.php.
     *
     * @return void
     */
    private function validate($type)
    {
        $checked = [];

        if ( $type === 'controllers' ) {
            $names = $this->controllers;

            if ( ! $names ) {
                return $this;
            }

            foreach ( $names as $name ) {

                if ( file_exists( $this->controllers_path . $name . '/' . $name . 'Controller.php' ) ) {
                    $checked[] = $name;
                }
            }

            $this->controllers = $checked;
        }

        if ( $type === 'routes' ) {
            $names = $this->routes;

            if ( ! $names ) {
                return $this;
            }

            foreach ( $names as $name ) {
                if ( file_exists( $this->routes_path . $name . '/' . 'routes.php' ) ) {
                    $checked[] = $name;
                }
            }

            $this->controllers = $checked;
        }

        return $this;
    }

    /**
     * Scrape subdirectory names from a particular path, returning
     * an array of names minus their paths.
     *
     * @param  string $path
     * @return array
     */
    private function scrape($path)
    {
        $dirs = $this->find($path);
        $names = $this->sanitize($dirs, $path);

        if ( strpos($path, '/Controllers/') !== FALSE ) {
            $this->controllers = $names;
            $this->validate('controllers');

            return $this;
        }

        $this->routes = $names;
        $this->validate('routes');

        return $this;
    }

    /**
     * Builds the controller namespace.
     *
     * @param  string $name
     * @return string
     */
    private function buildNamespace($name)
    {
        if ( ! $this->base_namespace ) {
            return;
        }

        $namespace = $this->base_namespace . 'Http\Controllers\\' . $name;

        return $namespace;
    }

    /**
     * Builds the route path.
     *
     * @param  string $name
     * @return string
     */
    private function buildRoute($name)
    {
        $route = 'Http/Routes/' . $name . '/routes.php';

        return $route;
    }

    /**
     * Strip paths and returns the directory names only
     *
     * @param  array  $directories
     * @return array
     */
    private function sanitize($directories, $path)
    {
        if ( ! $directories ) {
            return;
        }

        $sanitized = [];

        foreach ($directories as $directory) {
            $sanitized[] = str_replace($path, '', $directory);
        }

        return $sanitized;
    }

    /**
     * Looks in a path and finds all subdirectories
     *
     * @param  string $path
     * @return array
     */
    private function find($path)
    {
        if ( ! file_exists($path) ) {
            return;
        }

        $subdirectories = glob( $path . '*', GLOB_ONLYDIR );

        return $subdirectories;
    }

    /**
     * Sets the baseNamespace parameter.
     *
     * getAppNamespace() comes from AppNamespaceDetectorTrait
     */
    private function setBaseNamespace()
    {
        $this->base_namespace = $this->getAppNamespace();

        return $this;
    }

}
