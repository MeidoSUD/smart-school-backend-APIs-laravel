<?php

namespace Tests\Feature\Api;

use Modules\Core\Entities\User;
use Modules\Core\Services\StudentSessionResolver;

class StudentSessionResolverTest extends ApiTestCase
{
    public function test_resolves_session_from_sch_settings_session_id(): void
    {
        $fixtures = $this->seedSchoolFixtures();
        $user = $fixtures['user'];
        $resolver = app(StudentSessionResolver::class);

        $this->assertNotNull($resolver->currentSessionId());
        $this->assertNotNull($resolver->resolveSession($user));
        $this->assertSame($fixtures['student_session_id'], $resolver->resolveSession($user)->id);
    }
}
