function outd_open_media_window() {
    let frame = wp.media({
        title: 'Insira a mídia',
        multiple: true,
        button: { text: 'Inserir' }
    });


    frame.on('select', function () {

        let ids = 0;
        selection = frame.state().get('selection');
        selection.map(function (attachment) {
            ids += ',' + attachment.id;
        });

        let html = '';
        html += '<form style="display:none" method="post" id="outd_add_media_form">';
        html += '<input type="hidden" name="outd_add_media" value="' + ids + '">';
        html += '</form>';

        document.body.innerHTML += html;
        document.getElementById("outd_add_media_form").submit();
    });


    frame.open();

}

function outd_delete(el) {
    if (confirm('Deseja apagar este item?')) {
        window.location.href = el.getAttribute("data-href");
    }
}


function outd_visible(a, b) {
    let el_a = document.getElementById(a);
    let el_b = document.getElementById(b);
    if (el_a.style.display == 'none') {
        el_a.style.display = '';
        el_b.style.display = 'none';
    } else {
        el_a.style.display = 'none';
        el_b.style.display = '';
    }
}

function outd_action(action, id, field, value) {
    els = document.getElementsByName("action_row");
    for (let i = 0; i < els.length; i++) {
        els[i].value = action;
    }
    els = document.getElementsByName("id_row");
    for (let i = 0; i < els.length; i++) {
        els[i].value = id;
    }
    els = document.getElementsByName("field");
    for (let i = 0; i < els.length; i++) {
        els[i].value = field;
    }
    els = document.getElementsByName("value");
    for (let i = 0; i < els.length; i++) {
        els[i].value = document.getElementById(value).value;
    }
    document.getElementById("doaction").click()
}