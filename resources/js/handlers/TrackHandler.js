import { DOMHelper } from '../utils/DOMHelper';

export class TrackHandler {
    handleNewTrack(e) {
        console.log(e);
        const infoElement = this.createInfoElement('New track playing');
        DOMHelper.appendToMessages(infoElement);
        this.playTrack(e.url);
        DOMHelper.scrollToBottom();
    }

    handleFastForward(e) {
        console.log(e);
        const audioPlayer = $('#audioplayer')[0];
        audioPlayer.currentTime = Math.min(audioPlayer.duration, audioPlayer.currentTime + 30);
        DOMHelper.scrollToBottom();
    }

    handleGiveUp(e) {
        console.log(e);
        const giveUpElement = this.createGiveUpElement(e);
        DOMHelper.appendToMessages(giveUpElement);
        DOMHelper.scrollToBottom();
    }

    handleTrackFound(e) {
        console.log(e);
        this.updateTrackFoundUI(e);
        DOMHelper.scrollToBottom();
    }

    playTrack(url) {
        $('#audioplayer').attr('src', url);
        $('#audioplayer')[0].play();
    }

    createInfoElement(text) {
        return DOMHelper.createElement('div', ['d-flex', 'justify-content-end', 'mb-2', 'badge'], null, text);
    }

    createGiveUpElement(e) {
        const container = DOMHelper.createElement('div', ['d-flex', 'justify-content-end']);
		const insultElement = DOMHelper.createElement('span', ['mb-2', 'me-2', 'badge', 'text-light'], null, e.insult);

		let infoText;
		if (e.remix) {
			infoText = `${e.name} (${e.remix}) by ${e.artist}`;
		} else {
			infoText = `${e.name} by ${e.artist}`;
		}

		const infoElement = DOMHelper.createElement('span', ['mb-2', 'me-2', 'badge', 'text-info', 'animate__animated', 'animate__headShake'], null, infoText);
		
		container.appendChild(insultElement);
		container.appendChild(infoElement);
		return container;
    }

    updateTrackFoundUI(e) {
        $(`#${e.uuid}`).addClass('gradient-background');
        const scoreBadge = `<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success animate__animated animate__bounceIn">+${e.score} (${e.found})</span>`;
        $(`#${e.uuid}`).css('position', 'relative').append(scoreBadge);
    }
}