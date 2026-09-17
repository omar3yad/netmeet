<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $username;

    /**
     * Create a new job instance.
     */
    public function __construct($file, $username)
    {
        $this->file = $file;
        $this->username = $username;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // ????: ????? ??????? ?? S3 ?? Local storage
        $fileName = time() . '_' . $this->file->getClientOriginalName();
        $path = $this->file->storeAs('videos/' . $this->username, $fileName, 'public');

        // ?? ???? ???? ???????? ?? ????? ????????
        \DB::table('recordings')->insert([
            'username' => $this->username,
            'file_name' => $fileName,
            'file_path' => $path,
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
