import {Tab} from "./profil_lib/classes.js";
import {sendData_Offer, sendData_profil, sendOfferAction} from "./profil_lib/sendData.js";
import {getUser, getMyOfferList} from "./profil_lib/getData.js";
import {setTabs} from "./profil_lib/setTab";
import tab from "bootstrap/js/src/tab";

const $ = require('jquery')

$(document).ready(() => {
    console.log('jQuery est prêt à l\'utilisation')

    //Preparation des class
    const user_info = JSON.parse($('#user_info').text())
    const tabs = {
        profil: new Tab(user_info,'#profil', getUser, sendData_profil, false, false),
        my_offer: new Tab(user_info,'#my_offer', getMyOfferList, sendData_Offer, true),
        approve_signup: new Tab(user_info,'#approve_signup'),
        approve_offer: new Tab(user_info,'#approve_offer'),
        approve_applied: new Tab(user_info,'#approve_applied'),
        admin_page: new Tab(user_info,'#admin_page'),
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
