<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleFilesSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample text files
        $sampleFiles = [
            'document1.txt' => 'This is sample document 1 content.',
            'document2.txt' => 'This is sample document 2 content.',
            'report.pdf.txt' => 'This is a sample PDF report content.',
            'image.jpg.txt' => 'This represents an image file.',
            'data.csv.txt' => 'Sample,Data,Here\n1,2,3\n4,5,6',
            'readme.md' => '# Sample Project\nThis is a sample readme file.',
            'config.json' => '{"app": "Laravel Zip", "version": "1.0.0"}',
            'notes.txt' => 'Important notes go here.',
        ];

        foreach ($sampleFiles as $filename => $content) {
            Storage::put('public/files/' . $filename, $content);
        }

        $this->command->info('Sample files created successfully!');
    }
}