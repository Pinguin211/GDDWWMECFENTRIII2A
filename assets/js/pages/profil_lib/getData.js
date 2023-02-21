import {
    setTabOffer,
    setTabProfil,
    setTabApproveCandidates,
    setTabApproveRecruters,
    setTabApproveOffers,
    setTabApproveApplieds,
    setTabAdminPage
} from "./setTab";
import {errorRequest} from "./errorRequest.js";

//FONCTION QUI RECUPERE LES DONNEES

const $ = require("jquery");

//Recupere la liste des villes pour l'adresse du recruteur et set la liste dans la node
export function updateCityList() {
    $.ajax({
        url: '/get_city_list',
        dataType: 'json'
    })
        .done(function (response) {
            const arr = JSON.parse(response)
            arr.forEach(function (city) {
                $('#city_id').append(`<option value="${city.id}">${city.name}</option>`)
            })

        })
        .fail(function () {
            console.log('Liste des ville non récupérè')
        })
}

//Recupere les données pour l'onglet Profil
export function getUser(user_info, tab) {
    getData('/profil_get_user', user_info, tab, setTabProfil)
}

//Recupere les données des offre pour l'onglet mes offres
export function getMyOfferList(user_info, tab) {
    getData('/profil_get_recruter_offers', user_info, tab, setTabOffer)
}

//Recupere les données pour l'onglet gestion candidat
export function getNoValidatedCandidates(user_info, tab) {
    getData('/profil_get_no_validated_candidates', user_info, tab, setTabApproveCandidates)
}

//Recupere les données pour l'onglet gestion recruteurs
export function getNoValidatedRecruters(user_info, tab) {
    getData('/profil_get_no_validated_recruters', user_info, tab, setTabApproveRecruters)
}

//Recupere les données pour l'onglet gestion annonces
export function getNoValidatedOffers(user_info, tab) {
    getData('/profil_get_no_validated_offers', user_info, tab, setTabApproveOffers)
}

//Recupere les données pour l'onglet gestion des postulances
export function getNoValidatedApplieds(user_info, tab) {
    getData('/profil_get_no_validated_applieds', user_info, tab, setTabApproveApplieds)
}

//Recupere la liste des consultant pour l'onglet admin
export function getConsultants(user_info, tab) {
    getData('/profil_get_consultants', user_info, tab, setTabAdminPage)
}

function getData(url, user_info, tab, nextFunc) {
    $.ajax({
        url: url,
        method: 'POST',
        data: {id: user_info.id, password: user_info.password},
        dataType: 'json'
    })
        .done(function (response) {
            nextFunc(JSON.parse(response), tab)
        })
        .fail(function (error) {
            errorRequest(error.responseText, tab)
        })
}
