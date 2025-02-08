<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // *************************PERMISSIONS******************************
        // Step 1: Create a temporary table to store the existing data
        Schema::create('temp_permissions', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('module_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('name')->nullable();
            $table->string('route')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->default(1);
            $table->unsignedInteger('updated_by')->default(1);
            $table->integer('type')->nullable()->comment('1 for main menu, 2 for sub menu, 3 action');
            $table->timestamps();
        });

        // Step 2: Copy the data from the original table to the temporary table
        DB::statement('INSERT INTO temp_permissions SELECT * FROM permissions');

        // Step 3: Drop the original table
        Schema::dropIfExists('permissions');

        // Step 4: Create the new schema for the original table
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->integer('module_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('route')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->default(1);
            $table->unsignedInteger('updated_by')->default(1);
            $table->integer('type')->nullable()->comment('1 for main menu, 2 for sub menu, 3 action');
            $table->timestamps();
        });


        // Step 5: Copy the data from the temporary table back to the new table
        DB::statement('
            INSERT INTO permissions (id, module_id, parent_id, name, route, status, created_by, updated_by, type, created_at, updated_at)
            SELECT id, module_id, parent_id, name, route, status, created_by, updated_by, type, created_at, updated_at FROM temp_permissions
        ');

        // Step 6: Drop the temporary table
        Schema::dropIfExists('temp_permissions');


        // *************************ROLES******************************
        // Step 1: Backup the existing data in roles table
        Schema::create('temp_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->float('price', 8, 2)->default(0.00);
            $table->float('quarterly_price', 8, 2)->default(0.00);
            $table->float('yearly_price', 8, 2)->default(0.00);
            $table->string('type', 191);
            $table->integer('no_cases')->default(0);
            $table->integer('status')->default(1);
            $table->string('details', 191)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Copy the data from roles to temp_roles
        DB::statement('INSERT INTO temp_roles SELECT * FROM roles');

        // Step 2: Drop the old roles table
        Schema::dropIfExists('roles');

        // Step 3: Create the new roles table schema
        Schema::create('roles', function (Blueprint $table) use ($teams, $columnNames) {
            //$table->engine('InnoDB');
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name')->default('web'); // For MyISAM use string('guard_name', 25);
            $table->float('price', 8, 2)->default(0.00);
            $table->float('quarterly_price', 8, 2)->default(0.00);
            $table->float('yearly_price', 8, 2)->default(0.00);
            $table->string('type', 191);
            $table->integer('no_cases')->default(0);
            $table->integer('status')->default(1);
            $table->string('details', 191)->nullable();

            $table->timestamps();
        });

        // Step 4: Reinsert the old data into the new roles table
        DB::statement('INSERT INTO roles (id, name, price, quarterly_price, yearly_price, type, no_cases, status, details, created_at, updated_at) SELECT id, name, price, quarterly_price, yearly_price, type, no_cases, status, details, created_at, updated_at FROM temp_roles');

        // Step 5: Drop the temporary table
        Schema::dropIfExists('temp_roles');


        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });
        // Step 2: Migrate old role information from users table to model_has_roles
        $roles = DB::table('roles')->pluck('id')->implode(',');
        $permissions = DB::table('permissions')->pluck('id')->implode(',');
        DB::statement('
            INSERT INTO model_has_roles (role_id, model_type, model_id)
            SELECT role_id, "App\\\\Models\\\\User", id FROM users WHERE role_id IS NOT NULL AND role_id IN (' . $roles . ')
        ');


        // *************************ROLE HAS PERMISSION******************************
        Schema::dropIfExists('role_has_permissions');
        // Step 1: Create the new role_has_permissions table schema
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->default(1);
            $table->unsignedInteger('updated_by')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('permission_id')
                ->references('id') // permission id
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id') // role id
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Step 2: Copy the data from role_permission to role_has_permissions
        DB::statement('
            INSERT INTO role_has_permissions (permission_id, role_id, status, created_by, updated_by, created_at, updated_at)
            SELECT permission_id, role_id, status, created_by, updated_by, created_at, updated_at FROM role_permission WHERE role_id in (' . $roles . ') AND permission_id in (' . $permissions . ')
        ');

        // Step 3: Drop the old role_permission table
        Schema::dropIfExists('role_permission');

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
