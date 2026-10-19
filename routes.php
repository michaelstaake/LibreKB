<?php
/**
 * Routes configuration for LibreKB
 */

// Authentication routes (moved outside admin)
$router->get('/login', 'AdminController@login');
$router->post('/login', 'AdminController@authenticate');
$router->post('/logout', 'AdminController@logout');

// Password reset routes (MVC-integrated)
$router->get('/password/reset', 'PasswordController@reset');
$router->post('/password/reset', 'PasswordController@sendResetEmail');
$router->get('/password/new', 'PasswordController@newPassword');
$router->post('/password/new', 'PasswordController@updatePassword');

// Profile routes (frontend)
$router->get('/profile', 'ProfileController@show');
$router->post('/profile', 'ProfileController@update');

// Read-only JSON API. Specific paths are registered before {id} paths.
$router->get('/api/v1/articles/search', 'ApiController@searchArticles');
$router->options('/api/v1/articles/search', 'ApiController@preflight');
$router->get('/api/v1/articles/slug/{slug}', 'ApiController@articleBySlug');
$router->options('/api/v1/articles/slug/{slug}', 'ApiController@preflight');
$router->get('/api/v1/articles/{id}', 'ApiController@articleById');
$router->options('/api/v1/articles/{id}', 'ApiController@preflight');
$router->get('/api/v1/categories', 'ApiController@categories');
$router->options('/api/v1/categories', 'ApiController@preflight');
$router->get('/api/v1/categories/slug/{slug}', 'ApiController@categoryBySlug');
$router->options('/api/v1/categories/slug/{slug}', 'ApiController@preflight');
$router->get('/api/v1/categories/{id}', 'ApiController@categoryById');
$router->options('/api/v1/categories/{id}', 'ApiController@preflight');

// Public routes (with privacy check middleware)
$router->get('/', 'HomeController@index');
$router->get('/search', 'SearchController@index');
$router->post('/search', 'SearchController@search');
$router->get('/category/{slug}', 'CategoryController@show');
$router->get('/article/{slug}', 'ArticleController@show');
$router->get('/attachment/{id}', 'AttachmentController@download');

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
            $router->post('/categories/{id}/delete', 'CategoryController@delete');
            
            // Article management (create/edit only, no list view)
            $router->get('/articles/create', 'ArticleController@create');
            $router->post('/articles/create', 'ArticleController@store');
            $router->get('/articles/{id}', 'ArticleController@adminShow');
            $router->post('/articles/{id}', 'ArticleController@update');
            $router->post('/articles/{id}/delete', 'ArticleController@delete');
            $router->post('/articles/{id}/attachments/{attachmentId}/delete', 'ArticleController@deleteAttachment');
        });
        
        // Admin-only routes
        $router->middleware(['role:admin'], function($router) {
            $router->get('/settings', 'AdminController@settingsIndex');
            $router->get('/settings/{section}', 'AdminController@settings');
            $router->post('/settings/{section}', 'AdminController@updateSettings');
            
            // Activity logs
            $router->get('/logs', 'AdminController@logs');
            
            // User management
            $router->get('/users', 'UserController@index');
            $router->get('/users/create', 'UserController@create');
            $router->post('/users/create', 'UserController@store');
            $router->get('/users/{id}', 'UserController@show');
            $router->post('/users/{id}', 'UserController@update');
            $router->post('/users/{id}/delete', 'UserController@delete');
            $router->get('/users/{id}/password', 'UserController@changePassword');
            $router->post('/users/{id}/password', 'UserController@updatePassword');
        });
    });
});

// Special routes for installation and update
$router->get('/install', 'InstallController@index');
$router->post('/install', 'InstallController@install');
$router->get('/upgrade', 'UpgradeController@index');
$router->post('/upgrade', 'UpgradeController@run');
