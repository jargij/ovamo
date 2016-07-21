<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::get('vacaturesites/getAllScrapedVacatureWebsiteDates', 'VacatureSiteController@getAllScrapedVacatureWebsiteDates');
Route::get('vacaturesites/info', 'VacatureSiteController@info');
Route::get('info', 'VacatureSiteController@info');

Route::get('vacaturesites/{vacatureId}/{datum}', 'VacatureSiteController@loadStandardContent');

Route::get('action/nextschool', 'VacatureSiteController@nextschool');
Route::get('action/previousschool', 'VacatureSiteController@previousschool');
Route::get('action/nextdate', 'VacatureSiteController@nextdate');
Route::get('action/previousdate', 'VacatureSiteController@previousdate');

Route::get('getonesite/{vacatureId}/{datum}', 'VacatureSiteController@ajaxRequestForOneVacatureWebsite');

Route::resource('vacaturesites', 'VacatureSiteController');

Route::get('scraper', 'ScraperController@index');

Route::get('/', function () {
    return view('welcome');
});

