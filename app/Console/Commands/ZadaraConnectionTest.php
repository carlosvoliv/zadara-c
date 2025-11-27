<?php

namespace App\Console\Commands;
// adicionar outro comentário aqui pra aparecer no git
// vou colocar outro aqui só pra ver o que acontecere
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ZadaraConnectionTest extends Command
{
    protected $signature = 'zadara:test';
    protected $description = 'Testa conexão com Zadara Object Storage';

    public function handle()
    {
        $this->line('🔍 Iniciando teste...');

        try {
            $disk = Storage::disk('zadara');
            $testFile = env('ZADARA_PREFIX') . '/teste-conexao/' . now()->format('Y-m-d_H-i-s') . '.txt';
            $content = 'Teste Laravel - ' . now();

            $this->line("📤 Enviando: $testFile");

            // Força throw para ver erro real
            config(['filesystems.disks.zadara.throw' => true]);

            $disk->put($testFile, $content);

            $this->info("✅ Enviado com sucesso: $testFile");
            $url = $disk->temporaryUrl($testFile, now()->addMinutes(5));
            $this->line("🔗 URL temporária: $url");

        } catch (\Aws\S3\Exception\S3Exception $e) {
            $this->error('❌ AWS Erro: ' . $e->getAwsErrorMessage());
            $this->line('Código: ' . $e->getAwsErrorCode());
        } catch (\Throwable $e) {
            $this->error('❌ Erro geral: ' . $e->getMessage());
            $this->line('Arquivo: ' . $e->getFile() . ':' . $e->getLine());
        }

        $this->line('🏁 Fim do teste.');
    }
}

