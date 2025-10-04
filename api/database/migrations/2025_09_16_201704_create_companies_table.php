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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('person_type', ['physical', 'legal'])->default('legal');
            $table->string('cnpj')->nullable();
            $table->string('cpf')->nullable();
            $table->string('responsible_name');
            $table->string('phone_1');
            $table->boolean('has_whatsapp_1')->default(false);
            $table->string('phone_2')->nullable();
            $table->boolean('has_whatsapp_2')->default(false);
            $table->foreignId('timezone_id')->nullable()->constrained('timezones');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
