<?php /** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Common\Services\Revision\Solver\ByOwners;

use yii\base\Exception;
use Common\Models\Base\Batch;
use Common\Helpers\ModelHelper;
use Common\Models\Base\Revision;
use Common\Services\Revision\Solver\Traits\ExcludedBatchesTrait;
use Common\Services\Revision\Solver\Common\RevisionSolverConfig;
use Common\Services\Revision\Solver\Traits\DimensionalPricesTrait;

class RevisionSolver implements RevisionSolverInterface
{
    use ExcludedBatchesTrait, DimensionalPricesTrait;

    public function solve($config): void
    {
        $config = ModelHelper::ensure(RevisionSolverConfig::class, $config);

        Revision::getDb()->transaction(function () use ($config) {
            $this->recalculatePrices($config);
            $this->recalculate($config);
            $this->markSolved($config);
        });
    }

    private function recalculate($config): bool
    {
        $config      = ModelHelper::ensure(RevisionSolverConfig::class, $config);
        $revision    = Revision::findOne($config->revisionId);

        $query = Batch::find()->alias('b')->joinWith(['revisions r'], false)->where([
            'and',
            ['r.id' => $config->revisionId],
            ['not in', 'b.id', $this->getExcludedBatches($config)]
        ])->select(['b.user_id'])->groupBy('b.id')->distinct();

        /** @noinspection PhpUnusedLocalVariableInspection */
        YII_DEBUG && $sql = $query->createCommand()->getRawSql();

        $ownersCount = $query->distinct()->count();

        $pricing = $revision->pricing;

        $pricing->price_per_owner = $ownersCount ? $revision->pricing->price / $ownersCount : 0;
        $pricing->save() || throw new Exception(json_encode($pricing->errors));

        return true;
    }

    private function markSolved(array|RevisionSolverConfig $config): bool
    {
        $config = ModelHelper::ensure(RevisionSolverConfig::class, $config);

        return Revision::updateAll(['is_solved' => true], ['id' => $config->revisionId]);
    }
}