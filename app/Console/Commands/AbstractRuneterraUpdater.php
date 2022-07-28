<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Keyword;
use App\Models\Rarity;
use App\Models\Region;
use App\Models\SpellSpeed;
use App\Models\Version;
use App\Models\VocabTerm;
use Illuminate\Console\Command;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

abstract class AbstractRuneterraUpdater extends Command
{
    /**
     * @var string
     */
    protected $localeArray = [
        'en_us',
        'de_de',
        'es_es',
    ];

    protected const BASE_URL = 'https://dd.b.pvp.net/';

    /**
     * @var Filesystem
     */
    protected $downloadDir;

    /**
     * @var bool
     */
    protected $breakOnVersionFound = false;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->downloadDir = new Filesystem(new Local('runeterra/downloads/'));
        parent::__construct();
    }

    /**
     * @param string $archiveFile
     * @return Filesystem
     */
    protected function getArchive(string $archiveFile): Filesystem
    {
        return  new Filesystem(
            new ZipArchiveAdapter($archiveFile)
        );
    }

    /**
     * @param string $url
     * @throws \Exception
     */
    protected function download(string $url)
    {
        return file_get_contents($url);
    }

    /**
     * @return string
     * @throws FileNotFoundException
     */
    protected function getLatestVersionNumber(): ?string
    {
        $version = '';
        $this->alert("Downloading https://dd.b.pvp.net/latest/core-en_us.zip .....");
        try {
            $download = $this->download("https://dd.b.pvp.net/latest/core-en_us.zip");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return null;
        }
        $this->alert("Download Complete!");
        $this->downloadDir->put("core-en_us/core-en_us.zip", $download);
        if ($this->downloadDir->has("core-en_us/core-en_us.zip")) {
            $zippedFile = $this->getArchive("runeterra/downloads/core-en_us/core-en_us.zip");
            /** @var ZipArchiveAdapter $zip */
            $zip = $zippedFile->getAdapter();
            $zip->getArchive()->extractTo("runeterra/downloads/core-en_us/core-en_us/");
            $this->downloadDir->delete("core-en_us/core-en_us.zip");

            if ($this->downloadDir->has("core-en_us/core-en_us/en_us/data/globals-en_us.json")) {
                $json = $this->downloadDir->read("core-en_us/core-en_us/en_us/data/globals-en_us.json");
                $this->downloadDir->deleteDir("core-en_us");
                $globals = json_decode($json, true);
                $regions = $globals['regions'] ?? [];
                $version = str_replace('http://dd.b.pvp.net/', '', $regions[0]['iconAbsolutePath']);
                $version = substr($version, '0', strpos($version, '/'));
            }
        }

        return $version;
    }

    protected function downloadCore(string $downloadVersion, array $localeArray)
    {
        $cards = [];
        $version = $downloadVersion;

        foreach ($localeArray as $locale) {
            $this->alert("Downloading https://dd.b.pvp.net/$downloadVersion/core-$locale.zip .....");
            try {
                $download = $this->download("https://dd.b.pvp.net/$downloadVersion/core-$locale.zip");
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                return;
            }
            $this->alert("Download Complete!");
            $this->downloadDir->put("core-$locale/core-$locale.zip", $download);
            if ($this->downloadDir->has("core-$locale/core-$locale.zip")) {
                $zippedFile = $this->getArchive("runeterra/downloads/core-$locale/core-$locale.zip");
                /** @var ZipArchiveAdapter $zip */
                $zip = $zippedFile->getAdapter();
                $zip->getArchive()->extractTo("runeterra/downloads/core-$locale/core-$locale/");
                $this->downloadDir->delete("core-$locale/core-$locale.zip");
                $pathOne = "core-$locale/core-$locale/$locale/data/globals-$locale.json";
                $pathTwo = "core-$locale/core-$locale/core-$locale/$locale/data/globals-$locale.json";

                if ($this->downloadDir->has($pathOne) || $this->downloadDir->has($pathTwo)) {
                    $json = $this->downloadDir->has($pathOne)
                        ? $this->downloadDir->read($pathOne) : $this->downloadDir->read($pathTwo);
                    $this->downloadDir->deleteDir("core-$locale");
                    $globals = json_decode($json, true);
                    $vocabTerms = $globals['vocabTerms'] ?? [];
                    $keywords = $globals['keywords'] ?? [];
                    $regions = $globals['regions'] ?? [];
                    $spellSpeeds = $globals['spellSpeeds'] ?? [];
                    $rarities = $globals['rarities'] ?? [];

                    if ($version === 'latest') {
                        $version = str_replace('http://dd.b.pvp.net/', '', $regions[0]['iconAbsolutePath']);
                        $version = substr($version, '0', strpos($version, '/'));
                    }

                    $newVersion = Version::query()->firstWhere('version', '=', $version);
                    $cardsExists = Card::query()->firstWhere([
                        'version' => $version,
                        'locale' => $locale,
                    ]);

                    if (!$newVersion) {
                        $newVersion = new Version();
                        $newVersion->version = $version;
                        $newVersion->latest = false;
                        $newVersion->save();
                        $this->alert('Saved new version to database!');
                    } elseif ($this->breakOnVersionFound && $cardsExists) {
                        $this->alert("version $version in locale $locale already exists");
                        continue;
                    }

                    foreach ($vocabTerms as $resource) {
                        $model = VocabTerm::query()->firstOrCreate([
                            'nameRef' => $resource['nameRef'],
                            'version' => $version,
                            'locale' => $locale,
                        ]);
                        $model->name = $resource['name'];
                        $model->description = $resource['description'];
                        $model->locale = $locale;
                        $model->save();
                    }

                    foreach ($keywords as $resource) {
                        $model = Keyword::query()->firstOrCreate([
                            'nameRef' => $resource['nameRef'],
                            'version' => $version,
                            'locale' => $locale,
                        ]);
                        $model->name = $resource['name'];
                        $model->description = $resource['description'];
                        $model->locale = $locale;
                        $model->save();
                    }

                    foreach ($regions as $resource) {
                        $model = Region::query()->firstOrCreate([
                            'nameRef' => $resource['nameRef'],
                            'version' => $version,
                            'locale' => $locale,
                        ]);
                        $model->name = $resource['name'];
                        $model->abbreviation = $resource['abbreviation'];
                        $model->iconAbsolutePath = $resource['iconAbsolutePath'];
                        $model->locale = $locale;
                        $model->save();
                    }

                    foreach ($spellSpeeds as $resource) {
                        $model = SpellSpeed::query()->firstOrCreate([
                            'nameRef' => $resource['nameRef'],
                            'version' => $version,
                            'locale' => $locale,
                        ]);
                        $model->name = $resource['name'];
                        $model->locale = $locale;
                        $model->save();
                    }

                    foreach ($rarities as $resource) {
                        $model = Rarity::query()->firstOrCreate([
                            'nameRef' => $resource['nameRef'],
                            'version' => $version,
                            'locale' => $locale,
                        ]);
                        $model->name = $resource['name'];
                        $model->locale = $locale;
                        $model->save();
                    }
                }
            }

            for ($i = 1; $i < 5; $i++) {
                $downloadFail = false;
                $this->alert("Downloading https://dd.b.pvp.net/$version/set{$i}-lite-$locale.zip .....");
                try {
                    $download = $this->download("https://dd.b.pvp.net/$version/set{$i}-lite-$locale.zip");
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    $downloadFail = true;
                }
                $this->alert("Download Complete!");

                if (!$downloadFail) {
                    $this->downloadDir->put("set-bundles-lite/set{$i}-lite-$locale.zip", $download);
                    if ($this->downloadDir->has("set-bundles-lite/set{$i}-lite-$locale.zip")) {
                        $zippedFile = $this->getArchive("runeterra/downloads/set-bundles-lite/set{$i}-lite-$locale.zip");
                        /** @var ZipArchiveAdapter $zip */
                        $zip = $zippedFile->getAdapter();
                        $this->alert("Extracting Download set{$i}-lite-$locale.zip ...");
                        $zip->getArchive()->extractTo("runeterra/downloads/set-bundles-lite/set{$i}-lite-$locale/");
                        $this->alert("Extraction Complete!");
                        $this->alert("Deleting Zip file set{$i}-lite-$locale.zip ...");
                        $this->downloadDir->delete("set-bundles-lite/set{$i}-lite-$locale.zip");
                        $this->alert("Deleted!");
                        $pathOne = "set-bundles-lite/set{$i}-lite-$locale/$locale/data/set{$i}-$locale.json";
                        $pathTwo = "set-bundles-lite/set{$i}-lite-$locale/set{$i}-$locale/$locale/data/set{$i}-$locale.json";

                        if ($this->downloadDir->has($pathOne) || $this->downloadDir->has($pathTwo) ) {
                            $json = $this->downloadDir->has($pathOne)
                                ? $this->downloadDir->read($pathOne) : $this->downloadDir->read($pathTwo);
                            $cards = array_merge($cards, json_decode($json, true));
                            $this->downloadDir->deleteDir("set-bundles-lite/set{$i}-lite-$locale");
                        }
                    }
                }
            }

            $this->alert('Inserting Cards into the Database...');
            if ($cards && $version) {
                foreach ($cards as $card) {
                    $model = Card::query()->firstOrCreate([
                        'cardCode' => $card['cardCode'],
                        'version' => $version,
                        'locale' => $locale,
                    ]);
                    $model->associatedCards = $card['associatedCards'];
                    $model->associatedCardRefs = $card['associatedCardRefs'];
                    $model->assets = $card['assets'];
                    $model->region = $card['region'];
                    $model->regionRef = $card['regionRef'];
                    $model->attack = $card['attack'];
                    $model->cost = $card['cost'];
                    $model->health = $card['health'];
                    $model->description = $card['description'];
                    $model->descriptionRaw = $card['descriptionRaw'];
                    $model->levelupDescription = $card['levelupDescription'];
                    $model->levelupDescriptionRaw = $card['levelupDescriptionRaw'];
                    $model->flavorText = $card['flavorText'];
                    $model->artistName = $card['artistName'];
                    $model->name = $card['name'];
                    $model->cardCode = $card['cardCode'];
                    $model->version = $version;
                    $model->keywords = $card['keywords'];
                    $model->keywordRefs = $card['keywordRefs'];
                    $model->spellSpeed = $card['spellSpeed'];
                    $model->spellSpeedRef = $card['spellSpeedRef'];
                    $model->rarity = $card['rarity'];
                    $model->rarityRef = $card['rarityRef'];
                    $model->subtype = $card['subtype'];
                    $model->subtypes = $card['subtypes'];
                    $model->supertype = $card['supertype'];
                    $model->type = $card['type'];
                    $model->collectible = $card['collectible'];
                    $model->locale = $locale;
                    $model->save();
                }
            }
            $this->alert('Cards Inserted!');
        }
    }
}
