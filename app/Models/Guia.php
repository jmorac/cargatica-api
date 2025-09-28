<?php

namespace App\Models;
use Sofa\Eloquence\Mappable;
use Sofa\Eloquence\Eloquence;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Boolean;


class Guia extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'BLNo';
	public    $timestamps = false;

	protected $maps = [
		'id'   => 'BLNo',
		'numero' => 'BLNoVisual',
		'ext_id' =>'idextguia',
        'exporter'=>'Exporter',
        'consignatario' => 'Consignee',
        'numero_vuelo'  =>'flightnumber',
        'factura_id'=>'facturaid',
	];

	protected $appends = [
		'id',
		'numero',
		'ext_id',
        'exporter',
        'consignatario',
        'numero_vuelo',
        'factura_id',
        'piezas',
        'peso',
        'peso_cubico',
        'vol',
        'pagado',
        'solicito_efectivo',
        'app_status'
	];


	protected $hidden = [
		'BLNo',
		'BLNoVisual',
		'idextguia',
		'hide',
		'manifestoidOLD',
		'TEMPEXPORT',
        'edited_date',
        'Exporter',
        'Consignee',
        'exportreference',
        'portofloading',
        'portofdischarge',
        'exportingcarrier',
        'Notifyparty',
        'cost',
        'doneby',
        'donebyBK',
        'changedby',
        'aes',
        'flightnumber',
        'Tipos_Courier_Id',
        'facturaid',
        'Completada',
        'pcs',
        'weight',
        'cuft',
        'volumen',
        'viewpcs',
        'viewweight',
        'viewcuft',
        'viewvolumen',
        'viewWHdesc',
        'facturaimportadora',
        'facturacasillero',
        'facturareg',
        'agencia_desalmacena',
        'clientnotified',
        'nombre_desalmacena',
        'cedula_desalmacena',
      /*  'clienteid', */
        'iscasillero',
        'fuel',
        'bodegaje',
        'seguro',

	];


     protected $with = ['agencia_estado'];

    /**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'BLNo'
	];

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'CT_SHIP';

    /**
     * @return BelongsTo
     */

	public function agencia_estado()
    {

	    return $this->belongsTo(StatusAgencia::class,'agencia_status', 'id');
    }




    public function getAppStatusAttribute(){
/*
   $query = $asignadasEnviadasPorCliente!=null ? $asignadasEnviadasPorCliente ? $query->where('envio_cliente_id', '>', 0):$query->where('envio_cliente_id', '=', 0) : $query;
        $query = $fechaFactura!=null ? $fechaFactura ? $query->where('fecha_factura', '>', 0):$query->where('fecha_factura', '=', 0) : $query;
        $query = $entregado!=null ? $entregado? $query->where('entregado_id', '>', 0):  $query->where('entregado_id', '=', 0) : $query;
        $query = $pagado!=null ? $pagado? $query->where('fecha_pagado', '>', 0):  $query->where('fecha_pagado', '=', 0) : $query;
        $query = $listoParaRecoger!=null ? $listoParaRecoger? $query->where('listo_para_recoger', '>', 0):  $query->where('listo_para_recoger', '=', 0) : $query;

 */

        if ($this->fecha_factura=0  ){
        //    return "APROBADAS"; //no se ha pagado
        }



        if ($this->envio_cliente_id>0 && $this->entregado_id==0){
            return "TRANSITO"; // en transito
        }

       // if ($this->fecha_factura>0 && $this->desea_pagar_efectivo==0){
       if ($this->fecha_pagado==0 && $this->fecha_factura>0 && $this->desea_pagar_efectivo==0){
                return "PENDIENTE_PAGAR"; //no se ha pagado
        }
        if ($this->fecha_pagado>0 || $this->desea_pagar_efectivo>0){
            return "PAGADOOCREDITO"; //no se ha pagado
        }

        if ($this->fecha_pagado>0){
            return "PAGADO"; // pagado
        }

        if ($this->envio_cliente_id>0 && $this->entregado_id==0){
            return "LISTO_ENTREGAR"; // pagado
        }
        //TODO: REVISAR CAMPO
        if ($this->entregado_id>0 ){
            return "ENTREGADO"; //entregado
        }

        return "APROBADAS"; //Pendiente de aprobacion
    }


     public function  getPiezasAttribute(){
	    if($this->repackage==1){
	        return $this->viewpcs;
        }
        return $this->pcs;
     }

    public function  getPesoAttribute(){
        if($this->repackage==1){
            return $this->viewweight;
        }
        return $this->weight;
    }

    public function  getPesoCubicoAttribute(){
        if($this->repackage==1){
            return $this->viewcuft;
        }
        return $this->cuft;
    }

    public function  getVolAttribute(){
        if($this->repackage==1){
            return $this->viewvolumen;
        }
        return $this->volumen;
    }

    public function  getTieneFacturaAttribute(){
        if($this->facturaimportadora >0 ||
           $this->facturacasillero  >0 ||
            $this->facturareg >0 )
        {
           return true;
        }
        return false;
    }

    public function  getCombustibleAttribute(){
        if($this->fuel <0  )
        {
            return null;
        }
        return $this->fuel;
    }

    public function  getCostoBodegajeAttribute(){
        if($this->bodegaje <0  )
        {
            return null;
        }
        return $this->bodegaje;
    }

    public function  getCostoSeguroAttribute(){
        if($this->seguro <0  )
        {
            return null;
        }
        return $this->seguro;
    }


    /**
     * @return BelongsTo
     */
    public function historial()
    {

        return $this->hasMany(GuiaHistorial::class,  'blno', 'BLNo');

    }


    /**
     * @return BelongsTo
     */
    public function warehouseReceipts()
    {

        return $this->hasMany(ReciboBodega::class,  'bl_id', 'BLNo');

    }



    /**
     * @return BelongsTo
     */
    public function facturas()
    {

        return $this->hasManyThrough(Factura::class, FacturaGuia::class,'BLNo' ,'id','BLNo','factura_id');

    }
    

    /**
     * @return boolean
     */

    public function getPagadoAttribute(): Bool
    {

        $facturas=$this->facturas();

        if($facturas->count()==0){
            return false;
        }

        foreach ($facturas->get() as $factura){
            if($factura->pagado!=1){
                return false;
            }
        }
        return true;

    }


    public function getSolicitoEfectivoAttribute(): Bool
    {

        $facturas=$this->facturas();

        if($facturas->count()==0){
            return false;
        }

        foreach ($facturas->get() as $factura){
            if($factura->desea_pagar_efectivo!=1){
                return false;
            }
        }
        return true;

    }



}
