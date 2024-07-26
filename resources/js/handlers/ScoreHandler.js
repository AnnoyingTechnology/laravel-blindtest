import { DOMHelper } from '../utils/DOMHelper';

export class ScoreHandler {
    handleScoresReset(e) {
        console.log(e);
        const infoElement = this.createInfoElement('Scores have been reset');
        DOMHelper.appendToMessages(infoElement);
        DOMHelper.scrollToBottom();
    }

    handleScoreIncrease(e) {
        console.log(e);
        const scoreElement = this.createScoreElement(e.scores);
        DOMHelper.appendToMessages(scoreElement);
        DOMHelper.scrollToBottom();
    }

    createInfoElement(text) {
        return DOMHelper.createElement('div', ['d-flex', 'justify-content-end', 'mb-2', 'badge'], null, text);
    }

    createScoreElement(scores) {
        const container = DOMHelper.createElement('div', ['d-flex', 'justify-content-end']);
        Object.entries(scores).forEach(([username, score]) => {
            const scoreMessage = DOMHelper.createElement('span', ['mb-2', 'me-2', 'badge', 'text-warning'], null, `${username}'s score is ${score}`);
            container.appendChild(scoreMessage);
        });
        return container;
    }
}