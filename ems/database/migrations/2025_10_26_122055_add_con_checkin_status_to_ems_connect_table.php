<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ems_connect', function (Blueprint $table) {
            $table->tinyInteger('con_checkin_status')
                  ->default(0)
                  ->comment('0 = ยังไม่เช็กอิน, 1 = เช็กอินแล้ว')
                  ->after('con_delete_status');
        });
    }

    public function down(): void
    {
        Schema::table('ems_connect', function (Blueprint $table) {
            $table->dropColumn('con_checkin_status');
        });
    }
};
