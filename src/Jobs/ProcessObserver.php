<?php

namespace Chantouch\JWTRedis\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Chantouch\JWTRedis\Facades\RedisCache;

class ProcessObserver implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var Model $model */
    private $model;

    /** @var string */
    private string $process;

    /**
     * ProcessObserver constructor.
     *
     * @param Model $model
     * @param string $process
     */
    public function __construct(Model $model, string $process)
    {
        $this->model = $model;
        $this->process = $process;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $method = $this->process;

        $this->$method();
    }

    /**
     * @return mixed
     */
    protected function deleted(): mixed
    {
        return RedisCache::key($this->model->getRedisKey())->removeCache();
    }

    /**
     * @return mixed
     */
    protected function updated(): mixed
    {
        // Refresh user.
        $this->model = config('jwt-redis.user_model')::find($this->model->id);

        return RedisCache::key($this->model->getRedisKey())
            ->data($this->model->load(config('jwt-redis.cache_relations')))
            ->refreshCache();
    }
}
