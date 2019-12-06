<?php
class Controller_Attack extends Controller_Template
{

	public function action_index()
	{
		$data['attacks'] = Model_Attack::find(array('order_by'=>array('id'=>'DESC'),'limit'=>'100'));

        $data['count'] = null ;
        $count = Model_Attack::count();
        if($count == null){
            $data['count'] = 0;
        }
        else{
            $data['count'] = $count;
        }

        $this->template->title = "Attacks";
		$this->template->content = View::forge('attack/index', $data);

	}

	public function action_wipe()
	{
		$query = DB::query('TRUNCATE TABLE attacks');
		if($query->execute()){

			
		}
			Session::set_flash('detected attacks deleted');
			Response::redirect('attack/index');

		 $this->template->title = "Wipe recorded attacks";
		$this->template->content = View::forge('test/test');
	}

	public function action_test($id = null)
	{
		return Response::forge(View::forge('dashtemp'));
	}


	public function action_view($id = null)
	{
		is_null($id) and Response::redirect('attack');

		$data['attack'] = Model_Attack::find_by_pk($id);

		$this->template->title = "Attack";
		$this->template->content = View::forge('attack/view', $data);

	}

	public function action_create()
	{
		if (Input::method() == 'POST')
		{
			$val = Model_Attack::validate('create');

			if ($val->run())
			{
				$attack = Model_Attack::forge(array(
					'ip' => Input::post('ip'),
					'count' => Input::post('count'),
				));

				if ($attack and $attack->save())
				{
					Session::set_flash('success', 'Added attack #'.$attack->id.'.');
					Response::redirect('attack');
				}
				else
				{
					Session::set_flash('error', 'Could not save attack.');
				}
			}
			else
			{
				Session::set_flash('error', $val->error());
			}
		}

		$this->template->title = "Attacks";
		$this->template->content = View::forge('attack/create');

	}

	public function action_testcsv(){

        //opening csv fro writing
        $attacks = DOCROOT . "/python/test.csv";

        $line = array('kudzai','muchanyerei','25');
        $handle = fopen($attacks, "a");

        fputcsv($handle, $line);

        fclose($handle);
        return Response::forge(View::forge('test/test'));
    }

    public function action_countrow(){


        $row = 1;

        $dataset = DOCROOT."python/attacks.csv";

        $data1['count'] = "";



        if (($handle = fopen($dataset, "r")) !== FALSE) {

           
            while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                $row ++ ; 
            }

            $data1['count'] = $row;
            Session::set_flash('success','Subsystem access : '.number_format($row).' Records');
           
              
        }


        $this->template->title = "Dataset";
		$this->template->content = View::forge('count/index');
     
    
    }

	public function action_audit($id = null)
	{
		is_null($id) and Response::redirect('attack');

		$attack = Model_Attack::find_one_by_id($id);

		$ip_address =  $attack->ip ; 

		$row = 1;

		$attacks = DOCROOT . "/python/attacks.csv";


		if (($handle = fopen($attacks, "r")) !== FALSE) {

			$attack_count = 0;
			$row = 0 ; 
			while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {

					if($data[0] == $ip_address){

						$attack_count++ ; 
					}
					$row ++ ; 
			}
			$data['ip'] = $ip_address ; 
			$data['count'] = $attack_count ; 
			$data['percentage']= $attack_count / $row*100 ; 

		//Debug::dump($data['percentage']);die;
		}


		$this->template->title = "Audit";
		$this->template->content = View::forge('attack/audit',$data);

	}

	public function action_delete($id = null)
	{
		if ($attack = Model_Attack::find_one_by_id($id))
		{
			$attack->delete();

			Session::set_flash('success', 'Deleted attack #'.$id);
		}

		else
		{
			Session::set_flash('error', 'Could not delete attack #'.$id);
		}

		Response::redirect('attack');

	}

}
