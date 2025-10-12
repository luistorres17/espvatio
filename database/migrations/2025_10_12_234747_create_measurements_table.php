// database/migrations/xxxx_xx_xx_xxxxxx_create_measurements_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->timestamp('time');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->double('voltage')->nullable();
            $table->double('current')->nullable();
            $table->double('power')->nullable();
            $table->double('frequency')->nullable();

            // Definimos una clave primaria compuesta para optimizar las consultas
            $table->primary(['device_id', 'time']);
        });

        // Comando SQL nativo para convertir la tabla en una Hypertable
        // Particionamos los datos basados en la columna 'time'
        DB::statement("SELECT create_hypertable('measurements', 'time');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};