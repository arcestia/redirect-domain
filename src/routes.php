<?php

use Slim\App;
use App\Controllers\DomainController;

return function (App $app) {

    // Main redirect handler (should be last)
    $app->get('/{path:.*}', [DomainController::class, 'handleRedirect']);
};