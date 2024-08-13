<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

function uploadBase64ImageToS3($base64Image, $directory)
{
    // Extrair a string base64 do data URI
    list($type, $base64Image) = explode(';', $base64Image);
    list(, $base64Image) = explode(',', $base64Image);

    // Decodificar a string base64
    $image = base64_decode($base64Image);

    // Gerar o caminho do arquivo
    $filePath = $directory . '/' . time() . '.png';

    // Armazenar a imagem no bucket S3
    Storage::disk('s3')->put($filePath, $image, 'public');

    return $filePath;
}


function deleteImageFromS3($filePath)
{
    Storage::disk('s3')->delete($filePath);
}

function formatarCpf($cpf)
{
    return preg_replace('/[^0-9]/', '', $cpf);
}

function formatDate($date)
{
    return Carbon::parse($date)->isoFormat('DD [de] MMMM, YYYY');
}

function formatarDataString($data)
{
    // Criar uma instância Carbon a partir da data fornecida
    $carbonDate = Carbon::parse($data);
    
    // Formatar a data
    return $carbonDate->translatedFormat('d \d\e F \d\e Y');
}

function formatarCnpj($cnpj)
{
    return preg_replace('/[^0-9]/', '', $cnpj);
}

function formatarTelefone($telefone)
{
    return preg_replace('/[^0-9]/', '', $telefone);
}

function formatarCep($cep)
{
    return str_replace('-', '', $cep);
}

function formatarFrete($vlrFrete)
{
    return $vlrFrete * 100;
}

function extrairAtributo($descricao, $atributo)
{
    if (preg_match("/{$atributo}: (\S+)/", $descricao, $matches)) {
        return $matches[1];
    }

    return null;
}

function separarDDDTelefone($telefone)
{
    $ddd = substr($telefone, 0, 2);
    $numero = substr($telefone, 2);

    return [
        'ddd' => $ddd,
        'numero' => $numero
    ];
}
