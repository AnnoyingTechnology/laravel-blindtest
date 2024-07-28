import $ from "jquery";
import { MessageHandler } from './handlers/MessageHandler';
import { TrackHandler } from './handlers/TrackHandler';
import { UserHandler } from './handlers/UserHandler';
import { ScoreHandler } from './handlers/ScoreHandler';
import { ChatService } from './services/ChatService';

// Configure CSRF
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

class ChatApplication {
    constructor() {
        this.messageHandler = new MessageHandler();
        this.trackHandler = new TrackHandler();
        this.userHandler = new UserHandler();
        this.scoreHandler = new ScoreHandler();
        this.chatService = new ChatService();
    }

    initialize() {
        this.setupEventListeners();
        this.setupInputHandler();
        this.setupButtonHandlers();
    }

    setupEventListeners() {
        window.Echo.channel('chatroom')
            .listen('.user.message', (e) => this.messageHandler.handleNewMessage(e))
            .listen('.user.joined', (e) => this.userHandler.handleUserJoined(e))
            .listen('.track.new', (e) => this.trackHandler.handleNewTrack(e))
            .listen('.track.fastforward', (e) => this.trackHandler.handleFastForward(e))
            .listen('.track.giveup', (e) => this.trackHandler.handleGiveUp(e))
            .listen('.track.clues', (e) => this.trackHandler.handleClues(e))
            .listen('.track.found', (e) => this.trackHandler.handleTrackFound(e))
            .listen('.scores.reset', (e) => this.scoreHandler.handleScoresReset(e))
            .listen('.scores.increase', (e) => this.scoreHandler.handleScoreIncrease(e));
    }

    setupInputHandler() {
        $('#messageInput').on('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.sendMessage();
            }
        });
    }

    setupButtonHandlers() {
        document.querySelectorAll('.command').forEach(button => {
            button.addEventListener('click', (event) => {
                const command = event.target.getAttribute('data-command');
                if (command) {
                    this.sendMessage(command);
                }
            });
        });
    }

    sendMessage(message = null) {
        const messageInput = $('#messageInput');
        const messageToSend = message || messageInput.val();
        
        if (messageToSend) {
            this.chatService.sendMessage(messageToSend)
                .then(response => {
                    console.log(response);
                    if (!message) {
                        messageInput.val('');
                    }
                    else {
                        $('#messageInput').trigger('focus');
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        }
    }
}

// Initialize the application
const chatApp = new ChatApplication();
chatApp.initialize();
