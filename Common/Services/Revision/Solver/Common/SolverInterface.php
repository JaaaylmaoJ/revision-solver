<?php

namespace Common\Services\Revision\Solver\Common;

use yii\db\Exception;

interface SolverInterface
{
    /**
     * @param array|RevisionSolverConfig $config
     *
     * @throws Exception
     */
    public function solve($config);
}