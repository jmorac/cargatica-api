<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ReciboBodega extends Model
{

    use Eloquence, Mappable;

    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $maps = [
        'notas_destino' => 'notasdestino',
        'total_peso' => 'totalpeso',
        'total_volumen' => 'totalvolumen',
        'total_cu' => 'totalCU',
        'total_items' => 'totalitems',
        'tipo_embarque' => 'tipoembarque',
        'total_paletas' => 'totalpaletas',
        'fecha_autorizado_cliente' => 'autorizadoxcliente',
        'consignatario_final' => 'consignatariofinal',
        'id_ext' => 'idext',
        'notas_usa' => 'notasusa',
        'notas_cr' => 'notasCR',
         'fecha_wh' => 'fechaWH',
        'nota_cliente' => 'notacliente',
        'fecha_autorizacion_cliente' => 'fechaclienteaut',
        'valor_declarado' =>'Valor_Declarado',
        'llego_cr' =>'llegoaCR',
        'adjunto_factura_cliente' => 'cantidadfotoscliente',
        'adjuntos_factura_servicio_cliente' => 'cantidadfotos',
        'tiene_foto' => 'WHfoto',
        'num_guia' => 'bl_id'

    ];

    protected $appends = [
        'notas_destino',
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
        if($this->WHfoto==0){
            return "";
        }
        return env('WH_IMG_URL')."/".$this->idext."_canvas.png";
    }

}
