<?php

namespace App\Http\Controllers;

use App\Method;
use App\One\One;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class MethodsController
 * @package App\Http\Controllers
 */

class MethodsController extends Controller
{
    protected $keysRequired = [
        'method_group_id',
        'translations'
    ];

    /**
     * @SWG\Tag(
     *   name="Method",
     *   description="Everything about Methods",
     * )
     *
     *
     * @SWG\Definition(
     *   definition="translationsMethod",
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
     *   definition="methodCreate",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           required={"group_method_id","translations"},
     *           @SWG\Property(property="group_method_id", format="integer", type="integer"),
     *           @SWG\Property(
     *              property="translations",
     *              type="array",
     *              @SWG\Items(ref="#/definitions/translationsMethod")
     *           )
     *
     *       )
     *   }
     *  )
     *
     * @SWG\Definition(
     *   definition="methodReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *
     *           @SWG\Property(property="id", format="integer", type="integer"),
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
     *     definition="methodDeleteReply",
     *     @SWG\Property(property="string", type="string", format="string")
     * )
     *
     */




    /**
     * Request list of all Methods
     * Returns the list of all Methods
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $methods = Method::all();

            foreach ($methods as $method){
                if(!($method->translation($request->header('LANG-CODE')))){
                    if (!$method->translation($request->header('LANG-CODE-DEFAULT'))){
                        if (!$method->translation('en'))
                            return response()->json(['error' => 'No translation found'], 404);
                    }
                }
            }
            return response()->json(['data' => $methods], 200);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Methods list'], 500);
        }
    }

    /**
     * @SWG\Get(
     *  path="/method/{method_id}",
     *  summary="Show a Method detail",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method"},
     *
     *  @SWG\Parameter(
     *      name="method_id",
     *      in="path",
     *      description="Method id",
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
     *      description="Show the Method data",
     *      @SWG\Schema(ref="#/definitions/methodGroupReply")
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
     *      description="Method not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Method",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $method = Method::findOrFail($id);
            if(!($method->translation($request->header('LANG-CODE')))){
                if (!$method->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!$method->translation('en'))
                        return response()->json(['error' => 'No translation found'], 404);
                }
            }

            $methodGroup = $method->methodGroup()->first();
            if(!($methodGroup->translation($request->header('LANG-CODE')))){
                if (!$methodGroup->translation($request->header('LANG-CODE-DEFAULT'))){
                    if (!$method->translation('en'))
                        return response()->json(['error' => 'No translation found'], 404);
                }
            }

            $configurations = $method->configurations()->get();
            foreach ($configurations as $configuration){
                if(!($configuration->translation($request->header('LANG-CODE')))){
                    if (!$configuration->translation($request->header('LANG-CODE-DEFAULT'))){
                        if (!$method->translation('en'))
                            return response()->json(['error' => 'No translation found'], 404);
                    }
                }
            }

            $method['method_group'] = $methodGroup;
            $method['configurations'] = $configurations;

            return response()->json($method, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Method'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Post(
     *  path="/method",
     *  summary="Create a Method",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Method Created Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/methodCreate")
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
     *      description="Created Method",
     *      @SWG\Schema(ref="#/definitions/methodReply")
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
     * Store a new Method in the database
     * Return the Attributes of the Method created
     * @param Request $request
     * @return static
     */
    public function store(Request $request)
    {
        $userKey = ONE::verifyToken($request);
        ONE::verifyKeysRequest($this->keysRequired, $request);

        //VERIFY PERMISSIONS

        try{
            $method = Method::create(
                [
                    'code'            => $request->json('code'),
                    'method_group_id' => $request->json('method_group_id')
                ]
            );

            foreach ($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name']) && isset($translation['description'])){
                    $methodTranslation = $method->methodTranslations()->create(
                        [
                            'language_code' => $translation['language_code'],
                            'name'          => $translation['name'],
                            'description'   => $translation['description']
                        ]
                    );
                }
            }
            return response()->json($method, 201);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to store the Method'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * @SWG\Put(
     *  path="/method/{method_id}",
     *  summary="Update a Method",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Method Update Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/methodCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="method_id",
     *      in="path",
     *      description="Method Group id",
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
     *      description="The updated Method",
     *      @SWG\Schema(ref="#/definitions/methodGroupReply")
     *  ),
     *
     *  @SWG\Response(
     *      response="401",
     *      description="Unauthorized",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *   ),
     *     @SWG\Response(
     *      response="404",
     *      description="Method Group not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to create Method",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */

    /**
     * Update a existing Method
     * Return the Attributes of the Method Updated
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

            $method = Method::findOrFail($id);

            $translationsId = $method->methodTranslations()->get();
            foreach ($translationsId as $translationId){
                $translationsOld[] = $translationId->id;
            }
            $method->code = $request->json('code');
            $method->method_group_id = $request->json('method_group_id');
            $method->save();

            foreach($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name']) && isset($translation['description'])) {
                    $methodTranslation = $method->methodTranslations()->whereLanguageCode($translation['language_code'])->first();
                    if (empty($methodTranslation)) {
                        $methodTranslation = $method->methodTranslations()->create(
                            [
                                'language_code' => $translation['language_code'],
                                'name'          => $translation['name'],
                                'description'   => $translation['description']
                            ]
                        );
                    }
                    else {
                        $methodTranslation->name           = $translation['name'];
                        $methodTranslation->description    = $translation['description'];
                        $methodTranslation->save();
                    }
                }
                $translationsNew[] = $methodTranslation->id;
            }

            $deleteTranslations = array_diff($translationsOld, $translationsNew);
            foreach ($deleteTranslations as $deleteTranslation) {
                $deleteId = $method->methodTranslations()->whereId($deleteTranslation)->first();
                $deleteId->delete();
            }

            return response()->json($method, 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to update Method'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/method/{method_id}",
     *  summary="Delete a Method",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method"},
     *
     * @SWG\Parameter(
     *      name="method_id",
     *      in="path",
     *      description="Method Id",
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
     *      @SWG\Schema(ref="#/definitions/methodDeleteReply")
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
     *      description="Method Group not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to delete Method Group",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */



    /**
     * Delete existing Method
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $userKey = ONE::verifyToken($request);

        try{
            $method = Method::findOrFail($id);
            $method->delete();

            return response()->json('Ok', 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete Method'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }





    /**
     * Request of Configurations Associated with Method
     * Returns the Configurations of the Method
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function edit(Request $request, $id)
    {
        try {
            $method = Method::findOrFail($id);
            if(!($method->translations($request->header('LANG-CODE')))){
                $method->translations($request->header('LANG-CODE-DEFAULT'));
            }

            $methodGroup = $method->methodGroup()->first();
            if(!($methodGroup->translation($request->header('LANG-CODE')))){
                $methodGroup->translation($request->header('LANG-CODE-DEFAULT'));
            }

            $configurations = $method->configurations()->get();
            foreach ($configurations as $configuration){
                if(!($configuration->translation($request->header('LANG-CODE')))){
                    $configuration->translation($request->header('LANG-CODE-DEFAULT'));
                }
            }

            $method['method_group'] = $methodGroup;
            $method['configurations'] = $configurations;

            return response()->json($method, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the Method'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
