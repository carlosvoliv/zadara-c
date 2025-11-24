<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ZipHandler
{
    private string $zipPath;
    private array  $extracted = [];

    public function __construct(string $zipPath)
    {
        $this->zipPath = $zipPath;
    }

    /** Descompacta para memória (não salva disco) */
    public function extract(): void
    {
        $zip = new ZipArchive;
        if ($zip->open($this->zipPath) !== true) {
            throw new \Exception('ZIP inválido');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '/')) continue; // pasta
            $this->extracted[] = [
                'zipPath' => $name,
                'content' => $zip->getFromIndex($i),
            ];
        }
        $zip->close();

        if (empty($this->extracted)) {
            throw new \Exception('ZIP vazio');
        }
        \Log::info('EXTRACT FINAL', ['total_files' => count($this->extracted)]);
    }

    /** Valida nome do arquivo (último nível) – DESATIVADO */
    public function validateNames(): void
    {
        // vazio, não faz nada
    }

    /** Sobe cada arquivo para Zadara na estrutura fixa */
    public function uploadToZadara($user): array
    {
        $disk   = Storage::disk('zadara');
        $prefix = env('ZADARA_PREFIX', 'FIDC_AKRK');
        $lista  = [];

        foreach ($this->extracted as $file) {
            \Log::info('DENTRO DO FOREACH', ['zipPath' => $file['zipPath']]);

            $parts = $this->parseZipPath($file['zipPath']);
            $nomePasta = $parts['folder'] ?? 'sem-pasta';
            $nomeArq   = $parts['file'];
            $ccbControle = pathinfo($nomeArq, PATHINFO_FILENAME);

            $pathZadara = "$prefix/$nomePasta/$ccbControle/" . Str::uuid() . '_' . now()->timestamp . '_' . $nomeArq;

            // Upload com stream (sem carregar tudo na RAM)
            // ---- upload Flysystem-3 / S3-compatible ----
            $tempPath = tempnam(sys_get_temp_dir(), 'zad_');
            file_put_contents($tempPath, $file['content']);
            $stream = fopen($tempPath, 'r');
            $disk->put($pathZadara, $stream);   // <<<<< sem "Stream" no nome
            fclose($stream);
            unlink($tempPath);
            unset($file['content']);
            // ------------------------------------------
            // ----------------------------

            $lista[] = [
                'original' => $file['zipPath'],
                'path'     => $pathZadara,
                'url'      => $disk->temporaryUrl($pathZadara, now()->addMinutes(15)),
            ];
        }
        return $lista;
    }

    /** Limpa temporários */
    public function cleanTemp(): void
    {
        $this->extracted = [];
    }

    /* Auxiliar */
    private function parseZipPath(string $zipPath): array
    {
        $parts = explode('/', $zipPath);
        $file  = array_pop($parts);
        $folder = $parts ? implode('/', $parts) : null;
        return ['folder' => $folder, 'file' => $file];
    }
}
