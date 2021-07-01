<?php
//call the FPDF library

require ('fpdf17/fpdf.php');
// require_once ("vendor/autoload.php");
// \Dotenv\Dotenv::createImmutable(paths:__DIR__)->load();

// echo var_dump($_ENV);

// $db = $_ENV["DATABASE"];
// $host = $_ENV['HOST'];
// $password = $_ENV["PASSWORD"];
// $user = $_ENV["USER_NAME"];
// echo "Database: ".$db;


// echo $user;

// echo "host"." ". $host;

$db = getenv("DATABASE");
$host = getenv("HOST");
$password = getenv("PASSWORD");
$user = getenv("USER_NAME");

// // echo "database ".$db;

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
class PDF extends FPDF
{
    function Footer()
    {
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial','I',8);
        // Print centered page number
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

class PDF1 extends PDF
{
    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}

$pdf = new PDF1('P','mm','A4');
//add new page
$pdf->AddPage();

$pdf->SetFont('Arial','B',14);
$pdf->Image('aspire.png',$pdf->GetX(),$pdf->GetY(), 35, 20);
//Cell(width , height , text , border , end line , [align] )
$pdf->SetFont('Arial','B',15);
$pdf->Cell(190 ,5,'Aspire Digital Credit Card',0,1,'C');
$pdf->Cell(59 ,5,' ',0,1);//end of line

$x = $pdf->GetPageWidth()/2;
$y = $pdf->GetY();
$pdf->SetLineWidth(0.5);
$pdf -> Rect($x-30,$y-2,60,9,'D');
$pdf->SetLineWidth(0.2);
$pdf->Cell(190 ,5,$billing_month.' '.$billing_year.' Statement',0,1,'C');//end of line
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
$due_date = "";
$last_day_prev_month = "";
$first_day_timestamp = "";
$first_day_date = "";

$sql_2 = "select * from billing_info where user_id = '{$id}' and billing_month = '{$billing_month}' and billing_year = {$billing_year}";
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
		$due_date = $row["due_date"];
		$last_day_prev_month = $row["last_day_prev_month"];
		$first_day_timestamp = $row["first_day_timestamp"];
		$first_day_date = $row["first_day_date"];
		break;
	}
}

if($result-> num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pdf->Cell(59 ,20,' ',0,1);//end of line
		$x1 = $pdf->GetX();
		$y1 = $pdf->GetY();

		$pdf->Cell(100, 5, 'To,',0,1);
		$pdf->Cell(59 ,3,' ',0,1);//end of line
		$pdf->SetFont('Arial','B',11);

		$pdf->Cell(120,5,$row['user_name'],0,0);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x, $y - 5);
		$pdf->SetFont('Arial','B',15);
		$pdf->SetTextColor(178,9,9);
		$pdf->Cell(60,5,'Due Amount: '.$pdf->Image('inr.png',$pdf->GetX()+35,$pdf->GetY(), 3, 5).'  '.number_format($payment_min,2,'.',','), 0, 1);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x, $y + 5);
		$pdf->SetFont('Arial','B',11);
		$pdf->SetTextColor(0,0,0);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$row['address'] = str_replace(array("\r", "\n"), ' ', $row['address']);
		$pdf->MultiCell(100,8,substr(trim($row['address']),0, 100).' '.$row['pin_code'],0,0);
		$pdf->SetXY($x + 120, $y-2);
		$pdf->SetFont('Arial','B',15);
		$pdf->SetTextColor(178,9,9);
		$pdf->Cell(60,5,'Due Date: '.$due_date, 0, 1);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('Arial','B',11);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->SetXY($x + 120, $y+2);
		$y = $pdf->GetY();
		$pdf->MultiCell(60,5,'Late Charges: 300 + GST for payments after '.$due_date,0,1);
		$pdf->SetXY($x, $y+18);
		$pdf->Cell(120,10,'Phone: '.$row['phone_number'],0,1);

		$x2 = $pdf->GetX();
		$y2 = $pdf->GetY();

		$pdf->SetLineWidth(0.5);
		$pdf -> RoundedRect($x1-1,$y1-2,$pdf->GetPageWidth()/2,$y2-$y1+15,1.5,'D');
		$pdf -> RoundedRect($x1+$pdf->GetPageWidth()/2+10,$y1-2,$pdf->GetPageWidth()/2-25,$y2-$y1,1.5,'D');
		$pdf->SetLineWidth(0.2);
		$pdf->Cell(59 ,5,' ',0,1);//end of line
		// $link = 'https://stackoverflow.com/questions/54644886/insert-a-url-inside-a-cell-in-fpdf';
		// $pdf->SetFont('Arial','U',20);
		$pdf->SetFont('Arial','BU',22);
		// $pdf->SetTextColor(194,8,8);
		$pdf->SetTextColor(0,0,0);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->setFillColor(245,190,67);
		$pdf -> Rect(0,$y+17,$pdf->GetPageWidth(),19,'F');
		// $pdf->SetXY($x-50, $y+20);
		$pdf->SetXY($x, $y+20);
		$pdf->SetTextColor(178,9,9);
		$pdf->Cell($pdf->GetPageWidth()-10,12,'Click here to Pay Now','0','1','C',false, $link);
		$pdf->SetTextColor(0,0,0);

		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('Arial','B',11);
		$pdf->setFillColor(255,255,255);

		$pdf->Cell(59 ,10,' ',0,1);//end of line

		$pdf->Cell(120, 10, 'Summary of Charges', 0, 1);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->Line($x,$y,$x + $x+120, $y);
		$pdf->Cell(59 ,8,' ',0,1);//end of line
		$pdf->Cell(120,8, 'Due EMI this month', 1, 0);
		// $pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+22,$pdf->GetY()+2, 3, 3).'     '.number_format($due_emi_this_month,2,'.',','), 1, 1,'C');
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($due_emi_this_month,2,'.',','), 1, 1,'C');
		$pdf->Cell(120,8, 'Monthly Membership Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($monthly_fee,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'GST on Membership Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($monthly_tax,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Late Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($late_fee,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'GST on Late Fee', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($late_tax,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Excess Payment Carry Forward', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($amount_carry_forward,2,'.',','), 1, 1,'C');

		$pdf->Cell(120,8, 'Net Due', 1, 0);
		$pdf->Cell(60,8, $pdf->Image('inr.jpg',$pdf->GetX()+20,$pdf->GetY()+2, 3, 3).'     '.number_format($payment_min,2,'.',','), 1, 1,'C');

		$pdf->Cell(59 ,8,' ',0,1);//end of line

		$pdf->Cell(120,5,'Credit Line Standing as on '.$last_day_prev_month,0,1);
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
		break ;
    }
}

// $pdf->Cell(10,5,number_format($amt,2,'.',','), 0,1);

$pdf->Cell(60,8,' ', 0, 1);
$x1 = $pdf->GetX();
$y1 = $pdf->GetY();
if($DPD == 0)
{
	$pdf->MultiCell(180,8,'Congratulations! on improving your credit score (all bureaus including CIBIL) with on time payment before or on '.$due_date.'.',0,1);
}else {
	$pdf->MultiCell(180,8,'New year brings new hope! Please pay your dues to make the new year financially happy!',0,1);
}
$x2 = $pdf->GetX();
$y2 = $pdf->GetY();

$pdf->RoundedRect($x1-1, $y1-1, 182, $y2-$y1+2, 2, 'D');
$pdf->AddPage();
// $pdf->Cell(60,10,' ', 0, 1);
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
          AND m.`status` = 'success'
		  AND m.created_time < '{$first_day_timestamp}'
	order by m.created_time desc;";
$result1 = $conn->query($sql);

if($result1-> num_rows > 0) {
    while($row = $result1->fetch_assoc()) {
        $pdf->Cell(40,8,$row['created_time'],1,0);
        $pdf->Cell(90,8,substr($row['merchant_name'],0,40), 1, 0);
        $pdf->Cell(50,8,number_format($row['amount'],2,'.',','),1,1);
  
    }
}


// $pdf->Cell(60,10,' ', 0, 1);
$pdf->AddPage();
$pdf->Cell(60,10,'Repayment History', 0,1);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+120, $y);
$pdf->Cell(59 ,5,' ',0,1);//end of line
$pdf->Cell(60,8,'Repayment Time', 1, 0);
$pdf->Cell(60,8,'Amount', 1, 1);

// $payment_time = "2021-05-01 00:00:00";
$sql = "SELECT *
from (select user_id, amount, substr(created_time,1,10) as payment_time from pay_now_summary where user_id = '{$id}' and `status` = 'success' and created_time < '{$first_day_timestamp}'
union all select user_id, amount, substr(payment_time,1,10) as payment_time from repayment_details where user_id = '{$id}' and payment_time < '{$first_day_timestamp}') tbl 
order by payment_time desc;";
$result1 = $conn->query($sql);
if($result1-> num_rows > 0) {
    while($row = $result1->fetch_assoc()) {

        $pdf->Cell(60,8,$row['payment_time'], 1, 0);
		$pdf->Cell(60,8,number_format($row['amount'],2,'.',','), 1, 1);

    }
}

$pdf->Cell(60,10,' ', 0, 1);
$pdf->Cell(60,10,'Fee Payment Details', 0,1);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+120, $y);
$pdf->Cell(59 ,5,' ',0,1);//end of line
$pdf->Cell(45,8,'Billing Month', 1, 0);
$pdf->Cell(45,8,'Fee Type', 1, 0);
$pdf->Cell(45,8,'Fee Amount', 1, 0);
$pdf->Cell(45,8,'Status', 1, 1);

// write sql query here
$day_8 = date("Y-m-08", strtotime($first_day_date));
$sql = "SELECT
date_format(date_sub(billed_date, interval 1 month), '%b %Y') as billing_month,
fee_type,
fee_billed as amount,
(case when fl_paid = -1 then 'waived'
when fl_paid = 0 then 'charged'
when fl_paid = 1 then 'paid'
end) as status
FROM
fee_payment_details where user_id='{$id}' and billed_date <='{$day_8}'
order by billed_date desc;";
$result1 = $conn->query($sql);
if($result1-> num_rows > 0) {
    while($row = $result1->fetch_assoc()) {

        $pdf->Cell(45,8,$row['billing_month'], 1, 0);
		$pdf->Cell(45,8,$row['fee_type'], 1, 0);
		$pdf->Cell(45,8,number_format($row['amount'],2,'.',','), 1, 0);
		$pdf->Cell(45,8,$row['status'], 1, 1);
        
    }
}

$pdf->AddPage();

// $pdf->Cell(60,10,' ', 0, 1);
$pdf->Cell(60,10,'Refund Status Details', 0,1);
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Line($x,$y,$x+120, $y);
$pdf->Cell(59 ,5,' ',0,1);//end of line

$pdf->SetFont('Arial','B',10);
$pdf->Cell(29,8,'Date of Txn', 1, 0);
$pdf->Cell(35,8,'Downpayment Amt', 1, 0);
$pdf->Cell(30,8,'Refund Date', 1, 0);
$pdf->Cell(28,8,'Refund Amt', 1, 0);
$pdf->Cell(28,8,'Refund Status', 1, 0);
$pdf->Cell(30,8,'Reference ID', 1, 1);

// write sql query here
$sql = "SELECT 
STR_TO_DATE(m.created_time, '%Y-%m-%d') AS `date_of_txn`,
d.amount as `down_payment_amount`,
str_to_date(r.refund_date, '%Y-%m-%d') as `refund_date`,
r.amount as `refund_amount`,
r.refund_status as `refund_status`,
r.asp_request_id as `refund_reference_id`
FROM
down_payment_details d
	LEFT JOIN
merchant_payments m ON d.txn_id = m.down_payment_transaction_id
left join 
refund_payment_details r
on
d.txn_id = r.down_payment_transaction_id
WHERE
d.user_id='{$id}'
and
d.status = 'success'
	AND (m.status <> 'success'
	OR m.status IS NULL)
	order by m.created_time desc";

$result1 = $conn->query($sql);
if($result1-> num_rows > 0) {
    while($row = $result1->fetch_assoc()) {

        $pdf->Cell(29,8,$row['date_of_txn'], 1, 0);
		$pdf->Cell(35,8,number_format($row['down_payment_amount'],2,'.',','), 1, 0);
		$pdf->Cell(30,8,$row['refund_date'], 1, 0);
		$pdf->Cell(28,8,number_format($row['refund_amount'],2,'.',','), 1, 0);
		$pdf->Cell(28,8,$row['refund_status'], 1, 0);
		$pdf->Cell(30,8,$row['refund_reference_id'], 1, 1);
        
    }
}

$pdf->SetFont('Arial','B',11);
$conn->close();

$pdf->Cell(60,10,' ', 0, 1);
$x = $pdf->GetX();
$y = $pdf->GetY();

if($y + 100 > $pdf->GetPageHeight()) {
	$pdf->AddPage();
}
else {
	$pdf->Line($x,$y,$x+180, $y);
    $pdf->Cell(60,5,' ', 0, 1);
}
$x1= $pdf->GetX();
$y1 = $pdf->GetY();

$pdf->Cell(80,8,'Aspire Fintech Private Limited', 0, 0);
$pdf->Cell(80,8,'Our Address', 0, 1);

$pdf->Cell(80,8,'GST: 29AATCA6761C1ZB', 0, 0);
$y = $pdf->GetY();

$pdf->MultiCell(80,8,'3RD FLOOR, H-0302, SMONDO-3, NEOTOWN ROAD,
HULIMAMGALA VILLAGE, JIGANI HOBLI,
Bengaluru, Karnataka, 560099', 0, 1);
$x2 = $pdf->GetX();
$y2 = $pdf->GetY();
$pdf->SetXY($x2+80, $y2);
$whatsapp = "https://wa.me/918431568414/?text=Hii";
$pdf->Cell(45,8,'Whatsapp: +91 8431568414','0','1','',false, $whatsapp);

$pdf->SetLineWidth(0.5);

$pdf->RoundedRect($x1-1, $y1-1, 70, $y2-$y1-10, 2, 'D');
$pdf->RoundedRect($x1+78, $y1-1, 84, $y2-$y1+12, 2, 'D');
$pdf->SetLineWidth(0.2);

// $mail = "mailto:contact@letsaspire.in";
// $pdf->Cell(45,8,'Email: contact@letsaspire.in','0','1','',false, $mail);
// $whatsapp = "https://wa.me/918431568414/?text=hii";
// $pdf->Cell(45,8,'Whatsapp: +91 8431568414','0','1','',false, $whatsapp);
$pdf->SetXY($x, $y+8);
$pdf->Cell(80,8,'PAN: AATCA6761C', 0, 1);


//output the result
ob_start();
$pdf->Output('I', $billing_month.' '.$billing_year.' Statement.pdf');
 
?>