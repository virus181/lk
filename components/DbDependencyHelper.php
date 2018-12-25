<?php
namespace app\components;

use yii\caching\DbDependency;
use yii\db\ActiveQuery;

class DbDependencyHelper
{
    /**
     * @param ActiveQuery $query
     * @return DbDependency
     */
    public static function generateDependency($query): DbDependency
    {
        $dependencyQuery = clone $query;
        $modelClass = $query->modelClass;
        $dependencyQuery->select(['MAX('.$modelClass::tableName().'.updated_at)']);
        $dependencySql = $dependencyQuery->createCommand()->getRawSql();
        $dependency = new DbDependency(['sql' => $dependencySql]);
        $dependency->reusable = true;
        return $dependency;
    }
}