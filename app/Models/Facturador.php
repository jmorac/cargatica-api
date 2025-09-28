<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Facturador extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'id';
	public    $timestamps = false;

	protected $maps = [

	];

	protected $appends = [


	];


	protected $visible = [

		'id',
		'nombre',
		'nombre_corto',
        'nombre_para_cliente',
		'cedula',
		'logo',
		'direccion',
		'direccion2',
		'telefono',
		'email',
        'puede_usar_sinpe',
        'linea_extra1',
        'linea_extra2',
        'linea_extra3',
        'numero_sinpe',
        'whatsapp'

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
	protected $table = 'CT_Facturadores';



}
