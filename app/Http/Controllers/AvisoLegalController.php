<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\AvisoLegalesFirmadosClientes;

use App\Models\AvisoLegales;
use App\Models\User;
use App\Models\EmailAyuda;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use App\Util\Push;
use App\Util\Ticas;
use Log;

class AvisoLegalController extends Controller
{
    public function avisoLegalPendiente(Request $request)
    {

        $firmados_id=[];
        $cliente_id = Auth::user()->cliente_id;
        $firmados = AvisoLegalesFirmadosClientes::query()->where('cliente_id', $cliente_id)->get();
        foreach ($firmados as $firmado) {
            $firmados_id[] = $firmado->legal_id;
        }

        $legales = AvisoLegales::query()->where('active', 1)->get();
        $ret=[];
        foreach($legales as $legal){
            if(!in_array($legal->id, $firmados_id)){
                $ret[]=$legal;
            }
        }
        return $this->createOkResponse($ret);
    }


    public function firmarAvisoLegal(Request $request)
    {
        $cliente_id = Auth::user()->cliente_id;

        $validator = $this->validateRequest($request, [
            'ip' => 'required',
            'legal_id' => 'required',
            'header' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $documentoFirmado = $request->only('ip','legal_id','header');
        $documentoFirmado['cliente_id'] =$cliente_id;

        $trackingObj = AvisoLegalesFirmadosClientes::create($documentoFirmado);

        if (!$trackingObj) {
            return $this->createOkResponse(null, 'Error');
        } else {
            return $this->createOkResponse($trackingObj);
        }
    }

}