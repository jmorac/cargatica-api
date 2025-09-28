<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class GuiaController extends Controller
{

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getGuiasAgrupadas(Request $r)
    {
        $cliente_id = Auth::user()->cliente_id;
        $sixMonths = 60 * 60 * 24 * 30 * 6;

        $ret = [];
//fecha_factura', 'fecha_pagado','envio_cliente_id','entregado_id
        //     -Sin facturas    -No pagadas    -No enviadas 
        $ret[] = ["APROBADAS" => Guia::query()->with('facturas')->where('fecha', '>', time() - $sixMonths)->where('clienteid', $cliente_id)
            ->where('fecha_pagado', 0)
            ->where('fecha_factura', 0)
            ->where('envio_cliente_id', 0)
            ->where('entregado_id', 0)
            ->orderBy('BLNo', 'DESC')->limit(10)->get()->toArray()];

        //  -Con facturas     -No pagada     -No enviada  
        $ret[] = ["PENDIENTE_PAGAR" => Guia::query()->with('facturas')->where('fecha', '>', time() - $sixMonths)->where('clienteid', $cliente_id)
            ->where('fecha_pagado', 0)
            ->where('fecha_factura', '>', 1)
            /*
            ->where('envio_cliente_id',0)
            ->where('entregado_id',0)
            */
            ->orderBy('BLNo', 'DESC')->limit(10)->get()->toArray()];

        //     -Pagada    -No enviada  
        $ret[] = ["PAGADO" => Guia::query()->with('facturas')->where('fecha', '>', time() - $sixMonths)->where('clienteid', $cliente_id)
            ->where('fecha_pagado', '>', 1)
            ->where('fecha_factura', '>', 1)
            ->where('envio_cliente_id', 0)
            ->where('entregado_id', 0)
            ->orderBy('BLNo', 'DESC')->limit(10)->get()->toArray()];

        //       -Enviada
        $ret[] = ["TRANSITO" => Guia::query()->with('facturas')->where('fecha', '>', time() - $sixMonths)->where('clienteid', $cliente_id)
            ->where('envio_cliente_id', '>', 0)
            ->where('entregado_id', 0)
            ->orderBy('BLNo', 'DESC')->limit(10)->get()->toArray()];

        //       -Enviada
        $ret[] = ["ENTREGADO" => Guia::query()->with('facturas')->where('fecha', '>', time() - $sixMonths)->where('clienteid', $cliente_id)
            ->where('entregado_id', '>', 0)
            ->orderBy('BLNo', 'DESC')->limit(10)->get()->toArray()];


        return $this->createOkResponse($ret);

    }

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getGuias(Request $r)
    {

        $cliente_id = Auth::user()->cliente_id;

        if (!Cliente::query()->where('id', '=', $cliente_id)->first()->cedula) {
            return $this->createOkResponse(null, 'Error', 'Campo requerido[cedula]');
        }

        $sixMonths = 60 * 60 * 24 * 30 * 6;

        $statusApp = $r->get('status_app');
        $statusAppAvailable = ["APROBADAS", "PENDIENTE_PAGAR", "PAGADO", "TRANSITO", "ENTREGADO", "LISTO_ENTREGAR", "PAGADOOCREDITO", "ALL"];
        if (strlen($statusApp) && !in_array($statusApp, $statusAppAvailable)) {
            return $this->createErrorResponse("Invalid status_app, should be:" . implode(',', $statusAppAvailable));
        }

        $query = Guia::query()->where('clienteid', $cliente_id);
        if ($statusApp == 'APROBADAS') {
            $query = $query->where('envio_cliente_id', '=', 0)->where('fecha_factura', '=', 0)->where('fecha_pagado', '=', 0)->where('entregado_id', '=', 0)->where('entregado_id', '=', 0);;
        }
        if ($statusApp == 'PENDIENTE_PAGAR') {
            $query = $query->where('fecha_factura', '>', 0)->where('fecha_pagado', '=', 0)->where('desea_pagar_efectivo', '=', 0);
        }
        if ($statusApp == 'PAGADOOCREDITO') {

            $query = $query->where(function ($q) {
                $q->where('fecha_pagado', '>', 0)
                    ->orWhere('desea_pagar_efectivo', '>', 0);
            })->where(function ($q) {
                $q->where('entregado_id', '=', 0)->where('envio_cliente_id', '=', 0);
            });

            //   TODO: cuando se paga no lo esta marcando como pagada.
            //    $query =  $query->where('fecha_factura', '>', 0)->where('fecha_pagado', '>', 0)->orWhere('desea_pagar_efectivo','>',0);
        }
        if ($statusApp == 'PAGADO') {
            $query = $query->where('fecha_pagado', '>', 0)->where('entregado_id', '=', 0);
        }
        if ($statusApp == 'LISTO_ENTREGAR') {
            $query = $query->where('envio_cliente_id', '>', 0)->where('entregado_id', '=', 0);
        }
        if ($statusApp == 'TRANSITO') {
            $query = $query->where('envio_cliente_id', '>', 0)->where('entregado_id', '=', 0);
        }
        if ($statusApp == 'ENTREGADO') {
            $query = $query->where('entregado_id', '>', 0);
        }

        if ($statusApp != 'ALL') {
            $query = $query->orderBy('fecha_factura', 'DESC')
                ->orderBy('desea_pagar_efectivo', 'DESC')
                ->orderBy('fecha_pagado', 'DESC')
                ->orderBy('envio_cliente_id', 'DESC')
                ->orderBy('entregado_id', 'DESC')
                ->orderBy('BLNo', 'DESC');
        } else {
            $query = $query->orderBy('BLNo', 'DESC');
        }

        $query = $sixMonths ? $query->where('fecha', '>', time() - $sixMonths) : $query;

        if ($statusApp == 'APROBADAS') {
            $query = $query->with('facturas');
        }else{
            $query = $query->with('facturas')->whereHas('facturas', function ($query) {
                $query->where('anulado', '=', 0);
            });
        }



        $guias = $query->paginate($r->get('limit', 25));

        return $this->createOkResponse($guias);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getGuia(Request $request, $ext_id)
    {

        $cliente_id = Auth::user()->cliente_id;
        $r = Guia::query()->where('clienteid', $cliente_id)->where('idextguia', $ext_id)->with('facturas')->with('historial')->with('warehouseReceipts')->first();

        return $this->createOkResponse($r);
    }

}



