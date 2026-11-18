<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait HasAuditFields
{
    /**
     * Boot le trait pour remplir automatiquement les colonnes d'audit.
     */
    public static function bootHasAuditFields(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by ??= Auth::id();
                $model->updated_by ??= Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // deleted_by / restoring n'ont de sens que pour les modèles en soft delete
        // (l'événement `restoring` n'existe pas sur les autres modèles).
        if (! static::usesSoftDeletes(static::class)) {
            return;
        }

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });

        static::restoring(function ($model) {
            $model->deleted_by = null;
        });
    }

    protected static function usesSoftDeletes($model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }
}
