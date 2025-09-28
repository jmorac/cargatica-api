<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class BannerController extends Controller {

	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function getAllBanners( Request $r)
	{

		$r=Banner::all();
		return $this->createOkResponse( $r);
	}


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getBanners( Request $r)
    {

        $r=Banner::query()->where('status',1)->get();
        return $this->createOkResponse( $r);
    }




	public function addBanner( Request $r){

        $usuario_id = Auth::user()->usuario_id;
        $reciboId =0;

        if (!$r->hasFile('banner_file')) {
            Log::error('File not found for ' . $reciboId);

            throw new \Exception('File not found for ' . $reciboId);

        }

        $tipo = "05";
        $idImg = time();
        $uniquefile = $tipo . "_" . $reciboId . "_" . $idImg;

        $allowedExtensions = explode(',', strtolower(env('ALLOWED_EXT')));
        $original_filename = $r->file('banner_file')->getClientOriginalName();
        $original_filename_arr = explode('.', $original_filename);
        $fileExt = strtolower(end($original_filename_arr));

        if (!in_array($fileExt, $allowedExtensions)) {
            Log::error('Extension [' . $fileExt . ']is not valid' . $reciboId . ' must be' . strtolower(env('ALLOWED_EXT')));

            throw new \Exception('Extension [' . $fileExt . ']is not valid' . $reciboId . ' must be' . strtolower(env('ALLOWED_EXT')));


        }

        $destination_path = getcwd() . DIRECTORY_SEPARATOR . 'adjuntos';
        $image = $uniquefile . '.' . $fileExt;

        if (!$r->file('adjunto')->move($destination_path, $image)) {
            Log::error('Could not move file destination[' . $destination_path . '] image [' . $image . ']' . $reciboId);

            throw new \Exception('Could not move file destination[' . $destination_path . '] image [' . $image . ']' . $reciboId);

        }

        $paginas = 0;


        ReciboBodega::query()->findOrFail($reciboId)->update(['cantidadfotoscliente' => 1]);

        return Imagenes::create(['idWH' => $reciboId, 'idimg' => $idImg,
            'nombre' => 'adjuntos/' . $uniquefile . '.' . $fileExt,
            'descripcion' => $r->get('descripcion'),
            'paginas' => $paginas, 'fileext' => $fileExt,
            'fecha' => $idImg, 'tipo' => $tipo]);






    }


}



