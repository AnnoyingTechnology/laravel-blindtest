export class DOMHelper {
    static createElement(tag, classes = [], id = null, text = null, styles = []) {
        const element = document.createElement(tag);
        if (classes.length) element.classList.add(...classes);
        if (id) element.id = id;
        if (text) element.innerText = text;
        if (styles.length) {
            styles.forEach(style => {
                const [property, value] = style.split(':');
                element.style[property.trim()] = value.trim();
            });
        }
        return element;
    }

    static appendToMessages(element) {
        document.getElementById('messages').appendChild(element);
    }

    static scrollToBottom() {
        $('#messages').scrollTop($('#messages')[0].scrollHeight);
    }
}