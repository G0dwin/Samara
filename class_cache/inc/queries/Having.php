<?php 

Samara_Include('Where', 'inc/queries');

class Having extends Where
{
	protected function WhereFormat()
	{
		return 'HAVING ';
	}
	
}

