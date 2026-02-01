<h2>Login</h2>

<?= $this->Form->create() ?>
<div class="mb-3"><?= $this->Form->control('email', ['class' => 'form-control']) ?></div>
<div class="mb-3"><?= $this->Form->control('password', ['type' => 'password', 'class' => 'form-control']) ?></div>
<?= $this->Form->button('Login', ['class' => 'btn btn-success']) ?>
<?= $this->Form->end() ?>
<br>
<!--https://developers.google.com/identity/branding-guidelines-->
<!--Used the auto generate button, but addeed some bootstrap as it wasn't coming the same-->
<button id="googleSignInBtn" class="btn btn-outline-secondary w-30" style="border: 1px solid grey;">
  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 48 48" style="margin-right: 8px; vertical-align: middle; display: inline;">
    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
  </svg>
  Sign in with Google
</button>
<hr>
<p>No account? <?= $this->Html->link('Register', ['action' => 'add']) ?>
</p>

<!--SDK https://firebase.google.com/docs/web/setup -->
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
<!--Use some AI for assistance, had too many issues honestly to make it to work-->
<script>
    // https://firebase.google.com/docs/auth/web/google-signin
    const firebaseConfig = {
        apiKey: "<?= \Cake\Core\Configure::read('firebase.apiKey') ?>",
        authDomain: "<?= \Cake\Core\Configure::read('firebase.authDomain') ?>",
        projectId: "<?= \Cake\Core\Configure::read('firebase.projectId') ?>",
        storageBucket: "<?= \Cake\Core\Configure::read('firebase.storageBucket') ?>",
        messagingSenderId: "<?= \Cake\Core\Configure::read('firebase.messagingSenderId') ?>",
        appId: "<?= \Cake\Core\Configure::read('firebase.appId') ?>"
    };

    firebase.initializeApp(firebaseConfig);

    document.getElementById('googleSignInBtn').addEventListener('click', () => {
        const provider = new firebase.auth.GoogleAuthProvider();

        firebase.auth().signInWithPopup(provider)
            .then((result) => {
                const user = result.user;
                return user.getIdToken().then((idToken) => {
                    return fetch('<?= $this->Url->build('/oauth/google-callback') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            idToken: idToken,
                            user: {
                                uid: user.uid,
                                email: user.email,
                                firstName: user.displayName?.split(' ')[0] || 'User',
                                lastName: user.displayName?.split(' ').slice(1).join(' ') || ''
                            }
                        })
                    });
                });
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Login failed: ' + data.error);
                }
            })
            .catch((error) => alert('Sign-In failed: ' + error.message));
    });
</script>
