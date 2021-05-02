<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hello, world!</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .bg-container {
                font-family: Roboto
            }
            .dot-table td {
                max-width:550px;
                overflow:hidden;
                white-space:nowrap;
            }
            .dot-table td:first-child:after {
                content:" - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  "
            }
            
            .button {
                font-size:25px;
                color: blue;
                text-decoration:underline;
            }
            
            .header-container {
                background-color:black;
                color:chocolate;
            }
            
            .header {
                font-size:50px;
            }
    </style>
  </head>
  <body>
    <header class = "p-5">
        <div class="container">
            <div class="row row-header">
                <div class="col-3 header-container p-5 ml-5" style="background-color:black">
                    <h1 class = "header" style = "color:chocolate">Aspire</h1>
                </div>
                
                <div class="col offset-1 align-self-center">
                    <h1 style = "text-decoration:underline">March 2021 Statement</h1>
                </div>
            </div>
        </div>
    </header>
    <div class = "container p-5 bg-container border-top border-dark">
        <div class = "row align-items-center">
            <div class = "col-4 border border-warning ml-3">
                <h3>To,</h3>
                <?php
                    $conn = mysqli_connect("13.126.97.63", "tripan", "6r8y7dZs/j", "aspiredb");
                    if($conn-> connect_error) {
                        die("Connection failed:".$conn-> connect_error);
                    }
                    $id = "'ab2f3e3f-c202-464a-8292-9e3550ebe8ff'";
                    $id1 = "ab2f3e3f-c202-464a-8292-9e3550ebe8ff";
                    // $tsql ="select * from kycdetails where user_id = {$id}" ;
                    // $result1 = $conn -> query($tsql);
                    // while($result1 -> num_rows > 0) {
                    //     while($row = $result1->fetch_assoc()) {
                    //         echo $row['country'];
                    //     }
                    // }
                    
                    $sql = "select u.user_name, u.phone_number, uad.address,uad.pin_code from user u,user_address uad where u.id = uad.user_id and u.id = {$id} limit 1";
                    $result = $conn->query($sql);
                    if($result-> num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<h5>{$row['user_name']}</h5>";
                            echo "<p><b>Address: </b>{$row['address']},{$row['pin_code']}</p>";
                            echo "<p><b>Phone: </b>{$row['phone_number']}</p>";
                        }
                    }
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
                    // $handle = fopen('D:/aspire_crons/Billing Automation/february2021_automated_bills.csv', 'r');
                    $handle = fopen('D:/aspire_crons/user_payment/March biils/bills_to_be_generated.csv', 'r');
                              while(($data = fgetcsv($handle)) !== FALSE) {
                                //   echo $data[1];
                                if($data[0] === $id1) {
                                    $user_name = $data[1];
                                    $sanction_amount = $data[3];
                                    $available_limit = $data[4];
                                    $due_emi_this_month = $data[5];
                                    $monthly_fee = $data[6];
                                    $monthly_tax = $data[7];
                                    $late_fee = $data[8];
                                    $late_tax = $data[9];
                                    $DPD = $data[10];
                                    $payment_min = $data[11];
                                    $payment_max = $data[12];
                                    $amount_carry_forward = $data[13];
                                    $link = $data[14];
                                }
                              }
                    fclose($handle);
                         
                    // $conn->close();
                ?>
            </div>
            <div class = "col-4 ml-auto border border-warning py-3 mr-3">
                <?php
                    echo "<h5><span><b>Due Amount: </b></span>  <i class = 'fa fa-inr'></i> <b>{$payment_min}</b></h5>";
                ?>
                <h5><span><b>Due Date: </span>8 Apr 2021</b></h5>
                <p><span><b>Late Charges: </b></span><i class = "fa fa-inr"></i> 300 + GST for payments after 8 Apr 2021</p>
            </div>
            
            <div class = "col-12 mt-3">
                <h3><b>Summary of Charges</b></h3>
                <div class = "border-top border-bottom border-dark p-3">
                    <table class="dot-table">
                        <!-- <tr>
                          <td>
                             Due EMI this month
                          </td>
                          <td>
                            <span> <i class = "fa fa-inr"></i> 354.00 </span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            Monthly Membership Fee 
                          </td>
                          <td>
                            <span> <i class = "fa fa-inr"></i> 0.00 </span>
                          </td>
                         </tr>
                         <tr>
                            <td>
                                GST on Membership Fee  
                            </td>
                            <td>
                              <span> <i class = "fa fa-inr"></i> 0.00 </span>
                            </td>
                           </tr>
                           <tr>
                            <td>
                                Late Fee  
                            </td>
                            <td>
                              <span> <i class = "fa fa-inr"></i> 0.00 </span>
                            </td>
                           </tr>
                           <tr>
                            <td>
                                GST on Late Fee  
                            </td>
                            <td>
                              <span> <i class = "fa fa-inr"></i> 0.00 </span>
                            </td>
                           
                           <tr>
                            
                           </tr> -->
                           <?php
                              
                            echo "<tr><td> Due EMI this month </td><td> <i class = 'fa fa-inr'></i> {$due_emi_this_month}</td></tr>";
                            echo "<tr><td> Monthly Membership Fee </td><td> <i class = 'fa fa-inr'></i> {$monthly_fee}</td></tr>";
                            echo "<tr><td> GST on Membership Fee </td><td> <i class = 'fa fa-inr'></i> {$monthly_tax}</td></tr>";
                            echo "<tr><td> Late Fee </td><td> <i class = 'fa fa-inr'></i> {$late_fee}</td></tr>";
                            echo "<tr><td> GST on Late Fee </td><td> <i class = 'fa fa-inr'></i> {$late_tax}</td></tr>";
                            echo "<tr><td> Excess Payment Carry Forward </td><td> <i class = 'fa fa-inr'></i> {$amount_carry_forward}</td></tr>";
                            echo "<tr><td> Net Due </td><td> <b><i class = 'fa fa-inr'></i> {$payment_min}</b></td></tr>";
                        
                           ?>
                           </tr>
                          
                      </table>
                </div>
            </div>
            <div class = "col-7 mt-3">
                <h3><b>Credit Line Standing as on 31<sup>st</sup> March</b></h3>
                <div class = "p-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                          <tr>
                            <th scope="col">Total Line Limit</th>
                            <th scope="col">Available Limit</th>
                            <th scope="col">DPD</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <!-- <td><span> <i class = "fa fa-inr"></i> 4000.00 </span></td>
                            <td><span> <i class = "fa fa-inr"></i> 3645.80 </span></td>
                            <td>31</td> -->
                            <?php
                              
                            echo "<td><span> <i class = 'fa fa-inr'></i> {$sanction_amount}</span></td>";
                            echo "<td><span> <i class = 'fa fa-inr'></i> {$available_limit}</span></td>";
                            echo "<td><span> {$DPD}</span></td>";
                               
                           ?>
                          </tr>
                        </tbody>
                      </table>
                </div>
            </div>
            <div class = "offset-md-2">
                <div>
                <?php
                    if($payment_max > 0){
                        echo "<a href= '{$link}' target = '_blank'><button class = 'btn btn-warning px-4 button'>Pay Now</button></a>";
                    }
                    else {
                        echo "<a href= '#'><button class = 'btn btn-warning px-4 button'>No Need To Pay</button></a>";
                    }
                ?>    
                </div>
                
            </div>
            <div class = "col-12">
              <?php
                if($DPD == 0) {
                    echo "<h4><b>Congratulations!</b> on improving your credit score (all bureaus including CIBIL) with on time payment before or on <b>8<sup>th</sup> April</b>.</h4>";
                }
                else {
                    echo "<h4><b>New year brings new hope!</b> Please pay your dues to make the new year financially happy!</h4>";
                }
              ?>
            </div>
        </div>
        <div class = "row">
        <div class = "col-7 mt-3">
                <h3><b>Transaction History</b></h3>
                <div class = "p-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                          <tr>
                            <th scope="col"><b>Created Time</b></th>
                            <th scope="col"><b>Merchant Name</b></th>
                            <th scope="col"><b>Amount</b></th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- <tr>
                            <td><span> <i class = "fa fa-inr"></i> 45.00 </span></td>
                            <td><span> <i class = "fa fa-inr"></i> 45.00 </span></td>
                            <td>31</td>
                          </tr> -->
                          <?php
                          $sql = "SELECT 
                          SUBSTR(m.created_time, 1, 10) as created_time,
                          m1.merchant_name,
                          m.amount
                         
                      FROM
                          merchant_payments AS m
                              LEFT JOIN
                          merchant_details AS m1 ON m.merchant_id = m1.merchant_id
                      WHERE
                          m.user_id = {$id}
                              AND m.`status` = 'success';";
                    $result1 = $conn->query($sql);
                    if($result1-> num_rows > 0) {
                        while($row = $result1->fetch_assoc()) {

                            echo "<tr><td>{$row['created_time']}</td> <td>{$row['merchant_name']}</td> <td>{$row['amount']}</td></tr>";
                            
                        }
                    }
                    // $conn->close();
                        ?>  
                        </tbody>
                      </table>
                </div>
            </div>
            <div class = "col-5 mt-3">
                <h3><b>Payment History</b></h3>
                <div class = "p-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                          <tr>
                            <th scope="col"><b>Payment Time</b></th>
                            <!-- <th scope="col">Merchant Name</th> -->
                            <th scope="col"><b>Amount</b></th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- <tr>
                            <td><span> <i class = "fa fa-inr"></i> 45.00 </span></td>
                            <td><span> <i class = "fa fa-inr"></i> 45.00 </span></td>
                            <td>31</td>
                          </tr> -->
                          <?php
                          $sql = "SELECT 
                          SUBSTR(payment_time,1,10) as payment_time, amount
                      FROM
                          repayment_details AS r
                      WHERE
                          r.user_id = {$id}";
                    $result1 = $conn->query($sql);
                    if($result1-> num_rows > 0) {
                        while($row = $result1->fetch_assoc()) {

                            echo "<tr><td>{$row['payment_time']}</td> <td>{$row['amount']}</td></tr>";
                            
                        }
                    }
                    $conn->close();
                        ?>  
                        </tbody>
                      </table>
                </div>
            </div>
        </div>

    </div>
    <footer class="footer border-top">
        <div class="container py-3">
            <div class="row">
                <div class="col-3 offset-1">
                    <ul class="list-unstyled">
                        <li><b>Aspire Fintech Private Limited</b></li>
                        <li><b>GST:</b> 29AATCA6761C1ZB</li>  
                        <li><b>PAN:</b> AATCA6761C</li> 
                    </ul>
                </div>
                <div class="col-4">
                    <h5>Our Address</h5>
                    <address>
                        3RD FLOOR, H-0302, SMONDO-3, NEOTOWN ROAD,<br>
                        HULIMAMGALA VILLAGE, JIGANI HOBLI,<br> Bengaluru, Karnataka, 560099
                    </address>
                </div>
                <div class="col-4">
                    <address>
                        <i class="fa fa-whatsapp fa-lg"></i> Whatsapp: <a href="https://wa.me/918431568414/?text=hii" target = "_blank">+91 8431568414<br></a>
                        <i class="fa fa-envelope fa-lg"></i> Email: <a
                            href="mailto:confusion@food.net">contact@letsaspire.in</a>
                    </address>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-auto align-items-center">
                    <p>@ Copyright 2019 Aspire Fintech Private Limited</p>
                </div>
            </div>
        </div>
    </footer>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>