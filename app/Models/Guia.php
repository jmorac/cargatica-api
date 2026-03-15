<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    protected $primaryKey = 'BLNo';
    public $timestamps = false;

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
        /* 'clienteid', */
        'iscasillero',
        'fuel',
        'bodegaje',
        'seguro',
    ];

    protected $with = ['agencia_estado'];

    protected $guarded = [
        'BLNo'
    ];

    protected $table = 'CT_SHIP';

    public function agencia_estado()
    {
        return $this->belongsTo(StatusAgencia::class, 'agencia_status', 'id');
    }

    public function getIdAttribute()
    {
        return $this->attributes['BLNo'] ?? null;
    }

    public function getNumeroAttribute()
    {
        return $this->attributes['BLNoVisual'] ?? null;
    }

    public function getExtIdAttribute()
    {
        return $this->attributes['idextguia'] ?? null;
    }

    public function getExporterAttribute()
    {
        return $this->attributes['Exporter'] ?? null;
    }

    public function getConsignatarioAttribute()
    {
        return $this->attributes['Consignee'] ?? null;
    }

    public function getNumeroVueloAttribute()
    {
        return $this->attributes['flightnumber'] ?? null;
    }

    public function getFacturaIdAttribute()
    {
        return $this->attributes['facturaid'] ?? null;
    }

    public function getAppStatusAttribute()
    {
        $envioClienteId = (int) ($this->attributes['envio_cliente_id'] ?? 0);
        $entregadoId = (int) ($this->attributes['entregado_id'] ?? 0);
        $fechaPagado = (int) ($this->attributes['fecha_pagado'] ?? 0);
        $fechaFactura = (int) ($this->attributes['fecha_factura'] ?? 0);
        $deseaPagarEfectivo = (int) ($this->attributes['desea_pagar_efectivo'] ?? 0);

        if ($envioClienteId > 0 && $entregadoId === 0) {
            return 'TRANSITO';
        }

        if ($fechaPagado === 0 && $fechaFactura > 0 && $deseaPagarEfectivo === 0) {
            return 'PENDIENTE_PAGAR';
        }

        if ($fechaPagado > 0 || $deseaPagarEfectivo > 0) {
            return 'PAGADOOCREDITO';
        }

        if ($fechaPagado > 0) {
            return 'PAGADO';
        }

        if ($envioClienteId > 0 && $entregadoId === 0) {
            return 'LISTO_ENTREGAR';
        }

        if ($entregadoId > 0) {
            return 'ENTREGADO';
        }

        return 'APROBADAS';
    }

    public function getPiezasAttribute()
    {
        $repackage = (int) ($this->attributes['repackage'] ?? 0);

        if ($repackage === 1) {
            return $this->attributes['viewpcs'] ?? null;
        }

        return $this->attributes['pcs'] ?? null;
    }

    public function getPesoAttribute()
    {
        $repackage = (int) ($this->attributes['repackage'] ?? 0);

        if ($repackage === 1) {
            return $this->attributes['viewweight'] ?? null;
        }

        return $this->attributes['weight'] ?? null;
    }

    public function getPesoCubicoAttribute()
    {
        $repackage = (int) ($this->attributes['repackage'] ?? 0);

        if ($repackage === 1) {
            return $this->attributes['viewcuft'] ?? null;
        }

        return $this->attributes['cuft'] ?? null;
    }

    public function getVolAttribute()
    {
        $repackage = (int) ($this->attributes['repackage'] ?? 0);

        if ($repackage === 1) {
            return $this->attributes['viewvolumen'] ?? null;
        }

        return $this->attributes['volumen'] ?? null;
    }

    public function getTieneFacturaAttribute()
    {
        return (int) ($this->attributes['facturaimportadora'] ?? 0) > 0
            || (int) ($this->attributes['facturacasillero'] ?? 0) > 0
            || (int) ($this->attributes['facturareg'] ?? 0) > 0;
    }

    public function getCombustibleAttribute()
    {
        $fuel = $this->attributes['fuel'] ?? null;

        if ($fuel !== null && $fuel < 0) {
            return null;
        }

        return $fuel;
    }

    public function getCostoBodegajeAttribute()
    {
        $bodegaje = $this->attributes['bodegaje'] ?? null;

        if ($bodegaje !== null && $bodegaje < 0) {
            return null;
        }

        return $bodegaje;
    }

    public function getCostoSeguroAttribute()
    {
        $seguro = $this->attributes['seguro'] ?? null;

        if ($seguro !== null && $seguro < 0) {
            return null;
        }

        return $seguro;
    }

    public function historial()
    {
        return $this->hasMany(GuiaHistorial::class, 'blno', 'BLNo');
    }

    public function warehouseReceipts()
    {
        return $this->hasMany(ReciboBodega::class, 'bl_id', 'BLNo');
    }

    public function facturas()
    {
        return $this->hasManyThrough(
            Factura::class,
            FacturaGuia::class,
            'BLNo',
            'id',
            'BLNo',
            'factura_id'
        );
    }

    public function getPagadoAttribute(): bool
    {
        $facturas = $this->relationLoaded('facturas')
            ? $this->facturas
            : $this->facturas()->get();

        if ($facturas->isEmpty()) {
            return false;
        }

        foreach ($facturas as $factura) {
            if ((int) $factura->pagado !== 1) {
                return false;
            }
        }

        return true;
    }

    public function getSolicitoEfectivoAttribute(): bool
    {
        $facturas = $this->relationLoaded('facturas')
            ? $this->facturas
            : $this->facturas()->get();

        if ($facturas->isEmpty()) {
            return false;
        }

        foreach ($facturas as $factura) {
            if ((int) $factura->desea_pagar_efectivo !== 1) {
                return false;
            }
        }

        return true;
    }
}
