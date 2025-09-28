<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;


class Cliente extends Model {

	use Eloquence, Mappable;


	protected $primaryKey = 'id';
	public    $timestamps = false;

	protected $maps = [
		'notificar_email'   => 'notificar',
	];

    protected $fillable = [
        // other fillable fields,

    ];

	protected $appends = [
		'notificar_email'  ,

	];


	protected $hidden = [
         'nombre',
		'tipoenvio',
		'diascredito',
		'compania',
		'iscasillero',
		'compania_id',
		'contrasena',
        'consignatariofinal',
        'casilleroOLD_DELETE',
        'tipoenvio',
        'forgot_pass_token',
        'salt',
        'alert_WH',
        'omitir_nombre_factura',
        'pin'

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
	protected $table = 'CT_Clientes';


    public function oficina()
    {

        return $this->belongsTo(Oficina::class, 'default_oficina_id', 'id');

    }



}