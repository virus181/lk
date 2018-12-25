<?php

namespace app\workflow;

use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\WorkflowException;

class WorkflowHelper extends \raoul2000\workflow\helpers\WorkflowHelper
{
    public static function getNextTransitionListData($model)
    {
        if (!SimpleWorkflowBehavior::isAttachedTo($model)) {
            throw new WorkflowException('The model does not have a SimpleWorkflowBehavior behavior');
        }

        $data = [];

        /** @var Transition[] $transitions */
        $transitions = $model->getWorkflowStatus()->getTransitions();
        foreach ($transitions as $status => $transition) {
            $status = explode('/', $status)[1];
            foreach ($transition->getMetadata() as $key => $value) {
                $data[$status][$key] = $value;
            }
        }

        return $data;
    }

    public static function getLabel($model)
    {
        if ($model->status) {
            return $model->getWorkflowStatus()->getLabel();
        }

        return '';
    }
}