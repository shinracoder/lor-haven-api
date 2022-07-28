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
        $versions = $this->getVersions();

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

    public function getVersions(): array
    {
        $versions = [];
        $majorVersion = 1;
        $minorVersion = 0;
        $previousMajorVersion = null;
        $previousMinorVersion = null;
        $tries = 0;

        do {
            $version = "{$majorVersion}_{$minorVersion}_0";
            if ($version === '1_9_0') {
                //There is no version 1.9.0 there was a minor version gap
                $minorVersion++;
                $version = "{$majorVersion}_{$minorVersion}_0";
            }
            if (
                @file_get_contents(
                'https://dd.b.pvp.net/' . $version . '/core-en_us.zip',
                0,
                NULL,
                0,
                1)
            ) {
                $versions[] = $version;
                $tries = 0;
                $minorVersion++;
            } else {
                $minorVersion = 0;
                $majorVersion++;
                $tries++;
            }
        } while ($tries <= 2);

        return $versions;
    }

}
