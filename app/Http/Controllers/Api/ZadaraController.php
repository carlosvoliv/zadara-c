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
            // Devolve o erro na tela / Postman
            return response()->json([
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 500);
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
}
