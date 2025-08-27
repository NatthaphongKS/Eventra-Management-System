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
        Schema::create('ems_position', function (Blueprint $table) {
            $table->id();
            $table->string('pst_name')->unique();
            $table->enum('pst_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_department', function (Blueprint $table) {
            $table->id();
            $table->string('dpm_name')->unique();
            $table->enum('dpm_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_team', function (Blueprint $table) {
            $table->id();
            $table->string('tm_name')->unique();
            $table->enum('tm_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_company', function (Blueprint $table) {
            $table->id();
            $table->string('com_name')->unique();
            $table->enum('com_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id')->unique();
            $table->foreignId('emp_company_id')->constrained('ems_company')->onDelete('cascade');
            $table->enum('emp_prefix',['นาย', 'นาง', 'นางสาว'])->default('นาย');
            $table->string('emp_firstname');
            $table->string('emp_lastname');
            $table->string('emp_nickname')->nullable();
            $table->string('emp_email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('emp_phone')->unique();
            $table->foreignId('emp_position_id')->constrained('ems_position')->onDelete('cascade');
            $table->foreignId('emp_department_id')->constrained('ems_department')->onDelete('cascade');
            $table->foreignId('emp_team_id')->constrained('ems_team')->onDelete('cascade');
            $table->string('emp_password');
            $table->enum('emp_permission', ['enabled', 'disabled'])->default('disabled');
            $table->date('emp_create_at')->useCurrent();
            $table->unsignedBigInteger('emp_create_by')->nullable();
            $table->enum('emp_delete_status', ['active', 'inactive'])->default('active');
            $table->date('emp_delete_at')->nullable();
            $table->unsignedBigInteger('emp_delete_by')->nullable();
            $table->rememberToken()->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ems_employees');
        Schema::dropIfExists('ems_position');
        Schema::dropIfExists('ems_department');
        Schema::dropIfExists('ems_company');
        Schema::dropIfExists('ems_team');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
