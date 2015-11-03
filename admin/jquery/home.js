var in_enter_handler = function(event) {
    if(event.which == 13) { // enter
        if($(this).val().length > 5)
            send_data($(this).serialize());
        else {
            dialog_open("messages",returnMessage(false, mozilo_lang["home_error_test_mail"]+"<br \/><br \/><b>"+$(this).val()+"<\/b>"));
        }
        return false;
    }
};

$(function() {
    $('input[name="test_mail_adresse"]').bind("keydown", in_enter_handler);

    if(modrewrite != "false")
        test_modrewrite();
});