<?php

session_start();

include "db_connect.php";

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($stmt = $con->prepare('SELECT userid, first_name, last_name, email_id, country, mobile, timezone FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $first_name, $last_name, $email_id, $country, $mobile, $timezone);
            $stmt->fetch();
            echo '
            <!DOCTYPE html>
<html>
    <head>
        <title>Profile Page</title>
        <link rel="stylesheet" href="assets/css/profile.css"> 
    </head>
    <body>
        <center>
            <div class="box">
                <form action="profile.php" method="POST">
                    <img src="assets/img/satoru.jpg" width="100%" height="100%">

                    <table>
                        <tr>
                            <th>First Name:</th>
                            <th><span id="firstName">'.$first_name.'</span></th>
                            <th>Last Name:</th>
                            <th><span id="lastName">'.$last_name.'</span></th>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <th><span id="emailId">'.$email_id.'</span></th>
                            <th>Mobile Number:</th>
                            <th><span id="phoneNumber">'.$mobile.'</span></th>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <th><span id="birthDate">'.$country.'</span></th>
                            <th>Timezone:</th>
                            <th><span id="gender">'.$timezone.'</span></th>
                        </tr>
                        <tr>
                            <th colspan="2"><button type="submit" name="edit-profile">Edit</button></th>
                            <th colspan="2"><button name="done">Delete Account</button></th>
                        </tr>
                    </table>
                </form>
            </div>
        </center>
    </body>
</html>';
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['edit-profile'])) {
    if ($stmt = $con->prepare('SELECT userid, first_name, last_name, email_id, country, mobile, timezone FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $first_name, $last_name, $email_id, $country, $mobile, $timezone);
            $stmt->fetch();
            echo '<!DOCTYPE html>
            <html>
                <head>
                    <title>Profile Page</title>
                    <link rel="stylesheet" href="assets/css/profile-edit.css"> 
                </head>
                <body>
                    <center>
                        <div class="box">
                            <form action="profile.php" method="POST">
                                <img src="assets/img/satoru.jpg" width="100%" height="100%">

                                <table>
                                    <tr>
                                        <th>First Name:</th>
                                        <th><input type="text" name="fname" value='.$first_name.'></th>
                                        <th>Last Name:</th>
                                        <th><input type="text" name="lname" value='.$last_name.'></th>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <th><input type="text" placeholder="Email ID" name="email" value='.$email_id.'></th>
                                        <th>Mobile Number:</th>
                                        <th><input type="text" placeholder="Phone Number" name="mobile" value='.$mobile.'></th>
                                    </tr>
                                    <tr>
                                        <th>Country:</th>
                                        <th><input type="text" name="country" value='.$country.'></th>
                                        <th>Timezone:</th>
                                        <th><input type="text" name="timezone" value='.$timezone.'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"><button name="submit" type="submit">Edit</button></th>
                                        <th colspan="2"><button name="delete" type="submit">Delete Account</button></th>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </center>
                </body>
            </html>';
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    if (empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['email']) || empty($_POST['mobile']) || empty($_POST['country']) || empty($_POST['timezone'])) {
        exit('<script>alert("Please fill in all the fields");  window.location = "profile.php"</script>');
    }
    if ($stmt = $con->prepare('UPDATE usermaster SET first_name = ?, last_name = ?, email_id = ?, country = ?, mobile = ?, timezone = ? WHERE email_id = ?')) {
        $stmt->bind_param('sssssss', $_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['mobile'], $_POST['country'], $_POST['timezone'], $_SESSION['email']);
        if(!$stmt->execute()){
            exit('<script>alert("'.$stmt->error.'");  window.location = "profile.php"</script>');
        }
    }

    if ($stmt = $con->prepare('SELECT userid, first_name, last_name, email_id, country, mobile, timezone FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userid, $first_name, $last_name, $email_id, $country, $mobile, $timezone);
            $stmt->fetch();
            echo '<html>
            <head>
                <title>Profile Page</title>
                <link rel="stylesheet" href="assets/css/profile.css"> 
            </head>
            <body>
                <center>
                    <div class="box">
                        <form action="profile.php" method="POST">
                            <img src="assets/img/satoru.jpg" width="100%" height="100%">
        
                            <table>
                                <tr>
                                    <th>First Name:</th>
                                    <th><span id="firstName">'.$first_name.'</span></th>
                                    <th>Last Name:</th>
                                    <th><span id="lastName">'.$last_name.'</span></th>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <th><span id="emailId">'.$email_id.'</span></th>
                                    <th>Mobile Number:</th>
                                    <th><span id="phoneNumber">'.$mobile.'</span></th>
                                </tr>
                                <tr>
                                    <th>Country:</th>
                                    <th><span id="birthDate">'.$country.'</span></th>
                                    <th>Timezone:</th>
                                    <th><span id="gender">'.$timezone.'</span></th>
                                </tr>
                                <tr>
                                    <th colspan="2"><button type="submit" name="edit-profile">Edit</button></th>
                                    <th colspan="2"><button name="done">Delete Account</button></th>
                                </tr>
                            </table>
                        </form>
                    </div>
                </center>
            </body>
        </html>';
        }
    }
}
?>