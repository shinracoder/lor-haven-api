<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->foreign('version')
                ->references('version')
                ->on('versions');
            $table->string('locale');
            $table->json('associatedCards')->nullable();
            $table->json('associatedCardRefs')->nullable();
            $table->json('assets')->nullable();
            $table->string('region')->nullable();
            $table->string('regionRef')->nullable();
            $table->string('attack')->nullable();
            $table->string('cost')->nullable();
            $table->string('health')->nullable();
            $table->string('description')->type('text')->nullable();
            $table->string('descriptionRaw')->type('text')->nullable();
            $table->string('levelupDescription')->type('text')->nullable();
            $table->string('levelupDescriptionRaw')->type('text')->nullable();
            $table->string('flavorText')->type('text')->nullable();
            $table->string('artistName')->nullable();
            $table->string('name')->nullable();
            $table->string('cardCode');
            $table->json('keywords')->nullable();
            $table->json('keywordRefs')->nullable();
            $table->string('spellSpeed')->nullable();
            $table->string('spellSpeedRef')->nullable();
            $table->string('rarity')->nullable();
            $table->string('rarityRef')->nullable();
            $table->string('subtype')->nullable();
            $table->json('subtypes')->nullable();
            $table->string('supertype')->nullable();
            $table->string('type')->nullable();
            $table->boolean('collectible')->nullable();
            $table->timestamps();
            $table->unique(['version', 'cardCode', 'locale']);
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
        Schema::dropIfExists('cards');
        Schema::enableForeignKeyConstraints();
    }
}
