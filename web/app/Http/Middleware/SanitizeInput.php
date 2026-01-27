<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sanitize Input Middleware
 *
 * Sanitizes user input to prevent XSS attacks.
 */
class SanitizeInput
{
    /**
     * Fields that should contain HTML (don't strip HTML, but sanitize)
     *
     * @var array<string>
     */
    private array $htmlFields = [
        'content',
        'description',
        'body',
        'bio',
        'notes',
    ];

    /**
     * Fields that should be sanitized aggressively (strip all HTML)
     *
     * @var array<string>
     */
    private array $textFields = [
        'name',
        'title',
        'email',
        'phone',
        'address',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value)) {
                // Allow HTML in specific fields, but sanitize it
                if (in_array($key, $this->htmlFields)) {
                    $value = $this->sanitizeHtml($value);
                }
                // Strip all HTML from other text fields
                elseif (in_array($key, $this->textFields)) {
                    $value = strip_tags($value);
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                // Trim whitespace from all strings
                $value = trim($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }

    /**
     * Sanitize HTML using HTML Purifier.
     *
     * @param string $html
     * @return string
     */
    private function sanitizeHtml(string $html): string
    {
        // Use HTML Purifier for safe HTML
        return clean($html, [
            'HTML.Allowed' => 'p,b,strong,i,em,u,a[href],ul,ol,li,br,h1,h2,h3,h4,h5,h6',
            'AutoFormat.RemoveEmpty' => true,
        ]);
    }
}
