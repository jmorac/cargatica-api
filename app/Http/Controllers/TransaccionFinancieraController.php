<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\TransaccionFinanciera;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class TransaccionFinancieraController extends Controller
{


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function insertarTransaccion(Request $request)
    {

        $validator = $this->validateRequest($request, [
            'tipo' => 'required',
            'parent_id' => 'required',
            'child_id' => 'required',
            'cliente_id' => 'required',
            'oficina_id' => 'required',
            'monto' => 'required',
            'referencia' => 'required'
            ]

        );

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $transaccion = $request->only('tipo', 'parent_id', 'child_id', 'user_id', 'cliente_id', 'oficina_id', 'fecha', 'monto', 'referencia');
        

        $usuario_id = Auth::user()->usuario_id;

        $transaccion['usuario_id']=$usuario_id;

        Factura::findOrFail($transaccion["parent_id"]);


        $location = TransaccionFinanciera::create($transaccion);

        return $this->createOkResponse($location);
    }





}

