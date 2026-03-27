<?php
$response="";//initialize variable
//check the answer
if(isset($_POST['captcha_answer'])){
    //use numbers submitted in hidden fields
    $num1=isset($_POST['num1'])?(int)$_POST['num1']:0;
    $num2=isset($_POST['num2'])?(int)$_POST['num2']:0;
       }
    else{
        $response="";
        //Generate new numbers if first page load
        $num1=rand(1,9);
        $num2=rand(1,9);
        }
//calculate sum here,do NOT use $_POST['sum'] 
    $sum=$num1=$num2;
//check the answer
if(isset($_POST['captcha_answer']))
    {
$respone=((int) $_POST['captcha_answer']==$sum)?
    "correct!" :"wrong, try again!";
}else{
    $response="";
}
    
?>
<div class="captcha-box">
<form method="post">
    <h3>Human Verification</h3>
    <p>Solve: <?php echo "$num1 + $num2"; ?></p>
    <input type="text" name="captcha_answer" required placeholder="Enter answer">
    <!--Hidden fields to preserve numbers on submission-->
    <input type="hidden" name="num1" value="<?php echo $num1; ?>">
    <input type="hidden" name="num2" value="<?php echo $num2; ?>">
    <button type="submit">Submit</button>
    <p class="response">
        <?php 
        if(isset($response)&& $response !=""){
            echo $response;
        }
         ?>
    </p>
</form>
</div>

<style>
body{font-family:Arial,sans-serif; background:#eef2f7; display:flex; justify-content:center; align-items:center; height:100vh;}
.captcha-box{background:white; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.2); text-align:center; width:300px;}
.captcha-box h3{color:#333;}
.captcha-box input{padding:10px; margin-top:10px; width:80%; border-radius:5px; border:1px solid #ccc;}
.captcha-box button{margin-top:15px; padding:10px 20px; background:#4CAF50; color:white; border:none; border-radius:5px; cursor:pointer;}
.captcha-box button:hover{background:#45a049;}
.captcha-box .response{font-weight:bold; margin-top:10px; color:red;}
</style>









