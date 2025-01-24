<?php

global $reports, $dim;
			//Looks like it searches for file rep_bad_allocations...
$reports->addReport(RC_CUSTOMER,"_bad_allocations",_('Customer Allocations with an Error'),
	array(	_('Date') => 'DATE',
	//		_('Inventory Category') => 'CATEGORIES',
	//		_('Location') => 'LOCATIONS',
			_('Comments') => 'TEXTBOX',
			_('Destination') => 'DESTINATION'));				
?>
