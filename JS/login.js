document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    if (form) {
        form.addEventListener("submit", function(e) {
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();

            // validates that both email and password are entered
            if (!email || !password) {
                e.preventDefault();
                alert("Please enter both email and password.");
                return false;
            }
        });
    }
});