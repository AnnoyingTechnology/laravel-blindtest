import $ from "jquery";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
// Listen for new messages using Laravel Echo
window.Echo.channel('chatroom')
    .listen('.message.sent', (e) => {
        console.log(e);
        // Append the new message to the chatroom
		const messageContainer = document.createElement('div');
        const messageElement = document.createElement('div');
		const messageUsername = document.createElement('span');
		const messageContent = document.createElement('span');
		messageUsername.innerText = e.username;
		messageUsername.classList.add('text-black');
		messageUsername.classList.add('pe-2');

		messageElement.id = e.uuid;
		messageElement.classList.add('bg-dark');
		messageElement.classList.add('p-2');
		messageElement.classList.add('rounded');
		messageElement.classList.add('float-start');
        messageContent.innerText = e.message;

		messageContainer.classList.add('d-flex');
		messageContainer.classList.add('justify-content-start');
		messageContainer.classList.add('mb-2');
		messageContainer.classList.add('animate__animated');
		messageContainer.classList.add('animate__fadeIn');
		

		messageElement.appendChild(messageUsername);
		messageElement.appendChild(messageContent);
		messageContainer.appendChild(messageElement);
        document.getElementById('messages').appendChild(messageContainer);
		
		$('#messages').scrollTop($('#messages')[0].scrollHeight);

    }).listen('.track.new', (e) => {
        console.log(e);

		const infoContainer = document.createElement('div');
		infoContainer.classList.add('d-flex');
		infoContainer.classList.add('justify-content-end');
		infoContainer.classList.add('mb-2');
		infoContainer.classList.add('badge');
		infoContainer.innerText = 'New track playing';
		document.getElementById('messages').appendChild(infoContainer);

		$('#audioplayer').attr('src', e.url);
        $('#audioplayer')[0].play();

		$('#messages').scrollTop($('#messages')[0].scrollHeight);

    }).listen('.track.fastforward', (e) => {
        console.log(e);
		$('#audioplayer')[0].currentTime = Math.min(
			$('#audioplayer')[0].duration, 
			$('#audioplayer')[0].currentTime + 30
		);
		$('#messages').scrollTop($('#messages')[0].scrollHeight);

    }).listen('.user.joined', (e) => {
        console.log(e);

		const infoContainer = document.createElement('div');
		infoContainer.classList.add('d-flex');
		infoContainer.classList.add('justify-content-end');
		infoContainer.classList.add('mb-2');
		infoContainer.classList.add('badge');
		infoContainer.innerText = e.username  + ' just joined.';
		document.getElementById('messages').appendChild(infoContainer);

		$('#messages').scrollTop($('#messages')[0].scrollHeight);

    }).listen('.scores.reset', (e) => {
        console.log(e);

		const infoContainer = document.createElement('div');
		infoContainer.classList.add('d-flex');
		infoContainer.classList.add('justify-content-end');
		infoContainer.classList.add('mb-2');
		infoContainer.classList.add('badge');
		infoContainer.innerText = 'Scores have been reset';
		document.getElementById('messages').appendChild(infoContainer);

		$('#messages').scrollTop($('#messages')[0].scrollHeight);

    }).listen('.track.giveup', (e) => {
        console.log(e);

		const infoContainer = document.createElement('div');
		infoContainer.classList.add('d-flex');
		infoContainer.classList.add('justify-content-end');

		const insultMessage = document.createElement('span');
		
		insultMessage.classList.add('mb-2');
		insultMessage.classList.add('me-2');
		insultMessage.classList.add('badge');
		insultMessage.classList.add('text-light');
		insultMessage.innerText = e.insult;

		const infoMessage = document.createElement('span');

		infoMessage.classList.add('mb-2');
		infoMessage.classList.add('me-2');
		infoMessage.classList.add('badge');
		infoMessage.classList.add('text-info');
		infoMessage.classList.add('animate__animated');
		infoMessage.classList.add('animate__headShake');

		infoMessage.innerText = e.name+' by '+e.artist;

		infoContainer.appendChild(insultMessage);
		infoContainer.appendChild(infoMessage);
		document.getElementById('messages').appendChild(infoContainer);

		$('#messages').scrollTop($('#messages')[0].scrollHeight);


    }).listen('.track.found', (e) => {
        console.log(e);

		$('#'+e.uuid).addClass('gradient-background');

		const scoreBadge = '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success animate__animated animate__bounceIn">+'+e.score+' ('+e.found+')</span>';
    
		// Make sure the element has position relative to position the badge correctly
		$('#'+e.uuid).css('position', 'relative');
		$('#'+e.uuid).append(scoreBadge);

		$('#messages').scrollTop($('#messages')[0].scrollHeight);


    }).listen('.score.increase', (e) => {
        console.log(e);

		const infoContainer = document.createElement('div');
		infoContainer.classList.add('d-flex');
		infoContainer.classList.add('justify-content-end');

		$.each(e.scores, function(username, score){

			const infoMessage = document.createElement('span');

			infoMessage.classList.add('mb-2');
			infoMessage.classList.add('me-2');
			infoMessage.classList.add('badge');
			infoMessage.classList.add('text-warning');

			infoMessage.innerText = username + '\'s score is '+score;

			infoContainer.appendChild(infoMessage);
			

		});

		document.getElementById('messages').appendChild(infoContainer);

		$('#messages').scrollTop($('#messages')[0].scrollHeight);

    });

// Function to send a new message
window.sendMessage = function() {
    const messageInput = $('#messageInput');
    const message = messageInput.val();
    
    $.ajax({
        type: 'POST',
        url: '/chat/send-message',
        data: { message: message },
        success: function(response) {
            console.log(response);
            // Clear the input field after sending
            messageInput.val('');
        },
        error: function(error) {
            console.error(error);
        }
    });

    console.log('sent message');
};

$(function() {

    // Attach an event listener to the messageInput to send the message on Enter key press
    $('#messageInput').on('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent default Enter key behavior (e.g., form submission)
            window.sendMessage(); // Call the function to send the message
        }
    });

});