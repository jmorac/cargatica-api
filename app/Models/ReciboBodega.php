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

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'CT_Productos';

    /**
     * @return BelongsTo
     */
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
        if ($this->WHfoto == 0) {
            return "";
        }
        return env('WH_IMG_URL') . "/" . $this->idext . "_canvas.png";
    }

    public function getNotasDestinoAttribute()
    {
        return $this->notasdestino;
    }

    public function getTotalPesoAttribute()
    {
        return $this->totalpeso;
    }

    public function getTotalVolumenAttribute()
    {
        return $this->totalvolumen;
    }

    public function getTotalCuAttribute()
    {
        return $this->totalCU;
    }

    public function getTotalItemsAttribute()
    {
        return $this->totalitems;
    }

    public function getTipoEmbarqueAttribute()
    {
        return $this->tipoembarque;
    }

    public function getTotalPaletasAttribute()
    {
        return $this->totalpaletas;
    }

    public function getFechaAutorizadoClienteAttribute()
    {
        return $this->autorizadoxcliente;
    }

    public function getConsignatarioFinalAttribute()
    {
        return $this->consignatariofinal;
    }

    public function getIdExtAttribute()
    {
        return $this->idext;
    }

    public function getNotasUsaAttribute()
    {
        return $this->notasusa;
    }

    public function getNotasCrAttribute()
    {
        return $this->notasCR;
    }

    public function getFechaWhAttribute()
    {
        return $this->fechaWH;
    }

    public function getNotaClienteAttribute()
    {
        return $this->notacliente;
    }

    public function getFechaAutorizacionClienteAttribute()
    {
        return $this->fechaclienteaut;
    }

    public function getValorDeclaradoAttribute()
    {
        return $this->Valor_Declarado;
    }

    public function getLlegoCrAttribute()
    {
        return $this->llegoaCR;
    }

    public function getAdjuntoFacturaClienteAttribute()
    {
        return $this->cantidadfotoscliente;
    }

    public function getTieneFotoAttribute()
    {
        return $this->WHfoto;
    }

    public function getNumGuiaAttribute()
    {
        return $this->bl_id;
    }
}
