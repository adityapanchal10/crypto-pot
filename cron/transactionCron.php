<?php

include "../db_connect.php";

function approve_transactions($con)
{
    $stmt = $con->prepare('SELECT userid, transaction_id, fromWallet, toWallet, transaction_amount FROM transactionMaster WHERE isTransactionApproved = 0 AND isTransactionBlocked = 0');
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userid, $transaction_id, $from_wallet, $to_wallet, $transaction_amount);
        while ($stmt->fetch()) {
            if ($from_wallet == $to_wallet) {
                $stmt_2 = $con->prepare('UPDATE transactionMaster SET isTransactionBlocked = 1 WHERE transaction_id = ?');
                $stmt_2->bind_param('i', $transaction_id);
                $stmt_2->execute();
                $stmt_2->close();
            } else {
                $amount = $transaction_amount;
                if ($stmt_3 = $con->prepare('SELECT currency_id, currency_price FROM priceMaster WHERE currency_name = ?')) {
                    $stmt_3->bind_param('s', $from_wallet);
                    $stmt_3->execute();
                    $stmt_3->store_result();
                    $stmt_3->bind_result($from_currency_id, $from_currency_price);
                    $stmt_3->fetch();
                    $stmt_3->close();
                    if ($stmt_4 = $con->prepare('SELECT wallet_id, wallet_balance FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                        $stmt_4->bind_param('ii', $from_currency_id, $userid);
                        $stmt_4->execute();
                        $stmt_4->store_result();
                        $stmt_4->bind_result($from_wallet_id, $from_wallet_balance);
                        $stmt_4->fetch();
                        $stmt_4->close();
                    } else {
                        echo $stmt_4->error;
                        exit();
                    }
                } else {
                    echo 'Error fetching from-currency price';
                    exit();
                }
                if ($stmt_3 = $con->prepare('SELECT currency_id, currency_price FROM priceMaster WHERE currency_name = ?')) {
                    $stmt_3->bind_param('s', $to_wallet);
                    $stmt_3->execute();
                    $stmt_3->store_result();
                    $stmt_3->bind_result($to_currency_id, $to_currency_price);
                    $stmt_3->fetch();
                    $stmt_3->close();
                    if ($stmt_4 = $con->prepare('SELECT wallet_id, wallet_balance FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                        $stmt_4->bind_param('ii', $to_currency_id, $userid);
                        $stmt_4->execute();
                        $stmt_4->store_result();
                        $stmt_4->bind_result($to_wallet_id, $to_wallet_balance);
                        $stmt_4->fetch();
                        $stmt_4->close();
                    } else {
                        echo 'Error fetching to-wallet balance';
                        exit();
                    }
                } else {
                    echo 'Error fetching to-currency price';
                    exit();
                }
                if ($amount > $from_wallet_balance) {
                    $stmt_2 = $con->prepare('UPDATE transactionMaster SET isTransactionBlocked = 1 WHERE transaction_id = ?');
                    $stmt_2->bind_param('i', $transaction_id);
                    $stmt_2->execute();
                    $stmt_2->close();
                } else {
                    $from_wallet_balance = $from_wallet_balance - $amount;
                    $purchase_amount = $amount * ($from_currency_price / $to_currency_price);
                    $to_wallet_balance = $to_wallet_balance + $purchase_amount;
                    if ($stmt_5 = $con->prepare('UPDATE walletMappingMaster SET wallet_last_balance = wallet_balance, wallet_balance = ? WHERE wallet_id = ?')) {
                        $stmt_5->bind_param('di', $from_wallet_balance, $from_wallet_id);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else {
                        echo 'Error updating from-wallet balance';
                        exit();
                    }
                    if ($stmt_5 = $con->prepare('UPDATE walletMappingMaster SET wallet_last_balance = wallet_balance,  wallet_balance = ? WHERE wallet_id = ?')) {
                        $stmt_5->bind_param('di', $to_wallet_balance, $to_wallet_id);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else {
                        echo 'Error updating to-wallet balance';
                        exit();
                    }
                    if ($to_wallet == 'USD') {
                        $stmt_5 = $con->prepare('UPDATE userMaster SET remaining_balance = ? WHERE userid = ?');
                        $stmt_5->bind_param('di', $to_wallet_balance, $userid);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else if ($from_wallet == 'USD') {
                        $stmt_5 = $con->prepare('UPDATE userMaster SET remaining_balance = ? WHERE userid = ?');
                        $stmt_5->bind_param('di', $from_wallet_balance, $userid);
                        $stmt_5->execute();
                        $stmt_5->close();
                    }
                    if ($stmt_2 = $con->prepare('UPDATE transactionMaster SET currency_purchase_amount = ?, remaining_balance = ?, isTransactionApproved = 1, transaction_approved_time = CURDATE() WHERE transaction_id = ?')) {
                        if ($stmt_2->bind_param('ddi', $purchase_amount, $from_wallet_balance, $transaction_id)) {
                            if ($stmt_2->execute()) {
                                echo 'Transaction approved';
                            } else {
                                echo 'Error updating transaction' . $stmt_2->error;
                                exit();
                            }
                        } else {
                            echo 'Error binding params' . $stmt_2->error;
                            exit();
                        }

                        $stmt_2->close();
                    } else {
                        echo 'Transaction rejected';
                    }
                }
            }
        }
    }
    $stmt->close();
}

function approve_transfers($con)
{
    $stmt = $con->prepare('SELECT userid, transfer_id, fromWallet, toWallet, toWalletAddress, transfer_amount, transfer_amount_recieved FROM transferMaster WHERE isTransferApproved = 0 AND isTransferBlocked = 0');
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // echo '<h3>Transfer 1</h3>';
        $stmt->bind_result($userid, $transfer_id, $from_wallet, $to_wallet, $to_wallet_address, $transfer_amount, $transfer_amount_recieved);
        while ($stmt->fetch()) {
            if ($from_wallet != $to_wallet) {
                $stmt_2 = $con->prepare('UPDATE transferMaster SET isTransferBlocked = 1 WHERE transfer_id = ?');
                $stmt_2->bind_param('i', $transfer_id);
                $stmt_2->execute();
                $stmt_2->close();
            } else {
                // echo '<h3>Transfer 2</h3>';
                $amount = $transfer_amount;
                if ($stmt_3 = $con->prepare('SELECT currency_id FROM priceMaster WHERE currency_name = ?')) {
                    $stmt_3->bind_param('s', $from_wallet);
                    $stmt_3->execute();
                    $stmt_3->store_result();
                    $stmt_3->bind_result($from_currency_id);
                    $stmt_3->fetch();
                    $stmt_3->close();
                    if ($stmt_4 = $con->prepare('SELECT wallet_id, wallet_balance, wallet_address FROM walletMappingMaster WHERE currency_id = ? AND userid = ?')) {
                        $stmt_4->bind_param('ii', $from_currency_id, $userid);
                        $stmt_4->execute();
                        $stmt_4->store_result();
                        $stmt_4->bind_result($from_wallet_id, $from_wallet_balance, $from_wallet_address);
                        $stmt_4->fetch();
                        $stmt_4->close();
                    } else {
                        echo $stmt_4->error;
                        exit();
                    }
                } else {
                    echo 'Error fetching from-currency id';
                    exit();
                }
                if ($stmt_3 = $con->prepare('SELECT wallet_id, wallet_balance, currency_id FROM walletMappingMaster WHERE wallet_address = ?')) {
                    $stmt_3->bind_param('s', $to_wallet_address);
                    $stmt_3->execute();
                    $stmt_3->store_result();
                    $stmt_3->bind_result($to_wallet_id, $to_wallet_balance, $to_currency_id);
                    $stmt_3->fetch();
                    $stmt_3->close();
                } else {
                    echo 'To-wallet not found, proceding with transfer';

                    // if to_wallet address wrong, asset is still deducted as in real life scenario

                    $from_wallet_balance = $from_wallet_balance - $amount;

                    if ($stmt_5 = $con->prepare('UPDATE walletMappingMaster SET wallet_last_balance = wallet_balance, wallet_balance = ? WHERE wallet_id = ?')) {
                        $stmt_5->bind_param('di', $from_wallet_balance, $from_wallet_id);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else {
                        echo 'Error updating from-wallet balance';
                        exit();
                    }

                    if ($from_wallet == 'USD') {
                        $stmt_5 = $con->prepare('UPDATE userMaster SET remaining_balance = ? WHERE userid = ?');
                        $stmt_5->bind_param('di', $from_wallet_balance, $userid);
                        $stmt_5->execute();
                        $stmt_5->close();
                    }

                    if ($stmt_2 = $con->prepare('UPDATE transferMaster SET transfer_amount_recieved = ?, remaining_balance = ?, isTransferApproved = 1, transfer_approved_time = CURDATE() WHERE transfer_id = ?')) {
                        if ($stmt_2->bind_param('ddi', $transfer_amount_recieved, $from_wallet_balance, $transfer_id)) {
                            if ($stmt_2->execute()) {
                                echo 'Transfer approved';
                            } else {
                                echo 'Error updating transfer' . $stmt_2->error;
                                exit();
                            }
                        } else {
                            echo 'Error binding params' . $stmt_2->error;
                            exit();
                        }

                        $stmt_2->close();
                    } else {
                        echo 'Transfer rejected';
                    }

                    exit();
                }
                if ($amount > $from_wallet_balance) {
                    $stmt_2 = $con->prepare('UPDATE transferMaster SET isTransferBlocked = 1 WHERE transfer_id = ?');
                    $stmt_2->bind_param('i', $transfer_id);
                    $stmt_2->execute();
                    $stmt_2->close();
                } else {
                    // echo '<h3>Transfer 3</h3>';
                    $from_wallet_balance = $from_wallet_balance - $amount;
                    // $transfer_amount_recieved = $amount * ($from_currency_price / $to_currency_price);
                    $to_wallet_balance = $to_wallet_balance + $transfer_amount_recieved;
                    if ($stmt_5 = $con->prepare('UPDATE walletMappingMaster SET wallet_last_balance = wallet_balance, wallet_balance = ? WHERE wallet_id = ?')) {
                        $stmt_5->bind_param('di', $from_wallet_balance, $from_wallet_id);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else {
                        echo 'Error updating from-wallet balance';
                        exit();
                    }
                    if ($stmt_5 = $con->prepare('UPDATE walletMappingMaster SET wallet_last_balance = wallet_balance,  wallet_balance = ? WHERE wallet_id = ?')) {
                        $stmt_5->bind_param('di', $to_wallet_balance, $to_wallet_id);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else {
                        echo 'Error updating to-wallet balance';
                        exit();
                    }
                    if ($to_wallet == 'USD') {
                        $stmt_5 = $con->prepare('UPDATE userMaster SET remaining_balance = ? WHERE userid = ?');
                        $stmt_5->bind_param('di', $to_wallet_balance, $userid);
                        $stmt_5->execute();
                        $stmt_5->close();
                    } else if ($from_wallet == 'USD') {
                        $stmt_5 = $con->prepare('UPDATE userMaster SET remaining_balance = ? WHERE userid = ?');
                        $stmt_5->bind_param('di', $from_wallet_balance, $userid);
                        $stmt_5->execute();
                        $stmt_5->close();
                    }
                    if ($stmt_2 = $con->prepare('UPDATE transferMaster SET transfer_amount_recieved = ?, remaining_balance = ?, isTransferApproved = 1, transfer_approved_time = CURDATE() WHERE transfer_id = ?')) {
                        // echo '<h3>Transfer 4</h3>';
                        if ($stmt_2->bind_param('ddi', $transfer_amount_recieved, $from_wallet_balance, $transfer_id)) {
                            if ($stmt_2->execute()) {
                                echo 'Transfer approved';
                            } else {
                                echo 'Error updating transfer' . $stmt_2->error;
                                exit();
                            }
                        } else {
                            echo 'Error binding params' . $stmt_2->error;
                            exit();
                        }

                        $stmt_2->close();
                    } else {
                        echo 'Transfer rejected';
                    }
                }
            }
        }
    }
    $stmt->close();
}

approve_transactions($con);
approve_transfers($con);

?>