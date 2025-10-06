document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const usernameInput = document.getElementById('username');
    const messageInput = document.getElementById('message');

    // Function to fetch and display messages
    function fetchMessages() {
        fetch('get_messages.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    chatBox.innerHTML = '';
                    data.messages.forEach(msg => {
                        const msgDiv = document.createElement('div');
                        msgDiv.classList.add('chat-message');
                        const time = new Date(msg.created_at).toLocaleTimeString();
                        msgDiv.innerHTML = `<strong>${msg.username}</strong> [${time}]: ${msg.message}`;
                        chatBox.appendChild(msgDiv);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
    }

    // Initial fetch
    fetchMessages();

    // Poll for new messages every 3 seconds
    setInterval(fetchMessages, 3000);

    // Handle form submission
    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const username = usernameInput.value.trim();
        const message = messageInput.value.trim();

        if (!username || !message) {
            alert('Please enter both username and message.');
            return;
        }

        fetch('send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                fetchMessages();
            } else {
                alert('Failed to send message: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            alert('Error sending message');
            console.error('Error:', err);
        });
    });
});
