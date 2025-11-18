<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TokenSanctumPostman extends Command
{
    protected $signature = 'token:postman';
    protected $description = 'Gera token Sanctum para Postman';

    public function handle()
    {
        // Token fake (não salva no banco) - apenas para teste no Postman
        $fakeToken = bin2hex(random_bytes(32));
        $this->line("Bearer Token para Postman (FAKE - sem validação de banco):");
        $this->info($fakeToken);
    }
}

