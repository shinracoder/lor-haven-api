<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VocabTermTableCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vocab_terms', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->foreign('version')
                ->references('version')
                ->on('versions');
            $table->string('locale');
            $table->string('description')->type('text')->nullable();
            $table->string('name')->nullable();
            $table->string('nameRef');
            $table->unique(['version', 'nameRef', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vocab_terms');
        Schema::enableForeignKeyConstraints();
    }
}
