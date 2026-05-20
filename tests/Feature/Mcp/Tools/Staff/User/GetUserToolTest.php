<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\User\GetUserTool;
use App\Models\User;

it('returns the user profile by id', function (): void {
    $user = User::factory()->create([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'job_title' => 'Engineer',
        'organisation' => 'Analytical Engines',
        'bio' => 'Wrote the first algorithm.',
        'linkedin_url' => 'https://linkedin.com/in/ada',
    ]);

    StaffServer::tool(GetUserTool::class, ['id' => $user->id])
        ->assertOk()
        ->assertStructuredContent([
            'id' => $user->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'job_title' => 'Engineer',
            'organisation' => 'Analytical Engines',
            'bio' => 'Wrote the first algorithm.',
            'linkedin_url' => 'https://linkedin.com/in/ada',
        ]);
});

it('returns an error when the user does not exist', function (): void {
    StaffServer::tool(GetUserTool::class, ['id' => 999999])
        ->assertHasErrors(['User with id [999999] was not found.']);
});

it('rejects invalid input', function (): void {
    StaffServer::tool(GetUserTool::class, [])->assertHasErrors();
    StaffServer::tool(GetUserTool::class, ['id' => 0])->assertHasErrors();
    StaffServer::tool(GetUserTool::class, ['id' => 'abc'])->assertHasErrors();
});
