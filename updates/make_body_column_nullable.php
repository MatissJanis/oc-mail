<?php namespace Mja\Mail\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class MakeBodyColumnNullable extends Migration
{

    public function up()
    {
        Schema::table('mja_mail_email_log', function ($table) {
            $table->text('body')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('mja_mail_email_log', function ($table) {
            $table->text('body')->nullable(false)->change();
        });
    }

}
