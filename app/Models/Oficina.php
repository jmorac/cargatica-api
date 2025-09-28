<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Oficina extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'id';
	public    $timestamps = false;

	protected $maps = [

	];

	protected $appends = [

	];

	protected $hidden = [

	];


    public function getDireccionAttribute($value)
    {
       return str_replace([ "\n", "\t", "\r"], ' ', $value);
    }


    public function getHorarioAttribute($value)
    {
        return str_replace([ "\n", "\t", "\r"], ' ', $value);
    }

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
	protected $table = 'CT_oficina';

}
