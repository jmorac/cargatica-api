<?php

namespace App\Http\Controllers;

use App\Models\Guia;
use App\Models\Imagenes;
use App\Models\ReciboBodega;
use App\Models\SolicitudConsolidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SolicitudConsolidacionController extends Controller
{

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getSolicitudes(Request $r)
    {
        $cliente_id = Auth::user()->cliente_id;
        //$query = SolicitudConsolidacion::query()->where('cliente_id',$cliente_id);
        //$solicitudes = $query->paginate($r->get('limit', 25));


        $solicitudes = SolicitudConsolidacion::query()
            ->where('cliente_id', $cliente_id)
            ->whereHas('recibos', function ($query) {
                $query->where('bl_id', 0); // Only get records where CT_Productos.bl_id = 0
            })
            ->paginate($r->get('limit', 25));

        return $this->createOkResponse($solicitudes);
    }

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getSolicitud(Request $r, $id)
    {
        $cliente_id = Auth::user()->cliente_id;
        $query = SolicitudConsolidacion::query()->where('cliente_id',$cliente_id)->where('id',$id);
        $solicitudes = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($solicitudes);
    }

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function borrar(Request $r,$id)
    {
        $cliente_id = Auth::user()->cliente_id;
        $ret= $this->functionDesautorizarRecibo($cliente_id,$id);
        return $this->createOkResponse($ret);

    }


    private function functionDesautorizarRecibo($clienteId,$solicitudId){

        $solicitud = SolicitudConsolidacion::query()->where('cliente_id',$clienteId)->where('id',$solicitudId)->delete();
        return ReciboBodega::where('solicitud_id', $solicitudId)->where('clientes_id',$clienteId)->update(['notacliente' => '', 'autorizadoxcliente' => 0, 'solicitud_id' => null , 'fechaclienteaut' =>0]);

    }


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function quitarRecibo(Request $r,$id,$idExtRecibo)
    {
        $cliente_id = Auth::user()->cliente_id;

        $recibos = ReciboBodega::query()->where('clientes_id', $cliente_id)->where('solicitud_id', $id)->get();
        $RecibosActuales = count($recibos);

        $recibo = ReciboBodega::where('solicitud_id', $id)->where('clientes_id',$cliente_id)->where('idext',$idExtRecibo)->update(['notacliente' => '', 'autorizadoxcliente' => 0, 'solicitud_id' => null , 'fechaclienteaut' =>0]);

         //   dd('select * from CT_Productos where solicitud_id = '.$id.' and clientes_id = '.$cliente_id.' and idext ='.$idExtRecibo);
        if($RecibosActuales===1 ){
                $this->functionDesautorizarRecibo($cliente_id,$id);
          }


        $reciboAutorizar = [];
        $pcs     = 0;
        $weight  = 0;
        $cuft    = 0;
        $volumen = 0;
        foreach ($recibos as $recibo) {
            $weight=+$recibo->total_peso;
            $volumen =+$recibo->total_volumen;
            $cuft   =+$recibo->total_cu;
            $pcs =+$recibo->total_items;
            $reciboAutorizar[] = $recibo->id;
        }

        SolicitudConsolidacion::query()->where('cliente_id',$cliente_id)->where('id',$id)->update( [ 'pcs'=>$pcs, 'weight'=>$weight, 'cuft'=>$cuft, 'volumen'=>$volumen] );
        return $this->createOkResponse('OK');
    }


}

