<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ChangeLog
 *
 * @property int                             $id
 * @property string                          $short_description
 * @property string|null                     $description
 * @property string|null                     $object_type
 * @property string|null                     $before
 * @property string|null                     $after
 * @property string                          $operator
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereAfter( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereBefore( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereDescription( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereObjectType( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereOperator( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereShortDescription( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChangeLog whereUpdatedAt( $value )
 * @mixin \Eloquent
 */
class ChangeLog extends Model {

    /**
     * @var array
     */
    protected $guarded = [ 'id' ];

    /**
     * @param string      $shortDescription
     * @param string      $operator
     * @param string|null $description
     * @param string|null $objectType
     * @param null        $before
     * @param null        $after
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model|ChangeLog
     */
    public static function log( string $shortDescription, string $operator, string $description = null, string $objectType = null, $before = null, $after = null )
    {
        return ChangeLog::query()
                        ->create( [
                            'short_description' => $shortDescription,
                            'description'       => $description,
                            'object_type'       => $objectType,
                            'before'            => json_encode( $before ),
                            'after'             => json_encode( $after ),
                            'operator'          => $operator,
                        ] );
    }
}
