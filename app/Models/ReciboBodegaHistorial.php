<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ReciboBodegaHistorial extends Model
{

    use Eloquence, Mappable;

    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $maps = [


    ];

    protected $appends = [

    ];

    protected $hidden = [

    ];

    protected $with = [

    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [

    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'CT_Prod_Historial';


}
