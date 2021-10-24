<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class RouteListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all the registered routes in list like laravel';

    /*
     * Routes list
     */
    protected array $routes = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->displayRoutes();
    }

    /**
     * Display the routes in console
     *
     * @return bool
     */
    public function displayRoutes(): bool
    {
        $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware', 'Map To'];

        $this->generateRoutes();
        $this->applyFilters();
        if (!$this->routes) {
            $this->warn('No routes found!');

            return false;
        }

        //change the reverse order if command contains reverse command
        $str = '';
        if ($this->option('reverse')) {
            rsort($this->routes);
            $str = '. Displayed in reverse order';
        }

        $this->info("Route found: " . count($this->routes) . $str);
        $this->table($headers, $this->routes);

        return true;
    }

    /**
     * Generate the formatted routes array
     *
     * @return bool
     */
    public function generateRoutes(): bool
    {
        $routes = property_exists(app(), 'router') ? app()->router->getRoutes() : app()->getRoutes();

        foreach ($routes as $route) {
            $this->routes[] = [
                'method' => $route['method'],
                'uri' => $route['uri'],
                'name' => $this->getRouteName($route),
                'action' => $this->getRouteAction($route),
                'middleware' => $this->getRouteMiddleware($route),
                'map' => $this->getRouteMapTo($route)
            ];
        }

        return true;
    }

    /**
     * Get the route name
     *
     * @param $route
     *
     * @return null
     */
    private function getRouteName($route): ?string
    {
        return $route['action']['as'] ?? '';
    }

    /**
     * Get the route action type
     *
     * @param $route
     *
     * @return string
     */
    private function getRouteAction($route): string
    {
        return ($this->isClosureRoute($route)) ? 'Closure' : 'Controller';
    }

    /**
     *  Check if the route is closure or controller route
     *
     * @param $route
     *
     * @return bool
     */
    private function isClosureRoute($route): bool
    {
        return !isset($route['action']['uses']);
    }

    /**
     * Get route middleware
     *
     * @param $route
     *
     * @return string
     */
    private function getRouteMiddleware($route): string
    {
        if (isset($route['action']['middleware'])) {
            return implode(',', $route['action']['middleware']);
        }

        return '';
    }

    /**
     *  Get where the route map to
     *
     * @param $route
     *
     * @return string
     */
    private function getRouteMapTo($route): string
    {
        return (!$this->isClosureRoute($route)) ? $route['action']['uses'] : '';
    }

    /**
     * Apply filters on routes if user provide
     */
    private function applyFilters()
    {
        $availableOptions = ['name', 'method', 'uri', 'action', 'middleware'];
        foreach ($this->options() as $key => $option) {
            if (in_array($key, $availableOptions, true) && null != $option) {
                foreach ($this->routes as $index => $route) {
                    if (!str_contains(strtolower($route[$key]), strtolower($option)))
                        unset($this->routes[$index]);
                }
            }
        }
    }

    /**
     * Get console input options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['method', 'method', InputOption::VALUE_OPTIONAL, 'Method'],
            ['uri', 'uri', InputOption::VALUE_OPTIONAL, 'Uri'],
            ['name', 'name', InputOption::VALUE_OPTIONAL, 'Name'],
            ['action', 'action', InputOption::VALUE_OPTIONAL, 'Action'],
            ['middleware', 'middleware', InputOption::VALUE_OPTIONAL, 'Middleware'],
            ['map', 'map', InputOption::VALUE_OPTIONAL, 'Map to'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse route list']
        ];
    }
}
