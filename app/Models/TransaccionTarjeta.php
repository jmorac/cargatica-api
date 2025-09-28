<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TransaccionTarjeta extends Model {

	use Eloquence, Mappable;

	protected $primaryKey = 'id';

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

    protected $hidden = [
        'sent',
        'esdolares',
        'id_ext',
        'cliente_id',
        'received'
    ];


    /**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'credit_card_transaction';

}
