<?php

namespace App\Console\Commands;

use App\Http\Controllers\ReservasController;
use Illuminate\Console\Command;

class CancelarReservasVencidas extends Command
{
    protected $signature = 'reservas:cancelar-vencidas';
    protected $description = 'Cancela reservas pendientes que superaron cinco minutos de tolerancia';

    public function handle(): int
    {
        $this->info(ReservasController::cancelarReservasVencidas() . ' reserva(s) cancelada(s).');
        return self::SUCCESS;
    }
}
