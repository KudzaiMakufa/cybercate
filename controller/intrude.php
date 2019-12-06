<?php


class Controller_Intrude extends Controller_Template
{

	public function action_index()
	{
		$data['intrudes'] = Model_Intrude::find_all();
		$this->template->title = "Intrudes";
		$this->template->content = View::forge('intrude/index', $data);

	}
	public function action_view($id = null)
	{
		is_null($id) and Response::redirect('intrude');

		$data['intrude'] = Model_Intrude::find_by_pk($id);

		$this->template->title = "Intrude";
		$this->template->content = View::forge('intrude/view', $data);

	}

	public function action_preg($id = null)
	{
		$subject =  'HTTP_CLIENT_IP' ; 
		
		if(preg_match('[a-zA-Z0-9]{5})',$subject)){
			echo 1234;
		}else{
			echo 54321;
		}
		$this->template->title = "Intrude";
		$this->template->content = View::forge('test/test', $data);

	}

    public function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }


	public function action_create()
	{
		if (Input::method() == 'POST')
		{


			$val = Model_Intrude::validate('create');


			if ($val->run())
			{

				$count = 0 ;
				$change =  true ;
			    while ($count <= 1) {
                    $attacks = DOCROOT . "/python/attacks.csv";


                    // //random string value for packet
					// $packet = RAND(1, 40);
					


                    //random string value for asn
                    $asn = RAND(1, 64511);
                

                    //random string value for flags
                    $flag = Rand(1, 3);
                    $flags = "";
                    switch ($flag) {
                        case 1:
                            $flags = "-AP---";
                            break ;
                        case 2:
                            $flags = "-A---";
                            break ;
                        case 3:
                            $flags = '"-A----,-AP---"' ;
                            break ;
					}
					
					

					$packet = Input::post('num_packets') ;
					//random string value for num_packet
					$num_bytes = Input::post('num_byte');
					


					//ip docking
					$source_ip = "" ;
					$destination_ip = "" ; 



					if($change){

					
						$source_ip = Input::post('source_ip');
						$destination_ip = Input::post('destination_ip');
						$change = false ;

						
					}
					else{
						$destination_ip = Input::post('source_ip');
						$source_ip = Input::post('destination_ip');
						$packet =  round($packet/2) ;
						$num_bytes = round($num_bytes/2) ;
						$change = true ;
					}

                    //random string value for destination port

                    //when running under server use this for ip address Controller_Intrude::get_client_ip()
                    $line = array(
                        'source_ip' => $source_ip,
                        'destination_ip' => $destination_ip,
                        'start_time' => time(),
                        'source_port' => Input::post('source_port'),
                        'destination_port' => Input::post('destination_port'),
                        'flags' => $flags,
                        'site' => '45c48',
                        'asn' => $asn,
                        'num_packets' => $packet,
                        'num_bytes' => $num_bytes,
                    );

                    $handle = fopen($attacks, "a");

                    fputcsv($handle, $line);

                    fclose($handle);
					$count++ ;
					sleep(2);
					
				}
				
				Session::set_flash('success','repeated attack perfomed on sub systems with count '.$count);
				Response::redirect('attack');


			}
			else
			{
				Session::set_flash('error', $val->error());
			}
		}

		$this->template->title = "Intrudes";
		$this->template->content = View::forge('intrude/create');

	}

	public function action_edit($id = null)
	{
		is_null($id) and Response::redirect('intrude');

		$intrude = Model_Intrude::find_one_by_id($id);

		if (Input::method() == 'POST')
		{
			$val = Model_Intrude::validate('edit');

			if ($val->run())
			{
				$intrude->source_ip = Input::post('source_ip');
				$intrude->destination_ip = Input::post('destination_ip');
				$intrude->start_time = Input::post('start_time');
				$intrude->source_port = Input::post('source_port');
				$intrude->destination_port = Input::post('destination_port');
				$intrude->flags = Input::post('flags');
				$intrude->site = Input::post('site');
				$intrude->asn = Input::post('asn');
				$intrude->num_packets = Input::post('num_packets');
				$intrude->num_bytes = Input::post('num_bytes');

				if ($intrude->save())
				{
					Session::set_flash('success', 'Updated intrude #'.$id);
					Response::redirect('intrude');
				}
				else
				{
					Session::set_flash('error', 'Nothing updated.');
				}
			}
			else
			{
				Session::set_flash('error', $val->error());
			}
		}

		$this->template->set_global('intrude', $intrude, false);
		$this->template->title = "Intrudes";
		$this->template->content = View::forge('intrude/edit');

	}

	public function action_delete($id = null)
	{
		if ($intrude = Model_Intrude::find_one_by_id($id))
		{
			$intrude->delete();

			Session::set_flash('success', 'Deleted intrude #'.$id);
		}

		else
		{
			Session::set_flash('error', 'Could not delete intrude #'.$id);
		}

		Response::redirect('intrude');

	}

}
