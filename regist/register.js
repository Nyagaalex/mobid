document.addEventListener('DOMContentLoaded', () => {
    // Registration
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('reg-username').value.trim();
            const email = document.getElementById('reg-email').value.trim();
            const password = document.getElementById('reg-password').value;
            const confirm = document.getElementById('reg-confirm').value;
            const msg = document.getElementById('reg-message');

            //client-side validation
            if (!username || !email || !password || !confirm) {
                msg.textContent = "Fill in all fields.";
                msg.style.color = "#d32f2f";
                return;
            }
            if (password !== confirm) {
                msg.textContent = "Passwords do not match!";
                msg.style.color = "#d32f2f";
                return;
            }

            //send data to mysql via AJAX
            fetch('register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirm=${encodeURIComponent(confirm)}`
            })
            .then(response => response.text())
            .then(html => {
                //try extract message from the returned HTML
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let serverMsg = doc.querySelector('#reg-message');
                if (serverMsg) {
                    msg.innerHTML = serverMsg.innerHTML;
                    msg.className = serverMsg.className;
                } else {
                    msg.textContent = "Registration failed. Please try again.";
                    msg.style.color = "#d32f2f";
                }
        
                if (msg.className.includes('success')) regForm.reset();
            })
            .catch(() => {
                msg.textContent = "An error occurred. Please try again.";
                msg.style.color = "#d32f2f";
            });
        });
    }

    //login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('login-username').value.trim();
            const password = document.getElementById('login-password').value;
            const msg = document.getElementById('login-message');

            if (!username || !password) {
                msg.textContent = "Please fill in all fields.";
                msg.style.color = "#d32f2f";
                return;
            }

            // Send login data to mysql via AJAX
            fetch('login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.text())
            .then(html => {
                //try extract message from the returned HTML
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let serverMsg = doc.querySelector('#login-message');
                if (serverMsg) {
                    msg.innerHTML = serverMsg.innerHTML;
                    msg.className = serverMsg.className;
                    if (msg.className.includes('success')) {
                        setTimeout(() => {
                            window.location.href = "../home.php";
                        }, 1000);
                    }
                } else if (html.includes('Location: ../home.php')) {
                    //if redirected , go to home
                    window.location.href = "../home.php";
                } else {
                    msg.textContent = "Login failed. Please try again.";
                    msg.style.color = "#d32f2f";
                }
            })
            .catch(() => {
                msg.textContent = "An error occurred. Please try again.";
                msg.style.color = "#d32f2f";
            });
        });
    }
});