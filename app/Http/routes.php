<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/
Route::group(['middleware' => ['authOne']], function () {


    
    /**
     * Route for the requests of Method Groups
     */
    Route::get('methodGroup/list', 'MethodGroupsController@index');
    Route::get('methodGroup/{id}/listMethods', 'MethodGroupsController@listMethods');
    Route::get('methodGroup/{id}/edit', 'MethodGroupsController@edit');
    Route::resource('methodGroup', 'MethodGroupsController', ['only' => ['show', 'store', 'update', 'destroy']]);

    /**
     * Route for the requests of Methods
     */
    Route::get('method/list', 'MethodsController@index');
    Route::get('method/{id}/edit', 'MethodsController@edit');
    Route::resource('method', 'MethodsController', ['only' => ['show', 'store', 'update', 'destroy']]);

    /**
     * Route for the requests of Configurations
     */
    Route::get('configuration/list', 'ConfigurationsController@index');
    Route::get('configuration/{id}/edit', 'ConfigurationsController@edit');
    Route::resource('configuration', 'ConfigurationsController', ['only' => ['show', 'store', 'update', 'destroy']]);

    /**
     * Route for the requests of Votes
     */
    Route::post('vote/listVotes/{eventKey}', 'VotesController@listVotes');
    Route::post('vote/showAll/{eventKey}', 'VotesController@showAll');
    Route::post('vote/eventVotes/{eventKey}', 'VotesController@eventVotes');
    Route::post('vote/voteCode', 'VotesController@getDataForVoteCode');
    Route::get('vote/voteTimeline/', 'VotesController@voteTimeline');
    Route::post('vote/userVotesCount', 'VotesController@userVotesCount');
    Route::get('vote/getVoteList', 'VotesController@getVoteList');
    Route::post('vote/submitUserVote', 'VotesController@submitUserVote');
    Route::delete('vote/deleteUserVotes', 'VotesController@deleteUserVotes');
    Route::post('vote/deleteVotes', 'VotesController@deleteVotes'); 
    Route::resource('vote', 'VotesController', ['only' => ['show','store','destroy']]);

    /**
     * Route for the requests of Events
     */
    Route::post('event/getPadVotes', 'EventsController@getPadVotes');
    Route::post('event/voteCounts', 'EventsController@getEventsVoteCount');
    Route::get('event/getTopicVoteSubmitted', 'EventsController@getTopicVoteSubmitted');
    Route::post('event/unSubmitUserVotesInEvent', 'EventsController@unSubmitUserVotesInEvent');
    Route::post('event/deleteUserVotesInVoteEvent', 'EventsController@deleteUserVotesInVoteEvent');
    Route::post('event/registerUserInPersonVoting', 'EventsController@registerUserInPersonVoting');
    Route::post('event/attachUserToVoteEventWithCode' , 'EventsController@attachUserToVoteEventWithCode');
    Route::post('event/storePublicUserVoting', 'EventsController@storePublicUserVoting');
    Route::post('event/userVotes' , 'EventsController@getUserVotesForEvent');
    Route::post('event/userVotesForEvent' , 'EventsController@getUserVoteForEvent');
    Route::post('event/getCbTotalVotes/', 'EventsController@getCbTotalVotes');
    Route::post('event/showEvents', 'EventsController@showEvents');
    Route::post('event/showEventsNoTranslation', 'EventsController@showEventsNoTranslation');
    Route::get('event/list', 'EventsController@index');
    Route::post('event/{key}/configuration', 'EventsController@addConfigEvents');
    Route::delete('event/{key}/configuration', 'EventsController@removeConfigEvents');
    Route::get('event/{key}/votes', 'EventsController@totalVotes');
    Route::get('event/{key}/voteStatus', 'EventsController@voteStatus');
    Route::get('event/{key}/voteResults', 'EventsController@voteResults');
    Route::post('event/allVoteResults', 'EventsController@allVoteResults');
    Route::get('event/{key}/getEventAndVotes', 'EventsController@getEventAndVotes');
    Route::post('event/{eventKey}/submitVotes' , 'EventsController@submitVotes');
    Route::put('event/manualUpdateTopicVotesInfo' , 'EventsController@manualUpdateTopicVotesInfo');
    Route::get('eventOpen/{eventKey}', 'EventsController@eventOpen');
    Route::resource('event', 'EventsController', ['only' => ['show', 'store', 'update', 'destroy']]);


    /**
    * Route for the requests of EventLevels
    */
    Route::get('eventlevels/list', 'EventLevelsController@index');
    Route::get('eventlevels/eventlevel', 'EventLevelsController@eventLevel');
    Route::get('eventlevels/storeEventLevel', 'EventLevelsController@store');
    Route::get('eventlevels/updateEventLevel', 'EventLevelsController@update');
    Route::get('eventlevels/eventlevelCbKey', 'EventLevelsController@eventLevelCbKey');
    Route::get('eventlevels/getAllEventLevelsByCbKey', 'EventLevelsController@getAllEventLevelsByCbKey');


    /**
    * Route for the requests of General Configuration Types
    */

    Route::get('generalConfigType/list', 'GeneralConfigTypeController@index');

    /* SMS Vote Methods */
    Route::post("smsVote","VotesController@smsVote");
});
