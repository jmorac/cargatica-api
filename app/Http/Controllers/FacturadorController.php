<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Facturador;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class FacturadorController extends Controller {

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getFacturadores( Request $request)
	{
		$r=Facturador::all();

		return $this->createOkResponse( $r);
	}


}



