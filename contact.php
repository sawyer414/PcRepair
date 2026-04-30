<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js"></script>
</head>
<body>
    <h1>Contact Us</h1>
    <form id="contactForm">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        <label for="message">Message:</label><br>
        <textarea id="message" name="message" required></textarea><br><br>
        <button type="submit">Send</button>
    </form>
    <p id="responseMsg"></p>
    <script>
        // Replace with your Supabase project credentials
        const SUPABASE_URL = 'https://estqnicukbhhlvkduuca.supabase.co';
        const SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImVzdHFuaWN1a2JoaGx2a2R1dWNhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzczNDc0NTYsImV4cCI6MjA5MjkyMzQ1Nn0.EFbCxIQjv9HUWC7q3-A5ywwS-qX6rCaQ0pLKxkgfRns';
        const supabase = supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
            const createdAt = new Date().toISOString();

            if (!name || !email || !message) {
                document.getElementById('responseMsg').textContent = 'Please fill in all fields.';
                document.getElementById('responseMsg').style.color = 'red';
                return;
            }

            const { data, error } = await supabase
                .from('Ticket')
                .insert([
                    {
                        Name: name,
                        Email: email,
                        Message: message,
                        'Created at': createdAt
                    }
                ]);

            if (error) {
                document.getElementById('responseMsg').textContent = 'Error: ' + error.message;
                document.getElementById('responseMsg').style.color = 'red';
            } else {
                document.getElementById('responseMsg').textContent = 'Thank you for your message! We will get back to you soon.';
                document.getElementById('responseMsg').style.color = 'green';
                document.getElementById('contactForm').reset();
            }
        });
    </script>
</body>
</html>
