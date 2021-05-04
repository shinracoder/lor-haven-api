<?php

namespace App\Console\Commands;

use App\Constants\Locale;
use App\Models\Version;
use League\Flysystem\FileNotFoundException;

class RuneterraVersionDownloader extends AbstractRuneterraUpdater
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runeterra:download:old_versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the all old runeterra versions';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->breakOnVersionFound = true;
        $versions = [
            '1_0_0',
            '1_1_0',
            '1_2_0',
            '1_3_0',
            '1_4_0',
            '1_5_0',
            '1_6_0',
            '1_7_0',
            '1_8_0',
            '1_9_0',
            '1_10_0',
            '1_11_0',
            '1_12_0',
            '1_13_0',
            '1_14_0',
            '1_15_0',
            '1_16_0',
            '2_0_0',
            '2_1_0',
            '2_2_0',
            '2_3_0',
            '2_4_0',
            '2_5_0',
            '2_6_0',
        ];
        foreach ($versions as $version) {
            $newVersion = Version::query()->firstWhere('version', '=', $version);
            if (!$newVersion) {
                $this->downloadCore(
                    $version,
                    [Locale::ENGLISH_US]
                );
            }
        }
    }

}
