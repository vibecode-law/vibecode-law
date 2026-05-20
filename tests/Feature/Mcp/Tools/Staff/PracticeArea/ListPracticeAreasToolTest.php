<?php

use App\Mcp\Servers\StaffServer;
use App\Mcp\Tools\Staff\PracticeArea\ListPracticeAreasTool;
use App\Models\PracticeArea;

it('returns all practice areas ordered by name', function (): void {
    PracticeArea::query()->delete();

    $litigation = PracticeArea::factory()->create(['name' => 'Litigation', 'slug' => 'litigation']);
    $contracts = PracticeArea::factory()->create(['name' => 'Contracts', 'slug' => 'contracts']);

    StaffServer::tool(ListPracticeAreasTool::class)
        ->assertOk()
        ->assertStructuredContent([
            'items' => [
                ['id' => $contracts->id, 'name' => 'Contracts', 'slug' => 'contracts'],
                ['id' => $litigation->id, 'name' => 'Litigation', 'slug' => 'litigation'],
            ],
            'total_count' => 2,
        ]);
});
