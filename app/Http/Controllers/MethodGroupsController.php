<?php

namespace App\Http\Controllers;

use App\MethodGroup;
use App\MethodGroupTranslation;
use App\One\One;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;


/**
 * Class MethodGroupsController
 * @package App\Http\Controllers
 */
class MethodGroupsController extends Controller
{

    protected $keysRequired = [
        'translations'
    ];

    /**
     * @SWG\Tag(
     *   name="Method Groups",
     *   description="Everything about Method Groups",
     * )
     *
     *
     *
     * @SWG\Definition(
     *   definition="translationsMethodGroup",
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
     *   definition="methodGroupCreate",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           required={"translations"},
     *           @SWG\Property(
     *              property="translations",
     *              type="array",
     *              @SWG\Items(ref="#/definitions/translationsMethodGroup")
     *           )
     *
     *       )
     *   }
     *  )
     *
     *  @SWG\Definition(
     *   definition="methodGroupReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *
     *           @SWG\Property(property="id", format="integer", type="integer"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="description", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string")
     *       )
     *   }
     * )
     *

     *
     *  @SWG\Definition(
     *   definition="methodMethodGroupListReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(
     *              property="data",
     *              type="array",
     *              @SWG\Items(ref="#/definitions/methodReply")
     *           )
     *       )
     *   }
     * )
     *
     *
     *  @SWG\Definition(
     *     definition="methodGroupDeleteReply",
     *     @SWG\Property(property="string", type="string", format="string")
     * )
     */




    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $methodGroups = MethodGroup::all();

            foreach ($methodGroups as $methodGroup){
                $methodGroup->newTranslation($request->header('LANG-CODE'), $request->header('LANG-CODE-DEFAULT'));
            }

            return response()->json(['data' => $methodGroups], 200);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Method Groups list'], 500);
        }
    }

    /**
     * @SWG\Get(
     *  path="/methodGroup/{method_group_id}",
     *  summary="Show a Method Group detail",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method Groups"},
     *
     *  @SWG\Parameter(
     *      name="method_group_id",
     *      in="path",
     *      description="Method Group id",
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
     *      description="Show the Method Group data",
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
     *      description="Method Group not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Method Group",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Request of one Method Group
     * Returns the attributes of the Method Group
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show(Request $request, $id)
    {
        try{
            $methodGroup = MethodGroup::findOrFail($id);

            if(!($methodGroup->translation($request->header('LANG-CODE')))){
                if (!$methodGroup->translation($request->header('LANG-CODE-DEFAULT')))
                    if (!$methodGroup->translation('en'))
                        return response()->json(['error' => 'No translation found'], 404);
            }
            return response()->json($methodGroup, 200);

        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method Group not Found'], 404);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Method Groups'], 500);
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
            $methodGroup = MethodGroup::findOrFail($id);

            if(!($methodGroup->translation($request->header('LANG-CODE')))){
                if (!$methodGroup->translation($request->header('LANG-CODE-DEFAULT')))
                    if (!$methodGroup->translation('en'))
                        return response()->json(['error' => 'No translation found'], 404);
            }
            return response()->json($methodGroup, 200);

        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method Group not Found'], 404);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Method Groups'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * @SWG\Post(
     *  path="/methodGroup",
     *  summary="Create a Method Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method Groups"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Method Group Created Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/methodGroupCreate")
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
     *      description="Created Method Group",
     *      @SWG\Schema(ref="#/definitions/methodGroupReply")
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
     *      description="Failed to update Method Group",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */



    /**
     * Store a new Method Group in the database
     * Return the Attributes of the Method Group created
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
            $methodGroup = MethodGroup::create(
                [
                ]
            );
            foreach ($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name']) && isset($translation['description'])){
                    $methodGroupTranslation = $methodGroup->methodGroupTranslations()->create(
                        [

                            'language_code'     => $translation['language_code'],
                            'name'              => $translation['name'],
                            'description'       => $translation['description']
                        ]
                    );
                }
            }
            return response()->json($methodGroup, 201);

        }catch(Exception $e){
            return response()->json(['error' => 'Failed to store the Method Group'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Put(
     *  path="/methodGroup/{method_group_id}",
     *  summary="Update a Method Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method Groups"},
     *
     *  @SWG\Parameter(
     *      name="Body",
     *      in="body",
     *      description="Method Group Update Data",
     *      required=true,
     *      @SWG\Schema(ref="#/definitions/methodGroupCreate")
     *  ),
     *
     * @SWG\Parameter(
     *      name="method_group_id",
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
     *      description="The updated Method Group",
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
     *      description="Failed to create Method Group",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * Update a existing Method Group
     * Return the Attributes of the Method Group Updated
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

            $methodGroup = MethodGroup::findOrFail($id);

            $translationsId = $methodGroup->methodGroupTranslations()->get();
            foreach ($translationsId as $translationId){
                $translationsOld[] = $translationId->id;
            }

            foreach($request->json('translations') as $translation){
                if (isset($translation['language_code']) && isset($translation['name']) && isset($translation['description'])) {
                    $methodGroupTranslation = $methodGroup->methodGroupTranslations()->whereLanguageCode($translation['language_code'])->first();
                    if (empty($methodGroupTranslation)) {
                        $methodGroupTranslation = $methodGroup->methodGroupTranslations()->create(
                            [
                                'language_code'       => $translation['language_code'],
                                'name'              => $translation['name'],
                                'description'       => $translation['description']
                            ]
                        );
                    }
                    else {
                        $methodGroupTranslation->name           = $translation['name'];
                        $methodGroupTranslation->description    = $translation['description'];
                        $methodGroupTranslation->save();
                    }
                }
                $translationsNew[] = $methodGroupTranslation->id;
            }

            $deleteTranslations = array_diff($translationsOld, $translationsNew);
            foreach ($deleteTranslations as $deleteTranslation) {
                $deleteId = $methodGroup->methodGroupTranslations()->whereId($deleteTranslation)->first();
                $deleteId->delete();
            }

            return response()->json($methodGroup, 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method Group not Found'], 404);
        }catch(Exception $e){
            return response()->json(['error' => $e], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @SWG\Delete(
     *  path="/methodGroup/{method_group_id}",
     *  summary="Delete a Method Group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method Groups"},
     *
     * @SWG\Parameter(
     *      name="method_group_id",
     *      in="path",
     *      description="Method Group Id",
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
     *      @SWG\Schema(ref="#/definitions/methodGroupDeleteReply")
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
        ONE::verifyToken($request);

        try{
            $methodGroup = MethodGroup::destroy($id);

            return response()->json('Ok', 200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Method Group not Found'], 404);
        }catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete Method Group'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }



    /**
     * @SWG\Get(
     *  path="/methodGroup/{method_group_id}/listMethods",
     *  summary="List of methods of Method group",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"Method Groups"},
     *
     *  @SWG\Parameter(
     *      name="method_group_id",
     *      in="path",
     *      description="Method Group id",
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
     *      description="Show the Method Group data",
     *      @SWG\Schema(ref="#/definitions/methodMethodGroupListReply")
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
     *      description="Method Group not Found",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  ),
     *
     *  @SWG\Response(
     *      response="500",
     *      description="Failed to retrieve Method Group",
     *      @SWG\Schema(ref="#/definitions/errorDefault")
     *  )
     * )
     */


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function listMethods(Request $request, $id)
    {
        try{
            $methodGroup = MethodGroup::findOrFail($id);
            $listMethods = $methodGroup->methods()->get();

            foreach ($listMethods as $method){
                if(!($method->translation($request->header('LANG-CODE')))){
                    if (!$method->translation($request->header('LANG-CODE-DEFAULT'))){
                        if (!$method->translation('en'))
                            return response()->json(['error' => 'No translation found'], 404);
                    }

                }
            }
            return response()->json(['data' => $listMethods], 200);
        }catch(Exception $e){
            return response()->json(['error' => 'Failed to retrieve the Method Groups list'], 500);
        }
    }

}
