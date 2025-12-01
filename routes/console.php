<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Typesense\Client;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



// Créer la collection "images"
Artisan::command('typesense:create-images', function () {
    $client = new Client([
        'api_key' => 'xyz',
        'nodes' => [
            [
                'host' => 'localhost',
                'port' => '8108',
                'protocol' => 'http',
            ]
        ]
    ]);

    try {
        $client->collections->create([
            'name' => 'images',
            'fields' => [
                ['name' => 'id', 'type' => 'string'],
                ['name' => 'author', 'type' => 'string'],
                ['name' => 'width', 'type' => 'int32', 'facet'=>true],
                ['name' => 'height', 'type' => 'int32','facet'=>true],
                ['name' => 'url', 'type' => 'string'],
                ['name' => 'download_url', 'type' => 'string'],
            ],
            'default_sorting_field' => 'height'
        ]);
        $this->info("Collection 'images' créée !");
    } catch (\Exception $e) {
        $this->error($e->getMessage());
    }
});

// Indexer le fichier list.json
Artisan::command('typesense:index-images', function () {
    $client = new Client([
        'api_key' => 'xyz',
        'nodes' => [
            [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'protocol' => 'http',
            ]
        ]
    ]);

    // Lire le fichier JSON
    $path = storage_path('app/list.json');
    if (!file_exists($path)) {
        $this->error("list.json n'existe pas dans storage/app");
        return;
    }

    $json = json_decode(file_get_contents($path), true);

    if (!is_array($json)) {
        $this->error("list.json n'est pas un JSON valide.");
        return;
    }

    foreach ($json as $img) {
        $client->collections['images']->documents->upsert([
            'id' => (string)$img['id'],
            'author' => $img['author'],
            'width' => (int)$img['width'],
            'height' => (int)$img['height'],
            'url' => $img['url'],
            'download_url' => $img['download_url'],
        ]);
    }

    $this->info("Indexation terminée !");
});
