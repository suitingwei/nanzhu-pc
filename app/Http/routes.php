<?php

Route::get('/', 'HomeController@welcome');

Route::group(['middleware' => ['web']], function () {
    Route::auth();
});

// new manage
Route::group(['prefix' => 'manage','namespace' => 'Manage'], function () {

    Route::resource('/notices', 'NoticesController');

    Route::resource('/messages', 'MessagesController');
    Route::get('/messages/{id}/receivers', 'MessagesController@receivers');

    Route::resource('/pages', 'PagesController');
    Route::resource('/plans','PlansController');

    Route::get('/pages/{id}/receivers', 'PagesController@receivers');

    Route::post('/notices/upload','NoticesController@upload');
    Route::post('/messages/upload','MessagesController@upload');
    Route::post('/plans/upload','PlansController@upload');

    Route::post('/messages/{message_id}/redo','MessagesController@redo');

    Route::resource('/reports', 'ReportsController');
    Route::get('/reports/{id}/receivers', 'ReportsController@receivers');

    // new vehicle
    Route::get('ajax-user-locations','VehicleController@ajaxUserLocations');
    Route::resource('/vehicle','VehicleController');


});

