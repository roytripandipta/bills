<?php
//call the FPDF library

require ('fpdf17/fpdf.php');
// require_once ("vendor/autoload.php");
// \Dotenv\Dotenv::createImmutable(paths:__DIR__)->load();

// echo var_dump($_ENV);

// $db = $_ENV["DATABASE"];
// $host = $arr['HOST'];
// $password = $_ENV["PASSWORD"];
// $user = $_ENV["USER_NAME"];

$db = "aspiredb";
$host = "13.126.97.63";
$password = "6r8y7dZs/j";
$user = "tripan";

// echo $user;

// echo "host"." ". $host;

// $db = getenv("DATABASE");
// $host = getenv("HOST");
// $password = getenv("PASSWORD");
// $user = getenv("USER_NAME");

// echo $db;

$conn = mysqli_connect($host, $user, $password, $db);
if($conn-> connect_error) {
    die("Connection failed:".$conn-> connect_error);
    }

// A4 width : 219mm
// default margin : 10mm each side
// writable horizontal : 219-(10*2)=189mm

// create pdf object
$phone_number = isset($_GET['phone_number']) ? $_GET['phone_number'] : "";
$billing_month = isset($_GET['billing_month']) ? $_GET['billing_month'] : "";
$billing_year = isset($_GET['billing_year']) ? $_GET['billing_year'] : "";
if($phone_number == ""){
	exit("please enter a phone number");
}
if($billing_month == ""){
	exit("please select a month");
}
if($billing_year == ""){
	exit("please select a year");
}
$pdf = new FPDF('P','mm','A4');
//add new page
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Image('ASPIRE.png',$pdf->GetX(),$pdf->GetY(), 35, 15);
//Cell(width , height , text , border , end line , [align] )
$pdf->SetFont('Arial','B',15);
$pdf->Cell(190 ,5,'Aspire Digital Credit Card',0,1,'C');
$pdf->Cell(59 ,5,' ',0,1);//end of line
$pdf->Cell(190 ,5,'April 2021 Statement',0,1,'C');//end of line
$pdf->SetFont('Arial','B',11);

$sql = "select id from user where phone_number = '{$phone_number}' limit 1";
$res = $conn->query($sql);
$id = "";
if($res->num_rows > 0){
	while($row = $res->fetch_assoc()){
		$id = $row['id'];
	}
}
else{
	exit('Phone Number not found');
}

// $id = "ab2f3e3f-c202-464a-8292-9e3550ebe8ff";
$sql = "select u.user_name, u.phone_number, uad.address,uad.pin_code from user u,user_address uad where u.id = uad.user_id and u.id = '{$id}' limit 1";
$result = $conn->query($sql);
$user_name = "";
$sanction_amount = 0;
$available_limit = 0;
$due_emi_this_month = 0;
$monthly_fee = 0;
$monthly_tax = 0;
$late_fee = 0;
$late_tax = 0;
$DPD = 0;
$payment_min = 0;
$payment_max = 0;
$amount_carry_forward = 0;
$link = "";

$sql_2 = "select * from test_bills where user_id = '{$id}' and billing_month = '{$billing_month}' and billing_year = {$billing_year}";
$result2 = $conn->query($sql_2);
if($result2 -> num_rows > 0) {
	while($row = $result2->fetch_assoc()) {
		$user_name = $row["user_name"];
		$sanction_amount = $row["sanction_amount"];
		$available_limit = $row["available_limit"];
		$due_emi_this_month = $row["due_emi_this_month"];
		$monthly_fee = $row["monthly_fee"];
		$monthly_tax = $row["monthly_tax"];
		$late_fee = $row["late_fee"];
		$late_tax = $row["late_tax"];
		$DPD = $row["DPD"];
		$payment_min = $row["payment_min"];
		$payment_max = $row["payment_max"];
		$amount_carry_forward = $row["amount_carry_forward"];
		$link = $row["payment_link"];
	}
}

if($result-> num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pdf->Cell(59 ,10,' ',0,1);//end of line
		$pdf->Cell(100, 5, 'To,',0,1);
		$pdf->Cell(59 ,5,' ',0,1);//end of line
		$pdf->SetFont('Arial','B',11);
		$pdf->Cell(120,5,$row['user_name'],0,0);
		// $pdf->Cell(120,5,$billing_year,0,0);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x, $y - 5);
		$pdf->SetFont('Arial','B',15);
		$pdf->Cell(60,5,'Due Amount: '.$pdf->Image('inr.jpg',$pdf->GetX()+35,$pdf->GetY(), 3, 3).'  '.number_format($payment_min,2,'.',','), 0, 1);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x, $y + 5);
		$pdf->SetFont('Arial','B',11);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->MultiCell(100,8,trim($row['address']).' '.$row['pin_code'],0,0);
		$pdf->SetXY($x + 120, $y);
		$pdf->Cell(60,5,'Due Date: 8 May 2021', 0, 1);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x + 120, $y);
		$pdf->MultiCell(60,5,'Late Charges:  300 + GST for payments after 8 May 2021',0,1);
		$pdf->Cell(120,10,'Phone: '.$row['phone_number'],0,0);
		// $link = 'https://stackoverflow.com/questions/54644886/insert-a-url-inside-a-cell-in-fpdf';
		// $pdf->SetFont('Arial','U',20);
		$pdf->SetFont('Arial','BU',20);
		$pdf->SetTextColor(194,8,8);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x, $y+4);
		$pdf->Cell(45,12,'Pay Now','0','1','C',false, $link);

		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('Arial','B',11);

		$pdf->Cell(59 ,10,' ',0,1);//end of line

		$pdf->Cell(120, 10, 'Summary of Charges', 0, 1);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->Line($x,$y,$x + $x+120, $y);
		$pdf->Cell(59 ,8,' ',0,1);//end of line
		$pdf->Cell(120,8, 'Due EMI this month', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($due_emi_this_month,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Monthly Membership Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($monthly_fee,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'GST on Membership Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($monthly_tax,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Late Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($late_fee,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'GST on Late Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($late_tax,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Excess Payment Carry Forward', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($amount_carry_forward,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Net Due', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($payment_min,2,'.',','), 1, 1,'C');

		$pdf->Cell(59 ,8,' ',0,1);//end of line

		$pdf->Cell(120,5,'Credit Line Standing as on 30th April',0,1);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->Line($x,$y+2,$x+120, $y+2);
		$pdf->Cell(59 ,8,' ',0,1);//end of line

		$pdf->Cell(60,8,'Total Line Limit', 1, 0);
		$pdf->Cell(60,8,'Available Limit', 1, 0);
		$pdf->Cell(60,8,'DPD', 1, 1);

		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+2,$pdf->GetY()+2, 3, 3).'     '.number_format($sanction_amount,2,'.',','), 1, 0,);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+2,$pdf->GetY()+2, 3, 3).'     '.number_format($available_limit,2,'.',','), 1, 0,);
		$pdf->Cell(60,8, $DPD ,1, 1,);
    }
}

// $pdf->Cell(10,5,number_format($amt,2,'.',','), 0,1);

$pdf->Cell(60,8,' ', 0, 1);

if($DPD == 0)
{
	$pdf->MultiCell(180,8,'Congratulations! on improving your credit score (all bureaus including CIBIL) with on time payment before or on 8th May.',1,1);
}else {
	$pdf->MultiCell(180,8,'New year brings new hope! Please pay your dues to make the new year financially happy!',1,1);
}

$pdf->Cell(60,10,' ', 0, 1);
$pdf->Cell(60,10,'Transaction History', 0,1);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+120, $y);
$pdf->Cell(59 ,5,' ',0,1);//end of line
$pdf->Cell(40,8,'Created Time', 1, 0);
$pdf->Cell(90,8,'Merchant Name', 1, 0);
$pdf->Cell(50,8,'Amount', 1, 1);

$sql = "SELECT 
      SUBSTR(m.created_time, 1, 10) as created_time,
      m1.merchant_name,
      m.amount
     
  FROM
      merchant_payments AS m
          LEFT JOIN
      merchant_details AS m1 ON m.merchant_id = m1.merchant_id
  WHERE
      m.user_id = '{$id}'
          AND m.`status` = 'success';";
$result1 = $conn->query($sql);

if($result1-> num_rows > 0) {
    while($row = $result1->fetch_assoc()) {
        $pdf->Cell(40,8,$row['created_time'],1,0);
        $pdf->Cell(90,8,substr($row['merchant_name'],0,40), 1, 0);
        $pdf->Cell(50,8,number_format($row['amount'],2,'.',','),1,1);
  
    }
}


$pdf->Cell(60,10,' ', 0, 1);
$pdf->Cell(60,10,'Payment History', 0,1);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+120, $y);
$pdf->Cell(59 ,5,' ',0,1);//end of line
$pdf->Cell(60,8,'Payment Time', 1, 0);
$pdf->Cell(60,8,'Amount', 1, 1);

$payment_time = "2021-05-01 00:00:00";
$sql = "SELECT *
from (select user_id, amount, substr(created_time,1,10) as payment_time from pay_now_summary where user_id = '{$id}' and `status` = 'success'
union all select user_id, amount, substr(payment_time,1,10) as payment_time from repayment_details where user_id = '{$id}') tbl;";
$result1 = $conn->query($sql);
if($result1-> num_rows > 0) {
    while($row = $result1->fetch_assoc()) {

        $pdf->Cell(60,8,$row['payment_time'], 1, 0);
		$pdf->Cell(60,8,number_format($row['amount'],2,'.',','), 1, 1);
        
    }
}

$pdf->Cell(60,10,' ', 0, 1);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+180, $y);
$pdf->Cell(60,5,' ', 0, 1);
$pdf->Cell(80,8,'Aspire Fintech Private Limited', 0, 0);
$pdf->Cell(80,8,'Our Address', 0, 1);

$pdf->Cell(80,8,'GST: 29AATCA6761C1ZB', 0, 0);
$y = $pdf->GetY();

$pdf->MultiCell(80,8,'3RD FLOOR, H-0302, SMONDO-3, NEOTOWN ROAD,
HULIMAMGALA VILLAGE, JIGANI HOBLI,
Bengaluru, Karnataka, 560099', 0, 1);

$mail = "mailto:contact@letsaspire.in";
$pdf->Cell(45,8,'Email: contact@letsaspire.in','0','1','',false, $mail);
$whatsapp = "https://wa.me/918431568414/?text=hii";
$pdf->Cell(45,8,'Whatsapp: +91 8431568414','0','1','',false, $whatsapp);
$pdf->SetXY($x, $y+8);
$pdf->Cell(80,8,'PAN: AATCA6761C', 0, 1);


//output the result
ob_start();
$pdf->Output();
 
?>