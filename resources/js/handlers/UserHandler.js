import { DOMHelper } from '../utils/DOMHelper';

export class UserHandler {
    handleUserJoined(e) {
        console.log(e);
        const infoElement = this.createInfoElement(`${e.username} just joined`);
        DOMHelper.appendToMessages(infoElement);
        DOMHelper.scrollToBottom();
    }

    createInfoElement(text) {
        return DOMHelper.createElement('div', ['d-flex', 'justify-content-end', 'mb-2', 'badge'], null, text);
    }
}