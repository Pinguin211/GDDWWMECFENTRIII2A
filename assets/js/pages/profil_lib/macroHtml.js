//MACRO HTML

import {checkbox, table, tbody, td, th, thead, tr, i, a} from "../../lib/htmlMacro";

export function createTable(id, arr_class_name, arr_value_head, infos, type) {
    return table({'id': id}, [
        thead([],[tr([], createTheadContents(arr_class_name, arr_value_head, id))]),
        tbody([], getBodyContent(id, arr_class_name, infos, type))
    ])
}

function getBodyContent(id, classes, infos, type) {
    let tds = []
    let nb = 1;
    infos.forEach(function (info) {
        tds.push(getRowContentByType(id, info, classes, nb.toString(), type))
        nb++
    })
    return tds
}

function getRowContentByType(id, info, classes, nb, type)
{
    switch (type) {
        case 'offer':
            return  getRowContent_offer(id, info, classes, nb)
        case 'candidate':
            return getRowContent_candidate(id, info, classes, nb)
        case 'recruter':
            return getRowContent_recruter(id, info, classes, nb)
        case 'approve_offer':
            return getRowContent_approve_offer(id, info, classes, nb)
        case 'approve_applied':
            return getRowContent_approve_applied(id, info, classes, nb)
        case 'consultant':
            return getRowContent_consultant(id, info, classes, nb)
        default:
            return 'Une erreur est survenue, impossible liste non afficher'
    }
}

function createTheadContents(classes, arr_class_value, id) {
    let ths = []
    let i = 0
    while (classes[i]) {
        ths.push(th(arr_class_value[i], {'class': id + '_' + classes[i]}))
        i++
    }
    return ths
}


///////////////////////////////////////////////////
// ONGLET APPROUVE POSTULANCES

function getRowContent_consultant(id, info, classes, nb) {
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.email ?? 'Non transmis', {'class': id + '_' + classes[1]}),
        ])
}



/////////////////////////////////////////////////////
// ONGLET APPROUVE POSTULANCES


function getRowContent_approve_applied(id, info, classes, nb) {
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.email ?? 'Non transmis', {'class': id + '_' + classes[1]}),
        td(info.first_name ?? 'Non transmis', {'class': id + '_' + classes[2]}),
        td(info.last_name ?? 'Non transmis', {'class': id + '_' + classes[3]}),
        td(info.cv_id ?
            a('', {'class' : 'bi bi-file-earmark-person', 'href' : `/cv/${info.cv_id}.pdf`, 'target': '_blank'}) :
            i('', {'class' : 'bi bi-file-earmark-x'}), {'class' : id + '_' + classes[4]}),
        td(a(i('',{'class': 'bi bi-eye'}), {'href': '/annonce_detail?id=' + info.offer_id, 'target' : '_blank'}), {'class': id + '_' + classes[4]}),
    ])
}


/////////////////////////////////////////////////////
// ONGLET APPROUVE ANNONCES


function getRowContent_approve_offer(id, info, classes, nb) {
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.title ?? 'Non transmis', {'class': id + '_' + classes[1]}),
        td(info.company_name ?? 'Non transmis', {'class': id + '_' + classes[2]}),
        td(info.post_date ?? 'Non transmis', {'class': id + '_' + classes[3]}),
        td(a(i('',{'class': 'bi bi-eye'}), {'href': '/annonce_detail?id=' + info.id, 'target' : '_blank'}), {'class': id + '_' + classes[4]}),
    ])
}



/////////////////////////////////////////////////////
// ONGLET GESTION CANDIDATS


function getRowContent_candidate(id, info, classes, nb) {
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.email ?? 'Non transmis', {'class': id + '_' + classes[1]}),
        td(info.first_name ?? 'Non transmis', {'class': id + '_' + classes[2]}),
        td(info.last_name ?? 'Non transmis', {'class': id + '_' + classes[3]}),
        td(info.cv_id ?
            a('', {'class' : 'bi bi-file-earmark-person', 'href' : `/cv/${info.cv_id}.pdf`, 'target': '_blank'}) :
            i('', {'class' : 'bi bi-file-earmark-x'}), {'class' : id + '_' + classes[4]}),
    ])
}

////////////////////////////////////////////////////
// ONGLET GESTION RECRUTEURS

function getRowContent_recruter(id, info, classes, nb) {
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.email ?? 'Non transmis', {'class': id + '_' + classes[1]}),
        td(info.company_name ?? 'Non transmis', {'class': id + '_' + classes[2]}),
        td(info.address_name ?? 'Non transmis', {'class': id + '_' + classes[3]}),
    ])
}

/////////////////////////////////////////////////////
// ONGLET MES OFFRES

function getRowContent_offer(id, info, classes, nb) {
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.title, {'class': id + '_' + classes[1]}),
        td(info.post_date, {'class': id + '_' + classes[2]}),
        td(info.validated ? 'Validé' : 'Non validé', {'class': id + '_' + classes[3]}),
        td(a(i('',{'class': 'bi bi-eye'}), {'href': '/annonce_detail?id=' + info.id, 'target' : '_blank'}), {'class': id + '_' + classes[4]}),
        td(a(i('',{'class': 'bi bi-pencil'}), {'href': '/modifier_annonce?id=' + info.id, 'target' : '_blank'}), {'class': id + '_' + classes[5]}),
    ])
}
