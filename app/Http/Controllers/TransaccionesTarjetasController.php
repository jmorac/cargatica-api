<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Facturador;
use App\Models\Guia;
use App\Models\TipoCambio;
use App\Models\TransaccionTarjeta;
use App\Models\SolicitudProcesamientoTarjeta;
use App\Util\CCProccesors\Bac;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TransaccionesTarjetasController extends Controller
{


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function pagarFacturas3D(Request $r)
    {

        $ret = [];


        $clienteId = Auth::user()->cliente_id;

        $validator = $this->validateRequest($r, [
            'numero_tarjeta' => 'required',
            'mes_exp' => 'required',
            'ano_exp' => 'required',
            'cvs' => 'required',
            'factura_ids' => 'required'
        ]);


        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }
        $idRandom = Str::random(10);
        $transaccionData = ['cliente_id' => $clienteId, 'mensaje' => 'Procesando', 'id_ext' => $idRandom];

        $transaccion = TransaccionTarjeta::create($transaccionData);


        $url = '/forms/bac/3d/';
        $transaccionData = ['solicitud' => json_encode($r->except('api_token')),
                            'cliente_id' => $clienteId,
                            'fecha' => time(),
                            'id_ext' => $idRandom,
                            'id_credit_card_transaction'=>$transaccion->id];

        $solicitudId = SolicitudProcesamientoTarjeta::insertGetId($transaccionData);
        $ret = ['id' => $solicitudId, 'id_ext' => $idRandom,'id_credit_card_transaction'=>$transaccion->id, 'url' => $url, 'complete_url' => env('WS_URL') . $url . '?id=' . $solicitudId . '&id_ext=' . $idRandom];

        return $this->createOkResponse($ret);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTransaction(Request $r, $transaction_id)
    {

        $cliente_id = Auth::user()->cliente_id;

        $transaccion = TransaccionTarjeta::query()->where('cliente_id', $cliente_id)->where('id', $transaction_id)->firstOrFail();

        return $this->createOkResponse($transaccion);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTransactions(Request $r)
    {


        $cliente_id = Auth::user()->cliente_id;

        $transaccion = TransaccionTarjeta::query()->where('cliente_id', $cliente_id)->get();

        return $this->createOkResponse($transaccion);

    }


}



