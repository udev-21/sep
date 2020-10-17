<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;
use App\Stat;
use Illuminate\Support\Facades\DB;
class ImportTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $this->update();
        return 0;
    }

    public function update(){
        DB::statement('truncate passports');
        $last_stat = new Stat();
        // $link = "https://guvm.mvd.ru/upload/expired-passports/list_of_expired_passports.csv.bz2";
        // $file = file_get_contents($link);
        // if (!$file){
        //         $file = file_get_contents($link);
        //         if(!$file){
        //             $last_stat->save();
        //             return ['ok'=>false, "error"=>"File has not donwloaded :("];
        //         }
        // }
        $last_stat->downloaded = true;
        $fname = "dump/data.".date("d.m.Y").".csv.bz2";
        //$res = Storage::put($fname, $file,'public');
        if($res = true){
            $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
            $path = $storagePath.$fname;
            //exec("bunzip2 -dk $path", $r, $code);
            $code = 0;
            if($code === 0){
                $last_stat->unzipped = true;
                if (($handle = fopen($storagePath."dump/data.".date("d.m.Y").".csv", "r")) !== FALSE) {
                    $datas  = [];
                    fgetcsv($handle,100,',');
                    $start = microtime(true);
                    $ic = 0;
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
                                // DB::table('ttest')->insert([intval(time()) - intval($start)]);
                                echo "250K records inserted in  ". (microtime(true) - $start) ."  seconds\n";
                                $start = microtime(true);
                            }
                        }
                    }
                    DB::commit();
                    
                    // dump($datas);
                    fclose($handle);
                    // $res = DB::table('passports')->insertOrIgnore($datas);
                    // dd($res);
                }

                echo "everything is ok;";
            }else{

            }
            $last_stat->save();
            return ['ok'=>true, "error"=>""];
        }
        $last_stat->save();
    }
}
