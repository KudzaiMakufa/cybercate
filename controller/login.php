<?php
class Controller_Login extends Controller_Template
{
	public $template =  'template_login' ;
	public function action_view($id = null)
	{
		is_null($id) and Response::redirect('login');

		$data['login'] = Model_Login::find_by_pk($id);

		$this->template->title = "Login";
		$this->template->content = View::forge('login/view', $data);

	}
	public function action_test()
	{
		$this->template->title = "Logins";
		$this->template->content = View::forge('login/index');
	}
	public function action_index()
	{
		if (Input::method() == 'POST')
		{

			
			$val = Model_Login::validate('create');

			if ($val->run())
			{
				

				if (Auth::login(Input::post('username_or_email'), Input::post('password')))
				{
					Response::redirect('attack');
				}
				else
				{
					Session::set_flash('error', 'Incorrect details provided.');
				}
			}
			else
			{
				Session::set_flash('error', $val->error());
			}
		}
		

		$this->template->title = "CyberCate";
		$this->template->content = View::forge('login/create');

	}

	
	

	public function action_logout()
	{
		Auth::logout();

		Response::redirect('login');

	}

}
