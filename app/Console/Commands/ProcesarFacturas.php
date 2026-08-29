<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FacturaVista;

class ProcesarFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:procesar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa las facturas pendientes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $facturas = FacturaVista::where('fecha_creacion', '>=', '2026-08-28')->orderBy('fecha_creacion')->get();

        $this->info('Facturas encontradas: ' . $facturas->count());

        return Command::SUCCESS;
    }
}
