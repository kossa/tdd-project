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

    /** @test */
    public function read_a_project()
    {
        $user = $this->getLoggedUser();

        $project1 = Project::factory(['user_id' => $user->id])->create();

        $this->getJson(('/api/projects/' . $project1->id))
                // ->dump()
                ->assertOk()
                ->assertJsonPath('data.name', $project1->name)
        ;
    }

    /** @test */
    public function update_project()
    {
        $user = $this->getLoggedUser();

        $project1 = Project::factory(['user_id' => $user->id])->create();

        $data = ['name' => $this->faker->sentence()];

        $this->putJson('/api/projects/' . $project1->id, $data)
                // ->dump()
                ->assertOk()
                ->assertJsonPath('data.name', $data['name'])
                ->assertJsonPath('data.user_name', $user->name)
        ;

        $this->assertDatabaseHas('projects', ['name' => $data['name'], 'id' => 1]);
    }

    /** @test */
    public function delete_a_project()
    {
        $user = $this->getLoggedUser();

        $project1 = Project::factory(['user_id' => $user->id])->create();

        $this->deleteJson('/api/projects/' . $project1->id)
                // ->dump()
                ->assertOk()
        ;

        $this->assertDatabaseMissing('projects', ['id' => 1, 'name' => $project1->name]);

        $this->assertDatabaseCount('projects', 0);
    }

    /** @test */
    public function user_can_manage_only_his_projects()
    {
        $user = $this->getLoggedUser();

        $project1 = Project::factory(['user_id' => $user->id])->create();
        $project2 = Project::factory()->create();

        // index
        $this->getJson('/api/projects')
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.name', $project1->name)
                ->assertJsonPath('data.0.user_name', $user->name)
            ;

        // show
        $this->getJson('/api/projects/' . $project2->id)
            ->assertForbidden();

        // update
        $this->putJson('/api/projects/' . $project2->id)
            ->assertForbidden();

        // update
        $this->deleteJson('/api/projects/' . $project2->id)
            ->assertForbidden();
    }

    /** @test */
    public function mine_working_fine()
    {
        $user = $this->getLoggedUser();

        $project1 = Project::factory(['user_id' => $user->id])->create();
        $project2 = Project::factory()->create();

        $this->assertTrue($project1->isMine);
        $this->assertFalse($project2->isMine);
    }
}
