<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Staff\Challenge\GetChallengeTool;
use App\Mcp\Tools\Staff\Challenge\ListChallengeShowcasesTool;
use App\Mcp\Tools\Staff\Challenge\ListChallengesTool;
use App\Mcp\Tools\Staff\Course\GetCourseTool;
use App\Mcp\Tools\Staff\Course\GetLessonTool;
use App\Mcp\Tools\Staff\Course\ListCoursesTool;
use App\Mcp\Tools\Staff\Course\ListLessonsTool;
use App\Mcp\Tools\Staff\PracticeArea\ListPracticeAreasTool;
use App\Mcp\Tools\Staff\Showcase\GetShowcaseTool;
use App\Mcp\Tools\Staff\Showcase\ListShowcasesTool;
use App\Mcp\Tools\Staff\Showcase\ListShowcaseUpvotersTool;
use App\Mcp\Tools\Staff\User\GetUserTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Tool;

#[Name('vibecode.law Staff MCP')]
#[Version('0.0.1')]
#[Instructions('Provides staff-only tools and resources for analysing vibecode.law data. Requires explicitly granted permission to use.')]
class StaffServer extends Server
{
    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        ListShowcasesTool::class,
        GetShowcaseTool::class,
        ListShowcaseUpvotersTool::class,
        ListChallengesTool::class,
        GetChallengeTool::class,
        ListChallengeShowcasesTool::class,
        ListCoursesTool::class,
        GetCourseTool::class,
        ListLessonsTool::class,
        GetLessonTool::class,
        ListPracticeAreasTool::class,
        GetUserTool::class,
    ];

    /**
     * @var array<int, class-string<Server\Resource>>
     */
    protected array $resources = [];

    /**
     * @var array<int, class-string<Prompt>>
     */
    protected array $prompts = [];
}
