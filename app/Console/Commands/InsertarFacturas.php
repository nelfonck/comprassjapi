<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FacturasService;

class InsertarFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:insertar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insertar facturas';

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
    public function handle(FacturasService $service)
    {
    try {

        $service->guardarFacturas();
        return Command::SUCCESS;

    } catch (\Throwable $e) {

        $this->error($e->getMessage());
        return Command::FAILURE;

        }
    }
}
