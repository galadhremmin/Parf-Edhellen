<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportProfileFeatureBackground extends Command
{
    const IMAGE_FOLDER = 'public/profile-feature-backgrounds';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ed:import-feature-background {image*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the associated images to the collection of feature backgrounds available on users\' profile.';

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
        $images = $this->argument('image');

        foreach ($images as $image) {
            if (! file_exists($image)) {
                $this->error(sprintf('Aborted! %s can\'t be found.', $image));
                return 1;
            }
        }

        if (! Storage::exists(self::IMAGE_FOLDER)) {
            Storage::makeDirectory(self::IMAGE_FOLDER);
        }

        $basePath = Storage::path(self::IMAGE_FOLDER);
        foreach ($images as $imagePath) {
            $newPath = sprintf($basePath.'/%s.%s', uniqid(), pathinfo($imagePath)['extension']);
            copy($imagePath, $newPath);

            $this->line(sprintf('Copied %s to %s.', $imagePath, $newPath));
        }

        return 0;
    }
}
