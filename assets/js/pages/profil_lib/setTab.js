import {createTable} from "./macroHtml";
import {updateCityList} from "./getData.js";

const $ = require("jquery");


//Set le contenu html des differentes page selon le grade de l'utilisateurs
export function setTabs(tabs, user_info) {
    //Affiche la table active
    setViewTab(tabs, user_info.roles)
    for (let key in tabs) {
        if (tabs[key].activated) {
            tabs[key].setTab()
        }
    }
}

function setViewTab(tabs, roles) {
    if (roles.includes('ROLE_RECRUTER'))
        tabs.my_offer.show_tab()
    if (roles.includes('ROLE_CONSULTANT')) {
        tabs.approve_candidate.show_tab()
        tabs.approve_recruter.show_tab()
        tabs.approve_offer.show_tab()
        tabs.approve_applied.show_tab()
    }
    if (roles.includes('ROLE_ADMIN'))
        tabs.admin_page.show_tab()
}



export function setTabProfil(arr_info, tab) {
    //Partie user
    tab.setContent(
        '<div id="profil_tab">' +
            '<div class="row">' +
                '<label for="email">Email :</label>' +
                `<input type="text" id="email" value="${arr_info.user.email}" disabled>` +
            '</div>'
    )

    //Partie candidat
    if (arr_info.candidate) {
        tab.addContent(
            '<div>' +
                        '<div class="row">' +
                            '<label for="first_name">Prénom :</label>' +
                            `<input type="text" id="first_name" value="${arr_info.candidate.first_name ?? ''}">` +
                        '<div class="row">' +
                        '</div>' +
                            '<label for="last_name">Nom :</label>' +
                            `<input type="text" id="last_name" value="${arr_info.candidate.last_name ?? ''}">` +
                        '</div>' +
                        '<div class="row">' +
                            `<label for="cv">CV :${arr_info.candidate.cv_id ? '(Envoyé)' : '(non remis)'}</label>` +
                            '<form id="cv_upload">' +
                                '<input type="file" id="cv" name="cv">' +
                            '</form>' +
                        '</div>' +
                    '</div>'
        )
    }

    //Partie Recruter
    if (arr_info.recruter) {
        tab.addContent(
            '<div>' +
                    '<div class="row">' +
                        '<label for="company_name">Nom de votre entrprise :</label>' +
                        `<input type="text" id="company_name" value="${arr_info.recruter.company_name ?? ''}">` +
                    '</div>' +
                    '<p>Adresse :</p>' +
                    '<div class="row">' +
                    '<label for="number">Numero :</label>' +
                    `<input type="number" id="number" value="${arr_info.recruter.address?.number ?? ''}">` +
                    '</div>' +
                    '<div class="row">' +
                    '<label for="street_name">Nom de la rue :</label>' +
                    `<input type="text" id="street_name" value="${arr_info.recruter.address?.street_name ?? ''}">` +
                    '</div>' +
                    '<div class="row">' +
                    '<label for="city_id">Ville</label>' +
                    '<select id="city_id">' +
                    `<option value="${arr_info.recruter.address?.city_id ?? ''}">${arr_info.recruter.address?.city_name ?? 'Choisissez une ville'}</option>` +
                    '</select>' +
                    '</div>' +
                    '</div>'
        )
    }
    tab.addContent('</div>')
    tab.showContent()
    if (arr_info.recruter)
        updateCityList()
}


export function setTabOffer(arr_info, tab) {
    tab.setContent(
        '<div id="offer_tab">' +
            '<div>' +
                '<label for="offer_opt">Action à realiser :</label>' +
                '<select id="offer_opt">' +
                '<option value="0">Choisir une action</option>' +
                '<option value="1">Archiver</option>' +
                '<option value="2">Desarchiver</option>' +
                '<option value="3">Suprimer</option>' +
                '</select>' +
            '</div>' +
            '<div>' +
                createTable('list',['checkbox', 'title_o', 'date_o', 'status', 'detail', 'mod'],
                    ['', 'Titre', 'Date', 'Status', 'Détail', 'Modifier'], arr_info, 'offer') +
            '</div>' +
        '</div>'
    )
    tab.showContent()

    let nb = 1
    arr_info.forEach(function (info) {
        if (info.archived)
            $(`#tr${nb}`).css('color', 'orange')
        else
            $(`#tr${nb}`).css('color', 'green')
        nb++
    })
}


export function setTabApproveCandidates(arr_info, tab) {

    tab.setContent(setContentApproveTab(
        'approve_candidate_tab',
        'Selectionner les utilisateurs à validé :',
        createTable('list',['checkbox', 'email_u', 'first_name', 'last_name', 'cv_id'],
            ['','Email', 'Prénom', 'Nom', 'Cv'], arr_info, 'candidate'),
    ))
    tab.showContent()

    let nb = 1
    arr_info.forEach(function (info) {
        if (!info.last_name || !info.first_name || !info.cv_id)
            $(`#tr${nb}`).css('color', 'orange')
        else
            $(`#tr${nb}`).css('color', 'green')
        nb++
    })
}


export function setTabApproveRecruters(arr_info, tab) {
    tab.setContent(setContentApproveTab(
        'approve_recruter_tab',
        'Selectionner les recruteurs à validés :',
        createTable('list',['checkbox', 'email_r', 'company_name', 'adresse_name'],
            ['','Email', "Nom de l'entreprise", 'Adresse'], arr_info, 'recruter'),
    ))
    tab.showContent()

    let nb = 1
    arr_info.forEach(function (info) {
        if (!info.company_name || !info.address_name)
            $(`#tr${nb}`).css('color', 'orange')
        else
            $(`#tr${nb}`).css('color', 'green')
        nb++
    })
}


export function setTabApproveOffers(arr_info, tab) {
    tab.setContent(setContentApproveTab(
        'approve_offer_tab',
        'Selectionner les annonces à validés :',
        createTable('list',['checkbox', 'title_a_o', 'company_name', 'date_a_o', 'detail'],
            ['','Titre', "Nom de l'entreprise", 'Date', 'Détail'], arr_info, 'approve_offer'),
    ))
    tab.showContent()
}

export function setTabApproveApplieds(arr_info, tab) {
    tab.setContent(setContentApproveTab(
        'approve_applied_tab',
        'Selectionner les postulants à validés :',
        createTable('list',['checkbox', 'email_a', 'first_name', 'last_name', 'cv_id', 'detail'],
            ['','Email', "Prénom", 'Nom', 'Cv', 'Annonce'], arr_info, 'approve_applied'),
    ))
    tab.showContent()
}

export function setTabAdminPage(arr_info, tab) {
    tab.setContent(
        '<div id="admin_page_tab">' +
        '<a href="/create_consultant" target="_blank">Ici pour ajouter un nouveaux consultants :</a>' +
        '<br>' +
        '<br>' +
        '<div>' +
        '<label for="list">' +
        'Selectionner les consulatnts que vous souhaitez suprimmer :' +
        '</label>' +
        '</div>' +
        '<div>' +
        createTable('list',['checkbox', 'email_c'],
            ['', 'Email'], arr_info, 'consultant') +
        '</div>' +
        '</div>'
    )
    tab.showContent()
}


function setContentApproveTab(tab_id, descr, table)
{
    return  `<div id="${tab_id}">` +
                '<div>' +
                    `<p>${descr}</p>` +
                '</div>' +
                '<div>' +
                    table +
                '</div>' +
            '</div>'
}