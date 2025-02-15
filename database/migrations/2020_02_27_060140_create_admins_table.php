<?php
            use Illuminate\Support\Facades\Schema;
            use Illuminate\Database\Schema\Blueprint;
            use Illuminate\Database\Migrations\Migration;
            
            class CreateAdminsTable extends Migration
            {
                /**
                 * Run the migrations.
                 *
                 * @return void
                 */
                public function up()
                {
                    Schema::create("admins", function (Blueprint $table) {
						$table->increments('id');
						$table->string('email')->nullable();
						$table->string('password')->nullable();
						$table->timestamps();
						$table->softDeletes();

                    });
                }
    
                /**
                 * Reverse the migrations.
                 *
                 * @return void
                 */
                public function down()
                {
                    Schema::dropIfExists("admins");
                }
            }
        