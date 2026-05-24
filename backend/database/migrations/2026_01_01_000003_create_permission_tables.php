<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spatie laravel-permission v6 standard schema.
 * If you regenerate via `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`,
 * delete this file first.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams       = config('permission.teams');
        $pivotRole       = $columnNames['role_pivot_key']       ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        Schema::create($tableNames['permissions'], function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('name');
            $t->string('guard_name');
            $t->timestamps();
            $t->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $t) use ($teams, $columnNames) {
            $t->bigIncrements('id');
            if ($teams || config('permission.testing')) {
                $t->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $t->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $t->string('name');
            $t->string('guard_name');
            $t->timestamps();
            if ($teams || config('permission.testing')) {
                $t->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $t->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $t) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $t->unsignedBigInteger($pivotPermission);
            $t->string('model_type');
            $t->unsignedBigInteger($columnNames['model_morph_key']);
            $t->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $t->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            if ($teams) {
                $t->unsignedBigInteger($columnNames['team_foreign_key']);
                $t->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                $t->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            } else {
                $t->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $t) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $t->unsignedBigInteger($pivotRole);
            $t->string('model_type');
            $t->unsignedBigInteger($columnNames['model_morph_key']);
            $t->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $t->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            if ($teams) {
                $t->unsignedBigInteger($columnNames['team_foreign_key']);
                $t->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $t->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            } else {
                $t->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $t) use ($tableNames, $pivotRole, $pivotPermission) {
            $t->unsignedBigInteger($pivotPermission);
            $t->unsignedBigInteger($pivotRole);
            $t->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $t->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $t->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
