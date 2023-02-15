//Function erreur

import $ from "jquery";

export function errorRequest(error_message, tab) {
    $('#main_content').html(
        '<div class="error_content">' +
        '<h1 id="title">Erreur :</h1>' +
        `<p id="description">Erreur lors de la requètes : ${error_message}</p>` +
        '<button class="btn" id="error_refresh">Recharger</button>' +
        '</div>'
    );
    $('#error_refresh')
        .unbind("click")
        .on("click", function () {
            tab.setTab()
        })
}