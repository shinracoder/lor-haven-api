<?php

namespace App\Console\Commands;

use App\Constants\Locale;
use App\Models\Version;
use Illuminate\Support\Facades\DB;
use League\Flysystem\FileNotFoundException;

class RuneterraUpdater extends AbstractRuneterraUpdater
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runeterra:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest runeterra updates';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function handle(): void
    {
        $latestVersionNumber = $this->getLatestVersionNumber();
        $this->downloadCore(
            'latest',
            [Locale::ENGLISH_US]
        );
        DB::table('versions')->update(['latest' => false]);
        DB::table('versions')->where([
          'version' => $latestVersionNumber,
        ])->update(['latest' => true]);
    }
}
