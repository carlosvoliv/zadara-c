<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ZadaraController extends Controller
{
    /** Lista tudo dentro de FIDC_AKRK/ */
    public function lista(Request $request)
    {
        \Log::info('ROTA LISTA CHAMADA');

        try {
            // Autenticação fake (sem banco) - apenas para teste
            if ($request->bearerToken() !== 'fake-token-123') {
                return response()->json(['message' => 'Token inválido'], 401);
            }

            $prefix = env('ZADARA_PREFIX', 'FIDC_AKRK') . '/';
            $disk   = Storage::disk('zadara');

            $all = collect($disk->listContents($prefix, true))
                ->where('type', 'file')
                ->map(fn($item) => [
                    'path'      => $item['path'],
                    'size'      => $item['size'] ?? 0,
                    'timestamp' => $item['timestamp'] ?? null,
                    'url'       => $disk->temporaryUrl($item['path'], now()->addMinutes(15)),
                ]);

            return response()->json($all);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getPrevious()
                ? $e->getPrevious()->getMessage()
                : $e->getMessage(),
            ], 422);
        }
    }

    /** Gera URL temporária para download */
    public function download($path)
    {
        $disk = Storage::disk('zadara');
        if (!$disk->exists($path)) {
            return response()->json(['erro' => 'Arquivo não encontrado'], 404);
        }
        return response()->json(['url' => $disk->temporaryUrl($path, now()->addMinutes(15))]);
    }

    public function uploadZip(Request $request)
    {
        \Log::info('UPLOAD INICIADO', ['file' => $request->file('zip')->getClientOriginalName()]);
        \Log::info('ARQUIVO RECEBIDO', [
            'has_file' => $request->hasFile('zip'),
            'file'     => $request->file('zip'),
            'name'     => $request->file('zip') ? $request->file('zip')->getClientOriginalName() : null,
            'size'     => $request->file('zip') ? $request->file('zip')->getSize() : null,
        ]);
        \Log::info('ANTES DO HANDLER');

        // Logs de diagnóstico
        \Log::info('MIME TYPE', ['mime' => $request->file('zip')->getMimeType()]);
        \Log::info('EXTENSÃO', ['ext' => $request->file('zip')->getClientOriginalExtension()]);
        \Log::info('ARQUIVO VÁLIDO', ['valid' => $request->file('zip')->isValid()]);

        try {
            $validator = \Validator::make($request->all(), [
                'zip' => 'required|file|mimes:zip|max:100000',
            ], [
                'zip.required' => 'Por favor, selecione um arquivo ZIP.',
                'zip.file'     => 'O arquivo deve ser um upload válido.',
                'zip.mimes'    => 'O arquivo deve ser .zip.',
                'zip.max'      => 'O arquivo não pode ultrapassar 100 MB.',
            ]);

            if ($validator->fails()) {
                \Log::error('VALIDAÇÃO FALHOU', ['errors' => $validator->errors()->all()]);
                return response()->json([
                    'message' => 'Erro de validação',
                    'errors'  => $validator->errors()->all()
                ], 422);
            }

            $zipPath = $request->file('zip')->getRealPath();
            $handler = new \App\Services\ZipHandler($zipPath);

            $handler->extract();
            // $handler->validateNames();
            $files = $handler->uploadToZadara(auth()->user());

            return $request->expectsJson()
                ? response()->json(['ok' => true, 'files' => $files])
                : view('partials.upload-result', ['files' => $files]);

        } catch (\Throwable $e) {
            \Log::error('ERRO NO UPLOAD', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
