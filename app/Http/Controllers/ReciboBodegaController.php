<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Guia;

use App\Models\Usuario;
use App\Util\ImageHandler;
use App\Models\Imagenes;
use App\Models\ReciboBodega;
use App\Models\ReciboBodegaHistorial;
use App\Models\ReciboBodegaImagenes;
use App\Models\SolicitudConsolidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class ReciboBodegaController extends Controller
{


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function getRecibos(Request $r)
    {
        $cliente_id = Auth::user()->cliente_id;
        if (!Cliente::query()->where('id', '=', $cliente_id)->first()->cedula) {
            return $this->createOkResponse(null, 'Error', 'Campo requerido[cedula]');
        }

        $entregado = $r->get('entregado');
        $autorizado = $r->get('autorizado');
        $autorizadoCliente = $r->get('autorizado_cliente');
        $guiaNumero = $r->get('guia_numero');
        $guiaId = $r->get('guia_id');
        $query = ReciboBodega::query()->where('cancelado', '=', 0)->orderBy('id', 'DESC');
        $query = $autorizado !== null ? $autorizado ? $query->where('autorizado_cliente', '>', 0) : $query->where('bl_id', '=', 0) : $query;
      //  $query = $autorizadoCliente !== null ? $autorizadoCliente ? $query->where('fecha_autorizado_cliente', '>', 0) : $query->where('fecha_autorizado_cliente', '=', 0) : $query;
        $query = $autorizadoCliente !== null ? $autorizadoCliente ? $query->where('fecha_autorizado_cliente', '>', 0) : $query->where('fecha_autorizado_cliente', '=', 0) : $query;


        $sixMonths = 60 * 60 * 24 * 30 * 6;
        $nineMonths = 60 * 60 * 24 * 30 * 9;

        if ($guiaNumero) {
            $guiaInfo = Guia::query()->where('BLNoVisual', '=', $guiaNumero)->firstOrFail();
            $guiaId = $guiaInfo->id;
            $sixMonths = 0;
            $nineMonths = 0;
        }

        if ($guiaId) {
            $query = $query->where('bl_id', '=', $guiaId);
            $sixMonths = 0;
            $nineMonths = 0;
        }

        $query = $guiaId ? $query->where('bl_id', '=', $guiaId) : $query;
        $query = $entregado !== null ? $entregado ? $query->where('entregado', '>', 0) : $query->where('entregado', '=', 0) : $query;

        $query = $nineMonths ? $query->where('fechaWH', '>', time() - $nineMonths) : $query;
        // $query =   $query->where('cancelado', '<>', 1) ;
        $query = $query->where('clientes_id', $cliente_id)->with('guia:id,numero,ext_id,exporter,consignatario,numero_vuelo,factura_id,fecha');

        $recibos = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($recibos);
    }



    public function getRecibosByChangeDate(Request $r)
    {
        $format ='Y-m-d H:i:s';
        $cliente_id = Auth::user()->cliente_id;

        $startDate = $r->input('start_date');

        $endDate = $r->input('end_date', date($format));
        if (!Carbon::hasFormat($startDate, $format)) {
            return $this->createErrorResponse('Invalid Start Date should be:'.$format,[]);
        }

        if (!Carbon::hasFormat($endDate, $format)) {
            return $this->createErrorResponse('Invalid End Date should be:'.$format,[]);
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);


        $query = ReciboBodega::whereBetween('updated_at', [$startDate, $endDate]);
        $query = $query->where('clientes_id', $cliente_id);//->with('guia:id,numero,ext_id,exporter,consignatario,numero_vuelo,factura_id,fecha');

        $recibos = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($recibos);

    }

    public function getRecibosByChangeDateAffiliado(Request $r){
        $format ='Y-m-d H:i:s';
        $user_id = Auth::user()->user_id;

        $usuario_id = Auth::user()->usuario_id;
        $usuario = Usuario::query()->where('id',$usuario_id)->first();
        if($usuario->compania_id==0){
            return $this->createErrorResponse('El usuario no es un afiliado', null);
        }

        $startDate = $r->input('start_date');

        $endDate = $r->input('end_date', date($format));
        if (!Carbon::hasFormat($startDate, $format)) {
            return $this->createErrorResponse('Invalid Start Date should be:'.$format,[]);
        }

        if (!Carbon::hasFormat($endDate, $format)) {
            return $this->createErrorResponse('Invalid End Date should be:'.$format,[]);
        }

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);


        $query = ReciboBodega::whereBetween('updated_at', [$startDate, $endDate]);
        if($usuario->compania_id>0){
            $query = $query->where('compania_id', $usuario->compania_id);//->with('guia:id,numero,ext_id,exporter,consignatario,numero_vuelo,factura_id,fecha');
        }

        $recibos = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($recibos);
    }


    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public function buscarRecibos(Request $r)
    {

        $hileraBuscar = $r->get('buscar');

        if (strlen($hileraBuscar) < 4) {
            return $this->createOkResponse(null, 'Error', 'Debe enviar mas informacion para hacer la busqueda');
        }

        $sixMonths = 60 * 60 * 24 * 30 * 6;
        $cliente_id = Auth::user()->cliente_id;

        $query = ReciboBodega::query()->with('guia');

        $query = $query->where('clientes_id', $cliente_id)->where('cancelado', '=', 0);
        $query = $query->where('fechaWH', '>=', time() - $sixMonths);

        $query = $query->Where(function ($query) use ($hileraBuscar) {
            $query->where('descripcion', 'like', '%' . $hileraBuscar . '%')
                ->orwhere('tracking', 'like', '%' . $hileraBuscar . '%')
                ->orwhere('id', 'like', '%' . $hileraBuscar . '%');
        });

        $recibos = $query->paginate($r->get('limit', 25));
        return $this->createOkResponse($recibos);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getRecibo(Request $request, $ext_id)
    {
        $cliente_id = Auth::user()->cliente_id;

        $r = ReciboBodega::query()->where('clientes_id', $cliente_id)->where('idext', $ext_id)->with('guia:id,numero,ext_id,exporter,consignatario,numero_vuelo,factura_id,fecha')->firstOrFail();

        return $this->createOkResponse($r);
    }

    private function autorizadotoVerbose($autorizado, $nota)
    {
        if ($autorizado == -1) {
            return $nota . '  Esperar';
        }

        if ($autorizado == 1) {
            return $nota . '  Autorizo Aereo';
        }

        if ($autorizado == 2) {
            return $nota . ' Autorizo Maritimo';
        }
        if ($autorizado == 3) {
            return $nota . ' Autorizo Courier';
        }

    }

    public function autorizarRecibo(Request $r, $ext_id)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($r, [
            'autorizado' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $recibo = ReciboBodega::query()->where('clientes_id', $cliente_id)->where('idext', $ext_id)->firstOrFail();

        if (intval($recibo->autorizadoxcliente) > 0) {
            return $this->createErrorResponse('Recibo ya fue autorizado.', $recibo);
        }

        $autorizado = $r->get('autorizado');
        $notaCliente = $this->autorizadotoVerbose($autorizado, $r->get('nota'));

        $recibo->update(['notacliente' => $notaCliente, 'autorizadoxcliente' => $autorizado, 'fechaclienteaut' => time()]);

        return $this->createOkResponse($recibo);
    }


    public function autorizarRecibos(Request $r)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($r, [
            'autorizado' => 'required',
            'ext_ids' => 'required',
        ]);

        $extIds = explode(',', $r->get('ext_ids'));

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $recibos = ReciboBodega::query()->where('clientes_id', $cliente_id)->whereIn('idext', $extIds)->where('autorizadoxcliente', 0);

        $reciboAutorizar = [];
        $pcs = 0;
        $weight = 0;
        $cuft = 0;
        $volumen = 0;

        $autorizado = $r->get('autorizado');
        foreach ($recibos->get() as $recibo) {

            $weight = $weight + $recibo->total_peso;
            $volumen = $volumen + $recibo->total_volumen;
            $cuft = $cuft + $recibo->total_cu;
            $pcs = $pcs + $recibo->total_items;
            $reciboAutorizar[] = $recibo->id;
            if( $recibo->peligro>0 && ($autorizado==1 || $autorizado==3) ){
                return $this->createErrorResponse('Recibo '. $recibo->id.' no se puede autorizar areo ya que es material peligroso', null);
            }
        }

        if (sizeof($reciboAutorizar) == 0) {
            return $this->createErrorResponse('No hay informacion para esos ids o paquete ya fue autorizado', null);
        }

        $grupoTxt = ' Con:' . implode('', $reciboAutorizar);
        if (sizeof($reciboAutorizar) == 1) {
            $grupoTxt = '';
        }
        $notaCliente = $this->autorizadotoVerbose($autorizado, $r->get('nota')) . $grupoTxt;
        $solicitud = SolicitudConsolidacion::create(['cliente_id' => $cliente_id, 'tipo' => $autorizado, 'pcs' => $pcs, 'weight' => $weight, 'cuft' => $cuft, 'volumen' => $volumen]);

        $ret = ReciboBodega::wherein('id', $reciboAutorizar)->update(['notacliente' => $notaCliente, 'autorizadoxcliente' => $autorizado, 'solicitud_id' => $solicitud->id, 'fechaclienteaut' => time()]);
        return $this->createOkResponse($ret);
    }


    private function salvarAdjunto(Request $r, $reciboId)
    {

        $imageHandle = app(ImageHandler::class);
        return $imageHandle->saveImage($r, $reciboId);

    }


    public function imagenes(Request $r, $ext_id)
    {
        $cliente_id = Auth::user()->cliente_id;
        $month = 60 * 60 * 24 * 30;
        $recibos = ReciboBodega::query()->where('idext', '=', $ext_id)->where('clientes_id', $cliente_id)->where('fechaWH', '>', time() - ($month * 6))->get();
        $recibosId = [];
        foreach ($recibos as $recibo) {
            $recibosId[] = $recibo->id;
        }


        $imagenes = ReciboBodegaImagenes::query()->whereIn('WHID', $recibosId)->where('deletedate',0)->get();
        $ret=[];
        foreach ($imagenes as $imagen) {
            $imagen->url = str_replace('/WHimg', env('WH_IMG_URL'), $imagen->url);
            $ret[]=$imagen;
        }
        return $this->createOkResponse($ret);
    }

    public function adjuntosDisponibles(Request $r, $ext_id)
    {
        $cliente_id = Auth::user()->cliente_id;
        $month = 60 * 60 * 24 * 30;
        $recibos = ReciboBodega::query()->where('idext', '!=', $ext_id)->where('clientes_id', $cliente_id)->where('bl_id', 0)->where('fechaWH', '>', time() - ($month * 6))->get();
        $recibosId = [];
        foreach ($recibos as $recibo) {
            $recibosId[] = $recibo->id;
        }
        $imagenes = Imagenes::query()->whereIn('idWH', $recibosId)->where('padre', null)->get();
        return $this->createOkResponse($imagenes);
    }


    public function adjuntarReciboTest(Request $r, $ext_id)
    {
        $ret = ["requests" => $r->all()];
        $ret = array_merge($ret, ["ext_id" => $ext_id]);
        $ret = array_merge($ret, ["has_adjunto" => $r->hasFile('adjunto')]);

        if ($r->hasFile('adjunto')) {
            $ret = array_merge($ret, ["adjunto" => $r->file('adjunto')->getClientOriginalName()]);
        }


        return $this->createOkResponse($ret);
    }


    public function adjuntarRecibo(Request $r, $ext_id)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($r, [
            'descripcion' => ['required', 'min:3']
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $recibo = ReciboBodega::query()->where('clientes_id', $cliente_id)->where('idext', $ext_id)->firstOrFail();

        $recibo->update(['descripcion_cliente' => $r->get('descripcion'), 'revisado_por' => 0]);
        $reciboId = $recibo->id;
        $salvarData = $this->salvarAdjunto($r, $reciboId);
        if (!$salvarData) {
            return $this->createOkResponse('ERROR');
        }


        $recibo->update(['cantidadfotoscliente' =>$recibo->cantidadfotoscliente+1]);

        $historial = ['fecha' => time(), 'privado' => '1', 'mensaje' => 'Cliente agrego <a target=_new href=/' . $salvarData->nombre . ' >Adjunto </a>', 'id_producto' => $recibo->id, 'id_usuario' => 0];
        ReciboBodegaHistorial::create($historial);

        return $this->createOkResponse($salvarData);
    }


    public function borrarRecibo(Request $r, $ext_id, $adjunto_id)
    {

        $cliente_id = Auth::user()->cliente_id;
        $recibo = ReciboBodega::query()->where('clientes_id', $cliente_id)->where('idext', $ext_id)->firstOrFail();
        $images = Imagenes::query()->where('idWH', $recibo->id)->where('id', $adjunto_id)->firstOrFail();
        $images->update(['deleteddate' => time()]);
        $imagesAvailable = Imagenes::query()->where('idWH', $recibo->id)->where('deleteddate', 0)->get();
        if ($imagesAvailable->count() < 1) {
            ReciboBodega::query()->findOrFail($recibo->id)->update(['cantidadfotoscliente' => 0]);
        }

        return $this->createOkResponse('OK');
    }

    public function verAdjuntos(Request $r, $ext_id)
    {

        $cliente_id = Auth::user()->cliente_id;
        $recibo = ReciboBodega::query()->where('clientes_id', $cliente_id)->where('idext', $ext_id)->firstOrFail();
        $images = Imagenes::query()->where('idWH', $recibo->id)->where('deleteddate', 0)->get();

        return $this->createOkResponse($images);
    }


    /**
     * @param Request $r
     *
     * @return JsonResponse
     */
    public function buscar(Request $r)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($r, [
            'hilera' => 'required|min:5'
        ]);
        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }
        $hilera = $r->get('hilera');
        $recibos = ReciboBodega::query()
            ->where('clientes_id', $cliente_id)
            ->where('factura', 'like', '%' . $hilera . '%')->get();

        if (!$recibos) {
            $recibos = ReciboBodega::query()
                ->where('clientes_id', $cliente_id)
                ->where('descripcion', 'like', '%' . $hilera . '%')->get();
        }

        return $this->createOkResponse($recibos);
    }


    /**
     * @param Request $r
     *
     * @return JsonResponse
     */
    public function searchCliente(Request $r)
    {
        $query = Cliente::query();
        $from = $r->get('from');
        $to = $r->get('to');
        $clerkId = $r->get('clerk_id');
        $terminalId = $r->get('terminal_id');
        $status = $r->get('status');
        $walletId = $r->get('wallet_id');
        $minAmount = $r->get('min_amount');
        $maxAmount = $r->get('max_amount');

        $query = $to ? $query->where('SoldDate', '<', $to) : $query;
        $query = $from ? $query->where('SoldDate', '>', $from) : $query;
        $query = $clerkId ? $query->where('SoldIdUser', $clerkId) : $query;
        $query = $terminalId ? $query->where('SoldTerminalID', $terminalId) : $query;
        $query = $minAmount ? $query->where(\DB::raw('ABS(SoldValue)'), '>=', $minAmount) : $query;
        $query = $maxAmount ? $query->where(\DB::raw('ABS(SoldValue)'), '<=', $maxAmount) : $query;

        $query = $status ? $query->where('status', $status) : $query;
        $query = $walletId ? $query->where('TicketNumber', $walletId) : $query;

        $wallet = $query->paginate($r->get('limit', 25));

        return $this->createOkResponse($wallet);
    }


}
