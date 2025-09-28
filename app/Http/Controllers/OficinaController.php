<?php

namespace App\Http\Controllers;


use App\Models\Oficina;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class OficinaController extends Controller {

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getOficinas( Request $r)
	{
		$r=Oficina::where('status',1)->get();

		return $this->createOkResponse( $r);
	}


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getOficina( Request $request, $oficinaid)
    {
        $r=Oficina::where('id',$oficinaid)->firstOrFail();

        return $this->createOkResponse( $r);
    }


}



