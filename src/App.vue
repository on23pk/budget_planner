<template>
  <div id="app">
    <nav class="navbar">
      <h1>Budget Planner</h1>
      <button @click="logout" class="btn btn-danger">Logout</button>
    </nav>
    <router-view></router-view> <!-- Dynamischer Inhalt basierend auf der Route -->
  </div>
</template>

<script>
export default {
  name: 'App',
  methods: {
    async logout() {
      try {
        const response = await fetch('http://localhost/budget_planner/backend/logout.php', {
          method: 'POST',
          credentials: 'include', // Sende Session-Cookies mit
        });
        const result = await response.json();
        if (result.status === 'success') {
          // Entferne den Benutzer aus localStorage
          localStorage.removeItem('user');

          // Weiterleitung zur Login-Seite (Pfad "/")
          this.$router.push('/'); // Ändere hier den Pfad zu "/"
        } else {
          alert('Fehler beim Logout: ' + result.message);
        }
      } catch (error) {
        console.error('Fehler beim Logout:', error);
        alert('Fehler beim Logout');
      }
    },
  },
};
</script>

<style>
/* Globale Stile */
body {
  font-family: Arial, sans-serif;
  background-color: #f9f9f9;
  margin: 0;
  padding: 0;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
}

.btn-danger {
  background-color: #dc3545;
  border: none;
  padding: 8px 12px;
  color: white;
  cursor: pointer;
}

.btn-danger:hover {
  background-color: #c82333;
}
</style>
