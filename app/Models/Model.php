<?php

namespace App\Models;

class Model extends \Illuminate\Database\Eloquent\Model {


	protected $connection = 'mysql';

    /**
     * @param       $query
     * @param array $parameters
     *
     * @return mixed
     */
    public function scopeWhereLike( $query, array $parameters = [] )
    {
        foreach ( $parameters as $key => $value ) {
            $value = str_replace( [ '%*', '*%' ], [ '', '' ], "%$value%" );
            $query->where( $key, 'like', "%$value%" );
        }

        return $query;
    }

    /**
     * @param       $query
     * @param array $parameters
     *
     * @return mixed
     */
    public function scopeOrWhereLike( $query, array $parameters = [] )
    {
        if ( ! empty( $parameters ) ) {
            $query->where( function ( $query ) use ( $parameters ) {
                foreach ( $parameters as $key => $value ) {
                    $value = str_replace( [ '%*', '*%' ], [ '', '' ], "%$value%" );
                    $query->orWhere( $key, 'like', $value );
                }
            } );
        }

        return $query;
    }
}