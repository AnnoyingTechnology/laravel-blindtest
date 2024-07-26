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
    }

    setupEventListeners() {
        window.Echo.channel('chatroom')
            .listen('.message.sent', (e) => this.messageHandler.handleNewMessage(e))
            .listen('.track.new', (e) => this.trackHandler.handleNewTrack(e))
            .listen('.track.fastforward', (e) => this.trackHandler.handleFastForward(e))
            .listen('.user.joined', (e) => this.userHandler.handleUserJoined(e))
            .listen('.scores.reset', (e) => this.scoreHandler.handleScoresReset(e))
            .listen('.track.giveup', (e) => this.trackHandler.handleGiveUp(e))
            .listen('.track.found', (e) => this.trackHandler.handleTrackFound(e))
            .listen('.score.increase', (e) => this.scoreHandler.handleScoreIncrease(e));
    }

    setupInputHandler() {
        $('#messageInput').on('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.sendMessage();
            }
        });
    }

    sendMessage() {
        const messageInput = $('#messageInput');
        const message = messageInput.val();
        
        this.chatService.sendMessage(message)
            .then(response => {
                console.log(response);
                messageInput.val('');
            })
            .catch(error => {
                console.error(error);
            });
    }
}

// Initialize the application
const chatApp = new ChatApplication();
chatApp.initialize();
