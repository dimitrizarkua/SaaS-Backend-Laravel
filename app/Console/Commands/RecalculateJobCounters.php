<?php

namespace App\Console\Commands;

use App\Components\Jobs\DataProviders\JobCountersDataProvider;
use App\Components\Jobs\Enums\JobCountersCacheKeys;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class RecalculateJobCounters
 *
 * @package App\Console\Commands
 */
class RecalculateJobCounters extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:recalc_counters 
    {--user= : specify user id for recalculation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate job counters: inbox, teams, mine.';

    /**
     * @var JobCountersDataProvider
     */
    private $countersDataProvider;

    /** @var \Illuminate\Cache\TaggedCache */
    private $cache;

    public const LIMIT = 100;

    /**
     * RecalculateJobCounters constructor.
     *
     * @param JobCountersDataProvider $countersDataProvider
     */
    public function __construct(JobCountersDataProvider $countersDataProvider)
    {
        parent::__construct();
        $this->countersDataProvider = $countersDataProvider;
        $this->cache                = taggedCache(JobCountersCacheKeys::TAG_KEY);
    }

    /**
     * 1. Get specified user or all
     * 2. Recalculate inbox
     * 3. Recalculate mine counters by user
     * 4. Recalculate teams counters
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $userId = $this->option('user');

        if (empty($userId) && !$this->confirm('Will recalculate counters for all users. Continue?')) {
            $this->abort('Command was canceled by user');
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $this->prepareUserQuery($userId);

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $this->recalculateInboxCounter();
        $this->recalculateMineCounters($query, $bar);

        $bar->finish();

        $this->info(PHP_EOL . 'Done! Counters recalculated.');
    }

    /**
     * Prepares query builder based on CLI args and options.
     *
     * @param int|null $userId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function prepareUserQuery(?int $userId)
    {
        if (!empty($userId)) {
            $user = User::find($userId);

            if (empty($user)) {
                $this->abort('User not found');
            }

            return $user->select('id')
                ->where('id', $user->id);
        }

        $user  = new User();
        $query = $user->select('id');

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query;
    }

    /**
     * Recalculate inbox counter
     */
    private function recalculateInboxCounter()
    {
        $this->cache->forget(JobCountersCacheKeys::INBOX_KEY);

        $inboxCounter = $this->countersDataProvider->getInboxCounter();
        $this->cache->forever(JobCountersCacheKeys::INBOX_KEY, $inboxCounter);
    }

    /**
     * Recalculate users (mine) counters.
     *
     * @param \Illuminate\Database\Eloquent\Builder              $query
     * @param \Symfony\Component\Console\Helper\ProgressBar|null $bar
     */
    private function recalculateMineCounters(Builder $query, ProgressBar $bar)
    {
        $query->chunk(self::LIMIT, function ($users) use ($bar) {
            foreach ($users as $user) {
                $mineKey = sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $user->id);
                $this->cache->forget($mineKey);

                $mineCounters = $this->countersDataProvider->getMineCounters($user->id);

                $this->cache->forever($mineKey, json_encode($mineCounters));
                $bar->advance();
            }
        });
    }
}
