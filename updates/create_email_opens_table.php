<?php namespace Mja\Mail\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateEmailOpensTable extends Migration
{

    public function up()
    {
        Schema::create('mja_mail_email_opens', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('email_id');
            $table->string('ip_address');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mja_mail_email_opens');
    }

}
