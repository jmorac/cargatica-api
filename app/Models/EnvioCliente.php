<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class EnvioCliente extends Model
{

    use Eloquence, Mappable;

    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $maps = [

    ];

    protected $appends = [

    ];

    protected $hidden = [
      'cliente_id','user_id','direccion_id'
    ];

    protected $with = [

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
    protected $table = 'CT_cliente_envio';


}
