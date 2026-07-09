<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('gender')->nullable()->after('last_name');
            $table->unsignedTinyInteger('age')->nullable()->after('gender');
            $table->date('birthdate')->nullable()->after('age');
            $table->string('disability')->nullable()->after('birthdate');
            $table->string('contact_number')->nullable()->after('disability');
            $table->string('street_address')->nullable()->after('contact_number');
            $table->string('city')->default('Dasmarinas')->after('street_address');
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('pwd_id_path')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'gender',
                'age',
                'birthdate',
                'disability',
                'contact_number',
                'street_address',
                'city',
                'latitude',
                'longitude',
                'pwd_id_path',
            ]);
        });
    }
};
