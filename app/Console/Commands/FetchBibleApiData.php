<?php

namespace App\Console\Commands;

use App\Enums\Testament; // Make sure you have this Enum
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class FetchBibleApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bible:fetch-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all Bible chapters from an API and caches them locally in database/data/.';

    /**
     * The base URL for the public Bible API.
     */
    private const API_BASE_URL = 'https://bible-api.deno.dev/api/read/rv1960';

    /**
     * The full metadata required to fetch and identify all books.
     * Format: [Title, Abbreviation, Testament, Book Number, API URL Slug, Chapter Count]
     */
    private static array $bookMetadata = [
        // OT
        'genesis' => ['Génesis', 'Gen', Testament::OLD_TESTAMENT, 1, 'genesis', 50],
        'exodo' => ['Éxodo', 'Éxo', Testament::OLD_TESTAMENT, 2, 'exodo', 40],
        'levitico' => ['Levítico', 'Lev', Testament::OLD_TESTAMENT, 3, 'levitico', 27],
        'numeros' => ['Números', 'Núm', Testament::OLD_TESTAMENT, 4, 'numeros', 36],
        'deuteronomio' => ['Deuteronomio', 'Deut', Testament::OLD_TESTAMENT, 5, 'deuteronomio', 34],
        'josue' => ['Josué', 'Jos', Testament::OLD_TESTAMENT, 6, 'josue', 24],
        'jueces' => ['Jueces', 'Juec', Testament::OLD_TESTAMENT, 7, 'jueces', 21],
        'rut' => ['Rut', 'Rut', Testament::OLD_TESTAMENT, 8, 'rut', 4],
        '1_samuel' => ['1 Samuel', '1 Sam', Testament::OLD_TESTAMENT, 9, '1-samuel', 31],
        '2_samuel' => ['2 Samuel', '2 Sam', Testament::OLD_TESTAMENT, 10, '2-samuel', 24],
        '1_reyes' => ['1 Reyes', '1 Rey', Testament::OLD_TESTAMENT, 11, '1-reyes', 22],
        '2_reyes' => ['2 Reyes', '2 Rey', Testament::OLD_TESTAMENT, 12, '2-reyes', 25],
        '1_cronicas' => ['1 Crónicas', '1 Crón', Testament::OLD_TESTAMENT, 13, '1-cronicas', 29],
        '2_cronicas' => ['2 Crónicas', '2 Crón', Testament::OLD_TESTAMENT, 14, '2-cronicas', 36],
        'esdras' => ['Esdras', 'Esd', Testament::OLD_TESTAMENT, 15, 'esdras', 10],
        'nehemias' => ['Nehemías', 'Neh', Testament::OLD_TESTAMENT, 16, 'nehemias', 13],
        'ester' => ['Ester', 'Est', Testament::OLD_TESTAMENT, 17, 'ester', 10],
        'job' => ['Job', 'Job', Testament::OLD_TESTAMENT, 18, 'job', 42],
        'salmos' => ['Salmos', 'Sal', Testament::OLD_TESTAMENT, 19, 'salmos', 150],
        'proverbios' => ['Proverbios', 'Prov', Testament::OLD_TESTAMENT, 20, 'proverbios', 31],
        'eclesiastes' => ['Eclesiastés', 'Ecl', Testament::OLD_TESTAMENT, 21, 'eclesiastes', 12],
        'cantares' => ['Cantares', 'Cant', Testament::OLD_TESTAMENT, 22, 'cantares', 8],
        'isaias' => ['Isaías', 'Isa', Testament::OLD_TESTAMENT, 23, 'isaias', 66],
        'jeremias' => ['Jeremías', 'Jer', Testament::OLD_TESTAMENT, 24, 'jeremias', 52],
        'lamentaciones' => ['Lamentaciones', 'Lam', Testament::OLD_TESTAMENT, 25, 'lamentaciones', 5],
        'ezequiel' => ['Ezequiel', 'Eze', Testament::OLD_TESTAMENT, 26, 'ezequiel', 48],
        'daniel' => ['Daniel', 'Dan', Testament::OLD_TESTAMENT, 27, 'daniel', 12],
        'oseas' => ['Oseas', 'Ose', Testament::OLD_TESTAMENT, 28, 'oseas', 14],
        'joel' => ['Joel', 'Joel', Testament::OLD_TESTAMENT, 29, 'joel', 3],
        'amos' => ['Amós', 'Amós', Testament::OLD_TESTAMENT, 30, 'amos', 9],
        'abdias' => ['Abdías', 'Abd', Testament::OLD_TESTAMENT, 31, 'abdias', 1],
        'jonas' => ['Jonás', 'Jon', Testament::OLD_TESTAMENT, 32, 'jonas', 4],
        'miqueas' => ['Miqueas', 'Miq', Testament::OLD_TESTAMENT, 33, 'miqueas', 7],
        'nahum' => ['Nahúm', 'Nah', Testament::OLD_TESTAMENT, 34, 'nahum', 3],
        'habacuc' => ['Habacuc', 'Hab', Testament::OLD_TESTAMENT, 35, 'habacuc', 3],
        'sofonias' => ['Sofonías', 'Sof', Testament::OLD_TESTAMENT, 36, 'sofonias', 3],
        'hageo' => ['Hageo', 'Hag', Testament::OLD_TESTAMENT, 37, 'hageo', 2],
        'zacarias' => ['Zacarías', 'Zac', Testament::OLD_TESTAMENT, 38, 'zacarias', 14],
        'malaquias' => ['Malaquías', 'Mal', Testament::OLD_TESTAMENT, 39, 'malaquias', 4],
        // NT
        'mateo' => ['Mateo', 'Mat', Testament::NEW_TESTAMENT, 40, 'mateo', 28],
        'marcos' => ['Marcos', 'Mar', Testament::NEW_TESTAMENT, 41, 'marcos', 16],
        'lucas' => ['Lucas', 'Luc', Testament::NEW_TESTAMENT, 42, 'lucas', 24],
        'juan' => ['Juan', 'Jn', Testament::NEW_TESTAMENT, 43, 'juan', 21],
        'hechos' => ['Hechos', 'Hech', Testament::NEW_TESTAMENT, 44, 'hechos', 28],
        'romanos' => ['Romanos', 'Rom', Testament::NEW_TESTAMENT, 45, 'romanos', 16],
        '1_corintios' => ['1 Corintios', '1 Cor', Testament::NEW_TESTAMENT, 46, '1-corintios', 16],
        '2_corintios' => ['2 Corintios', '2 Cor', Testament::NEW_TESTAMENT, 47, '2-corintios', 13],
        'galatas' => ['Gálatas', 'Gál', Testament::NEW_TESTAMENT, 48, 'galatas', 6],
        'efesios' => ['Efesios', 'Efe', Testament::NEW_TESTAMENT, 49, 'efesios', 6],
        'filipenses' => ['Filipenses', 'Fil', Testament::NEW_TESTAMENT, 50, 'filipenses', 4],
        'colosenses' => ['Colosenses', 'Col', Testament::NEW_TESTAMENT, 51, 'colosenses', 4],
        '1_tesalonicenses' => ['1 Tesalonicenses', '1 Tes', Testament::NEW_TESTAMENT, 52, '1-tesalonicenses', 5],
        '2_tesalonicenses' => ['2 Tesalonicenses', '2 Tes', Testament::NEW_TESTAMENT, 53, '2-tesalonicenses', 3],
        '1_timoteo' => ['1 Timoteo', '1 Tim', Testament::NEW_TESTAMENT, 54, '1-timoteo', 6],
        '2_timoteo' => ['2 Timoteo', '2 Tim', Testament::NEW_TESTAMENT, 55, '2-timoteo', 4],
        'tito' => ['Tito', 'Tit', Testament::NEW_TESTAMENT, 56, 'tito', 3],
        'filemon' => ['Filemón', 'Filem', Testament::NEW_TESTAMENT, 57, 'filemon', 1],
        'hebreos' => ['Hebreos', 'Heb', Testament::NEW_TESTAMENT, 58, 'hebreos', 13],
        'santiago' => ['Santiago', 'Sant', Testament::NEW_TESTAMENT, 59, 'santiago', 5],
        '1_pedro' => ['1 Pedro', '1 Ped', Testament::NEW_TESTAMENT, 60, '1-pedro', 5],
        '2_pedro' => ['2 Pedro', '2 Ped', Testament::NEW_TESTAMENT, 61, '2-pedro', 3],
        '1_juan' => ['1 Juan', '1 Jn', Testament::NEW_TESTAMENT, 62, '1-juan', 5],
        '2_juan' => ['2 Juan', '2 Jn', Testament::NEW_TESTAMENT, 63, '2-juan', 1],
        '3_juan' => ['3 Juan', '3 Jn', Testament::NEW_TESTAMENT, 64, '3-juan', 1],
        'judas' => ['Judas', 'Jud', Testament::NEW_TESTAMENT, 65, 'judas', 1],
        'apocalipsis' => ['Apocalipsis', 'Apoc', Testament::NEW_TESTAMENT, 66, 'apocalipsis', 22],
    ];

    public function handle()
    {
        $this->info('Starting to fetch and cache Bible data from API...');

        $cacheDirectoryPath = base_path('database/data/bible-api-cache');

        if (! is_dir($cacheDirectoryPath)) {
            mkdir($cacheDirectoryPath, 0775, true);
        }

        try {
            $totalChapters = array_sum(array_column(self::$bookMetadata, 5));
            $progressBar = $this->getOutput()->createProgressBar($totalChapters);
            $this->info("Found {$totalChapters} chapters to fetch and cache.");
            $progressBar->start();

            foreach (self::$bookMetadata as [$title, $abbreviation, $testament, $book_number, $apiId, $chapterCount]) {
                for ($chapter = 1; $chapter <= $chapterCount; $chapter++) {
                    $fileName = "{$apiId}-{$chapter}.json";
                    $filePath = "{$cacheDirectoryPath}/{$fileName}";

                    if (file_exists($filePath)) {
                        $progressBar->advance();
                        continue;
                    }

                    $apiUrl = self::API_BASE_URL . "/{$apiId}/{$chapter}";
                    $response = Http::timeout(30)->get($apiUrl);

                    if ($response->failed()) {
                        throw new \Exception("Failed to fetch chapter {$apiId} {$chapter}. API Status: " . $response->status());
                    }

                    file_put_contents($filePath, $response->body());

                    $progressBar->advance();
                    usleep(100000); // 0.1 second pause to be respectful to the API
                }
            }

            $progressBar->finish();
            $this->info("\n✅ All Bible data has been successfully fetched and cached locally.");
            return 0;
        } catch (Throwable $e) {
            $this->error("\n❌ API fetch failed: " . $e->getMessage());
            return 1;
        }
    }
}
