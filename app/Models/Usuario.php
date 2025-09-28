<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Usuario extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'id';
	public    $timestamps = false;

	protected $maps = [

	];

	protected $appends = [

	];


	protected $hidden = [
       'contrasena'

	];

	protected $visible = [
     'nombre','id','compania_id'

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
	protected $table = 'CT_Usuarios';


    public function compania()
    {

        $compania= $this->belongsTo(Compania::class, 'compania_id', 'id');

        return $compania;

    }

}