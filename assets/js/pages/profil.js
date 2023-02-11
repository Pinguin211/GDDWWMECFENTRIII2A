const $ = require('jquery')

$(document).ready(() => {
    console.log('jQuery est prêt à l\'utilisation')

    //Preparation des class
    const arr = $('main').attr('id').split(' ')
    const id = arr[0]
    const password = arr[1]
    const tabs = {
        profil: new Tab('#profil', sendData_profil, true, false),
        my_offer: new Tab('#my_offer'),
        approve_signup: new Tab('#approve_signup'),
        approve_offer: new Tab('#approve_offer'),
        approve_applied: new Tab('#approve_applied'),
        admin_page: new Tab('#admin_page'),
    }

    //Déroulement de la page
    getUser()
    for (let key1 in tabs) {
        $(tabs[key1].id).on('click', function (tab) {
            for (let key2 in tabs)
                tabs[key2].disableTab()
            tabs[key1].showContent()
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


    //Premiere fonction, recupere l'information principale pour activer les bons onglet
    function getUser() {
        $.ajax({
            url: '/profil_get_user',
            method: 'POST',
            data: {id: id, password: password},
            dataType: 'json'
        })
            .done(function (response) {
                console.log(JSON.parse(response))
                setTabs((JSON.parse(response)))
            })
            .fail(function (error) {
                errorRequest(error.responseText, getUser)
            })
    }


    //Set le contenu html des differentes page selon le grade de l'utilisateurs
    function setTabs(arr_info) {
        //Partie user
        tabs.profil.setContent(
            '<div>' +
            '<h1>Profil :</h1>' +
            '<label for="email">Email :</label>' +
            `<input type="text" id="email" value="${arr_info.user.email}">` +
            '</div>'
        )

        //Partie candidat
        if (arr_info.candidate) {
            tabs.profil.updateContent(
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
            tabs.profil.updateContent(
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
            tabs.my_offer.show_tab()
            updateCityList()
            tabs.my_offer.setContent('hello')


            //Affiche la table active
            for (let key in tabs) {
                if (tabs[key].activated)
                    tabs[key].showContent()
            }
        }
    }

    //FONCTION QUI ENVOIE LES DONNEES

    //tab profil
    function sendData_profil() {
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
        if (data["candidate"]['first_name'] && data['candidate']['last_name'])
            sendCv()
        sendData('/profil_update_candidate_recruter', JSON.stringify(data))
    }

    function sendData(url, json_info) {
        $.ajax({
            url: url,
            method: 'POST',
            data: {id: id, password: password, info: json_info},
            dataType: 'text'
        })
            .done(function (response) {
                alert(response)
                getUser()
            })
            .fail(function (error) {
                alert(error.responseText)
            })
    }

    function sendCv() {
        const fd = new FormData();
        const files = $('#cv')[0].files[0];
        fd.append('file', files);
        fd.append('id', id)
        fd.append('password', password)

        $.ajax({
            url: '/profil_update_cv',
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            dataType: 'text'
        })
            .done(function (response) {
                if (response)
                {
                    alert(response)
                    getUser()
                }
            })
            .fail(function (error) {
                alert(error.responseText)
            })
    }

    //Fonction qui recupere les donnée
    function updateCityList() {
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
            .fail(function (error) {
                console.log('Liste des ville non récupérè')
            })
    }

    function errorRequest(error_message, callback) {
        $('#main_content').html(
            '<div class="error_content">' +
            '<h1 id="title">Erreur :</h1>' +
            `<p id="description">Erreur lors de la requètes : ${error_message}</p>` +
            '<button class="btn" id="error_refresh">Recharger</button>' +
            '</div>'
        );
        $('#error_refresh')
            .unbind("click")
            .on("click", callback)
    }
})

//CLASSE UTILE

class Tab {
    constructor(id, getDataCallback, activated = false, hidden = true) {
        this.id = id
        this.activated = activated
        this.hidden = hidden
        this.content = ''
        this.getDataCallback = getDataCallback;
    }

    show_tab() {
        $(this.id).removeAttr('hidden')
        this.hidden = false
    }

    addContent(content) {
        this.content = this.content + content
    }

    setContent(content) {
        this.content = content
    }

    updateContent(content) {
        this.addContent(content)
        this.showContent()
    }

    showContent() {
        $('#main_content').html(this.content)
        this.activatedTab()
    }

    activatedTab() {
        $(this.id).addClass('active-tabs')
        $(this.id).removeClass('disabled-tabs')
        this.activated = true;
    }

    disableTab() {
        $(this.id).removeClass('active-tabs')
        $(this.id).addClass('disabled-tabs')
        this.activated = false;
    }

    sendDataValue() {
        return this.getDataCallback()
    }
}