<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {

    $routes->setRouteClass(DashedRoute::class);
    $routes->setExtensions(['json']);//needed for json ext when testing the APIS

    $routes->scope('/', function (RouteBuilder $builder): void {

        $builder->connect('/login', ['controller' => 'Users', 'action' => 'login']);
        $builder->connect('/register', ['controller' => 'Users', 'action' => 'add']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/users', ['controller' => 'Users', 'action' => 'index']);
        $builder->connect('/users/change-role/*', ['controller' => 'Users', 'action' => 'changeRole']);
        $builder->connect('/users/ban/*', ['controller' => 'Users', 'action' => 'banUser']);
        $builder->connect('/users/unban/*', ['controller' => 'Users', 'action' => 'unBanUser']);

        // OAuth routes
        $builder->connect('/oauth/google-callback', ['controller' => 'OAuth', 'action' => 'googleCallback']);
        $builder->connect('/oauth/facebook-callback', ['controller' => 'OAuth', 'action' => 'facebookCallback']);

        // Primary app routes
        $builder->connect('/', ['controller' => 'Pets', 'action' => 'index']);
        $builder->connect('/pets/edit/*', ['controller' => 'Pets', 'action' => 'edit']);
        $builder->connect('/my-pets', ['controller' => 'Pets', 'action' => 'myPets']);
        $builder->connect('/likes/toggle/*', ['controller' => 'Likes', 'action' => 'toggle']);
        $builder->connect('/pet/{slug}', ['controller' => 'Pets', 'action' => 'view'], ['pass' => ['slug'], '_name' => 'pet:view']);

        $builder->setExtensions(['json']);

        $builder->connect('/users/{id}/pets',
            ['controller' => 'Pets', 'action' => 'getUsersPets'],
            ['pass' => ['id'], 'id' => '\d+']
        );

        $builder->resources('Pets');


        $builder->connect('/pages/*', 'Pages::display');


        $builder->fallbacks();
    });

};
