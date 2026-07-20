<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deal_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->string('event');
            $table->string('correlation_id', 36)->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index('deal_id');
            $table->index('event');
            
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_events');
    }
};
