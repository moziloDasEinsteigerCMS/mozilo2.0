function make_para(item,user_para) {
    var para = $(item).serialize();
    if($(item).is('[multiple]') && para.length < 1) {
        para = $(item).attr("name").replace(/\[/g, '%5B').replace(/\]/g, '%5D')+"=null";
    }
    if($(item).attr("type") == "checkbox" && para.length < 1) {
        para = $(item).attr("name")+"=false";
    }
// $("#out").html($("#out").html()+"<br />para="+user_para+para);
    send_data(user_para+para);
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
