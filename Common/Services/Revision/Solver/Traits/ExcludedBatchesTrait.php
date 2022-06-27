<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Common\Services\Revision\Solver\Traits;

use Common\Helpers\ModelHelper;
use Common\Models\Base\RevisionBatch;
use Common\Services\Revision\Solver\Common\RevisionSolverConfig;

trait ExcludedBatchesTrait
{
    public function getExcludedBatches($args): array
    {
        $config = ModelHelper::ensure(RevisionSolverConfig::class, $args);

        $query = RevisionBatch::find()->select('batch_id')
            ->where(['is_excluded' => RevisionBatch::STATUS_EXCLUDED])
            ->andWhere(['revision_id' => $config->revisionId]);

        /** @noinspection PhpUnusedLocalVariableInspection */
        YII_DEBUG && $sql = $query->createCommand()->getRawSql();

        return $query->column();
    }
}