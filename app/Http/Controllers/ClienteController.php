<?php

namespace App\Http\Controllers;
use App\Helpers\RemoteEmailService;
use Carbon\Carbon;
use App\Models\Cliente;
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

class ClienteController extends Controller
{


    private function PasswordStrength()
    {
        return $passwordStrength = [
            'required',
            'string',
            'min:8',             // must be at least 10 characters in length
            //'regex:/[a-z]/',      // must contain at least one lowercase letter
            //'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            // 'regex:/[@$!%*#?&]/', // must contain a special character
        ];

    }

    private function getLogin($email, $password, $forgotPasswordToken = false, $apiClient = 0)
    {

        if ($forgotPasswordToken) {
            Log::debug('ForgotPassord with token:'.$forgotPasswordToken); //
            $cliente = Cliente::query()->where('forgot_pass_token', $forgotPasswordToken)->first();
            if ($cliente) {
                Log::debug('ForgotPassord client:'.$cliente->email.' found');
                return $cliente;
            }
        } else {
            $cliente = Cliente::query()->where('email', $email)->first();
        }

        if(!$cliente){
            Log::debug('Cliente not found');
            return false;
        }

        if($cliente->deleted_cliente != '0000-00-00 00:00:00' && $cliente->deleted_cliente != null){
            Log::debug('Cliente deleted');
            return false;
        }

        // TODO:  CAMBIAR A MD5
        if ($cliente) {

            if ($cliente->contrasena == $password  ) {

                //   $user = User::query()->where("cliente_id", $cliente->id)->first();
                //   $user->update(['api_client'=>$apiClient,'last_login'=>time()  ]);

                return $cliente;
            }

            return $cliente;
        }
        return false;
    }


    public function contactosDeAyudaDisponibles(Request $request)
    {

        $result = EmailAyuda::query()
            ->paginate($request->get('limit', 1000));

        return $this->createOkResponse($result);

    }


    public function sacarDatosHacienda(Request $request)
    {
        $validator = $this->validateRequest($request, [
            'cedula' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }


        $tica = new Ticas();
        $data = $tica->getData($request->get('cedula'));
        if (!$data) {
            return $this->createOkResponse(null, 'Error');
        } else {
            return $this->createOkResponse($data);
        }

    }

    public function pedirAyuda(Request $request, $email_id)
    {

        $validator = $this->validateRequest($request, [
            'email_solicitante' => 'required|email',
            'encabezado' => 'required',
            'contenido' => 'required',
            'nombre' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }
        $email_solicitante = $request->get('email_solicitante');
        $encabezado = $request->get('encabezado');
        $contenido = $request->get('contenido');
        $nombre = $request->get('nombre');

        $ayuda = EmailAyuda::query()->where('id', $email_id)->firstOrFail();
        if ($ayuda) {
          //  $mail = new PHPMailer();
            $mail = new RemoteEmailService();

            $email = $ayuda->email;
            $nombreServicio = 'soporte';
            /*
                        $mail->IsSMTP();
                        $mail->SMTPDebug = 0;
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = "tls";
                        $mail->Host = env("EMAIL_HOST");
                        $mail->Port = env("EMAIL_PORT");
                        $mail->Username = env("EMAIL_USER");
                        $mail->Password = env("EMAIL_PASSWORD");
            */

            $mail->addReplyTo($email_solicitante, $nombre);
            $mail->SetFrom(env("EMAIL_USER"), 'CargaTica Ayuda');
            $mail->Subject = $encabezado;
            $mail->MsgHTML($email_solicitante." <Br>\n ".$nombre." <Br>\n".$contenido);
            $mail->AltBody = "Soporte";
            $mail->IsHTML(true);
            $mail->Body = $contenido;
            $mail->AddAddress($email, $nombreServicio);

            if (!$mail->Send()) {
                return $this->createOkResponse(null, 'Error', $mail->ErrorInfo);
            } else {
                return $this->createOkResponse("OK");
            }

        }

    }


    public function setPasswordWithToken(Request $request)
    {
        $validator = $this->validateRequest($request, [
            'token_contrasena' => 'required',
            'contrasena_nueva' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $forgotPasswordToken = $request->get('token_contrasena');
        $contresenaNueva = $request->get('contrasena_nueva');


        if (strlen($contresenaNueva) < 8) {
            return $this->createOkResponse(null, 'Error', 'Debe tener un minimo de 8');
        }

        if (!preg_match("#[a-z]+#", $contresenaNueva)) {
            return $this->createOkResponse(null, 'Error', 'Debe contener minisculas ');
        }

        if (!preg_match("#[A-Z]+#", $contresenaNueva)) {
            return $this->createOkResponse(null, 'Error', 'Debe contener mayusculas');
        }


        $cliente = $this->getLogin('', '', $forgotPasswordToken);

        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'Token Invalido');
        }

        if ($cliente->update(['contrasena' => $contresenaNueva, 'forgot_pass_token' => ''])) {

            return $this->createOkResponse($cliente);
        }

        return $this->createOkResponse(null, 'Error', 'Error Actualizando');

    }


    private function CreateSession($cliente, $apiClient,$channel=1)
    {
        $user = User::query()->where("cliente_id", $cliente->id)->where("channel", $channel);

        if ($user->get()->count()) {
            $user->delete();
        }

        $api_token = Str::random(60);

        $data = ['cliente_id' => $cliente->id, 'name' => $cliente->email, 'api_token' => $api_token, 'api_client' => $apiClient,  'last_login' => time(), 'password'=>'', 'channel' => $channel];

        User::create($data);

       return $api_token;
    }

    private
    function loginClienteAux($email, $contrasena, $apiClient = 0)
    {

        $cliente = $this->getLogin($email, $contrasena, false, $apiClient);

        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'Error en Credenciales..');
        }

        $api_token=$this->CreateSession($cliente,$apiClient,1);

        $cliente = Cliente::query()->with('oficina')
            ->findOrFail($cliente->id);


        $cliente["api_token"] =   $api_token;
        return $this->createOkResponse($cliente);

    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public
    function loginCliente(Request $request)
    {

        $validator = $this->validateRequest($request, [
            'email' => 'required',
            'contrasena' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $contrasena = $request->get('contrasena');
        $email = $request->get("email");
        $apiClient = $request->get("api_client", 0);

        return $this->loginClienteAux($email, $contrasena, $apiClient);

    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public
    function loginPin(Request $request)
    {

        $validator = $this->validateRequest($request, [
            'phone' => 'required|digits:8',
            'pin' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $phone = $request->get('phone');
        $pin = $request->get("pin");
        $apiClient = $request->get("api_client", 0);

        $cliente = Cliente::query()->where('telefono1', $phone)->where('pin', $pin)->first();
        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'Error en PIN o Numbero Telefonico');
        }

        $api_token=$this->CreateSession($cliente,$apiClient,2);

        $cliente = Cliente::query()->with('oficina')
            ->findOrFail($cliente->id);

        $cliente["api_token"] =   $api_token;
        return $this->createOkResponse($cliente);

    }


    public function getClienteParaAfiliados(Request $r ){

        $usuario_id = Auth::user()->usuario_id;
        $usuario = Usuario::query()->where('id',$usuario_id)->first();
        if($usuario->compania_id==0){
            return $this->createErrorResponse('El usuario no es un afiliado', null);
        }

        $query=Cliente::query()->orderBy('id','desc');
        $query=$query->where('compania_id',$usuario->compania_id);
        $clientes = $query->paginate( $r->get( 'limit', 25 ) );
        return $this->createOkResponse($clientes);

    }

    public
    function sendMessage(Request $request)
    {
        $validator = $this->validateRequest($request, [
            'email' => 'required',
            'title' => 'required',
            'message' => 'required',
            'smsauth' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $message = $request->get('message');
        $email = $request->get("email");
        $smsAuth = $request->get("smsauth");
        $title = $request->get("title");
        $debug = $request->get("debug");

        if ($smsAuth != env('PUSHEXTERNALKEY')) {
            return $this->createOkResponse(null, 'Error', 'Autorizacion no es valida ');
        }

        $cliente = Cliente::query()->where('email', $email)->first();
        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'No hay cliente ');
        }

        $push = new Push(env('PUSHURL'), env('PUSHKEY'), $debug);

        //     Log::info('Sending Push:' . $message . ',' . $title . ',' . $cliente->id . ',' . 'c_' );

        return $this->createOkResponse($push->send($message, $title, $cliente->id, 'c_'));

    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public
    function insertarCliente(Request $request)
    {

        $validator = $this->validateRequest($request, [
            'primer_nombre' => 'required',
            /*     'primer_apellido' => 'required', */
            /*   'cedula' => 'required', */
            'tipo_cedula' => 'required',
            'contrasena' => 'required',
            'telefono1' => 'required',
            'email' => ['required', 'email',
                Rule::unique('CT_Clientes', 'email')
            ],

        ]);


        $compania_id = $request->get('compania_id') ? $request->get('compania_id') : 1;

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $nombre = $request->get('primer_nombre') . ' ' . $request->get('primer_apellido') . ' ' . $request->get('segundo_apellido');

        $cliente = $userInfo = $request->only('primer_nombre', 'primer_apellido', 'segundo_apellido', 'cedula', 'email', 'nombre_factura', 'email_facturacion',
            'contrasena',
            'direccion',
            'telefono1',
            'telefono2',
            'notificaremail',
            'notificarSMS',
            'tipo_cedula',
            'compania_id');

        $cliente = array_merge($cliente, ['nombre' => $nombre]);
        $nuevoCliente = Cliente::create($cliente);
        $nuevoCliente->update(['casillero' => \DB::raw('id')]);

        $contrasena = $request->get('contrasena');
        $email = $request->get("email");
        $apiClient = $request->get("api_client", 0);

        return $this->loginClienteAux($email, $contrasena, $apiClient);

    }

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public
    function getCliente(Request $request)
    {
        $cliente_id = Auth::user()->cliente_id;

        $cliente = Cliente::query()->with('oficina')
            ->findOrFail($cliente_id);

        return $this->createOkResponse($cliente);
    }

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public
    function deleteCliente(Request $r)
    {
        $cliente_id = Auth::user()->cliente_id;

        $cliente = Cliente::query()
            ->where('id',$cliente_id)
            ->update(['deleted_cliente' => Carbon::now()]);
        return $this->createOkResponse($cliente);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public
    function getClientes(Request $request)
    {
        $result = Cliente::query()
            ->paginate($request->get('limit', 1000));

        return $this->createOkResponse($result);
    }

    /**
     * @param Request $request
     * @param         $cliente_id
     *
     * @return JsonResponse
     */
    public
    function updateCliente(Request $r)
    {

        $cliente_id = Auth::user()->cliente_id;
        $validator = $this->validateRequest($r, [
            'email' => 'required|email',
            'primer_nombre' => 'required',
            'primer_apellido' => 'required',
            'segundo_apellido' => 'required',
            'tipo_cedula' => 'required|int'

        ]);

        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }

        $duplicateEmail = Cliente::query()->where('id', '<>', $cliente_id)->where('email', $r->get('email'))->count();

        if ($duplicateEmail) {
            return $this->createErrorResponse('Algun otra cuenta utiliza ese correo', null);
        }

        $nombre = $r->get('primer_nombre') . ' ' . $r->get('primer_apellido') . ' ' . $r->get('segundo_apellido');
      //  $cliente = $r->only('nombre', 'no_email', 'primer_nombre', 'primer_apellido', 'segundo_apellido', 'cedula', 'email', 'email_facturacion', 'direccion', 'telefono1', 'telefono2', 'notificaremail', 'notificarSMS', 'cedula', 'tipo_cedula', 'default_oficina_id');

        $cliente = $userInfo = $r->only('primer_nombre', 'primer_apellido', 'segundo_apellido', 'cedula', 'email', 'nombre_factura', 'email_facturacion',
            'contrasena',
            'direccion',
            'telefono1',
            'telefono2',
            'notificaremail',
            'notificarSMS',
            'tipo_cedula',
            'compania_id',
            'exoneracion_tipo_doc',
            'exoneracion_num_doc',
            'exoneracion_nombre_inst',
            'exoneracion_nombre_inst_otro',
            'exoneracion_articulo',
            'exoneracion_inciso',
            'exoneracion_fecha_emision',
            'exoneracion_porcentaje',
            'codigo_actividad_receptor'


        );





        $cliente = Cliente::query()
            ->where('id',$cliente_id)
            ->update($cliente);



        return $this->getCliente($r);
    }


    /**
     * @param Request $r
     *
     * @return JsonResponse
     */
    public
    function searchCliente(Request $r)
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

        $wallet = $query->paginate($r->get('limit', 1000));

        return $this->createOkResponse($wallet);
    }

    public
    function resetPasswordLoggedIn(Request $request)
    {
        $validator = $this->validateRequest($request, [
            'contrasena_actual' => 'required',
            'contrasena_nueva' => 'required',

        ]);

        $cliente_id = Auth::user()->cliente_id;

        $cliente = Cliente::query()->with('oficina')
            ->findOrFail($cliente_id);


        if ($validator->fails()) {
            return $this->createValidatorErrorResponse($validator);
        }
        $contrasena = $request->get('contrasena_actual');
        $contresenaNueva = $request->get('contrasena_nueva');

        if (strlen($contresenaNueva) < 8) {
            return $this->createOkResponse(null, 'Error', 'Debe tener un minimo de 8');
        }


        if (!preg_match("#[a-z]+#", $contresenaNueva)) {
            return $this->createOkResponse(null, 'Error', 'Debe contener minisculas ');
        }

        if (!preg_match("#[A-Z]+#", $contresenaNueva)) {
            return $this->createOkResponse(null, 'Error', 'Debe contener mayusculas');
        }

        $contrasena = $request->get('contrasena_actual');

        $cliente = $this->getLogin($cliente->email, $contrasena);


        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'Error en Credenciales');
        }

        $cliente->update(['contrasena' => $contresenaNueva]);

        return $this->createOkResponse($cliente);

    }

    public
    function resetPassword(Request $r)
    {

        $email = $r->get('email');
        $url = $r->get('url');
        $cliente = Cliente::query()->where('email', $email)->first();


        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'Correo No existe');
        }

        if($cliente->deleted_cliente != '0000-00-00 00:00:00' && $cliente->deleted_cliente != null){
            return $this->createOkResponse(null, 'Error', 'Correo No existe.');
        }


        $randomconrasena = Str::random(10);
        $cliente->update(['forgot_pass_token' => $randomconrasena]);


        Log::debug('Reset Password Token: generated for: ' . $email.' with token: '.$randomconrasena );

       // $mail = new PHPMailer();
        $mail = new RemoteEmailService();

        $urlDefaultPasswordRecovery = env('RESET_PASSWORD_URL');// 'https://www.cargatica.com/login/';


        $content = "Por Favor accesar el sistema directamente en este enlace:<br><a href=" . $urlDefaultPasswordRecovery . "?rpt=" . $randomconrasena . "&passwordreset=1 >Link Temporal</a><br><br>
<br><br>Si esta utilizando el app puede utilizar el siguiente Token:  <b>" . $randomconrasena . "</b><br>
<br>";
        $nombre = '';
/*
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = env("EMAIL_HOST");
        $mail->Port = env("EMAIL_PORT");
        $mail->Username = env("EMAIL_USER");
        $mail->Password = env("EMAIL_PASSWORD");
        $mail->SetFrom(env("EMAIL_USER"), 'CargaTica Notificaciones');
*/
        $mail->Subject = "Ingreso Temporal";
        $mail->MsgHTML($content);
        $mail->AltBody = "Link temporal";
        $mail->IsHTML(true);
        $mail->Body = $content;
        $mail->AddAddress($email, $nombre);
        $sent= $mail->Send();
      //  Log::info($sent);
        $ret=json_decode($sent);
      //  Log::info(json_encode($ret));
        if ($ret ) {
            return $this->createOkResponse("ok");
        } else {

            return $this->createErrorResponse("Could not send email",$sent);
        }


    }




    public
    function sendPin(Request $r)
    {

        $phone = $r->get('phone');
        $cliente = Cliente::query()->where('telefono1', $phone)->first();

        if (!$cliente) {
            return $this->createOkResponse(null, 'Error', 'Correo No existe');
        }


        $pin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $cliente->update(['pin' => $pin]);

     //   $mail = new PHPMailer();
        $mail = new RemoteEmailService();
        $content = "Este es su nuevo pin  ".$pin;
        $nombre = '';

        /*
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = env("EMAIL_HOST");
        $mail->Port = env("EMAIL_PORT");
        $mail->Username = env("EMAIL_USER");
        $mail->Password = env("EMAIL_PASSWORD");
        $mail->SetFrom(env("EMAIL_USER"), 'CargaTica Notificaciones');
        */
        $mail->Subject = "Ingreso Pin";
        $mail->MsgHTML($content);
        $mail->AltBody = "Pin temporal";
        $mail->IsHTML(true);
        $mail->Body = $content;
        $mail->AddAddress($cliente->email, $nombre);
        $sent= $mail->Send();

        if (!$sent) {
            // echo "Error enviando correo: " . $mail->ErrorInfo;
        } else {
            // echo "<br>Se envio le envio un correo con la informacion de su cuenta.<br>";
        }

        return $this->createOkResponse("OK");

    }



}

