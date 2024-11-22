<template>
  <div>
    <h2>{{ isLogin ? 'Login' : 'Registrieren' }}</h2>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="username">Benutzername</label>
        <input
          v-model="username"
          type="text"
          id="username"
          class="form-control"
          placeholder="Benutzername"
          required
        />
      </div>
      <div class="form-group">
        <label for="password">Passwort</label>
        <input
          v-model="password"
          type="password"
          id="password"
          class="form-control"
          placeholder="Passwort"
          required
        />
      </div>
      <button type="submit" class="btn btn-primary">
        {{ isLogin ? 'Login' : 'Registrieren' }}
      </button>
    </form>

    <div>
      <p @click="toggleForm" style="cursor: pointer;">
        {{ isLogin ? 'Noch keinen Account? Registriere dich' : 'Bereits registriert? Login' }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      username: '',
      password: '',
      isLogin: true, // Standardmäßig auf Login setzen
    };
  },
  mounted() {
    // Wenn der Benutzer bereits eingeloggt ist, weiterleiten
    const user = localStorage.getItem('user');
    if (user) {
      this.$router.push('/transactions');
    }
  },
  methods: {
    toggleForm() {
      this.isLogin = !this.isLogin;
      this.username = '';
      this.password = '';
    },
    async handleSubmit() {
      if (this.isLogin) {
        await this.login();
      } else {
        await this.register();
      }
    },
    async login() {
      try {
        const response = await fetch('http://localhost/budget_planner/backend/login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            username: this.username,
            password: this.password,
          }),
          credentials: 'include', // Sendet die Session-Cookies mit
        });

        const textResponse = await response.text(); // Antwort als Text erhalten
        console.log("Serverantwort als Text:", textResponse); // Logge die rohe Antwort

        const data = JSON.parse(textResponse); // Versuche, die Antwort als JSON zu parsen
        console.log("Geparste JSON-Antwort:", data); // Logge die geparste Antwort

        if (data.status === 'success') {
          alert('Login erfolgreich');

          // Nutzerstatus lokal speichern
          localStorage.setItem('user', JSON.stringify(data.user)); // Speichere User-Daten

          // Weiterleitung zur Transaktionsliste
          this.$router.push('/transactions');
        } else {
          alert(data.message); // Fehlernachricht anzeigen
        }
      } catch (error) {
        console.error('Fehler beim Login:', error);
        alert('Ein Fehler ist aufgetreten. Bitte überprüfe deine Internetverbindung.');
      }
    },
    async register() {
      try {
        const response = await fetch('http://localhost/budget_planner/backend/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            username: this.username,
            password: this.password,
          }),
        });

        const data = await response.json();
        if (data.status === 'success') {
          alert('Registrierung erfolgreich!');

          localStorage.setItem('user', JSON.stringify(data.user)); // Speichere User-Daten

          // Weiterleitung zur Transaktionsseite
          this.$router.push('/transactions');
        } else {
          alert('Registrierung fehlgeschlagen: ' + data.message);
        }
      } catch (error) {
        console.error('Fehler bei der Registrierung:', error);
        alert('Ein Fehler ist aufgetreten. Bitte überprüfe deine Internetverbindung.');
      }
    },
  },
};
</script>

<style>
/* Einfache CSS-Stile für das Formular */
.form-group {
  margin-bottom: 15px;
}
</style>
