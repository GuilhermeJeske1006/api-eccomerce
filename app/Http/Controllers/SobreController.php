<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeSobreRequest;
use App\Http\Resources\SobreResource;
use App\Models\Sobre;
use Illuminate\Support\Facades\{Storage};

class SobreController extends Controller
{
    public function show(int $empresa_id)
    {
        $sobre = Sobre::where('empresa_id', $empresa_id)->first();

        if(isset($sobre['foto'])) {
            $sobre['foto'] = Storage::disk('s3')->url($sobre['foto']);
        }

        return new SobreResource($sobre);
    }

    public function update(storeSobreRequest $request, int $empresa_id)
    {
        try {
            $sobre                 = Sobre::where('empresa_id', $empresa_id)->first();
            $request['empresa_id'] = $empresa_id;

            if(!$sobre) {

                if (!empty($request->foto)) {
                    $request['foto'] = uploadBase64ImageToS3($request['foto'], 'sobre');
                } else {
                    $request['foto'] = '';
                }

                $sobre = Sobre::create($request->all());

                return response()->json([
                    'message' => 'Sobre atualizado com sucesso',
                    'data'    => new SobreResource($sobre),
                ], 200);

            } else {

                if (!empty($request->foto) && $request->foto != Storage::disk('s3')->url($sobre->foto)) {
                    deleteImageFromS3($sobre->foto);
                    $request['foto'] = uploadBase64ImageToS3($request['foto'], 'sobre');
                } elseif (empty($request->foto)) {
                    $request['foto'] = '';
                } else {
                    $request['foto'] = $sobre->foto;
                }

                $sobre->update($request->all());

                return response()->json([
                    'message' => 'Sobre atualizado com sucesso',
                    'data'    => new SobreResource($sobre),
                ], 200);

            }

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar sobre',
                'erro'    => $th->getMessage(),
            ], 500);
        }
    }
}
