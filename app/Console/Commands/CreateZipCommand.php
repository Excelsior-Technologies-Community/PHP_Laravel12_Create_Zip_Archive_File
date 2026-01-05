<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CreateZipCommand extends Command
{
    protected $signature = 'zip:create {directory? : Directory to zip} {--name= : Zip file name}';
    protected $description = 'Create a zip archive from files';

    public function handle()
    {
        $directory = $this->argument('directory') ?? 'public/files';
        $zipName = $this->option('name') ?? 'archive_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        $this->info("Creating zip archive from: {$directory}");
        
        if (!Storage::exists($directory)) {
            $this->error("Directory {$directory} does not exist!");
            return 1;
        }

        $zip = new ZipArchive;
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = Storage::allFiles($directory);
            
            if (empty($files)) {
                $this->warn("No files found in {$directory}");
                $zip->close();
                unlink($zipPath);
                return 0;
            }

            foreach ($files as $file) {
                if (Storage::exists($file)) {
                    $zip->addFile(
                        Storage::path($file),
                        str_replace($directory . '/', '', $file)
                    );
                    $this->line("Added: {$file}");
                }
            }
            
            $zip->close();
            $this->info("Zip archive created successfully: {$zipPath}");
            $this->info("File size: " . number_format(filesize($zipPath) / 1024, 2) . " KB");
            
            return 0;
        }
        
        $this->error("Failed to create zip archive!");
        return 1;
    }
}