<?php

namespace App\Jobs;

use App\ComModules\Empatia;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveVotesInTopic implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $voteCount;
    protected $requestVoteKey;
    protected $eventKey;
    protected $totalVotes;
    protected $totalUsers;

    /**
     * Create a new job instance.
     *
     * @param $voteCount
     * @param $requestVoteKey
     * @param $eventKey
     * @param $totalVotes
     */
    public function __construct($voteCount, $requestVoteKey, $eventKey, $totalVotes, $totalUsers)
    {
        $this->voteCount = $voteCount;
        $this->requestVoteKey = $requestVoteKey;
        $this->eventKey = $eventKey;
        $this->totalVotes = $totalVotes;
        $this->totalUsers = $totalUsers;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Empatia::updateTopicVotesInfo( $this->voteCount, $this->requestVoteKey, $this->eventKey, $this->totalVotes,  $this->totalUsers);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
    }
}
