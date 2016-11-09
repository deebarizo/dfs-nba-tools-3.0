<?php namespace App\UseCases;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

class FileUploader {

    public function uploadDkPlayerPool($request, $date, $slate) {

    	$fileDirectory = 'files/dk_player_pools/';

        $fileName = $date.'-'.$slate.'.csv';

       	Input::file('csv')->move($fileDirectory, $fileName);    

        return $fileDirectory . $fileName;   
    }

    public function uploadDkOwnershipPercentages($request, $date, $slate) {

    	$fileDirectory = 'files/dk_ownership_percentages/';

        $fileName = $date.'-'.$slate.'.csv';

       	Input::file('csv')->move($fileDirectory, $fileName);    

        return $fileDirectory . $fileName;   
    }

}