<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Stat;
use Illuminate\Support\Facades\DB;
class MainController extends Controller
{
    public function download(){
    	$last_stat = new Stat();
    	//$link = "https://guvm.mvd.ru/upload/expired-passports/list_of_expired_passports.csv.bz2";
    	$link = "https://kun.uz";
    	$file = file_get_contents($link);
    	if (!$file){
    			$file = file_get_contents($link);
    			if(!$file){
					$last_stat->save();
					return ['ok'=>false, "error"=>"File has not donwloaded :("];
    			}
    	}
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
    				$start = time();
    				$ic = 0;
				    while (($data = fgetcsv($handle, 100, ",")) !== FALSE) {
				        $datas[] = [
				        	'serie' => $data[0],
				        	'number' => $data[1]
				        ];
				        if(count($datas) > 10000){
				        	DB::table('passports')->insertOrIgnore($datas);
				        	$datas = [];
				        	$ic++;
				        	if($ic % 100 == 0 ){
				        		DB::table('ttest')->insert([intval(time()) - intval($start)]);
				        		// echo "10^6 records inserted in  ". ."  seconds\n";
				        		$start = time();
				        	}
				        }
				    }
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

    public function update(){
    	//get new dump from site then decode it to csv format and then update database;
    	if($this->download()['ok']){
    		die();
	    	$fname = 'dump/data.'.date("d.m.Y").".csv.bz2";
	    	$exists = Storage::disk('local')->exists($fname);
	    	if($exists){
	    		$content = Storage::get($fname);
	    		$dcontent = bzdecompress($content);
	    		foreach(str_getcsv($dcontent) as $row){
	    			var_dump($row);
	    			die();
	    		}
	    	}
    	}else{

    	}
    }

    public function search(Request $request){
    	if($request->has('series') && $request->has('number')){
    		return (DB::table('passports')->where('series', $request['series'])->where('number', $request['number'])->exists()) ? ['ok' => true, 'result'=> 'Exists'] : ['ok'=>false, 'error'=>'Not Exists'];
    		// DB::table('passports')->where
    	}else{
    		return view('search');
    	}
    	return true;
    }

    public function status(Request $request){
        $limit = $request['limit'] ?? 1000;
        $start = $request['start'] ?? 0;
        if(intval($limit) > 10000){
            $limit = 10000;
        }
        $records = DB::table('passports')->offset($start)->limit($limit)->get();
        return response()->json([
            'success'=>true, 
            'start'=>$start,
            'limit'=>$limit,
            'records'=>$records
        ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
