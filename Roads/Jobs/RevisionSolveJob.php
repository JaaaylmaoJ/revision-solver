<?php

namespace Roads\Jobs;

use Yii;
use Throwable;
use yii\db\Exception;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use Common\Models\Base\Revision;
use yii\base\InvalidConfigException;
use Common\Services\Contract\ContractServiceInterface;
use Common\Services\Revision\ServicesFactory\RevisionServicesFactoryInterface;

class RevisionSolveJob extends BaseObject implements JobInterface
{
    public ?int  $revisionId  = null;
    public ?bool $autoResolve = null;

    /**
     * @throws Exception
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function execute($queue): bool
    {
        $serviceLocator = Yii::createObject(RevisionServicesFactoryInterface::class);
        $revision       = Revision::findOne($this->revisionId);

        $serviceLocator->getSolver($revision)->solve([
            'revisionId'  => $this->revisionId,
            'autoResolve' => $this->autoResolve,
        ]);

        Yii::createObject(ContractServiceInterface::class)
            ->toHistoryTable($revision);

        return true;
    }
}