<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller {



	/**
	 * @param Request $r
	 *
	 * @return JsonResponse
	 */
	public function getFacturas( Request $r )
	{

        $twelveMonths = 60 *60 *24*30*12;
        $cliente_id = Auth::user()->cliente_id;
        if(!Cliente::query()->where('id','=',$cliente_id)->first()->cedula){
            return $this->createOkResponse(null,'Error','Campo requerido[cedula]');
        }

        $pagado              = $r->get( 'pagado' );
        $deseaPagarEfectivo  = $r->get( 'desea_pagar_efectivo' );

        $query=Factura::query()->orderBy('id','desc');
        $query = $pagado !==null ? $pagado ?$query->where( 'pagado', '>', 0 ):$query->where( 'pagado', '=', 0 ) : $query;
        $query = $deseaPagarEfectivo !==null ? $deseaPagarEfectivo ?$query->where( 'desea_pagar_efectivo', '>', 0 ):$query->where( 'desea_pagar_efectivo', '=', 0 ) : $query;

		$query=$query->where('clientid',$cliente_id)->where('fechaunix','>',time()-$twelveMonths);

        $query=$query->where('anulado','=',0);


        $facturas = $query->paginate( $r->get( 'limit', 25 ) );

		return $this->createOkResponse( $facturas );
	}


	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getFactura( Request $request, $identifier)
	{
        $cliente_id = Auth::user()->cliente_id;


        if(strlen($identifier)>40) {
            $factura = Factura::query()->where('clientid', $cliente_id)->where('clavehacienda', $identifier)->first();
        }else{
            $factura = Factura::query()->where('clientid', $cliente_id)->where('id', $identifier)->first();
        }


		return $this->createOkResponse( $factura);
	}


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function pagarFactura( Request $r, $clave_hacienda)
    {
        $cliente_id = Auth::user()->cliente_id;
        $factura=Factura::query()->where('clientid',$cliente_id)->where('clavehacienda',$clave_hacienda)->first();

         // $monto_pagado
         //$formaquepago
        //$pagoesdolares


         $data= ['pagado'=>time(),'montopagado'=>$r->get];


         //$guia
        $factura->update();

        return $this->createOkResponse( $factura);
    }


    public function testfact( Request $r)
    {

        $facturaId=141242;

        $clave_hacienda=50629102100310146971600100001010000002341147622451;

        $factura=Factura::query()->where('clavehacienda',$clave_hacienda)->firstOrFail();
        $deseaPagarEfectivo              = $r->get( 'desea_pagar_efectivo' );
        $factura->update(['desea_pagar_efectivo'=>$deseaPagarEfectivo]);
        $results = DB::select('select S.BLNo from CT_SHIP S inner join CT_FACTURA_GUIAS FG on S.BLNo=FG.BLNo where FG.factura_id = ?', [$factura->id]);

        foreach ($results as $r){
            $g= Guia::query()->where('BLNo',$r->BLNo)->update(['desea_pagar_efectivo'=>time()]);
        }
        return $this->createOkResponse( $results);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deseaPagarEfectivo( Request $r, $identifier)
    {

        $validator = $this->validateRequest($r, [
            'desea_pagar_efectivo' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $cliente_id = Auth::user()->cliente_id;

        if(strlen($identifier)>40){
            $factura=Factura::query()->where('clientid',$cliente_id)->where('clavehacienda',$identifier)->firstOrFail();
        }else{
            $factura=Factura::query()->where('clientid',$cliente_id)->where('id',$identifier)->firstOrFail();
        }

        $deseaPagarEfectivo              = $r->get( 'desea_pagar_efectivo' );
        $factura->update(['desea_pagar_efectivo'=>$deseaPagarEfectivo]);
        $results = DB::select('select S.BLNo from CT_SHIP S inner join CT_FACTURA_GUIAS FG on S.BLNo=FG.BLNo where FG.factura_id = ?', [$factura->id]);

        foreach ($results as $r){
            $g= Guia::query()->where('BLNo',$r->BLNo)->update(['desea_pagar_efectivo'=>time()]);
        }


        //Guia::query()->where('clienteid',$cliente_id)->where('id',$ext_id)->with('facturas')->first();


        return $this->createOkResponse( $factura);
    }

}



