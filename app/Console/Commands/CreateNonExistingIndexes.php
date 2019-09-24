<?php

namespace App\Console\Commands;

use App\Enums\ElasticIndexableModels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use ScoutElastic\Console\Features\RequiresIndexConfiguratorArgument;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Payloads\IndexPayload;

/**
 * Class CreateNonExistingIndexes
 *
 * @package App\Console\Commands
 */
class CreateNonExistingIndexes extends Command
{
    use RequiresIndexConfiguratorArgument;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:check-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create non-existing elastic indexes for indexable models';

    /**
     * @param string $modelName
     *
     * @throws \Exception
     */
    protected function createIndex(string $modelName)
    {
        /** @var \ScoutElastic\Searchable $model */
        $model = new $modelName;
        $configurator = $model->getIndexConfigurator();

        $payload = (new IndexPayload($configurator))
            ->setIfNotEmpty('body.settings', $configurator->getSettings())
            ->get();

        if (!ElasticClient::indices()->exists(['index' => $payload['index']])) {
            Artisan::call('elastic:create-index', [
                'index-configurator' => get_class($configurator),
            ]);

            Artisan::call('elastic:update-mapping', [
                'model' => $modelName,
            ]);

            \call_user_func([$modelName, 'makeAllSearchable']);

            $this->info(sprintf(
                'Index %s was created!',
                $configurator->getName()
            ));
        }
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        foreach (ElasticIndexableModels::values() as $model) {
            $this->createIndex($model);
        }
    }
}
