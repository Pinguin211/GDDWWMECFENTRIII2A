//FONCTION QUI ENVOIE LES DONNEES

const $ = require("jquery");


//////////////////////////////////////////////////////
/// ONGLET PROFIL

export function sendData_profil(user_info, tab) {
    const data = {
        recruter: {
            company_name: $('#company_name').val(),
            address: {
                number: $('#number').val(),
                street_name: $('#street_name').val(),
                city_id: $('#city_id').val()
            }
        },
        candidate: {
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
        }
    }
    if (user_info.roles.includes('ROLE_CANDIDATE'))
        sendCv(user_info, tab)
    sendData('/profil_update_candidate_recruter', JSON.stringify(data), user_info, tab)
}

function sendCv(user_info, tab) {
    const fd = new FormData();
    const files = $('#cv')[0].files[0];
    fd.append('file', files);
    fd.append('id', user_info.id)
    fd.append('password', user_info.password)

    $.ajax({
        url: '/profil_update_cv',
        type: 'post',
        data: fd,
        contentType: false,
        processData: false,
        dataType: 'text'
    })
        .done(function (response) {
            if (response) {
                alert(response)
                tab.setTab()
            }
        })
        .fail(function (error) {
            alert(error.responseText)
        })
}


//////////////////////////////////////////////////////////
/// ONGLET MES OFFRES

export function sendData_Offer(user_info, tab) {
    const offers_ids = getChecboxValue()
    const action_type = $('#offer_opt').val()

    if (action_type === '0')
        alert('Veuillez choisir une action')
    else if (offers_ids === [])
        alert("Vous n'avez pas selectionné d'offre")
    else {
        sendData('/profil_make_recruter_action_on_offers',
            JSON.stringify({'offers_ids' : offers_ids, 'action_type': action_type}),
            user_info, tab)
    }
}


//////////////////////////////////////////////
/// ONGLET GESTION CANDIDATS

export function sendData_approve_candidates(user_info, tab) {
    sendDataApprove('/profil_approves_candidates', user_info, tab,
        'Vous n\'avez pas selectionné de candidats')
}

/////////////////////////////////////////
//ONGLETS GESTION RECRUTER

export function sendData_approve_recruters(user_info, tab) {
    sendDataApprove('/profil_approves_recruters', user_info, tab,
        'Vous n\'avez pas selectionné de recruteurs')
}

/////////////////////////////////////////
//ONGLETS GESTION ANNONCES

export function sendData_approve_offers(user_info, tab) {
    sendDataApprove('/profil_approves_offers', user_info, tab,
        'Vous n\'avez pas selectionné d\'annonces')
}

/////////////////////////////////////////
//ONGLETS GESTION POSTULANCES

export function sendData_approve_applieds(user_info, tab) {
    sendDataApprove('/profil_approves_applieds', user_info, tab,
        'Vous n\'avez pas selectionné de candidats')
}


/////////////////////////////////////////
//ONGLETS ADMIN PAGE

export function sendData_admin_page(user_info, tab) {
    sendDataApprove('/profil_remove_consultants', user_info, tab,
        'Vous n\'avez pas selectionné de consultant')
}


///////////////////////////////////////////////
// FONCTION COMMUNE


function sendDataApprove(url, user_info, tab, no_select_alert) {
    const ids = getChecboxValue()

    if (ids.length < 1)
        alert(no_select_alert)
    else {
        sendData(url,
            JSON.stringify(ids), user_info, tab)
    }
}

function getChecboxValue(id_checkbox = '#checkbox') {
    let arr_id = []
    let i = 1
    while ($(id_checkbox + i.toString()).val()) {
        if ($(id_checkbox + i.toString()).is(':checked'))
            arr_id.push($(id_checkbox + i.toString()).val())
        i++
    }
    return arr_id
}

function sendData(url, json_info, user_info, tab) {
    $.ajax({
        url: url,
        method: 'POST',
        data: {id: user_info.id, password: user_info.password, info: json_info},
        dataType: 'text'
    })
        .done(function (response) {
            alert(response)
            tab.setTab()
        })
        .fail(function (error) {
            alert(error.responseText)
        })
}