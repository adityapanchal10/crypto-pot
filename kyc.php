<?php

session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['id'])) {
	header('Location: login.php');
	exit();
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $email = $_SESSION['email'];

    include "db_connect.php";
    if ($stmt = $con->prepare('SELECT is_KYC_request_sent, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($kyc_request, $kyc_verified);
            $stmt->fetch();

            if ($kyc_verified == 1) {
                echo '<!DOCTYPE html>

                <html>
                
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <title></title>
                    <meta name="description" content="">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                
                    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
                        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
                
                    <link rel="stylesheet" href="assets/css/kyc.css">
                
                </head>
                
                <body>
                    <form class="container" action="#" enctype="multipart/form-data">
                        <div id="inputText" class="text-content">
                
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">First Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="John" name="fname" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Middle Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="M." name="mname" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Last Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="Doe" name="lname" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputEmail4">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="johndoe99@email.com" name="email" disabled>
                                </div>
                            </div>
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Gender</legend>
                                    <div class="col-auto">
                                        <div class="form-check gender">
                                            <label class="form-check-label radio-inline" for="gridRadios1">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios1" value="Male" disabled>
                                                Male
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios2">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios2" value="female" disabled>
                                                Female
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios3">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios3" value="other" disabled>
                                                Other
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group">
                                <label for="inputAddress">Address Line 1</label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St" name="address_line_1" disabled>
                            </div>
                            <div class="form-group">
                                <label for="inputAddress2">Address Line 2</label>
                                <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor" name="address_line_2" disabled>
                            </div>
                
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputCity">City</label>
                                    <input type="text" class="form-control" id="inputCity" name="city" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputState">State</label>
                                    <input type="text" class="form-control" id="inputState" name="state" disabled>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="inputZip">Zip</label>
                                    <input type="text" class="form-control" id="inputZip" name="zipCode" disabled>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kycmethod" class="col-md-5 col-form-label">Choose your document</label>
                                <div class="col-md-7">
                                    <select id="inputState" class="form-control" name="document_type" disabled>
                                        <option selected>Choose...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                
                        <!-- Upload Area -->
                        <div id="uploadArea" class="upload-area">
                            <!-- Header -->
                            <div class="upload-area__header">
                                <h1 class="upload-area__title">Your KYC is already verified.</h1>
                            </div>
                            <!-- End Header -->
                
                            <!-- Drop Zoon -->
                            <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                                <span class="drop-zoon__icon">
                                    <i class="bx bxs-file-image"></i>
                                </span>
                                <img src="" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false">
                                <input type="file" id="fileInput" class="drop-zoon__file-input" accept="image/*" name="document" disabled>
                            </div>
                            <!-- End Drop Zoon -->
                
                            <!-- File Details -->
                            <div id="fileDetails" class="upload-area__file-details file-details">
                                <h3 class="file-details__title">Uploaded File</h3>
                
                                <div id="uploadedFile" class="uploaded-file">
                                    <div class="uploaded-file__icon-container">
                                        <i class="bx bxs-file-blank uploaded-file__icon"></i>
                                        <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                                    </div>
                
                                    <div id="uploadedFileInfo" class="uploaded-file__info">
                                        <span class="uploaded-file__name">Proejct 1</span>
                                        <span class="uploaded-file__counter">0%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- End File Details -->
                        </div>
                        <!-- End Upload Area -->
                    </form>
                    </>
                
                
                
                    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
                        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
                        crossorigin="anonymous"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
                        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
                        crossorigin="anonymous"></script>
                    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
                        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
                        crossorigin="anonymous"></script>
                
                    <script src="assets/js/kyc.js"></script>
                </body>
                
                </html>';
            } else if ($kyc_request == 1) {
                echo '<!DOCTYPE html>

                <html>
                
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <title></title>
                    <meta name="description" content="">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                
                    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
                        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
                
                    <link rel="stylesheet" href="assets/css/kyc.css">
                
                </head>
                
                <body>
                    <form class="container" action="#" enctype="multipart/form-data">
                        <div id="inputText" class="text-content">
                
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">First Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="John" name="fname" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Middle Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="M." name="mname" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Last Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="Doe" name="lname" disabled>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputEmail4">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="johndoe99@email.com" name="email" disabled>
                                </div>
                            </div>
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Gender</legend>
                                    <div class="col-auto">
                                        <div class="form-check gender">
                                            <label class="form-check-label radio-inline" for="gridRadios1">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios1" value="Male" disabled>
                                                Male
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios2">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios2" value="female" disabled>
                                                Female
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios3">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios3" value="other" disabled>
                                                Other
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group">
                                <label for="inputAddress">Address Line 1</label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St" name="address_line_1" disabled>
                            </div>
                            <div class="form-group">
                                <label for="inputAddress2">Address Line 2</label>
                                <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor" name="address_line_2" disabled>
                            </div>
                
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputCity">City</label>
                                    <input type="text" class="form-control" id="inputCity" name="city" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputState">State</label>
                                    <input type="text" class="form-control" id="inputState" name="state" disabled>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="inputZip">Zip</label>
                                    <input type="text" class="form-control" id="inputZip" name="zipCode" disabled>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kycmethod" class="col-md-5 col-form-label">Document type:</label>
                                <div class="col-md-7">
                                    <select id="inputState" class="form-control" name="document_type" disabled>
                                        <option selected>Choose...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                
                        <!-- Upload Area -->
                        <div id="uploadArea" class="upload-area">
                            <!-- Header -->
                            <div class="upload-area__header">
                                <h1 class="upload-area__title">You have already submitted KYC request.</h1>
                            </div>
                            <!-- End Header -->
                
                            <!-- Drop Zoon -->
                            <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                                <span class="drop-zoon__icon">
                                    <i class="bx bxs-file-image"></i>
                                </span>
                                <img src="" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false">
                                <input type="file" id="fileInput" class="drop-zoon__file-input" accept="image/*" name="document" disabled>
                            </div>
                            <!-- End Drop Zoon -->
                
                            <!-- File Details -->
                            <div id="fileDetails" class="upload-area__file-details file-details">
                                <h3 class="file-details__title">Uploaded File</h3>
                
                                <div id="uploadedFile" class="uploaded-file">
                                    <div class="uploaded-file__icon-container">
                                        <i class="bx bxs-file-blank uploaded-file__icon"></i>
                                        <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                                    </div>
                
                                    <div id="uploadedFileInfo" class="uploaded-file__info">
                                        <span class="uploaded-file__name">Proejct 1</span>
                                        <span class="uploaded-file__counter">0%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- End File Details -->
                        </div>
                        <!-- End Upload Area -->
                    </form>
                    </>
                
                
                
                    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
                        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
                        crossorigin="anonymous"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
                        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
                        crossorigin="anonymous"></script>
                    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
                        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
                        crossorigin="anonymous"></script>
                
                    <script src="assets/js/kyc.js"></script>
                </body>
                
                </html>';
            } else {
                echo '<!DOCTYPE html>

                <html>
                
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <title></title>
                    <meta name="description" content="">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                
                    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
                        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
                
                    <link rel="stylesheet" href="assets/css/kyc.css">
                
                </head>
                
                <body>
                    <form class="container" action="kyc.php" method="POST" enctype="multipart/form-data">
                        <div id="inputText" class="text-content">
                
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">First Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="John" name="fname">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Middle Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="M." name="mname">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Last Name</label>
                                    <input type="text" class="form-control" id="inputtext" placeholder="Doe" name="lname">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputEmail4">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="johndoe99@email.com" name="email">
                                </div>
                            </div>
                            <fieldset class="form-group">
                                <div class="row">
                                    <legend class="col-form-label col-sm-2 pt-0">Gender</legend>
                                    <div class="col-auto">
                                        <div class="form-check gender">
                                            <label class="form-check-label radio-inline" for="gridRadios1">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios1" value="Male">
                                                Male
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios2">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios2" value="female">
                                                Female
                                            </label>
                
                                            <label class="form-check-label radio-inline" for="gridRadios3">
                                                <input class="form-check-input radio-inline" type="radio" name="gridRadios"
                                                    id="gridRadios3" value="other">
                                                Other
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group">
                                <label for="inputAddress">Address Line 1</label>
                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St" name="address_line_1">
                            </div>
                            <div class="form-group">
                                <label for="inputAddress2">Address Line 2</label>
                                <input type="text" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor" name="address_line_2">
                            </div>
                
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputCity">City</label>
                                    <input type="text" class="form-control" id="inputCity" name="city">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputState">State</label>
                                    <input type="text" class="form-control" id="inputState" name="state">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="inputZip">Zip</label>
                                    <input type="text" class="form-control" id="inputZip" name="zipCode">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="kycmethod" class="col-md-5 col-form-label">Choose your document</label>
                                <div class="col-md-7">
                                    <select id="inputState" class="form-control" name="document_type">
                                        <option selected>Choose...</option>
                                        <option>Adhaar card</option>
                                        <option>Passport</option>
                                        <option>PAN card</option>
                                        <option>Driving license</option>
                                        <option>Voter ID</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gridCheck" name="agree">
                                    <label class="form-check-label" for="gridCheck">
                                        I agree to the terms and conditions...
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary gokyc"name="kyc-submit">Submit</button>
                        </div>
                
                        <!-- Upload Area -->
                        <div id="uploadArea" class="upload-area">
                            <!-- Header -->
                            <div class="upload-area__header">
                                <h1 class="upload-area__title">Upload your document</h1>
                                <p class="upload-area__paragraph">
                                    Supported File
                                    <strong class="upload-area__tooltip">
                                        Types
                                        <span class="upload-area__tooltip-data"></span> <!-- Data Will be Comes From Js -->
                                    </strong>
                                </p>
                            </div>
                            <!-- End Header -->
                
                            <!-- Drop Zoon -->
                            <div id="dropZoon" class="upload-area__drop-zoon drop-zoon">
                                <span class="drop-zoon__icon">
                                    <i class="bx bxs-file-image"></i>
                                </span>
                                <p class="drop-zoon__paragraph">Drop your file here or Click to browse</p>
                                <span id="loadingText" class="drop-zoon__loading-text">Please Wait</span>
                                <img src="" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false">
                                <input type="file" id="fileInput" class="drop-zoon__file-input" accept="image/*" name="document">
                            </div>
                            <!-- End Drop Zoon -->
                
                            <!-- File Details -->
                            <div id="fileDetails" class="upload-area__file-details file-details">
                                <h3 class="file-details__title">Uploaded File</h3>
                
                                <div id="uploadedFile" class="uploaded-file">
                                    <div class="uploaded-file__icon-container">
                                        <i class="bx bxs-file-blank uploaded-file__icon"></i>
                                        <span class="uploaded-file__icon-text"></span> <!-- Data Will be Comes From Js -->
                                    </div>
                
                                    <div id="uploadedFileInfo" class="uploaded-file__info">
                                        <span class="uploaded-file__name">Proejct 1</span>
                                        <span class="uploaded-file__counter">0%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- End File Details -->
                        </div>
                        <!-- End Upload Area -->
                    </form>
                    </>
                
                
                
                    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
                        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
                        crossorigin="anonymous"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
                        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
                        crossorigin="anonymous"></script>
                    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
                        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
                        crossorigin="anonymous"></script>
                
                    <script src="assets/js/kyc.js"></script>
                </body>
                
                </html>';
            }
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['kyc-submit'])) {
    $email = $_SESSION['email'];
    $userid = $_SESSION['id'];

    include "db_connect.php";
    if ($stmt = $con->prepare('SELECT is_KYC_request_sent, isKYCverified FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($kyc_request, $kyc_verified);
            $stmt->fetch();

            if ($kyc_verified == 1) {
                echo '';
            } else if ($kyc_request == 1) {
                echo '';
            } else if ($email != $_POST['email']) {
                echo '<script>alert("Please check your details"); window.location = "kyc.php";</script>';
            } else {
                if ($stmt = $con->prepare('INSERT INTO kycMaster (userid, fname, mname, lname, email, gender, addressLine1, addressLine2, city, state, zipCode, documentType, document) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
                    if(!empty($_FILES['document']['name'])) { 
                        // Get file info 
                        $fileName = basename($_FILES['document']['name']); 
                        $fileType = pathinfo($fileName, PATHINFO_EXTENSION); 
                         
                        // Allow certain file formats 
                        $allowTypes = array('jpg','png','jpeg','gif'); 
                        if(in_array($fileType, $allowTypes)){ 
                            $image = $_FILES['document']['tmp_name']; 
                            $imgContent = file_get_contents($image);
                            $stmt->bind_param('isssssssssiss', $userid, $_POST['fname'], $_POST['mname'], $_POST['lname'], $email, $_POST['gridRadios'], $_POST['address_line_1'], $_POST['address_line_2'], $_POST['city'], $_POST['state'], $_POST['zip_code'], $_POST['document_type'], $imgContent);
                            if(!$stmt->execute()){
                                echo $stmt->error;
                                exit;
                            } else {
                                if ($stmt = $con->prepare('UPDATE userMaster SET is_KYC_request_sent = 1 WHERE email_id = ?')) {
                                    $stmt->bind_param('s', $email);             
                                    if ($stmt->execute()) {
                                        header('Location: kyc.php');
                                    }
                                }
                            }
                        }
                    } else {
                        echo '<script>alert("An error occurred! Please check your file"); window.location = "kyc.php";</script>';
                    }
                }
            }
        }
    }
}
?>