<?php
/**
 * Routes configuration for LibreKB
 */

// Authentication routes (moved outside admin)
$router->get('/login', 'AdminController@login');
$router->post('/login', 'AdminController@authenticate');
$router->get('/logout', 'AdminController@logout');

// Password reset routes (MVC-integrated)
$router->get('/password/reset', 'PasswordController@reset');
$router->post('/password/reset', 'PasswordController@sendResetEmail');
$router->get('/password/new', 'PasswordController@newPassword');
$router->post('/password/new', 'PasswordController@updatePassword');

// Profile routes (frontend)
$router->get('/profile', 'ProfileController@show');
$router->post('/profile', 'ProfileController@update');

// Public routes (with privacy check middleware)
$router->get('/', 'HomeController@index');
$router->get('/search', 'SearchController@index');
$router->post('/search', 'SearchController@search');
$router->get('/category/{slug}', 'CategoryController@show');
$router->get('/article/{slug}', 'ArticleController@show');

// Admin routes
$router->group('/admin', function($router) {
    
    // Protected admin routes (require admin or manager authentication)
    $router->middleware(['auth'], function($router) {
        
        // Dashboard (admin and manager access only)
        $router->middleware(['role:admin,manager'], function($router) {
            $router->get('', 'AdminController@dashboard');
        });
        
        // Content management (admin and manager access)
        $router->middleware(['role:admin,manager'], function($router) {
            // Category management (create/edit only, no list view)
            $router->get('/categories/create', 'CategoryController@create');
            $router->post('/categories/create', 'CategoryController@store');
            $router->get('/categories/{id}', 'CategoryController@adminShow');
            $router->post('/categories/{id}', 'CategoryController@update');
            $router->get('/categories/{id}/delete', 'CategoryController@delete');
            
            // Article management (create/edit only, no list view)
            $router->get('/articles/create', 'ArticleController@create');
            $router->post('/articles/create', 'ArticleController@store');
            $router->get('/articles/{id}', 'ArticleController@adminShow');
            $router->post('/articles/{id}', 'ArticleController@update');
            $router->get('/articles/{id}/delete', 'ArticleController@delete');
        });
        
        // Admin-only routes
        $router->middleware(['role:admin'], function($router) {
            $router->get('/settings', 'AdminController@settings');
            $router->post('/settings', 'AdminController@updateSettings');
            
            // Activity logs
            $router->get('/logs', 'AdminController@logs');
            
            // User management
            $router->get('/users', 'UserController@index');
            $router->get('/users/create', 'UserController@create');
            $router->post('/users/create', 'UserController@store');
            $router->get('/users/{id}', 'UserController@show');
            $router->post('/users/{id}', 'UserController@update');
            $router->get('/users/{id}/delete', 'UserController@delete');
            $router->get('/users/{id}/password', 'UserController@changePassword');
            $router->post('/users/{id}/password', 'UserController@updatePassword');
        });
    });
});

// Special routes for installation and update
$router->get('/install', 'InstallController@index');
$router->post('/install', 'InstallController@install');
