<?php

/*
 * Kudzai Makufa
 *
 * kidkudzy@gmail.com
 *
 *
 *
 */

declare(strict_types=1);

//namespace PhpmlExamples;


include APPPATH.'classes/vendor/autoload.php';
use Phpml\Clustering\KMeans;
use Phpml\Dataset\CsvDataset;
use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Metric\Accuracy;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

class Controller_Intrusion extends Controller
{

    public function action_index1()
    {
        // Our data set
        $kmeans = new KMeans(2);
        $kmeans = new KMeans(4, KMeans::INIT_RANDOM);

        $dataset = new CsvDataset(DOCROOT."Datasets/attacks.csv", 2, false, ';');


         $sample =   $dataset->getSamples();


            //$samples = [[1, 1], [8, 7], [1, 2], [7, 8], [2, 1], [8, 9]];

           // Or if you need to keep your indentifiers along with yours samples you can use array keys as labels.
           // $samples = [ 'Label1' => [1, 1], 'Label2' => [8, 7], 'Label3' => [1, 2]];

            $kmeans = new KMeans(2);

       /* $res = ['check'=> 'Exists'];

        $str = json_decode($res);
        echo $str ; */


            //cluster 0ne

        Debug::dump($kmeans->cluster($sample)[0]);
            //echo json_encode($kmeans->cluster($sample)[0]);

            /*echo json_encode($kmeans->cluster($sample)[1]);
            echo json_deod*/


            // return [0=>[[1, 1], ...], 1=>[[8, 7], ...]] or [0=>['Label1' => [1, 1], 'Label3' => [1, 2], ...], 1=>['Label2' => [8, 7], ...]]


        /*//temporarily alter the memory limit for such large dataset
        ini_set('memory_limit', '-1');

        echo 'Loading dataset...' . PHP_EOL;
        $dataset = new CsvDataset(DOCROOT."Datasets/spam.csv", 1);
        $vectorizer = new TokenCountVectorizer(new WordTokenizer());
        $tfIdfTransformer = new TfIdfTransformer();

        echo 'Extracting samples ...' . PHP_EOL;
        $samples = [];
        foreach ($dataset->getSamples() as $sample) {

           // Debug::dump($dataset->getSamples());die;
            $samples[] = $sample[0];
        }

        echo 'Vectorizing samples ...' . PHP_EOL;
        $vectorizer->fit($samples);
        $vectorizer->transform($samples);

        $tfIdfTransformer->fit($samples);
        $tfIdfTransformer->transform($samples);`
        $dataset = new ArrayDataset($samples, $dataset->getTargets());
        $randomSplit = new StratifiedRandomSplit($dataset, 0.1);

        echo 'Training model ...' . PHP_EOL;
        $classifier = new SVC(Kernel::RBF, 1000);`````````````````````````````````````````````````
        $classifier->train($randomSplit->getTrainSamples(), $randomSplit->getTrainLabels());

        echo 'Performing prediction ...' . PHP_EOL;
        $predictedLabels = $classifi    er->predict($randomSplit->getTestSamples());
       // Debug::dump($randomSplit->getTestLabels());die;

        echo 'Accuracy: '.Accuracy::score($randomSplit->getTestLabels(), $predictedLabels) . PHP_EOL;*/
        return Response::forge(View::forge('test/test'));
    }




    public function action_index(){



        $file =  false ;
        $count = 0 ;

        while($file == false) 
        {

            /*
                    Debug::dump($file);die;
            */


            putenv('PYTHONPATH=/usr/lib/python3.7/site-packages:');
            //$command = "";
            $command = escapeshellcmd(DOCROOT . "/python/script.py");

            $output = exec($command . ' 2>&1', $output, $ret);


            //Checking if there are no errors
           

            if ($ret != 0)
            {
                echo "error is " . $output;
            }
            else
            {

                
                $csvfile = File::exists(DOCROOT . "/python/system_generated_csv/" . $output . ".csv");
                 //Debug::dump($csvfile);die;

                if ($csvfile)
                {
                    $file = true;
                    //care to print file name from python?

                    /*
                     * taking results from python generated csv file with attacks
                     *
                     * */

                    $row = 1;

                    $attacks = DOCROOT . "/python/system_generated_csv/" . $output . ".csv";


                    if (($handle = fopen($attacks, "r")) !== FALSE) 
                    {

                        $new_attacks = 0;
                        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) 
                        {

                            /*
                             *
                             * The script shld run in real time !!!!!!!!!!!!!!!!!!!
                             *
                             * checking new attacks and blocking access
                             *
                             *
                             * */




                            if ($data[0] == "source_ip")
                            {
                                //removing headers from csv file by doing nothing
                            }
                            else 
                            {


                                //checking if an ip has been captured before

                                $ipcheck = Model_Attack::find_by_ip($data[0]);


                                if ($ipcheck === null) {

                                    $pen = Model_Attack::forge(array(
                                        'ip' => $data[0],
                                        'count' => '0',
                                        'cluster' => $data[1],
                                        'created_at' => time(),
                                        'updated_at' => time(),
                                    ));
                                    if($pen->save())
                                    {
                                        //adding up new attacks to count
                                        $new_attacks++;
                                    }
                                }
                                else 
                                {
                                }
                            }
                        }
                    }
                } 
                else 
                {
                    Session::set_flash('error', 'Error encountered when processing data , Restart !!!');
                }


                $count++;

                if ($count == 2) 
                {

                    //checking script for infity process   ! fuck this is needed
                    $test = "The process just got stuck in an infiity loop";
                    Debug::dump($test);
                    die;
                }

            }

        }

        //checking hacker activity attacks
        
        if($new_attacks > 0 ){
            Session::set_flash('success'," new intrusions attempts detected: Attack count = ".$new_attacks);
            Response::redirect('attack');
           

        }
        else if($new_attacks == 0 ){

            Session::set_flash('success',"No new intrusion attempts detected in the first loop run: Attack count = ".$new_attacks);
            Response::redirect('attack');
            

        }




        return Response::forge(View::forge('test/test'));
    }
}
