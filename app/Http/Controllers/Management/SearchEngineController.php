<?php

namespace App\Http\Controllers\Management;

use App\Components\Finance\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Management\IndexModelRequests;
use Illuminate\Support\Facades\Artisan;

/**
 * Class SearchEngineController
 *
 * @package App\Http\Controllers\Management
 */
class SearchEngineController extends Controller
{
    /**
     * @OA\Post(
     *      path="/management/search/index",
     *      tags={"Management"},
     *      summary="Allows to import the models into the search index",
     *      description="Allows to import the models into the search index",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/IndexModelRequests")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Management\IndexModelRequests $requests
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function index(IndexModelRequests $requests)
    {
        $this->authorize('management.search.index');
        $classList = $requests->getModelClassList();
        foreach ($classList as $modelName) {
            $this->indexModel($modelName);
        }
    }

    /**
     * @OA\Post(
     *      path="/management/search/flush",
     *      tags={"Management"},
     *      summary="Allows to flush all of the model\'s records from the index",
     *      description="Allows to flush all of the model\'s records from the index",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/IndexModelRequests")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Management\IndexModelRequests $requests
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function flush(IndexModelRequests $requests)
    {
        $this->authorize('management.search.flush');
        $classList = $requests->getModelClassList();
        foreach ($classList as $class) {
            \call_user_func([$class, 'removeAllFromSearch']);
        }
    }

    /**
     * Import the models into the search index.
     *
     * @param string $modelName Class name of model to be indexed.
     *
     * @throws \Exception
     */
    private function indexModel(string $modelName): void
    {
        $indexConfiguratorClass = $this->getIndexConfiguratorClass($modelName);
        try {
            Artisan::call('elastic:drop-index', [
                'index-configurator' => $indexConfiguratorClass,
            ]);
        } catch (\Exception $e) {
        }

        Artisan::call('elastic:create-index', [
            'index-configurator' => $indexConfiguratorClass,
        ]);

        Artisan::call('elastic:update-mapping', [
            'model' => $modelName,
        ]);

        \call_user_func([$modelName, 'makeAllSearchable']);
    }

    /**
     * Returns class name of models index configurator.
     *
     * @param string $modelName Model name. Should use searchable trait.
     *
     * @throws \Exception
     * @return string
     */
    private function getIndexConfiguratorClass(string $modelName): string
    {
        /** @var \ScoutElastic\Searchable $model */
        $model        = new $modelName;
        $configurator = $model->getIndexConfigurator();

        return get_class($configurator);
    }
}
