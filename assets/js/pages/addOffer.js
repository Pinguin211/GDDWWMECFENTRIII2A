const $ = require('jquery')

$(document).ready(() => {
    console.log('jQuery est prêt à l\'utilisation')

    let arr_location = {city : {0 : 'non chargé'}, department : {0 : 'non chargé'}, region : {0 : 'non chargé'}}


    getLocationList()


    function getLocationList() {
        $.ajax({
            url: '/get_location_list',
            dataType: 'json'
        })
            .done(function (response) {
                arr_location = JSON.parse(response);
                //setLocationType(arr_location, $('#offer_location_id').val())
            })
            .fail(function (error) {
                console.log('Liste des lieux chargé')
            })
    }


    $('#offer_location_type').on('click', function () {
        setLocationType(arr_location)
    })


    function setLocationType(arr_location, id = false) {
        const type = $('#offer_location_type').val()
        switch (type) {
            case '1':
                setLocationId([{id : 0, name : 'Votre Adresse'}], id)
                break
            case '2':
                setLocationId(arr_location['city'], id)
                break
            case '3':
                setLocationId(arr_location['department'], id)
                break
            case '4':
                setLocationId(arr_location['region'], id)
                break
        }
    }

    function setLocationId(arr, id = false) {
        const loc = $('#offer_location_id')
        loc.children().remove()
        arr.forEach(elem => {
            loc.append(`<option value="${elem.id}">${elem.name}</option>`)
        })
        if (id)
            loc.val(id)
        else
            loc.val(arr[0]['id'])
    }


})
