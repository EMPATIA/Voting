<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    /**
     * @SWG\Swagger(
     *     schemes={"http"},
     *     basePath="/",
     *   @SWG\Info(
     *     title="Vote Module",
     *     version="1.0.0",
     *      @SWG\Contact(
     *             email="pvalente@onesource.pt"
     *         ),
     *   )
     * )
     *
     *  @SWG\Definition(
     *      definition="errorDefault",
     *      @SWG\Property(property="error", type="string", format="string")
     *  )
     */

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
