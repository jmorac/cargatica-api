<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoEnvios extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'id';
	public    $timestamps = false;

	protected $maps = [

	];

	protected $appends = [


	];


	protected $visible = [
		'id',
		'envio',
		'status'
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
	protected $table = 'CT_tipo_envio';

}