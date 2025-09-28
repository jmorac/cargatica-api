<?php

use Illuminate\Support\Facades\Route;

// Root of the API group → /api
Route::get('/', function () {
    return '<pre>CT [' . env('APP_ENV') . ']</pre>';
});

// Make string-based controllers work (Laravel 8+ no longer assumes this by default)
Route::namespace('App\Http\Controllers')->group(function () {
    // Simple example
    Route::get('/fact', 'FacturaController@testfact');

    // ----- Callbacks -----
    Route::prefix('callback')->group(function () {
        Route::match(['get', 'post'], 'bac/3d', 'BacController@update3dTransaction');
    });

    // ----- Forms -----
    Route::prefix('forms')->group(function () {
        Route::get('bac/3d', 'BacController@create3DForm');
    });

    // ----- Versioned API (final path will be /api/v1/...) -----
    Route::prefix('v1')->group(function () {
        // Auth / Login
        Route::post('login', 'ClienteController@loginCliente');
        Route::post('login-pin', 'ClienteController@loginPin');
        Route::post('send-message', 'ClienteController@sendMessage');
        Route::post('enviar-contrasena', 'ClienteController@resetPassword');
        Route::post('enviar-pin', 'ClienteController@sendPin');
        Route::post('cambiar-contrasena-token', 'ClienteController@setPasswordWithToken');
        Route::post('login-user', 'UsuarioController@loginUsuario');
        Route::post('login-afiliado', 'UsuarioController@loginUsuario');

        Route::post('crear-cliente', 'ClienteController@insertarCliente');
        Route::match(['get','post'], 'reset-password', 'ClienteController@resetPassword');
        Route::get('buscar-persona', 'ClienteController@sacarDatosHacienda');

        // ----- Usuario (requires auth) -----
        Route::middleware('auth')->prefix('usuario')->group(function () {
            // Manifiestos
            Route::prefix('manifiestos')->group(function () {
                Route::get('/', 'ManifiestoController@getManifiestos');
                Route::get('{manifiesto_id}', 'ManifiestoController@getManifiesto');
                Route::get('{manifiesto_id}/scans', 'ManifiestoController@getScans');
                Route::post('{manifiesto_id}/scans/{scan_id}', 'ManifiestoController@updateScans');
                Route::post('{manifiesto_id}/guia', 'ManifiestoController@agregarGuia'); // fixed extra brace
            });

            // Transacciones financieras
            Route::prefix('transaccion-financiera')->group(function () {
                Route::post('/', 'TransaccionFinancieraController@insertarTransaccion');
            });

            // Banners
            Route::prefix('banners')->group(function () {
                Route::post('/', 'BannerController@addBanner');
            });
        });

        // ----- Afiliado (requires auth) -----
        Route::middleware('auth')->prefix('affiliado')->group(function () {
            Route::get('who-am-i', 'UsuarioController@getUsuario');
            Route::get('clientes', 'ClienteController@getClienteParaAfiliados');
            Route::prefix('recibos-bodega')->group(function () {
                Route::get('buscar-por-cambio', 'ReciboBodegaController@getRecibosByChangeDateAffiliado');
            });
        });

        // ----- Cliente (requires authCliente) -----
        Route::middleware('authCliente')->prefix('cliente')->group(function () {
            Route::get('legal', 'AvisoLegalController@avisoLegalPendiente');
            Route::post('legal', 'AvisoLegalController@firmarAvisoLegal');

            Route::get('who-am-i', 'ClienteController@getCliente');
            Route::post('reset-password', 'ClienteController@resetPasswordLoggedIn');

            Route::get('/', 'ClienteController@getCliente');
            Route::delete('/', 'ClienteController@deleteCliente');
            Route::post('/', 'ClienteController@updateCliente');

            Route::get('banners', 'BannerController@getBanners');

            // Recibos bodega
            Route::prefix('recibos-bodega')->group(function () {
                Route::get('buscar-por-cambio', 'ReciboBodegaController@getRecibosByChangeDate');
                Route::get('buscar', 'ReciboBodegaController@buscarRecibos');
                Route::get('{ext_id}/adjuntos-disponibles', 'ReciboBodegaController@adjuntosDisponibles');
                Route::get('{ext_id}/imagenes', 'ReciboBodegaController@imagenes');
                Route::get('{ext_id}', 'ReciboBodegaController@getRecibo');
                Route::post('autorizar', 'ReciboBodegaController@autorizarRecibos');
                Route::post('{ext_id}/adjuntos', 'ReciboBodegaController@adjuntarRecibo');
                Route::post('{ext_id}/adjuntostest', 'ReciboBodegaController@adjuntarReciboTest');
                Route::get('{ext_id}/adjuntos', 'ReciboBodegaController@verAdjuntos');
                Route::delete('{ext_id}/adjuntos/{adjunto_id}', 'ReciboBodegaController@borrarRecibo');
                Route::get('/', 'ReciboBodegaController@getRecibos');
                Route::post('{recibo_id}', 'ReciboBodegaController@updateRecibo');
            });

            // Solicitudes de guía
            Route::prefix('solicitud-guia')->group(function () {
                Route::delete('{id}', 'SolicitudConsolidacionController@borrar');
                Route::delete('{id}/{id_ext_recibo}', 'SolicitudConsolidacionController@quitarRecibo');
                Route::get('/', 'SolicitudConsolidacionController@getSolicitudes');
                Route::get('{id}', 'SolicitudConsolidacionController@getSolicitud');
            });

            // Guías
            Route::prefix('guias')->group(function () {
                Route::get('agrupadas', 'GuiaController@getGuiasAgrupadas');
                Route::get('{ext_id}', 'GuiaController@getGuia');
                Route::get('/', 'GuiaController@getGuias');
            });

            // Direcciones
            Route::prefix('direcciones')->group(function () {
                Route::get('{direccion_id}', 'DireccionClienteController@getDireccion');
                Route::post('{direccion_id}', 'DireccionClienteController@editarDireccion');
                Route::delete('{direccion_id}', 'DireccionClienteController@borrarDireccion');
                Route::post('/', 'DireccionClienteController@agregarDireccion');
                Route::get('/', 'DireccionClienteController@getDirecciones');
            });

            // Envíos
            Route::prefix('envios')->group(function () {
                Route::get('{envio_id}', 'EnvioController@getEnvio');
                Route::get('/', 'EnvioController@getEnvios');
                Route::post('/', 'EnvioController@crearEnvio');
            });

            // Facturas
            Route::prefix('facturas')->group(function () {
                Route::get('{identifier}', 'FacturaController@getFactura');
                Route::post('{identifier}/pagar-efectivo', 'FacturaController@deseaPagarEfectivo');
                Route::get('/', 'FacturaController@getFacturas');
            });

            // Trackings
            Route::prefix('trackings')->group(function () {
                Route::get('/', 'TrackingController@getTrackings');
                Route::get('search/{tracking}', 'TrackingController@searchTrackingUrl');
                Route::get('search', 'TrackingController@searchTracking');
                Route::get('{tracking_id}', 'TrackingController@getTracking');
                Route::post('/', 'TrackingController@insertarTracking');
                Route::delete('{tracking_id}', 'TrackingController@deleteTrackings');
                Route::post('{tracking_id}/autorizar', 'TrackingController@autorizar');
                Route::get('{tracking_id}/adjuntos-disponibles', 'TrackingController@adjuntosDisponibles');
                Route::post('{tracking_id}/adjuntos', 'TrackingController@adjuntarRecibo');
                Route::get('{tracking_id}/adjuntos', 'TrackingController@verAdjuntos');
                Route::delete('{tracking_id}/adjuntos/{adjunto_id}', 'TrackingController@borrarRecibo');
            });

            // Transacciones tarjetas
            Route::prefix('transactiones-tarjetas')->group(function () {
                Route::get('/', 'TransaccionesTarjetasController@getTransactions');
                Route::get('{transaction_id}', 'TransaccionesTarjetasController@getTransaction');
                Route::post('/', 'TransaccionesTarjetasController@pagarFacturas');
                Route::post('bac3d', 'TransaccionesTarjetasController@pagarFacturas3D');
            });
        });

        // Catálogos / públicos dentro de v1
        Route::get('companias', 'CompaniaController@getCompanias');
        Route::get('companias/all', 'CompaniaController@getAllCompanias');
        Route::prefix('companias')->group(function () {
            Route::get('{id}', 'CompaniaController@getCompania');
            Route::get('/', 'CompaniaController@getCompanias');
        });

        Route::prefix('oficinas')->group(function () {
            Route::get('{oficina_id}', 'OficinaController@getOficina');
            Route::get('/', 'OficinaController@getOficinas');
        });

        Route::prefix('ayuda')->group(function () {
            Route::post('{email_id}/enviar', 'ClienteController@pedirAyuda');
            Route::get('/', 'ClienteController@contactosDeAyudaDisponibles');
        });

        Route::post('tracking', 'TrackingController@searchPublicTracking');
        Route::get('banners', 'BannerController@getBanners');
        Route::get('facturadores', 'FacturadorController@getFacturadores');
    });
});
