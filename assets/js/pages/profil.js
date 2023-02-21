import {Tab} from "./profil_lib/classes.js";
import {
    sendData_Offer,
    sendData_profil,
    sendData_approve_candidates,
    sendData_approve_recruters,
    sendData_approve_offers,
    sendData_approve_applieds,
    sendData_admin_page
} from "./profil_lib/sendData.js";
import {
    getUser,
    getMyOfferList,
    getNoValidatedCandidates,
    getNoValidatedRecruters,
    getNoValidatedOffers,
    getNoValidatedApplieds,
    getConsultants,
} from "./profil_lib/getData.js";
import {setTabs} from "./profil_lib/setTab";

const $ = require('jquery')

$(document).ready(() => {
    console.log('jQuery est prêt à l\'utilisation')

    //Preparation des class
    const user_info = JSON.parse($('#user_info').text())
    const tabs = {
        profil: new Tab(user_info,'#profil', getUser, sendData_profil, true, false),
        my_offer: new Tab(user_info,'#my_offer', getMyOfferList, sendData_Offer),
        approve_candidate: new Tab(user_info,'#approve_candidate', getNoValidatedCandidates, sendData_approve_candidates),
        approve_recruter: new Tab(user_info,'#approve_recruter', getNoValidatedRecruters, sendData_approve_recruters),
        approve_offer: new Tab(user_info,'#approve_offer', getNoValidatedOffers, sendData_approve_offers),
        approve_applied: new Tab(user_info,'#approve_applied', getNoValidatedApplieds, sendData_approve_applieds),
        admin_page: new Tab(user_info,'#admin_page', getConsultants, sendData_admin_page),
    }

    //Déroulement de la page
    setTabs(tabs, user_info)
    for (let key in tabs) {
        $(tabs[key].id).on('click', function () {
            desactiveAllTabs(tabs)
            tabs[key].activatedTab()
            tabs[key].setTab()
        })
    }
    $('#validate_data').on('click', function () {
        let tab = null
        for (let key in tabs) {
            if (tabs[key].activated)
                tab = tabs[key]
        }
        if (tab) {
            tab.sendDataValue()
        } else
            alert('Erreur recharger la page')
    })


    //Autre Fonction
    function desactiveAllTabs(tabs) {
        for (let key in tabs)
            tabs[key].disableTab()
        $('#main_content').html('')
    }
})
