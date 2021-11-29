$(()=>{
    $("#loading").show();
    $.post("app/ajax.php", {action: 'check_codes'}, (data) => {
    if (data.success != true) {
        openModal("<h1><b class='color-red'>Pri načítaní údajov sa vyskytla chyba! Skúste to neskôr.</b></h1>", true);
    } else {
        $("#loading").fadeOut(500, ()=>$("#content").fadeIn(500));
        $("#table").append(decode(data.data));
    }
}, 'json').fail(() => {
    $("#loading").hide();
    openModal("<h1><b class='color-red'>Pri načítaní údajov sa vyskytla chyba! Skúste to neskôr.</b></h1>", true);
});
});

$("#addcode_btn").click(()=> {
    var code = $("#addcode_input").val();
    if (code.length == 0) {
        openModal("<b class='color-red'>Nebol zadaný žiadny kód!</b>", false);
        return;
    }

    var codeRegex = new RegExp('[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}');
    if (codeRegex.test(code) && code.length == 14) {
        $("#addcode_input").prop("disabled", true);
        $("#addcode_btn").prop("disabled", true);
        addCode(code, ()=>{
            $("#addcode_input").prop("disabled", false).val('');
            $("#addcode_btn").prop("disabled", false);
        });
    } else {
        openModal("<b class='color-red'>Zadaný kód je neplatný!</b>", false);
    }
});

function decode(string) {
    return decodeURIComponent(atob(string).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
}

function openModal(content, sticky) {
    $("#modal").html(content);

    if (sticky) {
        $("#modal").modal({
            escapeClose: false,
            clickClose: false,
            showClose: false
        });
    } else {
        $("#modal").modal();
    }
}

function addCode(code, callback) {
    $.post("app/ajax.php", {action: "add_code", code: code}, (data) => {
        if (data.success) {
            $("#table").append(decode(data.data));
            callback(true);
        } else {
            openModal("<b class='color-red'>"+data.error+"</b>", false);
            callback(false);
        }
    }, 'json');
}

function deleteCode(code) {
    if (confirm("Ste si istý, že chcete zrušiť sledovanie tohto kódu?")) {
        $("#cc_"+code).remove();
        $.post("app/ajax.php", {action: 'delete_code', code: code});
    }
}