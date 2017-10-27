<?php

namespace App\Http\Controllers;

use App\Configuration;
use App\ConfigurationTranslation;
use App\One\One;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class ConfigurationsController
 * @package App\Http\Controllers
 */

class ConfigurationsController extends Controller
{
    protected $keysRequired = [
        'method_id',
        'translations'
    ];

    /**
     * @SWG\Tag(
     *   name="Configuration",
     *   description="Everything about Configurations",
     * )
     *
     * * @SWG\Definition(
     *   definition="translationsConfigs",
     *   type="object",
     *   allOf={
     *     @SWG\Schema(
     *           @SWG\Property(property="language_code", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="description", format="string", type="string")
     *      )
     *   }
     * )
     *
     * @SWG\Definition(
     *   definition="configurationReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *
     *           @SWG\Property(property="id", format="integer", type="integer"),
     *           @SWG\Property(property="method_id", format="integer", type="integer"),
     *           @SWG\Property(property="code", format="string", type="string"),
     *           @SWG\Property(property="parameter_type", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="description", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string")
     *       )
     *   }
     * )
     *
     * @SWG\Definition(
     *   definition="translationsConfiguration",
     *   type="object",
     *   allOf={
     *     @SWG\Schema(
     *           @SWG\Property(property="language_code", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="description", format="string", type="string")
     *      )
     *   }
     * )
     *
     * * @SWG\Definition(
     *   definition="configurationCreate",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           required={"method_id","translations"},
     *           @SWG\Property(property="method_id", format="integer", type="integer"),
     *           @SWG\Property(property="code", format="string", type="string"),
     *           @SWG\Property(property="parameter_type", format="string", type="string"),
     *           @SWG\Property(
     *              property="translations",
     *              type="array",
     *              @SWG\Items(ref="#/definitions/translationsConfiguration")
     *           )
     *
     *       )
     *   }
     *  )
     *
     *  @SWG\Definition(
     *     definition="configurationDeleteReply",
     *     @SWG\Property(property="string", type="string", format="string")
     * )
     *
     * /



    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $configurations = Configuration::all();

            foreach ($configurations as $configuration){
                if(!($configuration->translation($request->header('LANG-CODE')))){
                    if (!$configuration->translation($request->header('LANG-CODE-DEFAULT')))
                        return response()->json(['error' => 'No translation found'], 404);
                }
            }

            return response()->json(['data' => $configurations], 200);
        }catch(Exception $e){
            if($e->getCode()==404) {
                return response()->json(['error' => 'No Translation Found'], 404);
            }
            return response()->json(['error' => 'Failed to retrieve the Configurations list'], 500);
        }
    }


    /**
     * @SWG\Get(
     *  path="/configuration/{configuration_id}",
     *  summary="Show a Configuration detail",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Configuration"},
     *
     *  @SWG\Parameter(
     *      name="configuration_id",
     *      in="path",
     *      description="Configuration id",
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
     *      description="Show the Configuration data",
     *      @SWG\Schema(ref="#/definitions/configurationReply")
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
     *      description="Configuration not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Configuration",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Request of one Configuration
     * Returns the attributes of the Configuration
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show(Request $request, $id)
    {
        try{
            $configuration = Configuration::findOrFail($id);

            if(!($configuration->translation($request->header('LANG-CODE')))){
                if (!$configuration->translation($request->header('LANG-CODE-DEFAULT')))
                    return response()->json(['error' => 'No translation found'], 404);
            }
            return response()->json($configuration, 200);

        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Configuration not Found'], 404);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Configuration'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        try{
            $configuration = Configuration::findOrFail($id);

            if(!($configuration->translations($request->header('LANG-CODE')))){
                $configuration->translations($request->header('LANG-CODE-DEFAULT'));
            }
            return response()->json($configuration, 200);

        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Configuration not Found'], 404);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Configuration'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Post(
     *  path="/configuration",
     *  summary="Create a Configuration",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Configuration"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Configuration Created Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/configurationCreate")
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
     *  @SWG\Response(
     *      response=200,
     *      description="Created Configuration",
     *      @SWG\Schema(ref="#/definitions/configurationReply")
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
     * Store a new Configuration in the database
     * Return the Attributes of the Configuration created
     * @param Request $request
     *
     * @return static
     */
    public function store(Request $request)
    {
        $userKey = ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);

        //VERIFY PERMISSIONS

        try{
            $configuration = Configuration::create(
                [
                    'code'              => $request->json('code'),
                    'method_id'         => $request->json('method_id'),
                    'parameter_type'    => $request->json('parameter_type')
                ]
            );
            foreach ($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name']) && isset($translation['description'])){
                    $configurationTranslation = $configuration->configurationTranslations()->create(
                        [
                            'language_code' => $translation['language_code'],
                            'name'          => $translation['name'],
                            'description'   => $translation['description']
                        ]
                    );
                }
            }
            return response()->json($configuration, 201);

        } catch(Exception $e){
            return response()->json(['error' => 'Failed to store new Configuration'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Put(
     *  path="/configuration/{configuration_id}",
     *  summary="Update a Configuration",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Configuration"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Configuration Update Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/configurationCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="configuration_id",
     *      in="path",
     *      description="Configuration id",
     *      required=true,
     *      type="integer"
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
     *  @SWG\Response(
     *      response=200,
     *      description="The updated Configuration",
     *      @SWG\Schema(ref="#/definitions/configurationReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Configuration not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to create Configuration",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Update a existing Configuration
     * Return the Attributes of the Configuration Updated
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);

        //VERIFY PERMISSIONS

        try{

            $translationsOld = [];
            $translationsNew = [];

            $configuration = Configuration::findOrFail($id);

            $translationsId = $configuration->configurationTranslations()->get();
            foreach ($translationsId as $translationId){
                $translationsOld[] = $translationId->id;
            }

            $configuration->code            = $request->json('code');
            $configuration->method_id       = $request->json('method_id');
            $configuration->parameter_type  = $request->json('parameter_type');
            $configuration->save();

            foreach($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name']) && isset($translation['description'])) {
                    $configurationTranslation = $configuration->configurationTranslations()->whereLanguageCode($translation['language_code'])->first();
                    if (empty($configurationTranslation)) {
                        $configurationTranslation = $configuration->configurationTranslations()->create(
                            [
                                'language_code'     => $translation['language_code'],
                                'name'              => $translation['name'],
                                'description'       => $translation['description']
                            ]
                        );
                    }
                    else {
                        $configurationTranslation->name           = $translation['name'];
                        $configurationTranslation->description    = $translation['description'];
                        $configurationTranslation->save();
                    }
                }
                $translationsNew[] = $configurationTranslation->id;
            }

            $deleteTranslations = array_diff($translationsOld, $translationsNew);
            foreach ($deleteTranslations as $deleteTranslation) {
                $deleteId = $configuration->configurationTranslations()->whereId($deleteTranslation)->first();
                $deleteId->delete();
            }

            return response()->json($configuration, 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Configuration not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to update Configuration'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/configuration/{configuration_id}",
     *  summary="Delete a Configuration",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Configuration"},
     *
     * @SWG\Parameter(
     *      name="configuration_id",
     *      in="path",
     *      description="Configuration Id",
     *      required=true,
     *      type="integer"
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
     *      @SWG\Schema(ref="#/definitions/configurationDeleteReply")
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
     *      description="Configuration not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to delete Configuration",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Delete existing Configuration
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        ONE::verifyToken($request);

        try{
            Configuration::destroy($id);

            return response()->json('Ok', 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Configuration not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete Configuration'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

}
