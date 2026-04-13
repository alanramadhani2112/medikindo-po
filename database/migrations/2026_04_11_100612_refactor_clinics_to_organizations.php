<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename table and add 'type'
        if (!Schema::hasTable('organizations') && Schema::hasTable('clinics')) {
            Schema::rename('clinics', 'organizations');
        }
        
        if (!Schema::hasColumn('organizations', 'type')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->string('type', 50)->default('clinic')->after('name');
            });
        }

        // 2. Rename foreign keys in users
        if (Schema::hasColumn('users', 'clinic_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });
        }

        // 3. Rename foreign keys in purchase_orders
        if (Schema::hasColumn('purchase_orders', 'clinic_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            });
        }

        // 4. Rename foreign keys in audit_logs
        if (Schema::hasColumn('audit_logs', 'clinic_id')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });
        }

        // 5. Rename foreign keys in supplier_invoices
        if (Schema::hasColumn('supplier_invoices', 'clinic_id')) {
            Schema::table('supplier_invoices', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            });
        }

        // 5b. Rename foreign keys in customer_invoices
        if (Schema::hasColumn('customer_invoices', 'clinic_id')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->restrictOnDelete();
            });
        }

        // 6. Rename foreign keys in payments
        if (Schema::hasColumn('payments', 'clinic_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });
        }

        // 7. Rename foreign keys in credit_limits
        if (Schema::hasColumn('credit_limits', 'clinic_id')) {
            Schema::table('credit_limits', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                // When dropping a constrained column that's also part of an index or unique constraint, we have to drop the unique constraint first.
                $table->dropUnique(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
                $table->unique('organization_id');
            });
        }

        // 8. Rename foreign keys in credit_usages
        if (Schema::hasColumn('credit_usages', 'clinic_id')) {
            Schema::table('credit_usages', function (Blueprint $table) {
                $table->dropForeign(['clinic_id']);
                $table->renameColumn('clinic_id', 'organization_id');
                $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert credit_usages
        Schema::table('credit_usages', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->cascadeOnDelete();
        });

        // Revert credit_limits
        Schema::table('credit_limits', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropUnique(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->cascadeOnDelete();
            $table->unique('clinic_id');
        });

        // Revert payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
        });

        // Revert customer_invoices
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->restrictOnDelete();
        });

        // Revert supplier_invoices
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->restrictOnDelete();
        });

        // Revert audit_logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
        });

        // Revert purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->restrictOnDelete();
        });

        // Revert users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'clinic_id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
        });

        // Revert table name and drop type
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::rename('organizations', 'clinics');
    }
};
