//CLASSE UTILE

const $ = require("jquery");

export class Tab {
    constructor(user_info, id, setTabCallback, sendDataCallback, activated = false, hidden = true) {
        this.id = id
        this.activated = activated
        this.hidden = hidden
        this.content = ''
        this.sendDataCallback = sendDataCallback;
        this.setTabCallback = setTabCallback
        this.userInfo = user_info
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

    showContent() {
        $('#main_content').html(this.content)
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
        this.sendDataCallback(this.userInfo, this)
    }

    setTab() {
        this.setTabCallback(this.userInfo, this)
    }
}