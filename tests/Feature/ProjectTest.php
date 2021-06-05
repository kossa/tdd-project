<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /** @test */
    public function unlogged_user_cannot_access_to_project()
    {
        // $this->withoutExceptionHandling();

        $this->getJson('/api/projects')
                ->assertUnauthorized();
    }
}
