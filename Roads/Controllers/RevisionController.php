<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpUnused */

namespace Roads\Controllers;

use Yii;
use yii\rest\Controller;
use Roads\Jobs\RevisionSolveJob;
use Common\DI\Queue\RevisionQueueInterface;
use Common\Services\Revision\Searcher\RevisionSearcherInterface;
use Common\Services\Revision\Validator\RevisionValidatorInterface;

class RevisionController extends Controller
{
    public function __construct(
        $id,
        $module,
        private RevisionValidatorInterface $validator,
        private RevisionSearcherInterface $searcher,
        private RevisionQueueInterface $queue,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionCalc($id): array
    {
        $revision = $this->searcher->findRevision($id);

        $this->validator->validateLocked($revision);

        $job = Yii::createObject([
            'class'       => RevisionSolveJob::class,
            'revisionId'  => $revision->id,
            'autoResolve' => $this->request->get('auto', false)
        ]);

        $id = $this->queue->push($job);

        return ['id' => $id];
    }

}