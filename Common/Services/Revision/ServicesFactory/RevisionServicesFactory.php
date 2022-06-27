<?php /** @noinspection PhpDuplicateMatchArmBodyInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Common\Services\Revision\ServicesFactory;

use Yii;
use Common\Models\Base\Revision;
use yii\base\InvalidArgumentException;
use Common\Models\Base\ResolveStrategy;
use Common\Services\Revision\Solver\Common\SolverInterface;
use Common\Services\Batch\Search\Common\BatchSearcherInterface;
use Common\Services\Revision\SumSearcher\RevisionSumSearcherInterface;
use Common\Services\Counterparty\Searcher\Common\ChargesSearcherInterface;
use Common\Services\Revision\Validator\BaseValidator\BaseValidatorInterface;
use Common\Services\Revision\Validator\SimpleValidator\SimpleValidatorInterface;
use Common\Services\Counterparty\Searcher\ChargesByBatch\ChargesByBatchSearcherInterface;
use Common\Services\Counterparty\Searcher\ChargesByOwner\ChargesByOwnerSearcherInterface;
use Common\Services\Revision\Validator\PublicationValidator\PublicationValidatorInterface;
use Common\Services\Counterparty\Searcher\ChargesBySegment\ChargesBySegmentSearcherInterface;
use Common\Services\Revision\Solver\ByOwners\RevisionSolverInterface as ByOwnersSolverInterface;
use Common\Services\Batch\Search\ByOwners\BatchSearcherInterface as ByOwnerBatchSearcherInterface;
use Common\Services\Revision\Solver\ByBatches\RevisionSolverInterface as ByBatchesSolverInterface;
use Common\Services\Revision\Solver\BySegments\RevisionSolverInterface as BySegmentsSolverInterface;
use Common\Services\Batch\Search\BySegments\BatchSearcherInterface as BySegmentBatchSearcherInterface;

class RevisionServicesFactory implements RevisionServicesFactoryInterface
{
    public function getSolver(Revision|string $param): SolverInterface
    {
        $strategy = $this->extractStrategy($param);
        return match ($strategy) {
            ResolveStrategy::STRATEGY_BY_BATCHES  => Yii::createObject(ByBatchesSolverInterface::class),
            ResolveStrategy::STRATEGY_BY_SEGMENTS => Yii::createObject(BySegmentsSolverInterface::class),
            ResolveStrategy::STRATEGY_BY_OWNERS   => Yii::createObject(ByOwnersSolverInterface::class),
            default                               => $this->handleDefault($strategy)
        };
    }

    public function getPublicationValidator(Revision|string $param): BaseValidatorInterface
    {
        $strategy = $this->extractStrategy($param);
        return match ($strategy) {
            ResolveStrategy::STRATEGY_BY_BATCHES  => Yii::createObject(SimpleValidatorInterface::class),
            ResolveStrategy::STRATEGY_BY_OWNERS   => Yii::createObject(SimpleValidatorInterface::class),
            ResolveStrategy::STRATEGY_BY_SEGMENTS => Yii::createObject(PublicationValidatorInterface::class),
            default                               => $this->handleDefault($strategy)
        };
    }

    public function getCounterpartySearcher(Revision|string $param): ChargesSearcherInterface
    {
        $strategy = $this->extractStrategy($param);
        return match ($strategy) {
            ResolveStrategy::STRATEGY_BY_BATCHES  => Yii::createObject(ChargesByBatchSearcherInterface::class),
            ResolveStrategy::STRATEGY_BY_OWNERS   => Yii::createObject(ChargesByOwnerSearcherInterface::class),
            ResolveStrategy::STRATEGY_BY_SEGMENTS => Yii::createObject(ChargesBySegmentSearcherInterface::class),
            default                               => $this->handleDefault($strategy)
        };
    }

    public function getBatchSearcher(Revision|string $param): BatchSearcherInterface
    {
        $strategy = $this->extractStrategy($param);
        return match ($strategy) {
            ResolveStrategy::STRATEGY_BY_BATCHES  => Yii::createObject(ByOwnerBatchSearcherInterface::class),
            ResolveStrategy::STRATEGY_BY_OWNERS   => Yii::createObject(ByOwnerBatchSearcherInterface::class),
            ResolveStrategy::STRATEGY_BY_SEGMENTS => Yii::createObject(BySegmentBatchSearcherInterface::class),
            default                               => $this->handleDefault($strategy)
        };
    }

    public function getStatisticSearcher($param): RevisionSumSearcherInterface
    {
        $strategy = $this->extractStrategy($param);
        return match ($strategy) {
            ResolveStrategy::STRATEGY_BY_BATCHES  => Yii::createObject(RevisionSumSearcherInterface::class),
            ResolveStrategy::STRATEGY_BY_OWNERS   => Yii::createObject(RevisionSumSearcherInterface::class),
            ResolveStrategy::STRATEGY_BY_SEGMENTS => Yii::createObject(RevisionSumSearcherInterface::class),
            default                               => $this->handleDefault($strategy)
        };
    }

    private function extractStrategy(Revision|string $param): string
    {
        return $param instanceof Revision ? $param->strategy_alias : $param;
    }

    private function handleDefault(string $strategy)
    {
        throw new InvalidArgumentException("Invalid strategy - $strategy");
    }
}