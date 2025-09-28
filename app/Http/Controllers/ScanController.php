<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller {



	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function scan( Request $r )
	{

        $warehouseComplete = $r->get( 'whid' );


        $cantidad = $r->get( 'cantidad',1 );
        $containerId = $r->get( 'containerid' );
        $iglooName = $r->get( 'iglooname' );
        $forceInsert = $r->get( 'forceinsert',0 );
        $isManual = $r->get( 'ismanual', 0 );
        $oficina = $r->get( 'oficina' );

        $whidcomplete = str_pad($warehouseComplete, 12, "0", STR_PAD_LEFT);
        $item = substr($whidcomplete, 0, 4);
        $whid = intval(substr($whidcomplete, 4) * 1);


        if($forceInsert){

        }


    }








}



