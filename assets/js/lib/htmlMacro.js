/**
 * Fonctions qui renvoient des morceaux de code html
 */


function node_start(type, attr = []) {
    let str = '<' + type
    const keys = Object.keys(attr)
    keys.forEach(function (key) {
        if (attr[key] === false || attr[key] === null)
            str += ' ' + key
        else
            str += ' ' + key + '="' + attr[key] + '"'
    })
    str += '>'
    return str
}

function node_end(type) {
    return '</' + type + '>'
}

function master_node(type, attr, contents) {
    if (Array.isArray(contents))
        return [node_start(type, attr), contents.join(''), node_end(type)].join('')
    return [node_start(type, attr), contents, node_end(type)].join('')

}


///////////////////////////////////////////////////////////////////
//          TABLE

export function table(attr = [], contents = []) {
  return master_node('table', attr, contents)
}


export function thead (attr = [], contents = []) {
    return master_node('thead', attr, contents)
}

export function tbody (attr = [], contents = []) {
    return master_node('tbody', attr, contents)
}

export function tr(attr = [], contents = []) {
    return master_node('tr', attr, contents)
}

export function th(content, attr = []) {
    return master_node('th', attr, content)
}

export function td(content, attr = []) {
    return master_node('td', attr, content)
}


//////////////////////////////////////////////////
//      INPUT


function input(type, attr = []) {
    attr.type = type
    return node_start('input', attr)
}

export function checkbox(attr = []) {
    return input('checkbox', attr)
}

////////////////////////////////////////////////
//      AUTRE

export function a(content, attr = []) {
    return master_node('a', attr, content)
}

export function i(content, attr = []) {
    return master_node('i', attr, content)
}