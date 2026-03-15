<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboBodega extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $appends = [
        'notas_destino',
        'total_peso',
        'total_volumen',
        'total_cu',
        'total_items',
        'total_paletas',
        'tipo_embarque',
        'fecha_autorizado_cliente',
        'consignatario_final',
        'id_ext',
        'notas_usa',
        'notas_cr',
        'fecha_wh',
        'nota_cliente',
        'fecha_autorizacion_cliente',
        'valor_declarado',
        'llego_cr',
        'adjunto_factura_cliente',
        'tiene_foto',
        'url_img',
        'num_guia'
    ];

    protected $hidden = [
        'fecha',
        'wh_rack',
        'nombrecliente',
        /* 'clientes_id', */
        'compania_id',
        'fechaauto',
        'checked',
        'confirmacionimpresion',
        'last_bl_id',
        'sin_cliente',
        'notasdestino',
        'manifestoid',
        'creadoenCR',
        'carrier',
        'vistopor',
        'fechavisto',
        'llamar',
        'descripcion2',
        'bl_id',
        'factura_cr',
        'factura_cliente',
        'otrosdocuments_scan',
        'factura',
        'checked_by',
        'WHfoto',
        'WHfotoRecibo',
        'notasdestino',
        'totalpeso',
        'totalvolumen',
        'totalCU',
        'totalitems',
        'tipoembarque',
        'autorizadoxcliente',
        'totalpaletas',
        'consignatariofinal',
        'idext',
        'notasusa',
        'notasCR',
        'autorizadoenviar',
        'fechaWH',
        'recibidopor',
        'notacliente',
        'iscasillero',
        'fechaclienteaut',
        'Valor_Declarado',
        'llegoaCR',
        'cantidadfotos',
        'WHfoto'
    ];

    protected $with = [
        'autorizado'
    ];

    protected $guarded = [
        'id'
    ];

    protected $table = 'CT_Productos';

    public function guia()
    {
        return $this->belongsTo(Guia::class, 'bl_id', 'BLNo');
    }

    public function autorizado()
    {
        return $this->belongsTo(TipoEnvios::class, 'autorizadoenviar', 'id');
    }

    public function getUrlImgAttribute($value)
    {
        if ((int) ($this->attributes['WHfoto'] ?? 0) === 0) {
            return '';
        }

        return env('WH_IMG_URL') . '/' . ($this->attributes['idext'] ?? '') . '_canvas.png';
    }

    public function getNotasDestinoAttribute()
    {
        return $this->attributes['notasdestino'] ?? null;
    }

    public function getTotalPesoAttribute()
    {
        return $this->attributes['totalpeso'] ?? null;
    }

    public function getTotalVolumenAttribute()
    {
        return $this->attributes['totalvolumen'] ?? null;
    }

    public function getTotalCuAttribute()
    {
        return $this->attributes['totalCU'] ?? null;
    }

    public function getTotalItemsAttribute()
    {
        return $this->attributes['totalitems'] ?? null;
    }

    public function getTipoEmbarqueAttribute()
    {
        return $this->attributes['tipoembarque'] ?? null;
    }

    public function getTotalPaletasAttribute()
    {
        return $this->attributes['totalpaletas'] ?? null;
    }

    public function getFechaAutorizadoClienteAttribute()
    {
        return $this->attributes['autorizadoxcliente'] ?? null;
    }

    public function getConsignatarioFinalAttribute()
    {
        return $this->attributes['consignatariofinal'] ?? null;
    }

    public function getIdExtAttribute()
    {
        return $this->attributes['idext'] ?? null;
    }

    public function getNotasUsaAttribute()
    {
        return $this->attributes['notasusa'] ?? null;
    }

    public function getNotasCrAttribute()
    {
        return $this->attributes['notasCR'] ?? null;
    }

    public function getFechaWhAttribute()
    {
        return $this->attributes['fechaWH'] ?? null;
    }

    public function getNotaClienteAttribute()
    {
        return $this->attributes['notacliente'] ?? null;
    }

    public function getFechaAutorizacionClienteAttribute()
    {
        return $this->attributes['fechaclienteaut'] ?? null;
    }

    public function getValorDeclaradoAttribute()
    {
        return $this->attributes['Valor_Declarado'] ?? null;
    }

    public function getLlegoCrAttribute()
    {
        return $this->attributes['llegoaCR'] ?? null;
    }

    public function getAdjuntoFacturaClienteAttribute()
    {
        return $this->attributes['cantidadfotoscliente'] ?? null;
    }

    public function getTieneFotoAttribute()
    {
        return $this->attributes['WHfoto'] ?? null;
    }

    public function getNumGuiaAttribute()
    {
        return $this->attributes['bl_id'] ?? null;
    }
}
