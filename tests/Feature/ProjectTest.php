<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unlogged_user_cannot_access_to_project()
    {
        // $this->withoutExceptionHandling();

        $this->getJson('/api/projects')
                ->assertUnauthorized();
    }

    /** @test */
    public function list_projects()
    {
        $this->withoutExceptionHandling();

        $user = $this->getLoggedUser();

        $project1 = Project::factory(['user_id' => $user->id])->create();

        $this->getJson('/api/projects')
            ->dump()
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_name', auth()->user()->name) // Assert using resource
            ;
    }
}
