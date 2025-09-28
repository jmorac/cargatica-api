<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Compania;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CompaniaController extends Controller {

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getCompanias( Request $request)
	{
		$r=Compania::all();

		return $this->createOkResponse( $r);
	}


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCompania( Request $request,$id)
    {
        $r=Compania::query()->where('id',$id)->firstOrFail();

        return $this->createOkResponse( $r);
    }



}



