<?php Samara_Include('User', 'composition/domain');
Samara_Include('Controller', 'inc');
Samara_Include('UpdateManager', 'inc');

class UpdateController extends Controller
{
	
	public function renderDatabase()
	{
		$script = UpdateManager::CreateUpdateScript();
		if ($script !== FALSE)
		{
			if (Database::ExecuteQuery($script) === FALSE)
			{
				$message = 'Error updating database: '.Database::GetLastError()."<p>$script</p>";
			}
			else
			{
				$message = 'Successfully updated database';
			}
		}
		else
		{
			$message = 'Database is currently up to date';
		}
		return Theme::getView(array('message' => $message));
	}
	
	public function renderInstall()
	{
		$admin = new User('admin', 'admin');
		$admin->Save();
		return '<text>Installed</text>';
	}
	
}

