import {setTabOffer, setTabProfil} from "./setTab";
import {errorRequest} from "./errorRequest.js";

//FONCTION QUI RECUPERE LES DONNEES

//Premiere fonction, recupere l'information principale pour activer les bons onglets
const $ = require("jquery");

export function getUser(user_info, tab) {
    $.ajax({
        url: '/profil_get_user',
        method: 'POST',
        data: {id: user_info.id, password: user_info.password},
        dataType: 'json'
    })
        .done(function (response) {
            setTabProfil(JSON.parse(response), tab)
        })
        .fail(function (error) {
            errorRequest(error.responseText, tab)
        })
}

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

//Recupere les donné des offre qui appartienne au recruteur
export function getMyOfferList(user_info, tab) {
    $.ajax({
        url: '/profil_get_recruter_offers',
        method: 'POST',
        data: {id: user_info.id, password: user_info.password},
        dataType: 'json'
    })
        .done(function (response) {
            setTabOffer(JSON.parse(response), tab)
        })
        .fail(function (error) {
            errorRequest(error.responseText, getUser)
        })
}
