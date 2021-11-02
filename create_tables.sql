CREATE TABLE userMaster (
    userid BIGINT(20) AUTO_INCREMENT PRIMARY KEY NOT NULL,
	first_name VARCHAR(100),
	last_name VARCHAR(100),
	email_id VARCHAR(150) UNIQUE NOT NULL,
	country VARCHAR(20),
	mobile VARCHAR(20) UNIQUE NOT NULL,
	isVerified BOOLEAN default 0 NOT NULL,
	email_verfication_code VARCHAR(6),
	password VARCHAR(300) NOT NULL,
	account_transaction_limit INT(10),
	is_KYC_request_sent BOOLEAN default 0 NOT NULL,
	isKYCverified BOOLEAN default 0 NOT NULL,
	sign_up_date TIMESTAMP,
	recovery_code VARCHAR(100) UNIQUE NOT NULL,
	init_account_balance double(10,2) NOT NULL,
	remaining_balance double(10,2) NOT NULL,
	lastLogin DATETIME NOT NULL,
	lastLoginIPv4 VARCHAR(15),
    lastLoginIPv6 VARCHAR(39),
	lastLogin_http_user_agent VARCHAR(100) NOT NULL,
	isMFAEnabled BOOLEAN default 0 NOT NULL,
	MFA VARCHAR(6),
	timezone VARCHAR(150),
	isDisabled BOOLEAN default 0,
	isDeleted BOOLEAN default 0
);

CREATE TABLE accountBalanceMaster (
    userid BIGINT(20) PRIMARY KEY NOT NULL REFERENCES userMaster(userid),
	remaining_account_balance double(10,2),
	last_debit double(10,2),
	last_debit_timestamp TIMESTAMP,
	last_currency_purchase VARCHAR(20)
);

CREATE TABLE walletMaster (
	wallet_id BIGINT(20) PRIMARY KEY NOT NULL,
	wallet_type VARCHAR(20) NOT NULL,
	wallet_name VARCHAR(20) UNIQUE NOT NULL
);

CREATE TABLE walletMappingMaster (
	userid BIGINT(20) PRIMARY KEY NOT NULL REFERENCES userMaster(userid),
	wallet_id BIGINT(20) UNIQUE NOT NULL REFERENCES walletMaster(walletid),
	currency_id BIGINT(20) NOT NULL REFERENCES priceMaster(currency_id),
	wallet_address VARCHAR(30) UNIQUE NOT NULL,
	wallet_private_key VARCHAR(40)  NOT NULL,
	wallet_balance BIGINT(20) NOT NULL,
	wallet_last_balance BIGINT(20) NOT NULL,
	isWalletActive BOOLEAN default 0 NOT NULL
);

CREATE TABLE priceMaster (
	currency_id BIGINT(20) NOT NULL PRIMARY KEY,
	currency_name VARCHAR(200) NOT NULL,
	currency_price double(10,2) NOT NULL,
	currency_price_update_timestamp DATETIME NOT NULL,
	currency_last_price double(10,2),
	disable_currency_trade BOOLEAN
);

CREATE TABLE transactionMaster (
	userid BIGINT(20) NOT NULL REFERENCES userMaster(userid),
	transaction_id BIGINT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
	currency_id VARCHAR(3) NOT NULL REFERENCES priceMaster(currency_id),
	currency_purchase_amount double(10,2) NOT NULL,
	fromWallet VARCHAR(10) NOT NULL,
	toWallet VARCHAR(10) NOT NULL,
	remaining_balance double(10,2) NOT NULL,
	transaction_amount double(10,2) NOT NULL,
	isTransactionApproved BOOLEAN default 1,
	isTransactionBlocked BOOLEAN default 0,
	transaction_time TIME,
	transaction_approved_time TIME,
	timezone VARCHAR(150)
);

CREATE TABLE contactMaster (
	sr_no BIGINT(5) AUTO_INCREMENT PRIMARY KEY NOT NULL,
	first_name VARCHAR(100) NOT NULL,
	email_addr VARCHAR(150) NOT NULL,
	subject VARCHAR(200) NOT NULL,
	quick_comment text NOT NULL
);

CREATE TABLE logMaster (
	userid BIGINT(20) NOT NULL REFERENCES userMaster(userid),
	loginDatetime DATETIME NOT NULL,
	loginIPv4 VARCHAR(15),
	loginIPv6 VARCHAR(39),
	login_http_user_agent VARCHAR(100) NOT NULL,
	PRIMARY KEY (userid, loginDatetime)
);

CREATE TABLE kycMaster (
    userid BIGINT(20) PRIMARY KEY NOT NULL REFERENCES userMaster(userid),
    fname VARCHAR(100),
    mname VARCHAR(100),
    lname VARCHAR(100),
    email VARCHAR(150),
    gender VARCHAR(20),
    addressLine1 VARCHAR(100),
    addressLine2 VARCHAR(100),
    city VARCHAR(100),
    state VARCHAR(100),
    zipCode INT(10),
    documentType VARCHAR(20),
    document LONGBLOB
);