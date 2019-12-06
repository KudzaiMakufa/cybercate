<?php 

Class Controller_Users extends Controller_Template{

    public function action_createuser()
	{
		Auth::create_user(
			'0777000001',
			'pass123',
			'admin@admin.com',
			1,
			array(
				'fullname' => 'Administrator',
			)
		);

		//$this->template->set_global('login', $login, false);
		$this->template->title = "Logins";
		$this->template->content = View::forge('login/edit');

	}
	public function action_changepass()
	{
		$oldpass = Input::post('oldpass');
		$newpass = Input::post('newpass');
		$password =  Input::post('password');
		if(Input::method() == 'POST'){

			if ($newpass != $password) {
				Session::set_flash('error' , 'Passwords did not match');
				
			}
			else{
				if(Auth::change_password($oldpass,$password)){
					Session::set_flash('success' , 'Password Updated ');
				}
				else{
					Session::set_flash('error' , 'Incorrect Current Password entered  ');
				}
			}			
		}

		$this->template->title = "CyberCate";
		$this->template->content = View::forge('login/change',null);

	}
}

