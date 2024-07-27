import { DOMHelper } from '../utils/DOMHelper';

export class MessageHandler {
    handleNewMessage(e) {
        console.log(e);
        const messageElement = this.createMessageElement(e);
        DOMHelper.appendToMessages(messageElement);
        DOMHelper.scrollToBottom();
    }

    createMessageElement(e) {
        const messageContainer = DOMHelper.createElement('div', ['d-flex', 'justify-content-start', 'mb-2', 'animate__animated', 'animate__fadeIn']);
        const messageElement = DOMHelper.createElement('div', ['bg-dark', 'p-2', 'rounded', 'float-start'], e.uuid);
        const messageUsername = DOMHelper.createElement('span', ['pe-2'], null, e.username, ['color:'+e.color]);
        const messageContent = DOMHelper.createElement('span', [], null, e.message);

        messageElement.appendChild(messageUsername);
        messageElement.appendChild(messageContent);
        messageContainer.appendChild(messageElement);

        return messageContainer;
    }
}