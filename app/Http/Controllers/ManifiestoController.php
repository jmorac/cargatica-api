<?php

namespace App\Http\Controllers;

use App\Models\Imagenes;
use App\Models\Manifiesto;
use App\Models\ReciboBodega;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManifiestoController extends Controller
{


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getManifiestos(Request $r)
    {

        $usuario_id = Auth::user()->usuario_id;
        $query = Manifiesto::query()->where('usuario_repartidor',$usuario_id)->latest();
        $recibos = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($recibos);
    }


    public function getManifiesto(Request $r, $manifiesto_id)
    {

        $usuario_id = Auth::user()->usuario_id;
        $query = Manifiesto::query();

        $recibos = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($recibos);
    }

public function getScans(Request $r){

    $manifestoid=0;
   $SQL=" select scan.id as scan_id,
                            scan.productos_id,
                            scan.item,
                            sc.recibido_fecha,
                            User.nombre as usuario_llego,
                            User2.nombre as usuario_envio ,
                     
			       sc.enviado_fecha,
                   scan.id,sc.igloo,wh.creadoenCR,wh.nombrecliente,CL.nombre as nombre_cliente,s.BLNoVisual,
				   sc.enviado_ismanual,
				   sc.recibido_ismanual
					from  CT_SHIP  s   inner join 
					CT_Productos wh on wh.bl_id=s.BLNo inner join  
					CT_items_scan scan on scan.productos_id =wh.id inner join 
					CT_Manifiesto_items_scan sc on sc.item_scan_id=scan.id and sc.manifiesto_id=".$manifestoid."
					 left outer join CT_Usuarios User on User.id=sc.recibido_usuario
					 left outer join CT_Usuarios User2 on User2.id=sc.enviado_usuario
					 left outer join CT_Clientes CL on CL.id=wh.clientes_id
					inner join CT_Manifiesto_SHIP Maniship on Maniship.BLNo_id=s.BLNo 
                    
				 WHERE Maniship.manifiesto_id=".$manifestoid." 
					order by wh.creadoenCR,s.BLNoVisual,scan.productos_id,scan.item";


    return $this->createOkResponse(  Model::select(DB::raw($SQL))->get());


    }





}

