<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class ComputedModel extends Model
{

    public static function boot()
    {

        parent::boot();

        static::saved(function ($model) {
            static::afterModelSaved($model);
        });

        static::deleted(function ($model) {
            static::afterModelDeleted($model);
        });

        static::registerPivotEvents();
    }

    protected static function registerPivotEvents()
    {
        foreach (static::getRelationTrackedRelations() as $relation) {
            $relationInstance = (new static())->$relation();
            $relatedModel = $relationInstance->getRelated();
            foreach (['created', 'updated', 'deleted'] as $event) {
                $relatedModel::$event(function ($model) use ($relation, $event) {
                    $model->onPivotChanged($relation, $event, );

                });
            }
        }
        foreach (static::getPivotTrackedRelations() as $relation) {
            $relationInstance = (new static())->$relation();
            $relatedModel = $relationInstance->getRelated();
            foreach (['created', 'updated', 'deleted'] as $event) {
                $relatedModel::$event(function ($model) use ($relation, $event) {
                    $model->onPivotChanged($relation, $event, );
                });
            }
            $pivotModel = $relationInstance->getPivotClass();
            if (class_exists($pivotModel)) {
                foreach (['created', 'updated', 'deleted'] as $event) {
                    $pivotModel::$event(function ($pivot) use ($relation, $event) {
                        $pivot->pivotParent->onPivotChanged($relation, $event, );

                    });
                }
            }
        }
    }


    protected static function getPivotTrackedRelations(): array {
        return [];
    }
    protected static function getRelationTrackedRelations(): array {
        return [];
    }

    abstract public static function afterModelSaved($model);
    abstract public static function afterModelDeleted($model);
    public function onPivotChanged(string $relation, string $event){
        Log::debug("onPivotChanged ". $relation . " " . $event);
    }

}
