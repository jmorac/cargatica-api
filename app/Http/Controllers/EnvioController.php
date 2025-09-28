<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EnvioCliente;
use App\Models\Guia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EnvioController extends Controller {



	/**
	 * @param Request $request
	 * @param         $cliente_id
	 *
	 * @return JsonResponse
	 */
	public function getEnvios( Request $r )
	{
        $cliente_id = Auth::user()->cliente_id;

        $query=EnvioCliente::query()->where('cliente_id',$cliente_id);
		$envios = $query->paginate( $r->get( 'limit', 25 ) );

		return $this->createOkResponse( $envios );
	}


	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getEnvio( Request $request, $envioId)
	{
        $cliente_id = Auth::user()->cliente_id;
		$r=EnvioCliente::query()->where('cliente_id',$cliente_id)->where('id',$envioId)->first();

		return $this->createOkResponse( $r);
	}

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function crearEnvio( Request $request)
    {
        $cliente_id = Auth::user()->cliente_id;

        $validator = $this->validateRequest($request, [
            'recibe' => 'required',
            'nota_envio' => 'required',
            'lista_guia_id' =>'required'


        ]);

        if ($validator->fails()) {
            return $this->createOkResponse(null,'Error',$validator->messages()->first());
        }


        $guias =explode(',',$request->post('lista_guia_id'));
        foreach($guias as $guiaId){

            $guia=Guia::query()->where('clienteid',$cliente_id)->findOrFail($guiaId);

            if(!$guia->pagado && !$guia->solicito_efectivo  ){
         //       return $this->createOkResponse(null,'Error','Guia no pagada');
            }
            if($guia->envio_cliente_id!=0 ){
                return $this->createOkResponse(null,'Error','Ya esta asignado a un envio');

            }
        }

        $envioData = $userInfo = $request->only('direccion_id', 'recibe','recibe_telefono', 'nota_envio');
        $envioData = array_merge( $envioData , ['cliente_id'=>$cliente_id]);
        $location = EnvioCliente::create($envioData);

        foreach($guias as $guiaId){
            $guia=Guia::query()->where('clienteid',$cliente_id)->findOrFail($guiaId);
            $guia->update(['envio_cliente_id'=>$location->id]);

        }

        return $this->createOkResponse( $location);
    }



}



