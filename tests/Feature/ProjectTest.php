<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
            // ->dump()
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_name', auth()->user()->name) // Assert using resource
            ;
    }

    /** @test */
    public function logged_user_can_create_a_project()
    {
        $user = $this->getLoggedUser();

        $data = [
            'name' => $this->faker->sentence()
        ];

        $this->postJson('/api/projects', $data)
                // ->dump()
                ->assertJsonPath('data.user_name', $user->name)
                ->assertStatus(201)
        ;

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseHas('projects', [
            'id'      => 1,
            'name'    => $data['name'],
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function validation_for_creating_project()
    {
        $user = $this->getLoggedUser();

        $this->postJson('/api/projects')
                ->assertJsonValidationErrors(['name']);

        $this->postJson('/api/projects', ['name' => 'az'])
                ->assertJsonValidationErrors(['name']);
    }
}
