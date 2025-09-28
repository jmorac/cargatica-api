<?php

namespace App\Http\Controllers;


use App\Models\Guia;
use App\Models\Factura;
use App\Util\Enums\Moneda;
use App\Models\Facturador;
use App\Models\TipoCambio;
use App\Util\Push;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\TransaccionTarjeta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SolicitudProcesamientoTarjeta;

class BacController extends Controller
{

    private function isValidHash(Request $r, $facturadorId)
    {

        $bacHash = $r->get('hash');
        $time = $r->get('time');
        $amount = $r->get('amount');
        $orderId = $r->get('orderid');
        $response = $r->get('response');
        $transactionid = $r->get('transactionid');
        $avsresponse = $r->get('avsresponse');
        $cvvresponse = $r->get('cvvresponse');

        $facturador = Facturador::query()->where('id', $facturadorId)->firstOrFail();
        $key = $facturador->bac_key;
        $hash = md5($orderId . "|" . $amount . "|" . $response . "|" . $transactionid . "|" . $avsresponse . "|" . $cvvresponse . "|" . $time . "|" . $key);
        return ($bacHash == $hash);

    }

    public function update3dTransaction(Request $r)
    {

        $response = $r->get('response');
        $orderId = $r->get('orderid');
        $authCode = $r->get('authcode');
        $responseText = $r->get('responsetext');

        $transaccion = TransaccionTarjeta::query()->where('id', $orderId)->firstOrFail();
        $transaccion->update(['received' => json_encode($r->all()), 'mensaje' => $responseText]);
        if(!$this->isValidHash($r, $transaccion->facturador_id)){
            return  "Error autenticando transaccion";
        }

        if ($this->isValidHash($r, $transaccion->facturador_id) && $response == 1 && $orderId>0) {
            $transaccion->update(['numero_autorizacion' => $authCode, 'estado' => 1]);
            foreach (json_decode($transaccion->facturas_afectadas) as $facturaId) {
                $factura = Factura::query()->where('id', $facturaId);
                //referencia, modopago,pagado,formaquepago,pagoesdolares,montopagado
                $facturaData = ['referencia' => $authCode, 'pagado' => 1,'fecha_pagado'=>time(),'canal'=>1 ];
                $factura->update($facturaData);
                $results = DB::select('select S.BLNo,F.pagado from CT_SHIP S 
                                              inner join CT_FACTURA_GUIAS FG on S.BLNo=FG.BLNo 
                                              inner join CT_facturas F on  FG.factura_id=F.id
where FG.factura_id = ?', [$facturaId]);
                $pagados=0;
                $cuenta=0;
                foreach ($results as $r){
                    $cuenta=+1;
                    if($r->pagado==1){
                        $pagados=+1;
                    }
                }
                if($pagados== $cuenta && $cuenta>0){
                    $g= Guia::query()->where('BLNo',$r->BLNo);
                    $g->update(['fecha_pagado'=>time()]);

                    $push= new Push(env('PUSHURL'),env('PUSHKEY'),0);
                    $message = "La factura ".$facturaId." Ha sido pagada satisfactoriamente";
                    $title = "Factura Pagada";

                   // return $this->createOkResponse( $push->send($message, $title, $g->clienteid, 'c_') );

                }
            }

            return view('processors.bac.aprobado', compact('authCode' ));

        }

        $transaccion->update(['estado' => 2]);

        return view('processors.bac.denegado');

    }


    public function create3DForm(Request $r)
    {

        $validator = $this->validateRequest($r, [
            'id' => 'required',
            'id_ext' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $id = $r->get('id');
        $idExt = $r->get('id_ext');

        $solicitudProcesada = SolicitudProcesamientoTarjeta::query()->where('id', $id)->where('id_ext', $idExt)->firstOrFail();
        $info = json_decode($solicitudProcesada->solicitud);

        $time = time();
        $cardNumber = $info->numero_tarjeta;
        $cardExpiration = $info->mes_exp . $info->ano_exp;
        $cardCVC = $info->cvs;

      //  $moneda = $info->moneda;
        $monto = '0';
        $uniqueId = $solicitudProcesada->id;

        $facturaIdsParaProcesar = $info->factura_ids;
        $clienteId = $solicitudProcesada->cliente_id;
        $idCreditCardTransaction = $solicitudProcesada->id_credit_card_transaction;

        $transaccion = TransaccionTarjeta::query()->where('id', $idCreditCardTransaction)->firstOrFail();
        $solicitudProcesada->delete();

        $facturas = [];
        $totalMontoDolares = 0;
        $totalMontoColones = 0;

        $facturaIds = [];
        $facturaIdsOmitida = [];

        $tipoCambio = TipoCambio::query()->firstOrFail();
        $tipoCambioUSD = $tipoCambio->compra_USD;
        $tipoCambioColones = $tipoCambio->compra_colones;

        $facturadorId = 0;
        foreach (explode(',', $facturaIdsParaProcesar) as $facturaId) {
            $fact = Factura::query()->where('clientid', $clienteId)->where('id', $facturaId)->where('clientid', $clienteId)->firstOrFail();

            if (!$fact->pagado) {
                if ($facturadorId == 0) {
                    $facturadorId = $fact->facturador_id;
                } else {
                    if ($facturadorId != $fact->facturador_id) {
                        //TODO: throw exception different facturador
                        exit;
                    }
                }

                $facturas[] = $fact;
                $facturaIds[] = $fact->id;

                $totalMonto=$fact->monto_total;
                
                if ($fact->esdolares) {
                    $totalMontoDolares = $totalMontoDolares + $totalMonto;
                    $totalMontoColones = $totalMontoColones + ($totalMonto * $tipoCambioColones);

                } else {
                    $totalMontoColones = $totalMontoColones + $totalMonto;
                    $totalMontoDolares = round(($totalMontoDolares + ($totalMonto / $tipoCambioUSD)) * 100) / 100;
                }
                $fact->update(['credit_card_transaction_id' => $transaccion->id]);

            } else {
                $dataTransaction = ['mensaje'=>'Transaccion Abortada'];
                $transaccion->update($dataTransaction);

                return "Factura ya esta paga";
                $facturaIdsOmitida[] = $fact->id;
            }
        }

        $facturador = Facturador::query()->where('id', $facturadorId)->firstOrFail();
        $codigoMoneda = Moneda::Colones;
        $monto = $totalMontoColones;


        if($facturador->bac_moneda ==Moneda::Dolares ){
            $codigoMoneda = Moneda::Dolares;
            $monto = $totalMontoDolares;
        }

        $sentData = '';
        $dataTransaction = ['facturador_id' => $facturadorId, 'tipo_transaccion' => 1, 'sent' => $sentData, 'monto' => $monto, 'moneda_id' => $codigoMoneda, 'facturas_afectadas' => json_encode($facturaIds)];
        $transaccion->update($dataTransaction);
        $uniqueId = $transaccion->id;
        $key = $facturador->bac_key;
        $keyId = $facturador->bac_key_id;
        $processorId = $facturador->bac_procesor_id;
        if(strlen($key)==0){
            $dataTransaction = ['mensaje'=>'Transaccion Abortada falta configuracion'];
            $transaccion->update($dataTransaction);
            return "Falta configurar credenciales";
        }

        $hash = md5('' . $uniqueId . '|' . $monto . '|' . $time . '|' . $key);

    //    return view('processors.bac.3DformNoRedirect', compact('time', 'hash', 'cardNumber', 'cardExpiration', 'monto', 'uniqueId', 'keyId', 'processorId', 'cardCVC'));

        return view('processors.bac.3Dform', compact('time', 'hash', 'cardNumber', 'cardExpiration', 'monto', 'uniqueId', 'keyId', 'processorId', 'cardCVC'));

    }


}



