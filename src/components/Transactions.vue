<template>
  <div class="container mt-4">
    <h2>Transaktionen</h2>
    
    <!-- Gesamtbudget-Anzeige -->
    <div class="mb-3">
      <h4>Gesamtbudget: <span :class="balanceClass">{{ balance.toFixed(2) }} €</span></h4>
    </div>

    <!-- Button zum Hinzufügen einer neuen Transaktion -->
    <div class="mb-3">
      <button @click="goToAddTransaction" class="btn btn-primary">Neue Transaktion hinzufügen</button>
    </div>
    
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Betrag</th>
          <th>Kategorie</th>
          <th>Datum</th>
          <th>Aktionen</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="transaction in transactions" :key="transaction.id">
          <td>{{ transaction.name }}</td>
          <td :class="transaction.category === 'Einnahme' ? 'text-success' : 'text-danger'">
            {{ parseFloat(transaction.amount).toFixed(2) }} €
          </td>
          <td>{{ transaction.category || 'Unkategorisiert' }}</td>
          <td>{{ transaction.transaction_date }}</td>
          <td>
            <!-- Löschen Button -->
            <button @click="deleteTransaction(transaction.id)" class="btn btn-danger btn-sm">
              <i class="bi bi-trash"></i> Löschen
            </button>
            <!-- Bearbeiten Button -->
            <button @click="editTransaction(transaction.id)" class="btn btn-warning btn-sm">
              <i class="bi bi-pencil"></i> Bearbeiten
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      transactions: [],
      balance: 0, // Gesamtbudget
    };
  },
  mounted() {
    this.fetchTransactions(); // Transaktionen beim Laden der Seite abrufen
  },
  methods: {
    async fetchTransactions() {
      const response = await fetch("http://localhost/budget_planner/backend/budget.php", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include", // Sitzung mit übermitteln
      });
      const result = await response.json();
      if (result.status === "success") {
        this.transactions = result.data;
        this.calculateBalance(); // Gesamtbudget nach Abruf der Transaktionen berechnen
      } else {
        alert(result.message);
      }
    },
    async deleteTransaction(id) {
    // Bestätigung vor dem Löschen der Transaktion
    const confirmDelete = confirm("Möchtest du diese Transaktion wirklich löschen?");
    if (confirmDelete) {
        try {
            // Sende die DELETE-Anfrage an den Server
            const response = await fetch("http://localhost/budget_planner/backend/budget.php", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ id }), // Transaktions-ID an den Server senden
                credentials: "include", // Sitzungsdaten mit senden
            });

            const result = await response.json();
            console.log(result);

            // Wenn die Antwort erfolgreich ist, Transaktion aus der Liste entfernen
            if (result.status === "success") {
                alert("Transaktion gelöscht");
                this.transactions = this.transactions.filter(transaction => transaction.id !== id);
                this.calculateBalance(); // Budget nach Löschen neu berechnen
            } else {
                alert(result.message); // Fehlerbehandlung
            }
        } catch (error) {
            console.error("Fehler bei der Löschung:", error);
            alert("Fehler beim Löschen der Transaktion");
        }
    }
},
    editTransaction(id) {
      this.$router.push(`/edit-transaction/${id}`);
    },
    goToAddTransaction() {
      // Leitet den Benutzer zur Seite zum Hinzufügen einer neuen Transaktion weiter
      this.$router.push('/add-transaction');
    },
    calculateBalance() {
      let income = 0;
      let expenses = 0;

      // Berechne Einnahmen und Ausgaben
      this.transactions.forEach(transaction => {
        if (transaction.category === "Einnahme") {
          income += parseFloat(transaction.amount);
        } else if (transaction.category === "Ausgabe") {
          expenses += parseFloat(transaction.amount);
        }
      });

      this.balance = income - expenses; // Gesamtbudget = Einnahmen - Ausgaben
    },
  },
  computed: {
    balanceClass() {
      return this.balance >= 0 ? 'text-success' : 'text-danger'; // Wenn das Budget positiv ist, grün, sonst rot
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 900px;
}

.text-success {
  color: green;
}

.text-danger {
  color: red;
}
</style>
