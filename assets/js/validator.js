function loginValidator() {
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    if (email == '') {
        document.getElementById("email").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (password == '') {
        document.getElementById("password").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else {
        return true;
    }

}

function signupValidator() {
    var passwordregex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/;
    var password = document.getElementById("password").value;
    var verifypassword = document.getElementById("password-verify").value;
    var email = document.getElementById("email").value;
    var fname = document.getElementById("fname").value;
    var lname = document.getElementById("lname").value;
    var mobile = document.getElementById("mobile").value;
    
    if (email == '') {
        document.getElementById("email").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (password == '') {
        document.getElementById("password").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (fname == '') {
        document.getElementById("fname").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (lname == '') {
        document.getElementById("lname").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (mobile == '') {
        document.getElementById("mobile").parentElement.parentElement.classList.add("focus");
        $( "<style>.four.input-div.focus::before, .four.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (verifypassword == '') {
        document.getElementById("password-verify").parentElement.parentElement.classList.add("focus");
        $( "<style>.conf-pass.input-div.focus::before, .conf-pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    if (!passwordregex.test(password)) {
        $('.pass.input-div.focus::before').css('background-color: #ff0000;');
        $('.pass.input-div.focus::after').css('background-color: #ff0000;');
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        document.getElementById("password-message").style.color = "red";
        return false;
    }
    else if (password != verifypassword) {
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        $( "<style>.conf-pass.input-div.focus::before, .conf-pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        document.getElementById("password-verify").parentElement.parentElement.classList.add("focus");
        document.getElementById("password-message").style.color = "red";
        document.getElementById("password-message").innerHTML = "Passwords do not match.";
        return false;
    }
    else {
        return true;
    }
}

function changePasswordValidator() {
    var passwordregex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/;
    var old_password = document.getElementById("old-password").value;
    var new_password = document.getElementById("new-password").value;
    var verify_password = document.getElementById("password-verify").value;
    if (old_password == '') {
        document.getElementById("old-password").style.borderColor = "red";
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (new_password == '') {
        document.getElementById("new-password").style.borderColor = "red";
        return false;
    }
    else if (verify_password == '') {
        document.getElementById("password-verify").style.borderColor = "red";
        return false;
    }
    if (!passwordregex.test(new_password)) {
        document.getElementById("new-password").style.borderColor = "red";
        document.getElementById("password-message").style.color = "red";
        return false;
    }
    else if (new_password != verify_password) {
        document.getElementById("new-password").style.borderColor = "red";
        document.getElementById("password-verify").style.borderColor = "red";
        document.getElementById("password-message").innerHTML = "Passwords do not match.";
        return false;
    }
    else {
        return true;
    }
}

function forgotPasswordValidator() {
    var passwordregex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/;
    var new_password = document.getElementById("new-password").value;
    var verify_password = document.getElementById("password-verify").value;
    if (new_password == '') {
        document.getElementById("new-password").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        $( "<style>.conf-pass.input-div.focus::before, .conf-pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    else if (verify_password == '') {
        document.getElementById("password-verify").parentElement.parentElement.classList.add("focus");
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        $( "<style>.conf-pass.input-div.focus::before, .conf-pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        return false;
    }
    if (!passwordregex.test(new_password)) {
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        $( "<style>.conf-pass.input-div.focus::before, .conf-pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        document.getElementById("password-message").style.color = "red";
        return false;
    }
    else if (new_password != verify_password) {
        $( "<style>.pass.input-div.focus::before, .pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        $( "<style>.conf-pass.input-div.focus::before, .conf-pass.input-div.focus:after{ background-color: #ff0000; }</style>" ).appendTo( "head" );
        document.getElementById("password-message").style.color = "red";
        document.getElementById("password-message").innerHTML = "Passwords do not match.";
        return false;
    }
    else {
        return true;
    }
}