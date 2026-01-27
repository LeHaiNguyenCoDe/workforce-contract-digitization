<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestAllApis extends Command
{
    protected $signature = 'api:test-all {--base-url=http://localhost}';
    protected $description = 'Test all API endpoints and report errors';

    public function handle()
    {
        $baseUrl = $this->option('base-url');
        $results = [
            'passed' => [],
            'failed' => [],
        ];

        $this->info('Testing Frontend Public APIs...');
        $this->testFrontendPublic($baseUrl, $results);

        $this->info('Testing Frontend Authenticated APIs...');
        $sessionCookie = $this->testFrontendAuth($baseUrl, $results);

        if ($sessionCookie) {
            $this->testFrontendAuthenticated($baseUrl, $sessionCookie, $results);
        }

        $this->info('Testing Admin APIs...');
        $adminCookie = $this->testAdminAuth($baseUrl, $results);
        
        if ($adminCookie) {
            $this->testAdminEndpoints($baseUrl, $adminCookie, $results);
        }

        // Summary
        $this->newLine();
        $this->info('=== Test Summary ===');
        $this->info("Passed: " . count($results['passed']));
        $this->info("Failed: " . count($results['failed']));

        if (!empty($results['failed'])) {
            $this->error('Failed Endpoints:');
            foreach ($results['failed'] as $endpoint => $error) {
                $this->error("  - {$endpoint}: {$error}");
            }
        }

        return count($results['failed']) > 0 ? 1 : 0;
    }

    private function testFrontendPublic($baseUrl, &$results)
    {
        $endpoints = [
            'GET /api/v1/language' => '/api/v1/language',
            'GET /api/v1/language/supported' => '/api/v1/language/supported',
            'GET /api/v1/categories' => '/api/v1/categories',
            'GET /api/v1/products' => '/api/v1/products',
            'GET /api/v1/articles' => '/api/v1/articles',
            'GET /api/v1/promotions' => '/api/v1/promotions',
            'GET /api/v1/cart' => '/api/v1/cart',
        ];

        foreach ($endpoints as $name => $endpoint) {
            try {
                $response = Http::timeout(5)->get($baseUrl . $endpoint);
                if ($response->successful()) {
                    $results['passed'][] = $name;
                    $this->info("  ✓ {$name}");
                } else {
                    $results['failed'][$name] = "Status: {$response->status()}";
                    $this->error("  ✗ {$name} - Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                $results['failed'][$name] = $e->getMessage();
                $this->error("  ✗ {$name} - {$e->getMessage()}");
            }
        }
    }

    private function testFrontendAuth($baseUrl, &$results)
    {
        try {
            $response = Http::timeout(5)->post($baseUrl . '/api/v1/login', [
                'email' => 'customer1@example.com',
                'password' => 'password123',
            ]);

            if ($response->successful()) {
                $results['passed'][] = 'POST /api/v1/login';
                $this->info("  ✓ POST /api/v1/login");
                
                // Extract session cookie
                $cookies = $response->cookies();
                return $cookies['laravel_session'] ?? null;
            } else {
                $results['failed']['POST /api/v1/login'] = "Status: {$response->status()}";
                $this->error("  ✗ POST /api/v1/login - Status: {$response->status()}");
            }
        } catch (\Exception $e) {
            $results['failed']['POST /api/v1/login'] = $e->getMessage();
            $this->error("  ✗ POST /api/v1/login - {$e->getMessage()}");
        }

        return null;
    }

    private function testFrontendAuthenticated($baseUrl, $cookie, &$results)
    {
        $endpoints = [
            'GET /api/v1/me' => '/api/v1/me',
            'GET /api/v1/profile' => '/api/v1/profile',
            'GET /api/v1/orders' => '/api/v1/orders',
            'GET /api/v1/wishlist' => '/api/v1/wishlist',
            'GET /api/v1/loyalty' => '/api/v1/loyalty',
        ];

        foreach ($endpoints as $name => $endpoint) {
            try {
                $response = Http::timeout(5)
                    ->withCookie('laravel_session', $cookie)
                    ->get($baseUrl . $endpoint);

                if ($response->successful()) {
                    $results['passed'][] = $name;
                    $this->info("  ✓ {$name}");
                } else {
                    $results['failed'][$name] = "Status: {$response->status()}";
                    $this->error("  ✗ {$name} - Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                $results['failed'][$name] = $e->getMessage();
                $this->error("  ✗ {$name} - {$e->getMessage()}");
            }
        }
    }

    private function testAdminAuth($baseUrl, &$results)
    {
        try {
            $response = Http::timeout(5)->post($baseUrl . '/api/v1/login', [
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]);

            if ($response->successful()) {
                $results['passed'][] = 'POST /api/v1/login (admin)';
                $this->info("  ✓ POST /api/v1/login (admin)");
                
                $cookies = $response->cookies();
                return $cookies['laravel_session'] ?? null;
            } else {
                $results['failed']['POST /api/v1/login (admin)'] = "Status: {$response->status()}";
                $this->error("  ✗ POST /api/v1/login (admin) - Status: {$response->status()}");
            }
        } catch (\Exception $e) {
            $results['failed']['POST /api/v1/login (admin)'] = $e->getMessage();
            $this->error("  ✗ POST /api/v1/login (admin) - {$e->getMessage()}");
        }

        return null;
    }

    private function testAdminEndpoints($baseUrl, $cookie, &$results)
    {
        $endpoints = [
            'GET /api/v1/admin/users' => '/api/v1/admin/users',
            'GET /api/v1/admin/products' => '/api/v1/admin/products',
            'GET /api/v1/admin/categories' => '/api/v1/admin/categories',
            'GET /api/v1/admin/orders' => '/api/v1/admin/orders',
            'GET /api/v1/admin/promotions' => '/api/v1/admin/promotions',
            'GET /api/v1/admin/warehouses' => '/api/v1/admin/warehouses',
            'GET /api/v1/admin/articles' => '/api/v1/admin/articles',
            'GET /api/v1/admin/reviews' => '/api/v1/admin/reviews',
        ];

        foreach ($endpoints as $name => $endpoint) {
            try {
                $response = Http::timeout(5)
                    ->withCookie('laravel_session', $cookie)
                    ->get($baseUrl . $endpoint);

                if ($response->successful()) {
                    $results['passed'][] = $name;
                    $this->info("  ✓ {$name}");
                } else {
                    $results['failed'][$name] = "Status: {$response->status()}";
                    $this->error("  ✗ {$name} - Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                $results['failed'][$name] = $e->getMessage();
                $this->error("  ✗ {$name} - {$e->getMessage()}");
            }
        }
    }
}

