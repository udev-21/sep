<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;
use App\Stat;
use Illuminate\Support\Facades\DB;
class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data {--debug=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importing data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $debug = (int)$this->option('debug') === 1;

        $this->update($debug);
        return 0;
    }

    public function update($debug){

        if($debug) {
            echo "Update has begun\n";
            echo "Downloading...\n";
        }
        DB::statement('truncate passports'); // if on table used unique key then comment this line
        // $last_stat = new Stat();
        $link = "https://guvm.mvd.ru/upload/expired-passports/list_of_expired_passports.csv.bz2";
        $file = file_get_contents($link);
        if (!$file){
                $file = file_get_contents($link);
                if(!$file){
                    // $last_stat->save();
                    return ['ok'=>false, "error"=>"File has not donwloaded :("];
                }
        }
        // $last_stat->downloaded = true;
        if($debug){
            echo "Downloading compeleted !\n";
        }
        $fname = "dump/data.".date("d.m.Y").".csv.bz2";
        $res = Storage::put($fname, $file,'public');
        if($res){
            if($debug){
                echo "File stored as $fname to storage/dump\n";
            }
            $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
            $path = $storagePath.$fname;
            if($debug){
                echo "Unzipping...\n";
            }
        
            exec("bunzip2 -d $path", $r, $code);
            $code = 0;
            if($code === 0){
                if($debug){
                    echo "Unzipping compeleted !\n";
                    echo "Reading data and updating DB...\n";
                }

                if (($handle = fopen($storagePath."dump/data.".date("d.m.Y").".csv", "r")) !== FALSE) {
                    $datas  = [];
                    fgetcsv($handle,100,',');
                    $start = microtime(true);
                    $ic = 0;
                    $icc = 1;
                    DB::beginTransaction();
                    while (($data = fgetcsv($handle, 100, ",")) !== FALSE) {
                        $datas[] = [
                            'series' => $data[0],
                            'number' => $data[1]
                        ];
                        if(count($datas) > 5000){
                            DB::table('passports')->insertOrIgnore($datas);
                            $datas = [];
                            $ic++;
                            if($ic % 50 == 0 ){
                                DB::commit();
                                DB::beginTransaction();
                                if($debug){
                                    echo "250K * $icc records inserted in  ". (microtime(true) - $start) ."  seconds\n";
                                }
                                $start = microtime(true);
                                $icc++;
                            }
                        }
                    }
                    $res = DB::table('passports')->insertOrIgnore($datas);
                    
                    DB::commit();

                    // dump($datas);
                    fclose($handle);
                    // dd($res);
                }
                if($debug)
                    echo "Successfully updated;";
            
                // echo "everything is ok;";
            }else{
                // here send message to admin about error with email or something like this
            }
        }
    }
}
