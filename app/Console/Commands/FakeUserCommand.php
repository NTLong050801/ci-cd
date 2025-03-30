<?php

namespace App\Console\Commands;

use App\Jobs\CreateFakeUserJob;
use Illuminate\Console\Command;

class FakeUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new CreateFakeUserJob());
        $this->info('Đã dispatch job tạo user giả.');
    }
}
