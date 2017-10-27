<?php

namespace App\Http\Controllers;

use App\Configuration;
use App\ConfigurationEvent;
use App\Event;
use App\GeneralConfig;
use App\Method;
use App\One\One;
use App\UserEventCode;
use App\UserEventCodeVote;
use App\VoteMethods\Like;
use App\VoteMethods\MultiVote;
use App\VoteMethods\NegativeVote;
use App\VoteMethods\RankVote;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;

/**
 * Class EventsController
 * @package App\Http\Controllers
 */

class EventsController extends Controller
{
    protected $keysRequired = [
        'create' => [
            'method_id',
            'start_date',
            'end_date',
            'end_time'
        ],
        'update' => [
            'start_date',
            'end_date',
            'end_time'
        ],
        'configurationCreate' => [
            'configuration_id',
            'value'
        ],
        'configurationDelete' => [
            'configuration_id'
        ]

    ];


    /**
     * @SWG\Tag(
     *   name="Event",
     *   description="Everything about Events",
     * )
     *
     *  @SWG\Definition(
     *      definition="eventErrorDefault",
     *      @SWG\Property(property="error", type="string", format="string")
     *  )
     *
     * @SWG\Definition(
     *   definition="eventMethodReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="id", format="string", type="string"),
     *           @SWG\Property(property="code", format="string", type="string"),
     *           @SWG\Property(property="method_group_id", format="integer", type="integer"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="description", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string")
     *       )
     *   }
     * )
     *
     * @SWG\Definition(
     *   definition="eventConfigurationsReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="id", format="integer", type="integer"),
     *           @SWG\Property(property="configuration_event_key", format="string", type="string"),
     *           @SWG\Property(property="configuration_id", format="integer", type="integer"),
     *           @SWG\Property(property="event_id", format="integer", type="integer"),
     *           @SWG\Property(property="general_config_id", format="integer", type="integer"),
     *           @SWG\Property(property="value", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="parameter_type", format="string", type="string"),
     *           @SWG\Property(property="configuration_code", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string")
     *       )
     *   }
     * )
     *
     * @SWG\Definition(
     *   definition="eventShowReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="key", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="method_id", format="integer", type="integer"),
     *           @SWG\Property(property="created_by", format="string", type="string"),
     *           @SWG\Property(property="start_date", format="date", type="string"),
     *           @SWG\Property(property="end_date", format="date", type="string"),
     *           @SWG\Property(property="start_time", format="string", type="string"),
     *           @SWG\Property(property="end_time", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string"),
     *           @SWG\Property(
     *              property="method",
     *              type="object",
     *              allOf={
     *                  @SWG\Schema(ref="#/definitions/eventMethodReply")
     *              }
     *
     *           ),
     *           @SWG\Property(
     *              property="configurations",
     *              type="array",
     *              @SWG\Items(ref="#/definitions/eventConfigurationsReply")
     *           ),
     *       )
     *   }
     * )
     *
     *
     * @SWG\Definition(
     *   definition="eventConfigurationsCreate",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="configuration_id", format="integer", type="integer"),
     *           @SWG\Property(property="value", format="string", type="string")
     *       )
     *   }
     * )
     *
     *
     *  @SWG\Definition(
     *   definition="eventCreate",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="method_id", format="integer", type="integer"),
     *           @SWG\Property(property="start_date", format="date", type="string"),
     *           @SWG\Property(property="end_date", format="date", type="integer"),
     *           @SWG\Property(property="start_time", format="string", type="string"),
     *           @SWG\Property(property="end_time", format="date", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string"),
     *           @SWG\Property(
     *              property="configurations",
     *              type="array",
     *              allOf={
     *                  @SWG\Schema(ref="#/definitions/eventConfigurationsCreate")
     *              }
     *           )
     *       )
     *   }
     * )
     *
     *  @SWG\Definition(
     *   definition="eventReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="key", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="method_id", format="integer", type="integer"),
     *           @SWG\Property(property="created_by", format="string", type="string"),
     *           @SWG\Property(property="start_date", format="date", type="string"),
     *           @SWG\Property(property="end_date", format="date", type="string"),
     *           @SWG\Property(property="start_time", format="string", type="string"),
     *           @SWG\Property(property="end_time", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string"),
     *       )
     *   }
     * )
     *
     * @SWG\Definition(
     *     definition="eventDeleteReply",
     *     @SWG\Property(property="string", type="string", format="string")
     * )
     *
     *
     */




    /**
     * Request list of all Events
     *
     * @return list
     */
    public function index(Request $request)
    {
        try {
            $events = Event::all();

            return response()->json(['data' => $events], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Events list'], 500);
        }
    }


    /**
     * @SWG\Get(
     *  path="/event/{event_key}",
     *  summary="Show a Event detail",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Event"},
     *
     *  @SWG\Parameter(
     *      name="event_key",
     *      in="path",
     *      description="Event key",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-ENTITY-KEY",
     *      in="header",
     *      description="Entity Token",
     *      required=true,
     *      type="string"
     *  ),
     *  @SWG\Parameter(
     *      name="LANG-CODE",
     *      in="header",
     *      description="Language code",
     *      required=true,
     *      type="string"
     *  ),
     *  @SWG\Parameter(
     *      name="LANG-CODE-DEFAULT",
     *      in="header",
     *      description="Default Language code",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response="200",
     *      description="Show the Event data",
     *      @SWG\Schema(ref="#/definitions/eventShowReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Event not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Event",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Request of one Event
     * Returns the attributes of the Event
     * @param Request $request
     * @param $key
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show(Request $request, $key)
    {
        try {
            $event = Event::whereKey($key)->firstOrFail();

            //  begin timezone conversion
            $timezone = empty($request->header('timezone')) ? 'utc' : $request->header('timezone');
            $event = ONE::timezoneConversion($event, 'UTC', $timezone);
            //  end timezone conversion


            $method = $event->method()->first();

            if (!($method->translation($request->header('LANG-CODE')))) {
                if (!$method->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!$method->translation('en'))
                        return response()->json(['error' => 'No translation found'], 404);
                }
            }

            $configurationEvents = $event->configurationEvents()->get();

            foreach ($configurationEvents as $configurationEvent) {
                $config = $configurationEvent->configuration()->first();
                if (!($config->translation($request->header('LANG-CODE')))) {
                    if (! $config->translation($request->header('LANG-CODE-DEFAULT'))){
                        if (!$method->translation('en'))
                            return response()->json(['error' => 'No translation found'], 404);
                    }
                }

                $configurationEvent['name'] = $config->name;
                $configurationEvent['parameter_type'] = $config->parameter_type;
                $configurationEvent['id'] = $config->id;
                $configurationEvent['configuration_code'] = $config->code;
            }

            $event['method'] = $method;
            $event['configurations'] = $configurationEvents;


            return response()->json($event, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Event'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * @SWG\Post(
     *  path="/event",
     *  summary="Create a Event",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Event"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Event Created Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/eventCreate")
     *  ),
     *
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *  @SWG\Parameter(
     *      name="LANG-CODE",
     *      in="header",
     *      description="Language code",
     *      required=true,
     *      type="string"
     *  ),
     *  @SWG\Parameter(
     *      name="LANG-CODE-DEFAULT",
     *      in="header",
     *      description="Default Language code",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="TIMEZONE",
     *      in="header",
     *      description="Timezone",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response=200,
     *      description="Created Event",
     *      @SWG\Schema(ref="#/definitions/eventReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to update Method",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Store a new Event in the database
     * Return the Attributes of the Event
     * @param Request $request
     * @return static
     */
    public function store(Request $request)
    {
        $userKey = ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired['create'], $request);

        try {
            do {
                $rand = str_random(32);
                if (!($exists = Event::whereKey($rand)->exists())) {
                    $key = $rand;
                }
            } while ($exists);

            $event = Event::create(
                [
                    'method_id'     => $request->json('method_id'),
                    'key'           => $key,
                    'name'          => $request->json('name') ?? null,
                    'code'          => $request->json('code') ?? null,
                    'created_by'    => $userKey,
                    'start_date'    => $request->json('start_date'),
                    'end_date'      => $request->json('end_date'),
                    'start_time'    => !empty($request->json('start_time')) ? $request->json('start_time') : '00:00',
                    'end_time'      => !empty($request->json('end_time')) ? $request->json('end_time'): '00:00',
                ]
            );

            //  begin timezone conversion
            $timezone = empty($request->header('timezone')) ? 'utc' : $request->header('timezone');
            $event = ONE::timezoneConversion($event, $timezone);
            $event->save();
            //  end timezone conversion

            if (!empty($request->json('configurations'))) {
                foreach ($request->json('configurations') as $configuration) {
                    if (!empty($configuration['configuration_id'] && isset($configuration['value']))) {
                        if(Configuration::whereId($configuration['configuration_id'])->whereMethodId($request->json('method_id'))->exists()){
                            do {
                                $rand = str_random(32);

                                if (!($exists = ConfigurationEvent::whereConfigurationEventKey($rand)->exists())) {
                                    $key = $rand;
                                }
                            } while ($exists);

                            $event->configurationEvents()->create([
                                'configuration_event_key'   => $key,
                                'configuration_id'          => $configuration['configuration_id'],
                                'value'                     => $configuration['value']
                            ]);
                        }
                    }
                }
            }

            if (!empty($request->json('configuration_advance'))) {

                foreach ($request->json('configuration_advance') as $configurationAdvance) {
                    do {
                        $rand = str_random(32);

                        if (!($exists = GeneralConfig::whereGeneralConfigKey($rand)->exists())) {
                            $key = $rand;
                        }
                    } while ($exists);
                    $generalConfig = GeneralConfig::create(
                        [
                            'general_config_key'    => $key,
                            'general_config_type_id'=> 1,
                            'parameter_key'         => $configurationAdvance['parameter_key'],
                            'greater'               => !empty($configurationAdvance['greater']) ? $configurationAdvance['greater'] : 0,
                            'equal'                 => !empty($configurationAdvance['equal']) ? $configurationAdvance['equal'] : 0,
                            'less'                  => !empty($configurationAdvance['less']) ? $configurationAdvance['less'] : 0,
                            'value'                 => $configurationAdvance['value'],
                        ]
                    );
                    foreach ($configurationAdvance['configuration'] as $configuration) {
                        if (!empty($configuration['configuration_id'] && isset($configuration['value']))) {
                            if(Configuration::whereId($configuration['configuration_id'])->whereMethodId($request->json('method_id'))->exists()){
                                do {
                                    $rand = str_random(32);

                                    if (!($exists = ConfigurationEvent::whereConfigurationEventKey($rand)->exists())) {
                                        $key = $rand;
                                    }
                                } while ($exists);
                                $event->configurationEvents()->create([
                                    'configuration_event_key'   => $key,
                                    'configuration_id'          => $configuration['configuration_id'],
                                    'general_config_id'         => $generalConfig->id,
                                    'value'                     => $request->json('value')
                                ]);
                            }
                        }
                    }
                }
            }

            $event = Event::with('configurationEvents', 'method')->findOrFail($event->id);
            return response()->json($event, 201);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store the Event'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Put(
     *  path="/event/{event_key}",
     *  summary="Update a Event",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Event"},
     *
     *  @SWG\Parameter(
     *      name="event_key",
     *      in="path",
     *      description="Method Id",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Event Update Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/eventCreate")
     *  ),
     *
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *  @SWG\Parameter(
     *      name="LANG-CODE",
     *      in="header",
     *      description="Language code",
     *      required=true,
     *      type="string"
     *  ),
     *  @SWG\Parameter(
     *      name="LANG-CODE-DEFAULT",
     *      in="header",
     *      description="Default Language code",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="TIMEZONE",
     *      in="header",
     *      description="Timezone",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response=200,
     *      description="Update a Event",
     *      @SWG\Schema(ref="#/definitions/eventReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to update Event",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */

    /**
     * Update a existing Event
     * Return the Attributes of the Event Updated
     * @param Request $request
     * @param $key
     * @return mixed
     */
    public function update(Request $request, $key)
    {
        $userKey = ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired['update'], $request);

        try {

            $event = Event::whereKey($key)->firstOrFail();

            $event->start_date  = $request->json('start_date');
            $event->end_date    = $request->json('end_date');
            $event->start_time  = $request->json('start_time');
            $event->end_time    = $request->json('end_time');
            $event->name        = $request->json('name') ?? null;
            $event->code        = $request->json('code') ?? null;

            //  begin timezone conversion
            $timezone = empty($request->header('timezone')) ? 'utc' : $request->header('timezone');



            //$event = ONE::timezoneConversion($event, $timezone);
            $event->save();
            //  end timezone conversion

            if (!empty($request->json('configurations'))) {
                foreach ($request->json('configurations') as $configuration) {
                    if (!empty($configuration['configuration_id'] && isset($configuration['value']))) {
                        if(ConfigurationEvent::whereConfigurationId($configuration['configuration_id'])->whereEventId($event->id)->whereGeneralConfigId(null)->exists()){
                            $configurationEvent = ConfigurationEvent::whereConfigurationId($configuration['configuration_id'])->whereEventId($event->id)->whereGeneralConfigId(null)->first();
                            $configurationEvent->value = $configuration['value'];
                            $configurationEvent->save();
                        }else{
                            do {
                                $rand = str_random(32);

                                if (!($exists = ConfigurationEvent::whereConfigurationEventKey($rand)->exists())) {
                                    $key = $rand;
                                }
                            } while ($exists);

                            $event->configurationEvents()->create([
                                'configuration_event_key'   => $key,
                                'configuration_id'          => $configuration['configuration_id'],
                                'value'                     => $configuration['value']
                            ]);
                        }

                    }
                }
            }

            if (!empty($request->json('configuration_advance'))) {

                foreach ($request->json('configuration_advance') as $configurationAdvance) {
                    if(isset($configurationAdvance['general_config_key'])){
                        $generalConfig = GeneralConfig::whereGeneralConfigKey($configurationAdvance['general_config_key'])->first();
                    }
                    else{
                        do {
                            $rand = str_random(32);

                            if (!($exists = GeneralConfig::whereGeneralConfigKey($rand)->exists())) {
                                $key = $rand;
                            }
                        } while ($exists);
                        $generalConfig = GeneralConfig::create(
                            [
                                'general_config_key'    => $key,
                                'general_config_type_id'=> 1,
                                'parameter_key'         => $configurationAdvance['parameter_key'],
                                'greater'               => !empty($configurationAdvance['greater']) ? $configurationAdvance['greater'] : 0,
                                'equal'                 => !empty($configurationAdvance['equal']) ? $configurationAdvance['equal'] : 0,
                                'less'                  => !empty($configurationAdvance['less']) ? $configurationAdvance['less'] : 0,
                                'value'                 => $configurationAdvance['value'],
                            ]
                        );
                    }

                    foreach ($configurationAdvance['configuration'] as $configuration) {
                        if (!empty($configuration['configuration_id'] && isset($configuration['value']))) {
                            if (!empty($configuration['configuration_id'] && isset($configuration['value']))) {
                                if (Configuration::whereId($configuration['configuration_id'])->whereMethodId($request->json('method_id'))->exists()) {
                                    if (ConfigurationEvent::whereConfigurationId($configuration['configuration_id'])->whereEventId($event->id)->whereGeneralConfigId($generalConfig->id)->exists()) {
                                        $configurationEvent = ConfigurationEvent::whereConfigurationId($configuration['configuration_id'])->whereEventId($event->id)->whereGeneralConfigId($generalConfig->id)->first();
                                        $configurationEvent->value = $configuration['value'];
                                        $configurationEvent->save();
                                    } else {
                                        do {
                                            $rand = str_random(32);

                                            if (!($exists = ConfigurationEvent::whereConfigurationEventKey($rand)->exists())) {
                                                $key = $rand;
                                            }
                                        } while ($exists);

                                        $event->configurationEvents()->create([
                                            'configuration_event_key'   => $key,
                                            'configuration_id'          => $configuration['configuration_id'],
                                            'value'                     => $configuration['value']
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $event = Event::with('configurationEvents', 'method')->findOrFail($event->id);

            return response()->json($event, 201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update Event'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/eventv/{event_key}",
     *  summary="Delete a Method",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Event"},
     *
     * @SWG\Parameter(
     *      name="event_key",
     *      in="path",
     *      description="Method Id",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-MODULE-TOKEN",
     *      in="header",
     *      description="Module Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Parameter(
     *      name="X-AUTH-TOKEN",
     *      in="header",
     *      description="User Auth Token",
     *      required=true,
     *      type="string"
     *  ),
     *
     *  @SWG\Response(
     *      response=200,
     *      description="OK",
     *      @SWG\Schema(ref="#/definitions/eventDeleteReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *   ),
     *
     *  @SWG\Response(
     *      response="404",
     *      description="Event not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to delete Event",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */

    /**
     * Delete existing Event
     * @param Request $request
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $key)
    {
        $userKey = ONE::verifyToken($request);

        try {
            $event = Event::whereKey($key)->firstOrFail();
            $event->delete();

            return response()->json('Ok', 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete Event'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Store a new Configuration Event in the database
     * Return the Attributes of the Configuration Event created
     * @param Request $request
     *
     * @return static
     */
    public function addConfigEvents(Request $request, $key)
    {
        $userKey = ONE::verifyToken($request);

        ONE::verifyKeysRequest($this->keysRequired['configurationCreate'], $request);

        //VERIFY PERMISSIONS

        try {
            $event = Event::whereKey($key)->firstOrFail();
            $event->configurations()->attach($request->json('configuration_id'), ['value' => $request->json('value'), 'created_by' => $userKey]);

            $event = Event::with(['configurations' =>
                function ($query) {
                    $query->select(array('configuration_id', 'value'));
                }
            ])->whereKey($key)->firstOrFail();
            return response()->json($event, 201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store new Event Configuration'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * Delete a  Configuration Event in the database
     * Return the Attributes of the Configuration Event created
     * @param Request $request
     *
     * @return static
     */
    public function removeConfigEvents(Request $request, $key)
    {
        $userKey = ONE::verifyToken($request);

        ONE::verifyKeysRequest($this->keysRequired['configurationDelete'], $request);

        //VERIFY PERMISSIONS

        try {
            $event = Event::whereKey($key)->findOrFail();
            $event->configurations()->dettach($request->json('configuration_id'));

            return response()->json('Ok', 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete Event Configuration'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * Return the Attribute Total of Votes for the Event
     * @param Request $request
     * @return static
     */
    public function totalVotes(Request $request, $eventKey)
    {
        try {
            $event = Event::whereKey($eventKey)->firstOrFail();;
            $data['votes'] = $event->votes()->get();
            $data['positives'] = ($event->votes()->select(DB::raw('count(*) as total, vote_key'))->where('value', '>', 0)->groupBy('vote_key')->pluck('total', 'vote_key'));
            $data['negatives'] = ($event->votes()->select(DB::raw('count(*) as total, vote_key'))->where('value', '<', 0)->groupBy('vote_key')->pluck('total', 'vote_key'));
            $data['users'] = ($event->votes()->select(DB::raw('count(*) as total, user_key'))->groupBy('user_key')->pluck('total', 'user_key'));
            return response()->json(["data" => $data], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve Votes'], 500);
        }
    }

    /**
     * Request of Method Translations and Configurations Translations associated with Events
     * Returns the Method Translations and Configurations Translations of the Event
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEvents(Request $request)
    {
        $events = [];
        try {
            if (!empty($request->json('events'))) {

                foreach ($request->json('events') as $event) {
                    $event = Event::whereKey($event)->first();

                    if (!is_null($event)) {

                        //  begin timezone conversion
                        $timezone = empty($request->header('timezone')) ? 'utc' : $request->header('timezone');
                        $event = ONE::timezoneConversion($event,'UTC', $timezone);
                        //  end timezone conversion

                        $method = $event->method()->first();

                        if (!($method->translation($request->header('LANG-CODE')))) {
                            if (!$method->translation($request->header('LANG-CODE-DEFAULT'))){
                                if (!$method->translation('en'))
                                    return response()->json(['error' => 'No translation found'], 404);
                            }
                        }

                        $configurationEvents = $event->configurationEvents()->get();

                        foreach ($configurationEvents as $configurationEvent) {
                            $config = $configurationEvent->configuration()->first();
                            if (!($config->translation($request->header('LANG-CODE')))) {
                                if (! $config->translation($request->header('LANG-CODE-DEFAULT'))){
                                    if (!$method->translation('en'))
                                        return response()->json(['error' => 'No translation found'], 404);
                                }
                            }

                            $configurationEvent['name'] = $config->name;
                            $configurationEvent['parameter_type'] = $config->parameter_type;
                            $configurationEvent['id'] = $config->id;
                            $configurationEvent['configuration_code'] = $config->code;
                        }

                        $event['method'] = $method;
                        $event['configurations'] = $configurationEvents;

                        $events[] = $event;
                    }
                }
            }
            return response()->json(['data' => $events], 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Event'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function voteStatus(Request $request, $key)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            if(!empty($request->user_key)){
                $userKey = $request->user_key;
            }
        }

        try{
            $event = Event::whereKey($key)->firstOrfail();
            $canVote = false;
            $data['vote'] = $this->checkOpenVoting($event);

            $data["alreadySubmitted"] = $event->votes()->whereUserKey($userKey)->whereSubmitted(1)->exists();
            $data['votes'] = $event->votes()->select('vote_key',DB::raw('SUM(value) as value'))->whereUserKey($userKey)->groupBy('vote_key')->get()->keyBy('vote_key');
            $totalSummary = [];

            switch ($event->method_id) {
                case 1:
                    $likes = new Like($event);
                    $votesRemaining = null;
                    $totalVotes = $likes->getTotalVotes();
                    break;
                case 2:
                    $positiveVote = new MultiVote($event);
                    $totalVotes = $positiveVote->getTotalVotes();
                    $totalSummary = $positiveVote->getTotalSummary();
                    $votesRemaining = $positiveVote->getRemainingVotes($userKey);
                    $canVote        = $positiveVote->verifyVoteSubmit($userKey);
                    $submitedDate = $positiveVote->verifyVoteSubmitDate($userKey);

                    break;
                case 3:
                    $negativeVote = new NegativeVote($event);
                    $totalVotes = $negativeVote->getTotalVotes();
                    $votesRemaining = $negativeVote->getRemainingVotes($userKey);
                    $canVote        = $negativeVote->verifyVoteSubmit($userKey);
                    break;
                case 4:
                    $rankVote = new RankVote($event);
                    $votesRemaining = null;
                    $totalVotes = $rankVote->getTotalVotes();
                    break;
            }
            $data['can_vote'] = $canVote;
            $data['remaining_votes'] = $votesRemaining;
            $data['total_votes'] = $totalVotes;
            $data['total_summary'] = $totalSummary;
            $data['submited_date'] = $submitedDate ?? null;

            return response()->json($data, 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to verify status vote'], 500);
        }
    }

    /**
     * @param Request $request
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function voteResults(Request $request, $key)
    {
        try{
            $totalVotes=[];
            $totalSummary=[];
            $event = Event::whereKey($key)->firstOrfail();
            switch ($event->method_id) {
                case 1:
                    $likes = new Like($event);
                    $votesRemaining = null;
                    $totalVotes = $likes->getTotalVotes();
                    break;
                case 2:
                    $positiveVote = new MultiVote($event);
                    $totalVotes = $positiveVote->getTotalVotes();
                    $totalSummary = $positiveVote->getTotalSummary();
                    break;
                case 3:
                    $negativeVote = new NegativeVote($event);
                    $totalVotes = $negativeVote->getTotalVotes();
                    break;
            }
            $data['total_votes'] = $totalVotes;
            $data['total_summary'] = $totalSummary;

            return response()->json($data, 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to verify vote results'], 500);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allVoteResults(Request $request)
    {
        try{
            $keys = $request->json('keys');
            $data = [];

            foreach ($keys as $key) {
                $totalVotes=[];
                $totalSummary=[];
                $event = Event::with('method')->whereKey($key)->firstOrfail();
                switch ($event->method_id) {
                    case 1:
                        $likes = new Like($event);
                        $votesRemaining = null;
                        $totalVotes = $likes->getTotalVotes();
                        break;
                    case 2:
                        $positiveVote = new MultiVote($event);
                        $totalVotes = $positiveVote->getTotalVotes();
                        $totalSummary = $positiveVote->getTotalSummary();
                        break;
                    case 3:
                        $negativeVote = new NegativeVote($event);
                        $totalVotes = $negativeVote->getTotalVotes();
                        break;
                }
                $data[$event->method->code]['total_votes'] = $totalVotes;
                $data[$event->method->code]['total_summary'] = $totalSummary;
            }

            return response()->json($data, 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to verify vote results'], 500);
        }
    }

    /**
     * @param Request $request
     * @param $eventKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventOpen(Request $request, $eventKey){
        try{
            $event = Event::whereKey($eventKey)->firstOrFail();
            if($this->checkOpenVoting($event)){
                return response()->json(['vote' => true], 200);
            }
            return response()->json(['vote' => false], 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to verify status event'], 500);
        }
    }

    /**
     * @param $event
     * @return bool
     */
    private function checkOpenVoting($event){
        $currentDate = Carbon::now();
        $currentDay = Carbon::today();

        $eventStartDate = new Carbon($event->start_date);
        $eventEndDate = new Carbon($event->end_date);

        $eventStartTime = new Carbon($event->start_time);
        $eventEndTime = new Carbon($event->end_time);

        if(($eventStartDate->gt($currentDate) && $eventStartTime->gt($currentDate)) || ($currentDay->gt($eventEndDate) ) || ( $currentDay->eq($eventEndDate) && $currentDate->gt($eventEndTime)) ){
            return false;
        }

        return true;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEventsNoTranslation(Request $request)
    {
        $events = [];
        try {
            if (!empty($request->json('events'))) {
                foreach ($request->json('events') as $event) {
                    $event = Event::whereKey($event)->first();

                    if (!empty($event)){
                        $method = $event->method()->first();
                        $configurations = $event->configurationEvents()->get();
                        foreach ($configurations as $configuration){
                            $code = $configuration->configuration()->first()->code;
                            $configuration['code'] = $code;
                        }

                        $event['method']            = $method;
                        $event['configurations']    = $configurations;

                        //  begin timezone conversion
                        $timezone = empty($request->header('timezone')) ? 'utc' : $request->header('timezone');
                        $event = ONE::timezoneConversion($event, 'UTC', $timezone);
                        //  end timezone conversion

                        $events[] = $event;
                    }
                }
            }
            return response()->json(['data' => $events], 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Event'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $eventKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitVotes(Request $request, $eventKey)
    {
        $userKey = ONE::verifyToken($request);

        try {
            $event = Event::whereKey($eventKey)->firstOrFail();
            if(ONE::verifyRole($userKey, $request) == 'manager' ||ONE::verifyRole($userKey, $request) == 'admin') {
                if (!empty($request->json('user_key')))
                    $votes = $event->votes()->whereUserKey($request->json('user_key'))->whereSubmitted(0)->get();
            } else
                $votes = $event->votes()->whereUserKey($userKey)->whereSubmitted(0)->get();

            if (isset($votes)) {
                foreach ($votes as $vote) {
                    $vote->submitted = 1;
                    $vote->save();
                }

                if ($request->json("returnVotes",false))
                    return response()->json(['msg' => 'Submit Register','votes'=>$votes], 200);
                else
                    return response()->json(['msg' => 'Submit Register'], 200);
            }
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to submit Votes'], 500);
        }
        return response()->json(['error' => 'Failed to submit Votes'], 500);
    }

    /**
     * Returns the absolute total number of votes for the provided array of vote events
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCbTotalVotes(Request $request)
    {
        try {
            $voteEvents = $request->json('vote_event_keys');

            if (!empty($voteEvents)){
                $totalVotes = 0;

                foreach ($voteEvents as $voteEvent){
                    $event = Event::whereKey($voteEvent)->first();

                    if (!is_null($event)){
                        $votes = count($event->votes()->get());
                        if (!empty($votes)){
                            $totalVotes += $votes;
                        }
                    }
                }
                return response()->json($totalVotes, 200);
            }

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve total CB votes'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function getUserVotesForEvent(Request $request) {
        try{
            $userKey = ONE::verifyToken($request);
            $eventKeys = $request->get("eventKeys");

            $userVotes = array();
            foreach ($eventKeys as $eventKey) {
                $userVotesList = Event::whereKey($eventKey)->firstOrfail()->votes()->whereUserKey($userKey)->get();
                foreach ($userVotesList as $userVote) {
                    $userVotes[] = $userVote;
                }
            }

            return response()->json($userVotes, 200);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to get event or user votes'], 500);
        }
    }


    /**
     * ATTACH A USER TO A VOTE EVENT WITH A SPECIFIC CODE
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachUserToVoteEventWithCode(Request $request)
    {

        try{
            $userKey = $request->get("userKey");
            $eventKey = $request->get("eventKey");
            $code = $request->get("code");

            if(UserEventCode::whereCode($code)->whereEventKey($eventKey)->exists()){
                //CODE ALREADY REGISTERED IN THIS VOTE EVENT
                return response()->json(['error' => 'The code is already registered for this vote event.'], 408);
            }

            UserEventCode::create([
                'user_key' => $userKey,
                'event_key' => $eventKey,
                'code' => $code
            ]);

            return response()->json('OK', 200);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to attach user to vote event with code'], 500);
        }
    }

    /**
     * @param Request $request
     * @param null $event
     * @param null $userEventCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeInPersonVoting(Request $request, $event = null, $userEventCode = null)
    {
        try{
            $createBy = ONE::verifyToken($request);
            $userVotes = $request->get("userVotes");
            $code = $request->get("code");

            //CHECK IF THIS CALL IS FROM THIS CONTROLLER OR FROM A REQUEST, IF SO WE NEED TO REBUILD THE OBJECTS
            if(is_null($event)){
                $eventKey = $request->get("eventKey");
                $event = Event::whereKey($eventKey)->first();
                if(!$event){
                    return response()->json(['error' => 'Failed to find the vote event'], 500);
                }
                $userEventCode = UserEventCode::whereCode($code)->whereEventKey($eventKey)->first();
            }

            $userKey = $userEventCode->user_key;

            foreach($userVotes as $vote) {
                $event->votes()->create(
                    [
                        'vote_key' => $vote, //IN THIS CASE IT'S THE TOPIC KEY
                        'value'    => 1,
                        'user_key' => $userKey,
                        'source'   => 'in_person'
                    ]
                );
                UserEventCodeVote::create([
                    'created_by'         => $createBy,
                    'user_event_code_id' => $userEventCode->id, //IN THIS CASE IT'S THE TOPIC KEY
                    'vote_key'           => $vote,
                ]);
            }

            return response()->json('OK', 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to store user in person votes'], 500);
        }
    }

    /**
     * STEPS :
     * - check if the code is registered in the vote event
     * - check if user has already voted
     * - if not store the votes
     * - else return message to confirm
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUserInPersonVoting(Request $request)
    {
        try{
            $eventKey = $request->get("eventKey");
            $code = $request->get("code");

            $event = Event::whereKey($eventKey)->first();
            if(!$event){
                return response()->json(['error' => 'Failed to find the vote event'], 500);
            }

            $userEventCode = UserEventCode::whereCode($code)->whereEventKey($eventKey)->first();
            if($userEventCode){
                $userKey = $userEventCode->user_key;

                $userVotes = $event->votes()->whereUserKey($userKey)->exists();

                if($userVotes){
                    return response()->json(['error' => 'The user has already voted'], 409);
                }else{
                    $this->storeInPersonVoting($request, $event, $userEventCode);
                }

            }else{
                return response()->json(['error' => 'The code is not registered for this vote event'], 408);
            }

            return response()->json('OK', 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to register user in person voting'], 500);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unSubmitUserVotesInEvent(Request $request)
    {
        try{
            $userKey = ONE::verifyToken($request);
            $eventKey = $request->get("eventKey");

            $event = Event::whereKey($eventKey)->first();
            if(!$event){
                return response()->json(['error' => 'Failed to find the vote event'], 500);
            }

            $userVotes = $event->votes()->whereUserKey($userKey)->get();
            if($userVotes){
                foreach ($userVotes as $userVote){
                    $userVote->submitted = false;
                    $userVote->save();
                }
            }

            return response()->json('OK', 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to delete user votes in vote event'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserVotesInVoteEvent(Request $request)
    {
        try{
            $updatedBy = ONE::verifyToken($request);
            $eventKey = $request->get("eventKey");
            $code = $request->get("code");

            $event = Event::whereKey($eventKey)->first();
            if(!$event){
                return response()->json(['error' => 'Failed to find the vote event'], 500);
            }

            $userEventCode = UserEventCode::whereCode($code)->whereEventKey($eventKey)->first();
            if($userEventCode){

                $userKey = $userEventCode->user_key;

                $userEventCodeVotes = UserEventCodeVote::whereUserEventCodeId($userEventCode->id)->get();
                foreach ($userEventCodeVotes as $userEventCodeVote){
                    $userEventCodeVote->updated_by = $updatedBy;
                    $userEventCodeVote->save();
                    $userEventCodeVote->delete();
                }

                $event->votes()->whereUserKey($userKey)->delete();

            }else{
                return response()->json(['error' => 'The code is not registered for this vote event'], 408);
            }

            return response()->json('OK', 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to delete user votes in vote event'], 500);
        }
    }

    /**
     * @param Request $request
     * @param $eventKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventAndVotes(Request $request, $eventKey)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            try {
                $event = Event::whereKey($eventKey)->firstOrFail();
                $votes = $event->votes()->get();

                return response()->json(["eventID"=> $event->id, "event"=>$event, "votes"=>$votes], 200);
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to get the Votes'], 500);
            }
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePublicUserVoting(Request $request)
    {
        try{
            $eventKey = $request->get("eventKey");
            $userKey = $request->get("userKey");
            $votes = $request->get("votes");
            $event = Event::whereKey($eventKey)->first();
            if(!$event){
                return response()->json(['error' => 'Failed to find the vote event'], 500);
            }

            if(!$this->checkOpenVoting($event)){
                return response()->json(['error' => 'Voting is closed!'], 500);
            }

            if($event->votes()->whereUserKey($userKey)->exists()) {
                $event->votes()->whereUserKey($userKey)->delete();
            }

            foreach ($votes as $vote){
                $event->votes()->create(
                    [
                        'submitted' => 1,
                        'vote_key' => $vote, //IN THIS CASE IT'S THE TOPIC KEY
                        'value'    => 1,
                        'user_key' => $userKey,
                        'source'   => 'in_person'
                    ]
                );
            }

            return response()->json('OK', 200);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to store the public user votes in vote event'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manualUpdateTopicVotesInfo(Request $request)
    {
        $userKey = ONE::verifyToken($request);

        try{
            $eventKeys = $request->json('event_keys');
            $events = Event::wherein('key', $eventKeys)->get();

            if ($events){
                foreach ($events as $event){
                    DB::statement('call count_votes('.$event->id.')');
                }
                $updatedEvents = Event::wherein('key', $eventKeys)->get();

                return response()->json($updatedEvents->pluck('_count_votes', 'key'), 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Event not Found'], 404);
        } catch(Exception $e){
            return response()->json(['error' => 'Failed to Update vote events count'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     *Script to recount all votes of all the Events
     */
    public function manualUpdateVotesCount(){
        try{
            $events = Event::all();

            foreach ($events as $event){
                DB::statement('call count_votes('.$event->id.')');
            }
        } catch (Exception $e){
            dd($e->getMessage());
        }
    }
}
