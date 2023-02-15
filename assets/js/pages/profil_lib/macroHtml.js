//MACRO HTML

import {checkbox, table, tbody, td, th, thead, tr, i, a} from "../../lib/htmlMacro";

export function createTable(id, arr_class_name, arr_value_head, infos) {
    return table({'id': id}, [
        thead([],[tr([], createTheadContents(arr_class_name, arr_value_head, id))]),
        tbody([], getBodyContent(id, arr_class_name, infos))
    ])
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

function getBodyContent(id, classes, infos) {
    let tds = []
    let nb = 1;
    infos.forEach(function (info) {
        tds.push(getRowContent(id, info, classes, nb.toString()))
        nb++
    })
    return tds
}

function getRowContent(id, info, classes, nb) {
    const id_checkbox = info.id + 'checkbox'
    return tr( {'id': `tr${nb}`}, [
        td(checkbox({'id': `checkbox${nb}`, 'value': info.id}), {'class': id + '_' + classes[0]}),
        td(info.title, {'class': id + '_' + classes[1]}),
        td(info.post_date, {'class': id + '_' + classes[2]}),
        td(info.validated ? 'Validé' : 'Non validé', {'class': id + '_' + classes[3]}),
        td(a(i('',{'class': 'bi bi-eye'}), {'href': '/detail_annonce?id=' + info.id}), {'class': id + '_' + classes[4]}),
        td(a(i('',{'class': 'bi bi-pencil'}), {'href': '/modifier_annonce?id=' + info.id}), {'class': id + '_' + classes[5]}),
    ])
}

