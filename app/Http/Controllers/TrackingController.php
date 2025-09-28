<?php

namespace App\Http\Controllers;

use App\Models\ReciboBodega;
use App\Models\Tracking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use App\Util\Push;
use App\Util\ImageHandler;
use App\Models\Imagenes;
use Log;
use App\Util\Captcha;

class TrackingController extends Controller
{


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function insertarTracking(Request $request)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($request, [
            'tracking' => 'required',
        ]);

        $tracking = $request->get('tracking');
        $descripcion = $request->get('descripcion');
        $comentario = $request->get('comentario');

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $trackingExists = Tracking::query()->where('tracking', $tracking)->first();

        if ($trackingExists && $trackingExists->idcliente) {
            return $this->createOkResponse(null, 'Error', 'Tracking ya existe en el sistema');
        }
        if ($trackingExists && $trackingExists->idcliente == 0) {
            $trackingExists->update(['idcliente' => $cliente_id]);
        } else {

            $trackingInfo = $request->only('tracking', 'descripcion', 'comentario');
            $trackingInfo['idcliente'] = $cliente_id;
            $trackingInfo['fecha'] = time();
            $trackingObj = Tracking::create($trackingInfo);

            if ($request->has('autorizar')) {
                $this->autorizar($request, $trackingObj->id);
            }
            if ($request->file('adjunto')) {
                $this->salvarAdjunto($request, 0, $trackingObj->id);
            }


        }

        return $this->createOkResponse($trackingObj);

    }


    private function searchAux($cliente_id, $tracking_sch)
    {
        if (strlen($tracking_sch) < 4) {
            return $this->createOkResponse('[]');
        }

        $tracking = Tracking::query()->where('idcliente', $cliente_id)->where('tracking', $tracking_sch)->first();
        if ($tracking) {
            return $this->createOkResponse($tracking);
        }

        if (strlen($tracking_sch) >= 12) {
           // $tracking = Tracking::query()->where('idcliente', $cliente_id)->where('tracking', 'like', '%' . $tracking_sch . '%')->first();
            $tracking = Tracking::query()
                ->where('idcliente', 0)
                ->whereRaw('LOWER(tracking) LIKE ?', ['%' . strtolower($tracking_sch) . '%'])
                ->first();

            if ($tracking) {
                return $this->createOkResponse($tracking);
            }
        }

        $tracking = Tracking::query()->where('idcliente', 0)->where('tracking', $tracking_sch)->first();
        if ($tracking) {
            return $this->createOkResponse($tracking);
        }

        if (strlen($tracking_sch) >= 10) {


            $tracking = Tracking::query()
                ->where('idcliente', 0)
                ->whereRaw('LOWER(tracking) LIKE ?', ['%' . strtolower($tracking_sch) . '%'])
                ->first();
            if ($tracking) {
                return $this->createOkResponse($tracking);
            }

        }


        return $this->createOkResponse('[]');
    }


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function searchTracking(Request $request)
    {
        $cliente_id = Auth::user()->cliente_id;
        $tracking = $request->get('tracking');
        return $this->searchAux($cliente_id, $tracking);
    }

    public function searchPublicTracking(Request $request)
    {
        $validator = $this->validateRequest($request, [
            'tracking' => 'required|min:12',
            'ip' => ['required', 'ip'],
            'g-recaptcha-response' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $resp = Captcha::recaptcha_check(env('GOOGLE_CAPTCHA_SECRET_KEY'),
            $request->get('ip'),
            $request->get("g-recaptcha-response"));
        if (!$resp->is_valid) {
            return $this->createErrorResponse('Invalid Captcha:' ,$resp->error);
        }
        $trackingReq = $request->get('tracking');
        $recibos = ReciboBodega::query()
            ->with('guia')
            ->whereRaw('LOWER(tracking) LIKE ?', ['%' . strtolower($trackingReq) . '%'])
            ->first();

        $trackingResult = new \stdClass();
        $trackingResult->prealerts = null;
        $trackingResult->recibos = $recibos;

        if ($recibos   ) {
            return $this->createOkResponse($trackingResult);
        }

     //   $tracking = Tracking::query()->where('tracking', 'like', '%' . $trackingReq . '%')->first();
/*
        $tracking = Tracking::query()
            ->whereRaw('LOWER(tracking) LIKE ?', ['%' . strtolower($trackingReq) . '%'])
            ->first();
*/
        $tracking = Tracking::query()
            ->where('userid', '>', 0) // Filter where userid is greater than 0
            ->whereRaw('LOWER(tracking) LIKE ?', ['%' . strtolower($trackingReq) . '%'])
            ->first();

        if ($recibos || $tracking ) {
            $trackingResult->prealerts = $tracking;
            return $this->createOkResponse($trackingResult);
        }

        return $this->createOkResponse('[]');

    }


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function searchTrackingUrl(Request $request, $tracking_sch)
    {
        $cliente_id = Auth::user()->cliente_id;
        return $this->searchAux($cliente_id, $tracking_sch);
    }


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getTracking(Request $request, $trackingId)
    {
        $cliente_id = Auth::user()->cliente_id;
        $tracking = Tracking::query()->where('idcliente', $cliente_id)->where('id', $trackingId)->get();
        if ($tracking) {
            return $this->createOkResponse($tracking);
        }
        /*
        $tracking = Tracking::query()->where('idcliente',0)->where('id',$trackingId)->get();
        if($tracking){
            return $this->createOkResponse($tracking);
        }
        */

        return $this->createOkResponse($tracking);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTrackings(Request $request)
    {

        $cliente_id = Auth::user()->cliente_id;
        $tracking = $request->get('tracking');
        $result = Tracking::query()->where('idcliente', $cliente_id);

        if ($tracking) {
            $result = $result->where('tracking', $tracking);
        }
        $result = $result->paginate($request->get('limit', 1000));
        return $this->createOkResponse($result);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteTrackings(Request $request, $trackingId)
    {
        $cliente_id = Auth::user()->cliente_id;
        Tracking::query()->where('idcliente', $cliente_id)->where('id', $trackingId)->delete();
        return $this->createOkResponse('OK');
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autorizar(Request $request, $trackingId)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($request, [
            'autorizar' => 'required',
        ]);
        $autorizar = $request->get('autorizar');
        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }
        Tracking::query()->where('idcliente', $cliente_id)->where('id', $trackingId)->update(['autorizar' => $autorizar]);
        return $this->createOkResponse('OK');
    }

    private function salvarAdjunto(Request $r, $reciboId, $trackingId)
    {
        $imageHandle = app(ImageHandler::class);
        return $imageHandle->saveImage($r, $reciboId, $trackingId);
    }


    public function adjuntosDisponibles(Request $r, $trackingId)
    {

        $cliente_id = Auth::user()->cliente_id;
        $month = 60 * 60 * 24 * 30;

        $trackings = Tracking::query()->where('idcliente', $cliente_id)->where('id', '!=', $trackingId)->where('fecha', '>', time() - ($month * 6));

        $trackingsId = [];
        foreach ($trackings->get() as $tracking) {
            $trackingsId[] = $tracking->id;
        }

        $imagenes = Imagenes::query()->whereIn('idtracking', $trackingsId)->where('deleteddate', 0)->where('padre', null)->get();


        return $this->createOkResponse($imagenes);
    }


    public function adjuntarRecibo(Request $r, $tracking_id)
    {
        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($r, [
            'descripcion' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $tracking = Tracking::query()->where('idcliente', $cliente_id)->where('id', $tracking_id)->firstOrFail();
        $salvarData = $this->salvarAdjunto($r, 0, $tracking->id);

        if (!$salvarData) {
            return $this->createOkResponse('ERROR');
        }
        return $this->createOkResponse($salvarData);
    }

    public function borrarRecibo(Request $r, $tracking_id, $adjunto_id)
    {
        $cliente_id = Auth::user()->cliente_id;
        $tracking = Tracking::query()->where('idcliente', $cliente_id)->where('id', $tracking_id)->firstOrFail();
        $images = Imagenes::query()->where('idtracking', $tracking->id)->where('id', $adjunto_id)->firstOrFail();
        $images->update(['deleteddate' => time()]);
        $imagesAvailable = Imagenes::query()->where('idWH', $tracking->id)->where('deleteddate', 0)->get();
        return $this->createOkResponse('OK');
    }

    public function verAdjuntos(Request $r, $id)
    {

        $cliente_id = Auth::user()->cliente_id;
        $tracking = Tracking::query()->where('idcliente', $cliente_id)->where('id', $id)->firstOrFail();
        $images = Imagenes::query()->where('idtracking', $tracking->id)->where('deleteddate', 0)->get();
        return $this->createOkResponse($images);
    }


}

