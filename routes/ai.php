<?php

use App\Http\Middleware\EnsureCanAccessStaffMcp;
use App\Mcp\Servers\StaffServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp/staff', StaffServer::class)
    ->middleware(['auth:api', EnsureCanAccessStaffMcp::class]);
