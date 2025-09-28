<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class UsuarioController extends Controller
{

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function loginUsuario(Request $request)
    {

        $validator = $this->validateRequest($request, [
            'usuario' => 'required',
            'contrasena' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $usuario = DB::table('CT_Usuarios')->where('usuario', $request->get("usuario"))->first();

        if (!$usuario) {
            return $this->createOkResponse(null,'Error','Usuario no existe');
        }

        $contrasena = $request->get('contrasena');

        if ($usuario->contrasena !=  md5($contrasena.$usuario->salt) ) {
            return $this->createOkResponse(null,'Error','Error en Credenciales');
        }


        $user = User::query()->where("usuario_id", $usuario->id);

        if ($user->get()->count()) {
            $user->delete();
        }

        $api_token = Str::random(60);

        $data = ['usuario_id' => $usuario->id, 'name' => $usuario->nombre, 'api_token' => $api_token];
        User::create($data);

        $usuario = Usuario::query()->findOrFail($usuario->id)->toArray();
        $usuario["api_token"] = $api_token;

        return $this->createOkResponse($usuario);

    }


    public
    function getUsuario(Request $request)
    {
        $usuario_id = Auth::user()->usuario_id;
        $usuario = Usuario::query()->where('id',$usuario_id)->get();

        return $this->createOkResponse($usuario);
    }



}

