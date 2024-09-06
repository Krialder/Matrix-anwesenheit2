class MatrixEffect 
{
    constructor() 
    {
        // Select the matrix container element
        this.matrix = document.querySelector('.matrix');
    }

    startRain() 
    {
        // Make the matrix container visible
        this.matrix.style.visibility = 'visible';
        
        // Calculate the number of columns based on the window width
        const columns = Math.floor(window.innerWidth / 20);
        
        // Create and animate spans for each column
        for (let i = 0; i < columns; i++) 
        {
            const span = document.createElement('span');
            span.style.left = `${i * 20}px`;
            span.style.animationDuration = `${Math.random() * 10 + 5}s`;
            span.style.animationDelay = `${Math.random() * 10}s`;

            // Generate random symbols for the span
            span.innerHTML = Array(100).fill(0).map(() => 
            {
                const symbol = String.fromCharCode(0x30A0 + Math.floor(Math.random() * 96));
                return symbol;
            }).join('');

            // Append the span to the matrix container
            this.matrix.appendChild(span);
        }

        // Reveal the letters after 4 seconds
        setTimeout(() => {
            this.animateLetters();
        }, 4000);
    }

    animateLetters() 
    {
        // Select all elements with the class 'animate'
        const labels = document.querySelectorAll('.animate');
        
        // Animate each label by revealing its text character by character
        labels.forEach((label, index) => 
        {
            const text = label.textContent;
            label.textContent = '';
            label.classList.remove('hidden');
            text.split('').forEach((char, i) => 
            {
                setTimeout(() => 
                {
                    label.textContent += char;
                }, i * 100);
            });
        });
    }
}

class LoginForm 
{
    constructor() 
    {
        // Initialize login attempts
        this.attempts = 5;
        
        // Create an instance of MatrixEffect
        this.matrixEffect = new MatrixEffect();
        
        // Select the login container, attempts display, and crack effect elements
        this.loginContainer = document.querySelector('.login-container');
        this.attemptsDisplay = document.querySelector('.attempts');
        this.crackEffect = document.querySelector('.crack');
    }

    handleLogin(event) 
    {
        event.preventDefault();
        
        // Get the username and password values from the input fields
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        // Send a POST request to the server to validate the login
        fetch('retrieve_logs.php', 
        {
            method: 'POST',
            headers: 
            {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `username=${username}&password=${password}`
        })
        .then(response => response.json())
        .then(data => 
        {
            if (data.message === "Login successful.") 
            {
                // Redirect to the account details page if login is successful
                window.location.href = 'Account_Details.html';
            } 
            else 
            {
                // Decrement the attempts counter if login fails
                this.attempts--;
                if (this.attempts > 0) 
                {
                    // Trigger the break animation if attempts are left
                    this.triggerBreakAnimation(data.message);
                } 
                else 
                {
                    // Alert the user if no attempts are left
                    alert('No more attempts left.');
                }
            }
        })
        .catch(error => 
        {
            console.error('Error:', error);
            // Display an error message if the server connection fails
            this.displayErrorMessage('Failed to connect to the server.');
        });
    }

    triggerBreakAnimation(message) 
    {
        // Make the crack effect visible
        this.crackEffect.style.visibility = 'visible';

        setTimeout(() => 
        {
            // Add the collapse class to the matrix and login container elements
            this.matrixEffect.matrix.classList.add('collapse');
            this.loginContainer.classList.add('collapse');

            setTimeout(() => 
            {
                // Hide the matrix and login container, and show the attempts display
                document.body.style.backgroundColor = 'black';
                this.matrixEffect.matrix.style.visibility = 'hidden';
                this.loginContainer.style.visibility = 'hidden';
                this.crackEffect.style.visibility = 'hidden';
                this.attemptsDisplay.textContent = message;
                this.attemptsDisplay.style.visibility = 'visible';

                setTimeout(() => 
                {
                    // Reset the visibility and remove the collapse class
                    this.attemptsDisplay.style.visibility = 'hidden';
                    document.body.style.backgroundColor = 'black';
                    this.matrixEffect.matrix.style.visibility = 'visible';
                    this.loginContainer.style.visibility = 'visible';
                    this.matrixEffect.matrix.classList.remove('collapse');
                    this.loginContainer.classList.remove('collapse');
                }, 2000);
            }, 1000);
        }, 1000);
    }

    displayErrorMessage(message) 
    {
        // Create and display an error message element
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message';
        errorMessage.textContent = message;
        document.body.appendChild(errorMessage);
    }
}

// Initialize and start the matrix effect
const matrixEffect = new MatrixEffect();
setTimeout(() => matrixEffect.startRain(), 1000);

// Attach event listener to the login form
const loginForm = new LoginForm();
document.querySelector('.login-form').addEventListener('submit', (event) => loginForm.handleLogin(event));