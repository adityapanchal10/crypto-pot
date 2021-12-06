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

function changePassword() {

}