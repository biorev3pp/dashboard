<?php

namespace App\Listener;

use App\Events\ListInfoUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ListInfoUpdateInDB implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'database';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 60;
    /**
     * Create the event listener.
     *
     * @return void
     */
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ListInfoUpdate  $event
     * @return void
     */
    public function handle(ListInfoUpdate $event)
    {
        echo "response from";
        //dd($event->info);
        //echo "events";
        // DB::table("test_jobs")->insert([
        //     "name" => date("Y-m-d H:i:s", strtotime("now"))
        // ]);
    }
}
