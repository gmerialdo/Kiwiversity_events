document.addEventListener("DOMContentLoaded", function() {
    var elemsDropdown = document.querySelectorAll(".dropdown-trigger");
    var instancesDropdown = M.Dropdown.init(elemsDropdown, {constrainWidth: false, coverTrigger: false, hover:true});
    var elemsSidenav = document.querySelectorAll(".sidenav");
    var instancesSidenav = M.Sidenav.init(elemsSidenav);
    var elemsSelectNbTickets = document.querySelectorAll("select");
    var instancesSelectNbTickets = M.FormSelect.init(elemsSelectNbTickets);
});

function test_pass() {
    var level = "";
    try {
        var mdp = document.getElementById("new_password").value;
        var size = mdp.length;
        if (size < 6) {
            level="Password too short"; 
            document.getElementById("level_mdp").classList.add("red-text");
            return level;
        }
        var security = 0;
        var digit = new RegExp("[0-9]","gi");
        var special = new RegExp("[^a-zA-Z0-9]","gi");
        var chrs = new Array();
        for (var i=0; i<size; i++){
            var c = mdp.charAt(i);
            chrs[c] = 0;
        }
        for (var i=0;i<size;i++){
            var c = mdp.charAt(i);
            var cv = 0;
            chrs[c] = chrs[c] + 1
            if (chrs[c] < 4){
                cv = 1;
                if (digit.test(c)){
                    cv = 2;
                }
                else {
                    if (special.test(c)){
                        cv = 3;
                    }
                }
            }
            security = security + cv
        }
        if (security >= 13){
            level="Level of security: High";
            document.getElementById("level_mdp").classList.add("green-text");
        }
        else if (security >= 9){
            level="Level of security: Average";
            document.getElementById("level_mdp").classList.add("green-text");
        }
        else {
            level="Level of security: Low";
            document.getElementById("level_mdp").classList.add("red-text");
        }
    }
    finally {
        return level;
    }
}

function checkSecurityPW(){
    document.getElementById("level_mdp").innerHTML=test_pass();
}

function highlight(field, error){
    if(error){
        field.style.backgroundColor = "#FFD9CF";
        document.getElementById("no_match").innerHTML="Password doesn't match";
    }
    else {
        field.style.backgroundColor = "";
        document.getElementById("no_match").innerHTML="";
    }
}

function verifyPw(field){
    if(field.value !== document.getElementById("new_password").value){
        highlight(field, true);
        return false;
    }
    else {
        highlight(field, false);
        return true;
    }
}

