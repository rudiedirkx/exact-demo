<?php

use Picqer\Financials\Exact;
use Picqer\Financials\Exact\PrintedSalesInvoice;
use Picqer\Financials\Exact\SalesInvoice;

require 'inc.bootstrap.php';

$connection = new Exact\Connection();
$connection->setRedirectUrl(EXACT_REDIRECT_URL);
$connection->setExactClientId(EXACT_CLIENT_ID);
$connection->setExactClientSecret(EXACT_CLIENT_SECRET);
$connection->setAuthorizationCode(@$_GET['code']);
$connection->setRefreshToken(@$_SESSION['exact_refresh_token']);
$connection->setAccessToken(@$_SESSION['exact_access_token']);
$connection->setTokenExpires(@$_SESSION['exact_token_expires']);
$connection->connect();

$_SESSION['exact_refresh_token'] = $connection->getRefreshToken();
$_SESSION['exact_access_token'] = $connection->getAccessToken();
$_SESSION['exact_token_expires'] = $connection->getTokenExpires();

if ( isset($_GET['code']) ) {
	header('Location: ./');
	exit;
}

if ( isset($_POST['invoice_desc'], $_POST['invoice_price']) ) {
	// Make invoice
	$salesInvoice = new SalesInvoice($connection);
	$salesInvoice->InvoiceTo = '79edf4a0-983b-4632-afaa-914946224911';
	$salesInvoice->OrderedBy = '79edf4a0-983b-4632-afaa-914946224911';
	$salesInvoice->YourRef = rand();
	$salesInvoice->SalesInvoiceLines = [[
		'Item' => 'b7dbe69e-66ae-4b97-a100-8a9ad193906f',
		'Description' => $_POST['invoice_desc'],
		'Quantity' => 1,
		'UnitPrice' => $_POST['invoice_price'],
	]];
	$salesInvoice->save();

	// Print and mail invoice
	$printedInvoice = new PrintedSalesInvoice($connection);
	$printedInvoice->InvoiceID = $salesInvoice->InvoiceID;
	$printedInvoice->SendEmailToCustomer = true;
	$printedInvoice->SenderEmailAddress = "from@example.com";
	// $printedInvoice->DocumentLayout = "401f3020-35cd-49a2-843a-d904df0c09ff";
	// $printedInvoice->ExtraText = "Some additional text";
	$printedInvoice->save();

	header('Location: ./');
	exit;
}

header('Content-type: text/html; charset=utf-8');

?>
<form method="post" action>
	<fieldset>
		<legend>New invoice</legend>
		<p>Desc: <input name="invoice_desc" /></p>
		<p>Price: <input name="invoice_price" /></p>
		<p><button>Save</button></p>
	</fieldset>
</form>
