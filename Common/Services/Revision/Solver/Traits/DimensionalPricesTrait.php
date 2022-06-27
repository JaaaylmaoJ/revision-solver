<?php

namespace Common\Services\Revision\Solver\Traits;

use yii\db\Exception;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use Common\Models\Base\Revision;
use Common\Models\Base\RevisionPricing;
use Common\Models\Base\ResolveStrategy;
use Common\Services\Revision\Solver\Common\RevisionSolverConfig;

trait DimensionalPricesTrait
{
    public function recalculatePrices(RevisionSolverConfig $config): void
    {
        $revision           = Revision::findOne($config->revisionId);
        $strategyBySegments = ResolveStrategy::STRATEGY_BY_SEGMENTS;

        if(!is_null($revision->pricing->manual_price)) {
            $revision->pricing->price = $revision->pricing->manual_price;
            $revision->pricing->save();
            return;
        }

        $updateSql = RevisionPricing::find()
            ->alias('rp')
            ->joinWith('revision r')
            ->leftJoin(RevisionPricing::tableName() . ' rps', "rps.revision_id = rp.revision_id and rp.strategy_alias = '$strategyBySegments'")
            ->where([
                'rp.revision_id'    => $config->revisionId,
                'rp.strategy_alias' => $revision->strategy_alias,
            ])
            ->select([
                'price' => new Expression(<<<SQL
                    COALESCE(
                        ceil((rp.manual_price_per_km * NULLIF((r.length / (1000)::numeric), (0)::numeric))),
                        rps.price
                    )
                    SQL
                ),
            ]);

        RevisionPricing::updateAll(
            ['(price)' => $updateSql],
            [
                'revision_id'    => $revision->id,
                'strategy_alias' => $revision->strategy_alias
            ]
        );
    }
}