<?php

declare(strict_types=1);
include APPPATH . 'classes/vendor/autoload.php';

//namespace PhpmlExamples;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Dataset\CsvDataset;

class Controller_Pollution extends Controller
{
    public function action_index()
    {
        // Debug::dump(Controller_Pollution::test(3));die;
        $dataset = new CsvDataset(DOCROOT . "Datasets/attacks.csv", 2, false, ';');
        //$dataset = new CsvDataset(DOCROOT."Datasets/airtest.csv", 3, false, ';');
        foreach (range(1, 10) as $k) {
            $correct = 0;
            foreach ($dataset->getSamples() as $index => $sample) {
                $estimator = new KNearestNeighbors($k);
                //Debug::dump($sample);die;
                $estimator->train($other = Controller_Pollution::removeIndex($index, $dataset->getSamples()), Controller_Pollution::removeIndex($index, $dataset->getTargets()));

                //Debug::dump($dataset->getTargets()[$index]);die;
                $predicted = $estimator->predict([$sample]);

                // Debug::dump($predicted[0]);die;
                if($predicted[0] === $dataset->getTargets()[$index]) {

                    Debug::dump($predicted[0]);
                    die;
                    $correct++;
                }
            }

            //Debug::dump(sprintf('Accuracy (k=%s): %.02f%% correct: %s', $k, ($correct / count($dataset->getSamples())) * 100, $correct) . PHP_EOL);die;
            echo sprintf('Accuracy (k=%s): %.02f%% correct: %s', $k, ($correct / count($dataset->getSamples())) * 100, $correct) . PHP_EOL;
        }

        return Response::forge(View::forge('test/test'));
    }

    public function removeIndex($index, $array)
    {
        unset($array[$index]);
        return $array;
    }

    public function test($no)
    {

        return $no;
    }
}