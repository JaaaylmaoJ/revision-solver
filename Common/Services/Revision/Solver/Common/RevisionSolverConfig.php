<?php

namespace Common\Services\Revision\Solver\Common;

use Common\Models\Base\Revision;
use yii\base\Model;

class RevisionSolverConfig extends Model
{
    public ?int  $revisionId  = null;
    public ?bool $autoResolve = null;

    public function rules(): array
    {
        return [
            ['revisionId', 'required'],
            ['revisionId', 'integer'],
            [
                'revisionId',
                'exist',
                'targetClass'     => Revision::class,
                'targetAttribute' => 'id',
            ],

            ['autoResolve', 'boolean']
        ];
    }
}