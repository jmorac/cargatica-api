<?php

namespace App\Http\Controllers;

use App\Models\DireccionCliente;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DireccionClienteController extends Controller {



	/**
	 * @param Request $r
	 *
	 * @return JsonResponse
	 */
	public function getDirecciones( Request $r )
	{

        $clienteId = Auth::user()->cliente_id;
        $query=DireccionCliente::query();
        $query=$query->where('cliente_id',$clienteId);
		$direcciones = $query->paginate( $r->get( 'limit', 1000 ) );

		return $this->createOkResponse( $direcciones );
	}


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function agregarDireccion( Request $request)
    {
        $cliente_id = Auth::user()->cliente_id;

        $geoLat          = $request->get('geolat');
        $geoLong         = $request->get('geolong');

        if ($geoLat==0 && $geoLong==0) {
            return $this->createOkResponse(null,'Error','Ubicacion Incorrecta');
        }

        $nuevaDireccion = $request->only( 'nombre','direccion','geolat', 'geolong', 'notas','telefono');
        $nuevaDireccion = array_merge($nuevaDireccion,['fecha'=>time(),'cliente_id'=>$cliente_id]);
        $direccion=DireccionCliente::create($nuevaDireccion);

        return $this->createOkResponse( $direccion);
    }

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getDireccion( Request $request, $direccionId)
	{
        $cliente_id = Auth::user()->cliente_id;
        $direccion=DireccionCliente::query()->where('cliente_id',$cliente_id)->where('id',$direccionId)->firstOrFail();

		return $this->createOkResponse( $direccion);
	}


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function  editarDireccion( Request $r, $direccionId)
    {

        $cliente_id = Auth::user()->cliente_id;
        $factura=DireccionCliente::query()->where('cliente_id',$cliente_id)->where('id',$direccionId)->firstOrFail();
        $factura->update($r->only('nombre','direccion','geolat','geolong','notas','telefono'));

         return $this->createOkResponse( $factura);
    }


    /**
     * @param Request $r
     * @param $direccion_id
     *
     * @return JsonResponse
     */
    public function  borrarDireccion( Request $r, $direccionId)
    {
        $cliente_id = Auth::user()->cliente_id;
        $factura=DireccionCliente::query()->where('cliente_id',$cliente_id)->where('id',$direccionId)->firstOrFail();
        $factura->delete();

        return $this->createOkResponse( 'OK');
    }




}



