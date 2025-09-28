<?php

namespace App\Util;


use Exception;
use App\Models\Imagenes;
use App\Models\ReciboBodega;

use Illuminate\Support\Facades\Log;


class ImageHandler
{

    function saveImage($r, $reciboId, $trackingId=0 )
    {


        $adjuntoId = $r->get('adjunto_id');
        $descripcion = $r->get('descripcion');
        $padre = null;

        if (!$adjuntoId) {
            if (!$r->hasFile('adjunto')) {
                Log::error('File not found for ' . $reciboId);
                throw new \Exception('File not found for ' . $reciboId);
            }
            $tipo = "05";
            $idImg = $reciboId;
            $random = rand();
            $uniquefile = $tipo . "_" . $reciboId . "_" . $trackingId . "_" . $random . "_" . $idImg;
            $allowedExtensions = explode(',', strtolower(env('ALLOWED_EXT')));
            $original_filename = $r->file('adjunto')->getClientOriginalName();
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
            if ($fileExt == 'pdf' && false) {
                $im = new \Imagick();
                $im->setResolution(300, 300);     //set the resolution of the resulting jpg
                try {
                    if ($im->readImage($destination_path . DIRECTORY_SEPARATOR . $image)) {
                        $im->writeImages($_SERVER['DOCUMENT_ROOT'] . '/adjuntos/cache_' . $uniquefile . '.jpg', true);
                        $paginas = $im->getNumberImages();
                    } else {
                        Log::error('No encontro:' . $_SERVER['DOCUMENT_ROOT'] . 'adjuntos/cache_' . $uniquefile . '.jpg');
                        throw new Exception('No encontro:' . $_SERVER['DOCUMENT_ROOT'] . 'adjuntos/cache_' . $uniquefile . '.jpg');
                    }
                } catch (Exception $e) {
                    unlink($destination_path . DIRECTORY_SEPARATOR .  $image);
                    Log::error('El formato del pdf no es valido. Favor este seguro que el pdf este en portait o que la medida del ancho sea inferior al largo. Tambien puede intentar subiendo una imagen'.$e->getMessage());
                    throw new \Exception('El formato del pdf no es valido. Favor este seguro que el pdf este en portait o que la medida del ancho sea inferior al largo. Tambien puede intentar subiendo una imagen'.$e->getMessage().' Image is in ['.$destination_path . DIRECTORY_SEPARATOR . $image.']' );
                }
            }

            $nombre = 'adjuntos/' . $uniquefile . '.' . $fileExt;
        } else {

            $imagen = Imagenes::query()->where('id', $adjuntoId)->firstOrFail();
            $idImg = $imagen->idWH;
            $nombre = $imagen->nombre;
            $descripcion = $imagen->descripcion;
            $paginas = $imagen->paginas;
            $fileExt = $imagen->fileext;
            $tipo = $imagen->tipo;
            $padre = $imagen->id;

        }

        if(!file_exists($destination_path.$image)){
            Log::error('File does not exist:'.$destination_path.$image);
        }

        if ($reciboId) {

            ReciboBodega::query()->findOrFail($reciboId)->increment('cantidadfotoscliente', 1);
            ReciboBodega::query()->findOrFail($reciboId)->update(['factura_cliente'=>substr($descripcion, 0, 100)]);
        }

        $data= [
            'idWH' => $reciboId,
            'idtracking' => $trackingId,
            'idimg' => $idImg,
            'padre' => $padre,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'paginas' => $paginas,
            'fileext' => $fileExt,
            'fecha' => time(),
            'tipo' => $tipo
        ];


        return Imagenes::create( $data );
    }

}