<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class GenerateApiDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:docs
                            {--format=openapi : Output format (openapi, postman)}
                            {--output= : Output file path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documentation from routes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $format = $this->option('format');
        $output = $this->option('output');

        $this->info('Generating API documentation...');

        $routes = $this->getApiRoutes();

        if ($format === 'postman') {
            $content = $this->generatePostmanCollection($routes);
            $file = $output ?: 'postman_collection.json';
        } else {
            $content = $this->generateOpenApiSpec($routes);
            $file = $output ?: 'openapi.yaml';
        }

        File::put($file, $content);

        $this->info("✅ Documentation generated: {$file}");
        $this->info("📊 Total routes: " . count($routes));

        return Command::SUCCESS;
    }

    /**
     * Get all API routes
     *
     * @return array
     */
    private function getApiRoutes(): array
    {
        $routes = [];
        $apiRoutes = Route::getRoutes()->getRoutes();

        foreach ($apiRoutes as $route) {
            if (str_starts_with($route->uri(), 'api/v1')) {
                $routes[] = [
                    'method' => $route->methods()[0],
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware(),
                ];
            }
        }

        return $routes;
    }

    /**
     * Generate OpenAPI specification
     *
     * @param  array  $routes
     * @return string
     */
    private function generateOpenApiSpec(array $routes): string
    {
        // This is a simplified version
        // For full implementation, you might want to use a package like darkaonline/l5-swagger

        $yaml = "openapi: 3.0.3\n";
        $yaml .= "info:\n";
        $yaml .= "  title: Workforce Contract Digitization API\n";
        $yaml .= "  version: 1.0.0\n\n";
        $yaml .= "servers:\n";
        $yaml .= "  - url: http://localhost:8000/api/v1\n\n";
        $yaml .= "paths:\n";

        foreach ($routes as $route) {
            $path = str_replace('api/v1/', '', $route['uri']);
            $method = strtolower($route['method']);

            $yaml .= "  /{$path}:\n";
            $yaml .= "    {$method}:\n";
            $yaml .= "      summary: {$route['name']}\n";
            $yaml .= "      responses:\n";
            $yaml .= "        '200':\n";
            $yaml .= "          description: Success\n";
        }

        return $yaml;
    }

    /**
     * Generate Postman collection
     *
     * @param  array  $routes
     * @return string
     */
    private function generatePostmanCollection(array $routes): string
    {
        $collection = [
            'info' => [
                'name' => 'Workforce Contract Digitization API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => 'http://localhost:8000/api/v1'],
                ['key' => 'session_cookie', 'value' => ''],
            ],
            'item' => [],
        ];

        $folders = [];

        foreach ($routes as $route) {
            $path = str_replace('api/v1/', '', $route['uri']);
            $folder = $this->getFolderName($path);

            if (!isset($folders[$folder])) {
                $folders[$folder] = [
                    'name' => $folder,
                    'item' => [],
                ];
            }

            $request = [
                'name' => $this->getRequestName($route),
                'request' => [
                    'method' => $route['method'],
                    'header' => $this->getHeaders($route),
                    'url' => [
                        'raw' => '{{base_url}}/' . $path,
                        'host' => ['{{base_url}}'],
                        'path' => explode('/', $path),
                    ],
                ],
            ];

            if (in_array($route['method'], ['POST', 'PUT', 'PATCH'])) {
                $request['request']['body'] = [
                    'mode' => 'raw',
                    'raw' => $this->getExampleBody($route),
                ];
            }

            $folders[$folder]['item'][] = $request;
        }

        $collection['item'] = array_values($folders);

        return json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get folder name from path
     *
     * @param  string  $path
     * @return string
     */
    private function getFolderName(string $path): string
    {
        $parts = explode('/', $path);
        $first = $parts[0];

        $folders = [
            'language' => '🌍 Language',
            'login' => '🔑 Authentication',
            'logout' => '🔑 Authentication',
            'me' => '🔑 Authentication',
            'users' => '👥 Users',
            'categories' => '🛍️ Products & Categories',
            'products' => '🛍️ Products & Categories',
            'cart' => '🛒 Cart',
            'orders' => '📦 Orders',
            'wishlist' => '❤️ Wishlist',
            'reviews' => '⭐ Reviews',
            'promotions' => '🎁 Promotions',
            'loyalty' => '🎯 Loyalty',
            'articles' => '📰 Articles',
            'warehouses' => '🏭 Warehouses',
        ];

        return $folders[$first] ?? '📁 Other';
    }

    /**
     * Get request name
     *
     * @param  array  $route
     * @return string
     */
    private function getRequestName(array $route): string
    {
        $path = str_replace('api/v1/', '', $route['uri']);
        $parts = explode('/', $path);
        $last = end($parts);

        $method = $route['method'];
        $action = '';

        if ($method === 'GET' && $last === $parts[0]) {
            $action = 'Get All ' . ucfirst($parts[0]);
        } elseif ($method === 'GET' && is_numeric($last)) {
            $action = 'Get ' . ucfirst($parts[0]) . ' by ID';
        } elseif ($method === 'POST') {
            $action = 'Create ' . ucfirst($parts[0]);
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            $action = 'Update ' . ucfirst($parts[0]);
        } elseif ($method === 'DELETE') {
            $action = 'Delete ' . ucfirst($parts[0]);
        } else {
            $action = ucfirst($method) . ' ' . ucfirst($last);
        }

        return $action;
    }

    /**
     * Get headers for request
     *
     * @param  array  $route
     * @return array
     */
    private function getHeaders(array $route): array
    {
        $headers = [
            [
                'key' => 'Content-Type',
                'value' => 'application/json',
            ],
            [
                'key' => 'Accept',
                'value' => 'application/json',
            ],
        ];

        // Add cookie if route requires authentication
        if (in_array('auth', $route['middleware']) || in_array('App\Http\Middleware\Authenticate', $route['middleware'])) {
            $headers[] = [
                'key' => 'Cookie',
                'value' => 'laravel_session={{session_cookie}}',
            ];
        }

        return $headers;
    }

    /**
     * Get example body
     *
     * @param  array  $route
     * @return string
     */
    private function getExampleBody(array $route): string
    {
        $path = str_replace('api/v1/', '', $route['uri']);
        $parts = explode('/', $path);

        $examples = [
            'login' => ['email' => 'user@example.com', 'password' => 'password123'],
            'users' => ['name' => 'John Doe', 'email' => 'user@example.com', 'password' => 'password123'],
            'orders' => ['full_name' => 'John Doe', 'phone' => '0123456789', 'address_line' => '123 Main St', 'payment_method' => 'cod'],
            'cart/items' => ['product_id' => 1, 'qty' => 2],
            'wishlist' => ['product_id' => 1],
            'products/{id}/reviews' => ['rating' => 5, 'content' => 'Great product!'],
            'promotions' => ['name' => 'Sale', 'code' => 'SALE2025', 'type' => 'percent', 'value' => 20],
            'warehouses' => ['name' => 'Warehouse', 'code' => 'WH001'],
            'language' => ['locale' => 'en'],
        ];

        $key = $parts[0];
        if (isset($parts[1]) && isset($examples[$parts[0] . '/' . $parts[1]])) {
            $key = $parts[0] . '/' . $parts[1];
        }

        return json_encode($examples[$key] ?? [], JSON_PRETTY_PRINT);
    }
}

