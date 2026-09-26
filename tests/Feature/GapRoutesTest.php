<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Phase 5 (p2.md T-5.4): parametric guard proving none of the gap-closure
 * routes regress to 404 at the routing layer and that protected routes
 * still require authentication. No database needed.
 */
class GapRoutesTest extends TestCase
{
    /**
     * @dataProvider protectedGetRoutes
     */
    public function test_gap_routes_exist_and_require_auth(string $method, string $uri): void
    {
        $this->assertTrue(
            Route::has($uri) || $this->routeUriExists($method, $uri),
            "Route [{$method} {$uri}] is not registered"
        );

        $response = $this->json($method, $uri);

        // Sanctum-protected: unauthenticated JSON call must NOT be a 404.
        $this->assertNotEquals(
            404,
            $response->getStatusCode(),
            "Route [{$method} {$uri}] returned 404"
        );
        $this->assertContains($response->getStatusCode(), [401, 422]);
    }

    public static function protectedGetRoutes(): array
    {
        return [
            // G-1: previously missing routes (Phase 1)
            ['GET', '/api/mark'],
            ['GET', '/api/mark/1'],
            ['GET', '/api/attendence/getevents'],
            ['GET', '/api/exam/getByFeecategory'],
            ['POST', '/api/exam/getStudentCategoryFee'],
            ['GET', '/api/exam/examSearch'],
            ['GET', '/api/subject/getSubjctByClassandSection'],
            ['POST', '/api/teacher/getSubjctByClassandSection'],
            ['POST', '/api/teacher/getSubjectTeachers'],
            ['GET', '/api/teacher/1'],
            ['GET', '/api/visitors/download/1'],
            ['GET', '/api/chat'],
            ['GET', '/api/content'],
            // T-4.5/T-4.6: fee print/collect + read-only studentfee
            ['POST', '/api/user/getProcessingfees'],
            ['POST', '/api/user/getcollectfee'],
            ['POST', '/api/user/printFeesByGroupArray'],
            ['GET', '/api/user/view/1'],
            ['GET', '/api/studentfee/view/1'],
            ['GET', '/api/studentfee/searchpayment'],
        ];
    }

    public function test_user_timeline_download_alias_exists(): void
    {
        $this->assertTrue($this->routeUriExists('GET', '/api/user/timeline_download/1'));
    }

    private function routeUriExists(string $method, string $uri): bool
    {
        foreach (Route::getRoutes() as $route) {
            if (!in_array($method, $route->methods(), true)) {
                continue;
            }
            $uriPattern = preg_replace('#/\{[^}]+\?\}#', '(?:/[^/]*)?', $route->uri());
            $pattern = '#^' . preg_replace('#\{[^}]+\}#', '[^/]+', $uriPattern) . '$#';
            if (preg_match($pattern, ltrim($uri, '/'))) {
                return true;
            }
        }

        return false;
    }
}
