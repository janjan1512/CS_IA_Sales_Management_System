const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const loginButton = document.getElementById("login-button");

if (loginButton) {
    loginButton.addEventListener("click", (e) => {
        e.preventDefault();
        const email = emailInput.value;
        const password = passwordInput.value;

        if (email && password) {

            alert("Login successful!");
  
            window.location.href = "index.php";
        } else {
            alert("Please enter correct email and password.");
        }
    });
}