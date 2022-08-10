<?php

namespace App\Console\Commands;

use App\Models\ZipCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ZipCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zipcodes:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar Excel el Catálogo Nacional de Códigos Postales, es elaborado por Correos de México.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', '768M');
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes


        //$deleted = DB::table('zip_codes')->delete();
        //$this->info("Tabla de Zip codes eliminada [$deleted]");

        \DB::beginTransaction();
        try{

            $result =  $this->showMenu();
            if($result->valid){

                \DB::commit();
                $this->newLine();
                $this->info($result->message);
                $this->info("Base de Datos actualizada.");

                return Command::SUCCESS;

            }else{
                \DB::rollback();
                $this->error($result->message);
                return Command::FAILURE;
            }

        }catch(\Exception $e){

            \DB::rollback();
            $this->error($e->getMessage()." ".$e->getLine());
            $this->info("Ocurrio una Excepcion");
            return Command::FAILURE;

        }

    }


    private function showMenu()
    {

        $result = new \stdClass();
        $result->valid = false;
        $result->message = "Sin cambios";

        $this->info("= Importar  Códigos Postales de México ==");
        $this->info('Seleccione archivo a importar, por tamaño se dividio en archivos.');


        $this->info('1.- Aguas a Coahuila (A-C)');
        $this->info('2.- Colima a Chiapas (C-C)');
        $this->info('3.- Chihuahua (C)');
        $this->info('4.- Distrito F. Durango (D-D) ');
        $this->info('5.- Guanajato Mexico (G-M)');
        $this->info('6.- Michoacan oaxaca (M-O)');
        $this->info('7.- Puebla Sinaloa (P-S)');
        $this->info('8.- Sonora Zacatecas (S-Z)');

        $this->line('----- OTROS ----');
        $this->info('100.- Borrar todos los codigos postales');

        $opcion = (int) $this->ask('Ingrese el nro de opcion a importar:');

        if($opcion>=1 and $opcion<=8){
            $result = $this->importarExcel($opcion);
            return $result;
        }else if($opcion==100){
            $deleted = DB::table('zip_codes')->delete();
            $this->info("Tabla de Zip codes eliminada [$deleted]");
            $result->valid = true;
            $result->message = "Se eliminaron todos los registros de codigos zip.";
            return $result;
        }

        return $result;
    }


    protected function importarExcel($opcion){

        $result = new \stdClass();
        $result->valid = false;
        $result->message = "No se importaron los registros de Codigos Zip.";


        switch ($opcion) {
            case 1:
                $file_path = "zip_codes/01_aguas_coahuila.xls";
                break;
            case 2:
                $file_path = "zip_codes/02_colima_chiapas.xls";
                break;
            case 3:
                $file_path = "zip_codes/03_chihuahua.xls";
                break;
            case 4:
                $file_path = "zip_codes/04_df_durango.xls";
                break;
            case 5:
                $file_path = "zip_codes/05_guanato_mexico.xls";
                break;
            case 6:
                $file_path = "zip_codes/06_michoacan_oaxaca.xls";
                break;
            case 7:
                $file_path = "zip_codes/07_puebla_sinaloa.xls";
                break;
            case 8:
                $file_path = "zip_codes/08_sonora_zacatecas.xls";
                break;
        }

        $this->info("Importando opcion[$opcion]: $file_path ...");

        $data  =  Excel::toCollection(new  \App\Imports\ZipCodesImport(), public_path($file_path) );

        if(isset($data)){

            $cant=0;

            foreach ($data as $sheet){

                $cant_registros = $sheet->count();
                $info = $sheet->first();
                $estado  = $info["d_estado"];

                $this->newLine();
                $this->line("Estado '$estado' Zip codes:$cant_registros.");

                $bar = $this->output->createProgressBar($cant_registros);

                $reg=0;

                foreach ($sheet as $line){

                    $line = (object) $line;

                    $zip_code = new ZipCode();
                    $zip_code->d_codigo = $line["d_codigo"];
                    $zip_code->d_asenta = $line["d_asenta"];
                    $zip_code->d_tipo_asenta = $line["d_tipo_asenta"];
                    $zip_code->d_mnpio = $line["d_mnpio"];
                    $zip_code->d_estado = $line["d_estado"];
                    $zip_code->d_ciudad = $line["d_ciudad"];

                    $zip_code->d_cp = $line["d_cp"];
                    $zip_code->c_estado = $line["c_estado"];
                    $zip_code->c_oficina = $line["c_oficina"];

                    $zip_code->c_cp = $line["c_cp"];
                    $zip_code->c_tipo_asenta = $line["c_tipo_asenta"];
                    $zip_code->c_mnpio = $line["c_mnpio"];

                    $zip_code->id_asenta_cpcons = $line["id_asenta_cpcons"];
                    $zip_code->d_zona = $line["d_zona"];
                    $zip_code->c_cve_ciudad = isset($line["c_cve_ciudad"])?$line["c_cve_ciudad"]:null;

                    $zip_code->save();

                    $cant++; $reg++;

                    $bar->advance();
                }

                $bar->finish();

            }

        }

        $result->valid = $cant>0?true:false;
        $result->message ="$cant registros importados correctamente.";
        return $result;

    }
}
