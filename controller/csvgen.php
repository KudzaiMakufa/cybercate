<?php

class Controller_Csvgen extends Controller
{
    public function action_index()
    {


        $count = 0 ;
        $array = [];
        while($count < 50) {

            $attack = "" ;
            $countries = "" ;
            $id  = "";

            $random =  rand(0, 3);
            $randcountry =  rand(0, 6);
            $randid = rand(0,6);
            switch ($random){
                case 0:
                    $attack = "mitm";
                    break;
                case 1:
                    $attack = "packet sniffing";
                    break;
                case 2 :
                    $attack = "session hijack";
                    break;
                case 3 :
                    $attack = "sql injecion";
                    break;

            }

            switch ($randcountry){
                case 0:
                    $countries = "zimbabwe";
                    break;
                case 1:
                    $countries = "Malawi";
                    break;
                case 2 :
                    $countries = "South Africa";
                    break;
                case 3 :
                    $countries = "Tanzania";
                    break;
                case 4 :
                    $countries = "Gabon";
                    break;
                case 5 :
                    $countries = "Sudan";
                    break;
                case 6 :
                    $countries = "Namibia";
                    break;

            }

            $array[$count] = '' . $randid . ',' . $randcountry . ','.$attack;
            $count++;
        }

        $data = $array;

        $fp = fopen(DOCROOT."Datasets/attacks.csv", 'wb');

        foreach ($data as $line) {
            $val = explode(",", $line);
            fputcsv($fp, $val);
        }
        fclose($fp);
        return Response::forge(View::forge('test/test'));
    }
}