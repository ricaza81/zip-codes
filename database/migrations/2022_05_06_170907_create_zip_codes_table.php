<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zip_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("d_codigo");
            $table->string("d_asenta");
            $table->string("d_tipo_asenta");
            $table->string("d_mnpio");
            $table->string("d_estado");
            $table->string("d_ciudad")->nullable();

            $table->unsignedInteger("d_cp");
            $table->unsignedInteger("c_estado");
            $table->unsignedInteger("c_oficina");

            $table->string("c_cp")->nullable();
            $table->unsignedInteger("c_tipo_asenta");
            $table->unsignedInteger("c_mnpio");
            $table->unsignedInteger("id_asenta_cpcons");

            $table->string("d_zona")->nullable();
            $table->string("c_cve_ciudad")->nullable();

            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zip_codes');
    }
};
