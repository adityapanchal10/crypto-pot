<?php

session_start();

include "db_connect.php";

if (!isset($_SESSION['email'])) {
	header('Location: login.php');
	exit();
} else if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    if ($stmt = $con->prepare('SELECT isKYCverified FROM userMaster WHERE email_id = ?')) {
        $stmt->bind_param('s', $_SESSION['email']);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database.
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($kyc_verified);
            $stmt->fetch();

            if ($kyc_verified == 1) {
                if (isset($_POST['from-wallet'], $_POST['to-wallet'], $_POST['amount'])) {
                    $from_wallet = $_POST['from-wallet'];
                    $to_wallet = $_POST['to-wallet'];
					if ($from_wallet == $to_wallet) {
						echo '<script>alert("From and to wallets cannot be the same"); window.location="dashboard.php"</script>';
					}
                    $amount = $_POST['amount'];
                    if ($stmt = $con->prepare('SELECT currency_id, currency_price FROM priceMaster WHERE currency_name = ?')) {
                        $stmt->bind_param('s', $from_wallet);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($from_currency_id, $from_currency_price);
                        $stmt->fetch();
                        $stmt->close();
                        if ($stmt = $con->prepare('SELECT wallet_id, wallet_balance FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                          $stmt->bind_param('ii', $from_currency_id, $_SESSION['id']);
                          $stmt->execute();
                          $stmt->store_result();
                          $stmt->bind_result($from_wallet_id, $from_wallet_balance);
                          $stmt->fetch();
                          $stmt->close();
                        } else {
                            echo $stmt->error;
                            exit();
                        }
                    } else {
                        echo 'Error fetching from-currency price';
                        exit();
                    }

                    if ($stmt = $con->prepare('SELECT currency_id, currency_price FROM priceMaster WHERE currency_name = ?')) {
                        $stmt->bind_param('s', $to_wallet);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($to_currency_id, $to_currency_price);
                        $stmt->fetch();
                        $stmt->close();
                        if ($stmt = $con->prepare('SELECT wallet_id, wallet_balance FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                            $stmt->bind_param('ii', $to_currency_id, $_SESSION['id']);
                            $stmt->execute();
                            $stmt->store_result();
                            $stmt->bind_result($to_wallet_id, $to_wallet_balance);
                            $stmt->fetch();
                            $stmt->close();
                        } else {
                            echo 'Error fetching to-wallet balance';
                            exit();
                        }
                    } else {
                        echo 'Error fetching to-currency price';
                        exit();
                    }
                    
                    if ($amount > $from_wallet_balance) {
                        echo 'Insufficient balance';
                        exit();
                    } else {
                        $from_wallet_balance = $from_wallet_balance - $amount;
                        $purchase_amount = $amount * ($from_currency_price / $to_currency_price);
                        $to_wallet_balance = $to_wallet_balance + $purchase_amount;
                        if ($stmt = $con->prepare('UPDATE walletMappingMaster SET wallet_balance = ? WHERE wallet_id = ?')) {
                            $stmt->bind_param('ii', $from_wallet_balance, $from_wallet_id);
                            $stmt->execute();
                            $stmt->close();
                            if ($stmt = $con->prepare('UPDATE walletMappingMaster SET wallet_balance = ? WHERE wallet_id = ?')) {
                                $stmt->bind_param('ii', $to_wallet_balance, $to_wallet_id);
                                $stmt->execute();
                                $stmt->close();
                                if ($stmt = $con->prepare('INSERT INTO transactionMaster (userid, currency_id, currency_purchase_amount, fromWallet, toWallet, remaining_balance, transaction_amount) VALUES (?, ?, ?, ?, ?, ?, ?)')) {
                                    $stmt->bind_param('iiissii', $_SESSION['id'], $to_currency_id, $purchase_amount, $from_wallet, $to_wallet, $from_wallet_balance, $amount);
                                    $stmt->execute();
                                    $stmt->close();
                                    echo '<script>alert("Transaction successfull"); window.location="dashboard.php"</script>';
                                }
                            } else {
                                echo 'Error updating to-wallet balance';
                                exit();
                            }
                        } else {
                            echo 'Error updating from-wallet balance';
                            exit();
                        }
                    }                

                } else if (iset($_POST['from'], $_POST['wallet-address'], $_POST['amount'])) {
                    $from_wallet = $_POST['from'];
                    $to_wallet = $_POST['wallet-address'];
                    $amount = $_POST['amount'];
                    echo '<script>alert("This type of transaction is not yet supported."); window.location="dashboard.php"</script>';
                } else {
                    echo '<script>alert("Please fill all the fields"); window.location="dashboard.php"</script>';
                }

            } else {
                echo '<script>alert("Please verify your KYC first!"); window.location="kyc.php"</script>';
            }
        }
    }
} else {
	echo '<script>window.location="dashboard.php"</script>';
}
?>