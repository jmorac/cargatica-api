<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Factura extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'id';
	public    $timestamps = false;

	protected $maps = [
        'nombre_factura' =>'nombrefactura',
        'modo_pago'     =>'modopago',
        'recibo_dinero'  =>'recibodinero',
        'fecha'     =>'fechaunix',
        'dia_de_pago' => 'diadepago',
        'vence'   => 'venceunix',
        'notas_al_cliente' => 'notasalcliente',
        'monto_pagado' =>'montopagado',
        'forma_pago' =>'formaquepago',
        'compra_usd' =>'compra_USD',

        'plazo_credito' => 'PlazoCredito'
	];

	protected $appends = [
        'monto_total',
        'nombre_factura',
        'modo_pago',
        'recibo_dinero',
        'fecha',
        'dia_de_pago',
        'vence',
        'notas_al_cliente',
        'monto_pagado',
        'forma_pago',
        'compra_usd',
        'plazo_credito'

	];

    protected $with = [
    "facturador"
    ];

    protected $hidden = [
        'fechaunix',
        'XML_enviado',
        'XML_recibido',
        'xml_recibido_hacienda',
        'json_recibido_sufacturafacil',
        'retry',
        'guiaid',
        'total',
        'nombrefactura',
        'doneby',
        'modopago',
        'recibodinero',
        'BLNo',
        'diadepago',
        'venceunix',
        'isconsolidadora',
        'iscasillero',
        'notasalcliente',
        'clientefacturadorid',
        'clientid',
        'montopagado',
        'formaquepago',
        'ventadolar',
        'compradolar',
        'fechareproceso',
        'PlazoCredito',
        'MedioPago',
        'compra_USD',
        'diaagregopago',
        'dondedeposito'

	];

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	public function getMontoTotalAttribute(){
	    return round(($this->gran_total*100)/100)/100;
    }

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'CT_facturas';




    /**
     * @return BelongsTo
     */
    public function facturador()
    {

        return $this->belongsTo(Facturador::class,'facturador_id', 'id');

    }





}
