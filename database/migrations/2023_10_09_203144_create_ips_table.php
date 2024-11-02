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
        Schema::create('ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip')->unique();
            $table->string('continent_code');
            $table->string('continent_name');
            $table->string('country_code2');
            $table->string('country_code3');
            $table->string('country_name');
            $table->string('country_name_official');
            $table->string('country_capital');
            $table->string('state_prov');
            $table->string('district');
            $table->string('city');
            $table->string('zipcode');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('is_eu');
            $table->string('calling_code');
            $table->string('isp');
            $table->string('country_tld');
            $table->string('languages');
            $table->string('country_flag');
            $table->string('geoname_id');
            $table->string('connection_type');
            $table->string('organization');
            $table->string('asn')->default('');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ips');
    }
};
