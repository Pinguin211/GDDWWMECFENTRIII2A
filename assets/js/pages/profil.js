const $ = require('jquery')

$(document).ready(() => {
    console.log('jQuery est prêt à l\'utilisation')

    const arr = $('main').attr('id').split(' ')
    const id = arr[0]
    const password = arr[1]
    const tabs = [
        new Tab('profil', true),
        new Tab('my_offer'),
        new Tab('approve_signup'),
        new Tab('approve_offer'),
        new Tab('approve_applied'),
        new Tab('admin_page'),
    ]

    getUser(id, password)
})


//FUNCTION UTILE

function getUser(id, password) {
    $.ajax({
        url: '/profil_get_user',
        method: 'POST',
        data: {id: id, password: password},
        dataType: 'json'
    })
        .done(function (response) {
            console.log(JSON.parse(response))
        })
        .fail(function (error) {
            console.log('Erreur lors de la requètes : ' + error.responseText)
        })
}

//CLASSE UTILE

class Tab {
   constructor(id, activated) {
       this.id = id
       this.activated = activated
   }
}

class Address {
    constructor(id, city_id, number, street_name) {
        this.id = id
        this.city_id = city_id
        this.number = number
        this.street_name = street_name
    }
}

class User {
    constructor(id, mail, password, roles) {
        this.id = id
        this.mail = mail
        this.password = password
        this.roles = roles
    }
}

class Recruter {
    constructor(id, user, company_name, address) {
        this.id = id
        this.user = user
        this.company_name = company_name
        this.address = address
    }
}

class Candidate {
    constructor(id, user, first_name, last_name, cv) {
        this.id = id
        this.user = user
        this.first_name = first_name
        this.last_name = last_name
        this.cv = cv
    }
}
