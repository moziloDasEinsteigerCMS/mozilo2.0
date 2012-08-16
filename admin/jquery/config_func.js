function make_para(item) {
    var para = $(item).serialize();
    if($(item).attr("type") == "checkbox" && para.length < 1) {
        para = $(item).attr("name")+"=false";
    }
    send_data("chanceconfig=true&"+para);
}

function test_newname(name) {
    if(name.length >= 5)
        return true;
    else
        return false;
}

function test_newpw(pw) {
    if(pw.length >= 6) {
        var nr_search = /\d/;
        /* das password hat keine zahl und kein grossen buchstaben */
        if(!nr_search.test(pw) || pw.toLowerCase() == pw || pw.toUpperCase() == pw) {
            return false;
        } else
            return true;
    } else
        return false;
}

