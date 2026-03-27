<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class RemoveTaskMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Checklist Media';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->warn('Removal of media started');

        $folderPath = storage_path('app/public/workflow-task-uploads');

        DB::table('checklist_tasks')
            ->join('checklist_scheduling_extras', 'checklist_tasks.checklist_scheduling_id', '=', 'checklist_scheduling_extras.id')
            ->join('checklist_schedulings', 'checklist_scheduling_extras.checklist_scheduling_id', '=', 'checklist_schedulings.id')
            ->join('dynamic_forms', 'checklist_schedulings.checklist_id', '=', 'dynamic_forms.id')
            ->where(function ($builder) {
                $builder->whereNull('checklist_tasks.deleted_at')
                ->orWhere('checklist_tasks.deleted_at', '');
            })
            ->orderBy('checklist_tasks.id')
            ->selectRaw('checklist_tasks.id, checklist_schedulings.checklist_id, dynamic_forms.remove_media_frequency, dynamic_forms.remove_media_frequency_after_n_day, checklist_tasks.completion_date')
            ->chunk(1000, function ($tasks) use ($folderPath) {
                foreach ($tasks as $task) {
                    if (!empty($task->completion_date) && is_numeric($task->remove_media_frequency_after_n_day) && $task->remove_media_frequency_after_n_day >= 1 && $task->remove_media_frequency == 'every_n_day' && is_numeric($task->remove_media_frequency_after_n_day) && $task->remove_media_frequency_after_n_day > 0 &&
                    Carbon::parse($task->completion_date)->addDays($task->remove_media_frequency_after_n_day)->lt(Carbon::now())) {
                        // Delete Files
                        $logPath = storage_path('logs/media-removal.log');

                        $iterator = new \DirectoryIterator($folderPath);

                        foreach ($iterator as $file) {

                            if ($file->isDot()) {
                                continue;
                            }

                            if (!$file->isFile()) {
                                continue;
                            }

                            $realPath = $file->getRealPath();

                            if (
                                $realPath === false ||
                                !str_starts_with($realPath, realpath($folderPath))
                            ) {
                                continue;
                            }

                            $fileName = $file->getFilename();

                            if (
                                str_starts_with($fileName, 'SIGN-20') &&
                                str_ends_with($fileName, "-{$task->checklist_id}-{$task->id}.webp")
                            ) {
                                $this->info($fileName);

                                File::append(
                                    $logPath,
                                    'REAL-[' . now() . '] ' . $realPath . PHP_EOL
                                );

                                unlink($realPath);
                            }
                        }
                        // Delete Files
                    }                    
                }
            });

        $this->warn('Removal of media completed');
    }
}
