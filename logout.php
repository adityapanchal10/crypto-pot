<?php

// Start the session
session_start();

if (session_destroy()) {
    //redirect to the login page
    header("Location: login.php");
    exit;
}