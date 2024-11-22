<?php

namespace ExpertShipping\Spl\Jobs;

use ExpertShipping\Spl\Exports\AgentCommissionsExport;
use ExpertShipping\Spl\Models\Retail\Commissionable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class CreateAgentCommissionsExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const DIRECTORY_NAME = 'agent-commissions';
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $commissionables = Commissionable::all();

        Storage::disk('tmp')->deleteDirectory(self::DIRECTORY_NAME);
        Storage::disk('tmp')->delete(self::DIRECTORY_NAME.'.zip');

        foreach ($commissionables as $commissionable) {
            Excel::store(
                new AgentCommissionsExport($commissionable),
                self::DIRECTORY_NAME.'/'.$commissionable->name.'.csv',
                'tmp'
            );
        }

        $path = config('filesystems.disks.tmp.root') ."/". self::DIRECTORY_NAME ."/";
        $createdZip = config('filesystems.disks.tmp.root') ."/". self::DIRECTORY_NAME .".zip";

        // Create new zip class
        $zip = new ZipArchive;

        if($zip->open($createdZip, ZipArchive::CREATE) === TRUE) {
            // Store the path into the variable
            $dir = opendir($path);

            while($file = readdir($dir)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $info = pathinfo($file);
                $newName = $info['filename'] . '.iif';
                rename(
                    config('filesystems.disks.tmp.root').'/'. self::DIRECTORY_NAME .'/'.$file,
                    config('filesystems.disks.tmp.root') .'/' . self::DIRECTORY_NAME . '/'.$newName
                );
                $zip->addFile($path.$newName, $newName);
            }
            $zip->close();
        }

        Storage::disk('tmp')->deleteDirectory(self::DIRECTORY_NAME);
    }
}
