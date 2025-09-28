<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @param $result
     *
     * @return JsonResponse
     */
    protected function createOkResponse( $result ,$status='ok', $error = null): JsonResponse
    {
        return response()->json( [
            'status' => $status,
            'error'  => $error,
            'data'   => $result
        ] );
    }


    /**
     * @param $validator
     *
     * @return JsonResponse
     */
    protected function createValidatorErrorResponse( $validator ): JsonResponse
    {
        $errors=[];
        foreach ($validator->errors()->getMessages()  as $key=> $value){
            $errors[]=[$key=>$value];
        }
        return response()->json( [
            'status' => 'Error',
            'error'  => 'Informacion Invalida:'.$validator->errors()->first(),
            'data'   => $validator->errors(),
            'data2'   =>  $errors,

        ]  );

    }

    /**
     * @param $errors
     * @param $data
     *
     * @return JsonResponse
     */
    protected function createErrorResponse( $errors , $data ): JsonResponse
    {
        return response()->json( [
            'status' => 'Error',
            'error'  => $errors,
            'data'   => $data
        ]);

    }

    /**
     * @param $r
     * @param $validateArray
     *
     * @return \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
     */
    protected function validateRequest( Request $r, $validateArray )
    {
        $validator = Validator::make( $r->all(), $validateArray );

        return $validator;
    }
}
