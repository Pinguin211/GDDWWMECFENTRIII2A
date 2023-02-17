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
        tabs.approve_signup.show_tab()
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
        '<div>' +
        '<h1>Profil :</h1>' +
        '<label for="email">Email :</label>' +
        `<input type="text" id="email" value="${arr_info.user.email}" disabled>` +
        '</div>'
    )

    //Partie candidat
    if (arr_info.candidate) {
        tab.addContent(
            '<div>' +
            '<label for="first_name">Prénom :</label>' +
            `<input type="text" id="first_name" value="${arr_info.candidate.first_name ?? ''}">` +
            '<label for="last_name">Nom :</label>' +
            `<input type="text" id="last_name" value="${arr_info.candidate.last_name ?? ''}">` +
            `<label for="cv">CV :${arr_info.candidate.cv_id ? '(Envoyé)' : '(non remis)'}</label>` +
            '<form id="cv_upload">' +
            '<input type="file" id="cv" name="cv">' +
            '</form>' +
            '</div>'
        )
    }

    //Partie Recruter
    if (arr_info.recruter) {
        tab.addContent(
            '<div>' +
            '<label for="company_name">Nom de votre entrprise :</label>' +
            `<input type="text" id="company_name" value="${arr_info.recruter.company_name ?? ''}">` +
            '<p>Adresse :</p>' +
            '<label for="number">Numero :</label>' +
            `<input type="number" id="number" value="${arr_info.recruter.address?.number ?? ''}">` +
            '<label for="street_name">Nom de la rue :</label>' +
            `<input type="text" id="street_name" value="${arr_info.recruter.address?.street_name ?? ''}">` +
            '<label for="city_id">Ville</label>' +
            '<select id="city_id">' +
            `<option value="${arr_info.recruter.address?.city_id ?? ''}">${arr_info.recruter.address?.city_name ?? 'Choisissez une ville'}</option>` +
            '</select>' +
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
            '<h1>Mes offres :</h1>' +
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
                createTable('offer_list',['checkbox', 'title', 'date', 'status', 'detail', 'mod'],
                    ['', 'Titre', 'Date', 'Status', 'Détail', 'Modifier'], arr_info) +
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