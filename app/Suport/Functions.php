<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\{Storage};

function uploadBase64ImageToS3(string $base64Image, string $directory): string
{
    // Extrair a string base64 do data URI
    list($type, $base64Image) = explode(';', $base64Image);
    list(, $base64Image)      = explode(',', $base64Image);

    // Decodificar a string base64
    $image = base64_decode($base64Image);

    // Gerar o caminho do arquivo
    $filePath = $directory . '/' . time() . '.png';

    // Armazenar a imagem no bucket S3
    Storage::disk('s3')->put($filePath, $image, 'public');

    return $filePath;
}

function uploadUpdateBase64ImageToS3($param, $model, $directory): string
{
    if (!empty($param) && $param != Storage::disk('s3')->url($model)) {
        deleteImageFromS3($model);
        $retornoFile = uploadBase64ImageToS3($param, $directory);
    } elseif (empty($param)) {
        $retornoFile = '';
    } else {
        $retornoFile = $model;
    }

    return $retornoFile;
}

function deleteImageFromS3(string $filePath): void
{
    Storage::disk('s3')->delete($filePath);
}

function formatarCpf(string $cpf): string
{
    return preg_replace('/[^0-9]/', '', $cpf);
}

function formatDate(string $date): string
{
    return Carbon::parse($date)->isoFormat('DD [de] MMMM, YYYY');
}

function formatarDataString(string $data): string
{
    // Criar uma instância Carbon a partir da data fornecida
    $carbonDate = Carbon::parse($data);

    // Formatar a data
    return $carbonDate->translatedFormat('d \d\e F \d\e Y');
}

function formatarCnpj(string $cnpj): string
{
    return preg_replace('/[^0-9]/', '', $cnpj);
}

function formatarTelefone(string $telefone): string
{
    return preg_replace('/[^0-9]/', '', $telefone);
}

function formatarCep(string $cep): string
{
    return preg_replace('/[^0-9]/', '', $cep);
}

function formatarFrete(float $vlrFrete): float
{
    return $vlrFrete * 100;
}

function extrairAtributo(string $descricao, string $atributo): ?string
{
    if (preg_match("/{$atributo}: (\S+)/", $descricao, $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Separates a phone number into DDD and number components.
 *
 * @param string $telefone The phone number.
 * @return array<string, string> An associative array with 'ddd' and 'numero' keys.
 */
function separarDDDTelefone(string $telefone): array
{
    $ddd    = substr($telefone, 0, 2);
    $numero = substr($telefone, 2);

    return [
        'ddd'    => $ddd,
        'numero' => $numero,
    ];
}
