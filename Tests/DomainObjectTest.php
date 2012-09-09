<?php

require_once 'Samara_TestCase.php';

class DomainObjectTest extends Samara_TestCase
{

	public function testNativeName()
	{
		$tests = array(
					'User' => 'user',
					'BicycleRider' => 'bicycle_rider',
					'AUser' => 'a_user',
					'YMCAMembership' => 'ymca_membership',
					'Some1700sCoins' => 'some_1700s_coins',
					'ClassABC' => 'class_abc',
					'ArabicABCs' => 'arabic_abcs',
					'MembershipToYMCA' => 'membership_to_ymca',
					'PowersOf2Calculator' => 'powers_of_2_calculator',
					'NoticeToAUser' => 'notice_to_a_user'
				);
		
		Samara_Include('DomainObject', 'inc');
		
		foreach ($tests as $input => $output)
		{
			eval('namespace '.$this->namespace.'; class '.$input.' extends DomainObject {}');
			$class = $this->namespace.'\\'.$input;
			$actual = $class::NativeName();
			$this->assertEquals($output, $actual, 'Expected: \''.$output.'\', Actual: \''.$actual.'\'');
		}
	}

}

