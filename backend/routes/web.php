<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Xponent Global API']);
});

// This app has no HTML login form — it's an API consumed by the Nuxt site and
// the Vue admin SPA. Laravel's auth middleware redirects unauthenticated
// non-JSON requests to the named "login" route; without this, that redirect
// throws a RouteNotFoundException (surfacing as a 500 instead of a 401) for
// any unauthenticated request that doesn't send `Accept: application/json`.
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');
