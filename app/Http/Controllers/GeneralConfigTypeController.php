<?php

namespace App\Http\Controllers;

use App\GeneralConfigType;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class GeneralConfigTypeController
 * @package App\Http\Controllers
 */
class GeneralConfigTypeController extends Controller
{


    /**
     * @SWG\Tag(
     *   name="General Configuration Types",
     *   description="Everything about General Configuration Types",
     * )
     *
     * @SWG\Definition(
     *   definition="generalConfigTypeReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *           @SWG\Property(property="id", format="integer", type="integer"),
     *           @SWG\Property(property="general_config_type_key", format="string", type="string"),
     *           @SWG\Property(property="code", format="string", type="string"),
     *           @SWG\Property(property="name", format="string", type="string"),
     *           @SWG\Property(property="created_at", format="date", type="string"),
     *           @SWG\Property(property="updated_at", format="date", type="string")
     *       )
     *   }
     * )
     *
     * @SWG\Definition(
     *   definition="generalConfigTypeListReply",
     *   type="object",
     *   allOf={
     *      @SWG\Schema(
     *          @SWG\Property(
     *              property="data",
     *              type="array",
     *              @SWG\Items(ref="#/definitions/generalConfigTypeReply")
     *          )
     *      )
     *   }
     *  )
     *
     *
     */




    /**
     * @SWG\Get(
     *  path="/generalConfigType/list",
     *  summary="Show a General Configuration detail",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  tags={"General Configuration Types"},
     *
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
     *      @SWG\Schema(ref="#/definitions/generalConfigTypeListReply")
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $generalConfigTypes = GeneralConfigType::all();

            foreach ($generalConfigTypes as $generalConfigType){
                if(!($generalConfigType->translation($request->header('LANG-CODE')))){
                    if (!$generalConfigType->translation($request->header('LANG-CODE-DEFAULT')))
                        return response()->json(['error' => 'No translation found'], 404);
                }
            }

            return response()->json(['data' => $generalConfigTypes], 200);
        }catch(Exception $e){
            if($e->getCode()==404) {
                return response()->json(['error' => 'No Translation Found'], 404);
            }
            return response()->json(['error' => 'Failed to retrieve the General Configuration Types list'], 500);
        }
    }

}
