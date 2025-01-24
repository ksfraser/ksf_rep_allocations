<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_ITEMSVALREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Stock Check Sheet
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/includes/db/manufacturing_db.inc");

//----------------------------------------------------------------------------------------------------

print_bad_allocations();

//function getTransactions($category, $location, $rep_date)
function getTransactions($rep_date)
{
/**
	$sql = "SELECT d.trans_no as transaction, d.debtor_no as debtor_no,";
	//$sql .= "e.name as debtor, ";
	$sql .= "d.ov_amount+d.ov_gst as totalamount, sum( c.amt) as totalalloc, d.alloc as debtalloc ";
	$sql .="FROM ".TB_PREF."debtor_trans d, ".TB_PREF."cust_allocations c";
	$sql .=",  ".TB_PREF."debtors_master e";
	$sql .= " WHERE d.type=10 and d.trans_no=c.trans_no_to ";
//$sql .=		"	AND d.debtor_no=e.debtor_no";
$sql .=         "GROUP BY d.trans_no,
			d.type,
			totamt,
			d.alloc
		HAVING SUM(c.amt) < d.alloc";
*/

	$sql = "SELECT d.trans_no as transaction, 
			d.debtor_no as debtor_no,
			e.name as debtor,
			d.ov_amount+d.ov_gst as totalamount, 
			sum( c.amt) as totalalloc, 
			d.alloc as debtalloc 
		FROM ".TB_PREF."debtor_trans d, 
			".TB_PREF."cust_allocations c,
			".TB_PREF."debtors_master e
		WHERE d.type=10 and d.trans_no=c.trans_no_to 
			AND d.debtor_no = e.debtor_no
		GROUP BY d.trans_no,
			d.type,
			totalamount,
			d.alloc
		HAVING SUM(c.amt) < d.alloc";
	//$sql .=",  ".TB_PREF."debtors_master e";
//$sql .=		"	AND d.debtor_no=e.debtor_no";

    return db_query($sql,"No transactions were returned");
}

//----------------------------------------------------------------------------------------------------

function print_bad_allocations()
{
    global $comp_path, $path_to_root, $pic_height;

	$rep_date = $_POST['PARAM_0'];
    	$comments = $_POST['PARAM_1'];
	$destination = $_POST['PARAM_2'];

    	//$category = $_POST['PARAM_1'];
    	//$location = $_POST['PARAM_2'];
    	//$comments = $_POST['PARAM_3'];
	//$destination = $_POST['PARAM_4'];

	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");
/****
	if ($category == ALL_NUMERIC)
		$category = 0;
	if ($category == 0)
		$cat = _('All');
	else
		$cat = get_category_name($category);

	if ($location == ALL_TEXT)
		$location = 'all';
	if ($location == 'all')
		$loc = _('All');
	else
		$loc = get_location_name($location);
***/
		$cat = _('All');
		$loc = _('All');
		
	$cols = array(0, 50, 125, 200, 300, 400, 500);
	$headers = array(_('Transaction'), _('Customer Number'), _('Customer'), _('Amount'), _('Allocated'), _('Allocated in CustAlloc'));
	$aligns = array('left',	'left', 'left',	'right', 'right', 'right');


    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Category'), 'from' => $cat, 'to' => ''),
    				    2 => array('text' => _('Location'), 'from' => $loc, 'to' => ''),
						2 => array('text' => _('Date'), 'from' => $rep_date, 'to' => '')
    				  );

	$user_comp = "";

    $rep = new FrontReport(_('Customer Allocations with Errors'), "DatedStockSheet", user_pagesize());

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$res = getTransactions(date2sql($rep_date));
	//$res = getTransactions($category, $location,date2sql($rep_date));
	$catt = '';
	while ($trans=db_fetch($res))
	{
/*
		if ($location == 'all')
			$loc_code = "";
		else
			$loc_code = $location;
*/

/*
		if ($catt != $trans['cat_description'])
		{
			if ($catt != '')
			{
				$rep->Line($rep->row - 2);
				$rep->NewLine(2, 3);
			}
			$rep->TextCol(0, 1, $trans['category_id']);
			$rep->TextCol(1, 2, $trans['cat_description']);
			$catt = $trans['cat_description'];
			$rep->NewLine();
		}
*/
		$rep->NewLine();
		//$dec = get_qty_dec($trans['stock_id']);
		$dec = 5;
		$rep->TextCol(0, 1, $trans['transaction']);
		$rep->TextCol(1, 2, $trans['debtor_no']);
		$rep->TextCol(2, 3, $trans['debtor']);
		$rep->AmountCol(3, 4, $trans['totalamount'], $dec);
		$rep->AmountCol(4, 5, $trans['totalalloc'], $dec);
		$rep->AmountCol(5, 6, $trans['debtalloc'], $dec);
		
	}
	$rep->Line($rep->row - 4);
	$rep->NewLine();
    $rep->End();
}

?>
