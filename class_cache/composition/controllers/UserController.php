<?php Samara_Include('Controller', 'inc');
Samara_Include('Vars', 'inc');
Samara_Include('User', 'composition/domain');

class UserController extends Controller
{
	
	protected function components()
	{
		return Vars::UserID() ?
		'
				<component position="left-sidebar" weight="0" id="log-in">
					<title>Log Out</title>
					<form controller="user" action="logOut" id="log-out" title="Log Out">
					</form>
				</component>' : 
		'
				<component position="left-sidebar" weight="0" id="log-in">
					<title>Log In</title>
					<form controller="user" action="logIn" id="log-in" title="Log In">
						<control type="text" param="username" class="username" label="User Name" />
						<control type="password" param="password" class="password" label="Password" />
					</form>
				</component>';
	}
	
	protected function renderLogIn()
	{
		$user = new User(Controller::Param('username'), Controller::Param('password'));
		$result = Database::ExecuteQuery(Database::Select(User::ID())->Where(User::Name()->Equals($user->Name->Value), User::Password()->Equals($user->Password->Value))->Limit(1));
		if ($result)
		{
			Vars::UserID($result[0]['id']);
			Vars::UserID();
		}
		
		return '<text>Username: '.Controller::Param('username').' -- Password: '.Controller::Param('password').'</text>';
	}
	
	public function IsLoggedIn()
	{
		return Vars::UserID() !== null;
	}

	protected function renderLogOut()
	{
		Vars::UserID(null);
		return '<text>Username: '.Controller::Param('username').' -- Password: '.Controller::Param('password').'</text>';
	}
	
}
