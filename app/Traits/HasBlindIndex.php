<?php

namespace App\Traits;

trait HasBlindIndex
{
    /**
     * Boot the trait and hook into saving event.
     */
    protected static function bootHasBlindIndex()
    {
        static::saving(function ($model) {
            foreach ($model->getBlindIndexFields() as $field => $searchField) {
                if ($model->isDirty($field)) {
                    $model->{$searchField} = static::generateBlindIndex($model->{$field});
                }
            }
        });
    }

    /**
     * Generate a deterministic hash for searching.
     */
    public static function generateBlindIndex($value)
    {
        if (empty($value)) return null;
        // Use a non-salted hash (SHA-256) for searchability. 
        // Note: For extreme security, use a Pepper from .env
        return hash('sha256', strtolower(trim($value)) . config('app.key'));
    }

    /**
     * Define which fields have blind indexes.
     */
    abstract public function getBlindIndexFields(): array;
}
